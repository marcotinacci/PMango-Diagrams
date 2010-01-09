<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view all projects

 File:       vw_idx_all.php
 Location:   PMango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
   Third version, modified to insert Project Reports link. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage Mango list of projects.
 - 2006.07.30 Lorenzo
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

GLOBAL $AppUI, $projects, $company_id, $pstatus, $project_types, $currentTabId, $currentTabName;

$check = $AppUI->_('All Projects', UI_OUTPUT_RAW);
$show_all_projects = false;
if ( stristr($currentTabName, $check) !== false)
	$show_all_projects = true;

//$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
	// Let's check if the user submited the change status form
	
?>
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
</tr>
<tr>
    <th nowrap="nowrap">
        <?php echo $AppUI->_('Color');?>
    </th>
	<th nowrap="nowrap">
		
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=project_name" class="hdr"><?php echo $AppUI->_('Project Name');?></a>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Creator');?>
	</th>
    <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_start_date" class="hdr"><?php echo $AppUI->_('Start Date');?></a>
	</th>
    <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_finish_date" class="hdr"><?php echo $AppUI->_('Finish Date');?></a>
	</th>
    <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_effort" class="hdr"><?php echo $AppUI->_('Effort');?></a>
	</th>
    <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_target_budget" class="hdr"><?php echo $AppUI->_('Target Budget');?></a>
	</th>
	<?php
	if($show_all_projects){
		?>
		<th nowrap="nowrap">
			<?php echo $AppUI->_('Status'); ?>
		</th>
		<?php
	}
	?>
</tr>

<?php
$CR = "\n";
$CT = "\n\t";
$none = true;

//Tabbed view
$project_status_filter = $currentTabId;
//Project not defined
if ($currentTabId == count($project_types)-1)
	$project_status_filter = 0;

foreach ($projects as $row) {
	/*if (! $perms->checkModuleItem('projects', 'view', $row['project_id'])) {//non importa doverli rifiltrare... projects è già filtrato
		continue;
	}*/
	if ($show_all_projects ||
	    ($row["project_active"] > 0 && $row["project_status"] == $project_status_filter)) {
		$none = false;
		$style = $row['project_current'] != '0'? 'background-color:#CEFFFF' :'';
        $start_date = intval( @$row["project_start_date"] ) ? new CDate( $row["project_start_date"] ) : null;
		$end_date = intval( @$row["project_finish_date"] ) ? new CDate( $row["project_finish_date"] ) : null;

		$s = '<tr>';
		$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#'
			. $row["project_color_identifier"] . '">';
		$s .= $CT . '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
			. sprintf( "%.1f%%", CProject::getPr($row['project_id'] ))
			. '</font>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="center" nowrap style='.$style.'><a href='.'?m=report&a=view&project_id='. $row["project_id"] .'><img src="./modules/report/images/report.gif" border="0" title="Project Report"></a></td>';
		$s .= $CR . '<td align="left" width="100%" style='.$style.'>';
		$s .= $CT . '<a href="?m=projects&a=view&project_id=' . $row["project_id"] . '" title="' . htmlspecialchars( $row["project_description"], ENT_QUOTES ) . '">' . htmlspecialchars( $row["project_name"], ENT_QUOTES ) . '</a>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="left" nowrap style='.$style.'>' . htmlspecialchars( $row["user_username"], ENT_QUOTES ) . '</td>';
        $s .= $CR . '<td align="center" width="90" nowrap style='.$style.'>'. ($start_date ? $start_date->format( $df ) : '-') .'</td>';
        $s .= $CR . '<td align="center" width="90" nowrap style='.$style.'>'. ($end_date ? $end_date->format( $df ) : '-') .'</td>';
     
        $s .= $CR . '<td align="right" width="90" nowrap style='.$style.'>';
        $s .= $CR . $row['project_effort']." ph";
        $s .= $CR . '</td>';
		$s .= $CR . '<td align="right" width="90" nowrap style='.$style.'>';
        $s .= $CR . $row['project_target_budget']." ".$dPconfig['currency_symbol'];
        $s .= $CR . '</td>';

		if($show_all_projects){
			$s .= $CR . '<td align="center" width="90" nowrap style='.$style.'>';$ptypes = dPgetSysVal("ProjectStatus");
			$s .= $CT . $row["project_status"] == 0 ? $AppUI->_('Not Defined') : $ptypes[$row["project_status"]];
			$s .= $CR . '</td>';
		}
		
		$s .= $CR . '</tr>';
		echo $s;
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="10">' . $AppUI->_( 'No projects available' ) . '</td></tr>';
}
?>
</table>
