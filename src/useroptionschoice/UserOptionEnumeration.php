<?php

/**
 * this class model the concept of enumeration of a fixed list of item, in 
 * particular with this abstraction we capture the useroption choices to use in
 * UserOptionChoice class. 
 * 
 */

Class UserOptionEnumeration {
	
	// Common
	
	public static final $TaskNameUserOption = "TaskNameUserOption";
	public static final $WBSTreeSpecification = "WBSTreeSpecification";
	public static final $LevelSpecificationUserOption = "LevelSpecificationUserOption";
	public static final $ImageDimensionUserOption = "ImageDimensionUserOption";
	public static final $CustomDimUserOption = "CustomDimUserOption";
	public static final $OpenInNewWindowUserOption = "OpenInNewWindowUserOption";
	public static final $PlannedDataUserOption = "PlannedDataUserOption";
	public static final $PlannedTimeFrameUserOption = "PlannedTimeFrameUserOption";
	public static final $ResourcesUserOption = "ResourcesUserOption";
	public static final $ActualTimeFrameUserOption = "ActualTimeFrameUserOption";
	public static final $ActualDataUserOption = "ActualDataUserOption";
	public static final $AlertMarkUserOption = "AlertMarkUserOption";
	public static final $ReplicateArrowUserOption = "ReplicateArrowUserOption";
	public static final $UseDifferentPatternForCrossingLinesUserOption = "UseDifferentPatternForCrossingLinesUserOption";
	
	
	
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