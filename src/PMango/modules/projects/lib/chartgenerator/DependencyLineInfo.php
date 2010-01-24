<?php
require_once dirname(__FILE__) . "/PointInfo.php"; 

class DependencyLineInfo {
	/**
	 * 
	 * @var PointInfo
	 */
	var $neededTaskboxDrawInformation;
	
	/**
	 * 
	 * @var PointInfo
	 */
	var $dependentTaskboxDrawInformation;
	
	var $dependencyDescriptor;
	
	/**
	 * pixel
	 * @var int
	 */
	var $horizontalOffset;
	
	/**
	 * pixel
	 * @var int
	 */
	var $verticalOffset;
	
	/**
	 * task id of the needed task
	 * @var int
	 */
	var $neededTaskId;
	
	/**
	 * task id of the dependent task
	 * @var int
	 */
	var $dependentTaskId;
	
	/**
	 * number of dots to distinguish between a two dependency line
	 * @var integer
	 */
	var $dotsInPattern;
	
	var $replicateArrow;
}
?>