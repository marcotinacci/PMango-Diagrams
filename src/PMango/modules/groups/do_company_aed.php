<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store group information

 File:       do_company_aed.php
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

$del = dPgetParam( $_POST, 'del', 0 );
$obj = new CGroup();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

require_once("./classes/CustomFields.class.php");

$members = $_POST['group_members'];
$setcap = $_POST['group_setcap'];

if (isset($AppUI->user_groups[-1])) 
	$aro_gr=$AppUI->user_groups[-1];
else 
	$aro_gr=$AppUI->user_groups[$obj->group_id];
// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Group' );
if ($del) {
	if (!$obj->canDelete( $msg,null,$aro_gr)) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete(null,$aro_gr))) {
		//da eliminare anche in setcp
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		$AppUI->redirect( '', -1 );
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		if(!is_null($members)) {
			if(@$_POST['group_id']) 
				$obj->updateAssignedMembers($members,true);
			else 
		 		$obj->addAssignedMembers($members);
		}
		if(!is_null($setcap)) {
			if(@$_POST['group_id']) 
				$obj->updateAssignedSetcap($setcap);
			else 
		 		$obj->addAssignedSetcap($setcap);
		}
 		$custom_fields = New CustomFields( $m, 'addedit', $obj->group_id, "edit" );
 		$custom_fields->bind( $_POST );
 		$sql = $custom_fields->store( $obj->group_id ); // Store Custom Fields
 		
 		
		$AppUI->setMsg( @$_POST['group_id'] ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>
