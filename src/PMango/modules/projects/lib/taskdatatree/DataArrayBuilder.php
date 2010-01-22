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

	public function buildPlannedStartDate() {
		$this->_associativeArray[DataArrayKeyEnumeration::$planned_start_date] =
		$this->_CTaskObject->task_start_date;
	}
	
	public function buildPlannedFinishDate() {
		$this->_associativeArray[DataArrayKeyEnumeration::$planned_finish_date] =
		$this->_CTaskObject->task_finish_date;
	}

	public function buildActualStartDate() {
//		$actual_start_date = $this->_CTaskObject->getActualStartDate($this->_CTaskObject->task_id, null);
		$actual_start_date = $this->_CTaskObject->getActualStartDate($this->_CTaskObject->task_id, $this->_CTaskObject->getChild());
		if(!isset($actual_start_date['task_log_start_date']) || $actual_start_date['task_log_start_date']=="i" ||  $actual_start_date['task_log_start_date']=="")
			$this->_associativeArray[DataArrayKeyEnumeration::$actual_start_date] = "";
		else
			$this->_associativeArray[DataArrayKeyEnumeration::$actual_start_date] = $actual_start_date['task_log_start_date'];
	}
	
	public function buildActualFinishDate() {
//		$actual_finish_date = $this->_CTaskObject->getActualFinishDate($this->_CTaskObject->task_id, null);
		$actual_finish_date = $this->_CTaskObject->getActualFinishDate($this->_CTaskObject->task_id, $this->_CTaskObject->getChild());
		if(!isset($actual_finish_date['task_log_finish_date']) || $actual_finish_date['task_log_finish_date']=="i" ||  $actual_finish_date['task_log_finish_date']=="")
			$this->_associativeArray[DataArrayKeyEnumeration::$actual_finish_date] = "";
		else
			$this->_associativeArray[DataArrayKeyEnumeration::$actual_finish_date] = $actual_finish_date['task_log_finish_date'];
	}
	
  	public function buildAssignedToTask() {
  		$a = $this->_CTaskObject->getAssignedUsers();
  		for($i=0; $i<sizeOf($a); $i++)
  		{
  			$res[$i]['LastName'] = $a[$i]['LastName'];
  			$res[$i]['Effort'] = $a[$i]['Effort'];
  			$res[$i+1]['Role'] = $a[$i]['Role'];
  		}
  		$res[$i-1]['ActualEffort'] = $this->_CTaskObject->getResourceActualEffortInTask();
  		if(isset($res))
  			$this->_associativeArray[DataArrayKeyEnumeration::$assigned_to_task] = $res;
  		else
  			$this->_associativeArray[DataArrayKeyEnumeration::$assigned_to_task]=null;	
	}

	public function buildPlannedEffort() {
		$this->_associativeArray[DataArrayKeyEnumeration::$plan_effort] =
		$this->_CTaskObject->getEffort($this->_CTaskObject->task_id);
	}

	public function buildActualEffort() {
		$this->_associativeArray[DataArrayKeyEnumeration::$act_effort] =
		$this->_CTaskObject->getActualEffort($this->_CTaskObject->task_id, $this->_CTaskObject->getChild());
	}

	public function buildPlannedCost() {
		$this->_associativeArray[DataArrayKeyEnumeration::$plan_cost] = 
		$this->_CTaskObject->getBudget();
	}
	
	public function buildActualCost() {
		$this->_associativeArray[DataArrayKeyEnumeration::$act_cost] =
		$this->_CTaskObject->getActualCost($this->_CTaskObject->task_id, $this->_CTaskObject->getChild());
	}

	public function buildPercentage() {
		$this->_associativeArray[DataArrayKeyEnumeration::$percentage] =
		$this->_CTaskObject->getProgress();
	}
}
?>