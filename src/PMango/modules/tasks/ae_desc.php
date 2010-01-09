<?php

/**
---------------------------------------------------------------------------

 PMango Project

 Title:      management task description.

 File:       ae_desc.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango task.
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
global $AppUI, $task_id, $obj, $users, $task_access, $department_selection_list, $status, $priority, $percent;
global $dPconfig, $projects, $task_project, $tab;

$perms =& $AppUI->acl();//??
?>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>"
  method="post"  name="detailFrm">
<input type="hidden" name="dosql" value="do_task_aed" />
<input type="hidden" name="sub_form" value="1" />
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>"/>
<table class="std" width="100%" border="1" cellpadding="4" cellspacing="0">
<tr>
	<td width="50%" valign='top'>
	    <table border="0">
		    <tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Status' ).":";?></td>
				<td align="left">
					<?php  echo arraySelect( $status, 'task_status', 'style="width:200px" class="text"', $obj->task_status, true );?>
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Priority' ).":";?></td>
				<td nowrap>
					<?php echo arraySelect( $priority, 'task_priority', 'style="width:200px" class="text"', $obj->task_priority, true );?>
				</td>
			</tr>
			
	    	<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_("Task Type").":"; ?></td>
				<td align="left">
						<?php echo arraySelect(dPgetSysVal("TaskType"), "task_type",  "style=\"width:200px\" class='text'", $obj->task_type, false); ?>
				</td>
			</tr>
			<!--<tr>
	    		<td align="right" nowrap="nowrap"><?php //echo $AppUI->_( 'Access' ).":";?></td>
				<td align="left">
						<?php //echo arraySelect( $task_access, 'task_access', 'style="width:200px" class="text"', intval( $obj->task_access ), true );?>
				</td>
			</tr>-->
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Web Address' ).":";?></td>
				<td align="left">
						<input type="text" class="text" name="task_related_url" value="<?php echo @$obj->task_related_url;?>" size="31" maxlength="255" />
				</td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Milestone' ).":";?></td>
				<td align="left">
					<input type="checkbox" value=1 name="task_milestone" <?php if($obj->task_milestone){?>checked<?php }?> />
				</td>
			</tr>
		</table>
	</td>
	<td valign="top" align="center">
		<table>
			<tr>
				<td align="left" nowrap>
					<?php echo $AppUI->_( 'Description' );?>:<br>
					<textarea name="task_description" class="textarea" cols="60" rows="10" wrap="virtual"><?php echo dPformSafe(@$obj->task_description);?></textarea>
				</td>
			</tr>
		</table>
		<br />
		<?php
			require_once("./classes/CustomFields.class.php");
			GLOBAL $m;
			$custom_fields = New CustomFields( $m, 'addedit', $obj->task_id, "edit" );
			$custom_fields->printHTML();
		?>
	</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</form>
<script language="javascript">
 subForm.push(new FormDefinition(<?php echo $tab;?>, document.detailFrm, checkDetail, saveDetail));
</script>
