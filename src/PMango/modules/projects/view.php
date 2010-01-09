<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view project information

 File:       view.php
 Location:   PMango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.10.20 Marco
   Now the report's pages it's opened in a new windows.
 - 2007.05.08 Riccardo
   Third version, modified to create PDF reports. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage Mango projects.
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
$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );
$msg = '';
$objPr = new CProject();

// get ProjectPriority from sysvals
$projectPriority = dPgetSysVal( 'ProjectPriority' );
$projectPriorityColor = dPgetSysVal( 'ProjectPriorityColor' );

$working_hours = $AppUI->user_day_hours;
$user_id = $AppUI->user_id;
// load the record data
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery("group_name,
	CONCAT_WS(' ',user_first_name,user_last_name) user_name,
	projects.*");
$q->addJoin('groups', 'g', 'group_id = project_group');
$q->addJoin('users', 'u', 'user_id = project_creator');
$q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project');
$q->addWhere('project_id = '.$project_id);
$q->addGroup('project_id');
$sql = $q->prepare();
$q->clear();
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// check permissions for this record
$perms =& $AppUI->acl();
$canRead = $perms->checkModule($m, 'view','',intval($obj->project_group),1);
$canEdit = $perms->checkModule($m, 'edit','',intval($obj->project_group),1) && $obj->project_current == '0';

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

$canDelete = $perms->checkModule( $m, 'delete','',intval($obj->project_group), 1);

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
$start_date = intval( $obj->project_start_date ) ? new CDate( $obj->project_start_date ) : null;
$finish_date = intval( $obj->project_finish_date ) ? new CDate( $obj->project_finish_date ) : null;

$task_start_date = $objPr->getStartDateFromTask($project_id);
$task_start_date['task_start_date'] = intval( $task_start_date['task_start_date'] ) ? new CDate( $task_start_date['task_start_date'] ) : "-";
$task_finish_date = $objPr->getFinishDateFromTask($project_id);
$task_finish_date['task_finish_date'] = intval( $task_finish_date['task_finish_date'] ) ? new CDate( $task_finish_date['task_finish_date'] ) : "-";

$actual_start_date = $objPr->getActualStartDate($project_id);
$actual_start_date['task_log_start_date'] = intval( $actual_start_date['task_log_start_date'] ) ? new CDate( $actual_start_date['task_log_start_date'] ) : "-";
$actual_finish_date = $objPr->getActualFinishDate($project_id);
$actual_finish_date['task_log_finish_date'] = intval( $actual_finish_date['task_log_finish_date'] ) ? new CDate( $actual_finish_date['task_log_finish_date'] ) : "-";

$today = intval( $obj->project_today ) ? new CDate( $obj->project_today ) : null;

$style1 = (( $task_start_date['task_start_date'] < $start_date) && !empty($start_date)) ? 'style="color:red"' : '';
$style2 = (( $task_finish_date['task_finish_date'] > $finish_date) && !empty($finish_date)) ? 'style="color:red"' : '';
$style3 = (( $actual_start_date['task_log_start_date'] < $start_date) && !empty($start_date)) ? 'style="color:red"' : '';
$style4 = (( $actual_finish_date['task_log_finish_date'] > $finish_date) && !empty($finish_date)) ? 'style="color:red"' : '';

// setup the title block
$titleBlock = new CTitleBlock( 'View Project', 'applet3-48.png', $m, "$m.$a");
//$titleBlock->addCrumb( "?m=projects", "Projects list" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=projects&a=addedit&project_id=$project_id", "Edit project" );
}

if ($canDelete)
	$titleBlock->addCrumbDelete( 'Delete project', $canDelete, $msg );

if ($canEdit)
	$titleBlock->addCrumb( "?m=projects&a=saveas&project_id=$project_id", "Archive project" );
	
$titleBlock->addCrumb( "?m=projects&a=effort&project_id=$project_id", "Effort analysis" );

$titleBlock->addCrumb( "?m=report&a=view&project_id=$project_id", "Project Reports" );

if ($canEdit) 
	$titleBlock->addCrumb("?m=tasks&a=addedit&task_project=$project_id", "New task");
	
$titleBlock->show();

for($i=0;$i<count($AppUI->properties);$i++){
	if(strstr($AppUI->properties[$i], "Project isn't")!=false)
		{
		$message.=$AppUI->properties[$i]."|";
		}
}

$sql="SELECT * FROM reports WHERE project_id=".$project_id." AND user_id=".$user_id;
$exist=db_loadList($sql);

