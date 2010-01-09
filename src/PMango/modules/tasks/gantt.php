<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      Gantt generation.

 File:       gantt.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango Gantt.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1 (J. Christopher Pereira).
   
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

include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph.php");
include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph_gantt.php");

$project_id = defVal( @$_REQUEST['project_id'], 0 );
$f = defVal( @$_REQUEST['f'], 0 );
global $showLabels;
global $showWork;
global $locale_char_set;

$showLabels = dPgetParam($_REQUEST, 'showLabels', false);
// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

require_once $AppUI->getModuleClass('projects');
$project =& new CProject;
//$allowedProjects = $project->getAllowedRecords($AppUI->user_id, 'project_id, project_name');
//$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : NULL;
// pull valid projects and their percent complete information
$project_id = ($project_id > 0) ? $project_id : 0;
$psql = "
SELECT project_id, project_color_identifier, project_name, project_start_date, project_finish_date, project_group
FROM projects
LEFT JOIN tasks AS t ON projects.project_id = t.task_project
WHERE " . $project_id . "= project_id
GROUP BY project_id
ORDER BY project_name
";
$prc = db_exec( $psql );
echo db_error();
$pnums = db_num_rows( $prc );

$projects = array();
for ($x=0; $x < $pnums; $x++) {
	$z = db_fetch_assoc( $prc );
	$projects[$z["project_id"]] = $z;
	$pg = $z["project_group"];
}

if (!$perms->checkModule('projects','view','',intval($pg),1))
	$AppUI->redirect( "m=public&a=access_denied" );

// get any specifically denied tasks
$task =& new CTask;
//$deny = $task->getDeniedRecords($AppUI->user_id); PERMESSI!!!!!!!!!!!

// pull tasks

$select = "
tasks.task_id, task_parent, task_name, task_start_date, task_finish_date,
task_priority, task_order, task_project, task_milestone, 
project_name
";

$from = "tasks";
$join = "LEFT JOIN projects ON project_id = task_project";
$where = "task_project = $project_id";

/*switch ($f) {
	case 'all':
		$where .= "\nAND task_status > -1";
		break;
	case 'myproj':
		$where .= "\nAND task_status > -1\n	AND project_creator = $AppUI->user_id";
		break;
	case 'mycomp':
		$where .= "\nAND task_status > -1\n	AND project_company = $AppUI->user_company";
		break;
	case 'myinact':
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_status > -1
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
}*/

$tsql = "SELECT $select FROM $from $join WHERE $where ORDER BY project_id, task_wbs_index";
##echo "<pre>$tsql</pre>".mysql_error();##

$ptrc = db_exec( $tsql );
$nums = db_num_rows( $ptrc );
echo db_error();
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	
	if($row["task_start_date"] == "0000-00-00 00:00:00"){
		$row["task_start_date"] = date("Y-m-d H:i:s");
	}

	// calculate or set blank task_finish_date if unset
	if($row["task_finish_date"] == "0000-00-00 00:00:00") {
		$row["task_finish_date"] = "";
	}
		
	$projects[$row['task_project']]['tasks'][] = $row;
}

if($row["task_finish_date"] == "0000-00-00 00:00:00") {
		$row["task_finish_date"] = "";
	}
$width      = dPgetParam( $_GET, 'width', 600 );
//consider critical (concerning finish date) tasks as well
$start_date = dPgetParam( $_GET, 'start_date', $projects[$project_id]["project_start_date"] );

if($projects[$project_id]["project_finish_date"] == "0000-00-00 00:00:00" || empty($projects[$project_id]["project_finish_date"])) {
	$project_end = $start_date;
} else {
	$project_end = $projects[$project_id]["project_finish_date"];
}

$end_date   = dPgetParam( $_GET, 'finish_date', $project_end );

$count = 0;


$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
//$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY);

$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
//$graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE2);

// This configuration variable is obsolete
//$jpLocale = dPgetConfig( 'jpLocale' );
//if ($jpLocale) {
//	$graph->scale->SetDateLocale( $jpLocale );
//}
$graph->scale->SetDateLocale( $AppUI->user_locale );

