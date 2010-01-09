<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      add and edit project

 File:       addedit.php
 Location:   pmango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango projects.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.

---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team.

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

$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );
$group_id = intval( dPgetParam( $_GET, "group_id", 0 ) );

$q  = new DBQuery;

$perms =& $AppUI->acl();
$canEdit=0;
$canAddProjectGr=0;
$groupsAdd = array(); // insieme dei gruppi a cui l'utente può aggiungere un progetto
if ($project_id > 0) {//edit
	if (!$group_id > 0) {
		$q->addTable('projects');
		$q->addQuery('project_group,project_current');
		$q->addWhere('project_id='. $project_id);
		$ar = $q->loadList();
		$group_id = $ar[0]['project_group'];
		$prc = $ar[0]['project_current'] == '0';
	}
	$canEdit = $perms->checkModule('projects', 'edit','',$AppUI->user_groups[$group_id],1) && $prc;
	$canDelete = $perms->checkModule( $m, 'delete','',$AppUI->user_groups[$group_id], 1);
	if (!$canEdit) 
		$AppUI->redirect( "m=public&a=access_denied" );
}
else { //add
	$canAddProjectGr = 0;
	foreach ($AppUI->user_groups as $g => $sc) {
			$canAddProjectGr = $perms->checkModule('projects', 'add','',$sc);
			if ($canAddProjectGr) $groupsAdd[] = $g;
	}
	if (empty($groupsAdd)) 
		$AppUI->redirect( "m=public&a=access_denied" );
	$canDelete = false;
}


// get a list of permitted groups
require_once( $AppUI->getModuleClass ('groups' ) );

$gr=array();
$q->addTable('groups');
$q->addQuery('group_id, group_name');
if ($project_id > 0) 
	$q->addWhere('group_id ='.$group_id);
else 
	$q->addWhere('group_id IN ('.implode(',', array_values($groupsAdd)) . ')');
$gr = $q->loadHashList();//array(group_id => group_name)
$groups[0]=''; //null group
foreach($gr as $gid => $gname)
	$groups[$gid] = $gname;
$q->clear();
// pull users

/****************************************************/
$q->addTable('users','u');
$q->addQuery('us.group_id, u.user_id');
$q->addQuery('CONCAT_WS(", ",u.user_last_name,u.user_first_name) as nm');
$q->addJoin('user_setcap','us','us.user_id=u.user_id');
$q->addWhere('us.group_id IN ('.implode(',', array_keys($groups)) . ')');
$q->addOrder('user_last_name');
$loadUsers = $q->loadList();
$users = array(0=>array()); //gruppo nullo con nessun utente
foreach($loadUsers as $c => $us)
	$users[$us[group_id]][$us[user_id]] = $us[nm]; 

// load the record data
$row = new CProject();

if (!$row->load( $project_id, false ) && $project_id > 0) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if (count( $groups ) < 1 && $project_id == 0) {
	$AppUI->setMsg( "noCompanies", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($project_id == 0 && $group_id > 0) {//add (new project from groups)
	$row->project_group = $group_id;
}

// add in the existing group if for some reason it is dis-allowed
if ($project_id && !array_key_exists( $row->project_group, $groups )) {
	$q  = new DBQuery;
	$q->addTable('groups');
	$q->addQuery('group_name');
	$q->addWhere('groups.group_id = '.$row->project_group);
	$sql = $q->prepare();
	$q->clear();
	$groups[$row->project_group] = db_loadResult($sql);
}

// get critical tasks (criteria: task_finish_date)
$criticalTasks = ($project_id > 0) ? $row->getCriticalTasks() : NULL;

// get ProjectPriority from sysvals
$projectPriority = dPgetSysVal( 'ProjectPriority' );

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = new CDate( $row->project_start_date );
$today = new CDate( $row->today );
$finish_date = intval( $row->project_finish_date ) ? new CDate( $row->project_finish_date ) : null;

// setup the title block
$ttl = $project_id > 0 ? "Edit Project" : "New Project";
$titleBlock = new CTitleBlock( $ttl, 'applet3-48.png', $m, "$m.$a" );
//$titleBlock->addCrumb( "?m=projects", "Projects list" );
if ($project_id != 0) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "View project" );
	if ($canDelete) 
		$titleBlock->addCrumbDelete( 'Delete project', $canDelete, '' );
}
$titleBlock->show();

