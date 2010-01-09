<?php
/**
 ---------------------------------------------------------------------------

 PMango Project

 Title:      debug

 File:       debug.php
 Location:   pmango\misc
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
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

$debug_file = "{$dPconfig['root_dir']}/files/debug.log";

function writeDebug( $s, $t='', $f='?', $l='?' ) {
	GLOBAL $debug, $debug_file;
	if ( $debug && ($fp = fopen( $debug_file, "at" ))) {
		fputs( $fp, "Debug message from file [$f], line [$l], at: ".strftime( "%H:%S" ) );
		if ($t) {
			fputs( $fp, "\n * * $t * *\n" );
		}
		fputs( $fp, "\n$s\n\n" );
		fclose( $fp );
	}
}
?>
