<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      main page set of capabilities administration.

 File:       index.php
 Location:   pmango\modules\system\roles
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango sets of capabilities.
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

$AppUI->savePlace();

// pull all the key types
$perms =& $AppUI->acl();

// Get the permissions for this module

$canAccess = $perms->checkModule('roles','access','',$AppUI->user_groups[-1],1);
if (! $canAccess) {
	$AppUI->redirect("m=public&a=access_denied");
}
$canRead = $perms->checkModule('roles','view','',$AppUI->user_groups[-1],1);
$canAdd = $perms->checkModule('roles','add','',$AppUI->user_groups[-1],1);
$canEdit = $perms->checkModule('roles','edit','',$AppUI->user_groups[-1],1);
$canDelete = $perms->checkModule('roles','delete','',$AppUI->user_groups[-1],1);

$crole =& new CRole;
$roles = $crole->getRoles();

$role_id = dPgetParam( $_GET, 'role_id', 0 );

$modules = 
$sql = "SELECT mod_id, mod_name FROM modules WHERE mod_active > 0 ORDER BY mod_directory";
$modules = arrayMerge( array( '0'=>'All' ), db_loadHashList( $sql ) );

// setup the title block
$titleBlock = new CTitleBlock( 'Sets of Capabilities', 'main-settings.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "System admin" );
$titleBlock->show();

$crumbs = array();
$crumbs["?m=system"] = "System Admin";

?>

<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.roleFrm;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Sets of Capabilities ID');?></th>
	<th><?php echo $AppUI->_('Description');?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow( $role=null ) {
	global $canEdit, $canDelete, $role_id, $AppUI, $modules;
	$CR = "\n";
	$id = $role['id'];
	$name = $role['value'];
	$description = $role['name'];

	$s = '<tr>'.$CR;
	if (($role_id == $id || $id == 0) && $canEdit) {
	// edit form
		
			$s .= '<form name="roleFrm" method="post" action="?m=system&u=roles">'.$CR;
			$s .= '<input type="hidden" name="dosql" value="do_role_aed" />'.$CR;
			$s .= '<input type="hidden" name="del" value="0" />'.$CR;
			$s .= '<input type="hidden" name="role_id" value="'.$id.'" />'.$CR;
		
		$s .= '<td>&nbsp;</td>';
		$s .= '<td valign="top"><input type="text" name="role_name" value="'.$name.'" class="text" /></td>';
		$s .= "<td valign='top'><input type='text' name='role_description' class='text' value='$description'></td>";
		$s .= '<td><input type="submit" value="'.$AppUI->_($id ? 'edit' : 'add').'" class="button" /></td>';
	} else {
		
		$s .= '<td width="50" valign="top">';
		if ($role['value']<>'admin') {
			if ($canEdit) {
				$s .= '<a href="?m=system&u=roles&role_id='.$id.'">';
				$s .= dPshowImage('./images/icons/stock_edit-16.png');
				$s .= "</a><a href='?m=system&u=roles&a=viewrole&role_id=$id&tab=1' title=''>";
				$s .= dPshowImage('images/obj/lock.gif');
				$s .= "</a>";
			}
			if ($canDelete) {
				$s .= "<a href='javascript:delIt($id)'>";
				$s .= dPshowImage('images/icons/stock_delete-16.png');
				$s .= "</a>";
			}
		}
		$s .= "</td>$CR";
		if ($role['value']<>'admin') {
			$s .= '<td valign="top">'.$name.'</td>'.$CR;
			$s .= '<td valign="top">'.$AppUI->_($description).'</td>'.$CR;
		}
		else {
			$s .= '<td valign="top"><font color="red">'.$name.'</font></td>'.$CR;
			$s .= '<td valign="top"><font color="red">'.$AppUI->_($description).'</font></td>'.$CR;
		}
		$s .= '<td valign="top" width="16">';
		$s .= "&nbsp;";
		$s .= '</td>'.$CR;
	}
	$s .= '</tr>'.$CR;
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($roles as $row) {
	echo showRow( $row );
}
// add in the new key row:
if ($role_id == 0) {
	echo showRow();
}
?>
</table>
<?php
 // Do all the tab stuff.
 
?>
