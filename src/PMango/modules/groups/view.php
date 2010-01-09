<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view groups

 File:       view.php
 Location:   pmango\modules\groups
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to view PMango groups.
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

$company_id = intval( dPgetParam( $_GET, "group_id", 0 ) );

// check permissions for this record
$perms =& $AppUI->acl();

$msg = '';
$obj = new CGroup(); 
if (isset($AppUI->user_groups[-1])) {
		$canEdit = $perms->checkModule($m, "edit",'', $AppUI->user_groups[-1]);
		$canRead = $perms->checkModule($m, "view",'', $AppUI->user_groups[-1]);
		$canDelete = $obj->canDelete( $msg, $company_id, $AppUI->user_groups[-1]);
}
else {
  		$canEdit = $perms->checkModule($m, "edit", '', $AppUI->user_groups[$company_id],1);
  		$canRead = $perms->checkModule($m, "view", '', $AppUI->user_groups[$company_id],1);
  		$canDelete = $obj->canDelete( $msg, $company_id, $AppUI->user_groups[$company_id] );
}

$canAdd = 0;
foreach ($AppUI->user_groups as $g => $sc)
	if (!$canAdd)
		$canAdd = $perms->checkModule('groups', 'add','',$sc);
	else
		break;
			
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;

// check if this record has dependencies to prevent deletion



// load the record data
$q  = new DBQuery;
$q->addTable('groups');
$q->addQuery('groups.*');
$q->addQuery('u.user_first_name');
$q->addQuery('u.user_last_name');
$q->addJoin('users', 'u', 'u.user_id = groups.group_owner');
$q->addWhere('groups.group_id = '.$company_id);
$sql = $q->prepare();
$q->clear();

$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Group' );//$AppUI->setMsg($sql);
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// load the list of project statii and company types
$pstatus = dPgetSysVal( 'ProjectStatus' );
$types = dPgetSysVal( 'GroupType' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Group', 'handshake.png', $m, "$m.$a" );

if ($canAdd) 
	$titleBlock->addCrumb( "?m=groups&a=addedit", "New group" );
	
//$titleBlock->addCrumb( "?m=groups", "Group list" );

if ($canEdit) 
	$titleBlock->addCrumb( "?m=groups&a=addedit&group_id=$company_id", "Edit group" );
	
if ($perms->checkModule("projects", "add", '', $AppUI->user_groups[$company_id],1))
	$titleBlock->addCrumb( "?m=projects&a=addedit&group_id=$company_id", "New project" );
	
if ($canDelete) 
	$titleBlock->addCrumbDelete( 'Delete group', $canDelete, $msg );

$titleBlock->show();
?>
<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Group').'?';?>" )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<?php if ($canDelete) {
?>
<form name="frmDelete" action="./index.php?m=groups" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="group_id" value="<?php echo $company_id;?>" />
</form>
<?php } ?>

<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Group');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->group_name;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->group_email;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite"><?php echo @$obj->group_phone1;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone');?>2:</td>
			<td class="hilite"><?php echo @$obj->group_phone2;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax');?>:</td>
			<td class="hilite"><?php echo @$obj->group_fax;?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address');?>:</td>
			<td class="hilite"><?php
						echo @$obj->group_address1
							.( ($obj->group_address2) ? '<br />'.$obj->group_address2 : '' )
							.( ($obj->group_city) ? '<br />'.$obj->group_city : '' )
							.( ($obj->group_state) ? '<br />'.$obj->group_state : '' )
							.( ($obj->group_zip) ? '<br />'.$obj->group_zip : '' )
							;
			?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?>:</td>
			<td class="hilite">
				<a href="http://<?php echo @$obj->group_primary_url;?>" target="Group"><?php echo @$obj->group_primary_url;?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type');?>:</td>
			<td class="hilite"><?php echo $AppUI->_($types[@$obj->group_type]);?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner');?>:</td>
			<td class="hilite"><?php 
				$q  = new DBQuery;
				$q->addTable('users');
				$q->addQuery('user_first_name, user_last_name');
				$q->addWhere('user_id = '.@$obj->group_owner);
				$us = $q->loadList();
				$q->clear();
				echo $us[0][user_first_name]." ".$us[0][user_last_name];?>
			</td>
		</tr>
		</table>

	</td>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Sets of Capabilities');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				
				<?php 
					require_once "$baseDir/modules/system/roles/roles.class.php";
					$crole =& new CRole;
					$roles = $crole->getRoles();
					$roles_arr = array();
					foreach ($roles as $role) {
					  $roles_arr[$role['id']] = $role['name'];
					}
					$sql = "SELECT setcap_id FROM group_setcap WHERE group_id =$company_id";
					foreach(db_loadColumn($sql) as $setcap)
						echo $roles_arr[$setcap]."<BR>";
				?>
			</td>
		</tr>
		</table>
		<br>
		<strong><?php echo $AppUI->_('Description');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $obj->group_description);?>&nbsp;
			</td>
		</tr>
		</table>
		<?php
			require_once("./classes/CustomFields.class.php");
			$custom_fields = New CustomFields( $m, $a, $obj->group_id, "view" );
			$custom_fields->printHTML();
		?>
	</td>
</tr>
</table>
<br>
<?php
// tabbed information boxes
$moddir = $dPconfig['root_dir'] . '/modules/groups/';
$tabBox = new CTabBox( "?m=groups&a=view&group_id=$company_id", "", $tab );
$tabBox->add( $moddir . 'vw_users', 'Users' );
$tabBox->add( $moddir . 'vw_active', 'Active Projects' );
$tabBox->add( $moddir . 'vw_archived', 'Archived Projects' );
//$tabBox->add( $moddir . 'vw_depts', 'Departments' );//da elimin
//$tabBox->add( $moddir . 'vw_contacts', 'Contacts' );
$tabBox->loadExtras($m);
$tabBox->show();

?>
