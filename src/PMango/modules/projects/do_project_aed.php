<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store project information

 File:       do_project_aed.php
 Location:   pmango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango projects.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.

---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team.

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

$obj = new CProject();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

require_once("./classes/CustomFields.class.php");

$members = $_POST['members'];
$tmp_ar = explode(";", $members);
$roles_assign_ar = array();//Array(user_id=>Array(proles_id))
for ($i = 0; $i < sizeof($tmp_ar); $i++) {
	$tmp = explode("=", $tmp_ar[$i]);
	if (count($tmp) > 1) 
		$roles_assign_ar[$tmp[0]][] = $tmp[1];
}
	
// convert dates to SQL format first
$date = new CDate( $obj->project_start_date );
$obj->project_start_date = $date->format( FMT_DATETIME_MYSQL );

if ($obj->project_finish_date) {
	$date = new CDate( $obj->project_finish_date );
	$date->setHour(23);
	$date->setMinute(59);
	$obj->project_finish_date = $date->format( FMT_DATETIME_MYSQL );
}

$del = dPgetParam( $_POST, 'del', 0 );

if ($obj->project_id > 0) {
	$model_type= dPgetParam( $_POST, 'model', 0 );
	$model_delivery_day = dPgetParam( $_POST, 'deliveryDay', null );
	
	$sql = "";
	if ($model_type == 1)
		$sql = "REPLACE INTO models (model_pt, model_association, model_type) VALUES ($obj->project_id,1,$model_type)";
	if ($model_type == 2 && !is_null($model_delivery_day))
		$sql = "REPLACE INTO models (model_pt, model_association, model_type, model_delivery_day) VALUES ($obj->project_id,1,$model_type,'$model_delivery_day')";
	if ($sql <> "")
		db_exec($sql);
}

// prepare (and translate) the module name ready for the suffix
if ($del) {
	$obj->project_group = dPgetParam( $_POST, 'project_group', 0 );
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "Project deleted", UI_MSG_ALERT);
		$AppUI->redirect( "m=projects" );
	}
} else {
	if ($msg = $obj->store()) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['project_id'];
		
		if(!is_null($members)) {
			if($isNotNew) 
				$obj->updateAssignedMembers($roles_assign_ar, true);
			else 
		 		$obj->addAssignedMembers($roles_assign_ar);
		}
		
		if ( $importTask_projectId = dPgetParam( $_POST, 'import_tasks_from', '0' ) )
			$obj->importTasks ($importTask_projectId);

 		$custom_fields = New CustomFields( $m, 'addedit', $obj->project_id, "edit" );
 		$custom_fields->bind( $_POST );
 		$sql = $custom_fields->store( $obj->project_id ); // Store Custom Fields

		$AppUI->setMsg( $isNotNew ? 'Project updated' : 'Project inserted', UI_MSG_OK);
	}
	$AppUI->redirect();
}
?>
