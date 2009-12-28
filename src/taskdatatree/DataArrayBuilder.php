<?php
/**
 * this method is necessary to load at runtime and only if is needed the 
 * relative tasks.class.php file (if this is already loaded, the runtime
 * throw an error saying that some classes are alredy defined)
 * @param $class_name
 * @return void
 */
function __autoload($class_name){
	//print $class_name;
	require ("./modules/tasks/tasks.class.php");
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
		$this->_CTaskObject = &new CTask();
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

}
?>