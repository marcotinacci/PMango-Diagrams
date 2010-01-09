<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store project in a version

 File:       do_saveas.php
 Location:   pmango\modules\projects
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   First version, created to load and store information from saveas.php.

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

$project_id = dPgetParam($_POST, 'project_id');
$project_group = dPgetParam($_POST, 'project_group','0');
$version = dPgetParam($_POST, 'version',0); 
$description = dPgetParam($_POST, 'description','-');
$today = dPgetParam($_POST, 'today',0);

if (!$project_id || is_null($project_id) || is_null($version)) {
  	$AppUI->setMsg('Invalid Options', UI_MSG_ERR);
  	$AppUI->redirect('m=public&a=access_denied');
}

$sql = "INSERT INTO passive_project_versions (project_group, project_id, project_vdate, project_version, project_vdescription) VALUES ($project_group, $project_id, $today, '$version', '$description')";
db_exec($sql);

$sql = "SELECT project_vid FROM passive_project_versions WHERE project_group=$project_group && project_id=$project_id && project_version='$version'";
$pvid = db_loadResult($sql);

$q = new DBQuery();
$q->addTable('tasks');
$q->addQuery('task_id');
$q->addWhere("task_project = $project_id");	
$tasks = $q->loadColumn();
$q->clear();


// Build the SQL manually.
$db->setFetchMode(ADODB_FETCH_ASSOC) ;// DA MODIFICARE
//$alltables = $db->MetaTables('TABLE');
$output  = '';
$output .= '# Backup of project ' . $project_id . " version $version\r\n";
$output .= '# Generated on ' . date('j F Y, H:i:s') . "\r\n";
$output .= '# OS: ' . PHP_OS . "\r\n";
$output .= '# PHP version: ' . PHP_VERSION . "\r\n";
if ($dPconfig['dbtype'] == 'mysql')
	$output .= '# MySQL version: ' . mysql_get_server_info() . "\r\n";
$output .= "\r\n";
$output .= "\r\n";
// fetch all tables on by one

//TABELLA projects
$result = $db->Execute('SELECT * FROM projects WHERE project_id = '.$project_id);
while($tablerow = $result->fetchRow()) {
		unset($tablerow['project_id']); 
		$tablerow['project_current'] = "g".$project_group."p".$project_id."v".$version;
		$tablerow['project_name'] = $tablerow['project_name'].' Ver. '.$version;
		$tablerow['project_active'] = 0;
		$output .= 'INSERT INTO `projects` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
		$output .= ' VALUES (';
		$first = true;
		foreach ($tablerow as $value) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ',';
			  
		      // remove all enters from the field-string. MySql stamement must be on one line
		      $value = str_replace("\r\n",'\n',$value);
		  	  $value = str_replace("\n", '\n', $value); // Just in case there are unadorned newlines.
		      // replace ' by \'
		      $value = str_replace('\'',"\'",$value);
		      $output .= '\''.$value.'\'';
		}
		$output .= ');' . "\r\n";
		
		$prVer = "(SELECT MAX(project_id) FROM projects WHERE project_current = 'g".$project_group."p".$project_id."v".$version."')";
} // while
$output .= "\r\n"; $output .= "\r\n";

