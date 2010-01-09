<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      task view.

 File:       view.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to view PMango task.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.
   
---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
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

---------------------------------------------------------------------------
*/

$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$task_log_id = intval( dPgetParam( $_GET, "task_log_id", 0 ) );

//contr let mod add versionamento
// check permissions for this record

// check permissions for this record
//$canReadModule = !getDenyRead( $m );



$q =& new DBQuery;
$perms =& $AppUI->acl();

$q->addTable('tasks');
$q->leftJoin('users', 'u1', 'u1.user_id = task_creator');
$q->leftJoin('projects', 'p', 'p.project_id = task_project');
$q->leftJoin('task_log', 'tl', 'tl.task_log_task = task_id');
$q->addWhere('task_id = ' . $task_id);
$q->addQuery('tasks.*');
$q->addQuery('project_name, project_color_identifier, project_group, project_current');
//$q->addQuery("CONCAT_WS(' ',user_first_name,user_last_name) username");
$q->addQuery('ROUND(SUM(task_log_hours),2) as log_hours_worked');
$q->addGroup('task_id');

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CTask();


//$obj = null;
$sql = $q->prepare();
$q->clear();

if (!db_loadObject( $sql, $obj, true, false )) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}
$canRead = $perms->checkModule('tasks','view','', intval($obj->project_group),1);
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$canEdit = ($perms->checkModule('tasks','edit','', intval($obj->project_group),1) && ($obj->project_current == '0'));
$canAdd = ($perms->checkModule('tasks','add','', intval($obj->project_group),1) && ($obj->project_current == '0'));
/*if (!$obj->canAccess( $AppUI->user_id )) {
	$AppUI->redirect( "m=public&a=access_denied" );
}*/

$canDelete = ($obj->canDelete( $msg, $task_id, null, intval($obj->project_group)) && ($obj->project_current == '0'));

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TaskLogVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TaskLogVwTab' ) !== NULL ? $AppUI->getState( 'TaskLogVwTab' ) : 0;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT'); 
$df1 = $df;
//Also view the time
$df .= " " . $AppUI->getPref('TIMEFORMAT');
$childs = $obj->getChild(); 
$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_finish_date ) ? new CDate( $obj->task_finish_date ) : null;

$task_start_date = $obj->getStartDateFromTask($task_id, $childs);
$task_start_date['task_start_date'] = intval( $task_start_date['task_start_date'] ) ? new CDate( $task_start_date['task_start_date'] ) : "-";
$task_finish_date = $obj->getFinishDateFromTask($task_id, $childs);
$task_finish_date['task_finish_date'] = intval( $task_finish_date['task_finish_date'] ) ? new CDate( $task_finish_date['task_finish_date'] ) : "-";

$actual_start_date = $obj->getActualStartDate($task_id, $childs);
$actual_start_date['task_log_start_date'] = intval( $actual_start_date['task_log_start_date'] ) ? new CDate( $actual_start_date['task_log_start_date'] ) : "-";
$actual_finish_date = $obj->getActualFinishDate($task_id, $childs);
$actual_finish_date['task_log_finish_date'] = intval( $actual_finish_date['task_log_finish_date'] ) ? new CDate( $actual_finish_date['task_log_finish_date'] ) : "-";

$today = intval( $obj->task_today ) ? new CDate( $obj->task_today ) : null;

//check permissions for the associated project
$canReadProject = $perms->checkModule('projects','view','', intval($obj->project_group),1);

// get the users on this task
$q->addTable('users', 'u');
$q->addTable('user_tasks', 't');
$q->addQuery('u.user_id, u.user_username, user_email');
$q->addWhere('t.task_id = ' . $task_id);
$q->addWhere('t.user_id = u.user_id');
$q->addOrder('u.user_username');

$sql = $q->prepare();
$q->clear();
$users = db_loadList( $sql );

$durnTypes = dPgetSysVal( 'TaskDurationType' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Task', 'applet-48.png', $m, "$m.$a");
$titleBlock->addCell(
);
if ($canEdit) {
	if ($canAdd) {
		$titleBlock->addCrumb( "?m=tasks&a=addedit&task_project=$obj->task_project&task_parent=$task_id", "New task" );
	}
}

//$titleBlock->addCrumb( "?m=tasks", "Tasks list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id", "Edit task" );
}
if ($canDelete) {
	$titleBlock->addCrumbDelete( 'Delete task', $canDelete, $msg );
}
if ($canReadProject) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$obj->task_project", "View project" );
}
$titleBlock->show();

