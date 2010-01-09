<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      load and store event
 
 File:       do_event_aed.php
 Location:   pmango\modules\calendar
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
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
$obj = new CEvent();
$msg = '';

$del = dPgetParam( $_POST, 'del', 0 );

// bind the POST parameter to the object record
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}
// configure the date and times to insert into the db table
if ($obj->event_start_date) {
	$start_date = new CDate( $obj->event_start_date.$_POST['start_time'] );
	$obj->event_start_date = $start_date->format( FMT_DATETIME_MYSQL );
}
if ($obj->event_end_date) {
	$end_date = new CDate( $obj->event_end_date.$_POST['end_time'] );
	$obj->event_end_date = $end_date->format( FMT_DATETIME_MYSQL );
}

if (!$del && $start_date->compare ($start_date, $end_date) >= 0)
{
	$AppUI->setMsg( "Start-Date >= End-Date, please correct", UI_MSG_ERROR );
	$AppUI->redirect();
	exit;
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Event' );
$do_redirect = true;
require_once $AppUI->getSystemClass("CustomFields");

if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
	$AppUI->redirect( 'm=calendar' );
} else {
	$isNotNew = @$_POST['event_id'];
	if (!$isNotNew) {
		$obj->event_owner = $AppUI->user_id;
	}
	// Check for existence of clashes.
	if ($_POST['event_assigned'] > '' && ($clash = $obj->checkClash($_POST['event_assigned']))) {
	  $last_a = $a;
	  $GLOBALS['a'] = "clash";
	  $do_redirect = false;
	} else {
	  if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	  } else {
		$custom_fields = New CustomFields( 'calendar', 'addedit', $obj->event_id, "edit" );
		$custom_fields->bind( $_POST );
		$sql = $custom_fields->store( $obj->event_id ); // Store Custom Fields

		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
		if (isset($_POST['event_assigned']))
		      $obj->updateAssigned(explode(",",$_POST['event_assigned']));
		if (isset($_POST['mail_invited'])) {
		      $obj->notify(@$_POST['event_assigned'], $isNotNew);
		}
	  }
	}
}
if ($do_redirect)
  $AppUI->redirect();
?>
