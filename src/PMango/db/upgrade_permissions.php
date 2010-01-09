<?php
/** ---------------------------------------------------------------------------

 PMango Project

 Title:      upgrade permissions

 File:       upgrade_permissions.php
 Location:   PMango/db
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       php

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
   Third version, modified to add report module permissions. 
 - 2006.07.18 Lorenzo
   Second version, new control access policy.
 - 2006.07.18 Lorenzo
   First version, unmodified from dotProject 2.0.1.

-------------------------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi, Riccardo Nicolini
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

-------------------------------------------------------------------------------------------
*/

global $baseDir;

if (! isset($baseDir)) {
	die('You must not run this script manually.  Instead run the Installer in install/index.php');
}

@include_once "$baseDir/includes/config.php";
require_once "$baseDir/includes/main_functions.php";
require_once "$baseDir/install/install.inc.php";
require_once "$baseDir/includes/db_adodb.php";
require_once "$baseDir/includes/db_connect.php";


// Now update the GACL class information.
require_once "$baseDir/classes/permissions.class.php";
require_once "$baseDir/classes/query.class.php";

dPmsg("Creating new Permissions objects");
$perms =& new dPacl;

// First, create the basic ACL sections.
$perms->add_object_section('System', "system", 1, 0, "aco");
$perms->add_object_section('Application', "application", 2, 0, "aco");
$perms->add_object_section('Users', "user", 1, 0, "aro");
$perms->add_object_section('System', "sys", 1, 0, "axo");
$perms->add_object_section('Application', "app", 2, 0, "axo");

// Create the permissions in the ACO sections.
$perms->add_object("system", "Login", "login", 1, 0, "aco");

$perms->add_object("application", "Access", "access", 1, 0, "aco");
$perms->add_object("application", "View", "view", 2, 0, "aco");
$perms->add_object("application", "Add", "add", 3, 0, "aco");
$perms->add_object("application", "Edit", "edit", 4, 0, "aco");
$perms->add_object("application", "Be a resource", "res", 5, 0, "aco");
$perms->add_object("application", "Delete", "delete", 6, 0, "aco");

// Now create the groups we need.
$role = $perms->add_group("role", "Roles", 0, "aro");

$sysadmin_role = $perms->add_group("admin", "System Administrator", $role, "aro");
$supervisor_role = $perms->add_group("supervisor", "Supervisor", $role, "aro");
$manager_role = $perms->add_group("manager", "Manager", $role, "aro");
$projadmin_role = $perms->add_group("projadmin", "Project Administrator", $role, "aro");
$worker_role = $perms->add_group("worker", "Worker", $role, "aro");
$guest_role = $perms->add_group("guest", "Guest", $role, "aro");

$mod = $perms->add_group("mod", "Modules", 0, "axo");
$all_mods = $perms->add_group("all", "All Modules", $mod, "axo");
$admin_mods = $perms->add_group("admin", "Admin Modules", $mod, "axo");
$non_admin_mods = $perms->add_group("non_admin", "Class Objects", $mod, "axo");

// Now create all of the objects we need// il terz'ultimo campo è l'ordine
$perms->add_object("sys", "ACL Administration", "acl", 1, 0, "axo");
$perms->add_object("app", "User Administration", "admin", 1, 0, "axo");
$perms->add_object("app", "System Administration", "system", 10, 0, "axo");
$perms->add_object("app", "Roles Administration", "roles", 14, 0, "axo");
$perms->add_object("app", "User Table", "users", 15, 0, "axo");

//$perms->add_object("app", "Calendar", "calendar", 2, 0, "axo");
//$perms->add_object("app", "Events", "events", 2, 0, "axo");

//$perms->add_object("app", "Tickets", "ticketsmith", 12, 0, "axo");
//$perms->add_object("app", "Contacts", "contacts", 4, 0, "axo");
//$perms->add_object("app", "Departments", "departments", 5, 0, "axo");
//$perms->add_object("app", "Files", "files", 6, 0, "axo");
//$perms->add_object("app", "Forums", "forums", 7, 0, "axo");
$perms->add_object("app", "Groups", "groups", 3, 0, "axo");
$perms->add_object("app", "Projects", "projects", 9, 0, "axo");
$perms->add_object("app", "Tasks", "tasks", 11, 0, "axo");
$perms->add_object("app", "Task Logs", "task_log", 11, 0, "axo");
$perms->add_object("app", "Backup", "backup", 12, 0, "axo");
$perms->add_object("app", "Help", "help", 8, 0, "axo");
//$perms->add_object("app", "History", "history", 16, 0, "axo");
$perms->add_object("app", "Public", "public", 13, 0, "axo");
$perms->add_object("app", "Reports", "report", 16, 0, "axo");//report

