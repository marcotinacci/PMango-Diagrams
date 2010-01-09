<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      links events
 
 File:       links_events.php
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

/**
* Sub-function to collect events within a period
* @param Date the starting date of the period
* @param Date the ending date of the period
* @param array by-ref an array of links to append new items to
* @param int the length to truncate entries by
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
function getEventLinks( $startPeriod, $endPeriod, &$links, $strMaxLen ) {
	global $event_filter;
	$events = CEvent::getEventsForPeriod( $startPeriod, $endPeriod, $event_filter );

	// assemble the links for the events
	foreach ($events as $row) {
		$start = new CDate( $row['event_start_date'] );
		$end = new CDate( $row['event_end_date'] );
		$date = $start;
		$cwd = explode(",", $GLOBALS["dPconfig"]['cal_working_days']);

		for($i=0; $i <= $start->dateDiff($end); $i++) {
		// the link
			// optionally do not show events on non-working days 
			if ( ( $row['event_cwd'] && in_array($date->getDayOfWeek(), $cwd ) ) || !$row['event_cwd'] ) {
				$url = '?m=calendar&a=view&event_id=' . $row['event_id'];
				$link['href'] = '';
				$link['alt'] = $row['event_description'];
				$link['text'] = '<table cellspacing="0" cellpadding="0" border="0"><tr>'
					. '<td><a href=' . $url . '>' . dPshowImage( dPfindImage( 'event'.$row['event_type'].'.png', 'calendar' ), 16, 16, '' )
					. '</a></td>'
					. '<td><a href="' . $url . '" title="'.$row['event_description'].'"><span class="event">'.$row['event_title'].'</span></a>'
					. '</td></tr></table>';
				$links[$date->format( FMT_TIMESTAMP_DATE )][] = $link;
			 }
				$date = $date->getNextDay();
		}
	}
}
?>
