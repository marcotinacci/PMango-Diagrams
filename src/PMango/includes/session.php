<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      session

 File:       session.php
 Location:   pmango\includes
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       php

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2007.06.16 Lorenzo
   Second version, modified to guarantee compatibility with php > 5.2.0 and its new session type configuration.
 - 2006.07.18 Lorenzo
   First version, unmodified from dotProject 2.0.1.

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
##
## Session Handling Functions
##
/*
* Please note that these functions assume that the database
* is accessible and that a table called 'sessions' (with a prefix
* if necessary) exists.  It also assumes MySQL date and time
* functions, which may make it less than easy to port to
* other databases.  You may need to use less efficient techniques
* to make it more generic.
*
* NOTE: index.php and fileviewer.php MUST call dPsessionStart
* instead of trying to set their own sessions.
*/

require_once $baseDir . '/includes/main_functions.php';
require_once $baseDir . '/includes/db_adodb.php';
require_once $baseDir . '/includes/db_connect.php';
require_once $baseDir . '/classes/query.class.php';
require_once $baseDir . '/classes/ui.class.php';
require_once $baseDir . '/classes/event_queue.class.php';

function dPsessionOpen($save_path, $session_name)
{
	return true;
}

function dPsessionClose()
{
	return true;
}

function dPsessionRead($id)
{
	$q  = new DBQuery;
	$q->addTable('sessions');
	$q->addQuery('session_data');
	$q->addQuery('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) as session_lifespan');
	$q->addQuery('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) as session_idle');
	$q->addWhere("session_id = '$id'");
	$qid =& $q->exec();
	if (! $qid || $qid->EOF ) {
		dprint(__FILE__, __LINE__, 11, "Failed to retrieve session $id");
		$data =  "";
	} else {
		$max = dPsessionConvertTime('max_lifetime');
		$idle = dPsessionConvertTime('idle_time');
		dprint(__FILE__, __LINE__, 11, "Found session $id, max=$max/" . $qid->fields['session_lifespan']
		. ", idle=$idle/" . $qid->fields['session_idle']);
		// If the idle time or the max lifetime is exceeded, trash the
		// session.
		if ($max < $qid->fields['session_lifespan']
		 || $idle < $qid->fields['session_idle']) {
			dprint(__FILE__, __LINE__, 11, "session $id expired");
			dPsessionDestroy($id);
			$data = '';
		} else {
			$data = $qid->fields['session_data'];
		}
	}
	$q->clear();
	return $data;
}

function dPsessionWrite($id, $data)
{
    global $AppUI;
    
	$q = new DBQuery;
	$q->addQuery('count(*) as row_count');
	$q->addTable('sessions');
	$q->addWhere("session_id = '$id'");

	if ( $qid =& $q->exec() 
	&& ( @$qid->fields['row_count'] > 0 || @$qid->fields[0] > 0) ) {
		dprint(__FILE__, __LINE__, 11, "Updating session $id");
		$q->query = null;
		$q->addUpdate('session_data', $data);
        if (isset($AppUI))
            $q->addUpdate('session_user', $AppUI->last_insert_id);
	} else {
		dprint(__FILE__, __LINE__, 11, "Creating new session $id");
		$q->query = null;
		$q->where = null;
		$q->addInsert('session_id', $id);
		$q->addInsert('session_data', $data);
		$q->addInsert('session_created', date('Y-m-d H:i:s'));
	}
	$q->exec();
	$q->clear();
	return true;
}

function dPsessionDestroy($id, $user_access_log_id=0) {
 	global $AppUI;
    
    if(!($user_access_log_id) && isset($AppUI->last_insert_id)){
        $user_access_log_id = $AppUI->last_insert_id;
    }
    
	dprint(__FILE__, __LINE__, 11, "Killing session $id");
	$q = new DBQuery;
	$q->setDelete('sessions');
	$q->addWhere("session_id = '$id'");
	$q->exec();
	$q->clear();
    
	if ($user_access_log_id) {
 		$q->addTable('user_access_log');
 		$q->addUpdate('date_time_out', date('Y-m-d H:i:s'));
		$q->addWhere('user_access_log_id = ' . $user_access_log_id);
 		$q->exec();
 		$q->clear();
 	}
    
	return true;
}

function dPsessionGC($maxlifetime)
{
	global $AppUI;

	dprint(__FILE__, __LINE__, 11, 'Session Garbage collection running');
	$now = time();
	$max = dPsessionConvertTime('max_lifetime');
	$idle = dPsessionConvertTime('idle_time');
	// Find all the session
	$q = new DBQuery;
	$q->setDelete('sessions');
	$q->addWhere("UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) > $idle OR UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) > $max");
	$q->exec();
	$q->clear();
	if (dPgetConfig('session_gc_scan_queue')) {
		// We need to scan the event queue.  If $AppUI isn't created yet
		// And it isn't likely that it will be, we create it and run the
		// queue scanner.
		if (! isset($AppUI)) {
			$AppUI = new CAppUI;
			$queue = new EventQueue;
			$queue->scan();
		}
	}
	return true;
}

function dPsessionConvertTime($key)
{
	$key = 'session_' . $key;

	// If the value isn't set, then default to 1 day.
	if (dPgetConfig($key) == null || dPgetConfig($key) == null)
		return 86400;

	$numpart = (int) dPgetConfig($key);
	$modifier = substr(dPgetConfig($key), -1);
	if (! is_numeric($modifier)) {
		switch ($modifier) {
			case 'h':
				$numpart *= 3600;
				break;
			case 'd':
				$numpart *= 86400;
				break;
			case 'm':
				$numpart *= (86400 * 30);
				break;
			case 'y':
				$numpart *= (86400 * 365);
				break;
		}
	}
	return $numpart;
}

function dpSessionStart($start_vars = 'AppUI')
{
	session_name('dotproject');
	if (ini_get('session.auto_start') > 0) {
		session_write_close();
	}
	if (dPgetConfig('session_handling') == 'app') 
	{
		ini_set('session.save_handler', 'user');
	// PHP 5.2 workaround
    if (version_compare(phpversion(), '5.0.0', '>=')) {
        register_shutdown_function('session_write_close');
    } 
		session_set_save_handler(
			'dPsessionOpen', 
			'dPsessionClose', 
			'dPsessionRead', 
			'dPsessionWrite', 
			'dPsessionDestroy', 
			'dPsessionGC');
		$max_time = dPsessionConvertTime('max_lifetime');
	} else {
		$max_time = 0; // Browser session only.
	}
	// Try and get the correct path to the base URL.
	preg_match('_^(https?://)([^/]+)(:0-9]+)?(/.*)?$_i', dPgetConfig('base_url'), $url_parts);
	$cookie_dir = $url_parts[4];
	if (substr($cookie_dir, 0, 1) != '/')
		$cookie_dir = '/' . $cookie_dir;
	if (substr($cookie_dir, -1) != '/')
		$cookie_dir .= '/';
	session_set_cookie_params($max_time, $cookie_dir);
	session_start();
	if (is_array($start_vars)) {
		foreach ($start_vars as $var) {
			session_register($var);
		}
	} else if (! empty($start_vars)) {
		session_register($start_vars);
	}
}

// vi:ai sw=2 ts=2:
// vim600:ai sw=2 ts=2 fdm=marker:
?>
