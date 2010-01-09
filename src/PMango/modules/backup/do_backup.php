<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      creation of a backup file

 File:       do_backup.php
 Location:   pmango\modules\backup
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Third version, modified to manage PMango backup.
 - 2006.07.26 Lorenzo
   Second version, unmodified from dotProject 2.0.1.
 - 2006.07.26 Lorenzo
   First version, based on the work of the phpMyAdmin (c)2001-2002 phpMyAdmin group.
   
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

$perms =& $AppUI->acl();

$group_id = dPgetParam($_POST, 'group_id');
if (is_null($group_id)) {
	if (!$perms->checkModule('backup', 'view','',$AppUI->user_groups[-1],1))// Should we have an exec permission?
		$AppUI->redirect("m=public&a=access_denied");
}
else
	if (!$perms->checkModule('backup', 'view','',intval($group_id),1))
		$AppUI->redirect("m=public&a=access_denied");

$export_what = dPgetParam($_POST, 'export_what'); 
$output_format = dPgetParam($_POST, 'output_format');

$valid_export_options = array('all', 'table', 'data');
$valid_output_formats = array('zip', 'sql');

if ((! in_array($export_what, $valid_export_options) && (is_null($group_id)))
|| ! in_array($output_format, $valid_output_formats)) {
  	$AppUI->setMsg('Invalid Options', UI_MSG_ERR);
  	$AppUI->redirect('m=public&a=access_denied');
}

if (! is_null($group_id)) {
	$q = new DBQuery();
	$q->addTable('user_setcap');
	$q->addQuery('user_id');
	$q->addWhere('group_id ='.$group_id);
	$allGroupUsers = $q->loadColumn();
	// Solo gli utenti interni
	$ar_users = array();
	foreach ($allGroupUsers as $index => $u) 
		if ($perms->checkModule('projects','res',$u,intval($group_id),1)) {
			$ar_users[] = $u;
		}
	
	$q->clear();
	$q->addTable('projects');
	$q->addQuery('project_id');
	$q->addWhere('project_group ='.$group_id);
	$ar_projects = $q->loadColumn();
	$q->clear();
	if (count($ar_projects) > 0) {
			$q->addTable('tasks');
			$q->addQuery('task_id');
			$q->addWhere('task_project IN (' . implode(',', $ar_projects) . ')');	
			$ar_tasks = $q->loadColumn();
			$q->clear();
	}
}

