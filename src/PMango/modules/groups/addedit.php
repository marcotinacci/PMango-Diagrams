<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      add or edit group information

 File:       addedit.php
 Location:   pmango\modules\groups
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango groups.
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

// check permissions for this company
$perms =& $AppUI->acl();
// If the company exists we need edit permission,
// If it is a new company we need add permission on the module.
if ($company_id)
	if (isset($AppUI->user_groups[-1])) 
		$canEdit = $perms->checkModule($m, "edit",'',$AppUI->user_groups[-1]);
	else
  		$canEdit = $perms->checkModule($m, "edit", '', $AppUI->user_groups[$company_id], 1);
else {
	$canEdit = 0;
	foreach ($AppUI->user_groups as $g => $sc)
		if (!$canEdit)
			$canEdit = $perms->checkModule('groups', 'add','',$sc);
		else
			break;
}

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the company types
$types = dPgetSysVal( 'GroupType' );

// load the record data
$q  = new DBQuery;
$q->addTable('groups');
$q->addQuery('groups.*');
$q->addQuery('user_first_name');
$q->addQuery('user_last_name');// da aggiungere l'utente nella compagnia
$q->addJoin('users', 'u', 'u.user_id = groups.group_owner');
$q->addWhere('groups.group_id = '.$company_id);
$sql = $q->prepare();
$q->clear();

$obj = null;
if (!db_loadObject( $sql, $obj ) && $company_id > 0) {
	// $AppUI->setMsg( '	$qid =& $q->exec(); Company' ); // What is this for?
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}



// collect all the users for the company owner list
$q  = new DBQuery;
$q->addTable('users','u');
$q->addQuery('user_id');
$q->addQuery('CONCAT_WS(", ",user_last_name,user_first_name)'); 
$q->addOrder('user_last_name');
//$q->addWhere('u.user_contact = u.user_id'); Prima c'era il campo contact in user serviva per il join..
$users = $q->loadHashList();// collect all the users for the company owner list


require_once "$baseDir/modules/system/roles/roles.class.php";
$crole =& new CRole;
$roles = $crole->getRoles();
// Format the roles for use in arraySelect
$roles_arr = array();
foreach ($roles as $role) {
	if($role['value'] <> 'admin')
  		$roles_arr[$role['id']] = $role['name'];
}

$assignedsc = array();
$assignedu = array();
$group_members="";
$group_setcap="";

if ( $company_id > 0 ) {
	$sql = "
			 SELECT setcap_id
			 FROM group_setcap
			 WHERE group_id =$company_id
			 ";
	$assignedscTemp = db_loadColumn($sql);

	$sql = "
			 SELECT user_id
			 FROM user_setcap
			 WHERE group_id =$company_id
			 ";
	$assigneduTemp = db_loadColumn( $sql );	
	//print_r($assignedscTemp);
	foreach ($assignedscTemp as $sid) {
		$assignedsc[$sid] = $roles_arr[$sid];
		$group_setcap .= "$sid;";
	}
	
	foreach ($assigneduTemp as $uid) {
		$assignedu[$uid] = $users[$uid];
		$group_members .= "$uid;";
	}
}