//TABELLA tasks
$result = $db->Execute('SELECT * FROM tasks WHERE task_id IN ('.implode(', ', array_values($tasks)).')');
$output .="DROP TABLE IF EXISTS `Tg".$project_group."p".$project_id."v".$pvid."`;\r\n";
$output .="CREATE TABLE `Tg".$project_group."p".$project_id."v".$pvid."` (\r\n";
$output .="`old` int(11) NOT NULL default '0',\r\n";
$output .="`new` int(11) NOT NULL default '0',\r\n";//si può mettere anche null
$output .="PRIMARY KEY  (`old`,`new`)\r\n";
$output .=") TYPE=MyISAM;\r\n";//$output .= 'SELECT * FROM tasks WHERE task_id IN ('.implode(', ', array_values($tasks)).')';
while($tablerow = $result->fetchRow()) {
		
		$old = $tablerow['task_id']; 
		$tablerow['task_project']='MAX(project_id)';
		unset($tablerow['task_id']); 
		
		$output .= 'INSERT INTO `tasks` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
		$output .= ' SELECT ';
		$first = true;
		foreach ($tablerow as $value) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ',';
			  
		      // remove all enters from the field-string. MySql stamement must be on one line
		      $value = str_replace("\r\n",'\n',$value);
		  	  $value = str_replace("\n", '\n', $value); // Just in case there are unadorned newlines.
		      // replace ' by \'
		      $value = str_replace('\'',"\'",$value);
		      if ($value == 'MAX(project_id)')
		      	$output .= $value;
		      else
		      	$output .= '\''.$value.'\'';
		}
		$output .= "FROM `projects` WHERE project_current = 'g".$project_group."p".$project_id."v".$version."';" . "\r\n";
		$new = "(SELECT MAX(task_id) FROM tasks WHERE task_project = $prVer)";
		$output .= "INSERT INTO `Tg".$project_group."p".$project_id."v".$pvid."` (old,new)\r\n";
		$output .= "SELECT $old, MAX(task_id)\r\n";
		$output .= "FROM `tasks`\r\n WHERE task_project = $prVer;\r\n";
} // while
$result = $db->Execute('SELECT * FROM tasks WHERE task_id IN ('.implode(', ', array_values($tasks)).')');
while($tablerow = $result->fetchRow()) {
	$output .= "UPDATE `tasks` SET `task_parent` = (SELECT new FROM `Tg".$project_group."p".$project_id."v".$pvid."` WHERE old =". $tablerow['task_parent'].") WHERE task_id = (SELECT new FROM `Tg".$project_group."p".$project_id."v".$pvid."` WHERE old =". $tablerow['task_id'].");\r\n";
}
$output .= "\r\n"; $output .= "\r\n";

//user_projects
$result = $db->Execute('SELECT * FROM user_projects WHERE project_id = '.$project_id);
while($tablerow = $result->fetchRow()) {
		unset($tablerow['project_id']); 
		$tablerow['project_id'] = 'MAX(project_id)';
		
		$output .= 'INSERT INTO `user_projects` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
		$output .= ' SELECT ';
		$first = true;
		foreach ($tablerow as $value) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ',';
		      // remove all enters from the field-string. MySql stamement must be on one line
		      $value = str_replace("\r\n",'\n',$value);
		  	  $value = str_replace("\n", '\n', $value); // Just in case there are unadorned newlines.
		      // replace ' by \'
		      $value = str_replace('\'',"\'",$value);
		      if ($value == 'MAX(project_id)')
		      	$output .= $value;
		      else
		      	$output .= '\''.$value.'\'';
		}
		$output .= "FROM `projects` WHERE project_current = 'g".$project_group."p".$project_id."v".$version."';" . "\r\n";
} // while
$output .= "\r\n"; $output .= "\r\n";

//models for projects
$result = $db->Execute('SELECT * FROM models WHERE model_association = 1 && model_pt = '.$project_id);
while($tablerow = $result->fetchRow()) {
		unset($tablerow['model_pt']); 
		$tablerow['model_pt'] = 'MAX(project_id)';
		
		$output .= 'INSERT INTO `models` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
		$output .= ' SELECT ';
		$first = true;
		foreach ($tablerow as $value) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ',';
		      // remove all enters from the field-string. MySql stamement must be on one line
		      $value = str_replace("\r\n",'\n',$value);
		  	  $value = str_replace("\n", '\n', $value); // Just in case there are unadorned newlines.
		      // replace ' by \'
		      $value = str_replace('\'',"\'",$value);
		      if ($value == 'MAX(project_id)')
		      	$output .= $value;
		      else
		      	$output .= '\''.$value.'\'';
		}
		$output .= "FROM `projects` WHERE project_current = 'g".$project_group."p".$project_id."v".$version."';" . "\r\n";
} // while
$output .= "\r\n"; $output .= "\r\n";

