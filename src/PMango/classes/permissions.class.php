<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      permissions class

 File:       permissions.class.php
 Location:   pmango/classes
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       class

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   Second version, modified to create new control access policy.
 - 2006.07.18 Lorenzo
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

// Set the ADODB directory
if (! defined('ADODB_DIR')) {
  define('ADODB_DIR', "$baseDir/lib/adodb");
}
 
// Include the PHPGACL library
require_once "$baseDir/lib/phpgacl/gacl.class.php";
require_once "$baseDir/lib/phpgacl/gacl_api.class.php";
// Include the db_connections 

// Now extend the class
/**
 * Extend the gacl_api class.  There is an argument to separate this
 * into a gacl and gacl_api class on the premise that normal activity
 * only needs the functions in gacl, but it would appear that this is
 * not so for dP, which tends to require reverse lookups rather than
 * just forward ones (i.e. looking up who is allowed to do x, rather
 * than is x allowed to do y).
 */
class dPacl extends gacl_api {

  function dPacl($opts = null) {
    global $dPconfig;
    if (! is_array($opts))
      $opts = array();
    $opts['db_type'] = $dPconfig['dbtype'];
    $opts['db_host'] = $dPconfig['dbhost'];
    $opts['db_user'] = $dPconfig['dbuser'];
    $opts['db_password'] = $dPconfig['dbpass'];
    $opts['db_name'] = $dPconfig['dbname'];
    // We can add an ADODB instance instead of the database
    // connection details.  This might be worth looking at in
    // the future.
    if ($dPconfig['debug'] > 10)
      $this->_debug = true;
    parent::gacl_api($opts);
  }

  function checkLogin($login) {
    // Simple ARO<->ACO check, no AXO's required.
    return $this->acl_check("system", "login", "user", $login);
  }

  function checkModule($module, $op, $userid = null, $aro_groups=null, $check=null) {
    if (! $userid)
      $userid = $GLOBALS['AppUI']->user_id;
    
    if (is_int($aro_groups)) {//non � specificato il value ma il group_id
    	$q  = new DBQuery;
		$q->addTable('user_setcap', 'us');
		$q->addQuery('ga.value');//manca da inserire i groups e i set cap
		$q->addJoin('gacl_aro_groups', 'ga', 'ga.id = us.setcap_id');
		$q->addWhere("us.setcap_id <> 0 && us.user_id = $userid && us.group_id = $aro_groups");
		$aro_groups = "'".$q->loadResult()."'";// c'� inserito anche ALL GROUPS
		//echo $module." ".$op. " " .$userid." ".$aro_groups."<br>";
    }
    
    if ($check) 
      if ($aro_groups==null)
      	return 0;
	if ($check == 22) echo $aro_groups."<br>";
	
    $result = $this->acl_check("application", $op, "user", $userid, "app", $module, $aro_groups, null);

    //if (!is_null($aro_groups)) 
    	//echo "<br>Aro: $aro_groups Result: $result";
   
    dprint(__FILE__, __LINE__, 2, "checkModule( $module, $op, $userid, $aro_groups) returned $result");
    return $result;
  }

	// It seems don't funtction with the new permissions policy (Modified with $aro_gr)
  function checkModuleItem($module, $op, $item = null, $userid = null, $aro_gr = null) {
    if (! $userid)
      $userid = $GLOBALS['AppUI']->user_id;
    if (! $item) {//echo("NO HISTORY ITEM");
      return $this->checkModule($module, $op, $userid, $aro_gr);
    }//echo("11");
    $result = $this->acl_query("application", $op, "user", $userid, $module, $item, $aro_gr);//echo "--".$result;
    // If there is no acl_id then we default back to the parent lookup
    if (! $result || ! $result['acl_id']) {//print_r($result);
      dprint(__FILE__, __LINE__, 2, "checkModuleItem($module, $op, $userid, $aro_gr) did not return a record");
      return $this->checkModule($module, $op, $userid, $aro_gr);
    }//echo("33");
    dprint(__FILE__, __LINE__, 2, "checkModuleItem($module, $op, $userid, $aro_gr) returned $result[allow]");
    return $result['allow'];
  }

