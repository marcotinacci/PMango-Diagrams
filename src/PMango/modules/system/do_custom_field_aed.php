<?php

/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store custom field information.

 File:       do_custom_field_aed.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
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

	require_once("./classes/CustomFields.class.php");

	$edit_field_id = dpGetParam( $_POST, "field_id", NULL );

	if ($edit_field_id != NULL)
	{
		$edit_module = dpGetParam( $_POST, "module", NULL );
		$field_name = dpGetParam( $_POST, "field_name", NULL );
		$field_description = db_escape(strip_tags(dpGetParam( $_POST, "field_description", NULL )));
		$field_htmltype = dpGetParam( $_POST, "field_htmltype", NULL );
		$field_datatype = dpGetParam( $_POST, "field_datatype", "alpha" );
		$field_extratags = db_escape(dpGetParam( $_POST, "field_extratags", NULL ));

		$list_select_items = dpGetParam( $_POST, "select_items", NULL );

		$custom_fields = New CustomFields( strtolower($edit_module), 'addedit', null, null );


		if ($edit_field_id == 0)
		{
			$fid = $custom_fields->add( $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, $msg );
		}
		else
		{
			$fid = $custom_fields->update( $edit_field_id, $field_name, $field_description, $field_htmltype, $field_datatype, $field_extratags, $msg );
		}
	
		// Add or Update a Custom Field
		if ($msg)
		{
			$AppUI->setMsg($AppUI->_('Error adding custom field:').$msg, UI_MSG_ALERT, true);
		}
		else
		{
			if ($field_htmltype == "select")
			{
				$opts = New CustomOptionList( $fid );
				$opts->setOptions( $list_select_items );

				if ($edit_field_id == 0)
				{
					$o_msg = $opts->store();
				}
				else
				{
					// To update each list would be a lot more complex than rewriting it
					$opts->delete();
					$o_msg = $opts->store();
				}

				if ($o_msg)
				{
					// Select List Failed - Delete CustomField also
				}
	
			}	

			$AppUI->setMsg($AppUI->_('Custom field added successfully'), UI_MSG_OK, true);
		}
	}
?>
