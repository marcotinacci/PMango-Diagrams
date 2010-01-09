<?php 
/**---------------------------------------------------------------------------

 PMango Project

 Title:      dotProject class

 File:       dp.class.php
 Location:   pmango/classes
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       class

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   Second version, modified to create new control access policy.
 - 2006.07.18 Giovanni
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

---------------------------------------------------------------------------*/

/**
 *	@package PMango
 *	@subpackage modules
 *	@version $Revision: 1.25 $
 */

require_once $AppUI->getSystemClass('query');
require_once "$baseDir/classes/permissions.class.php";

/**
 *	CDpObject Abstract Class.
 *
 *	Parent class to all database table derived objects
 *	@author Andrew Eddie <eddieajau@users.sourceforge.net>
 *	@abstract
 */
class CDpObject {
/**
 *	@var string Name of the table in the db schema relating to child class
 */
	var $_tbl = '';
/**
 *	@var string Name of the primary key field in the table
 */
	var $_tbl_key = '';
/**
 *	@var string Error message
 */
	var $_error = '';

/**
 * @var object Query Handler
 */
 	var $_query;
 	
 	var $_kg = null;

/**
 *	Object constructor to set table and key field
 *
 *	Can be overloaded/supplemented by the child class
 *	@param string $table name of the table in the db schema relating to child class
 *	@param string $key name of the primary key field in the table
 */
	function CDpObject( $table, $key, $kg=null ) {
		global $dPconfig;
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_kg = $kg;
		if (isset($dPconfig['dbprefix']))
			$this->_prefix = $dPconfig['dbprefix'];
		else
			$this->_prefix = '';
		$this->_query =& new DBQuery;
	}
/**
 *	@return string Returns the error message
 */
	function getError() {
		return $this->_error;
	}
/**
 *	Binds a named array/hash to this object
 *
 *	can be overloaded/supplemented by the child class
 *	@param array $hash named array
 *	@return null|string	null is operation was satisfactory, otherwise returns an error
 */
	function bind( $hash ) {
		if (!is_array( $hash )) {
			$this->_error = get_class( $this )."::bind failed.";
			return false;
		} else {
			bindHashToObject( $hash, $this );
			return true;
		}
	}

/**
 *	Binds an array/hash to this object
 *	@param int $oid optional argument, if not specifed then the value of current key is used
 *	@return any result from the database operation
 */
	function load( $oid=null , $strip = true) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		$this->_query->addWhere("$this->_tbl_key = $oid");
		$sql = $this->_query->prepare();
		$this->_query->clear();
		return db_loadObject( $sql, $this, false, $strip );
	}

/**
 *	Returns an array, keyed by the key field, of all elements that meet
 *	the where clause provided. Ordered by $order key.
 */
	function loadAll($order = null, $where = null) {
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		if ($order)
		  $this->_query->addOrder($order);
		if ($where)
		  $this->_query->addWhere($where);
		$sql = $this->_query->prepare();
		$this->_query->clear();
		return db_loadHashList($sql, $this->_tbl_key);
	}

/**
 *	Return a DBQuery object seeded with the table name.
 *	@param string $alias optional alias for table queries.
 *	@return DBQuery object
 */
	function &getQuery($alias = null) {
		$this->_query->clear();
		$this->_query->addTable($this->_tbl, $alias);
		return $this->_query;
	}

/**
 *	Generic check method
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null if the object is ok
 */
	function check() {
		return NULL;
	}
	
/**
*	Clone the current record
*
*	@author	handco <handco@users.sourceforge.net>
*	@return	object	The new record object or null if error
**/
	function duplicate() {
		$_key = $this->_tbl_key;
		
		$newObj = $this;
		// blanking the primary key to ensure that's a new record
		$newObj->$_key = '';
		
		return $newObj;
	}


/**
 *	Inserts a new row if id is zero or updates an existing row in the database table
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function store( $updateNulls = false ) {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		$k = $this->_tbl_key;
		if( $this->$k ) {
                        addHistory($this->_tbl . '_update(' . $this->$k . ')', 0, $this->_tbl);
			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
                        addHistory($this->_tbl . '_add(' . $this->$k . ')', 0, $this->_tbl);
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

/**
 *	Generic check for whether dependencies exist for this object in the db schema
 *
 *	Can be overloaded/supplemented by the child class
 *	@param string $msg Error message returned
 *	@param int Optional key index
 *	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
 *	@return true|false
 */
