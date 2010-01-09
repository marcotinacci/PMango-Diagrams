<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      management task information.

 File:       tasks.php
 Location:   PMango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
   Third version, modified to create PDF reports.
 - 2006.07.30 Lorenzo
   Second version, modified to manage Mango task.
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
GLOBAL $m, $a, $f, $project_id, $min_view, $query_string;
GLOBAL $task_sort_item1, $task_sort_type1, $task_sort_order1;
GLOBAL $task_sort_item2, $task_sort_type2, $task_sort_order2;
GLOBAL $user_id, $dPconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;
/*
	tasks.php

	This file contains common task list rendering code used by
	modules/tasks/index.php and modules/projects/vw_tasks.php

	in

	External used variables:

	* $min_view: hide some elements when active (used in the vw_tasks.php)
	* $project_id
	* $f
	* $query_string
*/
if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}

// Number of columns (used to calculate how many columns to span things through)
$cols = 9;

/****
// Let's figure out which tasks are selected
*/

global $tasks_opened;
global $tasks_closed;

$tview = 0;//Default planned view
$today=date("d/m/Y");
$today2=date("Y-m-d H:i:s");
if ( stristr($currentTabName, 'actual') )
	$tview = 1;
else if ( ! $currentTabName)  // If we aren't tabbed we are in the tasks list.
	$tview = 0;

$tasks_closed = $AppUI->getState("tasks_closed");
if((!$tasks_closed)||($_POST['reset_level']==1)){

    $tasks_closed = array();
}

$tasks_opened = $AppUI->getState("tasks_opened");
if((!$tasks_opened)||($_POST['reset_level']==1)){
 	
    $tasks_opened = array();
}


$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$pinned_only = intval( dPgetParam( $_GET, 'pinned', 0) );
if (isset($_GET['pin']))
{
        $pin = intval( dPgetParam( $_GET, "pin", 0 ) );

        $msg = '';

        // load the record data 
        if($pin) {
        $sql = "INSERT INTO user_task_pin (user_id, task_id) VALUES($AppUI->user_id, $task_id)";
        } else {
        $sql = "DELETE FROM user_task_pin WHERE user_id=$AppUI->user_id AND task_id=$task_id";
        }
        
        if (!db_exec( $sql )) {
                $AppUI->setMsg( "ins/del err", UI_MSG_ERROR, true );
        }
        $AppUI->redirect('', -1);
}
else if($task_id > 0){
    $_GET["open_task_id"] = $task_id;
}

$AppUI->savePlace();

if(($open_task_id = dPGetParam($_GET, "open_task_id", 0)) > 0 && !in_array($_GET["open_task_id"], $tasks_opened)) {
    $tasks_opened[] = $_GET["open_task_id"];
    
    if(in_array($_GET["open_task_id"], $tasks_closed))
    unset($tasks_closed[array_search($open_task_id, $tasks_closed)]);
}

// Closing tasks needs also to be within tasks iteration in order to
// close down all child tasks
if(($close_task_id = dPGetParam($_GET, "close_task_id", 0)) > 0) {
    closeOpenedTask($close_task_id);
}

// We need to save tasks_opened until the finish because some tasks are closed within tasks iteration
//echo "<pre>"; print_r($tasks_opened); echo "</pre>";
/// End of tasks_opened routine

$sql="SELECT projects.project_start_date, projects.project_finish_date FROM projects WHERE project_id ='$project_id'";
$db_start_date = db_loadList($sql);

$taskPriority = dPgetSysVal( 'TaskPriority' );

$task_project = intval( dPgetParam( $_GET, 'task_project', null ) );
//$task_id = intval( dPgetParam( $_GET, 'task_id', null ) );

$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', '' );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', '' );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', '' );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', '' );
$child_task = dPgetParam( $_GET, 'child_task', '' );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', 0 ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', 0 ) );
if (isset($_POST['show_task_options'])) {
	$AppUI->setState('TaskListShowIncomplete', dPgetParam($_POST, 'show_incomplete', 0));
	$AppUI->setState('ExplodeTasks', dPgetParam($_POST, 'explode_tasks', '1'));
	$AppUI->setState('PersonsRoles', dPgetParam($_POST, 'roles', 'N'));
	$AppUI->setState('StartDate', dPgetParam($_POST, 'show_sdate', $db_start_date[0]['project_start_date']));
	$AppUI->setState('EndDate', dPgetParam($_POST, 'show_edate', $db_start_date[0]['project_finish_date']));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);
