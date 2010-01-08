<?php

/*
require_once dirname(__FILE__)."./classes/query.class.php";
require_once dirname(__FILE__)."./includes/main_functions.php";
require_once dirname(__FILE__)."./includes/db_connect.php";
require_once dirname(__FILE__)."./modules/tasks/tasks.class.php";
*/

/**
 *
 * This class has all requirements to be implemented like a singleton.
 * Because the user cannot generate multiple chart concurrently, specifying
 * a UserOption for each chart. Instead, when he want generate a chart, he go
 * to the relative chart and make his choices about the UserOption. Then when he
 * is satisfied, click "refresh" to generate the chart. For this reason the
 * UserOptionChoice may have a single instance that is mutable across the chart
 * generation.
 *
 * @author: Manuele Paolantonio
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */
class UserOptionsChoice {

	private static $instance;
	// preparing the query to fetch the root task
	var	$map;

	/**
	 * tasks' identifiers relative to the tasks that might be draw inside the chart
	 * @var array of integer
	 */
	var $tasksToShow;

	/**
	 * reference to the interface to retrieve some task information
	 * @var ITaskInformationRetriever retriever of task information
	 */
	var $taskInformationRetriever;

	private function __construct() {

		// setting only once the retriever
		$this->taskInformationRetriever = new DefaultTaskInformationRetriever();
	}
	
	
	
	private $array;
	/*
	private function __construct($array) {
	
		$this->array = $array;
	}*/
	
	
	// metodi che mostrano quali useroption ha selezionato l'utente
	
	function showTaskNameUserOption() {
		return isset($this->array[UserOptionEnumeration::$TaskNameUserOption]);
	}
	
	function showWBSTreeSpecification() {
		return isset($this->array[UserOptionEnumeration::$WBSTreeSpecification]);
	}
	
	function showLevelSpecificationUserOption() {
		return $this->array[UserOptionEnumeration::$LevelSpecificationUserOption];
	}
	
	function showCustomDimUserOption() {
		return isset($this->array[UserOptionEnumeration::$CustomDimUserOption]);
	}
	
	function showOpenInNewWindowUserOption() {
		return isset($this->array[UserOptionEnumeration::$OpenInNewWindowUserOption]);
	}
	
	function showPlannedDataUserOption() {
		return isset($this->array[UserOptionEnumeration::$PlannedDataUserOption]);
	}
	
	function showPlannedTimeFrameUserOption() {
		return isset($this->array[UserOptionEnumeration::$PlannedTimeFrameUserOption]);
	}
	
	function showResourcesUserOption() {
		return isset($this->array[UserOptionEnumeration::$ResourcesUserOption]);
	}
	
	function showActualTimeFrameUserOption() {
		return isset($this->array[UserOptionEnumeration::$ActualTimeFrameUserOption]);
	}
	
	function showActualDataUserOption() {
		return isset($this->array[UserOptionEnumeration::$ActualDataUserOption]);
	}
	
	function showAlertMarkUserOption() {
		return isset($this->array[UserOptionEnumeration::$AlertMarkUserOption]);
	}
	
	function showReplicateArrowUserOption() {
		return isset($this->array[UserOptionEnumeration::$ReplicateArrowUserOption]);
	}
	
	function showUseDifferentPatternForCrossingLinesUserOption() {
		return isset($this->array[UserOptionEnumeration::$UseDifferentPatternForCrossingLinesUserOption]);
	}
	
	function showEffortInformationUserOption() {
		return isset($this->array[UserOptionEnumeration::$EffortInformationUserOption]);
	}
	
	function showFinishToStartDependenciesUserOption() {
		return isset($this->array[UserOptionEnumeration::$FinishToStartDependenciesUserOption]);
	}
		
