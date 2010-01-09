<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      user administrator class

 File:       admin.class.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango users.
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
* User Class
*/
class CUser extends CDpObject {
	var $user_id = NULL;
	var $user_username = NULL;
	var $user_password = NULL;
	var $user_parent = NULL;
	//var $user_type = NULL;
    //var $user_contact = NULL;
	//var $user_signature = NULL;
	var $user_first_name = NULL;
	var $user_last_name = NULL;
	//var $user_company = NULL;
	//var $user_department = NULL;
	var $user_email = NULL;
	var $user_phone = NULL;
	//var $user_home_phone = NULL;
	var $user_mobile = NULL;
	var $user_address1 = NULL;
	var $user_address2 = NULL;
	var $user_city = NULL;
	var $user_state = NULL;
	var $user_zip = NULL;
	var $user_country = NULL;
	//var $user_icq = NULL;
	//var $user_aol = NULL;
	var $user_birthday = NULL;
	//var $user_pic = NULL;
	var $user_day_hours = 8; 

	function CUser() {
		$this->CDpObject( 'users', 'user_id' );
	}

	function check() {
		if ($this->user_id === NULL) {
			return 'user id is NULL';
		}
		if ($this->user_password !== NULL) {
			$this->user_password = db_escape( trim( $this->user_password ) );
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		$q  = new DBQuery;
		if( $this->user_id ) {
		// save the old password
			$perm_func = "updateLogin";
			$q->addTable('users');
			$q->addQuery('user_password');
			$q->addWhere("user_id = $this->user_id");
			$pwd = $q->loadResult();
			if ($pwd != $this->user_password) {
				$this->user_password = md5($this->user_password);
			} else {
				$this->user_password = null;
			}

			$ret = db_updateObject( 'users', $this, 'user_id', false );
		} else {
			$perm_func = "addLogin";
			$this->user_password = md5($this->user_password);
			$ret = db_insertObject( 'users', $this, 'user_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			$acl =& $GLOBALS['AppUI']->acl();
			$acl->$perm_func($this->user_id, $this->user_username);
			return NULL;
		}
	}

	function delete( $oid = NULL, $aro_gr=null ) {
		$id = $this->user_id;
		$result = parent::delete($oid, $aro_gr);
		if (! $result) {
			$acl =& $GLOBALS['AppUI']->acl();
			$acl->deleteLogin($id);
		}
		
		$sql = "DELETE FROM user_setcap WHERE user_id = $this->user_id";
		db_exec( $sql );//si elimina anche da group all
		
		$sql = "DELETE FROM user_projects WHERE user_id = $this->user_id";
		db_exec( $sql );
		
		return $result;
 	}
 	
 	function updateAssignedGroups( $cslist) {//MODIFIED => TEST PASSED

        // process assignees
        if (!is_array($cslist))
			$tarr = explode( ";", $cslist );
		else 
			$tarr= $cslist;	
		
		$sc = array();
		$q  = new DBQuery;
		$q->addTable('user_setcap', 'us');
		$q->addQuery('group_id, us.setcap_id');
		$q->addWhere('group_id > 0 && us.user_id = '.$this->user_id);	
		$sc = $q->loadHashList();
		$q->clear();// array(group_id => setcap_id)
		
		$upsc=array();$delsc=array();$g=array();
		$g=array_keys($sc);
		$upg = array_intersect($g,$tarr);
     	$delg = array_diff($g,$upg);
		//print_r($delsc);
	    // delete all current entries from $cslist
       	foreach ($delg as $group_id) {
			if (intval($group_id > 0)) {
				if ($this->checkSetCapConsistency($group_id,$this->user_id)) {
					$sql = "DELETE FROM user_setcap WHERE group_id = $group_id AND user_id = $this->user_id";
					db_exec( $sql );
				}
			}
        }
       // print_r($tarr);
		foreach ($tarr as $group_id) {
			if ((intval($group_id) > 0)) {
			   $s = is_null($sc[$group_id]) ? 0 : $sc[$group_id];
               $sql = "REPLACE INTO user_setcap (user_id, group_id, setcap_id) VALUES ($this->user_id, $group_id, $s)";
               db_exec( $sql );
			}
		}
       // return true;
	}
	
	function addAssignedGroups( $cslist ) { //MODIFIED => TEST PASSED
		
	  	if (!is_array($cslist))
			$tarr = explode( ";", $cslist );
		else 
			$tarr= $cslist;	

		// delete all current entries from $cslist
      	//$tarr[]=-1;//add to all groups
        foreach ($tarr as $group_id) { 
        	if ((intval($group_id) <> 0)) {
				$sql = "INSERT INTO user_setcap (user_id, group_id) VALUES ($this->user_id, $group_id)";
				db_exec( $sql );
        	}
        }	
	}
	
	function getUserProject($ui = null) {
		if (is_null($ui))
			$ui = $this->user_id;
		$q = new DBQuery;
		$q->addTable('user_projects');
		$q->addQuery('project_id');
		$q->addWhere('user_id = '.$ui);
		return $q->loadColumn();
	}
	
	static function getGroups($ui) {
		if (is_null($ui)) {
			$AppUI->setMsg( "failed to view user projects", UI_MSG_ERROR );
			$AppUI->redirect();
			return false;
		}
		$q = new DBQuery;
		$q->addTable('user_setcap');
		$q->addQuery('group_id');
		$q->addWhere('user_id = '.$ui);
		return $q->loadColumn();
	}
	
	// Restituisce un vettore nella forma array(Group_id => n) dove n vale 0 se l'utente $ui è esterno al gruppo
	// mentre vale 1 se l'utente è interno al gruppo.
	static function getGroupsSetCap($ui) {
		if (is_null($ui)) {
			$AppUI->setMsg( "failed to view user projects", UI_MSG_ERROR );
			$AppUI->redirect();
			return false;
		}
		$ar_gr = array();
		$q = new DBQuery;
		$q->addTable('user_setcap');
		$q->addQuery('group_id');
		$q->addWhere('group_id > 0 && setcap_id > 0 && user_id = '.$ui);
		$ar_gr = $q->loadColumn();
		
		$gsc = null;
		$perms = new dPacl;
		foreach ($ar_gr as $g)
			$gsc[$g] = $perms->checkModule('projects','res',$ui,intval($g),1) ? 1 : 0;
		return $gsc;
	}
	
	// $oldMember = 1 se l'utente è interno al gruppo 0 altrimenti (lo stesso vale per newMember)
	static function updateUserProjects($ui, $group_id, $oldMember, $newMember) {
		if (is_null($ui))
			return false;
		if ($oldMember == $newMember && $newMember == 1) 
			return true;
		$q = new DBQuery;
		$q->addTable('projects');
		$q->addQuery('project_id');
		$q->addWhere('project_group = '.$group_id);
		$projects = $q->loadColumn();
		if ((intval($group_id) > 0) && !$newMember) {//l'utente diventa esterno al gruppo
			if (is_array($projects))
				foreach ($projects as $pid) {
					$sql = "DELETE FROM user_projects WHERE project_id = $pid AND user_id = $ui";
					db_exec( $sql );
					$sql = "REPLACE INTO user_projects (user_id, project_id, proles_id) VALUES ($ui, $pid, 0)";
					db_exec( $sql );
				}
        } else if ((intval($group_id) > 0) && $newMember) {//l'utente diventa interno al gruppo
        	foreach ($projects as $pid) {
				$sql = "DELETE FROM user_projects WHERE project_id = $pid AND user_id = $ui";
				db_exec( $sql );
			}
        }
        return true;
	}
	
	static function delUserProjects($ui) {
		if (is_null($ui))
			return false;
		$q = new DBQuery;
		$q->addTable('user_setcap');
		$q->addQuery('DISTINCT(project_id)');
		$q->addJoin('projects','p','project_group = group_id');
		$q->addWhere('setcap_id = 0 && user_id = '.$ui);
		$projects = $q->loadColumn();
		if (is_array($projects) && !empty($projects))
			foreach ($projects as $pid) {
				if ($pid <> "")
					$sql = "DELETE FROM user_projects WHERE project_id = $pid AND user_id = $ui";
				db_exec( $sql );
			}
        
        return true;
	}
}

?>