$explodeTasks = $AppUI->getState('ExplodeTasks', '1');
$roles = $AppUI->getState('PersonsRoles', 'N');
$StartDate = $AppUI->getState('StartDate', $db_start_date[0]['project_start_date']);
$EndDate = $AppUI->getState('EndDate', $db_start_date[0]['project_finish_date'] );

$where = '0';

require_once( $AppUI->getModuleClass( 'admin' ) );
$usObj = new CUser();
$membProjects = $usObj->getUserProject($AppUI->user_id);
$q = new DBQuery();
$q->addTable('projects');
$q->addQuery('project_id,project_group');
if (count($membProjects) > 0)
	$q->addWhere('project_id IN ('.implode(',', array_values($membProjects)) . ')');
else
	$q->addWhere('0');
$groups = $q->loadHashList();
$q->clear();

$allowedProjects = array();
$perms =& $AppUI->acl();
foreach ($groups as $pid => $g)
	if ($perms->checkModule('tasks', 'view','',intval($g),1))
		$allowedProjects[] = $pid; 
// Non ci interessa richiamere allowedProjects perchè una persona può non avere la capability view project ma view task e quindi deve visualizzarne i tasks!

//$allowedProjects = array_intersect($membProjects,is_array($allowedProjects)?array_keys($allowedProjects):array());
//print_r($groups);
//print_r($allowedProjects);
if (count($allowedProjects) > 0) 
	$where='projects.project_id IN (' . implode(',', $allowedProjects) . ')';

$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks, project_group,
	group_name
FROM projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project" .
" LEFT JOIN groups ON group_id = project_group
 WHERE ". $where  . "
GROUP BY project_id
ORDER BY project_name
";
$where2=$where;
//echo "<pre>$psql</pre>";
		
$projects = array();
if (count($allowedProjects) > 0) {
	$prc = db_exec( $psql );
	echo db_error();
	while ($row = db_fetch_assoc( $prc )) {
		$projects[$row["project_id"]] = $row;
	}
}

$join = "";
// pull tasks
$select = "
distinct tasks.task_id, task_parent, task_name, task_start_date, task_finish_date, task_pinned, pin.user_id as pin_user,
task_priority, task_project,
task_description, task_creator, task_status, usernames.user_username, usernames.user_last_name, usernames.user_id, task_milestone,
assignees.user_username as assignee_username, count(distinct assignees.user_id) as assignee_count, assignees.user_first_name,
assignees.user_last_name, tlog.task_log_problem";

$from = "tasks";
$mods = $AppUI->getActiveModules();

$join .= " LEFT JOIN projects ON project_id = task_project";
$join .= " LEFT JOIN users as usernames ON task_creator = usernames.user_id";// da togliere
// patch 2.12.04 show assignee and count
$join .= " LEFT JOIN user_tasks as ut ON ut.task_id = tasks.task_id";
$join .= " LEFT JOIN users as assignees ON assignees.user_id = ut.user_id";

// check if there is log report with the problem flag enabled for the task
$join .= " LEFT JOIN task_log AS tlog ON tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > '0'";

// to figure out if a file is attached to task
$join .= ' LEFT JOIN user_task_pin as pin ON tasks.task_id = pin.task_id AND pin.user_id = ';
$join .= $user_id ? $user_id : $AppUI->user_id;

$where = $project_id ? "\ntask_project = $project_id" : "project_active = 1";

if ($pinned_only)
        $where .= ' AND task_pinned = 1 ';



