<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      add or edit user information

 File:       addedituser.php
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

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;

if ($user_id == 0)
	$canEdit = $perms->checkModule('admin', 'add', null, $AppUI->user_groups[-1]);
else
	$canEdit = $perms->checkModule('admin', 'edit', null, $AppUI->user_groups[-1]);

// check permissions
if (!$canEdit && $user_id != $AppUI->user_id) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$q  = new DBQuery;
$q->addTable('users', 'u');
$q->addQuery('u.*');
$q->addQuery('g.group_id, g.group_name');
$q->addJoin('user_setcap', 'us', 'us.user_id = u.user_id');
$q->addJoin('groups', 'g', 'g.group_id = us.group_id');
$q->addWhere('u.user_id = '.$user_id);
$sql = $q->prepare();
$q->clear();

$assigned = array();
$group_members="";

if (!db_loadHash( $sql, $user ) && $user_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'helix-setup-user.png', $m, "$m.$a" );
	//$titleBlock->addCrumb( "?m=admin", "Users list" );
	$titleBlock->show();
} else {
	if ( $user_id == 0)
        $user['user_id'] = 0;
     
// pull groups
	$q = new DBQuery;
	$q->addTable('groups');
	$q->addQuery('group_id, group_name');
	$q->addOrder('group_name');
	$companies = $q->loadHashList();
	
	if ($user_id > 0) {
     	$sql = " SELECT group_id
				 FROM user_setcap
				 WHERE group_id > 0 && user_id =$user_id
				 ";
		$assignedTemp = db_loadColumn( $sql );	

		foreach ($assignedTemp as $gid) {
			$assigned[$gid] = $companies[$gid];
			$group_members .= "$gid;";
		}
     }
     
// setup the title block
	$ttl = $user_id > 0 ? "Edit user" : "Add user";
	$titleBlock = new CTitleBlock( $ttl, 'helix-setup-user.png', $m, "$m.$a" );
	/*if ($perms->checkModule('admin', 'view', null, $AppUI->user_groups[-1]) && $perms->checkModule('users', 'view', null, $AppUI->user_groups[-1]))
		$titleBlock->addCrumb( "?m=admin", "Users list" );*/
	if ($user_id > 0) {
		$titleBlock->addCrumb( "?m=admin&a=viewuser&user_id=$user_id", "View user" );
		/*if ($canEdit || $user_id == $AppUI->user_id) {
		$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "Edit preferences" );
		}*/
	}
	$titleBlock->show();
?>

<SCRIPT language="javascript">
function submitIt(){
    var form = document.editFrm;
   if (form.user_username.value.length < <?php echo dPgetConfig('username_min_len'); ?> && form.user_username.value != '<?php echo dPgetConfig('admin_username'); ?>') {
        alert("<?php echo $AppUI->_('adminValidUserName', UI_OUTPUT_JS)  ;?>"  + <?php echo dPgetConfig('username_min_len'); ?>);
        form.user_username.focus();
    } else if (form.user_password.value.length < <?php echo dPgetConfig('password_min_len'); ?>) {
        alert("<?php echo $AppUI->_('adminValidPassword', UI_OUTPUT_JS);?>" + <?php echo dPgetConfig('password_min_len'); ?>);
        form.user_password.focus();
    } else if (form.user_password.value !=  form.password_check.value) {
        alert("<?php echo $AppUI->_('adminPasswordsDiffer', UI_OUTPUT_JS);?>");
        form.user_password.focus();
    } else if (form.user_first_name.value.length < 1) {
        alert("<?php echo $AppUI->_('adminValidFirstName', UI_OUTPUT_JS);?>");
        form.user_first_name.focus();
    } else if (form.user_last_name.value.length < 1) {
        alert("<?php echo $AppUI->_('adminValidLastName', UI_OUTPUT_JS);?>");
        form.user_last_name.focus();
    } else if (form.user_email.value.length < 4) {
        alert("<?php echo $AppUI->_('adminInvalidEmail', UI_OUTPUT_JS);?>");
        form.user_email.focus();
    } else if (form.user_birthday.value.length > 0) {
        dar = form.user_birthday.value.split("-");
        if (dar.length < 3) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.user_birthday.focus();
        } else if (isNaN(parseInt(dar[0],10)) || isNaN(parseInt(dar[1],10)) || isNaN(parseInt(dar[2],10))) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.user_birthday.focus();
        } else if (parseInt(dar[1],10) < 1 || parseInt(dar[1],10) > 12) {
            alert("<?php echo $AppUI->_('adminInvalidMonth', UI_OUTPUT_JS).' '.$AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.user_birthday.focus();
        } else if (parseInt(dar[2],10) < 1 || parseInt(dar[2],10) > 31) {
            alert("<?php echo $AppUI->_('adminInvalidDay', UI_OUTPUT_JS).' '.$AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.user_birthday.focus();
        } else if(parseInt(dar[0],10) < 1900 || parseInt(dar[0],10) > 2020) {
            alert("<?php echo $AppUI->_('adminInvalidYear', UI_OUTPUT_JS).' '.$AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS);?>");
            form.user_birthday.focus();
        } else {
            form.submit();
        }
    } else {
        form.submit();
    }
}


</script>
<form name="editFrm" action="./index.php?m=admin" method="post">
	<input type="hidden" name="user_id" value="<?php echo intval($user["user_id"]);?>" />
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="username_min_len" value="<?php echo dPgetConfig('username_min_len'); ?>" />
	<input type="hidden" name="password_min_len" value="<?php echo dPgetConfig('password_min_len'); ?>" />
	<input type="hidden" name="group_members" value="<?php echo $group_members;?>" />
<table width="100%" border="0" cellpadding="0" cellspacing="1" height="400" class="std">
<tr><td align="left">
<table>
<tr>
    <td align="right">* <?php echo $AppUI->_('Login Name');?>:</td>
    <td>
<?php
	if (@$user["user_username"]){
		echo '<input type="hidden" class="text" name="user_username" value="' . $user["user_username"] . '" />';
		echo '<strong>' . $user["user_username"] . '</strong>';
    } else {
        echo '<input type="text" class="text" name="user_username" value="' . $user["user_username"] . '" maxlength="255" size="40" />';
	//	echo ' <span class="smallNorm">(' . $AppUI->_('required') . ')</span>';
    }
?>
	</td>
</tr>
<tr>
    <td align="right">* <?php echo $AppUI->_('Password');?>:</td>
    <td><input type="password" class="text" name="user_password" value="<?php echo $user["user_password"];?>" maxlength="32" size="32" /> </td>
</tr>
<tr>
    <td align="right">* <?php echo $AppUI->_('Password');?>2:</td>
    <td><input type="password" class="text" name="password_check" value="<?php echo $user["user_password"];?>" maxlength="32" size="32" /> </td>
</tr>
<tr>
    <td align="right">* <?php echo $AppUI->_('Name');?>:</td>
    <td><input type="text" class="text" name="user_first_name" value="<?php echo $user["user_first_name"];?>" maxlength="50" /> <input type="text" class="text" name="user_last_name" value="<?php echo $user["user_last_name"];?>" maxlength="50" /></td>
</tr>
<tr>
    <td align="right">* <?php echo $AppUI->_('Email');?>:</td>
    <td><input type="text" class="text" name="user_email" value="<?php echo $user["user_email"];?>" maxlength="255" size="40" /> </td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Phone');?>:</td>
    <td><input type="text" class="text" name="user_phone" value="<?php echo $user["user_phone"];?>" maxlength="50" size="40" /> </td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Mobile');?>:</td>
    <td><input type="text" class="text" name="user_mobile" value="<?php echo $user["user_mobile"];?>" maxlength="50" size="40" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Address');?>1:</td>
    <td><input type="text" class="text" name="user_address1" value="<?php echo $user["user_address1"];?>" maxlength="50" size="40" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Address');?>2:</td>
    <td><input type="text" class="text" name="user_address2" value="<?php echo $user["user_address2"];?>" maxlength="50" size="40" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('City');?>:</td>
    <td><input type="text" class="text" name="user_city" value="<?php echo $user["user_city"];?>" maxlength="50" size="40" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('State');?>:</td>
    <td><input type="text" class="text" name="user_state" value="<?php echo $user["user_state"];?>" maxlength="50" size="40" /> </td></tr>
<tr>
    <td align="right"><?php echo  $AppUI->_('Postcode').' / '.$AppUI->_('Zip Code');?>:</td>
    <td><input type="text" class="text" name="user_zip" value="<?php echo $user["user_zip"];?>" maxlength="50" size="40" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Country');?>:</td>
    <td><input type="text" class="text" name="user_country" value="<?php echo $user["user_country"];?>" maxlength="50" size="40" /> </td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
    <td><input type="text" class="text" name="user_birthday" value="<?php if(intval($user["user_birthday"])!=0) { echo substr($user["user_birthday"],0,10);}?>" maxlength="50" size="40" /> format(YYYY-MM-DD)</td>
</tr>
</table>
</td>
<td align="right">
<table>
	<?php if ($canEdit) { ?>
			<tr>
			    <td align="right"><?php echo $AppUI->_('Working day hours');?>:</td>
			    <td align="left"><input type="text" class="text" name="user_day_hours" value="<?php echo$user["user_day_hours"];?>" maxlength="2" size="4" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center"><strong><?php echo $AppUI->_( 'Group List' );?>:</strong></td>
				<td align="center"><strong><?php echo $AppUI->_( 'Assigned to Group' );?>:</strong></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $companies, 'resources', 'style="width:210px" size="16" class="text" multiple="multiple" ', null ); ?>
				</td>
				<td>
					<?php echo arraySelect( $assigned, 'assigned', 'style="width:210px" size="16" class="text" multiple="multiple" ', null ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<table>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onClick="addGroup(document.editFrm)" /></td>	
						<td align="left"><input type="button" class="button" value="&lt;" onClick="removeGroup(document.editFrm)" /></td>					
					</tr>
					</table>
				</td>
			</tr>
	<?php } else echo "&nbsp";?>
	</tr>
</table>
</td></tr>
<tr>
    <td align="left">
        <input type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" class="button" />
    </td>
    <td align="right">
        <input type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" class="button" />
    </td>
</tr>
</table>* <?php echo $AppUI->_('indicates required field');?>
</form>
<?php } ?>