// Now we need to add some objects to some groups.
$perms->add_group_object($all_mods, "app", "admin", "axo");
//$perms->add_group_object($all_mods, "app", "calendar", "axo");
$perms->add_group_object($all_mods, "app", "groups", "axo");
//$perms->add_group_object($all_mods, "app", "events", "axo");
//$perms->add_group_object($all_mods, "app", "contacts", "axo");
//$perms->add_group_object($all_mods, "app", "departments", "axo");
//$perms->add_group_object($all_mods, "app", "files", "axo");
//$perms->add_group_object($all_mods, "app", "forums", "axo");
$perms->add_group_object($all_mods, "app", "help", "axo");
$perms->add_group_object($all_mods, "app", "projects", "axo");
$perms->add_group_object($all_mods, "app", "system", "axo");
$perms->add_group_object($all_mods, "app", "tasks", "axo");
$perms->add_group_object($all_mods, "app", "task_log", "axo");
$perms->add_group_object($all_mods, "app", "backup", "axo");
//$perms->add_group_object($all_mods, "app", "ticketsmith", "axo");
$perms->add_group_object($all_mods, "app", "public", "axo");
$perms->add_group_object($all_mods, "app", "roles", "axo");
$perms->add_group_object($all_mods, "app", "users", "axo");
$perms->add_group_object($all_mods, "app", "history", "axo");
$perms->add_group_object($all_mods, "app", "report", "axo");//report

// Admin groups
$perms->add_group_object($admin_mods, "app", "admin", "axo");
$perms->add_group_object($admin_mods, "app", "system", "axo");
$perms->add_group_object($admin_mods, "app", "roles", "axo");
$perms->add_group_object($admin_mods, "app", "users", "axo");
$perms->add_group_object($admin_mods, "app", "groups", "axo");
$perms->add_group_object($admin_mods, "app", "backup", "axo");

// Non admin groups
//$perms->add_group_object($non_admin_mods, "app", "calendar", "axo");
//$perms->add_group_object($non_admin_mods, "app", "events", "axo");
//$perms->add_group_object($non_admin_mods, "app", "contacts", "axo");
//$perms->add_group_object($non_admin_mods, "app", "departments", "axo");
//$perms->add_group_object($non_admin_mods, "app", "forums", "axo");
//$perms->add_group_object($non_admin_mods, "app", "groups", "axo");
$perms->add_group_object($non_admin_mods, "app", "projects", "axo");
$perms->add_group_object($non_admin_mods, "app", "tasks", "axo");
$perms->add_group_object($non_admin_mods, "app", "task_log", "axo");
$perms->add_group_object($non_admin_mods, "app", "backup", "axo");
//$perms->add_group_object($non_admin_mods, "app", "files", "axo");
$perms->add_group_object($non_admin_mods, "app", "public", "axo");
$perms->add_group_object($non_admin_mods, "app", "help", "axo");
$perms->add_group_object($non_admin_mods, "app", "report", "axo");//report
//$perms->add_group_object($non_admin_mods, "app", "ticketsmith", "axo");

//$perms->add_group_object($non_admin_mods, "app", "history", "axo");

// Assign default permissions

// the Roles group has Login permission.
$login_perms = array();
$login_perms['system'] = array('login');

$all_perms = array();
$all_perms['application'] = array('access', 'add', 'edit', 'view', 'delete');

$access_perms = array();
$access_perms['application'] = array('access');

$view_perms = array();
$view_perms['application'] = array('access', 'view');

$edit_perms = array();
$edit_perms['application'] = array('access', 'view', 'edit');

$acl_perms = array();
$acl_perms['sys'] = array('acl');

$res_perms = array();
$res_perms['application'] = array('res');

