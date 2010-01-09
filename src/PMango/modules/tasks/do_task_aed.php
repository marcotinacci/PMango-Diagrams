<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store task information.

 File:       do_task_aed.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango task.
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

function setItem($item_name, $defval = null) {
	if (isset($_POST[$item_name]))
		return $_POST[$item_name];
	return $defval;
}
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$task_id = setItem('task_id', 0);
$ass_res = setItem('ass_resources');
$hdependencies = setItem('hdependencies');
$sub_form = isset($_POST['sub_form']) ? $_POST['sub_form'] : 0;
if ($sub_form) {
	// in add-edit, so set it to what it should be
	$AppUI->setState('TaskAeTabIdx', $_POST['newTab']);
	if (isset($_POST['subform_processor'])) {
		$root = $dPconfig['root_dir'];
		if (isset($_POST['subform_module']))
			$mod = $AppUI->checkFileName($_POST['subform_module']);
		else
			$mod = 'tasks';
		$proc = $AppUI->checkFileName($_POST['subform_processor']);
		include "$root/modules/$mod/$proc.php";
	} 
} else {

	// Include any files for handling module-specific requirements
	foreach (findTabModules('tasks', 'addedit') as $mod) {
		$fname = dPgetConfig('root_dir') . "/modules/$mod/tasks_dosql.addedit.php";
		dprint(__FILE__, __LINE__, 3, "checking for $fname");
		if (file_exists($fname))
			require_once $fname;
	}

	$obj = new CTask();
	
	// If we have an array of pre_save functions, perform them in turn.
	/*if (isset($pre_save)) {
		foreach ($pre_save as $pre_save_function)
			$pre_save_function();
	} else {
		dprint(__FILE__, __LINE__, 1, "No pre_save functions.");
	}*/
	
	// Find the task if we are set
	if ($task_id)
		$obj->load($task_id);
	
	if ( isset($_POST)) {
		$obj->bind($_POST);
	}

	if (! $obj->task_creator)
		$obj->task_creator = $AppUI->user_id;

	if (!$obj->bind( $_POST )) {
		$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if ($obj->task_parent == "^") {
		$obj->task_parent = $obj->task_id;
	}
	// Make sure task milestone is set or reset as appropriate
	if (! isset($_POST['task_milestone']))
		$obj->task_milestone = false;

	//format ass_resources |ui,ri,mh,perc|ui,ri,mh,perc|..;
	$tmp_ar = explode("|", $ass_res);
	$res_ar = array();
	for ($i = 0; $i < sizeof($tmp_ar); $i++) {
		$tmp = explode(",", $tmp_ar[$i]);
		if (!is_null($tmp[0]) && !is_null($tmp[1]))
		if (count($tmp) > 1)
			$res_ar[] = array($tmp[0],$tmp[1],$tmp[2],$tmp[3]);
		else if (!$tmp[2] > 0)
			$res_ar[] = array($tmp[0],$tmp[1],0,$tmp[3]);
		else if (!$tmp[3] > 0)
			$res_ar[] = array($tmp[0],$tmp[1],$tmp[2],0);
	}
	// convert dates to SQL format first
	if ($obj->task_start_date) {
		$date = new CDate( $obj->task_start_date );
		$obj->task_start_date = $date->format( FMT_DATETIME_MYSQL );
	}
	if ($obj->task_finish_date) {
		$date = new CDate( $obj->task_finish_date );
		$obj->task_finish_date = $date->format( FMT_DATETIME_MYSQL );
	}

	if ($task_id > 0) {
		$sql = "";
		$model_type= setItem('model');
		$model_delivery_day = setItem('deliveryDay');
		if ($model_type == 1)
			$sql = "REPLACE INTO models (model_pt, model_association, model_type) VALUES ($task_id,2,$model_type)";
		if ($model_type == 2 && !is_null($model_delivery_day))
			$sql = "REPLACE INTO models (model_pt, model_association, model_type, model_delivery_day) VALUES ($task_id,2,$model_type,'$model_delivery_day')";
		if ($sql <> "")
			db_exec($sql);
	}
	
	require_once("./classes/CustomFields.class.php");
	//echo '<pre>';print_r( $hassign );echo '</pre>';die;
	// prepare (and translate) the module name ready for the suffix
	if ($del) {
		if (($msg = $obj->delete())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$AppUI->redirect();
		} else {
			$AppUI->setMsg( 'Task deleted', UI_MSG_ALERT);
			$AppUI->redirect( '', -1 );
		}
	} else {
		if (($msg = $obj->store($res_ar))) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$AppUI->redirect(); // Store failed don't continue?
		} else {
			$custom_fields = New CustomFields( $m, 'addedit', $obj->task_id, "edit" );
 			$custom_fields->bind( $_POST );
 			$sql = $custom_fields->store( $obj->task_id ); // Store Custom Fields

			$AppUI->setMsg( $task_id ? 'Task updated' : 'Task added', UI_MSG_OK);
		}
		$op=(setItem('oldParent', 0)=="") ? 0 : $_POST['oldParent'];
		$ow=(setItem('oldWBSi', 0)=="") ? 0 : $_POST['oldWBSi'];
		if ($obj->task_parent == 0) {
			$obj->task_parent = $obj->task_id;
		}
		$obj->updateWBS($op,$ow);
		if ($task_id > 0) {
			$obj->updateAssigned( $res_ar);
		}
		
		if (isset($hdependencies)) {
			$obj->updateDependencies( $hdependencies );
		}
		
		// If there is a set of post_save functions, then we process them

		if (isset($post_save)) {
			foreach ($post_save as $post_save_function) {
				$post_save_function();
			}
		}
		
		$AppUI->redirect();
	}

} // end of if subform
?>
