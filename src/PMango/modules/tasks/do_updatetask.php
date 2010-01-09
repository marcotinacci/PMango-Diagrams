<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store task log information.

 File:       do_updatetask.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango log task.
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
//There is an issue with international UTF characters, when stored in the database an accented letter
//actually takes up two letters per say in the field length, this is a problem with hour_cost since
//they are limited in size so saving a hour_cost as REDACIÓN would actually save REDACIÓ since the accent takes 
//two characters, so lets unaccent them, other languages should add to the replacements array too...
function cleanText($text){
	//This text file is not utf, its iso so we have to decode/encode
	$text = utf8_decode($text);
	$trade = array('á'=>'a','à'=>'a','ã'=>'a',
                 'ä'=>'a','â'=>'a',
                 'Á'=>'A','À'=>'A','Ã'=>'A',
                 'Ä'=>'A','Â'=>'A',
                 'é'=>'e','è'=>'e',
                 'ë'=>'e','ê'=>'e',
                 'É'=>'E','È'=>'E',
                 'Ë'=>'E','Ê'=>'E',
                 'í'=>'i','ì'=>'i',
                 'ï'=>'i','î'=>'i',
                 'Í'=>'I','Ì'=>'I',
                 'Ï'=>'I','Î'=>'I',
                 'ó'=>'o','ò'=>'o','õ'=>'o',
                 'ö'=>'o','ô'=>'o',
                 'Ó'=>'O','Ò'=>'O','Õ'=>'O',
                 'Ö'=>'O','Ô'=>'O',
                 'ú'=>'u','ù'=>'u',
                 'ü'=>'u','û'=>'u',
                 'Ú'=>'U','Ù'=>'U',
                 'Ü'=>'U','Û'=>'U',
                 'Ñ'=>'N','ñ'=>'n');
    $text = strtr($text,$trade);
	$text = utf8_encode($text);

	return $text;
}

// dylan_cuthbert: auto-transation system in-progress, leave this line commented out for now
//include( '/usr/local/translator/translate.php' );

$del = dPgetParam( $_POST, 'del', 0 );

$obj = new CTaskLog();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// dylan_cuthbert: auto-transation system in-progress, leave these lines commented out for now
//if ( $obj->task_log_description ) {
//	$obj->task_log_description .= "\n\n[translation]\n".translator_make_translation( $obj->task_log_description );
//}

if ($obj->task_log_edit_date) {
	$date = new CDate( $obj->task_log_edit_date );
	$obj->task_log_edit_date = $date->format( FMT_DATETIME_MYSQL );
}

if ($obj->task_start_log_date) { 
	$date = new CDate( $obj->task_log_start_date );
	$obj->task_log_start_date = $date->format( FMT_DATETIME_MYSQL );
}

if ($obj->task_log_finish_date) {
	$date = new CDate( $obj->task_log_finish_date );
	$obj->task_log_finish_date = $date->format( FMT_DATETIME_MYSQL );
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Task Log' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT );
	}
	$AppUI->redirect();
} else {
	$obj->task_log_proles_id = cleanText($obj->task_log_proles_id);
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( @$_POST['task_log_id'] ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
}
/*
$task = new CTask();
$task->load( $obj->task_log_task );
$task->check();

//$task->task_percent_complete = dPgetParam( $_POST, 'task_percent_complete', null );

if(dPgetParam($_POST, "task_end_date", "") != ""){
	$task->task_end_date = $_POST["task_end_date"];
}

if ($task->task_percent_complete >= 100 && ( ! $task->task_end_date || $task->task_end_date == '0000-00-00 00:00:00')){
	$task->task_end_date = $obj->task_log_edit_date;
}

if (($msg = $task->store())) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
}

*/
$AppUI->redirect("m=tasks&a=view&task_id={$obj->task_log_task}&tab=0#tasklog{$obj->task_log_id}");
?>