//---------------------------------------------------------------------------------------------
// 								RESEARCH METHOD				?
//								TEST						V
//								UPDATE						V
//---------------------------------------------------------------------------------------------
	function canDelete( &$msg, $oid=null, $joins=null, $aro_gr = null ) {
		global $AppUI;

		// First things first.  Are we allowed to delete?
		$acl =& $AppUI->acl();
		if ( ! $acl->checkModule($this->_tbl, "delete",null, $aro_gr, 1)) {//c'era item
		  $msg = $AppUI->_( "noDeletePermission" );
		  return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = "$k";
			$join = "";
			
			$q  = new DBQuery;
			$q->addTable($this->_tbl);
			$q->addWhere("$k = '".$this->$k."'");
			$q->addGroup($k);
			foreach( $joins as $table ) {
				$q->addQuery("COUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}");
				$q->addJoin($table['name'], $table['name'], "{$table['joinfield']} = $k");
			}
			$sql = $q->prepare();
			$q->clear();

			$obj = null;
			if (!db_loadObject( $sql, $obj )) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$msg = $AppUI->_( "noDeleteRecord" ) . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

/**
 *	Default delete method
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
//---------------------------------------------------------------------------------------------
// 								RESEARCH METHOD				?
//								TEST						V
//								UPDATE						V
//---------------------------------------------------------------------------------------------
	function delete( $oid=null, $aro_gr=null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (!$this->canDelete( $msg,null,null,$aro_gr )) {
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
		return $result;
	}

/**
 *	Get specifically denied records from a table/module based on a user
 *	@param int User id number
 *	@return array
 */
	function getDeniedRecords( $uid ) {
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getDeniedRecords failed, user id = 0" );

		$perms =& $GLOBALS['AppUI']->acl();
		return $perms->getDeniedItems($this->_tbl, $uid);
	}

/**
 *	Returns a list of records exposed to the user
 *	@param int User id number
 *	@param string Optional fields to be returned by the query, default is all
 *	@param string Optional sort order for the query
 *	@param string Optional name of field to index the returned array
 *	@param array Optional array of additional sql parameters (from and where supported)
 *	@return array
 */
// returns a list of records exposed to the user

//---------------------------------------------------------------------------------------------
// 								RESEARCH METHOD				?
//								TEST						V
//								UPDATE						V
//---------------------------------------------------------------------------------------------
	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) {
		$perms =& $GLOBALS['AppUI']->acl();
		$ugroups =& $GLOBALS['AppUI']->user_groups;
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
		
		$this->_query->clear();
		$this->_query->addQuery($fields);
		$this->_query->addTable($this->_tbl);

		if (@$extra['from']) {
			$this->_query->addTable($extra['from']);
		}
		
		if (isset($extra['where'])) {
		  $this->_query->addWhere($extra['where']);
		}

		//print_r($ugroups);
	    //$s=null;
	    // Check for system administrator
	    if (isset($ugroups[-1])) 
	    	if ($perms->checkModule($this->_tbl, "view", '', $ugroups[-1] )) {
	    		if ($orderby)
		    		$this->_query->addOrder($orderby);
		    	/*echo $this->_tbl;
		    	print_r ($ugroups);*/
		    	return $this->_query->loadHashList();
	    	}
	    
		foreach ($ugroups as $g => $sc) {
			if ($g > 0) // Non si considera qui il group all
				if ($perms->checkModule($this->_tbl, "view", '', $sc ))
					$s .= ($g.", ");
		}
		if (!is_null($s)) 
			$this->_query->addWhere($this->_kg.' IN ('.$s.'null)');
		else 
			return array();
		//echo "<br>";
		
		if ($orderby)
		  $this->_query->addOrder($orderby);
		
		//echo($this->_query->prepare());
		  
		return $this->_query->loadHashList();
	}

/**
 *	Check the consistency table of user_setcap and setcap of PHPGACL
 *	@param int $g group_id
 *	@param int $ui user_id (if null consistency is extended to all member's group)
  *	@param int $sc set capabilities to don' control
  *	@return boolean
 */
	static function checkSetCapConsistency($g, $ui=null, $sc=null) {
		$perms = new dPacl;
		
		$q  = new DBQuery;
		$q->addTable('user_setcap', 'us');
		$q->addQuery('us.setcap_id, user_id');
		if(!is_null($ui))
			$q->addWhere('us.group_id = '.$g.' && us.user_id ='.$ui);
		else 
			$q->addWhere('us.group_id = '.$g);
		$ar_scow = $q->loadHashList();//print_r($ar_scow);
		$q->clear();
		// formato [setcap_id => user_id]
		
		if ($ar_scow[$sc] == $ui)
			unset($ar_scow[$sc]);
			
		if (!is_null($ar_scow)) {
			foreach ($ar_scow as $scow => $u) {
				
				$q  = new DBQuery;
				$q->addTable('user_setcap', 'us');
				$q->addQuery('count(*)');
				$q->addWhere('us.setcap_id = '.$scow.' && us.user_id ='.$u);
				$now = $q->loadResult();
				$q->clear();
				
				if (($scow <> 0) && ($now == 1)) // non posso sovascrivere un sc se è definita su un altro gruppo
					if (!$perms->deleteUserRole($scow, $u)) {
						$AppUI->setMsg( "failed to delete sets of capabilities", UI_MSG_ERROR );
						$AppUI->redirect();
						return false;
					}
			}
		}
		return true;
	}
}
?>
