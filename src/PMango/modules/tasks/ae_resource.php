<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      management task resources.

 File:       ae_resource.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango task resources.
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
global $AppUI, $users, $roles, $meffort, $dwh, $task_id, $task_project, $obj, $tab, $loadFromTab;
global $redUsers, $redRoles, $redDwh;

$ass_users = array();
$ass_roles = array();
$ass_mh = array();
$ass_perc = array();
$ass_resources = "";
if ( $task_id > 0 ) {
	$q = new DBQuery;
	$q->addQuery('u.user_id, user_first_name, user_last_name, user_day_hours, ut.proles_id, proles_name, ut.effort, ut.perc_effort');
	$q->addTable('users','u');
	$q->addJoin('user_tasks','ut','u.user_id = ut.user_id');
	$q->addJoin('project_roles','pr','pr.proles_id = ut.proles_id');
	$q->addWhere('ut.proles_id > 0 && ut.task_id ='.$task_id);
	$q->addOrder('user_last_name, user_first_name');
	$q->exec();
	
	while ( $row = $q->fetchRow()) {
		$ass_users[$row['user_id'].",".$row['proles_id']] = $row['user_last_name'] . ", " . $row['user_first_name'];
		
		$ass_roles[$row['user_id'].",".$row['proles_id']] = $row['proles_name'];
		
		$ass_mh[$row['user_id'].",".$row['proles_id']] = $row['effort'];
		
		$ass_perc[$row['user_id'].",".$row['proles_id']] = $row['perc_effort'];
		
		$ass_resources .= "|".$row['user_id'].",".$row['proles_id'].",".$row['effort'].",".$row['perc_effort'];
	}
	if (count($ass_users)>0)
		$ass_resources .= "|";
	$q->clear();
}
if ($dPconfig['effort_perc']) {
	$personWidth = "148";
	$roleWidth = "152";
	$effortWidth = "95";
} else {
	$personWidth = "180";
	$roleWidth = "170";
	$effortWidth = "100";
}
?>
<script language="javascript">
var wl_desc = '%';
</script>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>"
  method="post" name="resourceFrm" >
