<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      administator functions

 File:       admin_func.php
 Location:   pmango/functions
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       php

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   Second version, modified to manage PMango tasks.
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

$percent = array(0=>'0',5=>'5',10=>'10',15=>'15',20=>'20',25=>'25',30=>'30',35=>'35',40=>'40',45=>'45',50=>'50',55=>'55',60=>'60',65=>'65',70=>'70',75=>'75',80=>'80',85=>'85',90=>'90',95=>'95',100=>'100');

// patch 2.12.04 add all finished last 7 days, my finished last 7 days
$filters = array(
	'my'           => 'My Tasks',
	'myunfinished' => 'My Unfinished Tasks',
	'allunfinished' => 'All Unfinished Tasks',
	'unassigned'   => 'All Tasks (unassigned)',
	'all'          => 'All Tasks',
);

$status = dPgetSysVal( 'TaskStatus' );

$priority = array(
 -1 => 'low',
 0 => 'normal',
 1 => 'high'
);

?>