  /**
   * This gets tricky and is there mainly for the compatibility layer
   * for getDeny functions.
   * If we get an ACL ID, and we get allow = false, then the item is
   * actively denied.  Any other combination is a soft-deny (i.e. not
   * strictly allowed, but not actively denied.
   */
  function checkModuleItemDenied($module, $op, $item, $user_id = null) {
    if (! $user_id) {
      $user_id = $GLOBALS['AppUI']->user_id;
    }
    $result = $this->acl_query("application", $op, "user", $user_id, $module, $item);
    if ( $result && $result['acl_id'] && ! $result['allow'])
      return true;
    else
      return false;
  }

  function addLogin($login, $username) {
    $res = $this->add_object("user", $username, $login, 1, 0, "aro");
    if (! $res)
      dprint(__FILE__, __LINE__, 0, "Failed to add user permission object");
    return $res;
  }

  function updateLogin($login, $username) {
    $id = $this->get_object_id("user", $login, "aro");
    if (! $id)
      return $this->addLogin($login, $username);
    // Check if the details have changed.
    list ($osec, $val, $oord, $oname, $ohid) = $this->get_object_data($id, "aro");
    if ($oname != $username) {
      $res = $this->edit_object( $id, "user", $username, $login, 1, 0, "aro");
      if (! $res)
	dprint(__FILE__, __LINE__, 0, "Failed to change user permission object");
    }
    return $res;
  }

  function deleteLogin($login) {
    $id = $this->get_object_id("user", $login, "aro");
    if ($id) {
      $id = $this->del_object($id, "aro", true);
    }
    if (! $id)
      dprint(__FILE__, __LINE__, 0, "Failed to remove user permission object");
    return $id;
  }

  function addModule($mod, $modname) {
    $res = $this->add_object("app", $modname, $mod, 1, 0, "axo");
    if ($res) {
       $res = $this->addGroupItem($mod);
    }
    if (! $res) {
      dprint(__FILE__, __LINE__, 0, "Failed to add module permission object");
    }
    return $res;
  }

  function addModuleSection($mod) {
    $res = $this->add_object_section(ucfirst($mod) . " Record", $mod, 0, 0, "axo");
    if (! $res) {
      dprint(__FILE__, __LINE__, 0, "Failed to add module permission section");
    }
    return $res;
  }

  function addModuleItem($mod, $itemid, $itemdesc) {
    $res = $this->add_object($mod, $itemdesc, $itemid, 0, 0, "axo");
    return $res;
  }

  function addGroupItem($item, $group = "all", $section = "app", $type = "axo") {
    if ($gid = $this->get_group_id($group, null, $type)) {
      return $this->add_group_object($gid, $section, $item, $type);
    }
    return false;
  }

  function deleteModule($mod) {
    $id = $this->get_object_id("app", $mod, "axo");
    if ($id) {
      $this->deleteGroupItem($mod);
      $id = $this->del_object($id, "axo", true);
    }
    if (! $id)
      dprint(__FILE__, __LINE__, 0, "Failed to remove module permission object");
    return $id;
  }

  function deleteModuleSection($mod) {
    $id = $this->get_object_section_section_id(null, $mod, "axo");
    if ($id) {
      $id = $this->del_object_section($id, "axo", true);
    }
    if (! $id)
      dprint(__FILE__, __LINE__, 0, "Failed to remove module permission section");
    return $id;
  }
  
