<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      Gantt generation

 File:       gantt.php
 Location:   pmango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango Gantt.
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


include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph.php");
include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph_gantt.php");

global $group_id, $locale_char_set, $proFilter, $projectStatus, $showInactive, $showLabels, $showAllGantt;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

$where="1";
$pjobj =& new CProject;
$allowedProjects = $pjobj->getAllowedRecords($AppUI->user_id, 'project_id, project_name', 'project_name' ); //CONTROLLARE I PERMESSI.OK
require_once( $AppUI->getModuleClass( 'admin' ) );
$usObj = new CUser();
$membProjects = $usObj->getUserProject($AppUI->user_id);//print_r($allowedProjects);
$allowedProjects = array_intersect($membProjects,is_array($allowedProjects)?array_keys($allowedProjects):array());
if (count($allowedProjects) > 0) { $where = 'p.project_id IN (' . implode(',', $allowedProjects) . ')'; } else $where = '0';

$projectStatus = dPgetSysVal( 'ProjectStatus' );
$projectStatus = arrayMerge( array( '-2' => $AppUI->_('All w/o in progress')), $projectStatus);
$proFilter = dPgetParam($_REQUEST, 'proFilter', '-1');
$group_id = dPgetParam($_REQUEST, 'group_id', 0);
$showLabels = dPgetParam($_REQUEST, 'showLabels', 0);
$showInactive = dPgetParam($_REQUEST, 'showInactive', 0);

/*if ($proFilter == '-2'){
        $where .= " AND project_status != 3 ";
} else if ($proFilter != '-1') {
        $where .= " AND project_status = $proFilter ";
}*/
if ($group_id != 0) {
        $where .= " AND project_group = $group_id ";
}

if ($showInactive != '1')
	$where .= " AND project_active <> 0 ";

// pull valid projects and their percent complete information
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
$q  = new DBQuery;
$q->addTable('projects', 'p');
$q->addQuery("DISTINCT project_id, project_color_identifier, project_name, project_start_date, project_finish_date, project_status, project_active");
$q->addJoin('groups', 'c1', 'p.project_group = c1.group_id');
if (!empty($where))
	$q->addWhere($where);
$q->addGroup('project_id');
$q->addOrder('project_name');
$projects = $q->loadList();
$q->clear();

$width      = dPgetParam( $_GET, 'width', 600 );
$start_date = dPgetParam( $_GET, 'start_date', 0 );
$end_date   = dPgetParam( $_GET, 'end_date', 0 );

$showAllGantt = dPgetParam( $_REQUEST, 'showAllGantt', '0' );
//$showTaskGantt = dPgetParam( $_GET, 'showTaskGantt', '0' );

$graph = new GanttGraph($width);
$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);

$graph->SetFrame(false);
$graph->SetBox(true, array(0,0,0), 2);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

$jpLocale = dPgetConfig( 'jpLocale' );
if ($jpLocale) {
	$graph->scale->SetDateLocale( $jpLocale );
}

if ($start_date && $end_date) {
	$graph->SetDateRange( $start_date, $end_date );
}

//$graph->scale->actinfo->SetFont(FF_ARIAL);
$graph->scale->actinfo->vgrid->SetColor('gray');
$graph->scale->actinfo->SetColor('darkgray');
$graph->scale->actinfo->SetColTitles(array( $AppUI->_('Project Name', UI_OUTPUT_RAW)),array(200));


$tableTitle = ($proFilter == '-1') ? $AppUI->_('All Projects') : $projectStatus[$proFilter];
$graph->scale->tableTitle->Set($tableTitle);

// Use TTF font if it exists
// try commenting out the following two lines if gantt charts do not display
if (is_file( TTF_DIR."arialbd.ttf" ))
	$graph->scale->tableTitle->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->scale->SetTableTitleBackground("#eeeeee");
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
        for($i = 0; $i < count(@$projects); $i++ ){
                $start = substr($p["project_start_date"], 0, 10);
                $finish = substr($p["project_finish_date"], 0, 10);

                $d_start->Date($start);
                $d_end->Date($finish);

                if ($i == 0){
                        $min_d_start = $d_start;
                        $max_d_end = $d_end;
                } else {
                        if (Date::compare($min_d_start,$d_start)>0){
                                $min_d_start = $d_start;
                        }
                        if (Date::compare($max_d_end,$d_end)<0) {
                                $max_d_end = $d_end;
                        }
                }//$max_d_end = $d_end;
        }
}

// check day_diff and modify Headers
$day_diff = $min_d_start->dateDiff($max_d_end);

if ($day_diff > 240){
        //more than 240 days
        $graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
} else if ($day_diff > 90){
        //more than 90 days and less of 241
        $graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HWEEK );
        $graph->scale->week->SetStyle(WEEKSTYLE_WNBR);
}

$row = 0;

if (!is_array($projects) || sizeof($projects) == 0) {
 $d = new CDate();
 $bar = new GanttBar($row++, array(' '.$AppUI->_('No projects found'),  ' ', ' ', ' '), $d->getDate(), $d->getDate(), ' ', 0.6);
 $bar->title->SetCOlor('red');
 $graph->Add($bar);
}

