<?php

require_once dirname(__FILE__)."/../../../tasks/tasks.class.php";

/**
 * this method is necessary to load at runtime and only if is needed the
 * relative tasks.class.php file (if this is already loaded, the runtime
 * throw an error saying that some classes are alredy defined)
 * @param $class_name
 * @return void
 */
function __autoload($class_name){
	//print $class_name;
	//	require ("./modules/tasks/tasks.class.php");
}
/**
 * This class provide the abstraction for creating the associative array
 * needed by the class Task.
 */
class DataArrayBuilder {
	/**
	 * $CTaskObject object with type CTask
	 * (defined in /modules/task.class.php) to be used for build a array
	 */
	var $_CTaskObject;

	/**
	 * associative array with the value made by this object
	 */
	var $_associativeArray = array();

	/**
	 * constructor
	 * @param task_id, task identifier (not the wbs identifier, this parameter
	 * refers to the key column of the task table)
	 */
	public function __construct($task_id) {
		$this->_CTaskObject = new CTask();
		$this->_CTaskObject->load($task_id);
		//var_dump($this->_CTaskObject);
	}

	public function __destruct() {
		$this->_CTaskObject = null; // to protect from memory leak
	}

	/**
	 * get the resulting array
	 * @return associative array with all the key needed by the Task class
	 */
	public function getAssociativeArray() {
		return $this->_associativeArray;
	}

	/**
	 * Build the entry into the associative array with wbs identifier information
	 * @return void
	 */
	public function buildWBSIdentifier() {
		$this->_associativeArray[DataArrayKeyEnumeration::$wbsIdentifier] =
		$this->_CTaskObject->getWBS($this->_CTaskObject->task_id);
		//task_wbs_index;
	}
	
	public function buildName() {
		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
		$this->_CTaskObject->task_name;
	}
	
	public function buildFtsDependencies(){
		$this->_associativeArray[DataArrayKeyEnumeration::$ftsDependencies] = 
			$this->_CTaskObject->getDependencies();
			//comma delimited list (string) of tasks id's
	}

//	public function buildName() {
//		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
//		$this->_CTaskObject->task_name;
//	}
//
//	public function buildName() {
//		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
//		$this->_CTaskObject->task_name;
//	}
//
//	public function buildName() {
//		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
//		$this->_CTaskObject->task_name;
//	}
//
//	public function buildName() {
//		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
//		$this->_CTaskObject->task_name;
//	}
//
//	public function buildName() {
//		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
//		$this->_CTaskObject->task_name;
//	}
//
//	public function buildName() {
//		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
//		$this->_CTaskObject->task_name;
//	}
//
//	public static $name = "name";
//	public static $plan_effort = "plan_effort";
//	public static $assigned_to_task = "assigned_to_task";
//	public static $plan_duration = "plan_duration";
//	public static $plan_cost = "plan_cost";
//	public static $planned_start_date = "planned_start_date";
//	public static $planned_finish_date = "planned_finish_date";
//	public static $act_duration = "act_duration";
//	public static $act_effort = "act_effort";
//	public static $act_cost = "act_cost";
//	public static $actual_start_date = "actual_start_date";
//	public static $actual_finish_date = "actual_finish_date";
//	public static $level = "level";
//	public static $percentage = "percentage";

	/*
	 public function buildTaskName() {
		$this->_associativeArray[DataArrayKeyEnumeration::$name] =
		$this->_CTaskObject->task_name;
		//task_name;
		}

		*/

}
?>