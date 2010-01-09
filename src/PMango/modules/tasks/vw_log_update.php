<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view log update.

 File:       vw_log_update.php
 Location:   PMango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
   Third version, modified to eliminate WBS index. 
 - 2006.07.30 Lorenzo
   Second version, modified to view Mango log update.
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

GLOBAL $AppUI, $task_id, $obj, $percent;

$task_log_id = intval( dPgetParam( $_GET, 'task_log_id', 0 ) );
if (!$task_id > 0)
	$AppUI->redirect("m=public&a=access_denied");

$perms =& $AppUI->acl();

$q = new DBQuery();
$q->addQuery('project_group, project_current');
$q->addTable('projects');
$q->addJoin('tasks','','task_project=project_id');
$log = new CTaskLog();
if ($task_log_id) {
	$q->addJoin('task_log','','task_id=task_log_task');
	$q->addWhere('task_log_id ='.$task_log_id);
	$ar=$q->loadList();
	$prg = $ar[0]['project_group'];
	$curPr = $ar[0]['project_current'] == '0';
	$q->clear();	
	$q->addQuery('task_log_creator');
	$q->addTable('task_log');
	$q->addWhere('task_log_id ='.$task_log_id);
	$usLog = $q->loadResult();
	if (! ($AppUI->user_id==$usLog && $perms->checkModule('task_log','edit','', intval($prg),1) && $curPr))
		$AppUI->redirect("m=public&a=access_denied");
	$log->load( $task_log_id );
} else {
	$q->addWhere('task_id ='.$task_id);
	$ar=$q->loadList();
	$prg = $ar[0]['project_group'];
	$curPr = $ar[0]['project_current'] == '0';
	$q->clear();	
	$q->addQuery('user_id');
	$q->addTable('user_tasks');
	$q->addWhere('proles_id > 0 && task_id ='.$task_id);
	$usLog = $q->loadColumn();
	if (! (in_array($AppUI->user_id,$usLog) && $perms->checkModule('task_log','add','', intval($prg),1) && $curPr))
		$AppUI->redirect("m=public&a=access_denied");
	$log->task_log_task = $task_id;
	$log->task_log_name = $obj->task_name;
}
$q->clear();
// Check that the user is at least assigned to a task
$task = new CTask;
$task->load($task_id);
/*if (! $task->canAccess($AppUI->user_id))
	$AppUI->redirect('m=public&a=access_denied');*/


$proj = &new CProject();
$proj->load($obj->task_project);
$q = new DBQuery();
$q->addQuery('pr.proles_id, pr.proles_name');
$q->addTable('project_roles','pr');
$q->addJoin('user_tasks','ut','pr.proles_id=ut.proles_id');
$q->addWhere('ut.task_id = '.$task_id.' && ut.user_id = '.$AppUI->user_id);
$task_log_proles_id=$q->loadHashList();
/*print_r($task_log_proles_id);*/
$q->clear();
/*$ptrc = db_exec($sql);
*/

$taskLogReference = dPgetSysVal( 'TaskLogReference' );

// Task Update Form
$df = $AppUI->getPref( 'SHDATEFORMAT' );
$df1 .= $df. " " . $AppUI->getPref('TIMEFORMAT');
$log_creation_date = new CDate( $log->task_log_creation_date );
$log_edit_date = new CDate();
$log_start_date = new CDate( $log->task_log_start_date );
$log_finish_date = new CDate( $log->task_log_finish_date );

