<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      groups class

 File:       groups.class
 Location:   pmango\modules\groups
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango groups.
 - 2006.07.26 Lorenzo
   First version, unmodified from dotProject 2.0.1.
   
---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team

 Other libraries used by PMango are redistributed under their own license.
 See ReadMe.txt in the root folder for details. 

 PMango is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation Inc., 51 Franklin St, 5th Floor, Boston, MA 02110-1301 USA

---------------------------------------------------------------------------
*/
/**
 *	@package PMango
 *	@subpackage modules
 *	@version $Revision: 1.9 $
*/
require_once( $AppUI->getSystemClass ('dp' ) );

/**
 *	Groups Class
 *	@todo Move the 'address' fields to a generic table
 */
class CGroup extends CDpObject {
/** @var int Primary Key */
	var $group_id = NULL;
/** @var string */
	var $group_name = NULL;

// these next fields should be ported to a generic address book
	var $group_phone1 = NULL;
	var $group_phone2 = NULL;
	var $group_fax = NULL;
	var $group_address1 = NULL;
	var $group_address2 = NULL;
	var $group_city = NULL;
	var $group_state = NULL;
	var $group_zip = NULL;
	var $group_email = NULL;

/** @var string */
	var $group_primary_url = NULL;
/** @var int */
	var $group_owner = NULL;
/** @var string */
	var $group_description = NULL;
/** @var int */
	var $group_type = null;
	
	var $group_custom = null;
	
	function CGroup() {
		$this->CDpObject( 'groups', 'group_id', 'group_id');
	}

// overload check
	function check() {
		if ($this->group_id === NULL) {
			return 'group id is NULL';
		}
		$this->group_id = intval( $this->group_id );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null, $aro_gr=null ) {
		$tables[] = array( 'label' => 'Projects', 'name' => 'projects', 'idfield' => 'project_id', 'joinfield' => 'project_group' );
		//$tables[] = array( 'label' => 'Departments', 'name' => 'departments', 'idfield' => 'dept_id', 'joinfield' => 'dept_company' );
		
		// DA CAMBIARE CON LE PROPAGAZIONI A CASCATA SULLE TABELLE CHE HO AGGIUNTO!!!!!!!!!!
		//$tables[] = array( 'label' => 'Users', 'name' => 'users', 'idfield' => 'user_id', 'joinfield' => 'user_company' );
	// call the parent class method to assign the oid
		return CDpObject::canDelete( $msg, $oid, $tables, $aro_gr );
	}
	
	function delete( $oid=null, $aro_gr=null ) {//MODIFIED => TEST PASSED
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (!$this->canDelete( $msg, null, $aro_gr )) {
			return $msg;
		}
                
        addHistory($this->_tbl, $this->$k, 'delete');
		$q  = new DBQuery;
		$q->setDelete($this->_tbl);
		$q->addWhere("$this->_tbl_key = '".$this->$k."'");
		$result = null;
		if (!$q->exec()) {
			$result = db_error();
		}
		$q->clear();
		
	     // delete all on this group for a hand-over of the task
	    if ($this->checkSetCapConsistency($this->group_id)) {
     		$sql = "DELETE FROM user_setcap WHERE group_id = $this->group_id";
 			db_exec( $sql );
 			$sql = "DELETE FROM group_setcap WHERE group_id = $this->group_id";
 			db_exec( $sql );
	    }

		return $result;
	}
	
	
	function updateAssignedMembers( $cslist, $rmUsers=false ) {//MODIFIED => TEST PASSED

        // process assignees
        if (!is_array($cslist))
			$tarr = explode( ";", $cslist );
		else 
			$tarr= $cslist;	
		
		$sc = array();
		$q  = new DBQuery;
		$q->addTable('user_setcap', 'us');
		$q->addQuery('user_id, us.setcap_id');
		$q->addWhere('us.group_id = '.$this->group_id);	
		$sc = $q->loadHashList();
		$q->clear();// array(user_id => setcap_id)
		
		$upu=array();$delu=array();$u=array();
		$u=array_keys($sc);
		$upu = array_intersect($u,$tarr);
     	$delu = array_diff($u,$upu);
		
	    // delete all current entries from $cslist
        if ($rmUsers == true) {
        	foreach ($delu as $user_id) {
				if (intval( $user_id ) > 0) {
					if ($this->checkSetCapConsistency($this->group_id,$user_id)) {
						$sql = "DELETE FROM user_setcap WHERE group_id = $this->group_id AND user_id = $user_id";
						db_exec( $sql );
					}
				}
        	}
        } else {      // delete all on this group for a hand-over of the task
        	if ($this->checkSetCapConsistency($this->group_id) ) {
                $sql = "DELETE FROM user_setcap WHERE group_id = $this->group_id";
                db_exec( $sql );
        	}
        }

		foreach ($tarr as $user_id) {
			if (intval( $user_id ) > 0) {
			   $s = is_null($sc[$user_id]) ? 0 : $sc[$user_id];
               $sql = "REPLACE INTO user_setcap (user_id, group_id, setcap_id) VALUES ($user_id, $this->group_id, $s)";
               db_exec( $sql );
			}
		}
       // return true;
	}
	
	
	function addAssignedMembers( $cslist ) { // OK
		
	  	if (!is_array($cslist))
			$tarr = explode( ";", $cslist );
		else 
			$tarr= $cslist;	

		// delete all current entries from $cslist
      
        foreach ($tarr as $user_id) { 
        	if (intval( $user_id ) > 0) {
				$sql = "INSERT INTO user_setcap (user_id, group_id) VALUES ($user_id, $this->group_id)";
				db_exec( $sql );
        	}
        }
			
	}	
	
