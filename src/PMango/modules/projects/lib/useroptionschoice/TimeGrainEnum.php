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

Class TimeGrainEnum {
	
	public static  $HourlyGrainUserOption = "HourlyGrainUserOption";
	public static  $DailyGrainUserOption = "DailyGrainUserOption";
	public static  $WeaklyGrainUserOption = "WeaklyGrainUserOption";
	public static  $MonthlyGrainUserOption = "MonthlyGrainUserOption";
	public static  $AnnuallyGrainUserOption = "AnnuallyGrainUserOption";
}

?>