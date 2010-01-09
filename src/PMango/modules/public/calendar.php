<?php 
/**
 ---------------------------------------------------------------------------

 PMango Project

 Title:      calendar.

 File:       calendar.php
 Location:   pmango\modules\public
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

require_once( "$baseDir/classes/ui.class.php" );
require_once( "$baseDir/modules/calendar/calendar.class.php" );

$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$date = dpGetParam( $_GET, 'date', null );
$prev_date = dpGetParam( $_GET, 'uts', null );

// if $date is empty, set to null
$date = $date !== '' ? $date : null;

$this_month = new CDate( $date );

$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
?>
<a href="javascript: void(0);" onClick="clickDay('', '');">clear date</a>
<?php
$cal = new CMonthCalendar( $this_month );
$cal->setStyles( 'poptitle', 'popcal' );
$cal->showWeek = false;
$cal->callback = $callback;
$cal->setLinkFunctions( 'clickDay' );

if(isset($prev_date)){
	$highlights=array(
		$prev_date => "#FF8888"
	);
	$cal->setHighlightedDays($highlights);
	$cal->showHighlightedDays = true;
}

echo $cal->show();
?>
<script language="javascript">
/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
	function clickDay( idate, fdate ) {
		window.opener.<?php echo $callback;?>(idate,fdate);
		window.close();
	}
</script>
<table border="0" cellspacing="0" cellpadding="3" width="100%">
	<tr>
<?php
	for ($i=0; $i < 12; $i++) {
		$this_month->setMonth( $i+1 );
		echo "\n\t<td width=\"8%\">"
			."<a href=\"index.php?m=public&a=calendar&dialog=1&callback=$callback&date=".$this_month->format( FMT_TIMESTAMP_DATE )."&uts=$prev_date\" class=\"\">".substr( $this_month->format( "%b" ), 0, 1)."</a>"
			."</td>";
	}
?>
	</tr>
	<tr>
<?php
	echo "\n\t<td colspan=\"6\" align=\"left\">";
	echo "<a href=\"index.php?m=public&a=calendar&dialog=1&callback=$callback&date=".$cal->prev_year->format( FMT_TIMESTAMP_DATE )."&uts=$prev_date\" class=\"\">".$cal->prev_year->getYear()."</a>";
	echo "</td>";
	echo "\n\t<td colspan=\"6\" align=\"right\">";
	echo "<a href=\"index.php?m=public&a=calendar&dialog=1&callback=$callback&date=".$cal->next_year->format( FMT_TIMESTAMP_DATE )."&uts=$prev_date\" class=\"\">".$cal->next_year->getYear()."</a>";
	echo "</td>";
?>
	</tr>
</table>
