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
	
	public static final $EffortInformationUserOption = "EffortInformationUserOption";
	public static final $FinishToStartDependenciesUserOption = "FinishToStartDependenciesUserOption";
	public static final $TimeGrainUserOption = "TimeGrainUserOption";
	public static final $TimeRangeUserOption = "TimeRangeUserOption";
	public static final $CustomRangeUserOption = "CustomRangeUserOption";
	public static final $FromStartRangeUserOption = "FromStartRangeUserOption";
	public static final $ToEndRangeUserOption = "ToEndRangeUserOption";
	
	
	
	// Task Network
	
	public static final $TimeGapsUserOption = "TimeGapsUserOption";
	public static final $ShowCompleteDiagramDependencies = "ShowCompleteDiagramDependencies";
	public static final $CriticalPathUserOption = "CriticalPathUserOption";
	public static final $MaxCriticalPathNumberUserOption = "MaxCriticalPathNumberUserOption";
} 

?>