if(count($exist)==0){
	$sql="INSERT INTO `reports` ( `report_id` , `project_id` , `user_id` , `p_is_incomplete` , `p_report_level` , `p_report_roles` , `p_report_sdate` , `p_report_edate` , `p_report_opened` , `p_report_closed` , `a_is_incomplete` , `a_report_level` , `a_report_roles` , `a_report_sdate` , `a_report_edate` , `a_report_opened` , `a_report_closed` , `l_hide_inactive` , `l_hide_complete` , `l_user_id` , `l_report_sdate` , `l_report_edate` , `properties`, `prop_summary` )
	VALUES ( NULL , ".$project_id." , ".$user_id." , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL, NULL);";		
	db_exec( $sql ); db_error();
}

if($_POST['properties']){
	//$AppUI->setMsg("Added to Report",5);
	$string=urldecode($_POST['properties']); 
	$sql="UPDATE reports SET properties='".$string."', prop_summary='".$_POST['summary']."' WHERE project_id=".$project_id." AND reports.user_id=".$user_id;
	$db_roles = db_loadList($sql);													
}
 
?>
<script language="javascript">
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
</script>

<table border="0" cellpadding="1" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="project_group" value="<?php echo $obj->project_group;?>" />
</form>

<tr>
	<td style="border: outset #d1d1cd 1px;background-color:#<?php echo $obj->project_color_identifier;?>" colspan="2">
	<?php 
		echo '<font color="' . bestColor( $obj->project_color_identifier ) . '"><strong>'
			. $obj->project_name .'<strong></font>';
	?>
	</td>
</tr>

