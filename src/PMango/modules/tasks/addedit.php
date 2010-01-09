<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      add and edit task.

 File:       addedit.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to add/edit PMango task.
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
$task_id = intval( dPgetParam( $_REQUEST, "task_id", 0 ) );
$perms =& $AppUI->acl();

// load the record data
$obj = new CTask();

// check if we are in a subform
if ($task_id > 0 && !$obj->load( $task_id )) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$task_parent = isset($_REQUEST['task_parent'])? $_REQUEST['task_parent'] : $obj->task_parent;

// check for a valid project parent
$task_project = intval( $obj->task_project );
if (!$task_project) {
	$task_project = dPgetParam( $_REQUEST, 'task_project', 0 );
	if (!$task_project) {
		$AppUI->setMsg( "badTaskProject", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}
$q = new DBQuery();
if ($task_project > 0) {
	$q->addQuery('project_group,project_current');
	$q->addTable('projects');	
	$q->addWhere('project_id = '.$task_project);
	$ar=$q->loadList();
	$prg = $ar[0]['project_group'];
	$curPr = $ar[0]['project_current'] == '0';
} else {
	$AppUI->setMsg( "badTaskProject", UI_MSG_ERROR );
	$AppUI->redirect();
}
$q->clear();

// check permissions
if ( $task_id ) {
	// we are editing an existing task
	$canEdit = $perms->checkModule('tasks','edit','', intval($prg),1) && $curPr;
	if (!$canEdit) 
		$AppUI->redirect( "m=public&a=access_denied&err=noedit" );
	$canDelete = ($obj->canDelete( $msg, $task_id, null, intval($prg))) && $curPr;
} else {
	$canAdd = $perms->checkModule('tasks','add','', intval($prg),1) && $curPr;
	$canDelete = false;
	if (!$canAdd) 
		$AppUI->redirect( "m=public&a=access_denied&err=noedit" );
}


//check permissions for the associated project
$canReadProject = $perms->checkModule('projects','view','', intval($prg),1) && $curPr;


$durnTypes = dPgetSysVal( 'TaskDurationType' );

// check the document access (public, participant, private)
/*if (!$obj->canAccess( $AppUI->user_id )) {
	$AppUI->redirect( "m=public&a=access_denied&err=noaccess" );
}*/

// pull the related project
$project = new CProject();
$project->load( $task_project );

$users = array();
$roles = array();
$meffort = array();
$dwh = array();

$parentTasks = array();
$effortTasks = array();
$q = new DBQuery();
$q->addQuery('ut.user_id, ut.proles_id, t.task_id, ut.effort, u.user_first_name, u.user_last_name, u.user_day_hours, pr.proles_name, t.task_parent');
$q->addTable('tasks','t');
$q->addJoin('user_tasks','ut','ut.task_id = t.task_id');
$q->addJoin('users','u','u.user_id = ut.user_id');
$q->addJoin('project_roles','pr','pr.proles_id = ut.proles_id');
$q->addWhere('t.task_project ='.$task_project);
$ut_ar = $q->loadList();
foreach($ut_ar as $ar) {
	if (!is_null($ar['task_id'])) {
		$parentTasks[$ar['task_id']] = $ar['task_parent'];
		if (!is_null($ar['user_id']) && $ar['proles_id']) 
			$effortTasks[$ar['task_id']][$ar['user_id'].",".$ar['proles_id']] = $ar['effort'];
	}
}

function sumLeafEffort($task_root, &$lEffort) {
	global $parentTasks, $effortTasks;
	
	//echo"$task_root inizio: ";print_r($lEffort);echo "<br>";
	//echo"$task_root PRIMA: "."<br>";
	if (!in_array($task_root, $parentTasks)) {//echo"$task_root FOGLIA: ";print_r($effortTasks[$task_root]);echo "<br>";// $task_root è una foglia 
		$temp2 = $effortTasks[$task_root];
		if (is_array($temp2))
			foreach ($temp2 as $uipi => $ef) 
				if (is_null($lEffort[$uipi])) 
					$lEffort[$uipi] = $ef;
				else //{
					$lEffort[$uipi] += $ef;//}//print_r($lEffort);echo "<br>";}
	}
	else {
		$temp = $parentTasks;
		foreach($temp as $tid => $pid) {
			
			if ($pid == $task_root && $tid != $task_root) {//echo $tid."<br>";//$tid è figlio di $task_root
				sumLeafEffort($tid, $lEffort);//splitto i valori*/
				//print_r($lEffort);echo "<br>";
			}
			//echo"$task_root DOPO: "."<br>";echo "tid ".$tid."tr ".$task_root."pid ".$pid."<br>";
		}
		//echo"$task_root fine: ";print_r($lEffort);echo "<br>";
		//return $lEffort;	
	}
}

// La funzione restituisce il Max effort dato un task
function findMaxEffort($task_root) {
	global $parentTasks, $effortTasks;
	
	$sum = array();
	if ($parentTasks[$task_root] == $task_root) { //echo $task_root." - <BR>";print_r($effortTasks[$task_root]);// è figlio di project
		return "-";
	}
	foreach ($parentTasks as $tid => $pid) {
		if ($pid != $tid && $task_root != $tid && $pid == $parentTasks[$task_root])	{// $tid è fratello di task_root
			// fai la somma del valore delle foglie di tid
			// quindi faccio una funzione che dato tid mi calcola il valore delle sue foglie
			// mi restituisce un array(ui,pi => ef)
			$brotherEffort = array();
			sumLeafEffort($tid, $brotherEffort);
			//print_r($brotherEffort);echo "$tid<br>";
			if (count($brotherEffort) > 0)
				foreach ($brotherEffort as $uipi => $ef)
					if (is_null($sum[$uipi])) 
						$sum[$uipi] = $ef;
					else 
						$sum[$uipi] += $ef;
		}
	}
	//echo $effortTasks[$task_root];
	if (count($sum)==0)
		return $effortTasks[$parentTasks[$task_root]];
	
	foreach ($effortTasks[$parentTasks[$task_root]] as $uipi => $ef) {
		$sum[$uipi] = $ef - $sum[$uipi];
		if ($sum[$uipi] < 0)
			$sum[$uipi] = 0;
	}
	
	foreach ($sum as $uipi => $ef) {
		if (!isset($effortTasks[$parentTasks[$task_root]][$uipi]))
			unset($sum[$uipi]);
	}
	return ($sum);
}

function findMaxEffortFromParent($parent, $originTask = 0) {
	global $parentTasks, $effortTasks;
	
	$sum = array();
	foreach ($parentTasks as $tid => $pid) {
		if ($pid != $tid && $parent != $tid && $pid == $parent && $originTask != $tid)	{// $tid è figlio di parent
			// fai la somma del valore delle foglie di tid
			// quindi faccio una funzione che dato tid mi calcola il valore delle sue foglie
			// mi restituisce un array(ui,pi => ef)
			$brotherEffort = array();
			sumLeafEffort($tid, $brotherEffort);
			//print_r($brotherEffort);echo "$tid<br>";
			if (count($brotherEffort) > 0)
				foreach ($brotherEffort as $uipi => $ef)
					if (is_null($sum[$uipi])) 
						$sum[$uipi] = $ef;
					else
						$sum[$uipi] += $ef;
		}
	}
	//echo $effortTasks[$task_root];
	if (count($sum)==0)
		return $effortTasks[$parent];
		
	foreach ($effortTasks[$parent] as $uipi => $ef) {
		$sum[$uipi] = $ef - $sum[$uipi];
		if ($sum[$uipi] < 0)
			$sum[$uipi] = 0;
	}
	
	foreach ($sum as $uipi => $ef) {
		if (!isset($effortTasks[$parent][$uipi]))
			unset($sum[$uipi]);
	}
	return ($sum);
}
//$sum = findMaxEffort(19); print_r($sum);echo "<br>";print_r($effortTasks[16]);
$us = array();
$pr = array();
$us_hours_list="|";
$us_name_list="|";
$proles_list="|";

$q->clear();
$q->addQuery('u.user_id, user_first_name, user_last_name, user_day_hours, up.proles_id, proles_name');
$q->addTable('users','u');
$q->addJoin('user_projects','up','u.user_id = up.user_id');
$q->addJoin('project_roles','pr','pr.proles_id = up.proles_id');
$q->addWhere('up.proles_id > 0 && up.project_id ='.$task_project);
$q->addOrder('user_last_name, user_first_name');
$q->exec();
$task_list = "-^[";
while ( $row = $q->fetchRow()) {
	if ($task_id == 0 && $task_parent < 1 || $task_parent==$task_id) if (!is_null($row['user_id'])) {//new task
		$users[$row['user_id'].",".$row['proles_id']] = $row['user_last_name'] . ", " . $row['user_first_name'];
		
		$roles[$row['user_id'].",".$row['proles_id']] = $row['proles_name'];
		
		$meffort[$row['user_id'].",".$row['proles_id']] = "-";
		
		$dwh[$row['user_id'].",".$row['proles_id']] = $row['user_day_hours']." h";
	}
	$task_list .= "(".$row['user_id'].",".$row['proles_id']."|-)";
	$prUsers[$row['user_id'].",".$row['proles_id']] = $row['user_last_name'] . ", " . $row['user_first_name'];
	$prRoles[$row['user_id'].",".$row['proles_id']] = $row['proles_name'];
	$prDwh[$row['user_id'].",".$row['proles_id']] = $row['user_day_hours']." h";
	
	if (!isset($us[$row['user_id']]) && !is_null($row['user_id'])) {
		$us[$row['user_id']] = 1;
		$us_hours_list .= $row['user_id']."=".$row['user_day_hours']."|";	
		$us_name_list .= $row['user_id']."=".$row['user_last_name'] . ", " . $row['user_first_name']."|";	
	}
	if (!isset($pr[$row['proles_id']]) && !is_null($row['proles_id'])) {
		$pr[$row['proles_id']] = 1;
		$proles_list .= $row['proles_id']."=".$row['proles_name']."|";	
	}
}
$task_list .= "]^-";
$q->clear();		
	
if (!(($task_id == 0 && $task_parent < 1) || $task_parent==$task_id)) {//edit task
	$par = ($task_parent > 0) ? $task_parent : $parentTasks[$task_id];
}

foreach($ut_ar as $ar) {//echo "<pre>".print_r($ar)."</pre>";
	if (!($task_id == 0 && $task_parent < 1 || $task_parent==$task_id)) {//edit task
		if ($ar['task_id'] == $par && !is_null($ar['user_id'])) {
			$users[$ar['user_id'].",".$ar['proles_id']] = $ar['user_last_name'] . ", " . $ar['user_first_name'];
			
			$roles[$ar['user_id'].",".$ar['proles_id']] = $ar['proles_name'];
			
			$temp = ($task_id > 0) ? findMaxEffort($task_id) : findMaxEffortFromParent($par);
			$meffort[$ar['user_id'].",".$ar['proles_id']] = $temp[$ar['user_id'].",".$ar['proles_id']]." ph";
			
			$dwh[$ar['user_id'].",".$ar['proles_id']] = $ar['user_day_hours']." h";
		}
	}
}

$redUsers = array_diff_assoc($prUsers, $users);
$redRoles = array_diff_assoc($prRoles, $roles);
$redDwh = array_diff_assoc($prDwh, $dwh);
//print_r($redUsers);print_r($redRoles);
/*echo $us_hours_list."<br>";
echo $us_name_list."<br>";
echo $proles_list."<br>";*/

foreach ($effortTasks as $tid => $uipi_ar) {
	$task_list .= $tid."[";
	foreach ($uipi_ar as $uipi => $ef) {
		//correggere max effort
		$temp = findMaxEffortFromParent($tid, $task_id);
		$task_list .= "(".$uipi."|".$temp[$uipi].")";
	}
	$task_list .= "]".$tid."-";
}/*
print_r( $task_list);*/
$twi = $obj->task_wbs_index;//echo $task_parent;
if ($task_parent > 0) 
	if ($task_id == $task_parent)
		$wbs_list="|^=,".$twi."|";
	else
		$wbs_list="|^=,".CTask::getWBSIndexFromParent("^",$task_project)."|";
else
	$wbs_list="|^=,".CTask::getWBSIndexFromParent("^",$task_project)."|";
		
function getSpaces($amount) {
	if($amount == 0) return "";
	return str_repeat("&nbsp;", $amount);
}

function constructTaskTree($task_data, $depth = 0, $wbs = "") {
	global $all_tasks, $parents, $task_parent_options, $task_parent, $task_id, $wbs_list, $twi;
	
	$selected = $task_data['task_id'] == $task_parent ? "selected='selected'" : "";
	/*$task_data['task_name'] = strlen($task_data[1]) > 40 ? substr($task_data['task_name'], 0, 40)."..." : $task_data['task_name'];*/
	
	$iwbs = CTask::getWBSIndexFromParent($task_data['task_id']);
	$wbs .= $task_data['task_wbs_index'];// da cambiare questa""""""""""""""""""""""""""""""""""!!
	$wbs2 = $wbs;
	if ($task_data['task_id'] == $task_parent && $task_id > 0 && $task_parent > 0)
		$wbs_list .= $task_data['task_id']."=".$wbs.",".$twi."|";
	else {// NON c'è bisogno di parentTask è in task_data ->'task_parent' USARE task_wbs_index
		if (isset($task_data['task_parent']) && $task_id > 0) // si gestisono i fratelli
			if ($task_data['task_parent'] == $task_parent) { // per i task non figli di project
				if ($wbs > CTask::getWBS($task_id)) 
					$wbs2{strlen($wbs)-1} = ($wbs2{strlen($wbs)-1} == 0) ? 9 : $wbs{strlen($wbs)-1} - 1;
			} else // per i figli di project
				if ($task_data['task_parent'] == $task_data['task_id'] && $task_parent == $task_id) {
					if ($wbs > CTask::getWBS($task_id)) 
						$wbs2{strlen($wbs)-1} = ($wbs2{strlen($wbs)-1} == 0) ? 9 : $wbs{strlen($wbs)-1} - 1;
				}
		if ($wbs2 != $wbs)
			$wbs_list .= $task_data['task_id']."=".$wbs2.",".$iwbs."|";
		else
			$wbs_list .= $task_data['task_id']."=".$wbs.",".$iwbs."|";
	}
	$twbs = CTask::getWBS($task_data['task_id']);
	$outOpt = $twbs." ".dPFormSafe($task_data['task_name']);
	$outOptFormatted = (strlen($outOpt)+$depth*2) > 46 ? getSpaces($depth*2)." ".substr($outOpt, 0, 46)."..." : getSpaces($depth*2)." ".$outOpt;
	$task_parent_options .= "<option value='".$task_data['task_id']."' $selected>".$outOptFormatted."</option>";
	if (isset($parents[$task_data['task_id']])) {
		foreach ($parents[$task_data['task_id']] as $child_task) {
			if ($child_task != $task_id)
				constructTaskTree($all_tasks[$child_task], ($depth+1), $wbs2.".");
		}
	}
}

// let's get root tasks
$sql = "select task_id, task_name, task_finish_date, task_start_date, task_milestone, task_parent, task_wbs_index
		from tasks
		where task_project = '$task_project'
			  and task_id  = task_parent
        order by task_wbs_index";

$root_tasks = db_loadHashList($sql, 'task_id');

$task_parent_options = "";

// Now lets get non-root tasks, grouped by the task parent
$sql = "select task_id, task_name, task_finish_date, task_start_date, task_milestone, task_parent, task_wbs_index
	from tasks
	where task_project = '$task_project'
	and task_id != task_parent
	order by task_wbs_index";

$parents = array();
$all_tasks = array();
$sub_tasks = db_exec($sql);
if ($sub_tasks) {
	while ($sub_task = db_fetch_assoc($sub_tasks)) {
		// Build parent/child task list
		$parents[$sub_task['task_parent']][] = $sub_task['task_id'];
		$all_tasks[$sub_task['task_id']] = $sub_task;
		$all_tasks[$sub_task['task_wbs_index']] = $sub_task;
	}
}
// let's iterate root tasks

foreach ($root_tasks as $root_task) {
	if ($root_task['task_id'] != $task_id)
		constructTaskTree($root_task);
}

// setup the title block
$ttl = $task_id > 0 ? "Edit Task" : "Add Task";
$titleBlock = new CTitleBlock( $ttl, 'applet-48.png', $m, "$m.$a");
//$titleBlock->addCrumb( "?m=tasks", "Tasks list" );
if ($task_id > 0)
  $titleBlock->addCrumb( "?m=tasks&a=view&task_id=$obj->task_id", "View task" );
if ($canDelete) {
	$titleBlock->addCrumbDelete( 'Delete task', $canDelete, $msg );
}
if ( $canReadProject ) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$task_project", "View project" );
}
$titleBlock->show();

$group_id = $project->project_group;

//get list of projects, for task move drop down list.
//require_once $AppUI->getModuleClass('projects');
//$project =& new CProject;
$pq = new DBQuery;
$pq->addQuery('project_id, project_name');
$pq->addTable('projects');
$pq->addWhere("project_group = '$group_id'");
$pq->addWhere('project_active = 1');
$pq->addOrder('project_name');
//$project->setAllowedSQL($AppUI->user_id, $pq);
$projects = $pq->loadHashList();

$childs = @$obj->getChild();
$df = $AppUI->getPref('SHDATEFORMAT');
$today = new CDate( $obj->task_today );
$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : new CDate();
$finish_date = intval( $obj->task_finish_date ) ? new CDate( $obj->task_finish_date ) : null;

$task_start_date = @$obj->getStartDateFromTask($task_id, $childs);
$task_start_date['task_start_date'] = intval( $task_start_date['task_start_date'] ) ? new CDate( $task_start_date['task_start_date'] ) : "-";
$task_finish_date = @$obj->getFinishDateFromTask($task_id, $childs);
$task_finish_date['task_finish_date'] = intval( $task_finish_date['task_finish_date'] ) ? new CDate( $task_finish_date['task_finish_date'] ) : "-";

$actual_start_date = @$obj->getActualStartDate($task_id, $childs);
$actual_start_date['task_log_start_date'] = intval( $actual_start_date['task_log_start_date'] ) ? new CDate( $actual_start_date['task_log_start_date'] ) : "-";
$actual_finish_date = @$obj->getActualFinishDate($task_id, $childs);
$actual_finish_date['task_log_finish_date'] = intval( $actual_finish_date['task_log_finish_date'] ) ? new CDate( $actual_finish_date['task_log_finish_date'] ) : "-";
// convert the numeric calendar_working_days config array value to a human readable output format
$cwd = explode(',', $dPconfig['cal_working_days']);
$cwd_conv = array_map( 'cal_work_day_conv', $cwd );
$cwd_hr = implode(', ', $cwd_conv);

function cal_work_day_conv($val) {
	GLOBAL $locale_char_set;
	$wk = Date_Calc::getCalendarWeek( null, null, null, "%a", LOCALE_FIRST_DAY );
	return htmlentities($wk[$val], ENT_COMPAT, $locale_char_set);
}

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

$q->clear();
$q->addQuery('model_type,model_delivery_day');
$q->addTable('models');
$q->addWhere('model_association = 2 && model_pt ='.$task_id);
$mod = $q->loadList();
?>
<SCRIPT language="JavaScript">
var task_id = '<?php echo $obj->task_id;?>';
var eff_perc = '<?php echo $dPconfig['effort_perc']?>';
var check_task_dates = <?php
  if (isset($dPconfig['check_task_dates']) && $dPconfig['check_task_dates'])
    echo 'true';
  else
    echo 'false';
?>;
var task_name_msg = "<?php echo $AppUI->_('taskName');?>";
var task_start_msg = "<?php echo $AppUI->_('taskValidStartDate');?>";
var task_end_msg = "<?php echo $AppUI->_('taskValidEndDate');?>";

//working days array from config.php
var working_days = new Array(<?php echo dPgetConfig( 'cal_working_days' );?>);
var cal_day_start = <?php echo intval(dPgetConfig( 'cal_day_start' ));?>;
var cal_day_end = <?php echo intval(dPgetConfig( 'cal_day_end' ));?>;
var daily_working_hours = 8;// DA TOGLIERE; ?>;
var i=0;
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
<table border="1" cellpadding="2" cellspacing="0" width="100%" class="std">
<form name="frmDelete" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_task_aed">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_id" value="<?php echo $task_id;?>" />
</form>

<form name="editFrm" action="?m=tasks&project_id=<?php echo $task_project;?>" method="post">
	<input name="dosql" type="hidden" value="do_task_aed" />
	<input name="task_id" type="hidden" value="<?php echo $task_id;?>" />
	<input name="task_project" type="hidden" value="<?php echo $task_project;?>" />	
	<input name="oldParent" type="hidden" value="<?php echo $obj->task_parent;?>" />
	<input name="oldWBSi" type="hidden" value="<?php echo $obj->task_wbs_index;?>" />	
	<input name="task_list" type="hidden" value="<?php echo $task_list;?>" />
	<input name="us_hours_list" type="hidden" value="<?php echo $us_hours_list;?>" />
	<input name="us_name_list" type="hidden" value="<?php echo $us_name_list;?>" />
	<input name="proles_list" type="hidden" value="<?php echo $proles_list;?>" />	
	<input name="wbs_list" type="hidden" value="<?php echo $wbs_list;?>" />	
	
	<input name="model" type="hidden" value="<?php echo $mod[0]['model_type'];?>" />	
	<input name="deliveryDay" type="hidden" value="<?php echo $mod[0]['model_delivery_day'];?>" />	
	
<tr>
	<td colspan="2" style="border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier;?>" >
		<font color="<?php echo bestColor( $project->project_color_identifier ); ?>">
			<strong><?php echo $AppUI->_('Project');?>: <?php echo @$project->project_name;?></strong>
		</font>
	</td>
</tr>
<tr>
  <td valign="bottom" width="50%">
  <strong><?php echo $AppUI->_('Base information');?></strong><br>
	<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right">
				<?php echo $AppUI->_( 'Task' ).":";?>
			</td>
			<td colspan="2" nowrap>
								
				<?php 
					if ($task_id == 0 && $task_parent > 0)
						$wbs_val = CTask::getWBSIndexFromParent($task_parent);
					else
						if ($task_id == 0) 
							$wbs_val = CTask::getWBSIndexFromParent("^",$task_project);
						else  
							$wbs_val = $obj->task_wbs_index;?>	
				<input type="hidden" name="task_wbs_index" id="task_wbs_index" value="<?php echo $wbs_val; ?>" />
				<?php
					$wbs_val2 = "";
					if ($task_id == 0 && $task_parent > 0)
						$wbs_val2 = CTask::getWBS($task_parent);
					else
						if ($task_id > 0 && $task_id != $obj->task_parent) 
							$wbs_val2 = CTask::getWBS($obj->task_parent);
							?>	
				<input type="hidden" name="task_wbs" id="task_wbs" value="<?php echo $wbs_val2; ?>" />
				<input type="text" size="9" name="wbs" id="wbs" value="<?php echo ("" == ($wbs_val2)) ? $wbs_val : $wbs_val2.".".$wbs_val; ?>" class="text" disabled/>
				<img src="./images/icons/updown.gif" align="absmiddle" border=0 usemap="#arrow"/>
				<input type="text" class="text" name="task_name" value="<?php echo dPformSafe( $obj->task_name );?>" size="49" maxlength="255" />
				<map name="arrow">
					<area coords="0,0,10,7" href="#" onclick="minusWBS(document.editFrm)">
					<area coords="0,8,10,14" href="#" onclick="plusWBS(document.editFrm)">
				</map>
				<SCRIPT language="JavaScript">
					var currentMaxWBSi = <?php if ($task_id > 0 && $task_parent != $task_id)
													$w = CTask::getWBSIndexFromParent($task_parent) - 1;
												else
													if ($task_id > 0) 
														$w = CTask::getWBSIndexFromParent("^",$task_project) - 1; 
													else 
														$w = $wbs_val;
												echo $w;?>;
					var maxWBSi = currentMaxWBSi;
				</script>
				
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap">
				<?php echo $AppUI->_( 'Start Date' ).":";?>
			</td>
			<td nowrap align="left" colspan="2">
				<input type="hidden" name="task_start_date" id="task_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
				<input type="text" name="start_date" id="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : "" ;?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar(document.editFrm.start_date,document.editFrm)">
								<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
							</a>
			<?php
				echo "&nbsp;&nbsp;&nbsp;".arraySelect($hours, "start_hour",'size="1" onchange="setAMPM(this)" class="text"', $start_date ? $start_date->getHour() : $start ) . " : ";
				echo arraySelect($minutes, "start_minute",'size="1" class="text"', $start_date ? $start_date->getMinute() : "0" );
				if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
					echo '<input type="text" name="start_hour_ampm" id="start_hour_ampm" value="' . ( $start_date ? $start_date->getAMPM() : ( $start > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" />';
				}
			?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Finish Date' ).":";?></td>
			<td nowrap="nowrap" align="left" colspan="2">
				<input type="hidden" name="task_finish_date" id="task_finish_date" value="<?php echo $finish_date ? $finish_date->format( FMT_TIMESTAMP_DATE ) : '';?>"/>
				<input type="text" name="finish_date" id="finish_date" value="<?php echo $finish_date ? $finish_date->format( $df ) : '';?>" class="text" disabled="disabled"/>
				<a href="#" onClick="popCalendar(document.editFrm.finish_date,document.editFrm)">
								<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
							</a>
	
			<?php
				echo "&nbsp;&nbsp;&nbsp;".arraySelect($hours, "end_hour",'size="1" onchange="setAMPM(this)" class="text"', $finish_date ? $finish_date->getHour() : $end ) ." : ";
				echo arraySelect($minutes, "end_minute",'size="1" class="text"', $finish_date ? $finish_date->getMinute() : "00" );
				if ( stristr($AppUI->getPref('TIMEFORMAT'), "%p") ) {
					echo '<input type="text" name="end_hour_ampm" id="end_hour_ampm" value="' . ( $finish_date ? $finish_date->getAMPM() : ( $end > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" />';
				}
			?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task Parent' ).":";?></td>
			<td nowrap="nowrap" align="left" colspan="2" nowrap>
				<select name='task_parent' class='text' style="width:308px" onchange="changeResources(document.editFrm,document.resourceFrm)">
					<option value='^'><?php echo @$project->project_name;?></option>
					<?php echo $task_parent_options; ?>
				</select>
				<?php echo $AppUI->_( 'Ef. Model' ).":";?>
				<a href="#" onClick="popModel()"> 
					<img src="./images/icons/graph.gif" width="25" height="22" align="absmiddle" border=0 />
				</a>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="2">
				<input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" />
			</td>
		</tr>
		</table>
  </td>
  <td width="50%" valign="bottom">
  	<table align="center" cellpadding="2" cellspacing="0" width="100%">
	<!--<tr><td>&nbsp;</td></tr>-->
  	<tr><td colspan="2" valign="middle">
		<strong><?php echo $AppUI->_('Computed Information');?></strong><br>
			<table border="0" cellpadding="2" cellspacing="1"   align="left"  >
				<tr>
					<td align="right" nowrap="nowrap" ><?php echo $AppUI->_('Today').":";?></td>
					<td class="hilite" width="110">
						<?php echo $today->format( $df );?>
						<input type="hidden" name="task_today" value="<?php echo $today->format( FMT_TIMESTAMP_DATE );?>" />
					</td>
					<td align="right" nowrap="nowrap" ><?php echo $AppUI->_('Progress').":";?></td>
					<td  class="hilite" width="110">
						<?php if ($task_id > 0) 
								echo @$obj->getProgress($obj->project_id,$obj->project_effort)."%";
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date from Tasks').":";?></td>
					<td  class="hilite">
						<?php if ($task_id > 0 && count($task_start_date) > 0 && $task_start_date['task_start_date'] <>"-") 
								 echo $task_start_date ? $task_start_date['task_start_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Start Date').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0 && count($actual_start_date) > 0 && $actual_start_date['task_log_start_date'] <>"-") 
								echo $actual_start_date ? $actual_start_date['task_log_start_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date from Tasks').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0 && count($task_finish_date) > 0 && $task_finish_date['task_finish_date'] <>"-") 
								echo $task_finish_date ? $task_finish_date['task_finish_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0 && count($actual_finish_date) > 0 && $actual_finish_date['task_log_finish_date'] <>"-") 
								echo $actual_finish_date ? $actual_finish_date['task_log_finish_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Effort from Tasks').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0) 
								echo @$obj->getEffortFromTask($task_id,$childs)." ph";
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Effort').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0) 
								echo @$obj->getActualEffort($task_id,$childs)." ph";
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Budget from Tasks').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0) 
								echo @$obj->getBudgetFromTask($task_id,$childs)." ".$dPconfig['currency_symbol'];
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Cost').":";?></td>
					<td class="hilite">
						<?php if ($task_id > 0) 
								echo @$obj->getActualCost($task_id,$childs)." ".$dPconfig['currency_symbol'];
							  else
							  	 echo "-"?>
					</td>
				</tr>
			</table>
	<tr>
		<td align="right" colspan="2">
			<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt(document.editFrm,document.resourceFrm);" />
		</td>
	</tr>
	</table>
  </td>
</tr>
</table><br>
</form>

<?php
	if (isset($_GET['tab']))
	  $AppUI->setState('TaskAeTabIdx', dPgetParam($_GET, 'tab', 0));
	$tab = $AppUI->getState('TaskAeTabIdx', 0);
	$tabBox =& new CTabBox("?m=tasks&a=addedit&task_id=$task_id", "", $tab);	
	$tabBox->add("{$dPconfig['root_dir']}/modules/tasks/ae_resource", "Resources");
	$tabBox->add("{$dPconfig['root_dir']}/modules/tasks/ae_depend", "Dependencies");
	$tabBox->add("{$dPconfig['root_dir']}/modules/tasks/ae_desc", "Details");
    //$tabBox->add("{$dPconfig['root_dir']}/modules/tasks/ae_dates", "Dates");
	$tabBox->loadExtras('tasks', 'addedit');
	$tabBox->show('', true);
?>