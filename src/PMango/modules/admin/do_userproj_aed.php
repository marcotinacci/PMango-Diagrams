<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store user projects information

 File:       do_userproj_aed.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to store user projects.
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

$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : FALSE;

$perms =& $AppUI->acl();

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Project member' );
$ui=$_REQUEST['user_id'];

if ($del) {
	$pi=$_REQUEST['project_id'];
	$sql = "DELETE FROM user_projects WHERE user_id = $ui && project_id = $pi ";
	if (db_exec( $sql )) {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "failed to remove user from the project", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	return;
}

if (! is_array($_REQUEST['project'])) {
	$AppUI->setMsg( "failed to add member project, you must select a project", UI_MSG_ERROR );
	$AppUI->redirect();
} 
else {
	$add=false;
	foreach($_REQUEST['project'] as $p) {
		$role=$_REQUEST['user_role'.$p];
		$sql = "INSERT INTO user_projects (user_id, proles_id, project_id) VALUES ($ui, $role, $p)";
		if (isset($role)) {
			if (db_exec( $sql )) {
				$add=true;
			}
			else {
				$AppUI->setMsg( "failed to add member project", UI_MSG_ERROR );
				$AppUI->redirect();
				break;
			}
		} else {
			$AppUI->setMsg( "failed to add member project", UI_MSG_ERROR );
			$AppUI->redirect();
			break;
		}
	}
	if ($add) {
		$AppUI->setMsg("added", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
}
?>