$task_types = dPgetSysVal("TaskType");

?>

<script language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.task_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
*	@param string Input date in the format YYYYMMDD
*	@param string Formatted date
*/
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

	e_date = document.getElementById('task_' + 'log_finish_date');
	e_fdate = document.getElementById('log_finish_date');
	s_date = document.getElementById('task_' + 'log_start_date');
	s_fdate = document.getElementById('log_start_date');

	var s = Date.UTC(s_date.value.substring(0,4),(s_date.value.substring(4,6)-1),s_date.value.substring(6,8), s_date.value.substring(8,10), s_date.value.substring(10,12));
	var e = Date.UTC(e_date.value.substring(0,4),(e_date.value.substring(4,6)-1),e_date.value.substring(6,8), e_date.value.substring(8,10), e_date.value.substring(10,12));

	if( s > e) {
		e_date.value = s_date.value;
		e_fdate.value = s_fdate.value;
	}
}

function updateTask() {
	var f = document.editFrm;
	
	if ( f.task_log_finish_date.value == f.task_log_start_date.value && f.start_hour.value > f.end_hour.value) {
		alert( 'Finish date is before start date!');
		f.start_hour.focus();
		return;
	}
	if ( f.task_log_finish_date.value == f.task_log_start_date.value && f.start_hour.value == f.end_hour.value && f.start_minute.value > f.end_minute.value) {
		alert( 'Finish date is before start date!');
		f.start_hour.focus();
		return;
	}
	if ( f.task_log_start_date.value.length > 0 ) {
		f.task_log_start_date.value += f.start_hour.value + f.start_minute.value + '00';
	} 
	if ( f.task_log_finish_date.value.length > 0 ) {
		f.task_log_finish_date.value += f.end_hour.value + f.end_minute.value + '00';
	}
	if (f.task_log_description.value.length < 1) {
		alert( "<?php echo $AppUI->_('tasksComment', UI_OUTPUT_JS);?>" );
		f.task_log_description.focus();
	} else if (!f.task_log_start_date.value) {
		alert( "<?php echo $AppUI->_('taskValidStartDate', UI_OUTPUT_JS);?>" );
		f.task_log_progress.focus();
	} else if (!f.task_log_finish_date.value) {
		alert( "<?php echo $AppUI->_('taskValidEndDate', UI_OUTPUT_JS);?>" );
		f.task_log_progress.focus();
	} else if (!(f.task_log_proles_id.value > 0)) {
		alert( "<?php echo $AppUI->_('tasksRoles', UI_OUTPUT_JS);?>" );
		f.task_log_progress.focus();
	} else if (isNaN( parseInt( f.task_log_progress.value+0 ) )) {
		alert( "<?php echo $AppUI->_('tasksPercent', UI_OUTPUT_JS);?>" );
		f.task_log_progress.focus();
	} else if (isNaN( parseInt( f.task_log_hours.value+0 ) ) || parseInt( f.task_log_hours.value+0 ) < 0) {
		alert( "<?php echo $AppUI->_('tasksHours', UI_OUTPUT_JS);?>" );
		f.task_log_progress.focus();
	} else if(f.task_log_progress.value  < 0 || f.task_log_progress.value > 100) {
		alert( "<?php echo $AppUI->_('tasksPercentValue', UI_OUTPUT_JS);?>" );
		f.task_log_progress.focus();
	} else {
		f.submit();
	}
}

function setAMPM( field) {
	ampm_field = document.getElementById(field.name + "_ampm");
	if (ampm_field) {
		if ( field.value > 11 ){
			ampm_field.value = "pm";
		} else {
			ampm_field.value = "am";
		}
	}
}
</script>
<?php if ($canDelete) {?>
<script language="JavaScript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Task and its children', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete.submit();
	}
}
</script>
<?php }?>
<table border="0" cellpadding="1" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_task_aed">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_id" value="<?php echo $task_id;?>" />
</form>

