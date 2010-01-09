<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      administator functions

 File:       admin_func.php
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
// user types
$utypes = array(
// DEFAULT USER (nothing special)
	0 => '',
// DO NOT CHANGE ADMINISTRATOR INDEX !
	1 => 'Administrator',
// you can modify the terms below to suit your organisation
	2 => 'CEO',
	3 => 'Director',
	4 => 'Branch Manager',
	5 => 'Manager',
	6 => 'Supervisor',
	7 => 'Employee'
);

##
##	NOTE: the user_type field in the users table must be changed to a TINYINT
##
?>