switch ($f) {
	case 'all':
		break;
	case 'children':
		$where = "\n task_parent = $task_id AND tasks.task_id != $task_id";	
		break;
	case 'myunfinished':
		//$from .= ", user_tasks";
		// This filter checks all tasks that are not already in 100% 
		// and the project is not on hold nor completed
		// patch 2.12.04 finish date required to be consider finish
		$where .= "
					AND task_project     = projects.project_id
					AND ut.user_id       = $user_id
					AND ut.task_id       = tasks.task_id
					
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
					$showIncomplete = true;
		break;
	case 'allunfinished':
		// patch 2.12.04 finish date required to be consider finish
		// patch 2.12.04 2, also show unassigned tasks
		$where .= "
					AND task_project             = projects.project_id
					
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
					$showIncomplete = true;		
		break;
	case 'unassigned':
		$join .= "\n LEFT JOIN user_tasks AS ut2 ON tasks.task_id = ut.task_id";
		$where .= "
					AND ut2.task_id IS NULL";
		break;
	default:
		$where .= "
	AND task_project = projects.project_id
	AND ut.user_id = $user_id
	AND ut.task_id = tasks.task_id";
		break;
}

$task_status = 0;
if ( $min_view && isset($_GET['task_status']) ) 
	$task_status = intval( dPgetParam( $_GET, 'task_status', null ) );
else if ( stristr($currentTabName, 'inactive') )
	$task_status = '-1';
else if ( ! $currentTabName)  // If we aren't tabbed we are in the tasks list.
	$task_status = intval( $AppUI->getState( 'inactive' ) );

$where .= "\n	AND task_status = '$task_status'";

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering

$where .= " AND $where";
/*
//
$obj =& new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
if ( count($allowedTasks))
	$where .= " AND " . implode(" AND ", $allowedTasks);*/

// echo "<pre>$where</pre>";

// Filter by group
if ( ! $min_view && $f2 != 'all' ) {
	 $join .= "\nLEFT JOIN groups ON group_id = projects.project_group";
         $where .= "\nAND group_id = " . intval($f2) . " ";
}

// patch 2.12.04 ADD GROUP BY clause for assignee count
$tsql = "SELECT $select FROM $from $join WHERE $where && task_project IN (" . implode(',', $allowedProjects) . ')'.
  "\nGROUP BY task_id" .
  "\nORDER BY project_id, task_wbs_index";
//echo $tsql;
if (count($allowedProjects) > 0) {
	$ptrc = db_exec( $tsql );
	$nums = db_num_rows( $ptrc );
	echo db_error();
} else {
	$nums = 0;
}

//pull the tasks into an array
/*
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	$projects[$row['task_project']]['tasks'][] = $row;
}
*/
for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );

	//add information about assigned users into the page output
	$ausql = "SELECT ut.user_id,
	u.user_username, user_email, ut.perc_effort, SUM(ut.perc_effort) AS assign_extent, user_first_name, user_last_name
	FROM user_tasks ut
	LEFT JOIN users u ON u.user_id = ut.user_id
	WHERE ut.task_id=".$row['task_id']."
    GROUP BY ut.user_id";

	$assigned_users = array ();
	$paurc = db_exec( $ausql );
	$nnums = db_num_rows( $paurc );
	echo db_error();
	for ($xx=0; $xx < $nnums; $xx++) {
		$row['task_assigned_users'][] = db_fetch_assoc($paurc);
	}
	//pull the final task row into array
	$projects[$row['task_project']]['tasks'][] = $row;
}
$showEditCheckbox = false;
if ($project_id > 0 ) {
	$q->addQuery('project_current');
	$q->addTable('projects');
	$q->addWhere('project_id ='.$project_id);
	$pv = $q->loadResult();
	$q->clear();
	$canEdit = $perms->checkModule('tasks', 'edit','',intval($groups[$project_id]),1) && $pv == '0';
	if ($canEdit && $dPconfig['direct_edit_assignment'])
		$showEditCheckbox = true;
}
else if($f!='children')
	$canEdit = false;

?>

<script type="text/JavaScript">
function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if (isset($canEdit) && $canEdit && $dPconfig['direct_edit_assignment']) {
?>
function checkAll(project_id) {
        var f = eval( 'document.assFrm' + project_id );
        var cFlag = f.master.checked ? false : true;

        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == cFlag && e.name != 'master')
                {
                         e.checked = !e.checked;
                }
        }

}