	function showCustomRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$CustomRangeUserOption]);
	}
	
	function showFromStartRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$FromStartRangeUserOption]);
	}
	
	function showToEndRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$ToEndRangeUserOption]);
	}
	
	function showTimeGapsUserOption() {
		return isset($this->array[UserOptionEnumeration::$TimeGapsUserOption]);
	}
	
	function showShowCompleteDiagramDependencies() {
		return isset($this->array[UserOptionEnumeration::$ShowCompleteDiagramDependencies]);
	}
	
	function showCriticalPathUserOption() {
		return isset($this->array[UserOptionEnumeration::$CriticalPathUserOption]);
	}
	
	function showMaxCriticalPathNumberUserOption() {
		return $this->array[UserOptionEnumeration::$MaxCriticalPathNumberUserOption];
	}
	
	function showTimeGrain() {
		return $this->array[UserOptionEnumeration::TimeGRainUserOption];
	}
	
	function showImageDimensionUserOption() {
		return isset($this->array[UserOptionEnumeration::$ImageDimensionUserOption]);
	}
	
	function showTimeRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$TimeRangeUserOption]);
	}
	
	
	public static function GetInstance() {
		if(!isset(UserOptionsChoice::$instance)) {
			UserOptionsChoice::$instance = new UserOptionsChoice();
		}
		return UserOptionsChoice::$instance;
	}

	/**
	 * This method search the task identifiers needed to generate the chart.
	 * The search criterion is based on the user wbs exploding/collapsing activity
	 * on the planned/actual view tab.
	 * @param integer $explodeLevel wbs level to explode the project plan
	 * @param array of integer $openedTasks tasks that user had chosed to explode
	 * @param unknown_type $closedTasks tasks that user had chosed to collapse
	 * @return UserOptionChoice reference to the userOptionChoice instance to chain a request
	 */
	public function retrieveDrawableTasks($explodeLevel, $openedTasks, $closedTasks) {

		$this->initializeForDrawableTasksResearch();

		$this->explodeWbsToLevel($explodeLevel);
		
		$this->tasksToShow = $this->getExplodedTasks();

		print "<br>Tasks after wbs explosion level: " . implode(",", $this->tasksToShow);
		
		//now I remove from the resulting array the task that are collapsed
		$closedTaskDistiller = new CloseArrayDistiller($closedTasks, $this->taskInformationRetriever);
		$closedTaskDistiller->distill();

		print "<br>Distiller had find this to close tasks: " . implode(",", $closedTaskDistiller->getToCloseTasks());
		print "<br>Distiller had find this to show tasks: " . implode(",", $closedTaskDistiller->getToShowTasks());

		$this->eraseClosedTasks($closedTaskDistiller->getToCloseTasks());
		
		print "<br>Tasks after deletion of collapsed tasks: " . implode(",", $this->tasksToShow);

		$this->appendDrawableTasks($closedTaskDistiller->getToShowTasks());
		
		print "<br>Tasks after adding distilled tasks: " . implode(",", $this->tasksToShow);

		foreach ($openedTasks as $opened_task) {
			$this->appendDrawableTasks($this->taskInformationRetriever->getChildren($opened_task));
		}
		
		
		print "<br>Tasks after last append action: " . implode(",", $this->tasksToShow);
		
		return $this;
	}
	
	/**
	 * get the tasks set to be draw
	 * @return array of integer
	 */
	public function getDrawableTasks() {
		return $this->tasksToShow;
	}

	/**
	 * initiliaze the map
	 * @return void
	 */
	private function initializeForDrawableTasksResearch() {
		unset($this->map);
		$this->map = array();
		
		unset($this->tasksToShow);
	}

	/**
	 * append a list of tasks identifier to the set of tasks that will be draw inside a chart
	 * @param array of integer $drawableTasks
	 * @return void
	 */
	private function appendDrawableTasks($drawableTasks) {
		foreach ($drawableTasks as $task) {
			if(!in_array($task, $this->tasksToShow)){
				$this->tasksToShow[] = $task;
			}
		}
	}

	/**
	 * erase from the current tasks identifiers set, those which were collapsed
	 * @param $closedTasks identifiers set of tasks that have to be collapsed
	 * @return void
	 */
	private function eraseClosedTasks($closedTasks){
		$eraseIndices = array();
		$result = array();
		for ($i = 0; $i < count($this->tasksToShow); $i++) {
			if(!in_array($this->tasksToShow[$i], $closedTasks)) {
				//array_splice($this->tasksToShow, $i, $i);
				//unset($this->tasksToShow[$i]);
				$eraseIndices[] = $i;
			}
		}
		
		foreach($eraseIndices as $eraseIndex) {
			$result[] = $this->tasksToShow[$eraseIndex];
		}
		
		$this->tasksToShow = $result;
	}

	/**
	 * get the exploded tasks relative of the map entries
	 * @return array of integer identifiers of the exploded tasks
	 */
	private function getExplodedTasks() {
		$result = array();
		foreach ($this->map as $level => $children) {
			foreach ($children as $child) {
				$result[] = $child;
			}
		}
		return $result;
	}

	/**
	 * populate the map instance field with the tasks foreach level
	 * @param integer $explodeLevel
	 * @return void
	 */
	private function explodeWbsToLevel($explodeLevel) {

		// getting the tasks which belong to the first level (aka the root-node's children)
		$this->map[0] =	$this->getRootChildren();

		// start from one because the children of the root have been fetched in the step before
		for($level = 1; $level < $explodeLevel; $level++) {
			$this->map[$level] = array();

			// getting a reference to a array, thus I work directly to the original array $this->map[$level]
			$currentChildren =& $this->map[$level];

			foreach($this->map[$level - 1] as $parentTask) {
				foreach($this->taskInformationRetriever->getChildren($parentTask) as $child) {
					$currentChildren[] = $child;
				}
			}
		}
	}

	/**
	 * get the tasks of level one, aka the children of the root node.
	 * @return array of integer the identifiers of root's children
	 */
	private function getRootChildren() {
		$firstLevelTask_ids = $this->RetrieveRootTaskIdentifier();
		$result = array();
		foreach($firstLevelTask_ids as $firstLevelTask) {
			$result[] = $firstLevelTask["task_id"];
		}
		return $result;
	}

	/**
	 * Method that query the db for fetch the first level task identifiers
	 * @return task identifier
	 */
	private function RetrieveRootTaskIdentifier() {
		$sql = 'SELECT task_id FROM tasks where task_id = task_parent';
		$tasks = db_loadList($sql);

		return $tasks;
		//		$query = new DBQuery();
		//		$query->addQuery("task_id");
		//		$query->addTable("tasks");
		//		$query->addWhere("task_id = task_parent");
		//		return $query->loadResult();
	}
}

