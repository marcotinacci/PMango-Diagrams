<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      config default

 File:       config-dist.php
 Location:   pmango\includes
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       php

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   Second version, modified to manage PMango parameters.
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

---------------------------------------------------------------------------

*/

// DATABASE ACCESS INFORMATION [DEFAULT example]
// Modify these values to suit your local settings

$dPconfig['dbtype'] = "mysql";      // ONLY MySQL is supported at present
$dPconfig['dbhost'] = "localhost";
$dPconfig['dbname'] = "pmango";  // Change to match your PMango Database Name
$dPconfig['dbuser'] = "root";  // Change to match your MySQL Username
$dPconfig['dbpass'] = "";  // Change to match your MySQL Password

// set this value to true to use persistent database connections
$dPconfig['dbpersist'] = false;

/***************** Configuration for DEVELOPERS use only! ******/
// Root directory is now automatically set to avoid
// getting it wrong. It is also deprecated as $baseDir
// is now set in top-level files index.php and fileviewer.php.
// All code should start to use $baseDir instead of root_dir.
$dPconfig['root_dir'] = $baseDir;

// Base Url is now automatically set to avoid
// getting it wrong. It is also deprecated as $baseUrl
// is now set in top-level files index.php and fileviewer.php.
// All code should start to use $baseUrl instead of base_url.
$dPconfig['base_url'] = $baseUrl;
?>
