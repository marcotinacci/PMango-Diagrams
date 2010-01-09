<?php 
/**
 ---------------------------------------------------------------------------

 PMango Project

 Title:      change password.

 File:       chpwd.php
 Location:   pmango\modules\public
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

if (! ($user_id = dPgetParam($_REQUEST, 'user_id', 0)) )
        $user_id = @$AppUI->user_id;

// check for a non-zero user id
if ($user_id) {
	$old_pwd = db_escape( trim( dPgetParam( $_POST, 'old_pwd', null ) ) );
	$new_pwd1 = db_escape( trim( dPgetParam( $_POST, 'new_pwd1', null ) ) );
	$new_pwd2 = db_escape( trim( dPgetParam( $_POST, 'new_pwd2', null ) ) );

	// has the change form been posted
	if ($new_pwd1 && $new_pwd2 && $new_pwd1 == $new_pwd2 ) {
		// check that the old password matches
								$old_md5 = md5($old_pwd);
                $sql = "SELECT user_id FROM users WHERE user_password = '$old_md5' AND user_id=$user_id";
                if ($AppUI->user_type == 1 || db_loadResult( $sql ) == $user_id) {
			require_once( "{$dPconfig['root_dir']}/modules/admin/admin.class.php" );
			$user = new CUser();
			$user->user_id = $user_id;
			$user->user_password = $new_pwd1;

			if (($msg = $user->store())) {
				$AppUI->setMsg( $msg, UI_MSG_ERROR );
			} else {
				echo $AppUI->_('chgpwUpdated');
			}
		} else {
			echo $AppUI->_('chgpwWrongPW');
		}
	} else {
?>
<script language="javascript">
function submitIt() {
	var f = document.frmEdit;
	var msg = '';

        <?php if ($AppUI->user_type != 1)
        {
        ?>
	if (f.old_pwd.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('chgpwValidOld');?>";
		f.old_pwd.focus();
	}
        <?php } ?>
	if (f.new_pwd1.value.length < 3) {
		msg += "\n<?php echo $AppUI->_('chgpwValidNew');?>";
		f.new_pwd1.focus();
	}
	if (f.new_pwd1.value != f.new_pwd2.value) {
		msg += "\n<?php echo $AppUI->_('chgpwNoMatch');?>";
		f.new_pwd2.focus();
	}
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}
</script>
<h1><?php echo $AppUI->_('Change User Password');?></h1>
<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std">
<form name="frmEdit" method="post" onsubmit="return false">
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
<?php if ($AppUI->user_type != 1)
{
?>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Current Password');?></td>
	<td><input type="password" name="old_pwd" class="text"></td>
</tr>
<?php } ?>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('New Password');?></td>
	<td><input type="password" name="new_pwd1" class="text"></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Repeat New Password');?></td>
	<td><input type="password" name="new_pwd2" class="text"></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="right" nowrap="nowrap"><input type="button" value="<?php echo $AppUI->_('submit');?>" onclick="submitIt()" class="button"></td>
</tr>
<form>
</table>
<?php
	}
} else {
	echo $AppUI->_('chgpwLogin');
}
?>