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

Class ImageDimension {
	
	public static $CustomDimUserOption = "CustomDimUserOption"; //boolean
	public static $CustomWidthUserOption = "CustomWidthUserOption"; //int
	public static $CustomHeightUserOption = "CustomHeightUserOption"; //int
	public static $FitInWindowDimUserOption = "FitInWindowDimUserOption"; //boolean
	public static $OptiomalDimUserOption = "OptiomalDimUserOption"; //boolean
	public static $DefaultDimUserOption = "DefaultDimUserOption"; //boolean
	public static $DefaultWidthUserOption = "DefaultWidthUserOption"; //int
	public static $DefaultHeightUserOption = "DefaultHeightUserOption"; //int
}

?>