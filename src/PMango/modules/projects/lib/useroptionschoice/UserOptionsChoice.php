<?php

/*
 require_once dirname(__FILE__)."./classes/query.class.php";
 require_once dirname(__FILE__)."./includes/main_functions.php";
 require_once dirname(__FILE__)."./includes/db_connect.php";
 require_once dirname(__FILE__)."./modules/tasks/tasks.class.php";
 */

require_once dirname(__FILE__)."/UserOptionEnumeration.php";
require_once dirname(__FILE__)."/TimeRange.php";
require_once dirname(__FILE__)."/TimeGrainEnum.php";
require_once dirname(__FILE__)."/ImageDimension.php";
require_once dirname(__FILE__)."/../chartgenerator/ChartTypesEnum.php";

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

	private static $instances;
	private $instanceName = "";
	private $alreadyRefreshed = false;
	public function hasBeenRefreshed()
	{
		return $this->alreadyRefreshed;
	}
	public function setRefreshed($value)
	{
		$this->alreadyRefreshed = $value;
	}
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

	private function __construct($Name) {

		$this->instanceName = $Name;
		// setting only once the retriever
		$this->taskInformationRetriever = new DefaultTaskInformationRetriever();
	}

	//array of choices
	private $array;

	public function setFromArray($array){
		$this->array = $array;
	}

	/**
	 * Put all fields (in the form UserOptionEnumerationKey_Value) of the private variable $array
	 * into a string separated by "|".
	 * @return String
	 */
	public function saveToString(){
		$pieces = array();
		
		$pieces[]=isset($this->array[UserOptionEnumeration::$TaskNameUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$OpenInNewWindowUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$PlannedDataUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$PlannedTimeFrameUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$ResourcesUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$ActualTimeFrameUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$ActualDataUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$AlertMarkUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$ReplicateArrowUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$UseDifferentPatternForCrossingLinesUserOption])?1:0; //bool
		$pieces[]=$this->array[UserOptionEnumeration::$TimeRangeUserOption]; //Enum
		$pieces[]=$this->array[UserOptionEnumeration::$TimeGrainUserOption]; //Enum

		$pieces[]=$this->array[UserOptionEnumeration::$ImageDimensionsUserOption]; //Enum
		$pieces[]=$this->array[UserOptionEnumeration::$DefaultWidthUserOption]; //int
		$pieces[]=$this->array[UserOptionEnumeration::$DefaultHeightUserOption]; //int
		$pieces[]=$this->array[UserOptionEnumeration::$CustomWidthUserOption]; //int
		$pieces[]=$this->array[UserOptionEnumeration::$CustomHeightUserOption]; //int
		$pieces[]=$this->array[UserOptionEnumeration::$FitInWindowWidthUserOption]; //int
		$pieces[]=$this->array[UserOptionEnumeration::$FitInWindowHeightUserOption]; //int

		$pieces[]=$this->array[UserOptionEnumeration::$TodayDateUserOption]; //date
		$pieces[]=$this->array[UserOptionEnumeration::$CustomStartDateUserOption]; //date
		$pieces[]=$this->array[UserOptionEnumeration::$CustomEndDateUserOption]; //date

		// Gantt

		$pieces[]=isset($this->array[UserOptionEnumeration::$EffortInformationUserOption])?1:0; //bool
		$pieces[]=isset($this->array[UserOptionEnumeration::$FinishToStartDependenciesUserOption])?1:0; //bool



		// Task Network

		$pieces[]=$this->array[UserOptionEnumeration::$TimeGapsUserOption];
		$pieces[]=isset($this->array[UserOptionEnumeration::$ShowCompleteDiagramDependencies])?1:0;
		$pieces[]=isset($this->array[UserOptionEnumeration::$CriticalPathUserOption])?1:0;
		$pieces[]=$this->array[UserOptionEnumeration::$SelectedCriticalPathNumberUserOption];
		
		return implode("|", $pieces);
	}

	/**
	 * Set the private variable $array from a string that has all the fields of interest
	 * (in the form UserOptionEnumerationKey_Value) separated by "|".
	 * @param $str String
	 */
	public function loadFromString($str){
		$this->array = array();
		$pieces = explode("|",$str);
		
		
		if($pieces[0]==1)
			$this->array[UserOptionEnumeration::$TaskNameUserOption]=true;
		if($pieces[1]==1)
			$this->array[UserOptionEnumeration::$OpenInNewWindowUserOption]=true;
		if($pieces[2]==1)
			$this->array[UserOptionEnumeration::$PlannedDataUserOption]=true;
		if($pieces[3]==1)
			$this->array[UserOptionEnumeration::$PlannedTimeFrameUserOption]=true; //bool
		if($pieces[4]==1)
			$this->array[UserOptionEnumeration::$ResourcesUserOption]=true; //bool
		if($pieces[5]==1)
			$this->array[UserOptionEnumeration::$ActualTimeFrameUserOption]=true; //bool
		if($pieces[6]==1)
			$this->array[UserOptionEnumeration::$ActualDataUserOption]=true; //bool
		if($pieces[7]==1)
			$this->array[UserOptionEnumeration::$AlertMarkUserOption]=true; //bool
		if($pieces[8]==1)
			$this->array[UserOptionEnumeration::$ReplicateArrowUserOption]=true; //bool
		if($pieces[9]==1)
			$this->array[UserOptionEnumeration::$UseDifferentPatternForCrossingLinesUserOption]=true; //bool
		
		$this->array[UserOptionEnumeration::$TimeRangeUserOption]=$pieces[10]; //Enum
		$this->array[UserOptionEnumeration::$TimeGrainUserOption]=$pieces[11]; //Enum

		$this->array[UserOptionEnumeration::$ImageDimensionsUserOption]=$pieces[12]; //Enum
		$this->array[UserOptionEnumeration::$DefaultWidthUserOption]=$pieces[13]; //int
		$this->array[UserOptionEnumeration::$DefaultHeightUserOption]=$pieces[14]; //int
		$this->array[UserOptionEnumeration::$CustomWidthUserOption]=$pieces[15]; //int
		$this->array[UserOptionEnumeration::$CustomHeightUserOption]=$pieces[16]; //int
		$this->array[UserOptionEnumeration::$FitInWindowWidthUserOption]=$pieces[17]; //int
		$this->array[UserOptionEnumeration::$FitInWindowHeightUserOption]=$pieces[18]; //int

		$this->array[UserOptionEnumeration::$TodayDateUserOption]=$pieces[19]; //date
		$this->array[UserOptionEnumeration::$CustomStartDateUserOption]=$pieces[20]; //date
		$this->array[UserOptionEnumeration::$CustomEndDateUserOption]=$pieces[21]; //date

		// Gantt

		if($pieces[22]==1)
			$this->array[UserOptionEnumeration::$EffortInformationUserOption]=true; //bool
		if($pieces[23]==1)
			$this->array[UserOptionEnumeration::$FinishToStartDependenciesUserOption]=true; //bool



		// Task Network

		$this->array[UserOptionEnumeration::$TimeGapsUserOption]=$pieces[24];
		if($pieces[25]==1)
			$this->array[UserOptionEnumeration::$ShowCompleteDiagramDependencies]=true; //bool
		if($pieces[26]==1)
			$this->array[UserOptionEnumeration::$CriticalPathUserOption]=true; //bool
		$this->array[UserOptionEnumeration::$SelectedCriticalPathNumberUserOption]=$pieces[27];
	}

	// metodi che mostrano quali useroption ha selezionato l'utente

	//Common shows

	function showTaskNameUserOption() {
		return isset($this->array[UserOptionEnumeration::$TaskNameUserOption]);
	}

	//return type of Image Dimension requested by the user: defaultDimension default
	function getImageDimensionUserOption() {
		if(isset($this->array[UserOptionEnumeration::$ImageDimensionsUserOption]))
		return $this->array[UserOptionEnumeration::$ImageDimensionsUserOption];
		else
		return ImageDimension::$FitInWindowDimUserOption;
	}

	function getCustomDimValues(){
		$custom_dim = array("width"=>$this->array[UserOptionEnumeration::$CustomWidthUserOption],
							"height"=>$this->array[UserOptionEnumeration::$CustomHeightUserOption]);
		return $custom_dim;
	}

	function getDefaultDimValues(){
		global $dPconfig;
		$w = isset($dPconfig['chart_default_width'])?$dPconfig['chart_default_width']:600;
		$def_dim = array("width"=>$w,
							"height"=>$this->array[UserOptionEnumeration::$DefaultHeightUserOption]);
		return $def_dim;
	}

	function getFitInWindowDimValues(){
		$def_dim = array("width"=>$this->array[UserOptionEnumeration::$FitInWindowWidthUserOption],
							"height"=>$this->array[UserOptionEnumeration::$FitInWindowHeightUserOption]);
		return $def_dim;
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

	//Gantt shows

	function showEffortInformationUserOption() {
		return isset($this->array[UserOptionEnumeration::$EffortInformationUserOption]);
	}

	function showFinishToStartDependenciesUserOption() {
		return isset($this->array[UserOptionEnumeration::$FinishToStartDependenciesUserOption]);
	}

	//return type of time grain requested by the user: monthly default.
	function getTimeGrainUserOption() {
		return $this->array[UserOptionEnumeration::$TimeGrainUserOption];
	}

	//returns the type of visualization range requested by the user: FromNowToEnd default
	function getTimeRangeUserOption() {
		return $this->array[UserOptionEnumeration::$TimeRangeUserOption];
	}

	function getCustomRangeValues()
	{
		if(isset($this->array[UserOptionEnumeration::$CustomStartDateUserOption]))
			$start = $this->array[UserOptionEnumeration::$CustomStartDateUserOption];
		else
			$start = "";
		if(isset($this->array[UserOptionEnumeration::$CustomEndDateUserOption]))
			$end = $this->array[UserOptionEnumeration::$CustomEndDateUserOption];
		else
			$end = "";
		$custom_range = array("start"=>$start,
							"end"=>$end,
							"today"=>$this->array[UserOptionEnumeration::$TodayDateUserOption]
		);
		return $custom_range;
	}

	function showCustomRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$CustomRangeUserOption]);
	}

	function showFromStartRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$FromStartToNowRangeUserOption]);
	}

	function showToEndRangeUserOption() {
		return isset($this->array[UserOptionEnumeration::$FromNowToEndRangeUserOption]);
	}

	//Task Network shows

	function showTimeGapsUserOption() {
		return isset($this->array[UserOptionEnumeration::$TimeGapsUserOption]);
	}

	function showShowCompleteDiagramDependencies() {
		return isset($this->array[UserOptionEnumeration::$ShowCompleteDiagramDependencies]);
	}

	function showCriticalPathUserOption() {
		return isset($this->array[UserOptionEnumeration::$CriticalPathUserOption]);
	}

	function getSelectedCriticalPathNumberUserOption() {
		return $this->array[UserOptionEnumeration::$SelectedCriticalPathNumberUserOption];
	}


	public static function &GetInstance($instanceName="default") {
		if(!isset(UserOptionsChoice::$instances["uoc_$instanceName"]) && isset($_SESSION["uoc_$instanceName"]))
		{
			//print "instance 'uoc_$instanceName' loaded from session";
			UserOptionsChoice::$instances["uoc_$instanceName"] = unserialize($_SESSION["uoc_$instanceName"]);
			UserOptionsChoice::$instances["uoc_$instanceName"]->setRefreshed(false);
		}
		if(!isset(UserOptionsChoice::$instances["uoc_$instanceName"])) {
			//print "instance 'uoc_$instanceName' created new";
			UserOptionsChoice::$instances["uoc_$instanceName"] = new UserOptionsChoice($instanceName);
		}
		//CONTROLLO SE DEVE ESSERE AGGIORNATA DA UN QUALCHE FORM
		if(!UserOptionsChoice::$instances["uoc_$instanceName"]->hasBeenRefreshed() && isset($_GET["REFRESH_UOC_$instanceName"]))
		{
			//print "instance 'uoc_$instanceName' setted from get";
			UserOptionsChoice::$instances["uoc_$instanceName"]->setRefreshed(true);
			UserOptionsChoice::$instances["uoc_$instanceName"]->setFromArray($_GET);
		}
		else if(!UserOptionsChoice::$instances["uoc_$instanceName"]->hasBeenRefreshed() && isset($_POST["REFRESH_UOC_$instanceName"]))
		{
			//print "instance 'uoc_$instanceName' setted from post";
			UserOptionsChoice::$instances["uoc_$instanceName"]->setRefreshed(true);
			UserOptionsChoice::$instances["uoc_$instanceName"]->setFromArray($_POST);
		}
		return UserOptionsChoice::$instances["uoc_$instanceName"];
	}
	
	public function saveOnSession()
	{
		$instanceName = $this->instanceName;
		//print "instance 'uoc_$instanceName' saved to session";
		$_SESSION["uoc_$instanceName"] = serialize(UserOptionsChoice::$instances["uoc_$instanceName"]);
	}
	
	public function getRefreshHiddenField()
	{
		$instanceName = $this->instanceName;
		return "<input type='hidden' name='REFRESH_UOC_$instanceName' value='1'>";
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

		//		print "<br>Tasks after wbs explosion level: " . implode(",", $this->tasksToShow);

		//now I remove from the resulting array the task that are collapsed
		$closedTaskDistiller = new CloseArrayDistiller($closedTasks, $this->taskInformationRetriever);
		$closedTaskDistiller->distill();

		//		print "<br>Distiller had find this to close tasks: " . implode(",", $closedTaskDistiller->getToCloseTasks());
		//		print "<br>Distiller had find this to show tasks: " . implode(",", $closedTaskDistiller->getToShowTasks());

		$this->eraseClosedTasks($closedTaskDistiller->getToCloseTasks());

		//		print "<br>Tasks after deletion of collapsed tasks: " . implode(",", $this->tasksToShow);

		$this->appendDrawableTasks($closedTaskDistiller->getToShowTasks());

		//		print "<br>Tasks after adding distilled tasks: " . implode(",", $this->tasksToShow);

		foreach ($openedTasks as $opened_task) {
			$this->appendDrawableTasks($this->taskInformationRetriever->getChildren($opened_task));
		}


		//		print "<br>Tasks after last append action: " . implode(",", $this->tasksToShow);

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
		$toAppend = array();
		foreach ($this->map as $parent => $children) {
			$isDeepChildren = false;
			foreach ($this->getToCloseTasks() as $currentCloseTask) {
				if(in_array($parent, $this->taskInformationRetriever->getDeepChildren($currentCloseTask))) {
					print "<br>il task " . $parent . " is deep child of " . $currentCloseTask;
					$isDeepChildren = true;
					break;
				}
			}
			if(!$isDeepChildren) {
				$toAppend[] = $parent;
			}
		}

		return $toAppend;
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