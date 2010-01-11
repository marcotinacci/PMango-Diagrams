<?php

/**
 * this class model the concept of enumeration of a fixed list of item, in 
 * particular with this abstraction we capture the useroption choices to use in
 * UserOptionChoice class. 
 * 
 * @author: Manuele Paolantonio
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

Class TimeRange {

	public static $TodayDateUserOption = "TodayDateUserOption"; //date
	public static $CustomStartDateUserOption = "CustomStartDateUserOption"; //date
	public static $CustomEndDateUserOption = "CustomFinishDateUserOption"; //date	
	
	public static $CustomRangeUserOption = "CustomRangeUserOption"; //boolean
	public static $WholeProjectRangeUserOption = "WholeProjectRangeUserOption"; //boolean
	public static $FromStartToNowRangeUserOption = "FromStartToNowRangeUserOption"; //boolean
	public static $FromNowToEndRangeUserOption = "FromNowToEndRangeUserOption"; //boolean
}

?>