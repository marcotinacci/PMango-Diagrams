<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      backup module home page 
 File:       index.php
 Location:   pmango\modules\backup
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango backup.
 - 2006.07.26 Lorenzo
   First version, unmodified from dotProject 2.0.1.
   
---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team

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
 Foundation Inc., 51 Franklin St, 5th Floor, Boston, MA 02110-1301 USA

---------------------------------------------------------------------------
*/

$perms =& $AppUI->acl();
$groups=array();
foreach ($AppUI->user_groups as $g => $sc)
	if ($perms->checkModule('backup', 'view','',$sc,1))	// Should we have an exec permission?
		$groups[]=$g;
		
if (count($groups)==0)
	$AppUI->redirect("m=public&a=access_denied");

$title = new CTitleBlock('Backup', 'companies.gif', $m, "$m.$a");
$title->show();

$q = new DBQuery();
$q->addTable('groups');
$q->addQuery('group_id, group_name');
$groupsName = $q->loadHashList();

?>
<script>
	function check_backup_options()
	{
		var f = document.frmBackup;
		if(f.export_what.options[f.export_what.selectedIndex].value == 'data') {
			f.droptable.enabled=false;
			f.droptable.checked=false;
		}
		else {
			f.droptable.enabled=true;
		}
	}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
	<form name="frmBackup" action="<?php echo "$baseUrl/index.php?m=backup&a=do_backup&suppressHeaders=1"; ?>" method="post">
	<tr>
		<td align="right" valign="top"  nowrap="nowrap"><?php echo $AppUI->_('Group'); ?>:</td>
		<td width="100%" nowrap="nowrap">
			<select style="width:200px" name="group_id" class="text" 
			<?php if (in_array(-1,$groups)) {?>disabled>	
					<option checked="checked"><?php echo $AppUI->_('All groups'); ?></option>
			<?php } else {?>> 
				<?php foreach ($groups as $g) {?>
					<option value="<?php echo $g?>" ><?php echo $groupsName[$g] ?></option>
				<?php }?>
			<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top" nowrap="nowrap">
			<?php echo $AppUI->_('Export'); ?>:
		</td>
		<td width="100%" nowrap="nowrap">
			<select name="export_what" style="width:200px" class="text" 
				<?php if (in_array(-1,$groups)) {?>>
					<option value="data" checked="checked"><?php echo $AppUI->_('Only data'); ?></option>
					<option value="all"><?php echo $AppUI->_('Table structure and data'); ?></option>
					<option value="table"><?php echo $AppUI->_('Only table structure'); ?></option>
				<?php } else {?> disabled>	
					<option checked="checked"><?php echo $AppUI->_('Data group'); ?></option>
				<?php }?>	
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap"><?php echo $AppUI->_('Save as'); ?>:</td>
		<td width="100%" nowrap="nowrap">
			<select style="width:200px" name="output_format" class="text" >
				<option value="zip" checked="checked"><?php echo $AppUI->_('Compressed ZIP SQL file', UI_OUTPUT_RAW); ?></option>
				<option value="sql"><?php echo $AppUI->_('Plain text SQL file', UI_OUTPUT_RAW); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;
		</td>
		<td align="right">
			<input type="submit" value="<?php echo $AppUI->_('Download backup'); ?>" class="button"/>
		</td>
	</tr>
	</form>
</table>