function chAssignment(project_id, rmUser, del) {
        var f = eval( 'document.assFrm' + project_id );
        var fl = f.add_users.length-1;
        var c = 0;
        var a = 0;

        f.hassign.value = "";
        f.htasks.value = "";

        // harvest all checked checkboxes (tasks to process)
        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +","+ e.value;
                }
        }

        // harvest all selected possible User Assignees
        for (fl; fl > -1; fl--){
                if (f.add_users.options[fl].selected) {
                        a++;
                        f.hassign.value = "," + f.hassign.value +","+ f.add_users.options[fl].value;
                }
        }

        if (del == true) {
                        if (c == 0) {
                                 alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                        } else if (a == 0 && rmUser == 1){
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                if (confirm( '<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?', UI_OUTPUT_JS); ?>' )) {
                                        f.del.value = 1;
                                        f.rm.value = rmUser;
                                        f.project_id.value = project_id;
                                        f.submit();
                                }
                        }
        } else {

                if (c == 0) {
                        alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                } else {

                        if (a == 0) {
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                f.rm.value = rmUser;
                                f.del.value = del;
                                f.project_id.value = project_id;
                                f.submit();

                        }
                }
        }


}
<?php } ?>
</script>

<?
$maxLevel=CTask::getLevel($project_id);
 
function selector($dis,$level=1) //selector: dynamically selects a value in a select
{	
	// Level Select
 		for($i=1;$i<=$level;$i++){
			$arr2[$i-1] = "Level ".$i;
    		$arr[$i-1] = $i;}
    
	for($i = 0; $i < count($arr); $i++){
    	$selected = ($arr[$i] == $dis) ? 'selected="selected"' : '';
     	echo "<option value=\"{$arr[$i]}\" {$selected}>{$arr2[$i]}</option>\n";
 	}
}
 
$state=$AppUI->state;
$whole_start = intval( $db_start_date[0]['project_start_date'] ) ? new CDate( $db_start_date[0]['project_start_date'] ) : new CDate();
$whole_finish =intval( $db_start_date[0]['project_finish_date'] ) ? new CDate( $db_start_date[0]['project_finish_date'] ) : new CDate();

 	$tmp_sdate = $StartDate;
	$tmp_sdate=explode('/',$tmp_sdate);
	$sdate=$tmp_sdate[2].$tmp_sdate[1].$tmp_sdate[0];

 	$tmp_edate = $EndDate;
	$tmp_edate=explode('/',$tmp_edate);
	$edate=$tmp_edate[2].$tmp_edate[1].$tmp_edate[0];
	
$display_option = "custom";

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

if ($display_option == 'custom') {
	// custom dates
	$start_date = intval( $sdate ) ? new CDate( $sdate ) : new CDate();
	$end_date = intval( $edate ) ? new CDate( $edate ) : new CDate();
} else {
	// month
	$start_date = new CDate();
	$end_date = new CDate();
	$end_date->addMonths( $scroll_date );
}