$alltables = array('task_log','task_dependencies','user_tasks','user_task_pin','models');
$taskIdName = array('task_log_task','dependencies_task_id','task_id','model_pt');
foreach ($alltables as $table) {	
	//$db->setFetchMode(ADODB_FETCH_ASSOC) ;
	
	switch ($table)	{
		case 'task_log': $result = $db->Execute('SELECT * FROM task_log WHERE task_log_task IN ('.implode(', ', array_values($tasks)).')');
							 break;
		case 'task_dependencies': 	$result = $db->Execute('SELECT * FROM task_dependencies WHERE dependencies_task_id IN ('.implode(', ', array_values($tasks)).')');
									break;
		case 'user_tasks': 	$result = $db->Execute('SELECT * FROM user_tasks WHERE task_id IN ('.implode(', ', array_values($tasks)).')');
								break;
		case 'user_task_pin': 	$result = $db->Execute('SELECT * FROM user_task_pin WHERE task_id IN ('.implode(', ', array_values($tasks)).')');
									break;
		case 'models': $result = $db->Execute('SELECT * FROM models WHERE model_association = 2 && model_pt IN ('.implode(', ', array_values($tasks)).')');
									break;
	}
  	
	while($tablerow = $result->fetchRow()) {
		switch ($table)	{
			case 'task_log': $task_id=$tablerow['task_log_task'];
							 $tablerow['task_log_task']='new';
							 unset($tablerow['task_log_id']);
							 break;
			case 'task_dependencies': 	$task_id=$tablerow['dependencies_task_id'];
										$tablerow['dependencies_task_id']='new';
										break;
			case 'user_tasks': 	
			case 'user_task_pin': 	$task_id=$tablerow['task_id'];
									$tablerow['task_id']='new';
									break;
			case 'models': 			$task_id=$tablerow['model_pt'];
									$tablerow['model_pt']='new';
									break;
		}
			
		$output .= "INSERT INTO `$table` ( `" . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
		$output .= ' SELECT ';
		$first = true;
		foreach ($tablerow as $i => $value) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ',';
		      // remove all enters from the field-string. MySql stamement must be on one line
		      $value = str_replace("\r\n",'\n',$value);
		  	  $value = str_replace("\n", '\n', $value); // Just in case there are unadorned newlines.
		      // replace ' by \'
		      $value = str_replace('\'',"\'",$value);
		      if ($value == 'new' && in_array($i,$taskIdName))
		      	$output .= $value;
		      else
		      	$output .= '\''.$value.'\'';
		}
		$output .= "FROM `Tg".$project_group."p".$project_id."v".$pvid."` WHERE old = $task_id;" . "\r\n";
		if ($table == 'task_dependencies') {
			$output .= "UPDATE `task_dependencies` SET `dependencies_req_task_id` = (SELECT new FROM `Tg".$project_group."p".$project_id."v".$pvid."` WHERE old =". $tablerow['dependencies_req_task_id'].") WHERE dependencies_task_id = (SELECT new FROM `Tg".$project_group."p".$project_id."v".$pvid."` WHERE old =". $task_id.") && dependencies_req_task_id = ".$tablerow['dependencies_req_task_id'].";\r\n";
		}
	} // while
	$output .= "\r\n"; $output .= "\r\n";
}
$output .= "\r\n";

$output .="DROP TABLE IF EXISTS `Tg".$project_group."p".$project_id."v".$pvid."`;";
$filename = "$baseDir/modules/projects/versions/g$project_group-p$project_id-v$version.sql";
/*
// Let's make sure the file exists and is writable first.
if (is_writable($filename)) {*/

    // In our example we're opening $filename in append mode.
    // The file pointer is at the bottom of the file hence 
    // that's where $output will go when we fwrite() it.
    if (!$handle = fopen($filename, 'w')) {
         $AppUI->setMsg("Cannot open file ($filename)",UI_MSG_ERROR);
         $AppUI->redirect();
    }

    // Write $output to our opened file.
    if (fwrite($handle, $output) === FALSE) {
        $AppUI->setMsg("Cannot write to file ($filename)",UI_MSG_ERROR);
        $AppUI->redirect();
    }
    
    //echo "Success, wrote ($output) to file ($filename)";
    
    fclose($handle);
    
require_once "$baseDir/install/install.inc.php";
InstallLoadSql("$filename",'',false);

$AppUI->setMsg( 'Version created', UI_MSG_OK);
$AppUI->redirect('?m=projects&tab=4');
?>
