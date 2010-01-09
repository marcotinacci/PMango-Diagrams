<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store system configuration information.

 File:       do_systemconfig_aed.php
 Location:   pmango\modules\system
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
$obj = new CConfig();

// set all checkboxes to false
// overwrite the true/enabled/checked checkboxes later
$sql = "UPDATE config SET config_value='false' WHERE config_type='checkbox'";
$rs = db_loadResult($sql);

foreach ($_POST['dPcfg'] as $name => $value) {
	$obj->config_name = $name;
	$obj->config_value = $value;

	// grab the appropriate id for the object in order to ensure
	// that the db is updated well (config_name must be unique)
	$obj->config_id = $_POST['dPcfgId'][$name];

	// prepare (and translate) the module name ready for the suffix
	$AppUI->setMsg( 'System Configuration' );
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "updated", UI_MSG_OK, true );
	}
}
$AppUI->redirect();
?>
