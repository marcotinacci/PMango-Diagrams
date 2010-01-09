<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      home page calendar module
 
 File:       index.php
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
$AppUI->savePlace();

dPsetMicroTime();

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany', $AppUI->user_company);

// Using simplified set/get semantics. Doesn't need as much code in the module.
$event_filter = $AppUI->checkPrefState('CalIdxFilter', @$_REQUEST['event_filter'], 'EVENTFILTER', 'my');

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// get the list of visible companies
$company = new CCompany();
$companies = $company->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

#echo '<pre>';print_r($events);echo '</pre>';
// setup the title block
$titleBlock = new CTitleBlock( 'Monthly Calendar', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCell( $AppUI->_('Company').':' );
$titleBlock->addCell(
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany">', '</form>'
);
$titleBlock->addCell( $AppUI->_('Event Filter') . ':');
$titleBlock->addCell(
	arraySelect($event_filter_list, 'event_filter', 'onChange="document.pickFilter.submit()" class="text"',
	$event_filter, true ), '', "<Form action='{$_SERVER['REQUEST_URI']}' method='post' name='pickFilter'>", '</form>'
);
$titleBlock->show();
?>

<script language="javascript">
function clickDay( uts, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+uts;
}
function clickWeek( uts, fdate ) {
	window.location = './index.php?m=calendar&a=week_view&date='+uts;
}
</script>

<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td>
<?php
// establish the focus 'date'
$date = new CDate( $date );

// prepare time period for 'events'
$first_time = new CDate( $date );
$first_time->setDay( 1 );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( $date );
$last_time->setDay( $date->getDaysInMonth() );
$last_time->setTime( 23, 59, 59 );

$links = array();

// assemble the links for the tasks
require_once( dPgetConfig( 'root_dir' )."/modules/calendar/links_tasks.php" );
getTaskLinks( $first_time, $last_time, $links, 20, $company_id );

// assemble the links for the events
require_once( dPgetConfig( 'root_dir' )."/modules/calendar/links_events.php" );
getEventLinks( $first_time, $last_time, $links, 20 );

// create the main calendar
$cal = new CMonthCalendar( $date  );
$cal->setStyles( 'motitle', 'mocal' );
$cal->setLinkFunctions( 'clickDay', 'clickWeek' );
$cal->setEvents( $links );

echo $cal->show();
//echo '<pre>';print_r($cal);echo '</pre>';

// create the mini previous and next month calendars under
$minical = new CMonthCalendar( $cal->prev_month );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions( 'clickDay' );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td valign="top" align="center" width="200">'.$minical->show().'</td>';
echo '<td valign="top" align="center" width="100%">&nbsp;</td>';

$minical->setDate( $cal->next_month );

echo '<td valign="top" align="center" width="200">'.$minical->show().'</td>';
echo '</tr></table>';
?>
</td></tr></table>
