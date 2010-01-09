<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      set of capabilities functions.

 File:       role.class.php
 Location:   pmango\modules\system\roles
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.
   
---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (C) 2003-2005 The dotProject Development Team.

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
 Foundation Inc., 51 Franklin St, 5th Floor, Boston, MA 02110-1301 USA.

---------------------------------------------------------------------------
*/

/**
 * This class abstracts the concept of a user Role, which is, in effect, an ARO
 * group in phpGACL speak.  phpGACL has a few constraints, e.g. having only a
 * single parent group, from which all other groups must be determined.  The
 * parent for Roles is 'role'.  You can create parent trees, however a role
 * cannot be its own parent.  For the first pass of this, we limit to a single
 * depth role structure.
 *
 * Once a Role is created, users can be assigned to one or more roles, by adding
 * their user ARO id to the group. All users are given an ARO id which is separate
 * from their user id, but maps it between the dP database and the phpGacl database.
 *
 * If a role is deleted, then all of the ACLs associated with the role must also
 * be deleted, and then the user id mappings.  Note that the user ARO is _never_
 * deleted, unless the user is.
 */
class CRole {
	var $role_id = NULL;
	var $role_name = NULL;
	var $role_description = NULL;
	var $perms = null;

	function CRole( $name='', $description='') {
		$this->role_name = $name;
		$this->role_description = $description;
		$this->perms =& $GLOBALS['AppUI']->acl();
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		// Not really much to check, just return OK for this iteration.
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		if( $this->role_id ) {
			$ret = $this->perms->updateRole($this->role_id, $this->role_name, $this->role_description);
		} else {
			$ret = $this->perms->insertRole($this->role_name, $this->role_description);
		}
		if( !$ret ) {
			return get_class( $this )."::store failed";
		} else {
			return NULL;
		}
	}

	function delete() {
		// Delete a role requires deleting all of the ACLs associated
		// with this role, and all of the group data for the role.
		// Si potrebbe togliere...
		if ($this->perms->checkModuleItem('roles', "delete", $this->role_id)) {
			// Delete all the children from this group
			$this->perms->deleteRole($this->role_id);
			return null;
		} else {
			return get_class( $this)."::delete failed <br/>You do not have permission to delete this Sets of Capabilities";
		}
	}

	function __sleep()
	{
		return array('role_id', 'role_name', 'role_description');
	}

	function __wakeup()
	{
		$this->perms =& $GLOBALS['AppUI']->acl();
	}

	/**
	 * Return a list of known roles.
	 */
	function getRoles() 
	{
		$role_parent = $this->perms->get_group_id("role");
		$roles = $this->perms->getChildren($role_parent);
		return $roles;
	}
	

	function rename_array(&$roles, $from, $to) {
		if ( count($from) != count($to) ) {
			return false;
		}
		foreach ($roles as $key => $val) {
			// 4.2 and before return NULL on fail, later returns false.
			if ( ($k = array_search($k, $from)) !== false && $k !== null ) {
				unset($roles[$key]);
				$roles[$to[$k]] = $val;
			}
		}
		return true;
	}
}
// vim:ai sw=8 ts=8:
?>
