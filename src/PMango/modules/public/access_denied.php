<?php 
/**
 ---------------------------------------------------------------------------

 PMango Project

 Title:      access denied.

 File:       access_denied.php
 Location:   pmango\modules\public
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango access denied.
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

?>
<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br />
<table width="98%" cellspacing="1" cellpadding="0" border="0">
	<tr>
	<td><img src="./images/icons/mangoIcon.jpg" alt="" border="0"></td>
		<td nowrap="nowrap" width="100%"><h1><?php echo $AppUI->_('Access Denied');?></h1></td>
	</tr>
</table>

<p>
<table width="95%" border=0 cellpadding="5" cellspacing=0>
<tr valign=top>
	<td width=50%><?php echo $AppUI->_('accessDeniedMsg');?></td>
	<td width=50%>&nbsp;</td>
</tr>
</table>
</p>