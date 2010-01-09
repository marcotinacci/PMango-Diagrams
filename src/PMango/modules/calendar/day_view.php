<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      day view
 
 File:       day_view.php
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
global $tab, $locale_char_set;
$AppUI->savePlace();

require_once( $AppUI->getModuleClass( 'tasks' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'CalIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'CalIdxCompany', $AppUI->user_company);

$event_filter = $AppUI->checkPrefState('CalIdxFilter', @$_REQUEST['event_filter'], 'EVENTFILTER', 'my');

$AppUI->setState( 'CalDayViewTab', dPgetParam($_GET, 'tab', $tab) );
$tab = $AppUI->getState( 'CalDayViewTab' ,'0');

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// establish the focus 'date'
$this_day = new CDate( $date );
$dd = $this_day->getDay();
$mm = $this_day->getMonth();
$yy = $this_day->getYear();

// get current week
$this_week = Date_calc::beginOfWeek ($dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY );

// prepare time period for 'events'
$first_time = $this_day;
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );

$last_time = $this_day;
$last_time->setTime( 23, 59, 59 );

$prev_day = new CDate( Date_calc::prevDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );
$next_day = new CDate( Date_calc::nextDay( $dd, $mm, $yy, FMT_TIMESTAMP_DATE ) );

// setup the title block
$titleBlock = new CTitleBlock( 'Day View', 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar&date=".$this_day->format( FMT_TIMESTAMP_DATE ), "month view" );
$titleBlock->addCrumb( "?m=calendar&a=week_view&date=".$this_week, "week view" );
$titleBlock->addCell(
	'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
	'<form action="?m=calendar&a=addedit&date=' . $this_day->format( FMT_TIMESTAMP_DATE )  . '" method="post">', '</form>'
);
$titleBlock->show();
?>
<script language="javascript">
function clickDay( idate, fdate ) {
	window.location = './index.php?m=calendar&a=day_view&date='+idate;
}
</script>

<table width="100%" cellspacing="0" cellpadding="4">
<tr>
	<td valign="top">
		<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
		<tr>
			<td>
				<a href="<?php echo '?m=calendar&a=day_view&date='.$prev_day->format( FMT_TIMESTAMP_DATE ); ?>"><img src="images/prev.gif" width="16" height="16" alt="pre" border="0"></a>
			</td>
			<th width="100%">
				<?php echo htmlentities($this_day->format( "%A" ), ENT_COMPAT, $locale_char_set).', '.$this_day->format( $df ); ?>
			</th>
			<td>
				<a href="<?php echo '?m=calendar&a=day_view&date='.$next_day->format( FMT_TIMESTAMP_DATE ); ?>"><img src="images/next.gif" width="16" height="16" alt="next" border="0"></a>
			</td>
		</tr>
		</table>

<?php
// tabbed information boxes
$tabBox = new CTabBox( "?m=calendar&a=day_view&date=" . $this_day->format( FMT_TIMESTAMP_DATE ),
	"{$dPconfig['root_dir']}/modules/calendar/", $tab );
$tabBox->add( 'vw_day_events', 'Events' );
$tabBox->add( 'vw_day_tasks', 'Tasks' );
$tabBox->show();
?>
	</td>
<?php if ($dPconfig['cal_day_view_show_minical']) { ?>
	<td valign="top" width="175">
<?php
$minical = new CMonthCalendar( $this_day );
$minical->setStyles( 'minititle', 'minical' );
$minical->showArrows = false;
$minical->showWeek = false;
$minical->clickMonth = true;
$minical->setLinkFunctions( 'clickDay' );

$minical->setDate( $minical->prev_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$minical->setDate( $minical->next_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table><hr noshade size="1">';

$minical->setDate( $minical->next_month );

echo '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
echo '<td align="center" >'.$minical->show().'</td>';
echo '</tr></table>';
?>
	</td>
 <?php } ?>
</tr>
</table>