$intUsers = array(0=>array());//utenti interni del gruppo
foreach ($users as $g => $us) 
	foreach ($us as $u => $uname) 
		if ($perms->checkModule('projects','res',$u,intval($g),1)) {
			$intUsers[$g][$u] = $uname;
		}

if ( $project_id > 0 ) {
	$q->clear();
	$q->addTable('user_projects','up');
	$q->addQuery('user_id as u, proles_id as pr');
	$q->addWhere('proles_id > 0 && project_id <> 0 && project_id = '.$project_id);
	$q->addOrder('pr, u');	
	$assigned_roles = $q->loadList();
} 

$initRolesAssignment = "";
$assigned = array();

$sql = "
		 SELECT proles_id, proles_name
		 FROM project_roles
		 WHERE proles_status = 0
		 ";
$rolesName = db_loadHashList( $sql );	//array(roles_id=>roles_name)

$q->addTable('users','u');
$q->addQuery('CONCAT_WS(", ",u.user_last_name,u.user_first_name) as nm');
$q->addWhere('user_id ='.$AppUI->user_id);
$initProjectCreator = $q->loadResult();
$initProjectCreator .= " [".$rolesName[1]."]";
if ( $project_id > 0 ) {
	foreach ($assigned_roles as $a) {
		$assigned[$a['u']."=".$a['pr']] = $users[$group_id][$a['u']] . " [" . $rolesName[$a['pr']] . "]";
		$initRolesAssignment .= $a['u'] ."=". $a['pr'].";";
	}
}

$task_start_date = @$row->getStartDateFromTask($project_id);
$task_start_date['task_start_date'] = intval( $task_start_date['task_start_date'] ) ? new CDate( $task_start_date['task_start_date'] ) : "-";
$task_finish_date = @$row->getFinishDateFromTask($project_id);
$task_finish_date['task_finish_date'] = intval( $task_finish_date['task_finish_date'] ) ? new CDate( $task_finish_date['task_finish_date'] ) : "-";

$actual_start_date = @$row->getActualStartDate($project_id);
$actual_start_date['task_log_start_date'] = intval( $actual_start_date['task_log_start_date'] ) ? new CDate( $actual_start_date['task_log_start_date'] ) : "-";
$actual_finish_date = @$row->getActualFinishDate($project_id);
$actual_finish_date['task_log_finish_date'] = intval( $actual_finish_date['task_log_finish_date'] ) ? new CDate( $actual_finish_date['task_log_finish_date'] ) : "-";

$q->clear();
$q->addQuery('model_type,model_delivery_day');
$q->addTable('models');
$q->addWhere('model_association = 1 && model_pt ='.$project_id);
$mod = $q->loadList();
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $dPconfig['base_url'];?>/lib/calendar/calendar-dp.css" title="blue" />
<script type="text/javascript" src="<?php echo $dPconfig['base_url'];?>/lib/calendar/calendar.js"></script>
<script type="text/javascript" src="<?php echo $dPconfig['base_url'];?>/lib/calendar/lang/calendar-<?php echo $AppUI->user_locale; ?>.js"></script>
<script language="javascript">
function setColor(color) {
	var f = document.projectFrm;
	if (color) {
		f.project_color_identifier.value = color;
	}
	//test.style.background = f.project_color_identifier.value;
	document.getElementById('test').style.background = '#' + f.project_color_identifier.value; 		//fix for mozilla: does this work with ie? opera ok.
}

function setShort() {
var f = document.projectFrm;
var x = 10;
	if (f.project_name.value.length < 11) {
		x = f.project_name.value.length;
	}
	if (f.project_short_name.value.length == 0) {
		f.project_short_name.value = f.project_name.value.substr(0,x);
	}
}

var calendarField = '';
var calWin = null;

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.projectFrm.project_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=280, height=250, scollbars=false' );
}

