<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view inactive user

 File:       vw_inactive_usr.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango users.
 - 2006.07.26 Lorenzo
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

GLOBAL $dPconfig, $canEdit, $stub, $where, $orderby;

$q  = new DBQuery;
$q->addTable('users', 'u');
$q->addQuery('DISTINCT(user_id), user_username, user_last_name, user_first_name, user_email');
//$q->addJoin('users', 'con', 'user_user = user_id');
//$q->addJoin('companies', 'com', 'user_company = company_id');
//$q->addJoin('permissions', 'per', 'user_id = permission_user');


if ($stub) {
	$q->addWhere("(UPPER(user_username) LIKE '$stub%' or UPPER(user_first_name) LIKE '$stub%' OR UPPER(user_last_name) LIKE '$stub%')");
} else if ($where) {
	$where = $q->quote("%$where%");
	$q->addWhere("(UPPER(user_username) LIKE $where or UPPER(user_first_name) LIKE $where OR UPPER(user_last_name) LIKE $where)");
}

$q->addOrder($orderby);
$users = $q->loadList();
$canLogin = false;

require "{$dPconfig['root_dir']}/modules/admin/vw_usr.php";
?>