// Build the SQL manually.
$db->setFetchMode(ADODB_FETCH_NUM);
$alltables = $db->MetaTables('TABLE');
$output  = '';
$output .= '# Backup of database \'' . $dPconfig['dbname'] . '\'' . "\r\n";
$output .= '# Generated on ' . date('j F Y, H:i:s') . "\r\n";
$output .= '# OS: ' . PHP_OS . "\r\n";
$output .= '# PHP version: ' . PHP_VERSION . "\r\n";
if ($dPconfig['dbtype'] == 'mysql')
$output .= '# MySQL version: ' . mysql_get_server_info() . "\r\n";
$output .= "\r\n";
$output .= "\r\n";
// fetch all tables on by one
foreach ($alltables as $table)
{	
	// introtext for this table
	$output .= '# TABLE: ' . $table . "\r\n";
	$output .= '# --------------------------' . "\r\n";
	$output .= '#' . "\r\n";
	$output .= "\r\n";
	
	if (($export_what != 'data') && is_null($group_id)) 
	{
		  // structure of the table
		  $output .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\r\n";
		  $output .= "\r\n";
		  $rs = $db->Execute('SELECT * FROM ' . $table . ' WHERE -1');
		
		  $fields = $db->MetaColumns($table);
		  $indexes = $db->MetaIndexes($table);
		  $output .= 'CREATE TABLE `' . $table . '` (' . "\r\n";
		  $primary = array();
		  $first = true;
		  if (is_array($fields)) {
			foreach ($fields as $details) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ",\r\n";
			  if ($details->primary_key)
			    $primary[] = $details->name;
			  $output .= '  `' . $details->name . '` ' . $details->type;
			  if ($details->max_length > -1) {
			    $output .= '(' . $details->max_length;
			    if (isset($details->scale))
			      $output .= ',' . $details->scale;
			    $output .= ')';
			  }
			  if ($details->not_null)
			    $output .= ' NOT NULL';
			  if ($details->has_default)
			  	if ($table!="sessions") 
			    	$output .= ' DEFAULT ' . "'$details->default_value'";
			  if ($details->auto_increment)
			    $output .= ' auto_increment';
			}
		  }
		  if (is_array($indexes)) {
			foreach ($indexes as $index => $details) {
			  if ($first)
			    $first = false;
			  else
			    $output .= ",\r\n";
			  $output .= '  ';
			  if ($details['unique'])
			    $output .= 'UNIQUE ';
			  $output .= 'KEY `' . $index . '` ( `' . implode('`, `', $details['columns'] ) . '` )';
			}
		  }
		  if (count($primary)) {
			$output .= ",\r\n" . '  PRIMARY KEY ( `'. implode('`, `', $primary) . '` )';
		  }
		  $output .= "\r\n" . ') TYPE=MYISAM;'."\r\n\r\n";
		  
		
	}
	
	if (($export_what != 'table') && is_null($group_id)) {
		// all data from table
		$db->setFetchMode(ADODB_FETCH_ASSOC) ;
		  $result = $db->Execute('SELECT * FROM '.$table);
		  while($tablerow = $result->fetchRow()) {
	    	$output .= 'REPLACE INTO `'.$table.'` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
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
		  } // while
		  $output .= "\r\n";
		  $output .= "\r\n";
	}
	if (!is_null($group_id)) {
	//inserire il contenuto per l'upload dal programma
	//leggere l'id del gruppo
	//all data from table
	  
	  $db->setFetchMode(ADODB_FETCH_ASSOC) ;
	  $result = $db->Execute('SELECT * FROM '.$table);
	  if ($table == 'config') continue;
	  if ($table == 'config_list') continue;
	  if (substr_count($table,'custom') > 0) continue;
	  if ($table == 'dpversion') continue;
	  if (substr_count($table,'gacl') > 0) continue;
	  if ($table == 'modules') continue;
	  if ($table == 'sessions') continue;
	  if ($table == 'syskeys') continue;
	  if ($table == 'sysvals') continue;
	  if ($table == 'type_dependencies') continue;
	  if ($table == 'active_project_versions') continue;
	  while($tablerow = $result->fetchRow()) {
	  	//if ($table == 'groups') print_r( $tablerow['group_id']);
	  	if (($table == 'groups') && ($tablerow['group_id'] != $group_id)) continue;
	  	if (($table == 'group_setcap') && ($tablerow['group_id'] != $group_id)) continue;  	
  		if (($table == 'user_setcap') && (($tablerow['group_id'] != $group_id) || (!in_array($tablerow['user_id'], $ar_users)))) continue;  	
  		if (($table == 'users') && (!in_array($tablerow['user_id'], $ar_users))) continue;
		if (($table == 'user_tasks') && ((!in_array($tablerow['user_id'], $ar_users)) || (!in_array($tablerow['task_id'], $ar_tasks)))) continue;	  		
  		if (($table == 'user_access_log') && (!in_array($tablerow['user_id'], $ar_users))) continue;
  		if (($table == 'user_task_pin') && (!in_array($tablerow['user_id'], $ar_users))) continue;
  		if (($table == 'user_projects') && ((!in_array($tablerow['user_id'], $ar_users)) || (!in_array($tablerow['project_id'], $ar_projects)))) continue;
  		if (($table == 'users') && ($tablerow['user_id'] == 1) && (in_array($tablerow['user_id'], $ar_users))) {
  			$tablerow['user_password']='MD5(passwd)';
	  	}
	  	// Le relazioni di amministrazione di sistema non si inseriscono
	  	if (($table == 'user_setcap') && ($tablerow['group_id'] == -1)) continue;
  		if (($table == 'projects') && (!in_array($tablerow['project_id'], $ar_projects))) continue;
  		if (($table == 'passive_project_versions') && (!in_array($tablerow['project_vid'], $ar_projects))) continue;
	  	if (($table == 'tasks') && (!in_array($tablerow['task_id'], $ar_tasks))) continue;
	  	if (($table == 'task_log') && (!in_array($tablerow['task_log_task'], $ar_tasks))) continue;
	  	if (($table == 'task_dependencies') && (!in_array($tablerow['dependencies_task_id'], $ar_tasks))) continue;
	  	// project_roles completamente riscritta ma anche user_preferences
	  	
	  	//print_r($tablerow);echo "<br>";
    	$output .= 'REPLACE INTO `'.$table.'` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
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
	  } // while
	  $output .= "\r\n";
	  $output .= "\r\n";
	}
}

switch ($output_format) {
  case 'zip':
    header('Content-Disposition: inline; filename="backup.zip"');
    header('Content-Type: application/x-zip');
    include_once $baseDir . '/modules/backup/zip.lib.php';
    $zip = new zipfile;
    $zip->addFile($output,'backup.sql');
    echo $zip->file();
    break;
  case 'sql':
    header('Content-Disposition: inline; filename="backup.sql"');
    header('Content-Type: text/sql');
    echo $output;
    break;
}

?>