/**
*	@param string Input date in the format YYYYMMDD
*	@param string Formatted date
*/
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.projectFrm.project_' + calendarField );
	fld_fdate = eval( 'document.projectFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

	// set end date automatically with start date if start date is after end date
	if (calendarField == 'start_date') {
		if( document.projectFrm.finish_date.value < idate) {
			document.projectFrm.project_finish_date.value = idate;
			document.projectFrm.finish_date.value = fdate;
		}
	}
}

function submitIt() {
	var f = document.projectFrm;
	var msg = '';

	if (f.project_id.value < 1) {
		s = new String(f.members.value);
		if (s.indexOf(f.project_creator.value + "=") == -1) 
			msg += "\n<?php echo $AppUI->_('projectsAbsentCreator', UI_OUTPUT_JS);?>";
	}
	if (f.project_name.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('projectsValidName', UI_OUTPUT_JS);?>";
		f.project_name.focus();
	}
	if (f.project_color_identifier.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('projectsColor', UI_OUTPUT_JS);?>";
		f.project_color_identifier.focus();
	}
	if (f.project_group.options[f.project_group.selectedIndex].value < 1) {
		msg += "\n<?php echo $AppUI->_('projectsBadCompany', UI_OUTPUT_JS);?>";
		f.project_name.focus();
	}
	if (!f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadStartDate', UI_OUTPUT_JS);?>";
	}
	if (f.project_finish_date.value > 0 && f.project_finish_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate1');?>";
	}
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}

<?php 
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Project', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete.submit();
	}
}
<?php } ?>

function popModel() {
	if (document.projectFrm.project_id.value > 0)
		window.open( 'index.php?m=public&a=model&dialog=1&callback=setModel&model='+document.projectFrm.model.value+'&deliveryDay='+document.projectFrm.deliveryDay.value+'&project_id='+document.projectFrm.project_id.value, 'modelwin', 'top=250,left=250,width=451, height=180, scollbars=false' );
	else
		alert('After you have created project, you can insert model informations');
}

function setModel(model,deliveryDay) {
	document.projectFrm.model.value = model;
	document.projectFrm.deliveryDay.value = deliveryDay;
}
</script>

<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
<form name="frmDelete" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="project_group" value="<?php echo $row->project_group;?>" />
</form>

<form name="projectFrm" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="project_creator" value="<?php echo $AppUI->user_id;?>" />
	<input type="hidden" name="creator_name" value="<?php echo $initProjectCreator;?>" />
	<input type="hidden" name="members" value="<?php echo $initRolesAssignment;?>" />
	
	<input name="model" type="hidden" value="<?php echo $mod[0]['model_type'];?>" />	
	<input name="deliveryDay" type="hidden" value="<?php echo $mod[0]['model_delivery_day'];?>" />
