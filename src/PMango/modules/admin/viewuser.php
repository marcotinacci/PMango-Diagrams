<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view user information

 File:       viewuser.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango users.
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

$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

$canRead = $perms->checkModule('admin', 'view', null, $AppUI->user_groups[-1]);

if ($user_id != $AppUI->user_id && ( ! $canRead || ! $perms->checkModule('users', 'view', null, $AppUI->user_groups[-1]) ) )
	$AppUI->redirect('m=public&a=access_denied');

$canEdit = $perms->checkModule('admin', 'edit', null, $AppUI->user_groups[-1]);
$canDelete = $perms->checkModule('admin', 'delete', null, $AppUI->user_groups[-1]);
$canAdd = $perms->checkModule('admin', 'add', null, $AppUI->user_groups[-1]);

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserVwTab' ) !== NULL ? $AppUI->getState( 'UserVwTab' ) : 0;

// pull data
$q  = new DBQuery;
$q->addTable('users', 'u');
$q->addQuery('u.*');
//$q->addQuery('con.*, user_id, group_name');
//$q->addJoin('user_groups', 'ug', 'ug.user_id = u.group_id');
//$q->addJoin('groups', 'g', 'contact_company = company_id');
//$q->addJoin('departments', 'dep', 'dept_id = contact_department');
$q->addWhere('u.user_id = '.$user_id);
$sql = $q->prepare();//echo($sql);
$q->clear();
//echo $sql;
if (!db_loadHash( $sql, $user )) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'helix-setup-user.png', $m, "$m.$a" );
	//$titleBlock->addCrumb( "?m=admin", "Users list" );
	$titleBlock->show();
} else {

// setup the title block
	$titleBlock = new CTitleBlock( 'View User', 'helix-setup-user.png', $m, "$m.$a" );
	if ($canAdd) {
		 $titleBlock->addCrumb( "?m=admin&a=addedituser", "Add user" );
	}
	/*if ($canRead) {
	  $titleBlock->addCrumb( "?m=admin", "Users list" );
	}*/
	if ($canEdit || $user_id == $AppUI->user_id) {
	      $titleBlock->addCrumb( "?m=admin&a=addedituser&user_id=$user_id", "Edit user" );
	      //$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "Edit preferences" );
	}
	
	$titleBlock->show();
?>


<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr valign="top">
	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Login Name');?>:</td>
			<td class="hilite" width="100%"><?php echo $user["user_username"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Real Name');?>:</td>
			<td class="hilite" width="100%"><?php echo $user["user_first_name"].' '.$user["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_phone"] .' '.@$user["user_mobile"];?></td>
		</tr>
		<tr valign=top>
		<td align="right" nowrap><?php echo $AppUI->_('Address');?>:</td>
		<td class="hilite" width="100%"><?php
			echo @$user["user_address1"]
				.( ($user["user_address2"]) ? '<br />'.$user["user_address2"] : '' )
				.'<br />'.$user["user_city"]
				.'&nbsp;&nbsp;'.$user["user_state"]
				.'&nbsp;&nbsp;'.$user["user_zip"]
				.'<br />'.$user["user_country"]
				;
		?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Birthday');?>:</td>
			<td class="hilite" width="100%">
				<?php 
					$birthday = intval( @$user["user_birthday"] ) ? new CDate( @$user["user_birthday"] ) : null;
					echo ($birthday ? $birthday->format($AppUI->getPref('SHDATEFORMAT')) : '-')?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Email');?>:</td>
			<td class="hilite" width="100%"><?php echo '<a href="mailto:'.@$user["user_email"].'">'.@$user["user_email"].'</a>';?></td>
		</tr>
		</table>
	</td>
	<td width="50%">
		<strong><?php echo $AppUI->_('Assigned groups');?></strong>
		<table width="100%">
		<tr>
			<td class="hilite" width="100%"><?php 
				$sql = "SELECT group_name FROM user_setcap AS us LEFT JOIN groups AS g ON g.group_id = us.group_id WHERE us.user_id =$user_id";
					foreach(db_loadColumn($sql) as $g)
						if(!is_null($g))
						echo $g."<BR>";
						
			?></td>
		</tr>
		</table>
	</td>
</tr>
</table>
<br>
<?php
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=admin&a=viewuser&user_id=$user_id", "{$dPconfig['root_dir']}/modules/admin/", $tab );
	$tabBox->add( 'vw_usr_proj', 'Projects' );
	if ($canRead) $tabBox->add( 'vw_usr_roles', 'Sets of Capabilities' );
	//$tabBox->add( 'vw_usr_perms', 'Permissions' );
	if ($canRead) $tabBox->add( 'vw_usr_log', 'User Log');
	$tabBox->show();
}
?>
