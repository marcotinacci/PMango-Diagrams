<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      task properties control 

 File:       do_properties.php
 Location:   pmango\modules\tasks
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   First version, created to verify properties in a task.

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

function setItem($item_name, $defval = null) {
	if (isset($_POST[$item_name]))
		return $_POST[$item_name];
	return $defval;
}

$obj = new CTask();
if (!$obj->load(setItem('task_id',0))) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
/*echo $obj->project_id;
echo $obj->project_start_date;*/
$wf = setItem('wf', 0);
$ce = setItem('ce', 0);
$te = setItem('te', 0);
$ee = setItem('ee', 0);

$r='';
$okMsg='Task is ';
$koMsg='Task isn\'t ';

$child = $obj->getChild();
if ($wf || $ce || $te) {
	$r2 = $obj->isDefined();
	if ($r2 == '')
		$AppUI->setProperties('Task is Defined', UI_MSG_PROP_OK);
	else {
		$AppUI->setProperties('Task isn\'t Defined', UI_MSG_PROP_KO);
		if (strlen($r2)>0) {
			$r2{strlen($r2)-1}=' ';
			$AppUI->setProperties($r2, UI_MSG_PROP_KO);
		}
	}
}

if ($wf) {
	$r = $obj->isWellFormed('',$child);
	if ($r == '' && $r2 == '') {
		$AppUI->setProperties('Task is Well Formed', UI_MSG_PROP_OK);
		$okMsg.='Well Formed, ';
	}
	else {
		$AppUI->setProperties('Task isn\'t Well Formed', UI_MSG_PROP_KO);
		if (strlen($r)>0) {
			$r{strlen($r)-1}=' ';
			$AppUI->setProperties($r, UI_MSG_PROP_KO);
		}
		$koMsg.='Well Formed, ';
	}
}

if ($ce) {
	$r = $obj->isCostEffective('',$child);
	if ($r == '' && $r2 == '') {
		$AppUI->setProperties('Task is Cost Effective', UI_MSG_PROP_OK);
		$okMsg.='Cost Effective, ';
	}
	else {
		$AppUI->setProperties('Task isn\'t Cost Effective', UI_MSG_PROP_KO);
		if (strlen($r)>0) {
			$r{strlen($r)-1}=' ';
			$AppUI->setProperties($r, UI_MSG_PROP_KO);
		}
		$koMsg.='Cost Effective, ';
	}
}

if ($ee) {
	$r = $obj->isEffortEffective('',$child);
	if ($r == '' && $r2 == '') {
		$AppUI->setProperties('Task is Effort Effective', UI_MSG_PROP_OK);
		$okMsg.='Effort Effective, ';
	}
	else {
		$AppUI->setProperties('Task isn\'t Effort Effective', UI_MSG_PROP_KO);
		if (strlen($r)>0) {
			$r{strlen($r)-1}=' ';
			$AppUI->setProperties($r, UI_MSG_PROP_KO);
		}
		$koMsg.='Effort Effective, ';
	}
}

if ($te) {
	$r = $obj->isTimeEffective('',$child);
	if ($r == '' && $r2 == '') {
		$AppUI->setProperties('Task is Time Effective', UI_MSG_PROP_OK);
		$okMsg.='Time Effective';
	}
	else {
		$AppUI->setProperties('Task isn\'t Time Effective', UI_MSG_PROP_KO);
		if (strlen($r)>0) {
			$r{strlen($r)-1}=' ';
			$AppUI->setProperties($r, UI_MSG_PROP_KO);
		}
		$koMsg.='Time Effective';
	}
}

if ($okMsg{strlen($okMsg)-2}==",") $okMsg{strlen($okMsg)-2}=".";
if ($koMsg{strlen($koMsg)-2}==",") $koMsg{strlen($koMsg)-2}=".";

if (strlen($koMsg) == 11 && strlen($okMsg) > 8 )
	$AppUI->setMsg( $okMsg , UI_MSG_PROP_OK);
elseif (strlen($koMsg) > 11) 
	$AppUI->setMsg( $koMsg , UI_MSG_PROP_KO);
	
$AppUI->redirect();