  function deleteGroupItem($item, $group = "all", $section = "app", $type = "axo") {
    if ($gid = $this->get_group_id($group, null, $type)) {
      return $this->del_group_object($gid, $section, $item, $type);
    }
    return false;
  }
//---------------------------------------------------------------------------------------------
// 								RESEARCH METHOD				V
//								TEST						V
//								UPDATE						X
//---------------------------------------------------------------------------------------------
  function isUserPermitted($userid, $module = null, $aro_gr = null) {
    if ($module) {
      return $this->checkModule($module, "view", $userid, $aro_gr);
    } else {
      return $this->checkLogin($userid);
    }
  }
/*---------------------------------------------------------------------------------------------
	RESEARCH METHOD			?
	TEST					V
	UPDATE					V
	DESCRIPTION:			La funzione restituisce tutti gli utenti se l'utente ha diritto di 
							vista sulla tabella degli utenti. Altrimenti solo se stesso.
-----------------------------------------------------------------------------------------------*/
  function getPermittedUsers($module = null, $aro_gr = null) {
    // Not as pretty as I'd like, but we can do it reasonably well.
    // Check to see if we are allowed to see other users.
    // If not we can only see ourselves.
    global $AppUI;
    $canViewUsers = $this->checkModule('users', 'view', null, $aro_gr);
    $q  = new DBQuery;
    $q->addTable('users','u');
    $q->addQuery('u.user_id, concat_ws(" ", user_last_name, user_first_name) as contact_name'); 
    $q->addOrder('user_last_name');
    $res = $q->exec();
    $userlist = array();
    while ($row = $q->fetchRow()) {
      if ($canViewUsers || $row['user_id'] == $AppUI->user_id)
		$userlist[$row['user_id']] = $row['contact_name'];
    }
		$q->clear();
    //  Now format the userlist as an assoc array.
    return $userlist;
  }

  function getItemACLs($module, $uid = null) {
    if (! $uid)
      $uid = $GLOBALS['AppUI']->user_id;
    // Grab a list of all acls that match the user/module, for which Deny permission is set.
    return $this->search_acl("application", "view", "user", $uid, false, $module, false, false, false);
  }

  function getUserACLs($uid = null) {
    if (! $uid)
      $uid = $GLOBALS['AppUI']->user_id;
    return $this->search_acl("application", false, "user", $uid, null, false, false, false, false);
  }

  function getRoleACLs($role_id) {
    $role = $this->getRole($role_id);
    return $this->search_acl("application", false, false, false, $role['name'], false, false, false, false);
  }

  function getRole($role_id) {
    $data = $this->get_group_data($role_id);
    if ($data) {
      return array('id' => $data[0],
      	'parent_id' => $data[1],
	'value' => $data[2],
	'name' => $data[3],
	'lft' => $data[4],
	'rgt' => $data[5]);
    } else {
      return false;
    }
  }

  function & getDeniedItems($module, $uid = null) {
    $items = array();
    if (! $uid)
      $uid = $GLOBALS['AppUI']->user_id;

    $acls = $this->getItemACLs($module, $uid);
    // If we get here we should have an array.
    if (is_array($acls)) {
      // Grab the item values
      foreach ($acls as $acl) {
	$acl_entry =& $this->get_acl($acl);
	if ($acl_entry['allow'] == false && $acl_entry['enabled'] == true && isset($acl_entry['axo'][$module]))
	  foreach ($acl_entry['axo'][$module] as $id) {
	  	$items[] = $id;
	  }
      }
    } else {
      dprint(__FILE__, __LINE__, 2, "getDeniedItems($module, $uid) - no ACL's match");
    }
    dprint(__FILE__,__LINE__, 2, "getDeniedItems($module, $uid) returning " . count($items) . " items");
    return $items;
  }

  // This is probably redundant.
  function & getAllowedItems($module, $uid = null) {
    $items = array();
    if (! $uid)
      $uid = $GLOBALS['AppUI']->user_id;
    $acls = $this->getItemACLs($module, $uid);
    if (is_array($acls)) {
      foreach ($acls as $acl) {
	$acl_entry =& $this->get_acl($acl);
	if ($acl_entry['allow'] == true && $acl_entry['enabled'] == true && isset($acl_entry['axo'][$module])) {
	  foreach ($acl_entry['axo'][$module] as $id) {
	    $items[] = $id;
	  }
	}
      }
    } else {
      dprint(__FILE__, __LINE__, 2, "getAllowedItems($module, $uid) - no ACL's match");
    }
    dprint(__FILE__,__LINE__, 2, "getAllowedItems($module, $uid) returning " . count($items) . " items");
    return $items;
  }

