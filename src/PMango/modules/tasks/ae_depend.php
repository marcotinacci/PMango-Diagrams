<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      task dependencies management.

 File:       ae_depend.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango task dependencies.
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

global $AppUI, $dPconfig, $loadFromTab;
global $obj, $task_parent_options;
global $durnTypes, $task_project, $task_id, $tab;

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

$sql = "
	SELECT t.task_id, t.task_name
	FROM tasks t, task_dependencies td
	WHERE td.dependencies_task_id = $task_id
	AND t.task_id = td.dependencies_req_task_id
	ORDER BY t.task_id
";
$taskDep = db_loadHashList( $sql );
foreach ($taskDep as $ti => $tn) 
	$taskDep[$ti] = CTask::getWBS($ti)." ".$tn;
	
$sql = "
	SELECT dependencies_task_type_id, dependencies_task_type_name
	FROM type_dependencies 
";
$typeDep = db_loadHashList( $sql );

?>
<form name="dependFrm" action="?m=tasks&a=addedit&task_project=<?php echo $task_project;?>" method="post">
<input name="dosql" type="hidden" value="do_task_aed" />
<input name="task_id" type="hidden" value="<?php echo $task_id;?>" />
<input name="sub_form" type="hidden" value="1" />
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="std">
	<tr>
		<td>
			<table width="40%" border="0" cellpadding="2" cellspacing="0" align="center">
			<tr>
				<td align="center"><b><?php echo $AppUI->_( 'All Tasks' );?>:</b></td>
				<td align="center"><b><?php echo $AppUI->_( 'Task Dependencies' );?>:</b></td>
			</tr>
			<tr>
				<td align="right">
					<select name='all_tasks' class="text" style="width:380px" size="16" class="text" multiple="multiple">
						<?php echo str_replace("selected", "", $task_parent_options); // we need to remove selected added from task_parent options ?>
					</select>
				</td>
				<td align="left">
					<?php echo arraySelect( $taskDep, 'task_dependencies', 'style="width:380px" size="16" class="text" multiple="multiple" ', null ); ?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table>
			<tr>
				<td align="right"><input type="button" class="button" value="&gt;" onClick="addTaskDependency(document.dependFrm)" /></td>
				<td align="center">
					<select name="dep_ass" style="width:110px" class="text" disabled>
					<?php 
						foreach ($typeDep as $tid => $tname) {
							echo "<option value=\"".$tid."\">".$tname."</option>";
						}
					?>
					</select>
				</td>				
				<td align="left"><input type="button" class="button" value="&lt;" onClick="removeTaskDependency(document.dependFrm)" /></td>
			</tr>
			</table>
		</td>
	</tr>
</table>
<input type="hidden" name="hdependencies" />
</form>
<script language="javascript">
  subForm.push( new FormDefinition(<?php echo $tab; ?>, document.dependFrm, checkDetail, saveDepend));
</script>
