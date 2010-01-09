<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store project roles.

 File:       addeditpref.php
 Location:   pmango\modules\system
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   First version, created to manage PMango project roles.
   
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

$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = new prole();
$obj->proles_id = isset($_POST['proles_id']) ? $_POST['proles_id'] : 0;


        // prepare (and translate) the module name ready for the suffix
        $AppUI->setMsg( 'Project Roles' );
        if ($del) {
                if (($msg = $obj->delete())) {
                        $AppUI->setMsg( $msg, UI_MSG_ERROR );
                } else {
                        $AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
                }
        } else {
                $obj->proles_hour_cost=$_REQUEST["proles_hour_cost"];
                $obj->proles_name=$_REQUEST["proles_name"];
                $obj->proles_description=$_REQUEST["proles_description"];
				$obj->proles_status=$_REQUEST["proles_status"];
                if (($msg = $obj->store())) {
                        $AppUI->setMsg( $msg, UI_MSG_ERROR );
                } else {
                        $AppUI->setMsg( "updated", UI_MSG_OK, true );
                }
        }

$AppUI->redirect("m=system&a=prole");
?>

