<?php /** ---------------------------------------------------------------------------

 PMango Project

 Title:      upgrade latest

 File:       upgrade_latest.php
 Location:   pmango/db
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       php

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
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

---------------------------------------------------------------------------*/
global $baseDir;

if (! isset($baseDir)) {
	die("You must not call this file directly, it is run automatically on install/upgrade");
}
include_once "$baseDir/includes/config.php";
include_once "$baseDir/includes/main_functions.php";
require_once "$baseDir/includes/db_adodb.php";
include_once "$baseDir/includes/db_connect.php";
include_once "$baseDir/install/install.inc.php";
require_once "$baseDir/classes/permissions.class.php";

/**
 * DEVELOPERS PLEASE NOTE:
 *
 * For the new upgrader/installer to work, this code must be structured
 * correctly.  In general if there is a difference between the from
 * version and the to version, then all updates should be performed.
 * If the $last_udpated is set, then a partial update is required as this
 * is a CVS update.  Make sure you create a new case block for any updates
 * that you require, and set $latest_update to the date of the change.
 *
 * Each case statement should fall through to the next, so that the
 * complete update is run if the last_updated is not set.
 */
function dPupgrade($from_version, $to_version, $last_updated)
{

	global $baseDir;
	$latest_update = '20050409'; // Set to the latest upgrade date.

	if (! $last_updated)
		$last_updated = '00000000';
	
	// Place the upgrade code here, depending on the last_updated date.
	return $latest_update;
}

?>
