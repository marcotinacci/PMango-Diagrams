<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view permissions and set of capabilities information.

 File:       vw_role_perms.php
 Location:   pmango\modules\system\roles
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
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


GLOBAL $AppUI, $role_id, $canEdit, $canDelete, $tab;

$perms =& $AppUI->acl();
$module_list = $perms->getModuleList();
$pgos = array();
$count = 0;
$modules = array();
foreach ($module_list as $module) {
	//print_r ($module);
	//echo $module['type']."<br>";
	// NEgazione del permesso di modifica degli accessi ai moduli amministrativi
	if (!in_array($module['value'],array('all','admin','system','roles','users')))
  		$modules[$module['type'] . ',' . $module['id']] = $module['name'];
}

//Pull User perms
$role_acls = $perms->getRoleACLs($role_id);
if (! is_array($role_acls)) {
  $role_acls = array(); // Stops foreach complaining.
}
$perm_list = $perms->getPermissionList();

?>

<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>

function clearIt(){
	var f = document.frmPerms;
	f.sqlaction2.value = "<?php echo $AppUI->_('add'); ?>";
	f.permission_id.value = 0;
	f.permission_grant_on.selectedIndex = 0;
}

function delIt(id) {
	if (confirm( '<?php echo $AppUI->_('Are you sure you want to delete this permission?', UI_OUTPUT_JS);?>' )) {
		var f = document.frmPerms;
		f.del.value = 1;
		f.permission_id.value = id;
		f.submit();
	}
}

<?php } ?>
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="50%" valign="top">

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="100%"><?php echo $AppUI->_('Item');?></th>
	<th nowrap><?php echo $AppUI->_('Type');?></th>
	<th nowrap><?php echo $AppUI->_('Status');?></th>
	<th>&nbsp;</th>
</tr>

<?php
foreach ($role_acls as $acl){
	$buf = '';
	$permission = $perms->get_acl($acl);

	$style = '';
	// TODO: Do we want to make the colour depend on the allow/deny/inherit flag?
	// Module information.
	if (is_array($permission)) {
		$buf .= "<td $style>";
		$modlist = array();
		$itemlist = array();
		if (is_array($permission['axo_groups'])) {
			foreach ($permission['axo_groups'] as $group_id) {
				$group_data = $perms->get_group_data($group_id, 'axo');
				$modlist[] = $AppUI->_($group_data[3]);
			}
		}
		if (is_array($permission['axo'])) {
			foreach ($permission['axo'] as $key => $section) {
				foreach ($section as $id) {
					$mod_data = $perms->get_object_full($id, $key, 1, 'axo');
					$modlist[] = $AppUI->_($mod_data['name']);
				}
			}
		}
		$buf .= implode("<br />", $modlist);
		$buf .= "</td>";
		// Item information TODO:  need to figure this one out.
	// 	$buf .= "<td></td>";
		// Type information.
		$buf .= "<td nowrap>";
		$perm_type = array();
		if (is_array($permission['aco'])) {
			foreach ($permission['aco'] as $key => $section) {
				foreach ($section as $value) {
					$perm = $perms->get_object_full($value, $key, 1, 'aco');
					$perm_type[] = $AppUI->_($perm['name']);
				}
			}
		}
		$buf .= implode("<br />", $perm_type);
		$buf .= "</td>";

		// Allow or deny
		$buf .= "<td>" . $AppUI->_( $permission['allow'] ? 'allow' : 'deny' ) . "</td>";
		$buf .= '<td nowrap>';
		if ($canDelete) {
			$buf .= "<a href=\"javascript:delIt({$acl});\" title=\"".$AppUI->_('delete')."\">"
				. dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
				. "</a>";
		}
		$buf .= '</td>';
		
		echo "<tr>$buf</tr>";
	}
}
?>
</table>

</td><td width="50%" valign="top">

<?php if ($canEdit) {?>

<table cellspacing="1" cellpadding="2" border="0" class="std" width="100%">
<form name="frmPerms" method="post" action="?m=system&u=roles">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_perms_aed" />
	<input type="hidden" name="role_id" value="<?php echo $role_id;?>" />
	<input type="hidden" name="permission_id" value="0" />
	<input type="hidden" name="permission_item" value="-1" />
<tr>
	<th colspan="2"><?php echo $AppUI->_('Add Permissions');?></th>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Module');?>:</td>
	<td width="100%"><?php echo arraySelect($modules, 'permission_module', 'size="1" class="text"', 'grp,all', true);?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Access');?>:</td>
	<td>
		<select name="permission_access" class="text">
			<option value='1'><?php echo $AppUI->_('allow');?></option>
			<option value='0'><?php echo $AppUI->_('deny');?></option>
		</select>
	</td>
</tr>
<?php
	foreach ($perm_list as $perm_id => $perm_name) {
?>
<tr>
	<td nowrap align='right'><?php echo $AppUI->_($perm_name);?>:</td>
	<td>
	  <input type='checkbox' name='permission_type[]' value='<?php echo $perm_id;?>'>
	</td>
</tr>
<?php
	}
?>
<tr>
	<td>
		<input type="reset" value="<?php echo $AppUI->_('clear');?>" class="button" name="sqlaction" onClick="clearIt();">
	</td>
	<td align="right">
		<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
	</td>
</tr>
</form>
</table>
<?php } ?>

</td>

</tr>



</tr>

</table>
