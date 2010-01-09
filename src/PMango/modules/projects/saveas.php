<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      save project in a version

 File:       saveas.php
 Location:   pmango\modules\projects
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   First version, created to save a project in a version.

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
$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery("group_name, projects.*");
$q->addJoin('groups', 'g', 'group_id = project_group');
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

// check permissions to archive projects. It is necessary add and edit permission
$perms =& $AppUI->acl();
$canRead = $perms->checkModule($m, 'view','',intval($obj->project_group),1) && $obj->project_current == '0';
$canSave = $perms->checkModule($m, 'edit','',intval($obj->project_group),1) && $obj->project_current == '0';
		
if (!$canSave || !$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

	
$title = new CTitleBlock('Archive project', 'applet3-48.png', $m, "$m.$a");
/*if ($canRead)
	$title->addCrumb( "?m=projects", "Projects list" );*/
$title->show();

?>
<script>
	function submitIt() {
	var f = document.frmSave;
	var msg = '';

	if (f.version.value.length < 1) {
		msg += "\n<?php echo $AppUI->_('projectsVersion', UI_OUTPUT_JS);?>";
		f.version.focus();
	}
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
	<form name="frmSave" action="<?php echo "./index.php?m=projects&a=do_saveas"; ?>" method="post">
	<input type="hidden" name="project_id" value="<?php echo $obj->project_id;?>" />
	<input type="hidden" name="project_group" value="<?php echo $obj->project_group;?>" />
	<tr>
		<td align="right" valign="top"  nowrap="nowrap">
			<?php echo $AppUI->_('Today'); ?>:
		</td>
		<td width="100%" nowrap="nowrap">
			<?php 
				$today = new CDate();
				echo $today->format($AppUI->getPref('SHDATEFORMAT')); 
			?>
			<input type="hidden" name="today" value="<?php echo $today->format( FMT_TIMESTAMP_DATE );?>" />
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap">
			<?php echo $AppUI->_('Group name'); ?>:
		</td>
		<td width="100%" nowrap="nowrap">
			<?php echo $obj->group_name; ?>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap">
			<?php echo $AppUI->_('Project name'); ?>:
		</td>
		<td width="100%" nowrap="nowrap">
			<?php echo $obj->project_name; ?>
		</td>
	</tr>
	<tr>
		<td align="right" valign="middle"  nowrap="nowrap">
			<?php echo $AppUI->_('Version'); ?>:
		</td>
		<td width="100%" nowrap="nowrap">
			<input class="text" type="text" id="version" name="version" value="" maxlength="8" size="8">
		</td>
	</tr>
	<tr>
		<td align="right" valign="middle"  nowrap="nowrap">
			<?php echo $AppUI->_('Description'); ?>:
		</td>
		<td width="100%" nowrap="nowrap">
			<textarea name="description" cols="100" rows="4" wrap="virtual" class="textarea"></textarea>
		</td>
	</tr>
	<tr>
		<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" /></td>
		<td align="right">
			<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt();" />	</td>
	</tr>
	</form>
</table>