if(dPgetParam( $_POST, 'addreport', '' )&&dPgetParam( $_POST, 'addreport', '' )!=4){

//$AppUI->setMsg("Added to Report",5);

 $sd=$start_date->format(FMT_DATETIME_MYSQL);
 $ed=$end_date->format(FMT_DATETIME_MYSQL);

for($i=0;$i<count($tasks_opened);$i++){
	$r_task_opened=$r_task_opened.$tasks_opened[$i]."/";
}
for($i=0;$i<count($tasks_closed);$i++){
	$r_task_closed=$r_task_closed.$tasks_closed[$i]."/";
}

	if(dPgetParam( $_POST, 'addreport', '' )==1){
		$sql="UPDATE
				reports
			  SET
			  	p_is_incomplete='$showIncomplete',
				p_report_level=$explodeTasks,
				p_report_roles='$roles',
				p_report_sdate='$sd', 
				p_report_edate='$ed', 
				p_report_opened='$r_task_opened', 
				p_report_closed='$r_task_closed' 
			  WHERE
			  	reports.project_id=".$project_id."
			  AND
			  	reports.user_id=".$user_id;
		$db_roles = db_loadList($sql);}
	else if(dPgetParam( $_POST, 'addreport', '' )==2){
			$sql="UPDATE
					reports
				  SET
				  	a_is_incomplete='$showIncomplete',
					a_report_level=$explodeTasks,
					a_report_roles='$roles', 
					a_report_sdate='$sd', 
					a_report_edate='$ed', 
					a_report_opened='$r_task_opened', 
					a_report_closed='$r_task_closed' 
				  WHERE 
				  	reports.project_id=".$project_id." 
				  AND 
				  	reports.user_id=".$user_id;
		$db_roles = db_loadList($sql);
	}	
}
?>
<SCRIPT SRC="js/dateControl.js"></SCRIPT>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.task_list_options.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.task_list_options.' + calendarField );
	fld_fdate = eval( 'document.task_list_options.show_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function showFullProject() {
	document.task_list_options.show_sdate.value = "<?php echo $whole_start->format($df);?>";
	document.task_list_options.show_edate.value = "<?php echo $whole_finish->format($df);?>";

}
</script>
<script type="text/javascript" src="/js/dateControl.js"></script>
<?php
 if ($project_id) { ?>

<form name='task_list_options' method='POST' action='<?php echo $query_string; ?>'>
<table width='100%' border='0' cellpadding='1' cellspacing='0'>
<input type='hidden' name='show_task_options' value='1'>
<tr>
	<td align='left'valign="top"  width='50%' style="border-right: outset #d1d1cd 1px">
		<table border="0" cellpadding="1" cellspacing="0">
			<tr>
				<td><?php echo $AppUI->_('Show');?>:</td>
				<td>
					<input type='checkbox' name='show_incomplete' <?php echo $showIncomplete ? 'checked="checked"' : '';?> />
				</td>
				<td>
					<?php echo $AppUI->_('Incomplete tasks only'); ?></td>
				</td>
				<td>
				<?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$AppUI->_('Explode tasks').": ";?>
				</td>
				<td>
					<select name="explode_tasks" class="text">
					<?selector($explodeTasks,$maxLevel);?>
					</select>
				</td> 				
				<td nowrap>
				</td>
				<td>
					
				</td>
			</tr>
		</table><br>		
		<table border="0" cellpadding="1" cellspacing="0"  width='100%'>
			<input type="hidden" name="display_option" value="<?php echo $display_option;?>" />
			<input type="hidden" name="roles" value="<? echo $roles?>" />

                <tr> 
                        <td align="left" nowrap="nowrap"><?php echo $AppUI->_( 'From' );?>:</td>
                        <td align="left" nowrap="nowrap">
                                <input type="hidden" name="sdate" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
                                <input type="text" class="text" name="show_sdate" value="<?php echo $start_date->format( $df );?>" size="12" onchange='document.task_list_options.show_sdate.value=this.value; validateDate(this);'/>
                                <a href="javascript:popCalendar('sdate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a>
                        </td>

                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'To' );?>:</td>
                        <td align="left" nowrap="nowrap">
                                <input type="hidden" name="edate" value="<?php echo $end_date->format( FMT_TIMESTAMP_DATE );?>" />
                                <input type="text" class="text" name="show_edate" value="<?php echo $end_date->format( $df );?>" size="12" onchange='document.task_list_options.show_edate.value=this.value; validateDate(this);' />
                                <a href="javascript:popCalendar('edate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a>
                        <td valign="middle" nowrap="nowrap">
                        <a href="javascript:showFullProject()"><img src="./images/calendar2.gif" alt="Show Whole Project" title="Show Whole Project" border="0"></a>
                        </td>
                        <input type="hidden" name="reset_level" value="1" />
                        <td align="right" nowrap="nowrap" width='40%'>&nbsp;&nbsp;<input type="button" class="button" value="<?php echo $AppUI->_( 'refresh' );?>" onclick='document.task_list_options.display_option.value="custom"; if(compareDate(document.task_list_options.show_sdate,document.task_list_options.show_edate)) submit();'>
				</td>
                </tr>
		</table>
				               
	</td>
	</form>
	<form name='pdf_options' method='POST' action='<?php echo $query_string; ?>'>
	<td valign="bottom">
		<table width='100%' border='0' cellpadding='1' cellspacing='0'>
			<tr align="right">
				<td align="right">
				
				</td>
			</tr>
			<tr>
				<td align="right">
				<?if ($_POST['make_pdf']=="true")	{
					include('modules/report/makePDF.php');

					$task_level=$explodeTasks;
					$q  = new DBQuery;
					$q->addQuery('projects.project_name');
					$q->addTable('projects');
					$q->addWhere("project_id = $project_id ");
					$name = $q->loadList();
					
					$q  = new DBQuery;
					$q->addTable('groups');
					$q->addTable('projects');
					$q->addQuery('groups.group_name');
					$q->addWhere("projects.project_group = groups.group_id and projects.project_id = '$project_id'");
					$group = $q->loadList();
					
					foreach ($group as $g){
						$group_name=$g['group_name'];
					}
					
					$pdf = PM_headerPdf($name[0]['project_name'],'P',1,$group_name);
					PM_makeTaskPdf($pdf, $project_id, $task_level, $tasks_closed, $tasks_opened, $roles, $tview, $start_date, $end_date, $showIncomplete);
					if ($tview) $filename=PM_footerPdf($pdf, $name[0]['project_name'], 2);
					else $filename=PM_footerPdf($pdf, $name[0]['project_name'], 1);
					?>
					<a href="<?echo $filename;?>"><img src="./modules/report/images/pdf_report.gif" alt="PDF Report" border="0" align="absbottom"></a><?
				}?>
				
				
					<input type="hidden" name="make_pdf" value="false" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Make PDF ' );?>" onclick='document.pdf_options.make_pdf.value="true"; document.pdf_options.submit();'>
				<? if($tview==0){?>
					<input type="hidden" name="addreport" value="-1" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Add to Report ' );?>" onclick='document.pdf_options.addreport.value="1"; document.pdf_options.submit();'><?}
				else{?>
					<input type="hidden" name="addreport" value="-1" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Add to Report ' );?>" onclick='document.pdf_options.addreport.value="2"; document.pdf_options.submit();'><?}?>
				</td>
			</tr>
		</table>
	</td>
</tr>

</table>
<?php } 
if ($project_id == 0)
	$explodeTasks = intval( dPgetParam( $_GET, 'explode', 0) );
if ($project_id == 0)
	$tview = intval( dPgetParam( $_GET, 'actual', 0) );
	
$task_level=$explodeTasks;
?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<?php if ($canEdit) { ?>
		<th width="10">&nbsp;</th>
	<?php } ?>
    <!--<th width="10"><?php //echo $AppUI->_('Pin'); ?></th>-->
    <?php if ($tview) {?>
	<th nowrap></th>
	<?php }?>
	<th width="35" nowrap><?php echo $AppUI->_('Prog.');?></th>
	<th width="10" align="center">&nbsp;</th>
	<th width="40" align="center" nowrap><?php echo $AppUI->_('WBS') ?></th>
	<th width="100%" align="center"><?php echo $AppUI->_('Task name');?></th>
	<th nowrap="nowrap" align="center">

	<?if ($min_view) {?>
	<input type="button" class="button2" value="N" onclick='document.task_list_options.roles.value="N"; document.task_list_options.reset_level.value=""; document.task_list_options.submit();' title="Show Person number">
	<input type="button" class="button2" value="P" onclick='document.task_list_options.roles.value="P"; document.task_list_options.reset_level.value=""; document.task_list_options.submit();' title="Show Person Name">
	<input type="button" class="button2" value="R" onclick='document.task_list_options.roles.value="R"; document.task_list_options.reset_level.value=""; document.task_list_options.submit();' title="Show Person Role">
	<input type="button" class="button2" value="A" onclick='document.task_list_options.roles.value="A"; document.task_list_options.reset_level.value=""; document.task_list_options.submit();' title="Show Person Name and Role">
	<?}else echo $AppUI->_( 'Persons' );?>
	</th>	 
	<?php if ($tview) {?>
	<th align="center" nowrap="nowrap" width="110"><?php echo $AppUI->_('First Log Date');?></th>
	<th align="center" nowrap="nowrap" width="110"><?php echo $AppUI->_('Last Log Date');?></th>
	<th align="center" nowrap="nowrap" width="80"><?php echo  $AppUI->_('Actual Effort');?></th>
	<th align="center" nowrap="nowrap" width="80"><?php echo  $AppUI->_('Actual Cost');?></th>
	<?php } else {?>
	<th align="center" nowrap="nowrap" width="110"><?php echo $AppUI->_('Start Date');?></th>
	<th align="center" nowrap="nowrap" width="110"><?php echo $AppUI->_('Finish Date');?></th>
	<th align="center" nowrap="nowrap" width="80"><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$AppUI->_('Effort')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";?></th>
	<th align="center" nowrap="nowrap" width="80"><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$AppUI->_('Budget')."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";?></th>
	<?php }?>
        <?php if ($showEditCheckbox) { echo '<th width="1">&nbsp;</th>'; }?>
</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );

foreach ($projects as $k => $p) {//echo $p['project_id']." "."$tnums<br>";
	$tnums = count( @$p['tasks'] );
// don't show project if it has no tasks
// patch 2.12.04, show project if it is the only project in view
	if ($tnums > 0 || $project_id == $p['project_id']) {
//echo '<pre>'; print_r($p); echo '</pre>';
		if (!$min_view) {
                echo "<form name=\"assFrm{$p['project_id']}\" action=\"index.php?m=$m&a=$a\" method=\"post\">
                                <input type=\"hidden\" name=\"del\" value=\"1\" />
                                <input type=\"hidden\" name=\"rm\" value=\"0\" />
                                <input type=\"hidden\" name=\"store\" value=\"0\" />
                                <input type=\"hidden\" name=\"dosql\" value=\"do_task_assign_aed\" />
                                <input type=\"hidden\" name=\"project_id\" value=\"{$p['project_id']}\" />
                                <input type=\"hidden\" name=\"hassign\" />
                                <input type=\"hidden\" name=\"htasks\" />"
?>
<tr>
	<td colspan="<?php echo $dPconfig['direct_edit_assignment'] ? $cols-4 : $cols; ?>">
		<table width="100%" border="0">
		<tr>
			<td width="<?php $pp = intval(CProject::getPr(@$p["project_id"]));echo (intval($pp));?>%" nowrap style="border: outset #eeeeee 2px;background-color:#<?php echo @$p["project_color_identifier"];?>">
				<?php $viewPrl = $perms->checkModule('projects','view','',intval($p['project_group']),1);
				if ($viewPrl) {?>
					<a href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
				<?php } ?>
					<span style='color:
				<?php
					echo bestColor( @$p["project_color_identifier"] ); 
				?>;
					text-decoration:none;'><strong>
				<?php echo @$p["group_name"].' :: '.@$p["project_name"];?>
				</strong></span>
				<?php if ($viewPrl) { ?>
				</a>
				<?php } ?>
			</td>
			<td width="<?php echo (100 - intval($pp));?>%">
				<?php echo ($pp);?>%
			</td>
		</tr>
		</table>
        </td>
        <?php if ($dPconfig['direct_edit_assignment']) { 
            // get Users with all Allocation info (e.g. their freeCapacity)
            $tempoTask = new CTask();
            $userAlloc = $tempoTask->getAllocation("user_id");
                ?>
         <!--<td colspan="3" align="right" valign="middle">
                <table width="100%" border="0">
                        <tr>
                                <td align="right">
                                <select name="add_users" style="width:200px" size="2" multiple="multiple" class="text"  ondblclick="javascript:chAssignment('.$user_id.', 0, false)">
                                <?php /*foreach ($userAlloc as $v => $u) {
                                echo "\n\t<option value=\"".$u['user_id']."\">" . dPformSafe( $u['userFC'] ) . "</option>";
                                }*/?>
                                </select>
                                </td>
                                 <td align="center">
                                <?php
                                        /*echo "<a href='javascript:chAssignment({$p['project_id']}, 0, 0);'>".
                                        dPshowImage(dPfindImage('add.png', 'tasks'), 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks')."</a>";
                                        echo  "&nbsp;<a href='javascript:chAssignment({$p['project_id']}, 1, 1);'>".
                                        dPshowImage(dPfindImage('remove.png', 'tasks'), 16, 16, 'Unassign Users', 'Unassign Users from Task')."</a>";*/
                                ?><br />
                                <?php
                                        /*echo "<select class=\"text\" name=\"percentage_assignment\" title=\"".$AppUI->_('Assign with Percentage')."\">";
                                        for ($i = 0; $i <= 100; $i+=5) {
                                                echo "<option ".(($i==30)? "selected=\"true\"" : "" )." value=\"".$i."\">".$i."%</option>";
                                        }*/
                                ?>
                                </select>
                                </td>
                        </tr>
                </table>
         </td>-->
         <?php }?>
</tr>
<?php
		}
		global $done;
		$done = array();
		if ( $task_sort_item1 != "" )
		{
			if ( $task_sort_item2 != "" && $task_sort_item1 != $task_sort_item2 )
				$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1
										  , $task_sort_item2, $task_sort_order2, $task_sort_type2 );
			else $p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1 );
		}
		
		for ($i=0; $i < $tnums; $i++) {
			$t = $p['tasks'][$i];
			//print_r($p['tasks'][$i]);
			//echo " --- ";
			/* 20041118 gregorerhardt bug #311:
			** added the following task status condition to the if clause in order to make sure inactive children
			** are shown in the 'inactive view'; their parents are for instance not listed with them.
			*/
			if ($t["task_parent"] == $t["task_id"] || $p['tasks'][$i]["task_status"] != 0) {
				if ((CTask::getTaskLevel($t["task_id"])<$task_level)&&(!in_array($t["task_id"], $tasks_closed)))
					$is_opened = true;
				else
			    	$is_opened = in_array($t["task_id"], $tasks_opened);
			    if ($tview) 
					showTaskActual( $t, 0, $is_opened,'','', $canEdit, $showIncomplete, $roles, $start_date->format( FMT_TIMESTAMP_DATE ), $end_date->format( FMT_TIMESTAMP_DATE ));
				else{
					if($min_view) showTaskPlanned( $t, 0, $is_opened,'','', $canEdit, $showIncomplete, $roles, true, $start_date->format( FMT_TIMESTAMP_DATE ), $end_date->format( FMT_TIMESTAMP_DATE ));
					else showTaskPlanned( $t, 0, $is_opened,'','', $canEdit, $showIncomplete, $roles, false, $start_date->format( FMT_TIMESTAMP_DATE ), $end_date->format( FMT_TIMESTAMP_DATE ));}
				if($is_opened){
				    findchild( $p['tasks'], $t["task_id"],'', $tview, $explodeTasks, $canEdit, $showIncomplete, $roles, $start_date->format( FMT_TIMESTAMP_DATE ), $end_date->format( FMT_TIMESTAMP_DATE ), $min_view);
				}
			}
		}