  // Copied from get_group_children in the parent class, this version returns
  // all of the fields, rather than just the group ids.  This makes it a bit
  // more efficient as it doesn't need the get_group_data call for each row.
  function getChildren($group_id, $group_type = 'ARO', $recurse = 'NO_RECURSE') {
	$this->debug_text("get_group_children(): Group_ID: $group_id Group Type: $group_type Recurse: $recurse");

	switch (strtolower(trim($group_type))) {
		case 'axo':
			$group_type = 'axo';
			$table = $this->_db_table_prefix .'axo_groups';
			break;
		default:
			$group_type = 'aro';
			$table = $this->_db_table_prefix .'aro_groups';
	}

	if (empty($group_id)) {
		$this->debug_text("get_group_children(): ID ($group_id) is empty, this is required");
		return FALSE;
	}

	$q = new DBQuery;
	$q->addTable($table, 'g1');
	$q->addQuery('g1.id, g1.name, g1.value, g1.parent_id');
	$q->addOrder('g1.value');
	
	//FIXME-mikeb: Why is group_id in quotes?
	switch (strtoupper($recurse)) {
		case 'RECURSE':
			$q->addJoin($table, 'g2', 'g2.lft<g1.lft AND g2.rgt>g1.rgt');
			$q->addWhere('g2.id='. $group_id);
			break;
		default:
			$q->addWhere('g1.parent_id='. $group_id);
	}
	
	$result = array();
	$q->exec();
	while ($row = $q->fetchRow()) {
		$result[] = array(
		 'id' => $row[0],
		 'name' => $row[1],
		 'value' => $row[2],
		 'parent_id' => $row[3]);
	}
	$q->clear();
	return $result;
  }

  function insertRole($value, $name) {
    $role_parent = $this->get_group_id("role");
    $value = str_replace(" ", "_", $value);
    return $this->add_group($value, $name, $role_parent);
  }

  function updateRole($id, $value, $name) {
    return $this->edit_group($id, $value, $name);
  }

  function deleteRole($id) {
    // Delete all of the group assignments before deleting group.
    $objs = $this->get_group_objects($id);
    foreach ($objs as $section => $value) {
      $this->del_group_object($id, $section, $value);
    }
    return $this->del_group($id, false);
  }

  function insertUserRole($role, $user) {
    // Check to see if the user ACL exists first.
    
    $id = $this->get_object_id("user", $user, "aro");
    if (! $id) {
      $q = new DBQuery;
      $q->addTable('users');
      $q->addQuery('user_username');
      $q->addWhere("user_id = $user");
      $rq = $q->exec();
      if (! $rq) {
				dprint(__FILE__, __LINE__, 0, "Cannot add role, user $user does not exist!<br>" . db_error() );
				$q->clear();
				return false;
      }
      $row = $q->fetchRow();
      if ($row) {
		$this->addLogin($user, $row['user_username']);
      }
	  $q->clear();
    }
	
    return $this->add_group_object($role, "user", $user);
  }
	
  function insertUserGroupRole($role, $user, $group) {
    // Check to see if the user ACL exists first.
    
    $id = $this->get_object_id("user", $user, "aro");
    if (! $id) {
      $q = new DBQuery;
      $q->addTable('users');
      $q->addQuery('user_username');
      $q->addWhere("user_id = $user");
      $rq = $q->exec();
      if (! $rq) {
		dprint(__FILE__, __LINE__, 0, "Cannot add role, user $user does not exist!<br>" . db_error() );
		$q->clear();
		return false;
      }
      $row = $q->fetchRow();
      if ($row) {
		$this->addLogin($user, $row['user_username']);
      }
	  $q->clear();
    }
    $perms =& new dPacl;
    $group_mods = $perms->add_group($group."all", $group."Modules", 10, "axo");//da cambiare il parent
    // devo aggiungerci ipermessi per il gruppo
    return $this->add_group_object($role, "user", $user);//ci devo mettere il gruppo
  }
  
