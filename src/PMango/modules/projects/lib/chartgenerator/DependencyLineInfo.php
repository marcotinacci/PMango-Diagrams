<?php
require "./modules/PMangoCPM/chartgenerator/PointInfo.php"; 

class DependencyLineInfo {
	/**
	 * 
	 * @var PointInfo
	 */
	var $exitPoint;
	
	/**
	 * 
	 * @var PointInfo
	 */
	var $entryPoint;
	
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
}
?>