<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      projects functions

 File:       projects_func.php
 Location:   pmango/functions
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       php

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
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

---------------------------------------------------------------------------*/

// project statii
$pstatus = dPgetSysVal( 'ProjectStatus' );
$ptype = dPgetSysVal( 'ProjectType' );

$priority = array(
 -1 => array(
 	'name' => 'low',
 	'color' => '#E5F7FF'
 	),
 0 => array(
 	'name' => 'normal',
 	'color' => ''//#CCFFCA
 	),
 1 => array(
 	'name' => 'high',
 	'color' => '#FFDCB3'
 	),
 2 => array(
 	'name' => 'immediate',
 	'color' => '#FF887C'
 	)
);

?>
