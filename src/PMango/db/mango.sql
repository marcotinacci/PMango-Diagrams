
#
# Use this schema for creating your database for
# a new installation of PMango.
#
#---------------------------------------------------------------------------
#
# PMango Project
#
# Title:      database initialise file
#
# File:       mango.sql
# Location:   PMango/db
# Started:    2005.09.30
# Author:     Lorenzo Ballini
# Type:       sql
#
# This file is part of the PMango project
# Further information at: http://penelope.di.unipi.it
#
# Version history.
# - 2007.06.23 Riccardo
#   Sixth version, modified to create reports table and to insert report module and permissions. 
# - 2007.06.16 Lorenzo
#   Fifth version, modified to insert in sessions table a new field called session_user, to guarantee compatibility 
#	with php > 5.2.0 and its new session type configuration.
# - 2007.03.18 Lorenzo
#   Fourth version, modified to insert four indexes on user_tasks, user_setcap and user_project tables.
# - 2006.11.05 Lorenzo
#   Third version, modified to manage two parameters to setting width and height image.
# - 2006.07.18 Lorenzo
#   Second version, modified to create new PMango database.
# - 2006.07.18 Lorenzo
#   First version, unmodified from dotProject 2.0.1.
#
#-------------------------------------------------------------------------------------------
#
# PMango - A web application for project planning and control.
#
# Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi, Riccardo Nicolini
# All rights reserved.
#
# PMango reuses part of the code of dotProject 2.0.1: dotProject code is
# released under GNU GPL, further information at: http://www.dotproject.net
# Copyright (c) 2003-2005 The dotProject Development Team
#
# Other libraries used by PMango are redistributed under their own license.
# See ReadMe.txt in the root folder for details.
#
# PMango is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
#-------------------------------------------------------------------------------------------


DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
	`report_id` int(11) unsigned NOT NULL auto_increment,
	`project_id` int(11) default NULL,
	`user_id` int(11) default NULL,
	`p_is_incomplete` varchar(3) collate latin1_general_ci default NULL,
	`p_report_level` int(2) default NULL,
	`p_report_roles` varchar(3) collate latin1_general_ci default NULL,
	`p_report_sdate` datetime default NULL,
	`p_report_edate` datetime default NULL,
	`p_report_opened` text collate latin1_general_ci,
	`p_report_closed` text collate latin1_general_ci,
	`a_is_incomplete` varchar(3) collate latin1_general_ci default NULL,
	`a_report_level` int(2) default NULL,
	`a_report_roles` varchar(3) collate latin1_general_ci default NULL,
	`a_report_sdate` datetime default NULL,
	`a_report_edate` datetime default NULL,
	`a_report_opened` text collate latin1_general_ci,
	`a_report_closed` text collate latin1_general_ci,
	`l_hide_inactive` varchar(3) collate latin1_general_ci default NULL,
	`l_hide_complete` varchar(3) collate latin1_general_ci default NULL,
	`l_user_id` int(4) default NULL,
	`l_report_sdate` datetime default NULL,
	`l_report_edate` datetime default NULL,
	`properties` text collate latin1_general_ci,
	`prop_summary` text collate latin1_general_ci,
	PRIMARY KEY  (`report_id`),