if (is_array($projects)) {
	foreach($projects as $p) {

		if ( $locale_char_set=='utf-8' && function_exists("utf8_decode") ) {
			$name = strlen( utf8_decode($p["project_name"]) ) > 25 ? substr( utf8_decode($p["project_name"]), 0, 22 ).'...' : utf8_decode($p["project_name"]) ;
		} else {
			//while using charset different than UTF-8 we need not to use utf8_deocde
			$name = strlen( $p["project_name"] ) > 25 ? substr( $p["project_name"], 0, 22 ).'...' : $p["project_name"] ;
		}
	
		//using new jpGraph determines using Date object instead of string
		$start = ($p["project_start_date"] > "0000-00-00 00:00:00") ? $p["project_start_date"] : date("Y-m-d H:i:s");
		$end_date   = $p["project_finish_date"];
		$obj = new CProject();
	    
	    $end_date = new CDate($end_date);
	//	$finish->addDays(0);
		$finish = $end_date->getDate();
	
		$start = new CDate($start);
	//	$start->addDays(0);
		$start = $start->getDate();
	
		$progress = CProject::getPr($p['project_id']);
		
		$caption = "";
		if(!$start || $start == "0000-00-00 00:00:00"){
			$start = !$finish ? date("Y-m-d") : $finish;
			$caption .= $AppUI->_("(no start date)");
		}
	
		if(!$finish || $finish == "0000-00-00 00:00:00") {
			$finish = $start;
			$caption .= " ".$AppUI->_("(no finish date)");
		} else {
			$cap = "";
		}
	
        if ($showLabels){
                $caption .= $AppUI->_($projectStatus[$p['project_status']]).", ";
                $caption .= $p['project_active'] <> 0 ? $AppUI->_('active') : $AppUI->_('inactive');
        }
		$enddate = new CDate($finish);
		$startdate = new CDate($start);
		
        $bar = new GanttBar($row++, array($name), $start, $p["project_finish_date"], $cap, 0.6);
        $bar->progress->Set($progress/100);

        $bar->title->SetFont(FF_FONT1,FS_NORMAL,10);
        $bar->SetFillColor("#".$p['project_color_identifier']);
        $bar->SetPattern(BAND_SOLID,"#".$p['project_color_identifier']);
	
		//adding captions
		$bar->caption = new TextProperty($caption);
		$bar->caption->Align("left","center");
	
	        // gray out templates, completes, on ice, on hold
	        if ($p['project_status'] != '3' || $p['project_active'] == '0') {
	                $bar->caption->SetColor('darkgray');
	                $bar->title->SetColor('darkgray');
	                $bar->SetColor('darkgray');
	                $bar->SetFillColor('gray');
	                //$bar->SetPattern(BAND_SOLID,'gray');
	                $bar->progress->SetFillColor('darkgray');
	                $bar->progress->SetPattern(BAND_SOLID,'darkgray',98);
	        }
	
		$graph->Add($bar);
	
	 	// If showAllGant checkbox is checked 
	 	if ($showAllGantt)
	 	{
	 	// insert tasks into Gantt Chart
	 		
	 		// select for tasks for each project	
			
	 		$q  = new DBQuery;
			$q->addTable('tasks');
			$q->addQuery('DISTINCT tasks.task_id, tasks.task_name, tasks.task_start_date, tasks.task_finish_date, tasks.task_milestone');
			$q->addJoin('projects', 'p', 'p.project_id = tasks.task_project');
			$q->addWhere("p.project_id = {$p["project_id"]}");
	 		$tasks = $q->loadList();
			$q->clear();
			$wbs=array();
	 		foreach($tasks as $i => $t) 
	 			$wbs[$i] = CTask::getWBS($t["task_id"]);
	 		
	 		array_multisort($wbs, SORT_ASC,  $tasks);
	 		
	 		foreach($tasks as $i => $t)	{
	 			if ($t["task_finish_date"] == null)
	 				$t["task_finish_date"] = $t["task_start_date"];
	 			
				$tStart = ($t["task_start_date"] > "0000-00-00 00:00:00") ? $t["task_start_date"] : date("Y-m-d H:i:s");
				$tEnd = ($t["task_finish_date"] > "0000-00-00 00:00:00") ? $t["task_finish_date"] : date("Y-m-d H:i:s");
				$tStartObj = new CDate($tStart);
				$tEndObj = new CDate($tEnd);
				$bar2 = new GanttBar($row++, array(substr($wbs[$i]." ".$t["task_name"], 0, 30)."..."), $tStart, $tEnd, ' ', 0.5);
				$tid = $t["task_id"] > 0 ? intval($t["task_id"]) : 0;
				$bar2->progress->Set((CTask::getPr($tid))/100);
				if ($t["task_milestone"] == 1) 
					//$bar2  = new MileStone ($row++, "-- " . $t["task_name"], $t["task_start_date"], (substr($t["task_start_date"], 0, 10)));
	 				$bar2->title->SetColor("#CC0000");
				else 
					$bar2->title->SetColor( bestColor( '#ffffff', '#'.$p['project_color_identifier'], '#000000' ) );
				$bar2->SetFillColor("#".$p['project_color_identifier']);	
 				$graph->Add($bar2);
	 		}	
	 		
	 		// finish of insert tasks into Gantt Chart 
	 	}
	 	// finish of if showAllGant checkbox is checked
	}
} // finish of check for valid projects array.

$today = date("y-m-d");
$vline = new GanttVLine($today, $AppUI->_('Today', UI_OUTPUT_RAW));
$graph->Add($vline);
$graph->Stroke();
?>