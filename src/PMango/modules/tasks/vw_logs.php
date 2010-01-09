<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view logs.

 File:       vw_logs.php
 Location:   PMango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 Version history.
 - 2007.05.08 Riccardo
   Third version, modified to create PDF reports. 
 - 2006.07.30 Lorenzo
   Second version, modified to view Mango logs.
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

global $AppUI, $task_id, $canEdit, $m;

if (!$task_id > 0)
	$AppUI->redirect("m=public&a=access_denied");
	
$q = new DBQuery();
$q->addQuery('project_group, project_current');
$q->addTable('projects');
$q->addJoin('tasks','','task_project=project_id');
$q->addWhere('task_id ='.$task_id);
$ar=$q->loadList();
$prg = $ar[0]['project_group'];
$curPr = $ar[0]['project_current'] == '0';

$perms =& $AppUI->acl();
if (! $perms->checkModule('task_log', 'view', '', intval($prg),1)) {
	$AppUI->redirect("m=public&a=access_denied");
}

$df1 = $AppUI->getPref('SHDATEFORMAT'); 
$df .= $df1. " " . $AppUI->getPref('TIMEFORMAT');
$problem = intval( dPgetParam( $_GET, 'problem', null ) );
// get sysvals
$taskLogReference = dPgetSysVal( 'TaskLogReference' );
$taskLogReferenceImage = dPgetSysVal( 'TaskLogReferenceImage' );
$canDelete = $perms->checkModule('task_log', 'delete', '', intval($prg),1) && $curPr;
?>
<script language="JavaScript">
<?php 
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions

if ($canDelete) {
?>
function delIt2(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Task Log', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<form name="frmDelete2" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_updatetask">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>

<tr>
	<th></th>
	<th><?php echo $AppUI->_('Pr.');?></th>
	<th><?php echo $AppUI->_('Dates');?></th>
	<th><?php echo $AppUI->_('WBS');?></th>
	<th nowrap><?php echo $AppUI->_('Task Log Name');?></th>
	<th><?php echo $AppUI->_('Worker');?></th>
	<th><?php echo $AppUI->_('Role');?></th>
	<th><?php echo $AppUI->_('Effort');?></th>
	<th><?php echo $AppUI->_('Cost');?></th>
	<th width="100%"><?php echo $AppUI->_('Comments');?></th>
	<th></th>
</tr>
<?php
// Pull the task comments
$sql = "
SELECT task_log.*, CONCAT_WS(' ',user_first_name,user_last_name) as user_username, proles_name, proles_hour_cost
FROM task_log
LEFT JOIN users ON user_id = task_log_creator
LEFT JOIN project_roles ON proles_id = task_log_proles_id
WHERE task_log_task = $task_id". ($problem ? " AND task_log_problem > '0'" : '') .
" ORDER BY task_log_creation_date
";
$logs = db_loadList( $sql );

$s = '';
$canEd = $perms->checkModule('task_log', 'edit', '', intval($prg),1) && $curPr;
foreach ($logs as $row) {
	$task_log_date = intval( $row['task_log_creation_date'] ) ? new CDate( $row['task_log_creation_date'] ) : null;
	$task_log_edit_date = intval( $row['task_log_edit_date'] ) ? new CDate( $row['task_log_edit_date'] ) : null;
	$task_log_start_date = intval( $row['task_log_start_date'] ) ? new CDate( $row['task_log_start_date'] ) : null;
	$task_log_finish_date = intval( $row['task_log_finish_date'] ) ? new CDate( $row['task_log_finish_date'] ) : null;
    $style = $row['task_log_problem'] ? 'background-color:#cc6666;color:#ffffff' :'';
    $style2 = ($task_log_date != $task_log_edit_date) ? $style2='background-color:#ffff00;' : '';
	$s .= '<tr bgcolor="white" valign="center">';
	$s .= "\n\t<td>";
	if ($canEd && $AppUI->user_id == $row['task_log_creator']) {
		if ($tab == -1) {
		 	$s .= "\n\t\t<a href=\"?m=tasks&a=view&task_id=$task_id&tab=".$AppUI->getState( 'TaskLogVwTab' );
		} else {
			$s .= "\n\t\t<a href=\"?m=tasks&a=view&task_id=$task_id&tab=1";

		}
		 $s .= "&task_log_id=".@$row['task_log_id']."#log\">"
			. "\n\t\t\t". dPshowImage( './images/icons/stock_edit-16.png', 16, 16, '' )
			. "\n\t\t</a>";
	}
        $s .= "\n\t</td>";
    $date1 = ($task_log_date ? $task_log_date->format( $df ) : '-');
    $date2 = ($task_log_edit_date ?  $task_log_edit_date->format( $df ) : '-');
    $date3 = ($task_log_start_date ?  $task_log_start_date->format( $df ) : '-');
    $date4 = ($task_log_finish_date ? $task_log_finish_date->format( $df ) : '-');
    $date = $date1." (C)";
    if ($date2 != $date1 && $date2 != '-')
    	$date .="<br>".$date2." (E)";
    if (($date3 != $date1 && $date3 != '-') || ($date4 != $date1 && $date4 != '-')) {
    	$date .="<br>".$date3." (S)";
    	$date .="<br>".$date4." (F)";
    }
    $s .= '<td align="right">'.$row['task_log_progress'] . '%</td>';

	$s .= '<td style="'.$style2.'" nowrap>'.$date.'</td>';
	$s .= '<td nowrap>'.CTask::getWBS($task_id).'</td>';
	$s .= '<td width="36%" style="'.$style.'">'.@$row["task_log_name"].'</td>';
    $s .= '<td>'.$row["user_username"].'</td>'; 
    $s .= '<td>'.$row["proles_name"].'</td>';
	$s .= '<td width="80" align="right" nowrap>'.sprintf( "%.2f", $row["task_log_hours"] ) . ' ph</td>';
	$s .= '<td width="80" align="right" nowrap>'.(float)($row["proles_hour_cost"]*$row["task_log_hours"])." ".$dPconfig['currency_symbol'].'</td>';
	$s .= '<td>'.'<a name="tasklog'.@$row['task_log_id'].'"></a>';

// dylan_cuthbert: auto-transation system in-progress, leave these lines
	$transbrk = "\n[translation]\n";
	$descrip = str_replace( "\n", "<br />", htmlspecialchars($row['task_log_description']) );
	$tranpos = strpos( $descrip, str_replace( "\n", "<br />", $transbrk ) );
	if ( $tranpos === false) 
		$s .= $descrip;
	else {
		$descrip = substr( $descrip, 0, $tranpos );
		$tranpos = strpos( $row['task_log_description'], $transbrk );
		$transla = substr( $row['task_log_description'], $tranpos + strlen( $transbrk ) );
		$transla = trim( str_replace( "'", '"', $transla ) );
		$s .= $descrip."<div style='font-weight: bold; text-align: right'><a title='$transla' class='hilite'>[".$AppUI->_("translation")."]</a></div>";
	}
// end auto-translation code
			
	$s .= '</td>';
	$s .= "\n\t<td>";
	if ($canDelete && ($AppUI->user_id == $row['task_log_creator'] || $perms->checkModule('tasks', 'delete', '', intval($prg),1)) && $curPr) {
		$s .= "\n\t\t<a href=\"javascript:delIt2({$row['task_log_id']});\" title=\"".$AppUI->_('delete log')."\">"
			. "\n\t\t\t". dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
	$s .= '</tr>';
}
echo $s;
?>
</table>
<table>
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Normal Log');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#CC6666">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Problem Report');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffff00">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Modified');?></td>
</tr>
</table>
