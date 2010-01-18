<?php 
/**
 * This class model a descriptor for a dependency line. The information that are 
 * captured are the position of the entry and exit point for the dependency line
 * and the two task id (the needed and the dependent) that are associated by the
 * relation finish-to-start.
 * @author massimonocentini
 *
 */
class DependencyDescriptor {
	/**
	 * 
	 * @var TaskLevelPositionEnum
	 */
	var $dependentTaskPositionEnum;
	
	/**
	 * 
	 * @var TaskLevelPositionEnum
	 */
	var $neededTaskPositionEnum;
	
	var $dependentTaskId;
	var $neededTaskId;
}

/**
 * Enum to fix the position of a dependant task relative to
 * his visible parent
 * @author massimonocentini
 *
 */
class TaskLevelPositionEnum {
	public static $starting = "starting";
	public static $ending = "ending";
	public static $inner = "inner";
}

?>