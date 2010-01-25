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

Class UserOptionEnumeration {
	
	// Common
	
	public static  $TaskNameUserOption = "TaskNameUserOption";
	public static  $OpenInNewWindowUserOption = "OpenInNewWindowUserOption";
	public static  $PlannedDataUserOption = "PlannedDataUserOption";
	public static  $PlannedTimeFrameUserOption = "PlannedTimeFrameUserOption";
	public static  $ResourcesUserOption = "ResourcesUserOption";
	public static  $ActualTimeFrameUserOption = "ActualTimeFrameUserOption";
	public static  $ActualDataUserOption = "ActualDataUserOption";
	public static  $AlertMarkUserOption = "AlertMarkUserOption";
	public static  $ReplicateArrowUserOption = "ReplicateArrowUserOption";
	public static  $UseDifferentPatternForCrossingLinesUserOption = "UseDifferentPatternForCrossingLinesUserOption";
	public static  $TimeRangeUserOption = "TimeRangeUserOption";
	public static  $TimeGrainUserOption = "TimeGrainUserOption";
	
	public static $ImageDimensionsUserOption = "ImageDimensionsUserOption";
	public static $DefaultWidthUserOption = "DefaultWidthUserOption"; //int
	public static $DefaultHeightUserOption = "DefaultHeightUserOption"; //int
	public static $CustomWidthUserOption = "CustomWidthUserOption"; //int
	public static $CustomHeightUserOption = "CustomHeightUserOption"; //int
	public static $FitInWindowWidthUserOption = "FitInWindowWidthUserOption"; //int
	public static $FitInWindowHeightUserOption = "FitInWindowHeightUserOption"; //int
	
	public static $TodayDateUserOption = "TodayDateUserOption"; //date
	public static $CustomStartDateUserOption = "CustomStartDateUserOption"; //date
	public static $CustomEndDateUserOption = "CustomEndDateUserOption"; //date	
	
	// Gantt
	
	public static $EffortInformationUserOption = "EffortInformationUserOption";
	public static $FinishToStartDependenciesUserOption = "FinishToStartDependenciesUserOption";
	
	// Task Network
	
	public static $TimeGapsUserOption = "TimeGapsUserOption";
	public static $ShowCompleteDiagramDependencies = "ShowCompleteDiagramDependencies";
	public static $CriticalPathUserOption = "CriticalPathUserOption";
	public static $SelectedCriticalPathNumberUserOption = "SelectedCriticalPathNumberUserOption";
} 

?>