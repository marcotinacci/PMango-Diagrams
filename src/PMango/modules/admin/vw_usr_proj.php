<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view user projects

 File:       vw_usr_proj.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to view user projects.
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

GLOBAL $AppUI, $user_id;

$q  = new DBQuery;
$q->addTable('user_projects', 'uprol');
$q->addQuery('p.project_group, p.project_id, p.project_name, p.project_start_date, p.project_finish_date, prol.proles_name, group_name, uprol.proles_id');
$q->addJoin('projects', 'p', "uprol.user_id = $user_id && uprol.project_id = p.project_id");
$q->addJoin('project_roles', 'prol', 'prol.proles_id= uprol.proles_id');
$q->addJoin('groups', 'g', 'g.group_id= p.project_group');
$q->addWhere('project_active <> 0');
$q->addOrder('project_name');
$projects = $q->loadList();

$canEdt=0;
$perms =& $AppUI->acl();
$canEdt= $perms->checkModule('admin', 'edit', null, $AppUI->user_groups[-1],1);

?>

<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdt) {
?>
function delIt(id,ui) {
	if (confirm( 'Are you sure you want to remove this user from the project?' )) {
		var f = document.frmRoles;
		f.user_id.value = ui;
		f.del.value = 1;
		f.project_id.value = id;
		f.submit();
	}
}
<?php
}?>

</script>
<?php 
if ($canEdt) {
?>
<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr><td width="50%" valign="top">
<?php
	}
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="30%"><?php echo $AppUI->_('Name');?></th>
	<th width="30%"><?php echo $AppUI->_('Group');?></th>	
	<?php if (!$canEdt) { 
			echo "<th nowrap>".$AppUI->_('Start Date')."</th>";
			echo "<th nowrap>".$AppUI->_('Finish Date')."</th>";
			$df = $AppUI->getPref('SHDATEFORMAT');
			}?>
	<th	width="40%"><?php echo $AppUI->_('Roles');?></th>
	<th>&nbsp;</th>
</tr>

<?php
	$a=-1; $rid='';
	foreach ($projects as $row) {	
		if ($a <> $row["project_id"]) {
			if ($a <> -1) {
?>
	</td>	
	<?php 
			$buf = '<td nowrap>';
			if ($canEdt) {
				$buf .= "<a href=\"javascript:delIt({$rid},{$user_id});\" title=\"".$AppUI->_('delete')."\">"
					. dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
					. "</a>";
			}
			$buf .= '</td>';
			echo "$buf";	
	?>			
</tr>
<?php		}
			$a = $row["project_id"];
			// stampa normale
?>
<tr>
	<td align="left">
		<?php 	
			if ($perms->checkModule('projects', 'view', null, $AppUI->user_groups[intval($row["project_group"])],1))
				echo '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a>';
			else 
				echo $row["project_name"];
		?>
	</td>
	<td align="left">
		<?php echo $row["group_name"]; ?>
	</td>
	<?php 
		$rid=$row['project_id'];
		if (!$canEdt) { 
			$start_date = intval( $row['project_start_date'] ) ? new CDate( $row['project_start_date'] ) : null;
			$finish_date = intval( $row['project_finish_date'] ) ? new CDate( $row['project_finish_date'] ) : null;
			echo "<td nowrap align=\"center\" width=\"120\">".($start_date ? $start_date->format( $df ) : '-')."</td>";
			echo "<td nowrap align=\"center\" width=\"120\">".($finish_date ? $finish_date->format( $df ) : '-')."</td>";
		  }
	?>
	<td align="left"><?php 
			if ($row['proles_id']==0) 
				echo "<font color=\"red\"><i>External<i></font>";
			else 
				echo $row["proles_name"]."<br>"; ?>
<?php		}
		else {
			// stampa di un solo elemento
			echo $row["proles_name"]."<br>"; 
		}
		
?>
<?php 
	} 
	if (!is_null($row)) {
?>		
	</td>
	<?php 
			$buf = '<td nowrap>';
			if ($canEdt) {
				$buf .= "<a href=\"javascript:delIt({$rid},{$user_id});\" title=\"".$AppUI->_('delete')."\">"
					. dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
					. "</a>";
			}
			$buf .= '</td>';
			echo "$buf";	
	?>		
</tr>
<?php 		
	}
	
?>

</table>
<?php 
if ($canEdt) {
?>
</td><td width="50%" valign="top">
<?php
	//$pid = array(
	foreach ($projects as $row)
		$pid[]=$row['project_id'];
	$gsc = CUser::getGroupsSetCap($user_id);
	$notProjects=null;
	if (!is_null($gsc)) {
		$q  = new DBQuery;
		$q->addTable('user_projects', 'uprol');
		$q->addQuery('DISTINCT(p.project_id), p.project_name, group_name, group_id');
		$q->addJoin('projects', 'p', "uprol.project_id = p.project_id");
		$q->addJoin('groups', 'g', 'g.group_id = p.project_group');
		$q->addWhere('project_active <> 0 ');
	    $q->addWhere('p.project_group IN ('.implode(',',array_keys($gsc)).')');
		if (!is_null($pid)) $q->addWhere('p.project_id NOT IN ('.implode(',',$pid).')');
		$q->addOrder('project_name');
		$notProjects = $q->loadList();
		
		//print_r($notProjects);
		//print_r($gsc);
		$sql = "
			 SELECT proles_id, proles_name
			 FROM project_roles
			 WHERE proles_status = 0
			 ";
		$rolesName = db_loadHashList( $sql );	//array(roles_id=>roles_name)
	}
	
?>

<table cellspacing="1" cellpadding="3" class="std" border="0" width="100%">
<form name="frmRoles" method="post" action="?m=admin">
	<input type="hidden" name="del" value="0">
	<input type="hidden" name="dosql" value="do_userproj_aed">
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>">
	<input type="hidden" name="project_id" value="">
	
<tr>
	<th width="30%" align="center"><?php echo $AppUI->_('Name');?></th>
	<th width="30%" align="center"><?php echo $AppUI->_('Groups');?></th>
	<th width="40%" align="center"><?php echo $AppUI->_('Roles');?></th>
	<th align="right">&nbsp;</th>
</tr>


<?php
 	$i=0;
 	if (!is_null($notProjects)) {
		foreach ($notProjects as $row) {	
?>
<tr>
	<td align="center"><?php echo $row['project_name'];?>:</td>
	<td align="center"><?php echo $row['group_name'];?></td>
	<td align="center"><?php if ($gsc[$row['group_id']])
								echo arraySelect($rolesName, 'user_role'.$row['project_id'], 'size="1" class="text"',null, true);
							 else {
							 	echo "<font color=\"red\"><i>External<i></font>";
								echo "<input type=\"hidden\" name=\"user_role".$row['project_id']."\" value=\"0\">";
							 }
						?>
	</td>
	<td align="center">
	   <?php //echo "<input type=\"hidden\" name=\"project_id".$row['project_id']."\" value=\"".$row['project_id']."\">";?>
	   <input type="checkbox" name="project[]" value="<?php echo $row['project_id'];?>">
	</td>
</tr>
<?php  }
 	}?>
<td colspan="4" align="right">
		<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
</td>
</tr>
</table>
</form>
</td>
</tr>
</table>
<?php }//$perms->insertUserRole(11, 1)?>