  function deleteUserRole($role, $user) {
    return $this->del_group_object($role, "user", $user);
  }

  // Returns the group ids of all groups this user is mapped to.
  // Not provided in original phpGacl, but useful.
  function getUserRoles($user) {
    $id = $this->get_object_id("user", $user, "aro");
    $result = $this->get_group_map($id);
    if (! is_array($result))
      $result = array();
    return $result;
  }

  // Return a list of module groups and modules that a user can
  // be permitted access to.
  function getModuleList() {
    $result = array();
    // First grab all the module groups.
   	$parent_id = $this->get_group_id("mod", null, "axo");
    if (! $parent_id)
      dprint(__FILE__, __LINE__, 0, "failed to get parent for module groups");
    $groups = $this->getChildren($parent_id, "axo");
    if (is_array($groups)) {
      foreach ($groups as $group) {
	$result[] = array('id' => $group['id'], 'type' => 'grp', 'name' => $group['name'], 'value' => $group['value']);
      }
    } else {
      dprint(__FILE__, __LINE__, 1, "No groups available for $parent_id");
    }
    // Now the individual modules.
    $modlist = $this->get_objects_full("app", 0, "axo");
    if (is_array($modlist)) {
      foreach ($modlist as $mod) {
	$result[] = array('id' => $mod['id'], 'type' => 'mod', 'name' => $mod['name'], 'value' => $mod['value']);
      }
    }
    return $result;
  }

  // An assignable module is one where there is a module sub-group
  // Effectivly we just list those module in the section "modname"
  function getAssignableModules() {
    return $this->get_object_sections(null, 0, 'axo', "value not in ('sys', 'app')");
  }

  function getPermissionList() {
    $list = $this->get_objects_full("application", 0, "aco");
    // We only need the id and the name
    $result = array();
    if (! is_array($list))
      return $result;
    foreach ($list as $perm)
      $result[$perm['id']] = $perm['name'];
    return $result;
  }

