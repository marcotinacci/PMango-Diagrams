<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      page header.

 File:       header.php
 Location:   pmango\style\default
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2007.01.08 Giovanni
   Added a target='_new' when $dialog is false for the anchor linked to 
   the PMango logo.
 - 2006.07.30 Lorenzo
   Second version, modified to generate new PMango interface.
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
$dialog = dPgetParam( $_GET, 'dialog', 0 );
if ($dialog)
	$page_title = '';
else
	$page_title = ($dPconfig['page_title'] == 'PMango') ? $dPconfig['page_title'] . '&nbsp;' . $AppUI->getVersion() : $dPconfig['page_title'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="Description" content="PMango Default Style" />
	<meta name="Version" content="<?php echo @$AppUI->getVersion();?>" />
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';?>" />
	<title><?php echo @dPgetConfig( 'page_title' );?></title>
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>
	<link rel="shortcut icon" href="./style/<?php echo $uistyle;?>/images/favicon.jpg" type="image/ico" />
	<?php @$AppUI->loadJS(); ?>
</head>
<!--Serve per far rientrare il contenuto dello schermo, lasciare un po' di spazi ai bordi-->
<table width="100%" cellspacing="0" cellpadding="4" border="0">
<tr>
<td valign="top" align="left" width="98%">
	<body onload="this.focus();">
	
	<table width='100%' cellpadding=0 cellspacing=0 border=0 >
		<tr>
			<td height="26" align="left" background="style/<?php echo $uistyle;?>/images/titlegrad.jpg">
					&nbsp;&nbsp;
					<?php if (!$dialog) {?>
						<strong><a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $AppUI->user_id;?>">
						<?php echo " $AppUI->user_first_name $AppUI->user_last_name"; ?></a></strong>
						<i><?php echo "at ".$dPconfig['organization_name']?></i>
					<?php }?>
			</td>
			<td align="right" width='50'>
					<?php if (!$dialog) {?><a href='http://pmango.sourceforge.net/' <?php if ($dialog) echo "target='_blank'"; else echo "target='_new'"; ?>><?php }?>
				<img  border=0 src="style/<?php echo $uistyle;?>/images/mango.jpg"><?php if (!$dialog) {?></a><?php }?></td>
		</tr>
	</table>

