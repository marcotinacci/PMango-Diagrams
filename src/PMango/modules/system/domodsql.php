<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      activate or move a module entry.

 File:       domodsql.php
 Location:   pmango\modules\system
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

$cmd = dPgetParam( $_GET, 'cmd', '0' );
$mod_id = intval( dPgetParam( $_GET, 'mod_id', '0' ) );
$mod_directory = dPgetParam( $_GET, 'mod_directory', '0' );

$obj = new CModule();
if ($mod_id) {
	$obj->load( $mod_id );
} else {
	$obj->mod_directory = $mod_directory;
}

$ok = @include_once( "{$dPconfig['root_dir']}/modules/$obj->mod_directory/setup.php" );

if (!$ok) {
	if ($obj->mod_type != 'core') {
		$AppUI->setMsg( 'Module setup file could not be found', UI_MSG_ERROR );
                if ($cmd == 'remove')
                {
                        $sql = "DELETE FROM modules WHERE mod_id = $mod_id";
                        db_exec($sql);
                        echo db_error();
                        $AppUI->setMsg( 'Module has been removed from the modules list - please check your database for additional tables that may need to be removed', UI_MSG_ERROR );       
                }       
		$AppUI->redirect();
	}
}
$setupclass = $config['mod_setup_class'];
if (! $setupclass) {
  if ($obj->mod_type != 'core') {
    $AppUI->setMsg('Module does not have a valid setup class defined', UI_MSG_ERROR);
    $AppUI->redirect();
  }
}
else
  $setup = new $setupclass();

switch ($cmd) {
	case 'moveup':
	case 'movedn':
		$obj->move( $cmd );
		$AppUI->setMsg( 'Module re-ordered', UI_MSG_OK );
		break;
	case 'toggle':
	// just toggle the active state of the table entry
		$obj->mod_active = 1 - $obj->mod_active;
		$obj->store();
		$AppUI->setMsg( 'Module state changed', UI_MSG_OK );
		break;
	case 'toggleMenu':
	// just toggle the active state of the table entry
		$obj->mod_ui_active = 1 - $obj->mod_ui_active;
		$obj->store();
		$AppUI->setMsg( 'Module menu state changed', UI_MSG_OK );
		break;
	case 'install':
	// do the module specific stuff
		$AppUI->setMsg( $setup->install() );
		$obj->bind( $config );
	// add to the installed modules table
		$obj->install();
		$AppUI->setMsg( 'Module installed', UI_MSG_OK, true );
		break;
	case 'remove':
	// do the module specific stuff
		$AppUI->setMsg( $setup->remove() );
	// remove from the installed modules table
		$obj->remove();
		$AppUI->setMsg( 'Module removed', UI_MSG_ALERT, true );
		break;
	case 'upgrade':
		if ( $setup->upgrade( $obj->mod_version ) )	// returns true if upgrade succeeded
		{
			$obj->bind( $config );
			$obj->store();
			$AppUI->setMsg( 'Module upgraded', UI_MSG_OK );
		}
		else
		{
			$AppUI->setMsg( 'Module not upgraded', UI_MSG_ERROR );
		}
		break;
	case 'configure':
		if ( $setup->configure() ) 	//returns true if configure succeeded
		{
		}
		else {
			$AppUI->setMsg( 'Module configuration failed', UI_MSG_ERROR );
		}
		break;
	default:
		$AppUI->setMsg( 'Unknown Command', UI_MSG_ERROR );
		break;
}
$AppUI->redirect();
?>
