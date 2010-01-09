<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view PMango groups

 File:       vw_companies.php
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
global $search_string;
global $owner_filter_id;
global $currentTabId;
global $currentTabName;
global $tabbed;
global $type_filter;
global $orderby;
global $orderdir;

// load the group types

$types = dPgetSysVal( 'GroupType' );
// get any records denied from viewing

$obj = new CGroup();//print_r( $obj->group_id);
$allowedCompanies = $obj->getAllowedRecords($AppUI->user_id, 'group_id, group_name');

$company_type_filter = $currentTabId;
//Not Defined
$companiesType = true;
if ($currentTabName == "All Groups")
	$companiesType = false;
if ($currentTabName == "Not Applicable")
	$company_type_filter = 0;

// retrieve list of records
$q  = new DBQuery;
$q->addTable('groups', 'g');
$q->addQuery('g.group_id, g.group_name, g.group_type, g.group_description, count(distinct p.project_id) as countp, count(distinct p2.project_id) as inactive, user_first_name, user_last_name');
$q->addJoin('projects', 'p', 'g.group_id = p.project_group AND p.project_active <> 0');
$q->addJoin('users', 'u', 'g.group_owner = u.user_id');
$q->addJoin('projects', 'p2', 'g.group_id = p2.project_group AND p2.project_active = 0');
if (count($allowedCompanies) > 0) { $q->addWhere('g.group_id IN (' . implode(',', array_keys($allowedCompanies)) . ')'); } else $q->addWhere('0');
if ($companiesType) { $q->addWhere('g.group_type = '.$company_type_filter); }
if ($search_string != "") { $q->addWhere("g.group_name LIKE '%$search_string%'"); }
if ($owner_filter_id > 0) { $q->addWhere("g.group_owner = $owner_filter_id "); }
$q->addGroup('g.group_id');
$q->addOrder($orderby.' '.$orderdir);
$rows = $q->loadList();
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<td nowrap="nowrap" width="60" align="right">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
	<th nowrap="nowrap">
		<a href="?m=groups&orderby=group_name" class="hdr"><?php echo $AppUI->_('Group Name');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=groups&orderby=countp" class="hdr"><?php echo $AppUI->_('Active Projects');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=groups&orderby=inactive" class="hdr"><?php echo $AppUI->_('Archived Projects');?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=groups&orderby=group_type" class="hdr"><?php echo $AppUI->_('Type');?></a>
	</th>
</tr>
<?php
$s = '';
$CR = "\n"; // Why is this needed as a variable?

$none = true;
foreach ($rows as $row) {
	$none = false;
	$s .= $CR . '<tr>';
	$s .= $CR . '<td>&nbsp;</td>';
	$s .= $CR . '<td><a href="./index.php?m=groups&a=view&group_id=' . $row["group_id"] . '" title="'.$row['group_description'].'">' . $row["group_name"] .'</a></td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . $row["countp"] . '</td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . @$row["inactive"] . '</td>';
	$s .= $CR . '<td width="125" align="center" nowrap="nowrap">' . $AppUI->_($types[@$row["group_type"]]) . '</td>';
	$s .= $CR . '</tr>';
}
echo "$s\n";
if ($none) {
	echo $CR . '<tr><td colspan="5">' . $AppUI->_( 'No groups available' ) . '</td></tr>';
}
?>
</table>
