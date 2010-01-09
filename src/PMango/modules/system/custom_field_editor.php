<?php

/**
---------------------------------------------------------------------------

 PMango Project

 Title:      custom field editor.

 File:       custom_field_editor.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango custom field editor.
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

	$AppUI->savePlace();

	require_once("./classes/CustomFields.class.php");

	$titleBlock = new CTitleBlock('Custom field editor', "customfields.png", "admin", "admin.custom_field_editor");
	$titleBlock->addCrumb( "?m=system", "System admin" );

	$edit_field_id = dpGetParam( $_POST, "field_id", NULL );

	$titleBlock->show();

	$sql = "SELECT * FROM modules WHERE mod_name IN ('Groups', 'Projects', 'Tasks') ORDER BY mod_ui_order";
	$modules = db_loadList( $sql );

	echo "<table cellpadding=\"2\">";

	foreach ($modules as $module)
	{
		echo "<tr><td colspan=\"4\">";
		echo "<h3>".$AppUI->_($module["mod_name"])."</h3>";
		echo "</td></tr>";

		echo "<tr><td colspan=\"4\">";
		echo "<a href=\"?m=system&a=custom_field_addedit&module=".$module["mod_name"]."\"><img src='./images/icons/stock_new.png' align='center' width='16' height='16' border='0'>".$AppUI->_('Add a new Custom Field to this Module')."</a><br /><br />";
		echo "</td></tr>";

		$sql = "SELECT * FROM custom_fields_struct WHERE field_module = '".strtolower($module["mod_name"])."'";
		$custom_fields = db_loadList( $sql );

		foreach ($custom_fields as $f)
		{
			echo "<tr><td class=\"hilite\">";
			echo "<a href=\"?m=system&a=custom_field_addedit&module=".$module["mod_name"]."&field_id=".$f["field_id"]."\"><img src='./images/icons/stock_edit-16.png' align='center' width='16' height='16' border='0'>Edit</a>";
			echo "</td><td class=\"hilite\">";
			echo "<a href=\"?m=system&a=custom_field_addedit&field_id=".$f["field_id"]."&delete=1\"><img src='./images/icons/stock_delete-16.png' align='center' width='16' height='16' border='0'>Delete</a> ";
			echo "</td><td class=\"hilite\">";
			echo stripslashes($f["field_description"])."\n";
			echo "</td></tr>";
		}
	}
?>