<tr valign="top">
	<td width="50%" valign="top"  style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Base Information');?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<td align="right" nowrap ><?php echo $AppUI->_('Project');?>:</td>
				<td colspan="3" style="background-color:#<?php echo $obj->project_color_identifier;?>" nowrap width="25%">
						<font color="<?php echo bestColor( $obj->project_color_identifier ); ?>">
							<?php echo @$obj->project_name;?>
						</font>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task');?>:</td>
				<td colspan="3" class="hilite"><strong><?php echo @$obj->getWBS($task_id)." ".@$obj->task_name;?></strong></td>
			</tr>
			<?php if ( $obj->task_parent != $obj->task_id ) { 
				$obj_parent = new CTask();
				$obj_parent->load($obj->task_parent);
			?>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Parent');?>:</td>
				<td colspan="3" class="hilite"><a href="<?php echo "./index.php?m=tasks&a=view&task_id=" . @$obj_parent->task_id; ?>"><?php echo @$obj_parent->task_name;?></a></td>
			</tr>
			<?php } ?>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Start Date');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Effort');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $te = $obj->getEffort($task_id); echo $te." ph"; ?></td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Finish Date');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $end_date ? $end_date->format( $df ) : '-';?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Budget');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $tb = $obj->getBudget($task_id); echo $tb." ".$dPconfig['currency_symbol']; ?></td>
			</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Computed Information at');echo " ".(!is_null($today) ? " ".$today->format( $df ) : ' -');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Start Date from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%">
                     <?php if ($task_id > 0 && count($task_start_date) > 0 && $task_start_date['task_start_date'] <>"-") { ?>
                            <?php echo $task_start_date ? $task_start_date['task_start_date']->format( $df1 ) : '-';?>
                     <?php } else { echo "-";} ?>
	            </td>
	            <td align="right" nowrap width="25%"><?php echo $AppUI->_('Actual Start Date');?>:</td>
	            <td class="hilite" nowrap width="25%">
					 <?php if ($task_id > 0 && count($actual_start_date) > 0 && $actual_start_date['task_log_start_date'] <>"-") { ?>
                            <?php echo $actual_start_date ? $actual_start_date['task_log_start_date']->format( $df1 ) : '-';?>
                     <?php } else { echo "-";} ?>
	            </td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Finish Date from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%">
                     <?php if ($task_id > 0 && count($task_finish_date) > 0 && $task_finish_date['task_finish_date'] <>"-") { ?>
                            <?php echo $task_finish_date ? $task_finish_date['task_finish_date']->format( $df1 ) : '-';?>
                     <?php } else { echo "-";} ?>
	            </td>
	            <td align="right" nowrap width="25%"><?php echo $AppUI->_('Actual Finish Date');?>:</td>
				<td class="hilite" nowrap width="50" width="25%">
                     <?php if ($task_id > 0 && count($actual_finish_date) > 0 && $actual_finish_date['task_log_finish_date'] <>"-") { ?>
                            <?php echo $actual_finish_date ? $actual_finish_date['task_log_finish_date']->format( $df1 ) : '-';?>
                     <?php } else { echo "-";} ?>
	            </td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Effort from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->getEffortFromTask($task_id,$childs)." ph"; ?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Actual Effort');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $ae = $obj->getActualEffort($task_id, $childs); echo $ae." ph"; ?></td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Budget from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->getBudgetFromTask($task_id,$childs)." ".$dPconfig['currency_symbol']; ?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Actual Cost');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $ac = $obj->getActualCost($task_id, $childs); echo $ac." ".$dPconfig['currency_symbol']; ?></td>
			</tr>	
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Progress');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $pr = $obj->getProgress($task_id, $te);echo $pr;?>%</td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Effort Performance Index');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->getEffortPerformanceIndex($task_id,$ae,$te,$pr,$childs); ?></td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Time Performance Index');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->getTimePerformanceIndex($task_id,null,$start_date,$finish_date,$actual_finish_date['task_log_finish_date'],$pr,$childs); ?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Cost Performance Index');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->getCostPerformanceIndex($task_id,$ac,$tb,$pr,$childs); ?></td>
			</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Assigned to task');?></strong><br>
		<table cellspacing="0" cellpadding="2" border="1" style="border-collapse: collapse" bordercolor="#111111" width="100%">
				<tr>
					<td align="center" nowrap width="40%" bgcolor="#DDCC68"><?php echo $AppUI->_('Person');?>:</td>
					<td align="center" nowrap width="40%" bgcolor="#DDCC68"><?php echo $AppUI->_('Role');?>:</td>
					<td align="center" nowrap width="20%" bgcolor="#DDCC68"><?php echo $AppUI->_('Effort');?>:</td>
				</tr>
				<?php  
					$q->clear();
					$q->addTable('user_tasks','ut');
					$q->addQuery('CONCAT_WS(", ",u.user_last_name,u.user_first_name) as nm, u.user_email as um, pr.proles_name as pn, ut.effort as ue');
					$q->addJoin('users','u','u.user_id=ut.user_id');
					$q->addJoin('project_roles','pr','pr.proles_id = ut.proles_id');
					$q->addWhere('ut.proles_id > 0 && ut.task_id = '.$task_id);
					$ar_ur = $q->loadList();
					if (!is_null($ar_ur)){
						foreach ($ar_ur as $ur) {
							echo "<tr>";
							echo "<td class=\"hilite\" nowrap width=\"40%\"><a href=\"mailto:".$ur['um']."\">".$ur['nm']."</a></td>";
							echo "<td class=\"hilite\" nowrap width=\"40%\">".$ur['pn']."</td>";
							echo "<td class=\"hilite\" nowrap width=\"40%\">".$ur['ue']." ph</td>";
							echo "</tr>";
						}
					}
				?>
		</table>
	</td>
	<td width="50%" rowspan="9" valign="top" style="border: outset #d1d1cd 1px">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<?php
		// Pull tasks dependencies
		$q->addQuery('td.dependencies_req_task_id, t.task_name');
		$q->addTable('tasks', 't');
		$q->addTable('task_dependencies', 'td');
		$q->addWhere('td.dependencies_req_task_id = t.task_id');
		$q->addWhere('td.dependencies_task_id = ' . $task_id);

		$taskDep = $q->loadHashList();
		$q->clear();
		?>
		<tr>
			<td colspan="3" width="50%" nowrap><strong><?php echo $AppUI->_('Dependencies');?></strong></td>
			<td colspan="3" width="50%" nowrap><strong><?php echo $AppUI->_('Tasks depending on this Task');?></strong></td>
		</tr>
		<tr>
			<td colspan="3" width="50%" nowrap valign="top">
				<?php 
				$s = count( $taskDep ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($taskDep as $key => $value) {
					$s .= '<tr><td class="hilite">';
					$s .= '<a href="./index.php?m=tasks&a=view&task_id='.$key.'">'.$value.'</a>';
					$s .= '</td></tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
				?>
			</td>
                <?php
                // Pull the tasks depending on this Task
                $q->addQuery('td.dependencies_task_id, t.task_name');
                $q->addTable('tasks', 't');
                $q->addTable('task_dependencies', 'td');
                $q->addWhere('td.dependencies_task_id = t.task_id');
                $q->addWhere('td.dependencies_req_task_id = ' . $task_id);
                $dependingTasks = $q->loadHashList();
                $q->clear();
				?>
			<td colspan="3" width="50%" nowrap valign="top">
				<?php
				$s = count( $dependingTasks ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($dependingTasks as $key => $value) {
					$s .= '<tr><td class="hilite">';
					$s .= '<a href="./index.php?m=tasks&a=view&task_id='.$key.'">'.$value.'</a>';
					$s .= '</td></tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
				?>
			</td>
		</tr>		 
		<?php
		require_once  $AppUI->getSystemClass( 'CustomFields' );
		$custom_fields = New CustomFields( $m, $a, $task_id, "view" );
		$custom_fields->printHTML();
			 ?>
	 		</td>
	 	</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Details');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Status');?>:</td>
				<td class="hilite"  nowrap width="25%"><?php if($obj->task_status){echo $AppUI->_("Inactive");}else{echo $AppUI->_("Active");}?></td>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Milestone');?>:</td>
				<td class="hilite" width="25%"><?php if($obj->task_milestone){echo $AppUI->_("Yes");}else{echo $AppUI->_("No");}?></td>
			</tr>
			<tr>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Priority');?>:</td>
				<td class="hilite"  nowrap width="25%">
					<?php
						if ($obj->task_priority == 0) {
							echo $AppUI->_('normal');
						} else if ($obj->task_priority < 0){
							echo $AppUI->_('low');
						} else {
							echo $AppUI->_('high');
						}
					?>
				</td>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Type');?>:</td>
				<td class="hilite"  nowrap width="25%"><?php echo $task_types[$obj->task_type];?></td>
				<!--<td align="right" nowrap width="25%"><?php //echo $AppUI->_('Task Creator');?>:</td>
				<td class="hilite" nowrap width="25%"><?php //echo @$obj->username; ?></td>-->
			</tr>
			<tr>
				<td align="right" nowrap><?php echo $AppUI->_('URL');?>:</td>
				<td class="hilite" colspan="3" width="100%"><a href="<?php echo @$obj->task_related_url;?>" target="task<?php echo $task_id;?>"><?php echo @$obj->task_related_url;?></a></td>
			</tr>
			<tr>
				<td align="right" nowrap><?php echo $AppUI->_('Description');?>:</td>
				<td class="hilite" colspan="3" width="100%">
					<?php echo str_replace( chr(10), "<br>", $obj->task_description) ; ?>&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<?php
					require_once("./classes/CustomFields.class.php");
					$custom_fields = New CustomFields( $m, $a, $task_id, "view" );
					$custom_fields->printHTML();
				?>
				</td>
			</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Properties');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<form name="frmProp" action="./index.php?m=tasks" method="post">
				<input type="hidden" name="dosql" value="do_properties" />
				<input type="hidden" name="task_id" value="<?php echo $task_id;?>" />
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Well Formed');?>:
					<td align="left" nowrap> <input id="wf" name="wf" type="checkbox" value="1"></td>
					<td class="hilite" width="100%" rowspan="4" valign="top" style="border: outset #d1d1cd 2px">
						<?php echo str_replace( chr(10), "<br>", $AppUI->getProperties()) ; ?>&nbsp;
					</td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Cost Effective');?>:
					<td align="left" nowrap> <input id="ce" name="ce" type="checkbox" value="1"></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Effort Effective');?>:
					<td align="left" nowrap> <input id="ee" name="te" type="checkbox" value="1"></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Time Effective');?>:
					<td align="left" nowrap><input id="te" name="te" type="checkbox" value="1"></td>
				</tr>
				<tr>
					<td valign="bottom" align="right" colspan="3" nowrap> <input type="submit" class="button" value="compute"></td>
				</tr>
				</form>
			</tr>
		</table>
		
	
	</td>
</tr>
</table>
<br>
<?php
$query_string = "?m=tasks&a=view&task_id=$task_id";
$tabBox = new CTabBox( "?m=tasks&a=view&task_id=$task_id", "", $tab );
// controllo assegnamento task log
$tabBox_show = 0;
if ( $obj->isLeaf() ) {// Solo nelle foglie si possono aggiungere i log!!!!!
	// tabbed information boxes
	if ($perms->checkModule('task_log','view','', intval($obj->project_group),1)) {
		$tabBox_show = 1;
		$prg = intval($obj->project_group);
		$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/vw_logs", 'Task Logs' );
		// fixed bug that dP automatically jumped to access denied if user does not
		// have read-write permissions on task_id and this tab is opened by default (session_vars)
		// only if user has r-w perms on this task, new or edit log is beign showed
		$q->clear();
		$q->addQuery('user_id');
		$q->addTable('user_tasks');
		$q->addWhere('proles_id > 0 && task_id ='.$task_id);
		$usLog = $q->loadColumn();//echo $obj->project_current;
		if ( in_array($AppUI->user_id,$usLog) && ($obj->project_current == '0'))//task_log_id==234significa edit task log id 234
			if ($task_log_id == 0) {
				if ($perms->checkModule('task_log','add','', intval($obj->project_group),1))
					$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/vw_log_update", 'New Log' );
			} else {
				if ($perms->checkModule('task_log','edit','', intval($obj->project_group),1))
					$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/vw_log_update", 'Edit Log' );
			}
		$curPr = ($obj->project_current == '0');
	}
}

if ( count($obj->getChildren()) > 0 ) {
	// Has children
	// settings for tasks
	$f = 'children';
	$min_view = true;
	$tabBox_show = 1;
	// in the tasks file there is an if that checks
	// $_GET[task_status]; this patch is to be able to see
	// child tasks withing an inactive task
	$_GET["task_status"] = $obj->task_status;
	$tabBox->add( "{$dPconfig['root_dir']}/modules/tasks/tasks", 'Child Tasks' );
}

if ($tabBox->loadExtras($m, $a))
$tabBox_show = 1;

if ( $tabBox_show == 1)	$tabBox->show();
?>
