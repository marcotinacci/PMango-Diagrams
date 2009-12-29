<?php
require_once 'DataArrayBuilder.php';
require_once 'DataArrayDirector.php';
require_once 'DataArrayKeyEnumeration.php';
/**
 * Questa classe organizza le informazioni da incapsulare nei nodi della struttura,
 * Per ogni task.
 *
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class Task{

	private $data = array();

	/**
	 * private constructor to preevent client from creating a task
	 * simply calling the constructor. The object that that task obtained is not
	 * correct because the array with the info is empty
	 * @return unknown_type
	 */
	private function __construct() { }

	/**
	 * Static method that create a correct task, querying a db
	 * @param int $task_id
	 * @return Task a correct task to be handle
	 */
	public static function MakeTask($task_id) {
		// creating the object that build the associative array
		$dataArrayBuilder =& new DataArrayBuilder($task_id);
		$dataArrayDirector =& new DataArrayDirector($dataArrayBuilder);
		$dataArrayDirector->composeArray();

		// resulting task
		$task = & new Task();
		$task->data = $dataArrayBuilder->getAssociativeArray();

		return $task;
	}

	public function setData($data){
		$this->data = $data;
	}

	public function getAll(){
		return $this->data;
	}

	public function getWBSId(){
		// TODO: replace every string coded directly with the relative
		// static entry in DataArrayKeyEnumeration class
		return $this->data[DataArrayKeyEnumeration::$wbsIdentifier];
	}

	public function getTaskName(){
		return $this->data[DataArrayKeyEnumeration::$name];
	}

	public function getEffort(){
		return $this->data[DataArrayKeyEnumeration::$plan_effort];
	}

	/*
	 * Si pensa alla strutturazione dei dati delle risorse assegnate al task,
	 * come un vettore di vettori interno a $data.
	 * Avremo il vettore delle risorse assegnate in $data["assigned_to_task"].
	 * $data["assigned_to_task"][0] è il vettore di informazioni (personal_effort, name, role)
	 * riguardante la prima risorsa assegnata al task.
	 */
	public function getResources(){
		return $this->data[DataArrayKeyEnumeration::$assigned_to_task];
	}

	public function getPlannedData(){
		$planned_data = array ("duration"=>$this->data[DataArrayKeyEnumeration::$plan_duration], "effort"=>$this->data[DataArrayKeyEnumeration::$plan_effort], "cost"=>$this->data[DataArrayKeyEnumeration::$plan_cost]);
		return $planned_data;
	}

	public function getPlannedTimeFrame(){
		$planned_time_frame = array("start_date"=>$this->data[DataArrayKeyEnumeration::$start_date], "finish_date"=>$this->data[DataArrayKeyEnumeration::$finish_date]);
		return $planned_time_frame;
	}

	public function getActualData(){
		$actual_data = array ("duration"=>$this->data[DataArrayKeyEnumeration::$act_duration], "effort"=>$this->data[DataArrayKeyEnumeration::$act_effort], "cost"=>$this->data[DataArrayKeyEnumeration::$act_cost]);
		return $actual_data;
	}

	public function getLevel(){
		return $this->data[DataArrayKeyEnumeration::$level];
	}

	private function calculatePercentage(){
		return $this->data[DataArrayKeyEnumeration::$percentage];
	}
}

// the following lines are for testing...as soon as possible I move them
// into a more appropriate place...
global $AppUI;
$tasks_closed = $AppUI->getState("tasks_closed");
$tasks_opened = $AppUI->getState("tasks_opened");
// in $open_task there is a ID of a task, for each open task
foreach($tasks_opened as $open_task) {
	$task = Task::MakeTask($open_task);
	print "WBS identifier of task with id = " . $open_task . " is equals to " .
	$task->getWBSId() . "<br>";
}

print "note that task ids = {" . implode(",", $tasks_opened) . "} are the task ids" .
" of the tasks that are exploded " . "into the view tab that you have modified";

?>