<tr>
	<td width="50%" valign="top"  style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Base Information');?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Group');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo htmlspecialchars( $obj->group_name, ENT_QUOTES) ;?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Effort');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo @$obj->project_effort; ?> ph</td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Start Date');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Target Budget');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo @$obj->project_target_budget." ".$dPconfig['currency_symbol'] ?></td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Finish Date');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $finish_date ? $finish_date->format( $df ) : '-';?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Hard Budget');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo @$obj->project_hard_budget." ".$dPconfig['currency_symbol'] ?></td>
			</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Computed Information at');echo " ".(!is_null($today) ? " ".$today->format( $df ) : ' -');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Start Date from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%">
                     <?php if ($project_id > 0 && count($task_start_date) > 0 && $task_start_date['task_start_date'] <>"-") { ?>
                            <?php echo $task_start_date ? '<a href="?m=tasks&a=view&task_id='.$task_start_date['task_id'].'">' : '';?>
                            <?php echo $task_start_date ? '<span '. $style1.'>'.$task_start_date['task_start_date']->format( $df ).'</span>' : '-';?>
                            <?php echo $task_start_date ? '</a>' : '';?>
                     <?php } else { echo "-";} ?>
	            </td>
	            <td align="right" nowrap width="25%"><?php echo $AppUI->_('First Log Date');?>:</td>
	            <td class="hilite" nowrap width="25%">
					 <?php if ($project_id > 0 && count($actual_start_date) > 0 && $actual_start_date['task_log_start_date'] <>"-") { ?>
                            <?php echo $actual_start_date ? '<a href="?m=tasks&a=view&task_id='.$actual_start_date['task_id'].'">' : '';?>
                            <?php echo $actual_start_date ? '<span '. $style3.'>'.$actual_start_date['task_log_start_date']->format( $df ).'</span>' : '-';?>
                            <?php echo $actual_start_date ? '</a>' : '';?>
                     <?php } else { echo "-";} ?>
	            </td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Finish Date from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%">
                     <?php if ($project_id > 0 && count($task_finish_date) > 0 && $task_finish_date['task_finish_date'] <>"-") { ?>
                            <?php echo $task_finish_date ? '<a href="?m=tasks&a=view&task_id='.$task_finish_date['task_id'].'">' : '';?>
                            <?php echo $task_finish_date ? '<span '. $style2.'>'.$task_finish_date['task_finish_date']->format( $df ).'</span>' : '-';?>
                            <?php echo $task_finish_date ? '</a>' : '';?>
                     <?php } else { echo "-";} ?>
	            </td>
	            <td align="right" nowrap width="25%"><?php echo $AppUI->_('Last Log Date');?>:</td>
				<td class="hilite" nowrap width="50" width="25%">
                     <?php if ($project_id > 0 && count($actual_finish_date) > 0 && $actual_finish_date['task_log_finish_date'] <>"-") { ?>
                            <?php echo $actual_finish_date ? '<a href="?m=tasks&a=view&task_id='.$actual_finish_date['task_id'].'">' : '';?>
                            <?php echo $actual_finish_date ? '<span '. $style4.'>'.$actual_finish_date['task_log_finish_date']->format( $df ).'</span>' : '-';?>
                            <?php echo $actual_finish_date ? '</a>' : '';?>
                     <?php } else { echo "-";} ?>
	            </td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Effort from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $objPr->getEffortFromTask($project_id)." ph"; ?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Actual Effort');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $ae = $objPr->getActualEffort($project_id); echo $ae." ph"; ?></td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Budget from Tasks');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $objPr->getBudgetFromTask($project_id)." ".$dPconfig['currency_symbol']; ?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Actual Cost');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $ac = $objPr->getActualCost($project_id); echo $ac." ".$dPconfig['currency_symbol']; ?></td>
			</tr>	
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Progress');?>:</td>
				<td class="hilite" nowrap width="25%"><?php $pr = $objPr->getProgress($project_id,@$obj->project_effort);echo $pr;?>%</td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Effort Performance Index');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $objPr->getEffortPerformanceIndex($project_id,$ae,@$obj->project_effort,$pr); ?></td>
			</tr>
			<tr>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Time Performance Index');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $objPr->getTimePerformanceIndex($project_id,null,$start_date,$finish_date,$actual_finish_date['task_log_finish_date'],$pr); ?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Cost Performance Index');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $objPr->getCostPerformanceIndex($project_id,$ac,$obj->project_target_budget,$pr); ?></td>
			</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Assigned to project');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
				<?php  
					$q->clear();
					$q->addTable('user_projects','up');
					$q->addQuery('CONCAT_WS(", ",u.user_last_name,u.user_first_name) as nm, u.user_email as um, pr.proles_name as pn');
					$q->addJoin('users','u','u.user_id=up.user_id');
					$q->addJoin('project_roles','pr','pr.proles_id = up.proles_id');
					$q->addWhere('up.proles_id > 0 && up.project_id = '.$project_id);
					$ar_ur = $q->loadList();
					if (!is_null($ar_ur) && !empty($ar_ur)){
						foreach ($ar_ur as $ur) 
							$r["<a href=\"mailto:".$ur['um']."\">".$ur['nm']."</a>"].=$ur['pn'].", ";
						foreach ($r as $u => $pr) {
							$pr{strlen($pr)-2}=")</td></tr>";
							echo "<tr><td class=\"hilite\">".$u."<i> (".str_pad($pr,strlen($pr)-5,'T')."</i>";
						}
					}
				?>
		</table>
	</td>
	<td width="50%" rowspan="9" valign="top" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Details');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
			
			<tr>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Status');?>:</td>
				<td class="hilite"  nowrap width="25%"><?php echo $AppUI->_($pstatus[$obj->project_status]);?></td>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Short Name');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo htmlspecialchars( @$obj->project_short_name, ENT_QUOTES) ;?></td>
			</tr>
			<tr>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Type');?>:</td>
				<td class="hilite"  nowrap width="25%"><?php echo $AppUI->_($ptype[$obj->project_type]);?></td>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Active');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No');?></td>
			</tr>
			<tr>
				<td align="right"  nowrap width="25%"><?php echo $AppUI->_('Priority');?>:</td>
				<td class="hilite"  nowrap width="25%" style="background-color:<?php echo $projectPriorityColor[$obj->project_priority]?>"><?php echo $AppUI->_($projectPriority[$obj->project_priority]);?></td>
				<td align="right" nowrap width="25%"><?php echo $AppUI->_('Project Creator');?>:</td>
				<td class="hilite" nowrap width="25%"><?php echo $obj->user_name; ?></td>
			</tr>
			<tr>
				<td align="right" nowrap><?php echo $AppUI->_('URL');?>:</td>
				<td class="hilite" colspan="3" width="100%"><a href="<?php echo @$obj->project_url;?>" target="_new"><?php echo @$obj->project_url;?></A></td>
			</tr>
			<tr>
				<td align="right" nowrap><?php echo $AppUI->_('Description');?>:</td>
				<td class="hilite" colspan="3" width="100%">
					<?php echo str_replace( chr(10), "<br>", $obj->project_description) ; ?>&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<?php
					require_once("./classes/CustomFields.class.php");
					$custom_fields = New CustomFields( $m, $a, $obj->project_id, "view" );
					$custom_fields->printHTML();
				?>
				</td>
			</tr>
		</table>
		<hr align="center" style="border: outset #d1d1cd 1px">
		<strong><?php echo $AppUI->_('Properties');?></strong><br>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
				<form name="frmProp" action="./index.php?m=projects" method="post">
				<input type="hidden" name="dosql" value="do_properties" />
				<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Well Formed');?>:
					<td align="left" nowrap> <input id="wf" name="wf" type="checkbox" value="1"></td>
					<td class="hilite" width="100%" rowspan="4" colspan="2" valign="top" style="border: outset #d1d1cd 2px">
						<?php
							if($_POST['properties']){
							 	$string=$_POST['properties'];
								$string=urldecode($string);
								echo str_replace("@","'",$string);
							}
							else{
						 		echo $string=str_replace( chr(10), "<br>", $AppUI->getProperties()) ;
								if($string!=''){
								$string=str_replace("'","@",$string);
								}
							}
					
						
						?>&nbsp;
					</td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Cost Effective');?>:
					<td align="left" nowrap> <input id="ce" name="ce" type="checkbox" value="1"></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Effort Effective');?>:
					<td align="left" nowrap> <input id="ee" name="ee" type="checkbox" value="1"></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_('Time Effective');?>:
					<td align="left" nowrap><input id="te" name="te" type="checkbox" value="1"></td>
				</tr>
				<tr>
					<td align="left" nowrap>
						<input type="submit" class="button" value="<?php echo $AppUI->_( 'compute ' );?>">
					</td>
					<td align="left">
					</td>
			</form>
			<form name="prop_report" action=<?echo './index.php?m=projects&a=view&project_id='.$project_id;?> method="post">
					<td nowrap="nowrap" align="right" width="100%">
					
					<?if ($_POST['make_prop_pdf']=="true")	{
						include('modules/report/makePDF.php');
	
						$task_level=$explodeTasks;
						$q  = new DBQuery;
						$q->addQuery('projects.project_name');
						$q->addTable('projects');
						$q->addWhere("project_id = $project_id ");
						$name = $q->loadList();
						$pdf = PM_headerPdf($name[0]['project_name']);
						PM_makePropPdf($pdf, str_replace("@","'",$string),$project_id,'P');
						
						$filename=PM_footerPdf($pdf, $name[0]['project_name'], 4);
						?>
						<a href="<?echo $filename;?>" TARGET="_new"><img src="./modules/report/images/pdf_report.gif" alt="PDF Report" border="0" align="absbottom"></a><?
				}?>
						<input type="hidden" name="properties" value="<?php echo strip_tags($string,"<br>");?>" />
						<input type="hidden" name="make_prop_pdf" value="false" />
						<input type="button" class="button" value="<?php echo $AppUI->_( 'Make PDF' );?>" onclick='document.prop_report.make_prop_pdf.value="true"; document.prop_report.submit();'>
						
					</td>
					<td>
						<? 
						$string=urlencode($string);
						?>
						
							<input type="hidden" name="properties" value="<?php echo strip_tags($string,"<br>");?>" />
							<input type="hidden" name="summary" value="<?php echo $message;?>" />
							<input type="submit" class="button" value="<?php echo $AppUI->_( 'Add to Report ' );?>">
						
					</td>
			</form>
				</tr>
		</table>
	</td>
