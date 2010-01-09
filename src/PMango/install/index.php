<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      home page to PMango install

 File:       index.php
 Location:   pmango\install
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, new procedure to install PMango.
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

$mode = 'install';
if (is_file( "../includes/config.php" )) {
	$mode = 'upgrade';
}
?>
<html>
<head>
	<title>PMango Installer</title>
	<meta name="Description" content="PMango Installer">
 	<link rel="stylesheet" type="text/css" href="../style/default/main.css">
</head>
<body>
<h1><img src="mango.png" align="middle" alt="Mango Logo"/>&nbsp;PMango Installer</h1>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center">
<tr>
        <td class="item" colspan="2">Welcome to the PMango Installer! It will setup the database for PMango and create an appropriate config file.
	In some cases a manual installation cannot be avoided.
        </td>
</tr>
<tr>
        <td colspan="2">&nbsp;</td>
</tr>
<tr>
        <td class="title" colspan="2">There is an initial Check for (minimal) Requirements appended down below for troubleshooting. At least a database connection
	must be available and ../includes/config.php must be writable for the webserver!</td>
</tr>
<?php
	if ($mode == 'upgrade') {
?>
<tr>
	<td class='title' colspan='2'><p class='error'>It would appear that you already have a PMango installation. The installar will attempt to upgrade your system, however it is a good idea to take a full backup first!</p></td>
<?php
	}
?>
<tr>
        <td colspan="2" align="center"><br /><form action="db.php" method="post" name="form" id="form">
	<input class="button" type="submit" name="next" value="Start <?php echo $mode == 'install' ? "Installation" : "Upgrade" ?>" />
	<input type="hidden" name="mode" value="<?php echo $mode; ?>" /></form></td>
</tr>
</table>
<br />
<?php
// define some necessary variables for check inclusion
$failedImg = '<img src="../images/icons/stock_cancel-16.png" width="16" height="16" align="middle" alt="Failed"/>';
$okImg = '<img src="../images/icons/stock_ok-16.png" width="16" height="16" align="middle" alt="OK"/>';
$tblwidth = '90%';
$cfgDir = "../includes";
$cfgFile = "../includes/config.php";
$filesDir = "../files";
$locEnDir = "../locales/en";
$tmpDir = "../files/temp";
include_once("vw_idx_check.php");
?>
</body>
</html>
