<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view user sets of capabilities

 File:       vw_usr_roles.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango access control policy.
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

GLOBAL $AppUI, $user_id, $canEdit, $canDelete, $tab, $baseDir;



$q  = new DBQuery;
$q->addTable('group_setcap', 'gs');
$q->addQuery('gs.group_id, g.group_name, gs.setcap_id, ga.name');
$q->addJoin('user_setcap', 'us', 'us.group_id = gs.group_id');
$q->addJoin('gacl_aro_groups', 'ga', 'ga.id = gs.setcap_id');
$q->addJoin('groups', 'g', 'g.group_id = us.group_id');
$q->addWhere('us.user_id = '.$user_id);
$q->addOrder('gs.group_id');
$usc = $q->loadList();//print_r($sql);
$q->clear();

$gid=array();$gsc=array();
foreach ($usc as $r) {	
		$gsc[$r['group_name']][$r['setcap_id']]=$r['name'];
		$gid[] = $r['group_id'];
}	

$gid = array_values(array_unique($gid));
$gid[] = -1;

$perms =& $AppUI->acl();
$user_roles = $perms->getUserRoles($user_id);		

$canEdt= $perms->checkModule('admin', 'edit', null, $AppUI->user_groups[-1]);

$q  = new DBQuery;
$q->addTable('user_setcap', 'us');
$q->addQuery('us.setcap_id, g.group_name');
$q->addJoin('groups', 'g', 'g.group_id = us.group_id');
$q->addWhere('us.setcap_id <> 0 && us.user_id ='.$user_id);
$scg = $q->loadList();
$q->clear();
foreach ($scg as $r) {	
	if (!is_null($r['group_name']))
		$scgr[$r['setcap_id']][]=$r['group_name'];
	else
		$scgr[$r['setcap_id']][]='<font color="red"><i>'.$dPconfig['organization_name'].'</i></font>';
}
?>

<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdt) {
?>
function delIt(id,ui) {
	if (confirm( 'Are you sure you want to delete this Sets of Capabilities?' )) {
		var f = document.frmPerms;
		f.user_id.value = ui;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
<?php
}?>

</script>

<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr><td width="50%" valign="top">

<table width="100%" border="0" cellpadding="4" cellspacing="1" class="tbl">
<tr>
	<th width="70%"><?php echo $AppUI->_('Sets of Capabilities');?></th>
	<th width="30%"><?php echo $AppUI->_('Groups');?></th>
	<th>&nbsp;</th>
</tr>

<?php
foreach ($user_roles as $row){
	$bu='';
	if (!is_null($scgr[$row['id']])) {
		foreach($scgr[$row['id']] as $s) 
				$bu .= $s."<br>";
	}
	$buf = '';
	$style = '';
	$buf .= "<td>";
	$buf .= $row['value']=='admin' ? "<font color=red>".$row['name']."</font>" : $row['name'];
	$buf .= "</td>";
	$buf .= "<td>" . $bu . "</td>";
	$buf .= '<td nowrap>';
	if ($canEdt) {
		$buf .= "<a href=\"javascript:delIt({$row['id']},{$user_id});\" title=\"".$AppUI->_('delete')."\">"
			. dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
			. "</a>";
	}
	$buf .= '</td>';
	
	echo "<tr>$buf</tr>";
}
?>
</table>

</td><td width="50%" valign="top">

<?php if ($canEdt) {?>

<table cellspacing="1" cellpadding="3" class="std" border="0" width="100%">
<form name="frmPerms" method="post" action="?m=admin">
	<input type="hidden" name="del" value="0">
	<input type="hidden" name="dosql" value="do_userrole_aed">
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>">
	<input type="hidden" name="user_name" value="<?php echo $user_name;?>">
	<input type="hidden" name="role_id" value="">
	
<tr>
	<th width="30%" align="center"><?php echo $AppUI->_('Groups');?></th>
	<th width="70%" align="center"><?php echo $AppUI->_('Sets of Capabilities');?></th>
	<th align="right">&nbsp;</th>
</tr>


<?php
 	$i=0;
 	if (!is_null($gsc)) {
		foreach ($gsc as $name => $row) {	
?>
<tr>
	<td align="center"><?php echo $name;?>:</td>
	<!--Solo l'amministratore ha accesso totale al modulo company.
		Le istanze di questo modulo sono poi assegnate secondo l'apparteneza di ogni user al proprio gruppo.-->
	<td align="center"><?php echo arraySelect($row, 'user_role'.$gid[$i], 'size="1" class="text"',null, true);?></td>
	<td align="center">
	  <input type='checkbox' name='group[]' value='<?php echo $gid[$i++];?>'>
	</td>
</tr>
<?php  }
 	}?>
<tr>
	<td></td>
	<td align="center"><font color="red">
		<?php echo "System Administrator of <i>".$dPconfig['organization_name']."</i>"; 
			$q  = new DBQuery;
			$q->addTable('gacl_aro_groups');
			$q->addQuery('id');
			$q->addWhere('value = \'admin\'');
			$id = $q->loadResult();
			$q->clear();	
		?>
		<input type="hidden" name=<?php echo 'user_role'.$gid[$i] ?> value=<?php echo $id ?>>
	</font></td>
	<!--Solo l'amministratore ha accesso totale al modulo company.
		Le istanze di questo modulo sono poi assegnate secondo l'apparteneza di ogni user al proprio gruppo.-->
	<td align="center">
		<input type='checkbox' name='group[]' value='<?php echo $gid[$i];?>'>
	</td>
</tr>
<td colspan="3" align="right">
		<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
</td>
</tr>
</table>
</form>

<?php }?>
</td>
</tr>
</table>