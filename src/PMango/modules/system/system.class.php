<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      system administration functions.

 File:       system.class.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango system administration functions.
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
* Preferences class
*/
class CPreferences {
	var $pref_user = NULL;
	var $pref_name = NULL;
	var $pref_value = NULL;

	function CPreferences() {
		// empty constructor
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return "CPreferences::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return "CPreference::store-check failed<br />$msg";
		}
		if (($msg = $this->delete())) {
			return "CPreference::store-delete failed<br />$msg";
		}
		if (!($ret = db_insertObject( 'user_preferences', $this, 'pref_user' ))) {
			return "CPreference::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM user_preferences WHERE pref_user = $this->pref_user AND pref_name = '$this->pref_name'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}

/**
* Module class
*/
class CModule extends CDpObject {
	var $mod_id=null;
	var $mod_name=null;
	var $mod_directory=null;
	var $mod_version=null;
	var $mod_setup_class=null;
	var $mod_type=null;
	var $mod_active=null;
	var $mod_ui_name=null;
	var $mod_ui_icon=null;
	var $mod_ui_order=null;
	var $mod_ui_active=null;
	var $mod_description=null;
	var $permissions_item_label=null;
	var $permissions_item_field=null;
	var $permissions_item_table=null;

	function CModule() {
		$this->CDpObject( 'modules', 'mod_id' );
	}

	function install() {
		$sql = "SELECT mod_directory FROM modules WHERE mod_directory = '$this->mod_directory'";
		if (db_loadHash( $sql, $temp )) {
			// the module is already installed
			// TODO: check for older version - upgrade
			return false;
		}
                $sql = 'SELECT max(mod_ui_order)
                        FROM modules';
                $this->mod_ui_order = db_loadResult($sql) + 1;

		$perms =& $GLOBALS['AppUI']->acl();
		$perms->addModule($this->mod_directory, $this->mod_name);
		// Determine if it is an admin module or not, then add it to the correct set
		if (! isset($this->mod_admin))
			$this->mod_admin = 0;
		if ($this->mod_admin) {
			$perms->addGroupItem($this->mod_directory, "admin");
		} else {
			$perms->addGroupItem($this->mod_directory, "non_admin");
		}
		if (isset($this->permissions_item_table) && $this->permissions_item_table)
		  $perms->addModuleSection($this->permissions_item_table);
		$this->store();
		return true;
	}

	function remove() {
		$sql = "DELETE FROM modules WHERE mod_id = $this->mod_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			$perms =& $GLOBALS['AppUI']->acl();
			if (! isset($this->mod_admin))
				$this->mod_admin = 0;
			if ($this->mod_admin) {
				$perms->deleteGroupItem($this->mod_directory, "admin");
			} else {
				$perms->deleteGroupItem($this->mod_directory, "non_admin");
			}
			$perms->deleteModule($this->mod_directory);
			if (isset($this->permissions_item_table) && $this->permissions_item_table)
			  $perms->deleteModuleSection($this->permissions_item_table);
			return NULL;
		}
	}

	function move( $dirn ) {
		$temp = $this->mod_ui_order;
		if ($dirn == 'moveup') {
			$temp--;
			$sql = "UPDATE modules SET mod_ui_order = (mod_ui_order+1) WHERE mod_ui_order = $temp";
			db_exec( $sql );
		} else if ($dirn == 'movedn') {
			$temp++;
			$sql = "UPDATE modules SET mod_ui_order = (mod_ui_order-1) WHERE mod_ui_order = $temp";
			db_exec( $sql );
		}
		$sql = "UPDATE modules SET mod_ui_order = $temp WHERE mod_id = $this->mod_id";
		db_exec( $sql );

		$this->mod_id = $temp;
	}
// overridable functions
	function moduleInstall() {
		return null;
	}
	function moduleRemove() {
		return null;
	}
	function moduleUpgrade() {
		return null;
	}
}

/**
* Configuration class
*/
class CConfig extends CDpObject {

	function CConfig() {
		$this->CDpObject( 'config', 'config_id' );
	}

	function getChildren($id) {
		$this->_query->clear();
		$this->_query->addTable('config_list');
		$this->_query->addOrder('config_list_id');
		$this->_query->addWhere('config_id = ' . $id);
		$sql = $this->_query->prepare();
		$this->_query->clear();
		return db_loadHashList($sql, 'config_list_id');
	}

}


class prole {
        var $proles_id=NULL;
        var $proles_name;
        var $proles_description;
        var $proles_hour_cost;
        var $proles_status;

        function bcode() {
        }

        function bind( $hash ) {
                if (!is_array($hash)) {
                        return "Project roles::bind failed";
                } else {
                        bindHashToObject( $hash, $this );
                        return NULL;
                }
        }

        function delete() {
            $sql = "UPDATE project_roles SET proles_status=1 WHERE proles_id='".$this->proles_id."'";
            if (!db_exec( $sql )) {
                        return db_error();
            } else {
                        return NULL;
            }
                
        }

        function store() {
            if (!($ret = db_insertObject ( 'project_roles', $this, 'proles_id' ))) {
                    return "Project Roles::store failed. " . db_error();
            } else {
                    return NULL;
            }
        }
}