if ($start_date && $end_date) {
	$graph->SetDateRange( $start_date, $end_date );
}
if (is_file( TTF_DIR."arialbd.ttf" )){
	$graph->scale->actinfo->SetFont(FF_ARIAL);
}
$graph->scale->actinfo->vgrid->SetColor('gray');
$graph->scale->actinfo->SetColor('darkgray');
$graph->scale->actinfo->SetColTitles(array( $AppUI->_('Task', UI_OUTPUT_RAW)),array(200));

$graph->scale->tableTitle->Set($projects[$project_id]["project_name"]);

// Use TTF font if it exists
// try commenting out the following two lines if gantt charts do not display
if (is_file( TTF_DIR."arialbd.ttf" ))
	$graph->scale->tableTitle->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->scale->SetTableTitleBackground("#".$projects[$project_id]["project_color_identifier"]);
$graph->scale->tableTitle->Show(true);

//-----------------------------------------
// nice Gantt image
// if diff(end_date,start_date) > 90 days it shows only
//week number
// if diff(end_date,start_date) > 240 days it shows only
//month number
//-----------------------------------------
if ($start_date && $end_date){
        $min_d_start = new CDate($start_date);
        $max_d_end = new CDate($end_date);
        $graph->SetDateRange( $start_date, $end_date );
} else {
        // find out DateRange from gant_arr
        $d_start = new CDate();
        $d_end = new CDate();
        for($i = 0; $i < count(@$gantt_arr); $i++ ){
                $a = $gantt_arr[$i][0];
                $start = substr($a["task_start_date"], 0, 10);
                $end = substr($a["task_finish_date"], 0, 10);

                $d_start->Date($start);
                $d_end->Date($end);

                if ($i == 0){
                        $min_d_start = $d_start;
                        $max_d_end = $d_end;
                } else {
                        if (Date::compare($min_d_start,$d_start)>0){
                                $min_d_start = $d_start;
                        }
                        if (Date::compare($max_d_end,$d_end)<0){
                                $max_d_end = $d_end;
                        }
                }
        }
}

// check day_diff and modify Headers
$day_diff = $min_d_start->dateDiff($max_d_end);

if ($day_diff > 300){
        //more than 240 days
        $graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
} else if ($day_diff > 120){
        //more than 90 days and less of 241
        $graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HWEEK );
        $graph->scale->week->SetStyle(WEEKSTYLE_WNBR);
}


//This kludgy function echos children tasks as threads

function showgtask( &$a, $level=0 ) {
	// Add tasks to gantt chart 

	global $gantt_arr;

	$gantt_arr[] = array($a, $level);	

}

function findgchild( &$tarr, $parent, $level=0 ){
	GLOBAL $projects;
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
			showgtask( $tarr[$x], $level );
			findgchild( $tarr, $tarr[$x]["task_id"], $level);
		}
	}
}

reset($projects);
$p = &$projects[$project_id];
$tnums = count( $p['tasks'] );

for ($i=0; $i < $tnums; $i++) {
	$t = $p['tasks'][$i];
	if ($t["task_parent"] == $t["task_id"]) {
		showgtask( $t );
		findgchild( $p['tasks'], $t["task_id"] );
	}
}

$hide_task_groups = false;

if($hide_task_groups) {
	for($i = 0; $i < count($gantt_arr); $i ++ ) {
		// remove task groups
		if($i != count($gantt_arr)-1 && $gantt_arr[$i + 1][1] > $gantt_arr[$i][1]) {
			// it's not a leaf => remove
			array_splice($gantt_arr, $i, 1);
			continue;
		}
	}
}

