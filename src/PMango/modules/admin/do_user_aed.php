<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store user information

 File:       do_user_aed.php
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
//include $AppUI->getModuleClass('contacts');

$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : FALSE;

$obj = new CUser();
//$contact = new CContact();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

        
//echo $_POST['group_members'];
// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'User' );

// !User's contact information not deleted - left for history.
if ($del) {
	if (($msg = $obj->delete(null,$AppUI->user_groups[-1]))) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect("m=admin");
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( "m=admin", -1 );
	}
	return;
}
	$isNewUser = !($_REQUEST['user_id']);
	if ( $isNewUser ) {
		// check if a user with the param Username already exists
		$userEx = FALSE;

		function userExistence( $userName ) {
			global $obj, $userEx;
			if ( $userName == $obj->user_username ) {
				$userEx = TRUE;
			}
		}

		//pull a list of existing usernames
		$sql = "SELECT user_username FROM users";
		$q  = new DBQuery;
		$q->addTable('users','u');
		$q->addQuery('user_username');
		$users = $q->loadList();

		// Iterate the above userNameExistenceCheck for each user
		foreach ( $users as $usrs ) {
			$usrLst = array_map( "userExistence", $usrs );
		}
		// If userName already exists quit with error and do nothing
		if ( $userEx == TRUE ) {
			$AppUI->setMsg( "already exists. Try another username.", UI_MSG_ERROR, true );
			$AppUI->redirect( );
		}
	}

    if ($msg = $obj->store()) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNewUser ? $obj->addAssignedGroups($_POST['group_members']) : $obj->updateAssignedGroups($_POST['group_members']);
		$AppUI->setMsg( $isNewUser ? 'added - please setup roles and permissions now.  User must have at least one role to log in.' : 'updated', UI_MSG_OK, true );
	}
	($isNewUser) ? $AppUI->redirect("m=admin&a=viewuser&user_id=". $obj->user_id . "&tab=1") : $AppUI->redirect();

?>