//Time arrays for selects
$start = intval(dPgetConfig('cal_day_start'));
$end   = intval(dPgetConfig('cal_day_end'));
$inc   = intval(dPgetConfig('cal_day_increment'));
if ($start === null ) $start = 8;
if ($end   === null ) $end = 17;
if ($inc   === null)  $inc = 15;
$hours = array();
for ( $current = $start; $current < $end + 1; $current++ ) {
	if ( $current < 10 ) { 
		$current_key = "0" . $current;
	} else {
		$current_key = $current;
	}
	
	if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ){
		//User time format in 12hr
		$hours[$current_key] = ( $current > 12 ? $current-12 : $current );
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

$minutes = array();
$minutes["00"] = "00";
for ( $current = 0 + $inc; $current < 60; $current += $inc ) {
	$minutes[$current] = $current;
}
?>
<!--
<a name="log"></a>-->

<table cellspacing="1" cellpadding="2" border="0" width="100%">
<form name="editFrm" action="?m=tasks&a=view&task_id=<?php echo $task_id;?>" method="post">
<input type="hidden" name="uniqueid" value="<?php echo uniqid("");?>" />
<input type="hidden" name="dosql" value="do_updatetask" />
<input type="hidden" name="task_log_id" value="<?php echo $log->task_log_id;?>" />
<input type="hidden" name="task_log_task" value="<?php echo $log->task_log_task;?>" />
<input type="hidden" name="task_log_creator" value="<?php echo $AppUI->user_id;?>" />
<input type="hidden" name="task_log_name" value="Update :<?php echo $log->task_log_name;?>" />
<tr>
    <td valign='middle' align='left'  width="50%">
      <table width='100%'>
      	<tr>
			<td align="right" nowrap>
				* <?php echo $AppUI->_('Task Log Name');?>:
			</td>
	        <td nowrap align="left" colspan="2">
	                <input type="text" class="text" name="task_log_name" value="<?php 
	                	
	                	echo htmlspecialchars($log->task_log_name);?>" maxlength="255" size="54" />
	        </td>
  		</tr>	
		<tr>
			<td align="right" nowrap>
				<?php echo $AppUI->_('Role').":";?>
			</td>
			<td align="left">
				<?php 
					echo arraySelect( $task_log_proles_id, 'task_log_proles_id', 'size="1" class="text" style="width:162px"', $log->task_log_proles_id );
				?>
			</td>
			<td align="left" nowrap>
				<?php echo $AppUI->_('Hours Worked').":";?>
				<input type="text" class="text" name="task_log_hours" value="<?php echo $log->task_log_hours;?>" maxlength="8" size="5" /> 
			</td>
		</tr>
		
		<tr>
			<td align="right" nowrap>
				<?php echo $AppUI->_('Start Date').":";?>
			</td>
			<td nowrap="nowrap" align="left">
			<!-- patch by rowan  bug #890841 against v1.0.2-1   email: bitter at sourceforge dot net -->
				<input type="hidden" name="task_log_start_date" id="task_log_start_date" value="<?php echo $log_start_date->format( FMT_TIMESTAMP_DATE );?>">
			<!-- end patch #890841 -->
				<input type="text" name="log_start_date" id="log_start_date" value="<?php echo $log_start_date->format( $df );?>" class="text" disabled="disabled">
				<a href="#" onClick="popCalendar('log_start_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		    <td nowrap="nowrap" align="left">
				<?php
				echo arraySelect($hours, "start_hour",'size="1" onchange="setAMPM(this)" class="text"', $log_start_date ? $log_start_date->getHour() : $start ) . " : ";
				echo arraySelect($minutes, "start_minute",'size="1" class="text"', $log_start_date ? $log_start_date->getMinute() : "0" );
				if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
					echo '<input type="text" name="start_hour_ampm" id="start_hour_ampm" value="' . ( $log_start_date ? $log_start_date->getAMPM() : ( $start > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" />';
				}
			?>
			</td>
		</tr>
		<tr>
		    
			<td align="right" nowrap>
				<?php echo $AppUI->_(' Finish Date').":";?>
			</td>
			<td nowrap="nowrap" align="left">
			<!-- patch by rowan  bug #890841 against v1.0.2-1   email: bitter at sourceforge dot net -->
				<input type="hidden" name="task_log_finish_date" id="task_log_finish_date" value="<?php echo $log_finish_date->format( FMT_TIMESTAMP_DATE );?>">
			<!-- end patch #890841 -->
				<input type="text" name="log_finish_date" id="log_finish_date" value="<?php echo $log_finish_date->format( $df );?>" class="text" disabled="disabled">
				<a href="#" onClick="popCalendar('log_finish_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
			<td nowrap="nowrap" align="left">
				<?php
				echo arraySelect($hours, "end_hour",'size="1" onchange="setAMPM(this)" class="text"', $log_finish_date ? $log_finish_date->getHour() : $end ) ." : ";
				echo arraySelect($minutes, "end_minute",'size="1" class="text"', $log_finish_date ? $log_finish_date->getMinute() : "00" );
				if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
					echo '<input type="text" name="end_hour_ampm" id="end_hour_ampm" value="' . ( $log_finish_date ? $log_finish_date->getAMPM() : ( $end > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" />';
				}
			?>
			</td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $AppUI->_('Creation Date').":";?>
			</td>
			<td nowrap="nowrap" align="left">
				<input type="hidden" name="task_log_creation_date" value="<?php echo $log_creation_date->format( FMT_TIMESTAMP );?>">
				<?php echo $log_creation_date->format( $df1 );?>
			</td>
			<td align="left" nowrap>
				<?php echo $AppUI->_('Progress').":";?>
				<select name="task_log_progress" class="text">
							<?php 
								for ($i = 5; $i <= 100; $i+=5) {
									if ($log->task_log_progress > 0 && $log->task_log_progress==$i)
										echo "<option style='width:43px' selected value=\"".$i."\">".$i."%</option>";
									else
										echo "<option value=\"".$i."\">".$i."%</option>";
								}
							?>
				</select>
		    </td>
		</tr>
		<tr>
			<td align="right">
				<?php echo $AppUI->_('Edit Date').":";?>
			</td>
			<td nowrap="nowrap" align="left">
				<input type="hidden" name="task_log_edit_date" value="<?php echo $log_edit_date->format( FMT_TIMESTAMP );?>">
				<?php echo $log_edit_date->format( $df1 );?>
			</td>
			<td align="left" nowrap>
				<?php echo $AppUI->_('Problem');?>:  
		    	<input type="checkbox" value="1" name="task_log_problem" <?php if($log->task_log_problem){?>checked="checked"<?php }?> />
		    </td>
		</tr>
		</table>
	</td>
	<td valign="top" align="right" width="50%">
		<table width="100%">
			<tr>
				<td align="left" valign="top"><?php echo $AppUI->_("Description");?>:<br>
					<textarea name="task_log_description" class="textarea" cols="72" rows="9"><?php echo htmlspecialchars($log->task_log_description);?></textarea>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2" valign="bottom" align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_('submit');?>" onclick="updateTask()" />
	</td>
</tr>
</form>
</table>

