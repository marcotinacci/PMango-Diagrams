<?php 
require_once dirname(__FILE__) . "/../commons/DateComparer.php";
require_once dirname(__FILE__) . "/../../../tasks/tasks.class.php";
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
	 * Position relative to the entry point for the dependent task
	 * @var TaskLevelPositionEnum
	 */
	var $dependentTaskPositionEnum;
	
	/**
	 * Position relative to the exit point for the needed task
	 * @var TaskLevelPositionEnum
	 */
	var $neededTaskPositionEnum;
	
	/**
	 * task id of the leaf that contains (or is) the dependent task
	 * present in a finish-to-start relation pair.
	 * @var integer
	 */
	var $dependentTaskId;
	
	/**
	 * task id of the leaf that contains (or is) the needed task
	 * present in a finish-to-start relation pair.
	 * @var integer
	 */
	var $neededTaskId;
	
	/**
	 * task id of the dependent task that is really associated in a relation
	 * pair
	 * @var integer
	 */
	var $reallyDependentTaskId;
	
	/**
	 * task id of the needed task that is really associated in a relation
	 * pair
	 * @var integer
	 */
	var $reallyNeededTaskId;
	
	public function __toString() {
		return "really relation pair = (" . $this->reallyNeededTaskId . ", " . 
			$this->reallyDependentTaskId . ") / " . 
			"leaves triple: (needed exit: " . $this->neededTaskPositionEnum . 
			", dep id: " . $this->dependentTaskId . 
			", dep entry: " . $this->dependentTaskPositionEnum . ")";
	}
	
	public function getTimeGap() {
		$neededTask = new CTask();
		$neededTask->load($this->reallyNeededTaskId);
		
		$dependentTask = new CTask();
		$dependentTask->load($this->reallyDependentTaskId);
		
		return TimeGapComputer::computeTimeGap($neededTask->task_finish_date, 
			$dependentTask->task_start_date);
	}
}

class TimeGapComputer {
	public static function computeTimeGap($from, $to) {
		$dependentDate = new DateComparer($to);
		return $dependentDate->substract($from);
	}
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