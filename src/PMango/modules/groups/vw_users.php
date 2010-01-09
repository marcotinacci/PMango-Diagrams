<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view group user

 File:       vw_users.php
 Location:   pmango\modules\groups
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango groups.
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
##
##	Companies: View User sub-table
##

GLOBAL $AppUI, $company_id;

$q  = new DBQuery;
$q->addTable('users','u');
$q->addQuery('u.user_id, u.user_username, u.user_first_name, u.user_last_name');
$q->addJoin('user_setcap', 'us', 'u.user_id = us.user_id');
$q->addWhere('us.group_id = '.$company_id);
$q->addOrder('u.user_last_name'); 

if (!($rows = $q->loadList())) {
	echo $AppUI->_('No data available').'<br />'.$AppUI->getMsg();
} else {
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th><?php echo $AppUI->_( 'Username' );?></td>
	<th><?php echo $AppUI->_( 'Name' );?></td>
</tr>
<?php
$s = '';
foreach ($rows as $row){
	$s .= '<tr><td>';
	$s .= '<a href="./index.php?m=admin&a=viewuser&user_id='.$row["user_id"].'">'.$row["user_username"].'</a>';
	$s .= '<td>'.$row["user_last_name"].", ".$row["user_first_name"].'</td>'; 
	$s .= '</tr>';
}
echo $s;
?>
</table>
<?php } ?>