$perms->add_acl($login_perms, null, array($role), null, null, 1, 1, null, null, "user");

// System Administrator has ALL on ALL
$perms->add_acl($all_perms, null, array($sysadmin_role), null, array($admin_mods), 1, 1, null, null, "user");
$perms->add_acl($access_perms, null, array($sysadmin_role), $acl_perms, null, 1, 1, null, null, 'user');

$perms->add_object("user", "admin", 1, 1, 0, "aro");
$perms->add_group_object($sysadmin_role, "user", 1, "aro");
// TODO:  Add the administrator ACL access.

//Worker supervisor e project admin da inserire

// Supervisor
$mod_ar[app]=array('groups');
$perms->add_acl($view_perms, null, array($supervisor_role), $mod_ar, array($non_admin_mods), 1, 1, null, null, "user");

// Manager 
$perms->add_acl($all_perms, null, array($manager_role), null, array($non_admin_mods), 1, 1, null, null, "user");
$mod_ar[app]=array('projects');
$perms->add_acl($res_perms, null, array($manager_role), $mod_ar, null, 1, 1, null, null, "user");

// Worker 
$mod_ar[app]=array('tasks','public','help');
$perms->add_acl($view_perms, null, array($worker_role), $mod_ar, null, 1, 1, null, null, "user");
$mod_ar[app]=array('task_log');
$perms->add_acl($all_perms, null, array($worker_role), $mod_ar, null, 1, 1, null, null, "user");
$mod_ar[app]=array('projects');
$perms->add_acl($res_perms, null, array($worker_role), $mod_ar, null, 1, 1, null, null, "user");

// Project Administrator 
$mod_ar[app]=array('projects','tasks','backup','public','help');
$perms->add_acl($view_perms, null, array($projadmin_role), $mod_ar, null, 1, 1, null, null, "user");
$mod_ar[app]=array('task_log');
$perms->add_acl($all_perms, null, array($projadmin_role), $mod_ar, null, 1, 1, null, null, "user");
$mod_ar[app]=array('projects');
$perms->add_acl($res_perms, null, array($projadmin_role), $mod_ar, null, 1, 1, null, null, "user");

// Guest 
$perms->add_acl($view_perms, null, array($guest_role), null, array($non_admin_mods), 1, 1, null, null, "user");

// Anon has only access on all
//$perms->add_acl($access_perms, null, array($anon_role), null, array($non_admin_mods), 1, 1, null, null, "user");



dPmsg("Converting admin user permissions to System Administrator");
// Now we have the basics set up we need to create objects for all users

//serve per l'upgrade di dot project
/**$sql = "SELECT user_id, user_username, permission_id from users
LEFT JOIN permissions ON permission_user = users.user_id and permission_grant_on = 'all' 
AND permission_item = -1 and permission_value = -1";

$res = db_exec($sql);
if ($res) {
  while ($row = db_fetch_assoc($res)) {
    // Add the basic ARO
    $perms->add_object("user", $row["user_username"], $row["user_id"], 1, 0, "aro");
    if ($row["permission_id"]) {
      $perms->add_group_object($sysadmin_role, "user", $row["user_id"], "aro");
    }
  }
}*/

dPmsg("Searching for add-on modules to add to new permissions");
// Upgrade permissions for custom modules
$sql = "SELECT mod_directory, mod_name, permissions_item_table
	FROM modules
	WHERE mod_ui_active = 1
	AND mod_type = 'user'";
$custom_modules = db_loadList($sql);
foreach($custom_modules as $mod)
{
  $perms->addModule($mod['mod_directory'], $mod['mod_name']);
  $perms->addGroupItem($mod['mod_directory'], "non_admin");
                
  if (isset($mod['permissions_item_table']) && $mod['permissions_item_table'])
    $perms->addModuleSection($mod['permissions_item_table']);
}
$us = array();
$q = new DBQuery();
$q->addTable('user_setcap');
$q->addQuery('user_id, setcap_id');
$us = $q->loadHashList();
//print_r($us);
foreach ($us as $ui => $sc)
	if (!$perms->insertUserRole($sc, $ui)) {
     	dPmsg("Failed to add set of Capabilities<br>");
	}	
?>
