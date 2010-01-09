<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view group active projects

 File:       vw_active.php
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
##	groups: View Projects sub-table
##

GLOBAL $AppUI, $company_id, $pstatus, $dPconfig;

$sort = dPgetParam($_GET, 'sort', 'project_name');
if ($sort == 'project_priority')
        $sort .= ' DESC';

$df = $AppUI->getPref('SHDATEFORMAT');

$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('project_id, project_name, project_start_date, project_finish_date, project_status, project_target_budget,
	project_start_date, project_priority');
$q->addWhere('projects.project_group = '.$company_id);
$q->addWhere('projects.project_active <> 0');
$q->addOrder($sort);
$s = '';

if (!($rows = $q->loadList())) {
	$s .= $AppUI->_( 'No data available' ).'<br />'.$AppUI->getMsg();
} else {
	$s .= '<tr>';
	$s .= '<th width ="90"><a style="color:white" href="index.php?m=groups&a=view&group_id='.$company_id.'&sort=project_priority">'.$AppUI->_('P').'</a></th>'
        .'<th width ="100%"><a style="color:white" href="index.php?m=groups&a=view&group_id='.$company_id.'&sort=project_name">'.$AppUI->_( 'Name' ).'</a></th>'
		.'<th width ="90" nowrap>'.$AppUI->_( 'Start Date' ).'</th>'
		.'<th width ="90" nowrap>'.$AppUI->_( 'Finish Date' ).'</th>'
		.'<th width ="90" nowrap>'.$AppUI->_( 'Target Budget' ).'</th>'
		.'<th width ="90" nowrap>'.$AppUI->_( 'Status' ).'</th>'
		.'</tr>';
	foreach ($rows as $row) {
		$start_date = new CDate( $row['project_start_date'] );
		$finish_date = new CDate( $row['project_finish_date'] );
		$s .= '<tr>';
                $s .= '<td>';
                if ($row['project_priority'] < 0 ) {
                        $s .= "<img src='./images/icons/low.gif' width=13 height=16>";
                } else if ($row["project_priority"] > 0) {
                        $s .= "<img src='./images/icons/" . $row["project_priority"] .".gif' width=13 height=16>";
}

                $s .= '</td>';
		$s .= '<td width="100%">';
		$s .= '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a></td>';
		$s .= '<td nowrap="nowrap" align="center">'.$start_date->format( $df ).'</td>';
		$s .= '<td nowrap="nowrap" align="center">'.$finish_date->format( $df ).'</td>';
		$s .= '<td nowrap="nowrap" align="right">'.$row["project_target_budget"]." ".$dPconfig["currency_symbol"].'</td>';
		$s .= '<td nowrap="nowrap" align="center">'.$AppUI->_($pstatus[$row["project_status"]]).'</td>';
		$s .= '</tr>';
	}
}
echo '<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">' . $s . '</table>';
?>