UNIQUE KEY `report_id` (`report_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `group_id` INT(10) NOT NULL auto_increment,
  `group_module` INT(10) NOT NULL default 0,				
  `group_name` varchar(100) default '',
  `group_phone1` varchar(30) default '',					
  `group_phone2` varchar(30) default '',					
  `group_fax` varchar(30) default '',						
  `group_address1` varchar(50) default '',					
  `group_address2` varchar(50) default '',					
  `group_city` varchar(30) default '',						
  `group_state` varchar(30) default '',						
  `group_zip` varchar(11) default '',						
  `group_primary_url` varchar(255) default '',				
  `group_owner` int(11) NOT NULL default '0',				
  `group_description` text NOT NULL default '',				
  `group_type` int(3) NOT NULL DEFAULT '0',
  `group_email` varchar(255),
  `group_custom` LONGTEXT,									
  PRIMARY KEY (`group_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL auto_increment,
  `project_group` int(11) NOT NULL default '0',
  `project_name` varchar(255) default NULL,
  `project_short_name` varchar(10) default NULL,
  `project_url` varchar(255) default NULL, 
  `project_start_date` datetime default NULL,
  `project_finish_date` datetime default NULL,
  `project_today` datetime default NULL,
  `project_status` int(11) default '0',
  `project_effort` float unsigned default '0',
  `project_color_identifier` varchar(6) default 'eeeeee',
  `project_description` text,
  `project_target_budget` decimal(10,2) default '0.00',
  `project_hard_budget` decimal(10,2) default '0.00',
  `project_creator` int(11) default '0',
  `project_active` tinyint(4) default '1',
  `project_current` varchar(255) NOT NULL default '0',
  `project_priority` tinyint(4) default '0',
  `project_type` SMALLINT DEFAULT '0' NOT NULL,
  PRIMARY KEY  (`project_id`),
  KEY `idx_project_creator` (`project_creator`),
  KEY `idx_sdate` (`project_start_date`),
  KEY `idx_edate` (`project_finish_date`),
  KEY `project_short_name` (`project_short_name`),
  KEY `idx_proj1` (`project_group`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `user_projects`;
CREATE TABLE `user_projects` (
  `user_id` int(11) NOT NULL default '0',
  `proles_id` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`proles_id`,`project_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_proles_id` (`proles_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `active_project_versions`;
CREATE TABLE `active_project_versions` (
  `project_id` int(11) NOT NULL default '0',
  `project_version` varchar(255) NOT NULL default '0',
  `project_load_user` int(11) NOT NULL default '0',
  PRIMARY KEY  (`project_id`,`project_version`,`project_load_user`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `passive_project_versions`;
CREATE TABLE `passive_project_versions` (
  `project_vid` int(11) NOT NULL auto_increment,	
  `project_group` int(11) NOT NULL default '0',
  `project_id` int(11) NOT NULL default '0',
  `project_vdate` datetime default NULL,
  `project_version` varchar(255) NOT NULL default '0',
  `project_vdescription` text,
  PRIMARY KEY  (`project_vid`)
) TYPE=MyISAM;

#
# Table structure for table 'projectRoles'
#

DROP TABLE IF EXISTS `project_roles`;
CREATE TABLE `project_roles` (
  `proles_id` int(11) unsigned NOT NULL auto_increment,
  `proles_name` varchar(24) NOT NULL default '',
  `proles_description` varchar(255) default NULL,
  `proles_hour_cost` int(11) unsigned NOT NULL default '0',
  `proles_status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`proles_id`),
  KEY `idx_proles_name` (`proles_name`)
) TYPE=MyISAM;

INSERT INTO `project_roles` VALUES (1,'Project Manager','',30,0);
INSERT INTO `project_roles` VALUES (2,'Requirement Engineer','',26,0);
INSERT INTO `project_roles` VALUES (3,'Sales Manager','',28,0);
INSERT INTO `project_roles` VALUES (4,'Librarian','',24,0);
INSERT INTO `project_roles` VALUES (5,'Software Designer','',24,0);
INSERT INTO `project_roles` VALUES (6,'Test Engineer','',20,0);
INSERT INTO `project_roles` VALUES (7,'Programmer','',18,0);

DROP TABLE IF EXISTS `task_log`;
CREATE TABLE `task_log` (
  `task_log_id` INT(11) NOT NULL auto_increment,
  `task_log_task` INT(11) NOT NULL default '0',
  `task_log_name` VARCHAR(255) default NULL,
  `task_log_description` TEXT,
  `task_log_creator` INT(11) NOT NULL default '0',
  `task_log_hours` FLOAT DEFAULT "0" NOT NULL,
  `task_log_creation_date` DATETIME,
  `task_log_edit_date` DATETIME,
  `task_log_start_date` DATETIME,
  `task_log_finish_date` DATETIME,
  `task_log_proles_id` int(11) NOT NULL default '0',
  `task_log_progress` tinyint(4) default '0',
  `task_log_problem` TINYINT( 1 ) DEFAULT '0',
  PRIMARY KEY  (`task_log_id`),
  KEY `idx_log_task` (`task_log_task`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL auto_increment,
  `task_name` varchar(255) default NULL,
  `task_parent` int(11) default '0',
  `task_milestone` tinyint(1) default '0',
  `task_project` int(11) NOT NULL default '0',
  `task_wbs_index` varchar(10) NOT NULL default '',
  `task_start_date` datetime default NULL,
  `task_today` datetime default NULL,  
  `task_finish_date` datetime default NULL,
  `task_status` int(11) default '0',
  `task_priority` tinyint(4) default '0',
  `task_description` text,
  `task_related_url` varchar(255) default NULL,
  `task_creator` int(11) NOT NULL default '0',
  `task_order` int(11) NOT NULL default '0',
  `task_access` int(11) NOT NULL default '0',
  `task_custom` LONGTEXT,
  `task_type` SMALLINT DEFAULT '0' NOT NULL,
  `task_model` int(11) NOT NULL default '0',
  PRIMARY KEY  (`task_id`),
  KEY `idx_task_parent` (`task_parent`),
  KEY `idx_task_project` (`task_project`),
  KEY `idx_task_creator` (`task_creator`),
  KEY `idx_task_order` (`task_order`),
  KEY `idx_task1` (`task_start_date`),
  KEY `idx_task2` (`task_finish_date`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `models`;
CREATE TABLE `models` (
  `model_pt` int(11) NOT NULL default '0',
  `model_association` tinyint(4) default '0',
  `model_type` tinyint(4) default '0',
  `model_delivery_day` datetime default NULL,
  PRIMARY KEY  (`model_pt`, `model_association`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `user_tasks`;
CREATE TABLE `user_tasks` (
  `user_id` int(11) NOT NULL default '0',
  `proles_id` int(11) NOT NULL default '0',
  `task_id` int(11) NOT NULL default '0',
  `effort` float unsigned default '0',
  `perc_effort` int(11) NOT NULL default '100',
  `user_task_priority` tinyint(4) default '0',
  PRIMARY KEY  (`user_id`,`proles_id`,`task_id`),
  KEY `idx_task_id` (`task_id`),
  KEY `proles_id` (`proles_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_username` varchar(255) NOT NULL default '',
  `user_password` varchar(32) NOT NULL default '',
  `user_parent` int(11) NOT NULL default '0',
  `user_first_name` varchar(50) default '',
  `user_last_name` varchar(50) default '',
  `user_email` varchar(255) default '',
  `user_phone` varchar(30) default '',
  `user_mobile` varchar(30) default '',
  `user_address1` varchar(30) default '',
  `user_address2` varchar(30) default '',
  `user_city` varchar(30) default '',
  `user_state` varchar(30) default '',
  `user_zip` varchar(11) default '',
  `user_country` varchar(30) default '',
  `user_birthday` datetime default NULL,
  `user_day_hours` int(11) NOT NULL default '8',
  PRIMARY KEY  (`user_id`),
  KEY `idx_uid` (`user_username`),
  KEY `idx_pwd` (`user_password`),
  KEY `idx_user_parent` (`user_parent`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `task_dependencies`;
CREATE TABLE `task_dependencies` (
    `dependencies_task_id` int(11) NOT NULL,
    `dependencies_task_type_id` int(11) NOT NULL default '1',
    `dependencies_req_task_id` int(11) NOT NULL,
    PRIMARY KEY (`dependencies_task_id`, `dependencies_req_task_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `type_dependencies`;
CREATE TABLE `type_dependencies` (
    `dependencies_task_type_id` int(11) NOT NULL  auto_increment,
    `dependencies_task_type_name` varchar(255) NOT NULL default '',
    `dependencies_task_description` varchar(255) default NULL,
    PRIMARY KEY (`dependencies_task_type_id`)
) TYPE=MyISAM;

INSERT INTO `type_dependencies` VALUES("1", "Finish to Start", "");
INSERT INTO `type_dependencies` VALUES("2", "Start to Start", "");

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `pref_user` varchar(12) NOT NULL default '',
  `pref_name` varchar(72) NOT NULL default '',
  `pref_value` varchar(32) NOT NULL default '',
  KEY `pref_user` (`pref_user`,`pref_name`)
) TYPE=MyISAM;

#
# ATTENTION:
# Customize this section for your installation.
# Recommended changes include:
#   New admin username -> replace {admin}
#   New admin password -> replace {passwd]
#   New admin email -> replace {admin@localhost}
#

INSERT INTO `users` VALUES (1,'admin',MD5('passwd'),0,'AdFirstName','AdLastName','admin@localhost','','','','','','','','',null,8);

INSERT INTO `user_preferences` VALUES("0", "LOCALE", "en");
INSERT INTO `user_preferences` VALUES("0", "TABVIEW", "0");
INSERT INTO `user_preferences` VALUES("0", "SHDATEFORMAT", "%d/%m/%Y");
INSERT INTO `user_preferences` VALUES("0", "TIMEFORMAT", "%I:%M %p");
INSERT INTO `user_preferences` VALUES("0", "UISTYLE", "default");
INSERT INTO `user_preferences` VALUES("0", "TASKASSIGNMAX", "100");

#
# AJE (24/Jan/2003)
# ---------
# N O T E !
#
# MODULES TABLE IS STILL IN DEVELOPMENT STAGE
#

#
# Table structure for table 'modules'
#
DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `mod_id` int(11) NOT NULL auto_increment,
  `mod_name` varchar(64) NOT NULL default '',
  `mod_directory` varchar(64) NOT NULL default '',
  `mod_version` varchar(10) NOT NULL default '',
  `mod_setup_class` varchar(64) NOT NULL default '',
  `mod_type` varchar(64) NOT NULL default '',
  `mod_active` int(1) unsigned NOT NULL default '0',
  `mod_ui_name` varchar(20) NOT NULL default '',
  `mod_ui_icon` varchar(64) NOT NULL default '',
  `mod_ui_order` tinyint(3) NOT NULL default '0',
  `mod_ui_active` int(1) unsigned NOT NULL default '0',
  `mod_description` varchar(255) NOT NULL default '',
  `permissions_item_table` CHAR( 100 ),
  `permissions_item_field` CHAR( 100 ),
  `permissions_item_label` CHAR( 100 ),
  PRIMARY KEY  (`mod_id`,`mod_directory`)
) TYPE=MyISAM;

#
# Dumping data for table 'modules'
#
INSERT INTO `modules` VALUES("1", "Groups", "groups", "1.0.0", "", "core", "1", "Groups", "handshake.png", "1", "1", "", "groups", "group_id", "group_name");
INSERT INTO `modules` VALUES("2", "Projects", "projects", "1.0.0", "", "core", "1", "Projects", "applet3-48.png", "2", "1", "", "projects", "project_id", "project_name");
INSERT INTO `modules` VALUES("3", "Tasks", "tasks", "1.0.0", "", "core", "1", "Tasks", "applet-48.png", "3", "1", "", "tasks", "task_id", "task_name");
INSERT INTO `modules` VALUES("4", "Backup", "backup", "1.0.0", "", "core", "1", "Backup", "companies.gif", "4", "1", "", "", "", "");
INSERT INTO `modules` VALUES("5", "User Administration", "admin", "1.0.0", "", "core", "1", "User Admin", "helix-setup-users.png", "5", "1", "", "users", "user_id", "user_username");
INSERT INTO `modules` VALUES("6", "System Administration", "system", "1.0.0", "", "core", "1", "System Admin", "48_my_computer.png", "6", "1", "", "", "", "");

INSERT INTO `modules` VALUES("7", "Help", "help", "1.0.0", "", "core", "1", "Help", "mangoIcon.jpeg", "7", "1", "", "", "", "");
INSERT INTO `modules` VALUES("8", "Public", "public", "1.0.0", "", "core", "1", "Public", "users.gif", "8", "0", "", "", "", "");
INSERT INTO `modules` VALUES("9", "Reports", "report", "1.0.0", "", "core", "1", "Reports", "applet-report.png", "9", "0", "", "", "", "");

#INSERT INTO `modules` VALUES("4", "Calendar", "calendar", "1.0.0", "", "core", "1", "Calendar", "myevo-appointments.png", "4", "1", "", "", "", "");
#INSERT INTO `modules` VALUES("6", "Contacts", "contacts", "1.0.0", "", "core", "1", "Contacts", "monkeychat-48.png", "6", "1", "", "", "", "");
#INSERT INTO `modules` VALUES("7", "Forums", "forums", "1.0.0", "", "core", "1", "Forums", "support.png", "7", "1", "", "forums", "forum_id", "forum_name");
#INSERT INTO `modules` VALUES("8", "Tickets", "ticketsmith", "1.0.0", "", "core", "1", "Tickets", "ticketsmith.gif", "8", "1", "", "", "", "");
#INSERT INTO `modules` VALUES("11", "Departments", "departments", "1.0.0", "", "core", "1", "Departments", "users.gif", "11", "0", "", "", "", "");
#INSERT INTO `modules` VALUES("14", "History", "history", "1.0.0", "", "core", "1", "History", "users.gif", "14", "0", "", "", "", "");

#
# Table structure for table 'syskeys'
#

DROP TABLE IF EXISTS `syskeys`;
CREATE TABLE `syskeys` (
  `syskey_id` int(10) unsigned NOT NULL auto_increment,
  `syskey_name` varchar(48) NOT NULL default '' unique,
  `syskey_label` varchar(255) NOT NULL default '',
  `syskey_type` int(1) unsigned NOT NULL default '0',
  `syskey_sep1` char(2) default '\n',
  `syskey_sep2` char(2) NOT NULL default '|',
  PRIMARY KEY  (`syskey_id`),
  UNIQUE KEY `idx_syskey_name` (`syskey_id`)
) TYPE=MyISAM;

#
# Table structure for table 'sysvals'
#

DROP TABLE IF EXISTS `sysvals`;
CREATE TABLE `sysvals` (
  `sysval_id` int(10) unsigned NOT NULL auto_increment,
  `sysval_key_id` int(10) unsigned NOT NULL default '0',
  `sysval_title` varchar(48) NOT NULL default '',
  `sysval_value` text NOT NULL,
  PRIMARY KEY  (`sysval_id`)
) TYPE=MyISAM;

#
# Table structure for table 'sysvals'
#

INSERT INTO `syskeys` VALUES("1", "SelectList", "Enter values for list", "0", "\n", "|");
INSERT INTO `syskeys` VALUES ("2", 'CustomField', 'Serialized array in the following format:\r\n<KEY>|<SERIALIZED ARRAY>\r\n\r\nSerialized Array:\r\n[type] => text | checkbox | select | textarea | label\r\n[name] => <Field\'s name>\r\n[options] => <html capture options>\r\n[selects] => <options for select and checkbox>', 0, '\n', '|');
INSERT INTO `syskeys` VALUES("3", "ColorSelection", "Hex color values for type=>color association.", "0", "\n", "|");

INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "ProjectStatus", "0|Not Defined\r\n1|In Planning\r\n2|In Progress\r\n3|Complete");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "GroupType", "0|Not Applicable\n1|Internal\n2|External");
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "TaskDurationType", "1|hours\n24|days");
#INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("1", "EventType", "0|General\n1|Appointment\n2|Meeting\n3|All Day Event\n4|Anniversary\n5|Reminder");
INSERT INTO `sysvals` VALUES (null, "1", 'TaskStatus', '0|Active\n-1|Inactive');
INSERT INTO `sysvals` VALUES (null, "1", 'TaskType', '0|Unknown\n1|Administrative\n2|Operative');
INSERT INTO `sysvals` VALUES (null, "1", 'ProjectType', '0|Unknown\n1|Administrative\n2|Operative');
INSERT INTO `sysvals` (`sysval_key_id`,`sysval_title`,`sysval_value`) VALUES("3", "ProjectColors", "Web|FFE0AE\nEngineering|AEFFB2\nHelpDesk|FFFCAE\nSystem Administration|FFAEAE");
INSERT INTO `sysvals` VALUES (null, "1", 'FileType', '0|Unknown\n1|Document\n2|Application');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'TaskPriority', '-1|low\n0|normal\n1|high');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'ProjectPriority', '-1|low\n0|normal\n1|high');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'ProjectPriorityColor', '-1|#E5F7FF\n0|\n1|#FFDCB3');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'TaskLogReference', '0|Not Defined\n1|Email\n2|Helpdesk\n3|Phone Call\n4|Fax');
INSERT INTO `sysvals` ( `sysval_id` , `sysval_key_id` , `sysval_title` , `sysval_value` ) VALUES (null, '1', 'TaskLogReferenceImage', '0| 1|./images/obj/email.gif 2|./modules/helpdesk/images/helpdesk.png 3|./images/obj/phone.gif 4|./images/icons/stock_print-16.png');


#
# Table structure for table 'group_setcap'
#

DROP TABLE IF EXISTS `group_setcap`;
CREATE TABLE `group_setcap` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `setcap_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`setcap_id`)
) TYPE=MyISAM;

#
# Table structure for table 'users_setcap'
#

DROP TABLE IF EXISTS `user_setcap`;
CREATE TABLE `user_setcap` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(12) NOT NULL default '0',
  `setcap_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`group_id`),
  KEY `idx_group_id` (`group_id`),
  KEY `idx_setcap_id` (`setcap_id`)
) TYPE=MyISAM;

INSERT INTO `user_setcap` (`user_id`, `group_id`, `setcap_id`) VALUES ('1', '-1', '11');


#20040823
#Added user access log
DROP TABLE IF EXISTS `user_access_log`;
CREATE TABLE `user_access_log` (
`user_access_log_id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`user_id` INT( 10 ) UNSIGNED NOT NULL ,
`user_ip` VARCHAR( 15 ) NOT NULL ,
`date_time_in` DATETIME DEFAULT '0000-00-00 00:00:00',
`date_time_out` DATETIME DEFAULT '0000-00-00 00:00:00',
`date_time_last_action` DATETIME DEFAULT '0000-00-00 00:00:00',
PRIMARY KEY ( `user_access_log_id` )
) TYPE = MyISAM;

#20040910
#Pinned tasks
DROP TABLE IF EXISTS `user_task_pin`;
CREATE TABLE `user_task_pin` (
`user_id` int(11) NOT NULL default '0',
`task_id` int(10) NOT NULL default '0',
`task_pinned` tinyint(2) NOT NULL default '1',
PRIMARY KEY (`user_id`,`task_id`)
) TYPE=MyISAM;

#
# Table structure for table `config`
#
# Creation: Feb 23, 2005 at 01:26 PM
# Last update: Feb 24, 2005 at 02:15 AM
#

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `config_id` int(11) NOT NULL auto_increment,
  `config_name` varchar(255) NOT NULL default '',
  `config_value` varchar(255) NOT NULL default '',
  `config_group` varchar(255) NOT NULL default '',
  `config_type` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`config_id`),
  UNIQUE KEY `config_name` (`config_name`)
) TYPE=MyISAM AUTO_INCREMENT=47 ;

#
# Dumping data for table `config`
#

INSERT INTO `config` VALUES ('', 'host_locale', 'en', '', 'text');
INSERT INTO `config` VALUES ('', 'currency_symbol', '&#8364;', '', 'text');
INSERT INTO `config` VALUES ('', 'host_style', 'default', '', 'text');
INSERT INTO `config` VALUES ('', 'organization_name', 'My Organization', '', 'text');
INSERT INTO `config` VALUES ('', 'page_title', 'PMango', '', 'text');
INSERT INTO `config` VALUES ('', 'site_domain', 'penelope.di.unipi.it', '', 'text');
INSERT INTO `config` VALUES ('', 'email_prefix', '[pmango]', '', 'text');
INSERT INTO `config` VALUES ('', 'admin_username', 'admin', '', 'text');
INSERT INTO `config` VALUES ('', 'username_min_len', '4', '', 'text');
INSERT INTO `config` VALUES ('', 'password_min_len', '4', '', 'text');
INSERT INTO `config` VALUES ('', 'effort_perc', 'true', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'enable_gantt_charts', 'true', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'jpLocale', '', '', 'text');
INSERT INTO `config` VALUES ('', 'log_changes', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'check_tasks_dates', 'true', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'locale_warn', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'locale_alert', '^', '', 'text');
INSERT INTO `config` VALUES ('', 'display_debug', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'link_tickets_kludge', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'show_all_task_assignees', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'direct_edit_assignment', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'restrict_color_selection', 'false', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'cal_day_view_show_minical', 'true', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'cal_day_start', '9', '', 'text');
INSERT INTO `config` VALUES ('', 'cal_day_end', '17', '', 'text');
INSERT INTO `config` VALUES ('', 'cal_day_increment', '15', '', 'text');
INSERT INTO `config` VALUES ('', 'cal_working_days', '1,2,3,4,5,6,0', '', 'text');
INSERT INTO `config` VALUES ('', 'default_view_m', '', '', 'text');
INSERT INTO `config` VALUES ('', 'default_view_a', '', '', 'text');
INSERT INTO `config` VALUES ('', 'default_view_tab', '1', '', 'text');
INSERT INTO `config` VALUES ('', 'index_max_file_size', '-1', '', 'text');
INSERT INTO `config` VALUES ('', 'session_handling', 'app', 'session', 'select');
INSERT INTO `config` VALUES ('', 'session_idle_time', '1h', 'session', 'text');
INSERT INTO `config` VALUES ('', 'session_max_lifetime', '1d', 'session', 'text');
INSERT INTO `config` VALUES ('', 'debug', '1', '', 'text');
INSERT INTO `config` VALUES ('', 'parser_default', '/usr/bin/strings', '', 'text');
INSERT INTO `config` VALUES ('', 'parser_application/msword', '/usr/bin/strings', '', 'text');
INSERT INTO `config` VALUES ('', 'parser_text/html', '/usr/bin/strings', '', 'text');
INSERT INTO `config` VALUES ('', 'parser_application/pdf', '/usr/bin/pdftotext', '', 'text');

INSERT INTO `config` VALUES ('', 'files_ci_preserve_attr', 'true', '', 'checkbox');
INSERT INTO `config` VALUES ('', 'files_show_versions_edit', 'false', '', 'checkbox');
INSERT INTO `config` ( `config_id` , `config_name` , `config_value` , `config_group` , `config_type` )
VALUES ('', 'reset_memory_limit', '128M', '', 'text');

# 20050302
# ldap system config variables
INSERT INTO `config` VALUES ('', 'auth_method', 'sql', 'auth', 'select'); 
INSERT INTO `config` VALUES ('', 'ldap_host', 'localhost', '', 'text'); 
INSERT INTO `config` VALUES ('', 'ldap_port', '389', '', 'text'); 
INSERT INTO `config` VALUES ('', 'ldap_version', '3', '', 'text'); 
INSERT INTO `config` VALUES ('', 'ldap_base_dn', 'dc=saki,dc=com,dc=au', '', 'text'); 
INSERT INTO `config` VALUES ('', 'ldap_user_filter', '(uid=%USERNAME%)', '', 'text'); 

# 20050302
# PostNuke authentication variables
INSERT INTO config VALUES ('', 'postnuke_allow_login', 'true', 'auth', 'checkbox');

INSERT INTO `config` VALUES ('', 'width_project_img', '640', '', 'text');
INSERT INTO `config` VALUES ('', 'height_project_img', '480', '', 'text');

# 20050302
# New list support for config variables

DROP TABLE IF EXISTS `config_list`;
CREATE TABLE `config_list` (
`config_list_id` integer not null auto_increment,
`config_id` integer not null default 0,
`config_list_name` varchar(30) not null default '',
PRIMARY KEY(`config_list_id`),
KEY(`config_id`)
);

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'sql'
	FROM `config`
	WHERE `config_name` = 'auth_method';

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'ldap'
	FROM `config`
	WHERE `config_name` = 'auth_method';

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'pn'
	FROM `config`
	WHERE `config_name` = 'auth_method';

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'app'
	FROM `config`
	WHERE `config_name` = 'session_handling';

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'php'
	FROM `config`
	WHERE `config_name` = 'session_handling';

# 20050303
# New mail handling options
INSERT INTO `config` VALUES (NULL, 'mail_transport', 'php', 'mail', 'select');
INSERT INTO `config` VALUES (NULL, 'mail_host', 'localhost', 'mail', 'text');
INSERT INTO `config` VALUES (NULL, 'mail_port', '25', 'mail', 'text');
INSERT INTO `config` VALUES (NULL, 'mail_auth', 'false', 'mail', 'checkbox');
INSERT INTO `config` VALUES (NULL, 'mail_user', '', 'mail', 'text');
INSERT INTO `config` VALUES (NULL, 'mail_pass', '', 'mail', 'text');
INSERT INTO `config` VALUES (NULL, 'mail_defer', 'false', 'mail', 'checkbox');
INSERT INTO `config` VALUES (NULL, 'mail_timeout', '30', 'mail', 'text');

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'php'
	FROM `config`
	WHERE `config_name` = 'mail_transport';

INSERT INTO `config_list` (`config_id`, `config_list_name`)
  SELECT `config_id`, 'smtp'
	FROM `config`
	WHERE `config_name` = 'mail_transport';

# 20050303
# Queue scanning on garbage collection
INSERT INTO `config` VALUES (NULL, 'session_gc_scan_queue', 'false', 'session', 'checkbox');

# 20050302
# new custom fields

DROP TABLE IF EXISTS `custom_fields_struct`;
CREATE TABLE `custom_fields_struct` (
`field_id` integer primary key,
`field_module` varchar(30),
`field_page` varchar(30),
`field_htmltype` varchar(20),
`field_datatype` varchar(20),
`field_order` integer,
`field_name` varchar(100),
`field_extratags` varchar(250),
`field_description` varchar(250)
);


DROP TABLE IF EXISTS `custom_fields_values`;
CREATE TABLE `custom_fields_values` (
`value_id` integer,
`value_module` varchar(30),
`value_object_id` integer,
`value_field_id` integer,
`value_charvalue` varchar(250),
`value_intvalue` integer
);

DROP TABLE IF EXISTS `custom_fields_lists`;
CREATE TABLE `custom_fields_lists` (
`field_id` integer,
`list_option_id` integer,
`list_value` varchar(250)
);


#20040920
# ACL support.
#
# Table structure for table `g<br>_acl`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 02:15 PM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_acl`;
CREATE TABLE `gacl_acl` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default 'system',
  `allow` int(11) NOT NULL default '0',
  `enabled` int(11) NOT NULL default '0',
  `return_value` longtext,
  `note` longtext,
  `updated_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `gacl_enabled_acl` (`enabled`),
  KEY `gacl_section_value_acl` (`section_value`),
  KEY `gacl_updated_date_acl` (`updated_date`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_acl_sections`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 22, 2004 at 01:04 PM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_acl_sections`;
CREATE TABLE `gacl_acl_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_acl_sections` (`value`),
  KEY `gacl_hidden_acl_sections` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aco`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 11:23 AM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_aco`;
CREATE TABLE `gacl_aco` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_aco` (`section_value`,`value`),
  KEY `gacl_hidden_aco` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aco_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 02:15 PM
#

DROP TABLE IF EXISTS `gacl_aco_map`;
CREATE TABLE `gacl_aco_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aco_sections`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 23, 2004 at 08:14 AM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_aco_sections`;
CREATE TABLE `gacl_aco_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_aco_sections` (`value`),
  KEY `gacl_hidden_aco_sections` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aro`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 29, 2004 at 11:38 AM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_aro`;
CREATE TABLE `gacl_aro` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_aro` (`section_value`,`value`),
  KEY `gacl_hidden_aro` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aro_groups`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 12:12 PM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_aro_groups`;
CREATE TABLE `gacl_aro_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`,`value`),
  KEY `gacl_parent_id_aro_groups` (`parent_id`),
  KEY `gacl_value_aro_groups` (`value`),
  KEY `gacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aro_groups_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 12:26 PM
#

DROP TABLE IF EXISTS `gacl_aro_groups_map`;
CREATE TABLE `gacl_aro_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aro_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 29, 2004 at 11:33 AM
#

DROP TABLE IF EXISTS `gacl_aro_map`;
CREATE TABLE `gacl_aro_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_aro_sections`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 22, 2004 at 03:04 PM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_aro_sections`;
CREATE TABLE `gacl_aro_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_aro_sections` (`value`),
  KEY `gacl_hidden_aro_sections` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_axo`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 26, 2004 at 06:23 PM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_axo`;
CREATE TABLE `gacl_axo` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_section_value_value_axo` (`section_value`,`value`),
  KEY `gacl_hidden_axo` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_axo_groups`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 26, 2004 at 11:00 AM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_axo_groups`;
CREATE TABLE `gacl_axo_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`id`,`value`),
  KEY `gacl_parent_id_axo_groups` (`parent_id`),
  KEY `gacl_value_axo_groups` (`value`),
  KEY `gacl_lft_rgt_axo_groups` (`lft`,`rgt`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_axo_groups_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 11:24 AM
#

DROP TABLE IF EXISTS `gacl_axo_groups_map`;
CREATE TABLE `gacl_axo_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_axo_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 28, 2004 at 02:15 PM
#

DROP TABLE IF EXISTS `gacl_axo_map`;
CREATE TABLE `gacl_axo_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(80) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_axo_sections`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 23, 2004 at 03:50 PM
# Last check: Jul 22, 2004 at 01:00 PM
#

DROP TABLE IF EXISTS `gacl_axo_sections`;
CREATE TABLE `gacl_axo_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(80) NOT NULL default '',
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL default '',
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `gacl_value_axo_sections` (`value`),
  KEY `gacl_hidden_axo_sections` (`hidden`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_groups_aro_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 29, 2004 at 11:38 AM
#

DROP TABLE IF EXISTS `gacl_groups_aro_map`;
CREATE TABLE `gacl_groups_aro_map` (
  `group_id` int(11) NOT NULL default '0',
  `aro_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`aro_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_groups_axo_map`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 26, 2004 at 11:01 AM
#

DROP TABLE IF EXISTS `gacl_groups_axo_map`;
CREATE TABLE `gacl_groups_axo_map` (
  `group_id` int(11) NOT NULL default '0',
  `axo_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `gacl_phpgacl`
#
# Creation: Jul 22, 2004 at 01:00 PM
# Last update: Jul 22, 2004 at 01:03 PM
#

DROP TABLE IF EXISTS `gacl_phpgacl`;
CREATE TABLE `gacl_phpgacl` (
  `name` varchar(230) NOT NULL default '',
  `value` varchar(230) NOT NULL default '',
  PRIMARY KEY  (`name`)
) TYPE=MyISAM;

INSERT INTO `gacl_phpgacl` (name, value) VALUES ('version', '3.3.2');
INSERT INTO `gacl_phpgacl` (name, value) VALUES ('schema_version', '2.1');

INSERT INTO `gacl_acl_sections` (id, value, order_value, name) VALUES (1, 'system', 1, 'System');
INSERT INTO `gacl_acl_sections` (id, value, order_value, name) VALUES (2, 'user', 2, 'User');


#
# Table structure for table `sessions`
#

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
	`session_id` varchar(40) NOT NULL default '',
	`session_user` INT DEFAULT '0' NOT NULL,
	`session_data` LONGBLOB,
	`session_updated` TIMESTAMP,
	`session_created` DATETIME NOT NULL default '0000-00-00 00:00:00',
	PRIMARY KEY (`session_id`),
	KEY (`session_updated`),
	KEY (`session_created`)
) TYPE=MyISAM;

# 20050304
# Version tracking table.  From here on in all updates are done via the installer,
# which uses this table to manage the upgrade process.

DROP TABLE IF EXISTS `dpversion`;
CREATE TABLE `dpversion` (
	`code_version` varchar(10) not null default '',
	`db_version` integer not null default '0',
	`last_db_update` date not null default '0000-00-00',
	`last_code_update` date not null default '0000-00-00'
);

INSERT INTO dpversion VALUES ('2.0', 2, '2006-02-20', '2005-12-30');

# 20050307
# Additional LDAP search user and search password fields for Active Directory compatible LDAP authentication
INSERT INTO `config` VALUES ('', 'ldap_search_user', 'Manager', 'ldap', 'text');
INSERT INTO `config` VALUES ('', 'ldap_search_pass', 'secret', 'ldap', 'text');
INSERT INTO `config` VALUES ('', 'ldap_allow_login', 'true', 'ldap', 'checkbox');