</tr>
</table>
<br>
<?php

$tabBox = new CTabBox( "?m=projects&a=view&project_id=$project_id", "", $tab );
$query_string = "?m=projects&a=view&project_id=$project_id";
// tabbed information boxes
// Note that we now control these based upon module requirements.
$canViewTask = $perms->checkModule('tasks', 'view','',intval($obj->project_group),1);
if ($canViewTask) {
	$tabBox->add( dPgetConfig('root_dir')."/modules/tasks/tasks", 'Tasks (Planned view)' );
	$tabBox->add( dPgetConfig('root_dir')."/modules/tasks/tasks", 'Tasks (Actual view)');
	//$tabBox->add( dPgetConfig('root_dir')."/modules/tasks/tasks", 'Tasks (Inactive)' );
}
//if ($perms->checkModule('files', 'view'))
//	$tabBox->add( dPgetConfig('root_dir')."/modules/projects/vw_files", 'Files' );
if ($canViewTask) {
	$tabBox->add( dPgetConfig('root_dir')."/modules/tasks/viewgantt", 'Gantt Chart' );
	$tabBox->add( dPgetConfig('root_dir')."/modules/projects/vw_logs", 'Task Logs' );
}
$tabBox->loadExtras($m);
$f = 'all';
$min_view = true;

$tabBox->show();
?>