$tab = $AppUI->getState( 'CompVwTab' ) !== NULL ? $AppUI->getState( 'CompVwTab' ) : 0;
// setup the title block
$ttl = $company_id > 0 ? "Edit Group" : "Add Group";
$titleBlock = new CTitleBlock( $ttl, 'handshake.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=groups", "Groups list" );
if ($company_id != 0)
  $titleBlock->addCrumb( "?m=groups&a=view&group_id=$company_id", "View group" );
$titleBlock->show();
?>

<script language="javascript">
// Gli altri JS sono in addedit.js
function submitIt() {
	var form = document.changeclient;
	if (form.group_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('companyValidName', UI_OUTPUT_JS);?>" );
		form.group_name.focus();
	} else if (form.group_setcap.value.length < 1) {
		alert( "<?php echo $AppUI->_('groupValidSetcap', UI_OUTPUT_JS);?>" );
		form.setcap.focus();
	} else {
		form.submit();
	}
}

function testURL( x ) {
	var test = "document.changeclient.group_primary_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}
</script>

<form name="changeclient" action="?m=groups" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="group_id" value="<?php echo $company_id;?>" />
	<input type="hidden" name="group_setcap" value="<?php echo $group_setcap;?>" /> <!--DA MODIFICARE PER L'EDIT va caricato con assignst-->
	<input type="hidden" name="group_members" value="<?php echo $group_members;?>" /> <!--DA MODIFICARE PER L'EDIT va caricato con assignu-->

<table cellspacing="1" cellpadding="1" border="0" width='100%' class="std">
<tr>
	<td align="left">
		<table>
			<tr>
				<td align="right"><strong><?php echo $AppUI->_('Group Name');?>:</strong></td>
				<td>
					<input type="text" class="text" name="group_name" value="<?php echo dPformSafe(@$obj->group_name);?>" size="50" maxlength="255" /> (<?php echo $AppUI->_('required');?>)
				</td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Email');?>:</td>
				<td>
					<input type="text" class="text" name="group_email" value="<?php echo dPformSafe(@$obj->group_email);?>" size="30" maxlength="255" />
				</td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
				<td>
					<input type="text" class="text" name="group_phone1" value="<?php echo dPformSafe(@$obj->group_phone1);?>" maxlength="30" />
				</td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
				<td>
					<input type="text" class="text" name="group_phone2" value="<?php echo dPformSafe(@$obj->group_phone2);?>" maxlength="50" />
				</td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
				<td>
					<input type="text" class="text" name="group_fax" value="<?php echo dPformSafe(@$obj->group_fax);?>" maxlength="30" />
				</td>
			</tr>
			<!--<tr>
				<td colspan=2 align="center">
					<img src="images/shim.gif" width="50" height="1" /><?php echo $AppUI->_('Address');?><br />
					<hr width="500" align="center" size=1 />
				</td>
			</tr>-->
			<tr>
				<td align="right"><?php echo $AppUI->_('Address');?>1:</td>
				<td><input type="text" class="text" name="group_address1" value="<?php echo dPformSafe(@$obj->group_address1);?>" size=50 maxlength="255" /></td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
				<td><input type="text" class="text" name="group_address2" value="<?php echo dPformSafe(@$obj->group_address2);?>" size=50 maxlength="255" /></td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('City');?>:</td>
				<td><input type="text" class="text" name="group_city" value="<?php echo dPformSafe(@$obj->group_city);?>" size=50 maxlength="50" /></td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('State');?>:</td>
				<td><input type="text" class="text" name="group_state" value="<?php echo dPformSafe(@$obj->group_state);?>" maxlength="50" /></td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
				<td><input type="text" class="text" name="group_zip" value="<?php echo dPformSafe(@$obj->group_zip);?>" maxlength="15" /></td>
			</tr>
			<tr>
				<td align="right">
					URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo dPformSafe(@$obj->group_primary_url);?>" name="group_primary_url" size="50" maxlength="255" />
					<a href="#x" onClick="testURL('GroupURLOne')">[<?php echo $AppUI->_('test');?>]</a>
				</td>
			</tr>
			
			<tr>
				<td align="right"><?php echo $AppUI->_('Group Owner');?>:</td>
				<td>
			<?php
				echo arraySelect( $users, 'group_owner', 'size="1" class="text"', @$obj->group_owner );
			?>
				</td>
			</tr>
			
			<tr>
				<td align="right"><?php echo $AppUI->_('Type');?>:</td>
				<td>
			<?php
				echo arraySelect( $types, 'group_type', 'size="1" class="text"', @$obj->group_type, true );
			?>
				</td>
			</tr>		
			<tr>
				<td align="right" valign=top><?php echo $AppUI->_('Description');?>:</td>
				<td align="left">
					<textarea  cols="70" rows="5" class="textarea" name="group_description"><?php echo @$obj->group_description;?></textarea>
				</td>
			</tr>
		</table>
	</td>
	<td align="right">
		<table cellspacing="0" cellpadding="2" border="0">	
			<tr>
				<td align="center"><strong><?php echo $AppUI->_( 'Sets of Capabilities' );?>:</strong></td>
				<td align="center"><strong><?php echo $AppUI->_( 'Available to Group' );?>:</strong></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $roles_arr, 'setcap', 'style="width:210px" size="6" class="text" multiple="multiple" ', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $assignedsc, 'assignedsc', 'style="width:210px" size="6" class="text" multiple="multiple" ', null ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<table>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onClick="addSetcap(document.changeclient)" /></td>	
						<td align="left"><input type="button" class="button" value="&lt;" onClick="removeSetcap(document.changeclient)" /></td>					
					</tr>
					</table>
				</td>
			</tr>	
			
			<tr>
				<td align="center"><strong><?php echo $AppUI->_( 'Resources' );?>:</strong></td>
				<td align="center"><strong><?php echo $AppUI->_( 'Assigned to Group' );?>:</strong></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $users, 'resources', 'style="width:210px" size="18" class="text" multiple="multiple" ', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $assignedu, 'assigned', 'style="width:210px" size="18" class="text" multiple="multiple" ', null ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<table>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser(document.changeclient)" /></td>	
						<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser(document.changeclient)" /></td>					
					</tr>
					</table>
				</td>
			</tr>		
		</table>
	</td>
</tr>
<tr>
	<td align='left'>
		<?php
 			require_once("./classes/CustomFields.class.php");
 			$custom_fields = New CustomFields( $m, $a, $obj->group_id, "edit" );
 			$custom_fields->printHTML();
		?>		
	</td>
</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" /></td>
	<td align="right" width="100%"><input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()" /></td>
</tr>
</table>
</form>
<script language="javascript">// non serve per lo spostamento degli user da sx a dx
//  subForm.push(new FormDefinition(0, document.changeclient, checkResource, saveResource));
</script>