<tr>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Base information');?></strong><br>
		<table cellspacing="1" cellpadding="1" border="0" align="left">
		<tr>
			<td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Project Name').":";?></td>
			<td width="100%" colspan="2">
				<input type="text" name="project_name" value="<?php echo dPformSafe( $row->project_name );?>" size="30" maxlength="50" onBlur="setShort();" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Group').":";?></td>
			<td width="100%" nowrap="nowrap" colspan="2">	
			<?php 
				$group="-";
				foreach ($intUsers as $g => $us) {
					$temp = "";
					foreach ($us as $u => $uname)
						$temp .= $u."=".$uname.";";
					$group .= $g."[".$temp."]".$g."-";
				}
			?>
				<input type="hidden" name="groups" value="<?php echo $group?>"/>
			<?php
				if ($project_id > 0)
					echo arraySelect( $groups, 'project_group', 'class="text" size="1" disabled style="width:194px;" onChange="changeGroup(document.projectFrm)"', $row->project_group );
				else
					echo arraySelect( $groups, 'project_group', 'class="text" size="1" style="width:194px;" onChange="changeGroup(document.projectFrm)"', $row->project_group );
			?> 
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date').":";?></td>
			<td nowrap="nowrap">	 <input type="hidden" name="project_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
				<input type="text" class="text" name="start_date" id="date1" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar( 'start_date', 'start_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date').":";?></td>
			<td nowrap="nowrap">	<input type="hidden" name="project_finish_date" value="<?php echo $finish_date ? $finish_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
				<input type="text" class="text" name="finish_date" id="date2" value="<?php echo $finish_date ? $finish_date->format( $df ) : '';?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('finish_date', 'finish_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Effort').":";?></td>
			<td>
				<input type="Text" name="project_effort" value="<?php echo @$row->project_effort;?>" maxlength="10" class="text" />
				 <?php echo "ph" ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget').":";?></td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo @$row->project_target_budget;?>" maxlength="10" class="text" />
				<?php echo $dPconfig['currency_symbol'] ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Hard Budget').":";?></td>
			<td>
				<input type="Text" name="project_hard_budget" value="<?php echo @$row->project_hard_budget;?>" maxlength="10" class="text" />
				<?php echo $dPconfig['currency_symbol'] ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Effort Model' ).":";?></td>
			<td>
				<a href="#" onClick="popModel()"> 
					<img src="./images/icons/graph.gif" width="25" height="22" align="absmiddle" border=0 />
				</a>
			</td>
		</tr>
		<tr>
		<td colspan="2">
			<hr align="center" style="border: outset #d1d1cd 1px">
			<strong><?php echo $AppUI->_('Computed Information');?></strong><br>
			<table border="0" cellpadding="2" cellspacing="1"   align="left"  >
				<tr>
					<td align="right" nowrap="nowrap" ><?php echo $AppUI->_('Today').":";?></td>
					<td class="hilite" width="110">
						<?php echo $today->format( $df );?>
						<input type="hidden" name="project_today" value="<?php echo $today->format( FMT_TIMESTAMP_DATE );?>" />
					</td>
					<td align="right" nowrap="nowrap" ><?php echo $AppUI->_('Progress').":";?></td>
					<td  class="hilite" width="110">
						<?php if ($project_id > 0) 
								echo @$row->getProgress($row->project_id,$row->project_effort)."%";
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date from Tasks').":";?></td>
					<td  class="hilite">
						<?php if ($project_id > 0 && count($task_start_date) > 0 && $task_start_date['task_start_date'] <>"-") 
								 echo $task_start_date ? $task_start_date['task_start_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Start Date').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0 && count($actual_start_date) > 0 && $actual_start_date['task_log_start_date'] <>"-") 
								echo $actual_start_date ? $actual_start_date['task_log_start_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date from Tasks').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0 && count($task_finish_date) > 0 && $task_finish_date['task_finish_date'] <>"-") 
								echo $task_finish_date ? $task_finish_date['task_finish_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0 && count($actual_finish_date) > 0 && $actual_finish_date['task_log_finish_date'] <>"-") 
								echo $actual_finish_date ? $actual_finish_date['task_log_finish_date']->format( $df ) : '-';
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Effort from Tasks').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0) 
								echo @$row->getEffortFromTask()." ph";
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Effort').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0) 
								echo @$row->getActualEffort()." ph";
							  else
							  	 echo "-"?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Budget from Tasks').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0) 
								echo @$row->getBudgetFromTask()." ".$dPconfig['currency_symbol'];
							  else
							  	 echo "-"?>
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Cost').":";?></td>
					<td class="hilite">
						<?php if ($project_id > 0) 
								echo @$row->getActualCost()." ".$dPconfig['currency_symbol'];
							  else
							  	 echo "-"?>
					</td>
				</tr>
			</table>
		</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="center">
		<table cellspacing="1" cellpadding="1" border="0" align="center">
		<tr>
			<td align="center"><b><?php echo $AppUI->_( 'Resources' );?>:</b></td>
			<td align="center"><b><?php echo $AppUI->_( 'Assigned to Project' );?>:</b></td>
		</tr>
		<tr>
			<td align="left">
				<?php echo arraySelect( $intUsers[$group_id], 'resources', 'style="width:240px" size="20" class="text" multiple="multiple" ', null ); ?>
			</td>
			<td align="left">
				<?php echo arraySelect( $assigned, 'assigned', 'style="width:240px" size="20" class="text" multiple="multiple" ', null ); ?>
			</td>
		<tr>
			<td colspan="2" align="center">
				<table>
				<tr>
					<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser(document.projectFrm)" /></td>
					<td>
						<select name="roles_assignment" class="text">
						<?php 
							foreach ($rolesName as $rid => $rname) {
								echo "<option value=\"".$rid."\">".$rname."</option>";
							}
						?>
						</select>
					</td>				
					<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser(document.projectFrm)" /></td>					
				</tr>
				</table>
			</td>
		</tr>	
		</table>
	</td>
	<tr>
	<td colspan="2">
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Details');?></strong><br>
		<table cellspacing="1" cellpadding="1" width="100%" align ="left">
			<tr><td width="50%"><table>
				<tr>
					<td align="right" nowrap="nowrap">
						<?php echo $AppUI->_('Short Name').":";?>
					</td>
					<td align="left" nowrap="nowrap">
						<input type="text" name="project_short_name" value="<?php echo dPformSafe( @$row->project_short_name ) ;?>" size="14" maxlength="10" class="text" />
					</td>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL').":";?></td>
					<td colspan="2">
						<input type="text" name="project_url" value='<?php echo @$row->project_url;?>' size="25" maxlength="255" class="text" />
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap">
						<?php echo $AppUI->_('Status').":";?>
					</td>
					<td align="left" nowrap="nowrap">
						<?php echo arraySelect( $pstatus, 'project_status', 'style="width:96px" size="1" class="text"', $row->project_status, true ); ?>
					</td>
					<td align="right" nowrap="nowrap">
						<?php echo $AppUI->_('Project Type').":";?>
					</td>
					<td align="left" nowrap="nowrap">
						<?php echo arraySelect( $ptype, 'project_type', 'style="width:165px" size="1" class="text"', $row->project_type, true );?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap">
						<?php echo $AppUI->_('Color Identifier').":";?>
					</td>
					<td align="left" nowrap="nowrap">
						<input type="text" name="project_color_identifier" value="<?php echo (@$row->project_color_identifier) ? @$row->project_color_identifier : 'FFFFFF';?>" size="14" maxlength="6" onBlur="setColor();" class="text" />
					</td>
					<td align="right" nowrap="nowrap" align="right">
						<a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scollbars=false');"><?php echo $AppUI->_('Change color').":";?></a>
					</td>
					<td align="left" nowrap="nowrap">
						<span id="test" title="test" style="background:#<?php echo (@$row->project_color_identifier) ? @$row->project_color_identifier : 'FFFFFF';?>;"><a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border="1" width="40" height="15" /></a></span>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap">
						<?php echo $AppUI->_( 'Priority' ).":";?>
					</td>
					<td align="left" nowrap="nowrap">
						<?php echo arraySelect( $projectPriority, 'project_priority', 'style="width:96px" size="1" class="text"', $row->project_priority, true );?>
					</td>
					<td align="right" nowrap="nowrap" align="right">
						<?php echo $AppUI->_('Active');?>?
					</td>
					<td align="left" nowrap="nowrap">
						<input type="checkbox" value="1" name="project_active" <?php echo $row->project_active||$project_id==0 ? 'checked="checked"' : '';?> />
					</td>
				</tr>
			</table></td>
			<td width="50%">
				<table cellpadding="1" cellspacing="1" align="center">
					<td align="left" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Description').":";?><br>
							<textarea name="project_description" cols="76" rows="5" wrap="virtual" class="textarea"><?php echo dPformSafe( @$row->project_description );?></textarea>
					</td>
				</table>
			</td>
			</tr>
		</table>	
	</td>
	</tr>
	<tr>
		<td align="right" colspan="3">
		<?php
			require_once("./classes/CustomFields.class.php");
			$custom_fields = New CustomFields( $m, $a, $row->project_id, "edit" );
			$custom_fields->printHTML();
		?>
		</td>
	</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" /></td>
	<td align="right">
		<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt();" />
	</td>
</tr>
</form>
</table>
* <?php echo $AppUI->_('requiredField');?>