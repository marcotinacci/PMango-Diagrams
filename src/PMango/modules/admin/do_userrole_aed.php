<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store user sets of capabilities

 File:       do_userrole_aed.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango access control policy.
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
$AppUI->setMsg('Sets of capabilities' );
$ui=$_REQUEST['user_id'];

if ($del) {
	$ri=$_REQUEST['role_id'];
	if ($perms->deleteUserRole($ri, $_REQUEST['user_id'])) {
		$sql="UPDATE user_setcap SET setcap_id = 0 WHERE user_id = $ui && setcap_id = $ri";
		db_exec( $sql );
		if(!CUser::delUserProjects($ui)) {
			$AppUI->setMsg( "failed to delete projects to user", UI_MSG_ERROR );
			$AppUI->redirect();
		}
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "failed to delete sets of capabilities", UI_MSG_ERROR );
		$AppUI->redirect();
	}
	return;
}

if (! is_array($_REQUEST['group'])) {
	$AppUI->setMsg( "failed to add role, you must select a group", UI_MSG_ERROR );
	$AppUI->redirect();
} 
else {
	$add=false;
	foreach($_REQUEST['group'] as $g) {
		$oldMember = $perms->checkModule('projects', 'res', $ui, intval($g), 1);
		if (!$oldMember) $oldMember=0;
		$sc=$_REQUEST['user_role'.$g];
		if (isset($sc) && $_REQUEST['user_role'].$g) {
			if ($perms->insertUserRole($sc, $ui)) {
				if(CDpObject::checkSetCapConsistency($g,$ui,$sc)) {
					$add=true;
					if ($g > 0)
						$sql="UPDATE user_setcap SET setcap_id = $sc WHERE user_id = $ui && group_id = $g";
					else 
						$sql="REPLACE INTO user_setcap (user_id, group_id, setcap_id) VALUES ($ui, $g, $sc)";
					db_exec( $sql );	
					$newMember = $perms->checkModule('projects', 'res', $ui, intval($g), 1);	
					if (!$newMember) $newMember=0;
					//echo "om ".$oldMember." nm ".$newMember;
					if (!CUser::updateUserProjects($ui, $g, $oldMember, $newMember)) {
						$AppUI->setMsg( "failed to add projects to user", UI_MSG_ERROR );
						$AppUI->redirect();
					} 			
				}
			} else {
				$AppUI->setMsg( "failed to add sets of capabilities", UI_MSG_ERROR );
				$AppUI->redirect();
				break;
			}
		}
	}
	if ($add) {
		$AppUI->setMsg("added", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
}
?>