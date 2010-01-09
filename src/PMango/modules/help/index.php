<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      help module home page
 
 File:       index.php
 Location:   pmango\modules\groups
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango help.
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

$titleBlock = new CTitleBlock( 'Help', 'help.png', $m, "$m.$a" );
$titleBlock->show();

$hid = dPgetParam( $_GET, 'hid', 'help.toc' );

$inc = "{$dPconfig['root_dir']}/modules/help/{$AppUI->user_locale}/$hid.hlp";

if (!file_exists( $inc )) {
	$inc = "{$dPconfig['root_dir']}/modules/help/en/$hid.hlp";
	if (!file_exists( $inc )) {
		$hid = "help.toc";
		$inc = "{$dPconfig['root_dir']}/modules/help/{$AppUI->user_locale}/$hid.hlp";
		if (!file_exists( $inc )) {
		  $inc = "{$dPconfig['root_dir']}/modules/help/en/$hid.hlp";
		}
	}
}
if ($hid != 'help.toc') {
	echo '<a href="?m=help&dialog=1">' . $AppUI->_( 'index' ) . '</a>';
}
readfile( $inc );
?>