$row = 0;
for($i = 0; $i < count(@$gantt_arr); $i ++ ) {

	$a     = $gantt_arr[$i][0];
	$level = $gantt_arr[$i][1];

	if($hide_task_groups) $level = 0;
	
	$name = Ctask::getWBS($a["task_id"]);
	$name .= " ".$a["task_name"];
	if ( $locale_char_set=='utf-8' && function_exists("utf8_decode") ) {
		$name = utf8_decode($name);
	}
	
	$name = str_repeat(" ", $level).$name;
	$w = CTask::isLeafSt($a["task_id"]) ? 42 : 35;
    $name = strlen( $name ) > $w ? substr( $name, 0, $w-1 ).'...' : $name ;
	//using new jpGraph determines using Date object instead of string
	$start = $a["task_start_date"];
	$end_date = $a["task_finish_date"];

	$end_date = new CDate($end_date);
//	$end->addDays(0);
	$end = $end_date->getDate();

	$start = new CDate($start);
//	$start->addDays(0);
	$start = $start->getDate();
	
	$progress = intval($a["task_id"]) > 0 ? CTask::getPr($a["task_id"]) : 0;
	//$ac = );
	//$progress = 0;//$progress > 0 ? intval($progress) : 0;

	$cap = "";
	if(!$start || $start == "0000-00-00"){
		$start = !$end ? date("Y-m-d") : $end;
		$cap .= "(no start date)";
	}
	
	if(!$end) {
		$end = $start;
		$cap .= " (no finish date)";
	} else {
		$cap = "";
	}

	$caption = "";
	/*if ($showLabels=='1') {
		$sql = "select ut.task_id, u.user_username, ut.perc_effort from user_tasks ut, users u where u.user_id = ut.user_id and ut.task_id = ".$a["task_id"];
		$res = db_exec( $sql );
		while ($rw = db_fetch_row( $res )) {
			switch ($rw[2]) {
				case 100:
					$caption = $caption."".$rw[1].";";
					break;
				default:
					$caption = $caption."".$rw[1]."[".$rw[2]."%];";
					break;
			}
		}
		$caption = substr($caption, 0, strlen($caption)-1);
	}*/
	
			

	$enddate = new CDate($end);
	$startdate = new CDate($start);
	$bar = new GanttBar($row++, array($name), $start, $end, $cap, CTask::isLeafSt($a["task_id"]) ? 0.4 : 0.15);//se padre sarebbe meglio 1
	$bar->progress->Set($progress/100);
	if (is_file( TTF_DIR."arialbd.ttf" )) {
		$bar->title->SetFont(FF_ARIAL,FS_NORMAL, 8);
	}
    if (!CTask::isLeafSt($a["task_id"])) {
    	if (is_file( TTF_DIR."arialbd.ttf" )){
        	$bar->title->SetFont(FF_ARIAL,FS_BOLD, 8);
		}
		$bar->rightMark->Show();
        $bar->rightMark->SetType(MARK_RIGHTTRIANGLE);
        $bar->rightMark->SetWidth(3);
        $bar->rightMark->SetColor('black');
        $bar->rightMark->SetFillColor('black');

        $bar->leftMark->Show();
        $bar->leftMark->SetType(MARK_LEFTTRIANGLE);
        $bar->leftMark->SetWidth(3);
        $bar->leftMark->SetColor('black');
        $bar->leftMark->SetFillColor('black');
        
        $bar->SetPattern(BAND_SOLID,'black');
    }
	
	//adding captions
	$bar->caption = new TextProperty($caption);
	$bar->caption->Align("left","center");

        // show tasks which are both finished and past in (dark)gray
        if ($progress >= 100 && $end_date->isPast() && get_class($bar) == "ganttbar") {
                $bar->caption->SetColor('darkgray');
                $bar->title->SetColor('darkgray');
                $bar->setColor('darkgray');
                $bar->SetFillColor('darkgray');
                $bar->SetPattern(BAND_SOLID,'gray');
                $bar->progress->SetFillColor('darkgray');
                $bar->progress->SetPattern(BAND_SOLID,'gray',98);
        }

	$sql = "SELECT dependencies_task_id FROM task_dependencies WHERE dependencies_req_task_id=" . $a["task_id"];
	$query = db_exec($sql);

	while($dep = db_fetch_assoc($query)) {
		// find row num of dependencies
		for($d = 0; $d < count($gantt_arr); $d++ ) {
			if($gantt_arr[$d][0]["task_id"] == $dep["dependencies_task_id"]) {
				$bar->SetConstrain($d, CONSTRAIN_ENDSTART,'brown');
			}
		}
	}
	if ($a["task_milestone"]) 
		$bar->title->SetColor("#CC0000");
	$graph->Add($bar);
}
$today = date("y-m-d");
$vline = new GanttVLine($today, $AppUI->_('Today', UI_OUTPUT_RAW));
if (is_file( TTF_DIR."arialbd.ttf" )) {
	$vline->title->SetFont(FF_ARIAL,FS_BOLD,12);
}
$graph->Add($vline);
$graph->Stroke();
?>
