<?php 
/**---------------------------------------------------------------------------

 PMango Project

 Title:      date class

 File:       date.class.php
 Location:   PMango/classes
 Started:    2005.09.30
 Author:     dotProject team
 Type:       class

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
   Second version, added regular date.
 - 2006.07.18 Lorenzo
   First version, unmodified from dotProject 2.0.1.

-------------------------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi, Riccardo Nicolini
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

-------------------------------------------------------------------------------------------
/**
* @package PMango
* @subpackage utilites
*/

require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );

define( 'FMT_DATEISO', '%Y%m%dT%H%M%S' );
define( 'FMT_DATELDAP', '%Y%m%d%H%M%SZ' );
define( 'FMT_DATETIME_MYSQL', '%Y-%m-%d %H:%M:%S' );
define( 'FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S' );
define( 'FMT_TIMESTAMP', '%Y%m%d%H%M%S' );
define( 'FMT_TIMESTAMP_DATE', '%Y%m%d' );
define( 'FMT_TIMESTAMP_TIME', '%H%M%S' );
define( 'FMT_REGULAR_DATE', '%d/%m/%Y' );
define( 'FMT_UNIX', '3' );
define( 'WDAY_SUNDAY',    0 );
define( 'WDAY_MONDAY',    1 );
define( 'WDAY_TUESDAY',   2 );
define( 'WDAY_WEDNESDAY',  3 );
define( 'WDAY_THURSDAY',  4 );
define( 'WDAY_FRIDAY',    5 );
define( 'WDAY_SATURDAY',  6 );
define( 'SEC_MINUTE',    60 );
define( 'SEC_HOUR',    3600 );
define( 'SEC_DAY',    86400 );

/**
* dotProject implementation of the Pear Date class
*
* This provides customised extensions to the Date class to leave the
* Date package as 'pure' as possible
*/
class CDate extends Date  {

/**
* Overloaded compare method
*
* The convertTZ calls are time intensive calls.  When a compare call is
* made in a recussive loop the lag can be significant.
*/
    function compare($d1, $d2, $convertTZ=false)
    {
		if ($convertTZ) {
			$d1->convertTZ(new Date_TimeZone('UTC'));
			$d2->convertTZ(new Date_TimeZone('UTC'));
		}
        $days1 = Date_Calc::dateToDays($d1->day, $d1->month, $d1->year);
        $days2 = Date_Calc::dateToDays($d2->day, $d2->month, $d2->year);
        if($days1 < $days2) return -1;
        if($days1 > $days2) return 1;
        if($d1->hour < $d2->hour) return -1;
        if($d1->hour > $d2->hour) return 1;
        if($d1->minute < $d2->minute) return -1;
        if($d1->minute > $d2->minute) return 1;
        if($d1->second < $d2->second) return -1;
        if($d1->second > $d2->second) return 1;
        return 0;
    }


/**
* Adds (+/-) a number of days to the current date.
* @param int Positive or negative number of days
* @author J. Christopher Pereira <kripper@users.sf.net>
*/
	function addDays( $n ) {
		$this->setDate( $this->getTime() + 60 * 60 * 24 * $n, DATE_FORMAT_UNIXTIME);
	}

/**
* Adds (+/-) a number of months to the current date.
* @param int Positive or negative number of months
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function addMonths( $n ) {
		$an = abs( $n );
		$years = floor( $an / 12 );
		$months = $an % 12;

		if ($n < 0) {
			$this->year -= $years;
			$this->month -= $months;
			if ($this->month < 1) {
				$this->year--;
				$this->month = 12 + $this->month;
			}
		} else {
			$this->year += $years;
			$this->month += $months;
			if ($this->month > 12) {
				$this->year++;
				$this->month -= 12;
			}
		}
	}	

/**
* New method to get the difference in days the stored date
* @param Date The date to compare to
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function dateDiff( $when ) {
		return Date_calc::dateDiff(
			$this->getDay(), $this->getMonth(), $this->getYear(),
			$when->getDay(), $when->getMonth(), $when->getYear()
		);
	}

/**
* New method that sets hour, minute and second in a single call
* @param int hour
* @param int minute
* @param int second
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function setTime( $h=0, $m=0, $s=0 ) {
		$this->setHour( $h );
		$this->setMinute( $m );
		$this->setSecond( $s );
	}
	
	function isWorkingDay(){
		global $AppUI;
		
		$working_days = dPgetConfig("cal_working_days");
		if(is_null($working_days)){
			$working_days = array('1','2','3','4','5');
		} else {
			$working_days = explode(",", $working_days);
		}
		
		return in_array($this->getDayOfWeek(), $working_days);
	}
	
	function getAMPM() {
		if ( $this->getHour() > 11 ) {
			return "pm";
		} else {
			return "am";
		}
	}
}
?>