<input type="hidden" name="sub_form" value="1" />
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
<input type="hidden" name="dosql" value="do_task_aed" />
<table width="100%" border="1" cellpadding="4" cellspacing="0" class="std">
<tr>
	<td valign="top" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<?php if ($dPconfig['effort_perc']) {?>
				<tr>
					<td align="center" nowrap="nowrap" colspan="2"><?php echo $AppUI->_( 'Type of effort' ).":";?>&nbsp;
						<?php echo $AppUI->_( 'person hours' );?>
							<input type="radio" name="work_load" value="1" onclick="changeWorkLoad(document.resourceFrm)" checked/>&nbsp;
						<?php echo $AppUI->_( 'percentage' );?>
							<input type="radio" name="work_load" value="0" onclick="changeWorkLoad(document.resourceFrm)"/>
					</td>
				</tr>
			<?php }?>
			<tr>
				<td align="center"><b><?php echo $AppUI->_( 'Resources' );?>:</b></td>
				<?php if (!$dPconfig['effort_perc']) {?>
							<td align="left">
								&nbsp;&nbsp;
							</td>
				<?php }?>
				<td align="center"><b><?php echo $AppUI->_( 'Assigned to Task' );?>:</b></td>
			</tr>
			<tr>
				<td>
					<table bordercolor="#111111" cellpadding="0" cellspacing="1" border="0" bgcolor="#111111">
					<tr>
						<td align="center" bgcolor="#DDCC68">
							<?php echo $AppUI->_( 'Person' ).":"; ?>
						</td>
						<td align="center" bgcolor="#DDCC68">
							<?php echo $AppUI->_( 'Role' ).":"; ?>
						</td>
						<td align="center" bgcolor="#DDCC68">
							<?php echo $AppUI->_( 'Max Effort' ).":"; ?>
						</td>
						<?php if ($dPconfig['effort_perc']) {?>
							<td align="center" bgcolor="#DDCC68">
								<?php echo $AppUI->_( 'Daily working' ).":"; ?>
							</td>
						<?php }?>
					</tr>
					<tr>
						<td align="left">
							<select name="users" id="users" class="text" style="width:<?php echo $personWidth?>px" size="15" class="text" multiple="multiple" onchange="selectPeople(document.resourceFrm,1)">
							<?php 
								foreach ($users as $urid => $uname) {
									echo "<option value=\"".$urid."\">".$uname."</option>";
								}
								foreach ($redUsers as $urid => $uname) {
									echo "<option style=\"color: red\" value=\"".$urid."\">".$uname."</option>";
								}
							?>
							</select>
						</td>
						<td align="left">
							<select name="roles" id="roles" class="text" style="width:<?php echo $roleWidth?>px" size="15" class="text" multiple="multiple" onchange="selectPeople(document.resourceFrm,2)">
							<?php 
								foreach ($roles as $urid => $role) {
									echo "<option value=\"".$urid."\">".$role."</option>";
								}
								foreach ($redRoles as $urid => $role) {
									echo "<option style=\"color: red\" value=\"".$urid."\">".$role."</option>";
								}
							?>
							</select>
							<?php // echo arraySelect( $roles, 'roles', 'style="width:'.$roleWidth.'px" size="15" class="text" multiple="multiple" onchange="selectPeople(document.resourceFrm,2)"', null ); ?>
						</td>
						<td align="left">
							<select name="meffort" id="meffort" class="text" style="width:<?php echo $effortWidth?>px" size="15" class="text" multiple="multiple" onchange="selectPeople(document.resourceFrm,3)">
							<?php 
								foreach ($meffort as $urid => $effort) {
									echo "<option value=\"".$urid."\">".$effort."</option>";
								}
								foreach ($redRoles as $urid => $effort) {
									echo "<option style=\"color: red\" value=\"".$urid."\">-</option>";
								}
							?>
							</select>
						</td>
						<?php if ($dPconfig['effort_perc']) {?>
							<td align="left">
								<select name="dwh" id="dwh" class="text" style="width:84px" size="15" class="text" multiple="multiple" onchange="selectPeople(document.resourceFrm,4)">
								<?php 
									foreach ($dwh as $urid => $d) {
										echo "<option value=\"".$urid."\">".$d."</option>";
									}
									foreach ($redDwh as $urid => $d) {
										echo "<option style=\"color: red\" value=\"".$urid."\">".$d."</option>";
									}
								?>
								</select>
								<?php //echo arraySelect( $dwh, 'dwh', 'style="width:84px" size="15" class="text" multiple="multiple" onchange="selectPeople(document.resourceFrm,4)"', null ); ?>
							</td>
						<?php }?>
					</tr>
					</table>
					<?php if (!$dPconfig['effort_perc']) {?>
							<td align="left">
								&nbsp;&nbsp;
							</td>
					<?php }?>
				</td>
				<td>
					<table bordercolor="#111111" cellpadding="0" cellspacing="1" border="0" bgcolor="#111111">
					<tr>
						<td align="center" bgcolor="#DDCC68">
							<?php echo $AppUI->_( 'Person' ).":"; ?>
						</td>
						<td align="center" bgcolor="#DDCC68">
							<?php echo $AppUI->_( 'Role' ).":"; ?>
						</td>
						<td align="center" bgcolor="#DDCC68">
							<?php echo $AppUI->_( 'Effort' ).":"; ?>
						</td>
						<?php if ($dPconfig['effort_perc']) {?>
							<td align="center" bgcolor="#DDCC68">
								<?php echo $AppUI->_( 'Percentage' ).":"; ?>
							</td>
						<?php }?>
					</tr>
					<tr>
						<td align="left">
							<?php echo arraySelect( $ass_users, 'ass_users', 'style="width:'.$personWidth.'px" size="15" class="text" multiple="multiple" onchange="selectAssigned(document.resourceFrm,1)"', null ); ?>
						</td>
						<td align="left">
							<?php echo arraySelect( $ass_roles, 'ass_roles', 'style="width:'.$roleWidth.'px" size="15" class="text" multiple="multiple" onchange="selectAssigned(document.resourceFrm,2)"', null ); ?>
						</td>
						<td align="left">
							<select name="ass_mh" style="width:<?php echo $effortWidth?>px" size="15" multiple="multiple" class="text" onchange="selectAssigned(document.resourceFrm,3)">
	                                <?php foreach ($ass_mh as $i => $mh) {
	                                echo "\n\t<option value=\"".$mh."\">" . $mh . " ph</option>";
	                                }?>
	                        </select>
                        </td>
						<?php if ($dPconfig['effort_perc']) {?>
                        <td align="left">
	                        <select name="ass_perc" style="width:84px" size="15" multiple="multiple" class="text" onchange="selectAssigned(document.resourceFrm,4)">
	                                <?php foreach ($ass_perc as $i => $perc) {
	                                echo "\n\t<option value=\"".$perc."\">" . $perc . " %</option>";
	                                }?>
	                        </select>
                        </td>
                        <?php }?>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3" align="center">
					<table>
					<tr>
						<td align="right">
							<input type="button" class="button" value="&gt;" onClick="addUser(document.resourceFrm, document.editFrm)" />
						</td>
						<td valign="middle">
							<input class="text" type="text" name="wl_value" id="wl_value" size="4" maxlength="6" value="">		
							<input class="text" type="text" name="wl_desc" id="wl_desc" size="4" value="ph" disabled>		
						</td>				
						<td align="left">
							<input type="button" class="button" value="&lt;" onClick="removeUser(document.resourceFrm)" />
						</td>					
					</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>
<input type="hidden" name="ass_resources" value="<?php echo $ass_resources;?>"/>
</form>
<script language="javascript">
  subForm.push(new FormDefinition(<?php echo $tab; ?>, document.resourceFrm, checkResource, saveResource));
</script>
