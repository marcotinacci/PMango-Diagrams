<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store direct task assignment.

 File:       do_task_assign_aed.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango task. It has to be change to support $dPconfig['direct_edit_assignment']
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

$del = isset($_POST['del']) ? $_POST['del'] : 0;
$rm = isset($_POST['rm']) ? $_POST['rm'] : 0;
$hassign = @$_POST['hassign'];
$htasks = @$_POST['htasks'];
$store = dPgetParam($_POST, 'store', 0);
$chUTP = dPgetParam($_POST, 'chUTP', 0);
$percentage_assignment = @$_POST['percentage_assignment'];
$user_id = @$_POST['user_id'];

// prepare the percentage of assignment per user as required by CTask::updateAssigned()
$hperc_assign_ar = array();
if (isset($hassign)){
        $tarr = explode( ",", $hassign );
        foreach ($tarr as $uid) {
                if (intval( $uid ) > 0) {
                  $hperc_assign_ar[$uid] = $percentage_assignment;
                }
        }
}

// prepare a list of tasks to process
$htasks_ar = array();
if (isset($htasks)){
        $tarr = explode( ",", $htasks );
        foreach ($tarr as $tid) {
                if (intval( $tid ) > 0) {
                  $htasks_ar[] = $tid;
                }
        }
}

$sizeof = sizeof($htasks_ar);
for( $i=0; $i <= $sizeof; $i++) {


        $_POST['task_id'] = $htasks_ar[$i];

        // verify that task_id is not NULL
        if ($_POST['task_id'] > 0) {
                $obj = new CTask();


                if (!$obj->bind( $_POST )) {
                        $AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
                        $AppUI->redirect();
                }

                if ($rm && $del){
                        $overAssignment = $obj->updateAssigned( $res_ar, true, true);
                        if ($overAssignment) {
                                $AppUI->setMsg( "Some Users could not be unassigned from Task", UI_MSG_ERROR );
                        } else {
                                $AppUI->setMsg( "User(s) unassigned from Task", UI_MSG_OK);
                                $AppUI->redirect();
                        }
                } else if ( ($rm || $del)  ) {
                        if (($msg = $obj->removeAssigned($user_id))) {
                                $AppUI->setMsg( $msg, UI_MSG_ERROR );

                        } else {
                                $AppUI->setMsg( "User unassigned from Task", UI_MSG_OK);
                        }
                }
                if (isset($hassign) && ! $del == 1) {
                        $overAssignment = $obj->updateAssigned( $res_ar, false, false);
                        //check if OverAssignment occured, database has not been updated in this case
                        if ($overAssignment) {
                                $AppUI->setMsg( "The following Users have not been assigned in order to prevent from Over-Assignment:", UI_MSG_ERROR );
                                $AppUI->setMsg( "<br>".$overAssignment, UI_MSG_ERROR, true );
                        } else {
                                $AppUI->setMsg( "User(s) assigned to Task", UI_MSG_OK);
                        }

                }
		// process the user specific task priority
		if ($chUTP == 1) {
			$obj->updateUserSpecificTaskPriority($user_task_priority, $user_id);
			 $AppUI->setMsg( "User specific Task Priority updated", UI_MSG_OK, true);
		}

                if ($store == 1) {
                        if (($msg = $obj->store())) {
                                $AppUI->setMsg( $msg, UI_MSG_ERROR, true );

                        } else {
                                $AppUI->setMsg( "Task(s) updated", UI_MSG_OK, true);
                        }

                }
        }
}
$AppUI->redirect();
?>
