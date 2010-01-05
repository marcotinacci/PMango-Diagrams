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
	public static  $WBSTreeSpecification = "WBSTreeSpecification";
	public static  $LevelSpecificationUserOption = "LevelSpecificationUserOption";
	public static  $ImageDimensionUserOption = "ImageDimensionUserOption";
	public static  $CustomDimUserOption = "CustomDimUserOption";
	public static  $OpenInNewWindowUserOption = "OpenInNewWindowUserOption";
	public static  $PlannedDataUserOption = "PlannedDataUserOption";
	public static  $PlannedTimeFrameUserOption = "PlannedTimeFrameUserOption";
	public static  $ResourcesUserOption = "ResourcesUserOption";
	public static  $ActualTimeFrameUserOption = "ActualTimeFrameUserOption";
	public static  $ActualDataUserOption = "ActualDataUserOption";
	public static  $AlertMarkUserOption = "AlertMarkUserOption";
	public static  $ReplicateArrowUserOption = "ReplicateArrowUserOption";
	public static  $UseDifferentPatternForCrossingLinesUserOption = "UseDifferentPatternForCrossingLinesUserOption";
	
	
	
	// Gantt
	
	public static $EffortInformationUserOption = "EffortInformationUserOption";
	public static $FinishToStartDependenciesUserOption = "FinishToStartDependenciesUserOption";
	public static $TimeGrainUserOption = "TimeGrainUserOption";
	public static $TimeRangeUserOption = "TimeRangeUserOption";
	public static $CustomRangeUserOption = "CustomRangeUserOption";
	public static $FromStartRangeUserOption = "FromStartRangeUserOption";
	public static $ToEndRangeUserOption = "ToEndRangeUserOption";
	
	
	
	// Task Network
	
	public static $TimeGapsUserOption = "TimeGapsUserOption";
	public static $ShowCompleteDiagramDependencies = "ShowCompleteDiagramDependencies";
	public static $CriticalPathUserOption = "CriticalPathUserOption";
	public static $MaxCriticalPathNumberUserOption = "MaxCriticalPathNumberUserOption";
} 

?>