// check that any 'orphaned' user tasks are also display
	//if($child_task){
		for ($i=0; $i < $tnums; $i++) {
			if ( !in_array( $p['tasks'][$i]["task_id"], $done ) ) {
			    /*if($p['tasks'][$i]["task_dynamic"] &&*/ 
			    if(in_array( $p['tasks'][$i]["task_parent"], $tasks_closed)) {
			        closeOpenedTask($p['tasks'][$i]["task_id"]);
			    }
			    if(in_array($p['tasks'][$i]["task_parent"], $tasks_opened)){// Child tasks
		    		showTaskPlanned( $p['tasks'][$i], 1, false,'',true,$canEdit,$showIncomplete,$roles, false, $start_date->format( FMT_TIMESTAMP_DATE ), $end_date->format( FMT_TIMESTAMP_DATE ));
			    }
			}
		}
	//}
		?></form><?php
	}
}


$AppUI->setState("tasks_opened", $tasks_opened);
$AppUI->setState("tasks_closed", $tasks_closed);
$AppUI->setState("PersonsRoles", $roles);

if ($tview) { 
?>
</table>
<table cellpadding="2" cellspacing="1">
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff" align="center">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Future task');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#e6eedd" align="center"><img src="images/icons/!r.png"></td>
	<td>=<?php echo $AppUI->_('Running');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#CC6666" align="center"><img src="images/icons/!t.png"></td>
	<td>=<?php echo $AppUI->_('Late');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#aaddaa" align="center"><img src="images/icons/!v.png"></td>
	<td>=<?php echo $AppUI->_('Done');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#bb0000" align="center"><img src="images/icons/!b.png"></td>
	<td>=<?php echo $AppUI->_('Out of budget');?></td>
</tr>
</table>

<?php }?>