  function get_group_map($id, $group_type = "ARO") {
	$this->debug_text("get_group_map(): Assigned ID: $id Group Type: $group_type");

	switch (strtolower(trim($group_type))) {
		case 'axo':
			$group_type = 'axo';
			$table = $this->_db_table_prefix .'axo_groups';
			$map_table = $this->_db_table_prefix . 'groups_axo_map';
			$map_field = "axo_id";
			break;
		default:
			$group_type = 'aro';
			$table = $this->_db_table_prefix .'aro_groups';
			$map_table = $this->_db_table_prefix . 'groups_aro_map';
			$map_field = "aro_id";
	}

	if (empty($id)) {
		$this->debug_text("get_group_map(): ID ($id) is empty, this is required");
		return FALSE;
	}

	$q = new DBQuery;
	$q->addTable($table, 'g1');
	$q->addTable( $map_table, 'g2');
	$q->addQuery('g1.id, g1.name, g1.value, g1.parent_id');
	$q->addWhere("g1.id = g2.group_id AND g2.$map_field = $id");
	$q->addOrder('g1.value');

	$result = array();
	$q->exec();
	while ($row = $q->fetchRow()) {
			$result[] = array(
			 'id' => $row[0],
			 'name' => $row[1],
			 'value' => $row[2],
			 'parent_id' => $row[3]);
	}
	$q->clear();
	return $result;

  }

/*======================================================================*\
		Function:	get_object()
	\*======================================================================*/
	function get_object_full($value = null , $section_value = null, $return_hidden=1, $object_type=NULL) {

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				break;
			case 'acl':
				$object_type = 'acl';
				$table = $this->_db_table_prefix .'acl';
				break;
			default:
				$this->debug_text('get_object(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("get_object(): Section Value: $section_value Object Type: $object_type");

		$q = new DBQuery;
		$q->addTable($table);
		$q->addQuery('id, section_value, name, value, order_value, hidden');
	
		if (!empty($value)) {
			$q->addWhere('value=' . $this->db->quote($value));

		}

		if (!empty($section_value)) {
			$q->addWhere('section_value='. $this->db->quote($section_value));

		}

		if ($return_hidden==0 AND $object_type != 'acl') {
			$q->addWhere('hidden=0');

		}


		$q->exec();
		$row = $q->fetchRow();
		$q->clear();

		if (!is_array($row)) {
			$this->debug_db('get_object');
			return false;
		}

		// Return Object info.
		return array(
		  'id' => $row[0],
		  'section_value' => $row[1],
		  'name' => $row[2],
		  'value' => $row[3],
		  'order_value' => $row[4],
		  'hidden' => $row[5]
		);
	}

	/*======================================================================*\
		Function:	get_objects ()
		Purpose:	Grabs all Objects in the database, or specific to a section_value
					returns format suitable for add_acl and is_conflicting_acl
	\*======================================================================*/
	function get_objects_full($section_value = NULL, $return_hidden = 1, $object_type = NULL, $limit_clause = NULL) {
		switch (strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				break;
			default:
				$this->debug_text('get_objects(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("get_objects(): Section Value: $section_value Object Type: $object_type");

		$q = new DBQuery;
		$q->addTable($table);
		$q->addQuery('id, section_value, name, value, order_value, hidden');

		if (!empty($section_value)) {
			$q->addWhere('section_value='. $this->db->quote($section_value));
		}

		if ($return_hidden==0) {
			$q->addWhere('hidden=0');
		}

		if (!empty($limit_clause)) {
			$q->addWhere($limit_clause);
		}

		$q->addOrder('order_value');

		/*
		$rs = $q->exec();

		if (!is_object($rs)) {
			$this->debug_db('get_objects');
			return FALSE;
		}
		*/

		$retarr = array();

		$q->exec();
		while ($row = $q->fetchRow()) {
			$retarr[] = array(
			  'id' => $row[0],
			  'section_value' => $row[1],
			  'name' => $row[2],
			  'value' => $row[3],
			  'order_value' => $row[4],
			  'hidden' => $row[5]
			);
		}
		$q->clear();

		// Return objects
		return $retarr;
	}

	function get_object_sections($section_value = NULL, $return_hidden = 1, $object_type = NULL, $limit_clause = NULL) {
		switch (strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco_sections';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro_sections';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo_sections';
				break;
			default:
				$this->debug_text('get_object_sections(): Invalid Object Type: '. $object_type);
				return FALSE;
		}

		$this->debug_text("get_objects(): Section Value: $section_value Object Type: $object_type");

		// $query = 'SELECT id, value, name, order_value, hidden FROM '. $table;
		$q = new DBQuery;
		$q->addTable($table);
		$q->addQuery('id, value, name, order_value, hidden');


		if (!empty($section_value)) {
			$q->addWhere('value='. $this->db->quote($section_value));

		}

		if ($return_hidden==0) {
			$q->addWhere('hidden=0');

		}

		if (!empty($limit_clause)) {
			$q->addWhere($limit_clause);

		}

		$q->addOrder('order_value');

		$rs = $q->exec();

		/*
		if (!is_object($rs)) {
			$this->debug_db('get_object_sections');
			return FALSE;
		}
		*/

		$retarr = array();

		while ($row = $q->fetchRow()) {
			$retarr[] = array(
			  'id' => $row[0],
			  'value' => $row[1],
			  'name' => $row[2],
			  'order_value' => $row[3],
			  'hidden' => $row[4]
			);
		}
		$q->clear();

		// Return objects
		return $retarr;
	}

  /** Called from do_perms_aed, allows us to add a new ACL */
  function addUserPermission() {
  	//print_r($_POST);
  	//print_r($_POST['permission_type']);
    // Need to have a user id, 
    // parse the permissions array
    if (! is_array($_POST['permission_type'])) {
      $this->debug_text("you must select at least one permission");
      return false;
    }
    /*
    echo "<pre>\n";
    var_dump($_POST);
    echo "</pre>\n";
    return true;
    */
	
    $mod_type = substr($_POST['permission_module'],0,4);
    $mod_id = substr($_POST['permission_module'],4);
    $mod_group = null;
    $mod_mod = null;
    if ($mod_type == 'grp,') {
      $mod_group = array($mod_id);
    } else {
      if (isset($_POST['permission_item']) && $_POST['permission_item']) {
	$mod_mod = array();
	$mod_mod[$_POST['permission_table']][] =  $_POST['permission_item'];
	// check if the item already exists, if not create it.
	// First need to check if the section exists.
	if (! $this->get_object_section_section_id(null, $_POST['permission_table'], 'axo')) {
	  $this->addModuleSection($_POST['permission_table']);
	}
	if (! $this->get_object_id($_POST['permission_table'], $_POST['permission_item'],  'axo')) {
	  $this->addModuleItem($_POST['permission_table'], $_POST['permission_item'], $_POST['permission_name']);
	}
      } else {
	// Get the module information
	$mod_info = $this->get_object_data($mod_id, 'axo');
	$mod_mod = array();
	$mod_mod[$mod_info[0][0]][] = $mod_info[0][1];
      }
    }
    $aro_info = $this->get_object_data($_POST['permission_user'], 'aro');
    $aro_map = array();
    $aro_map[$aro_info[0][0]][] = $aro_info[0][1];
    // Build the permissions info
    $type_map = array();
    foreach ($_POST['permission_type'] as $tid) {
      $type = $this->get_object_data($tid, 'aco');
      foreach ($type as $t) {
	$type_map[$t[0]][] = $t[1];
      }
    }
    return $this->add_acl(
      $type_map,
      $aro_map,
      null,
      $mod_mod,
      $mod_group,
      $_POST['permission_access'],
      1,
      null,
      null,
      "user");
  }

  function addRolePermission() {
    if (! is_array($_POST['permission_type'])) {
      $this->debug_text("you must select at least one permission");
      return false;
    }

    $mod_type = substr($_POST['permission_module'],0,4);
    $mod_id = substr($_POST['permission_module'],4);
    $mod_group = null;
    $mod_mod = null;
    if ($mod_type == 'grp,') {
      $mod_group = array($mod_id);
    } else {
      // Get the module information
      $mod_info = $this->get_object_data($mod_id, 'axo');//print_r( $mod_info);
      $mod_mod = array();
      $mod_mod[$mod_info[0][0]][] = $mod_info[0][1];
      //print_r ($mod_mod);
    }
    $aro_map = array($_POST['role_id']);
    // Build the permissions info
    $type_map = array();
    foreach ($_POST['permission_type'] as $tid) {
      $type = $this->get_object_data($tid, 'aco');
      foreach ($type as $t) {
	$type_map[$t[0]][] = $t[1];
      }
    }
    return $this->add_acl(
      $type_map,
      null,
      $aro_map,
      $mod_mod,
      $mod_group,
      $_POST['permission_access'],
      1,
      null,
      null,
      "user");
    if (! is_array($_POST['permission_type'])) {
      $this->debug_text("you must select at least one permission");
      return false;
    }
  }

  // Some function overrides.
  function debug_text($text) {
    $this->_debug_msg = $text;
    dprint(__FILE__, __LINE__, 9, $text);
  }

  function msg() {
    return $this->_debug_msg;
  }

}
?>
