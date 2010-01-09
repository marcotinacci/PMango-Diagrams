<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      keys information.

 File:       keys.php
 Location:   pmango\modules\system\syskeys
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   First version, modified to manage PMango keys information. 
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


$sql = "SELECT * FROM syskeys ORDER BY syskey_name";
$keys = db_loadList( $sql );

$syskey_id = isset( $_GET['syskey_id'] ) ? $_GET['syskey_id'] : 0;

$titleBlock = new CTitleBlock( 'System Lookup Keys', 'myevo-weather.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "System admin" );
$titleBlock->show();

?>
<script language="javascript">
<?php

// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.sysKeyFrm;
		f.del.value = 1;
		f.syskey_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Name');?></th>
	<th colspan="2"><?php echo $AppUI->_('Label');?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow($id=0, $name='', $label='') {
	GLOBAL $syskey_id, $CR, $AppUI;
	$perms =& $AppUI->acl();
	$canEdit = $perms->checkModule('system','edit','',$AppUI->user_groups[-1],1);
	$s = '<tr>'.$CR;
	if ($syskey_id == $id && $canEdit) {
		$s .= '<form name="sysKeyFrm" method="post" action="?m=system&u=syskeys&a=do_syskey_aed">'.$CR;
		$s .= '<input type="hidden" name="del" value="0" />'.$CR;
		$s .= '<input type="hidden" name="syskey_id" value="'.$id.'" />'.$CR;

		$s .= '<td>&nbsp;</td>';
		$s .= '<td><input type="text" name="syskey_name" value="'.$name.'" class="text" /></td>';
		$s .= '<td><textarea name="syskey_label" class="small" rows="2" cols="40">'.$label.'</textarea></td>';
		$s .= '<td><input type="submit" value="'.$AppUI->_($id ? 'edit' : 'add').'" class="button" /></td>';
		$s .= '<td>&nbsp;</td>';
	} else {
		$s .= '<td width="12">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=syskeys&a=keys&syskey_id='.$id.'"><img src="./images/icons/pencil.gif" alt="edit" border="0" width="12" height="12"></a>';
			$s .= '</td>'.$CR;
		}
		$s .= '<td>'.$name.'</td>'.$CR;
		$s .= '<td colspan="2">'.$label.'</td>'.$CR;
		$s .= '<td width="16">';
		if ($canEdit) {
			$s .= '<a href="javascript:delIt('.$id.')"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="'.$AppUI->_('delete').'" border="0"></a>';
		}
		$s .= '</td>'.$CR;
	}
	$s .= '</tr>'.$CR;
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($keys as $row) {
	echo showRow( $row['syskey_id'], $row['syskey_name'], $row['syskey_label'] );
}
// add in the new key row:
if ($syskey_id == 0) {
	echo showRow();
}
?>
</table>
