<?php  
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      project module main page

 File:       index.php
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

$AppUI->savePlace();

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'groups' ) );

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

if(isset($_POST["group_id"])){
	$AppUI->setState("group_id", $_POST["group_id"]);
	$group_id = $_POST["group_id"];
} else {
	$group_id = $AppUI->getState('group_id');
	if (! isset($group_id)) {
		$group_id = $AppUI->_("All");
		$AppUI->setState('group_id', $group_id);
	}
}

if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'ProjIdxOrderDir' ) ? ($AppUI->getState( 'ProjIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';    
    $AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'ProjIdxOrderDir', $orderdir);
}

$orderby  = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_name';
$orderdir = $AppUI->getState( 'ProjIdxOrderDir' ) ? $AppUI->getState( 'ProjIdxOrderDir' ) : 'asc';
// get any records denied from viewing
$obj = new CProject();

$working_hours = $AppUI->user_day_hours;

// retrieve list of records
// modified for speed
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
// get the list of permitted companies
$allowedProjects = $obj->getAllowedRecords( $AppUI->user_id, 'project_id, project_name', 'project_name' );
require_once( $AppUI->getModuleClass( 'admin' ) );
$usObj = new CUser();
$membProjects = $usObj->getUserProject($AppUI->user_id);
$allowedProjects = array_intersect($membProjects,is_array($allowedProjects)?array_keys($allowedProjects):array());
	
if(count($allowedProjects) == 0) $allowedProjects = array(0);
//print_r( $allowedCompanies);
//global $projects;

$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('projects.project_id, project_active, project_status, project_color_identifier, project_name, project_description,
	project_start_date, project_finish_date, project_color_identifier, project_group, group_name, project_status, project_current,
	project_priority, project_effort, project_target_budget, CONCAT_WS(" ",user_last_name,user_first_name) as user_username');
$q->addJoin('groups', 'g', 'projects.project_group = g.group_id');
$q->addJoin('users', 'u', 'projects.project_creator = u.user_id');

if ($group_id > 0) {
	$q->addWhere("projects.project_group = '$group_id'");
}
// DO we have to include the above DENY WHERE restriction, too?
//$q->addJoin('', '', '');
if (count($allowedProjects) > 0) { $q->addWhere('projects.project_id IN (' . implode(',', $allowedProjects) . ')'); } else $q->addWhere('0');
$q->addGroup('projects.project_id');
$q->addOrder("$orderby $orderdir");
//$obj->setAllowedSQL($AppUI->user_id, $q);
$projects = $q->loadList();
//print_r( $projects);
// get the list of permitted companies
//$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

//display the select list
$q->clear();
$q->addTable('groups');
$q->addQuery('group_id, group_name');
$q->addWhere('group_id IN ('.implode(',', array_keys($AppUI->user_groups)) . ')');
$groupName = $q->loadHashList();
$group='';

// setup the title block
$titleBlock = new CTitleBlock( 'Projects', 'applet3-48.png', $m, "$m.$a");
$gr = array( 0 => $AppUI->_("All")) + $groupName; // db_loadHashList($sql);
$grSel= arraySelect($gr, "group_id", "class='text' onchange='javascript:document.searchform.submit()'", $group_id, false);

// setup the title block
$titleBlock->addCell("<form name='searchform' action='?m=projects' method='post'>						
								<td align='right' nowrap='nowrap'>".$AppUI->_('Group') .":</td>
								<td align='right' nowrap='nowrap'>".$grSel." </td>							
                      </form>");

$canAddProject = 0;
foreach ($AppUI->user_groups as $g => $sc)
	if (!$canAddProject)
		$canAddProject = $perms->checkModule('projects', 'add','',$sc);
	else
		break;
		
if ($canAddProject) {
	$titleBlock->addCrumb("?m=projects&a=addedit",$AppUI->_('New project'));
}

$titleBlock->show();

$project_types = dPgetSysVal("ProjectStatus");

$active = 0;
$complete = 0;
$archive = 0;
$proposed = 0;

foreach($project_types as $key=>$value)
{
        $counter[$key] = 0;
	if (is_array($projects)) {
		foreach ($projects as $p)
			if ($p['project_status'] == $key && $p['project_active'] > 0)
				++$counter[$key];
	}
                
        $project_types[$key] = $AppUI->_($project_types[$key], UI_OUTPUT_RAW) . ' (' . $counter[$key] . ')';
}


if (is_array($projects)) {
        foreach ($projects as $p)
        {
                if ($p['project_active'] > 0 && $p['project_status'] == 3)
                        ++$active;
                else if ($p['project_active'] > 0 && $p['project_status'] == 5)
                        ++$complete;
                else if ($p['project_active'] < 1)
                        ++$archive;
                else
                        ++$proposed;
        }
}

$fixed_project_type_file = array(
        $AppUI->_('In Progress', UI_OUTPUT_RAW) . ' (' . $active . ')' => "vw_idx_all",
        $AppUI->_('Complete', UI_OUTPUT_RAW) . ' (' . $complete . ')'    => "vw_idx_all",// to modify with vw_idx_complete
        $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archive . ')'    => "vw_idx_archived");
// we need to manually add Archived project type because this status is defined by 
// other field (Active) in the project table, not project_status
$project_types[] = $AppUI->_('Archived', UI_OUTPUT_RAW) . ' (' . $archive . ')';

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page &orderby=$orderby
$tabBox = new CTabBox( "?m=projects", "{$dPconfig['root_dir']}/modules/projects/", $tab );
if ( $tabBox->isTabbed() ) {
	// This will overwrited the initial tab, so we need to add that separately.
	if (isset($project_types[0]))
		$project_types[] = $project_types[0];
	$project_types[0] = $AppUI->_('All Projects', UI_OUTPUT_RAW) . ' (' . count($projects) . ')';
}

/**
* Now, we will figure out which vw_idx file are available
* for each project type using the $fixed_project_type_file array 
*/
$project_type_file = array();

foreach($project_types as $project_type){
	$project_type = trim($project_type);
	if(isset($fixed_project_type_file[$project_type])){
		$project_file_type[$project_type] = $fixed_project_type_file[$project_type];
	} else { // if there is no fixed vw_idx file, we will use vw_idx_all
		$project_file_type[$project_type] = "vw_idx_all";
	}
}

// tabbed information boxes
foreach($project_types as $project_type) {
	$tabBox->add($project_file_type[$project_type], $project_type, true);
}
$min_view = true;
$tabBox->add("viewgantt", "Gantt");
$tabBox->show();
?>
