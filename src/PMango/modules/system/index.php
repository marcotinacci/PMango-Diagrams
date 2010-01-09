<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      system administration main page.

 File:       index.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango system administration.
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

$titleBlock = new CTitleBlock( 'System Administration', '48_my_computer.png', $m, "$m.$a" );
$titleBlock->show();
?>
<p>
<table width="50%" border="0" cellpadding="0" cellspacing="5" align="left">
<tr>
	<td width="42">
		<?php echo dPshowImage( dPfindImage( 'rdf2.png', $m ), 42, 42, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_( 'Language Support' );?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=translate"><?php echo $AppUI->_( 'Translation Management' );?></a>
	</td>
</tr>

<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'myevo-weather.png', $m ), 42, 42, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Preferences');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=systemconfig"><?php echo $AppUI->_('System Configuration');?></a>
		<!--<br /><a href="?m=system&a=addeditpref"><?php //echo $AppUI->_('Default User Preferences');?></a>-->
		<br /><a href="?m=system&u=syskeys&a=keys"><?php echo $AppUI->_( 'System Lookup Keys' );?></a>
		<br /><a href="?m=system&u=syskeys"><?php echo $AppUI->_( 'System Lookup Values' );?></a>
		<br /><a href="?m=system&a=custom_field_editor"><?php echo $AppUI->_( 'Custom Field Editor' );?></a>
        
	</td>
</tr>

<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'power-management.png', $m ), 42, 42, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Modules');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=viewmods"><?php echo $AppUI->_('View Modules');?></a>
	</td>
</tr>

<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'main-settings.png', $m ), 42, 42, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Administration');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&u=roles"><?php echo $AppUI->_('Sets of Capabilities');?></a>
		<br /><a href="?m=system&a=prole"><?php echo $AppUI->_( 'Project Roles Table' );?></a>
	</td>
</tr>

<!--<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=contacts_ldap"><?php /*echo $AppUI->_('Import Contacts');*/?></a>
	</td>
</tr>-->

</table>
</p>
