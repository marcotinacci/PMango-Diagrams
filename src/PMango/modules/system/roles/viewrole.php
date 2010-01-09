<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view set of capabilities information.

 File:       viewrole.php
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

$AppUI->savePlace();
$perms =& $AppUI->acl();
$role_id = $_GET['role_id'];
$role = $perms->getRole($role_id);

if (isset($_GET['tab'])) {
	$AppUI->setState('RoleVwTab', $_GET['tab']);
}
$tab = $AppUI->getState('RoleVwTab') !== NULL ? $AppUI->getState('RoleVwTab') : 0;

if (! is_array($role)) {
	$titleBlock = new CTitleBlock('Invalid Sets of Capabilities', 'main-settings.png', $m, "$m.$a");
	$titleBlock->addCrumb("?m=system&u=roles", "Sets of Capabilities list");
	$titleBlcok->show();
} else {
	$titleBlock = new CTitleBlock('View Sets of Capabilities', 'main-settings.png', $m, "$m.$a");
	$titleBlock->addCrumb("?m=system&u=roles", "Sets of Capabilities list");
	$titleBlock->show();
	// Now onto the display of the user.
?>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Sets of Capabilities ID');?>:</td>
			<td class="hilite" width="100%"><?php echo $role["value"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Description');?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($role["name"]);?></td>
		</tr>
</table>
<br>
<?php
	$tabBox = new CTabBox("?m=system&u=roles&a=viewrole&role_id=$role_id", "./modules/system/roles/", $tab );
	$tabBox->add( 'vw_role_perms', 'Permissions');
	$tabBox->show();
} // End of check for valid role
?>