interface ITaskInformationRetriever {
	public function isLeaf($task_id);
	public function getChildren($task_id);
	public function getDeepChildren($task_id);
}

// this class need to refactor because the meaning is quite the same of the class DataArrayBuilder
class DefaultTaskInformationRetriever implements ITaskInformationRetriever {
	private function loadTask($task_id) {
		$tmpTask = new CTask();
		$tmpTask->load($task_id);
		return $tmpTask;
	}

	public function isLeaf($task_id) {
		return $this->loadTask($task_id)->isLeaf();
	}

	public function getChildren($task_id) {
		return $this->loadTask($task_id)->getChildren();
	}

	public function getDeepChildren($task_id) {
		return $this->loadTask($task_id)->getDeepChildren();
	}
}

class CloseArrayDistiller {
	var $closed_tasks;
	var $map = array();
	var $toClose = array();
	var $taskInformationRetriever;

	/**
	 * constructor
	 * @param $closed_tasks array of closed task to distill info from
	 * @param ITaskInformationRetriever $taskInformationRetriever
	 */
	public function __construct($closed_tasks, $taskInformationRetriever) {
		$this->closed_tasks = $closed_tasks;
		$this->taskInformationRetriever = $taskInformationRetriever;
	}

	/**
	 * distill the closed array to be able to discover which tasks are opened and which to show
	 * @return void
	 */
	public function distill() {
		foreach ($this->closed_tasks as $closed_task) {
			if ($this->taskInformationRetriever->isLeaf($closed_task) || $this->taskIsDeepChild($closed_task)) {
				$this->appendToCloseArray($closed_task);
			}
			else {
				$this->createMapEntry($closed_task);
				//$this->clearMap();
			}
		}
		$this->clearMap();
	}

	private function taskIsDeepChild($closed_task) {
		foreach ($this->map as $parent => $children) {
			if(in_array($closed_task, $children)) {
				return true;
			}
		}
		return false;
	}

	private function appendToCloseArray($closed_task) {
		$this->toClose[] = $closed_task;
	}

	private function createMapEntry($closed_task) {
		$this->map[$closed_task] = $this->taskInformationRetriever->getDeepChildren($closed_task);
	}

	private function clearMap() {
		$toEraseArray = array();
		foreach ($this->map as $parent => $children) {
			foreach ($this->map as $innerParent => $innerChildren) {
				if ($parent == $innerParent) {
					continue;
				}

				if (in_array($parent, $innerChildren)) {
					$toEraseArray[] = $parent;
					break;
				}
			}
		}

		foreach ($toEraseArray as $eraseTask) {
			unset ($his->map[$eraseTask]);
			$this->toClose[] = $eraseTask;
		}

		foreach ($this->map as $parent => $children) {
			if (in_array($parent, $this->toClose)) {
				unset($this->map[$parent]);
			}
		}
	}

	public function getToCloseTasks() {
		return $this->toClose;
	}

	public function getToShowTasks() {
		$result = array();
		foreach ($this->map as $parent => $children) {
			$result[] = $parent;
		}
		return $result;
	}

}

class Queue {
	var $array = array();
	var $headIndex = 0;

	/**
	 * return the state of the queue: contains or not some elements.
	 * @return boolean
	 */
	public function isEmpty() {
		return count($this->array) <= $this->headIndex;
	}

	public function clear() {
		unset($this->array);
		$this->array = array();
		$this->headIndex = 0;
	}

	/**
	 * Get the top of the queue and erase that value from the queue
	 * @return first object on the queue
	 */
	public function Dequeue() {
		if($this->headIndex >= count($this->array)) {
			die("Impossible to fetch a empty object");
		}

		$value = $this->array[$this->headIndex];
		$this->headIndex++;
		return $value;
	}

	/**
	 * enque values
	 * @param one or more values to enque inside the queue
	 * @return void
	 */
	public function Enque($values) {
		if(is_array($values)) {
			foreach($values as $value) {
				$this->addSingleElement($value);
			}
		}
		else {
			$this->addSingleElement($values);
		}
	}

	private function addSingleElement($element) {
		$this->array[] = $element;
	}

}

?>