	function updateAssignedSetcap( $cslist, $rmSetcap=false ) { //MODIFIED => TEST PASSED

        // process assignees
        if (!is_array($cslist))
			$tarr = explode( ";", $cslist );
		else 
			$tarr= $cslist;	
			
		$sc = array();
		$q  = new DBQuery;
		$q->addTable('group_setcap', 'gs');
		$q->addQuery('gs.setcap_id');
		$q->addWhere('gs.group_id = '.$this->group_id);	
		$sc = $q->loadColumn();
		$q->clear();// array(user_id => setcap_id)	

		$upsc = array_intersect($sc,$tarr);
     	$delsc = array_diff($sc,$upsc);//si propaga l'eliminazione degli el. che non si reiseriscono
     	
        // delete all current entries from $cslist
        if ($rmSetcap == true) {
        	foreach ($delsc as $setcap_id) {
				if ($setcap_id > '') {
					$sql = "DELETE FROM group_setcap WHERE group_id = $this->group_id AND setcap_id = $setcap_id";
					db_exec( $sql );
				}
        	}
        } else {      // delete all on this group for a hand-over of the task
            $sql = "DELETE FROM group_setcap WHERE group_id = $this->group_id";
            db_exec( $sql );
        }

		
        
     	foreach ($delsc as $s) {
	     	$q  = new DBQuery;
			$q->addTable('user_setcap', 'us');
			$q->addQuery('us.user_id');
			$q->addWhere('us.setcap_id = '.$s.' && us.group_id = '.$this->group_id);	
			$arr_u = $q->loadColumn();
			foreach ($arr_u as $u) {
	     		if ($this->checkSetCapConsistency($this->group_id, $u) ) {
	                $sql = "REPLACE INTO user_setcap (user_id, group_id, setcap_id) VALUES ($u, $this->group_id, 0)";
	                db_exec( $sql );
	        	}
			}
     	}
     	
		foreach ($tarr as $setcap_id) {
			if (intval( $setcap_id ) > 0) {
               $sql = "INSERT INTO group_setcap (setcap_id, group_id) VALUES ($setcap_id, $this->group_id)";
               db_exec( $sql );
			}
		}
       // return true;
	}
	
	function addAssignedSetcap( $cslist ) {//OK
		
	  	if (!is_array($cslist))
			$tarr = explode( ";", $cslist );
		else 
			$tarr= $cslist;	

        // delete all current entries from $cslist
      
        foreach ($tarr as $setcap_id) { 
        	if (intval( $setcap_id ) > 0) {
				$sql = "INSERT INTO group_setcap (setcap_id, group_id) VALUES ($setcap_id, $this->group_id)";
				db_exec( $sql );
        	}
        }
			
	}
}
?>
