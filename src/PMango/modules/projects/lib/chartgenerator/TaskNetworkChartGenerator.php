<?php
require_once dirname(__FILE__) . "/ChartGenerator.php";
require_once dirname(__FILE__) . "/CriticalPathDomainObject.php";
require_once dirname(__FILE__) . "/PointInfo.php";
require_once dirname(__FILE__) . "/../taskdatatree/TaskData.php";
require_once dirname(__FILE__) . "/../gifarea/GifTaskBox.php";
require_once dirname(__FILE__) . "/../gifarea/DrawingHelper.php";
require_once dirname(__FILE__) . "/DependencyLineInfo.php";
require_once dirname(__FILE__) . "/ChartTypesEnum.php";

class TaskboxDrawInformation {
	var $dependency;
	var $pointInfo;
}

class TaskNetworkChartGenerator extends ChartGenerator {

	/**
	 * Maps to retrieve the objects which model one critical path respectively.
	 * The key of the map are a string representation of the chain of task, from the
	 * start milestone, to the end
	 * @var map of (string, CriticalPathDomainObject)
	 */
	private $criticalPathTable = array();

	/**
	 * Maps of the already drawed task. the value are the DependencyLine object, which
	 * abstract the information relative to the line draw activity.
	 * @var map of (task_id, DependencyLine)
	 */
	private $drawedTasksMap = array();

	/**
	 * array of all the dependency line that are candidate to be printed to
	 * the chart
	 * @var array of DependencyLineInfo
	 */
	private $dependencyLinesArray = array();

	/**
	 * the dots inside the pattern of a line dependency arrow.
	 * This variable permits to distinguish between dependency relation lines
	 * between exiting from two different tasks
	 *
	 * @var integer
	 */
	private $dotsDependencyLineCount = 1;

	/**
	 * @var PointInfo
	 */
	private $vertical;

	/**
	 * Costruttore
	 */
	public function __construct(){
		parent::__construct();
	}

	public function retrieveUserOptionChoice() {
		// retrieve this object from session??
		return UserOptionsChoice::GetInstance(ChartTypesEnum::$TaskNetwork);
		//		->retrieveDrawableTasks(
		//		$AppUI->getState('ExplodeTasks', '1'),
		//		$AppUI->getState("tasks_opened"),
		//		$AppUI->getState("tasks_closed"));
	}

	public function generateChart()	{
		$tdt = $this->tdtGenerator->generateTaskDataTree();
		$root = $this->buildDependencyTree($tdt);

		$userOptionChoice = $this->retrieveUserOptionChoice();

		AbstractTaskDataDrawer::$width = GifTaskBox::getTaskBoxesBestWidth(
			$this->tdtGenerator->generateTaskDataTree(),
			$userOptionChoice, 12, FF_VERDANA);

		AbstractTaskDataDrawer::$singleRowHeight = 20;
			
		AbstractTaskDataDrawer::$userOptionChoice = $userOptionChoice;

		//print "<br>the best width for taskboxes is " . AbstractTaskDataDrawer::$width;

		// building the canvas
		$this->chart = new GifImage(2000, 800);

		// start the generation from (5, 5) point
		$this->vertical = 5;
		$this->internalGenerateChart($root, new CriticalPathDomainObject(), 5);
		
		//print("ho finito la ricorsione!");
		
		$this->printTaskBoxes();

		$this->drawDependencyLine();
		
		$this->chart->draw();
	}
	
	private function printTaskBoxes() {
		//DrawingHelper::debug("Taskboxes");
		
		foreach ($this->taskboxes as $task_id => $taskbox) {
//			$gifTaskbox = new GifTaskBox($this->chart, 
//				$taskbox->pointInfo->horizontal, 
//				$taskbox->pointInfo->vertical, 
//				AbstractTaskDataDrawer::$width, 
//				AbstractTaskDataDrawer::$singleRowHeight, 
//				$taskbox->dependency->getNeededTask(), 
//				AbstractTaskDataDrawer::$userOptionChoice);
//
//			$gifTaskbox->drawOn();
			
			$taskbox->dependency->getDrawer()->drawOn($this->chart, $taskbox->pointInfo);
	
			DrawingHelper::debug("Drawed " . $task_id . " " . 
				$taskbox->dependency->getNeededTask()->getInfo()->getTaskID());
		}		
	}
	
	

	/**
	 * internal method that takes as parameter the root dependency node.
	 * This method is recursion ready
	 * @param IDependency $dependency
	 * @param CriticalPathDomainObject $criticalPathDomainObject
	 * @param int $horizontal
	 * @return void
	 */
	private function internalGenerateChart($dependency, $criticalPathDomainObject, $horizontal) {
		//var_dump($dependency);
		//print "<br>Drawing " . $dependency->getNeededTask()->getInfo()->getTaskID();
		if ($dependency->hasDependentTasks()) {

			// freeze the current vertical quote
			$freezeVertical = $this->vertical;
			//print "<br>freezed vertical $freezeVertical";

			//$entryPointArray = array();
			
			foreach ($dependency->getDependentTasks() as $dependentTask) {

				//print "<br>drawing dependant task " . $dependantTaskId;

				/*
				 * cloning the critical path domain object to duplicate the info
				 * to have one object foreach fork of the path
				 */
				$cpdoClone = $this->buildNewCriticalPathDomainObjectFrom(
					$criticalPathDomainObject,
					$dependency->getNeededTask()->getInfo(),
					$dependentTask->getNeededTask()->getInfo());

				/*
				 * Move the recursive call before the above conditional logic, keep the result point and manage
				 * the dependency line info respect to that point
				 */
				$dependentPointInfo = $this->internalGenerateChart(
					$dependentTask, 
					$cpdoClone, 
					$horizontal + 
						$this->getGapBetweenHorizonalTasks() + 
						AbstractTaskDataDrawer::$width);

				//$dependantTaskId =	$dependentTask->getNeededTask()->getInfo()->getTaskID();
				
				//if(!$this->isDependencyAlreadyConsidered($d))
				
//				if (array_key_exists($dependantTaskId, $this->drawedTasksMap)) {
//					$existingDependencyLineInfo =& $this->drawedTasksMap[$dependantTaskId];
//					print "$dependantTaskId already exists in the drawed task map";
//
//					$existingDependencyLineInfo->horizontal -= $this->getHorizontalGapForExistingDependency();
//					$this->appendDependencyLine(clone $existingDependencyLineInfo);
//				} else {
//					print "$dependantTaskId not exists into drawed task map";
//					$drawer = $dependentTask->getDrawer();
//
//					print "<br> drawer found for " . $dependantTaskId;
//					$entryPoint = $drawer->computeEntryPoint(new PointInfo($horizontal, $this->vertical));
//
//					// the following method draw a line only if is necessary in presence of composed task
//					//$entryPoint = $this->connectEntryPointToCurrentVertical($entryPoint);
//
//					$entryPointArray[$dependantTaskId] = $entryPoint;
//					print " now reach this point";
//
//					$this->drawedTasksMap[$dependantTaskId] = $dependentTask;
//				}

			}
				
//			if (!($dependency->getNeededTask() instanceof StartMilestoneDependencyProxy)) {
//
//				$freezeVertical += $this->calculateSpan($dependency);
//
//				$exitPoint = $dependency->getDrawer()->computeExitPoint(new PointInfo($horizontal, $freezeVertical));
//				foreach($entryPointArray as $task_id => $entryPoint) {
//					print "<br>" . $dependency->getNeededTask()->getInfo()->getTaskID() .
//				" dependency is start milestone? " . $dependency->neededTaskIsStartMilestone() . " / is end milestone? " . $dependency->neededTaskIsEndMilestone();
//					if ($dependency->getNeededTask() instanceof StartMilestoneDependencyProxy) {
//						print "<br>needed task is a proxy: " . $dependency->getNeededTask()->getInfo()->pippo;
//
//					}
//					$dependencyLine = $this->buildDependencyLine($dependency->getNeededTask()->getInfo()->getTaskID(), $task_id, $exitPoint, $entryPoint);
//				}
//
//
//					
//				$dependency->getDrawer()->drawOn($this->getChart(), new PointInfo($horizontal, $freezeVertical));
//			}
			
			$dependencyTaskId = $dependency->getNeededTask()->getInfo()->getTaskID();

			if(!$this->isDependencyAlreadyConsidered($dependencyTaskId) &&
				!($dependency->getNeededTask() instanceof StartMilestoneDependencyProxy)) {

				$freezeVertical += $this->calculateSpan($dependency, $freezeVertical);
				
				$this->appendTaskBox($dependency, new PointInfo($horizontal, $freezeVertical));
				
				$this->vertical += $dependency->getDrawer()->computeHeight() + 
					$this->getGapBetweenVerticalTasks();
				
			}
		
			$neededTaskBoxDrawInformation = $this->taskboxes[$dependencyTaskId];
			
			// generating the dependency
			foreach ($dependency->_dependencyDescriptors as 
				$dependentTaskId => $dependencyDescriptors) {

				DrawingHelper::debug("analizing dependency descriptors for " . 
					$dependentTaskId);
						
				$dependentTaskBoxDrawInformation = $this->taskboxes[$dependentTaskId];

				foreach ($dependencyDescriptors as $dependencyDescriptor) {
					$dependencyLineInfo = new DependencyLineInfo();
					$dependencyLineInfo->neededTaskboxDrawInformation = $neededTaskBoxDrawInformation;
					$dependencyLineInfo->dependentTaskboxDrawInformation = $dependentTaskBoxDrawInformation;
					$dependencyLineInfo->dependencyDescriptor = $dependencyDescriptor;
					
					$this->appendDependencyLineInfo($dependencyLineInfo);
				}
			}
			
			return $this->taskboxes[$dependencyTaskId]->pointInfo;
		}
		else {
			$this->appendCriticalPathDomainObject($criticalPathDomainObject, $dependency);

			$dependencyTaskId = $dependency->getNeededTask()->getInfo()->getTaskID();
			//if (!array_key_exists($dependency->getNeededTask()->getInfo()->getTaskID(), $this->drawedTasksMap)) {
			if(!$this->isDependencyAlreadyConsidered($dependencyTaskId)) {		
				//print "<br>printing the taskbox for " . $dependency->getNeededTask()->getInfo()->getTaskID();
				DrawingHelper::debug("Added a terminal task: " . $dependencyTaskId);
				$drawPointInfo = new PointInfo($horizontal, $this->vertical);
				DrawingHelper::debug("pointInfo: " . $drawPointInfo);
				$this->appendTaskBox($dependency, $drawPointInfo);
				
				//print "<br>Task box drawed for " . $dependency->getNeededTask()->getInfo()->getTaskID();

				$taskDrawer = $dependency->getDrawer();
				//$taskDrawer->drawOn($this->getChart(), new PointInfo($horizontal, $this->vertical));
				
				// the last taskbox put a empty space, but he haven't the information necessary to know that he
				// is the last and not put the separator gap
				$this->vertical += $taskDrawer->computeHeight() + $this->getGapBetweenVerticalTasks();
				
				// is right to return the new vertical??
				return $drawPointInfo;
			}
		}
	}
	
	public function appendDependencyLineInfo($dependencyLineInfo) {
		$key = $dependencyLineInfo->dependentTaskboxDrawInformation->
			dependency->getNeededTask()->getInfo()->getTaskID();
		
		if(!array_key_exists($key, $this->dependencyLinesArray)) {
			$this->dependencyLinesArray[$key] = array();
			$this->dependencyLinesArray[$key][TaskLevelPositionEnum::$starting] = array();
			$this->dependencyLinesArray[$key][TaskLevelPositionEnum::$inner] = array();
		}
		
		$dependentEntryPosition = $dependencyLineInfo->dependencyDescriptor->
			dependentTaskPositionEnum;

		if(!array_key_exists($dependentEntryPosition, 
			$this->dependencyLinesArray[$key])) {
			DrawingHelper::debug("Impossible to have exit point for a dependent entry logic!");
		}
		
		$this->dependencyLinesArray[$key][$dependentEntryPosition][] = $dependencyLineInfo;
		
	}
	
	public function appendTaskBox($dependency, $pointInfo) {
		$dependencyTaskId = $dependency->getNeededTask()->getInfo()->getTaskID();
		$taskBoxDrawInfo = new TaskboxDrawInformation();
		$taskBoxDrawInfo->dependency = $dependency;
		$taskBoxDrawInfo->pointInfo = $pointInfo;
		$this->taskboxes[$dependencyTaskId] = $taskBoxDrawInfo;
	}
	
	private $taskboxes = array();
	
	private function drawDependencyLine() {
		$horizontalsNotAvailable = array();
		foreach ($this->dependencyLinesArray as $dependentLeafId => $positionsArray) {
			DrawingHelper::debug("printing dependency entry for " . $dependentLeafId);
				
			if(count($positionsArray[TaskLevelPositionEnum::$starting]) > 0) {
				
				$first = true;
				$dependentEntryPointInfo = null;
				foreach ($positionsArray[TaskLevelPositionEnum::$starting] as 
					$dependencyLineInfo) {
					
					if ($first) {
						$first = false;
						
						$entryPoint = $dependencyLineInfo->
							computeDependentEntryPointInfo();
							
						$exitPoint = $dependencyLineInfo->
							computeNeededExitPointInfo();
							
						$brokerHorizontal = $dependencyLineInfo->
							computeHorizontal();

						// questo while se nn va toglierlo
						while(in_array($brokerHorizontal, $horizontalsNotAvailable)) {
							$brokerHorizontal -= $this->getHorizontalGapForExistingDependency();
						}
						// anche questa riga sotto
						$horizontalsNotAvailable[] = $brokerHorizontal;
							
						$this->drawLineOnChart($exitPoint, $entryPoint, 
							$brokerHorizontal - $exitPoint->horizontal);

						$dependentEntryPointInfo = new PointInfo(
							$brokerHorizontal, 
							$entryPoint->vertical);
						
						$direction = null;
						if($dependentEntryPointInfo->vertical > $exitPoint->vertical) {
							$direction = "DOWN";
						}
						else if($dependentEntryPointInfo->vertical < $exitPoint->vertical) {
							$direction = "UP";	
						} 
						else {
							$direction = "RIGHT";
						}
						
						if(count($positionsArray[TaskLevelPositionEnum::$starting]) > 1) {
							DrawingHelper::drawArrow($dependentEntryPointInfo->horizontal, 
								$dependentEntryPointInfo->vertical, 
								10, 10, $direction, $this->chart);	
						}
					}
					else {
						$exitPoint = $dependencyLineInfo->
							computeNeededExitPointInfo();

						$entryPoint = clone $dependentEntryPointInfo;

						// se non va lasciare solo il corpo del do-while
						do {
							$dependentEntryPointInfo->horizontal -= 
								$this->getHorizontalGapForExistingDependency();
						} while(in_array($dependentEntryPointInfo->horizontal, $horizontalsNotAvailable));
							
						$this->drawLineOnChart($exitPoint, 
							$entryPoint,
							$dependentEntryPointInfo->horizontal - 
								$exitPoint->horizontal);
					}		
				}
			}	
			else if(count($positionsArray[TaskLevelPositionEnum::$inner]) > 0) {
				
			}
		}
	}
	
	private function drawLineOnChart($fromPoint, 
		$toPoint, 
		$horizontalOffset) {
		DrawingHelper::segmentedOffsetLine($fromPoint->horizontal, $fromPoint->vertical, 
			$horizontalOffset, $toPoint->vertical - $fromPoint->vertical, 
			$toPoint->horizontal, $toPoint->vertical, $this->chart);
			
		DrawingHelper::drawArrow($toPoint->horizontal, 
							$toPoint->vertical, 
							10, 10, "RIGHT", $this->chart);
						
	}
	
	private function isDependencyAlreadyConsidered($dependency_task_id) {
		return array_key_exists($dependency_task_id, $this->taskboxes);
	}

	private function getGapBetweenVerticalTasks() {
		return 20;
	}

	private function getGapBetweenHorizonalTasks() {
		return AbstractTaskDataDrawer::$width;
	}

	private function buildDependencyTree($tdt) {
		$analizedDependency = array();

		// building the internal graph relation
		foreach ($tdt->computeDependencyRelationOnVisibleTasks() as
			$neededTaskId => $dependantTasksdictionary) {
			
			$this->checkDependencyExistence($tdt->selectTask($neededTaskId),
			$analizedDependency);

			foreach ($dependantTasksdictionary as $dependantId => $dependencydescriptors) {
				$this->checkDependencyExistence($tdt->selectTask($dependantId),
					$analizedDependency);

				// update the needed task adding a child
				$analizedDependency[$neededTaskId]->_dependencies[] =
					$analizedDependency[$dependantId];
				DrawingHelper::debug($neededTaskId . " is necessary for " . $dependantId);
					
				// update the dependant task adding a father
				$analizedDependency[$dependantId]->_fathersDependencies[] =
					$analizedDependency[$neededTaskId];
				
				// setting the dependencies descriptors array relative to
				// a pair ($neededTaskid, $dependantTaskId)
//				$analizedDependency[$dependantId]->_dependencyDescriptors = 
//					$dependencydescriptors;
			}
			
			$analizedDependency[$neededTaskId]->_dependencyDescriptors = $dependantTasksdictionary;
		}

		$root = new DefaultDependency(null);
		$root->_dependencyType = DependencyType::$start;

		$end = new DefaultDependency(null);
		$end->_dependencyType = DependencyType::$end;

		foreach ($analizedDependency as $dependency) {
			//print "<br>dependency for: " . $dependency->_taskData->getInfo()->getTaskID();
//			foreach($dependency->_dependencies as $dep) {
//				print ", " . $dep->_taskData->getInfo()->getTaskID();
//			}

			if(!$dependency->hasFathersDependency()) {
				$dependency->_fathersDependencies[] = $root;
				$root->_dependencies[] = $dependency;
				DrawingHelper::debug("added root dependency for: " . 
					$dependency->getNeededTask()->getInfo()->getTaskID());
				
				//print "<br>added root for: " . $dependency->_taskData->getInfo()->getTaskID();
			}
				
			// the end node it's not necessary because he can't be drawed within the drawing of
			// the others node. Only at the end he know the vertical and horizontal position
			// that can stay on.
			// remove the following code
			if(!$dependency->hasDependentTasks()) {
				$end->_fathersDependencies[] = $dependency;
				$dependency->_dependencies[] = $end;
				//print "<br>added end for: " . $dependency->_taskData->getInfo()->getTaskID();
			}
		}

		return $root;

	}


	private function checkDependencyExistence($taskData, & $analizedDependency) {
		$neededTaskId = $taskData->getInfo()->getTaskID();
		if(!array_key_exists($neededTaskId, $analizedDependency)) {
//			print "<br>adding data to analizedDependency array: " . $neededTaskId .
//			" / " . $taskData->getInfo()->getTaskID();
			$analizedDependency[$neededTaskId] = new DefaultDependency($taskData);
		}
	}


	private function getHorizontalGapForExistingDependency() {
		// return a constant
		return 10;
	}

	private function buildDependencyLine($neededTask_id, $task_id, $exitPoint, $entryPoint) {
		$dependencyLineInfo = new DependencyLineInfo();
		$dependencyLineInfo->dependentTaskId = $task_id;
		$dependencyLineInfo->neededTaskId = $neededTask_id;
		$dependencyLineInfo->exitPoint = $exitPoint;
		$dependencyLineInfo->entryPoint = $entryPoint;
		$dependencyLineInfo->horizontalOffset = ($entryPoint->horizontal - $exitPoint->horizontal) / 2;
		$dependencyLineInfo->verticalOffset = $entryPoint->vertical - $exitPoint->vertical;

		$dependencyLineInfo->dotsInPattern = $this->dotsDependencyLineCount;
		$this->dotsDependencyLineCount = $this->dotsDependencyLineCount + 1;

		$this->drawedTasksMap[$task_id] = $dependencyLineInfo;

		// append a clone prevent to have update when the dependency are modified when the dependent task
		// already exists.
		$this->appendDependencyLine(clone $dependencyLineInfo);

		return $dependencyLineInfo;
	}

	private function appendDependencyLine($dependencyLine) {
		$this->dependencyLinesArray[] = $dependencyLine;
	}

	private function appendCriticalPathDomainObject($criticalPathDomainObject, $dependency) {
		// calculating the last time gap and add this information to the domain object
		// --> pass the information relative to the end milestone of the project, may be
		// --> istantiating a project entity (if exists) and get the two dates, start and end.
		// --> pass to the method the end date or the entire project entity
		$criticalPathDomainObject->setLastGap($dependency->getNeededTask()->getInfo());

		// adding the path to the table
		$this->criticalPathTable[$criticalPathDomainObject->getImplodedChain(" - ")] = $criticalPathDomainObject;
	}

	//	private function connectEntryPointToCurrentVertical($entryPoint) {
	//		if($entryPoint->vertical > $this->vertical) {
	//			DrawingHelper::LineFromTo($entryPoint->horizontal, $this->vertical,
	//			$entryPoint->horizontal, $entryPoint->vertical, $this->getChart());
	//
	//			$copyOfVertical = $this->vertical;
	//			$this->vertical = $entryPoint->vertical;
	//			$entryPoint->vertical = $copyOfVertical;
	//		}
	//		return $entryPoint;
	//	}

	private function calculateSpan($dependency, $freezedVertical) {
		$span = ($this->vertical -
			$freezedVertical - 
			$dependency->getDrawer()->computeHeight() - 
			$this->getGapBetweenVerticalTasks()) / 2.0;

		if ($span < 0) {
			$this->vertical += -$span;
		}

		return $span;
	}

	/**
	 *
	 * @param CriticalPathObjectDomain $criticalPathDomainObject
	 * @param Task $dependency
	 * @param Task $dependentTask
	 * @return CriticalPathObjectDomain
	 */
	private function buildNewCriticalPathDomainObjectFrom(
		$criticalPathDomainObject, $dependency, $dependentTask) {
		//print "<br>original duration: " . $criticalPathDomainObject->getDuration();
		$result = clone $criticalPathDomainObject;

		$result->increaseDurationOf($this->ComputeDuration($dependentTask));

		//print " / still original duration: " .
		$criticalPathDomainObject->getDuration();

		//print " / cloned duration " . $result->getDuration();

		$result->increaseDurationOf($this->ComputeTimeGap($dependency, $dependentTask));
		$result->increaseTotalEffortOf($this->ComputeTotalEffort($dependentTask));
		$result->increaseTotalCostOf($this->computeTotalCost($dependentTask));
		$result->appendTaskNode($dependentTask->getCTask()->task_id);

		//var_dump($result);
		return $result;
	}


	private function ComputeDuration($dependentTask) {
		return 5;
	}

	private function ComputeTimeGap($dependency, $dependentTask) {
		return 3;
	}

	private function ComputeTotalEffort($dependentTask) {
		return 100;
	}

	private function computeTotalCost($dependentTask) {
		return 20000;
	}
}

interface IDependency {
	public function getNeededTask();
	public function hasDependentTasks();
	public function getDependentTasks();
	public function neededTaskIsStartMilestone();
	/*
	 * remove the following method
	 */
	public function neededTaskIsEndMilestone();
	//public function getEndPointDrawer();
	//public function neededTaskIsStartMilestone();
	public function getDrawer();
	public function hasFathersDependency();
	//public function neededTaskAlreadyDrawed();
}

class DependencyType {
	public static $normal = "normal";
	public static $start = "start";
	public static $end = "end";
}

class DefaultDependency implements IDependency {
	var $_taskData;
	var $_dependencies = array();
	var $_drawer;
	var $_fathersDependencies = array();
	var $_dependencyType;
	var $_dependencyDescriptors;

	/**
	 * constructor
	 * @param TaskData $taskData
	 * @param array of IDependency $dependencies
	 */
	public function __construct($taskData) {
		$this->_taskData = $taskData;

		//		foreach ($dependencies as $dependency) {
		//			$this->_dependencies[] = $dependency;
		//		}

		$this->_dependencyType = DependencyType::$normal;
	}

	public function neededTaskIsStartMilestone() {
		return $this->_dependencyType == DependencyType::$start;
	}

	public function neededTaskIsEndMilestone() {
		return $this->_dependencyType == DependencyType::$end;
	}

	//	public function __construct($taskData, $dependencies) {
	//		self::__construct($taskData, $dependencies, null);
	//	}

	public function hasFathersDependency() {
		return count($this->_fathersDependencies) > 0;
	}

	private function neededTaskIsAtomic() {
		return $this->getNeededTask()->isAtomic();
	}

	private function neededTaskIsComposed() {
		return $this->getNeededTask()->getCollapsed();
	}

	/**
	 * getter of the underlyng task data
	 * @return TaskData
	 */
	public function getNeededTask() {
		if(!isset($this->_taskData) || $this->_taskData == null) {
			return new StartMilestoneDependencyProxy();
		}

		return $this->_taskData;
	}

	/**
	 * return if this dependency has deep dependency or not
	 * return boolean
	 */
	public function hasDependentTasks() {
		/*
		 * the following conditional logic is not correct because the end milestone
		 * has no such responsibility to be capturated with some abstraction.
		 * remove the IF and leave only the original code.
		 */
		if (count($this->_dependencies) == 1) {
			if ($this->_dependencies[0]->neededTaskIsEndMilestone()) {
				return false;
			}
		}

		return count($this->_dependencies) > 0;
	}

	public function getDependentTasks() {
		return $this->_dependencies;
	}

	public function getDrawer() {
		return new CommonTaskDataDrawer($this);
		
		if($this->neededTaskIsAtomic()) {
			$this->_drawer = new AtomicTaskDataDrawer($this);
		}

		if ($this->neededTaskIsComposed()) {
			$this->_drawer = new ComposedTaskDataDrawer($this);
		}

		if ($this->_dependencyType == DependencyType::$start) {
			$this->_drawer = new StartMilestoneDrawer($this);
		}
			
		if (isset($this->_drawer)) {
			return $this->_drawer;
		}
		else{
			DrawingHelper::debug("No drawer are implemented for the kind of the undarlying task data with id = " .
				$this->getNeededTask()->getInfo()->getTaskID());
		}
	}
	
	public function hasInnerDependentTasks() {
		DrawingHelper::debug("hasInnerDependentTasks method for " . 
			$this->getNeededTask()->getInfo()->getTaskID());
		
		foreach ($this->_dependencyDescriptors
			as $dependentTaskId => $dependencyDescriptors) {

			foreach ($dependencyDescriptors as $dependencyDescriptor) {
				
				DrawingHelper::debug("found descriptor " . 
							$dependencyDescriptor);
							
				DrawingHelper::debug("needed task for the descritor are  " . 
							$dependencyDescriptor->neededTaskId);
				
				if ($dependencyDescriptor->neededTaskPositionEnum ==
						TaskLevelPositionEnum::$inner) {

					DrawingHelper::debug("inner found!");
							
					return true;
							
				}
			}
		}
		
		return false;
	}
	
	public function hasInnerNeededTasks() {
		DrawingHelper::debug("hasInnerNeededTask method for " . 
			$this->getNeededTask()->getInfo()->getTaskID());
			
		foreach ($this->_fathersDependencies as $fatherDependency) {
			
			DrawingHelper::debug("found father " . 
				$fatherDependency->getNeededTask()->getInfo()->getTaskID());
			
			foreach ($fatherDependency->_dependencyDescriptors
				as $dependentTaskId => $dependencyDescriptors) {

				if ($dependentTaskId == $this->getNeededTask()->getInfo()->getTaskID()) {
					
					foreach ($dependencyDescriptors as $dependencyDescriptor) {
						DrawingHelper::debug("found descriptor " . 
							$dependencyDescriptor);
							
						if ($dependencyDescriptor->dependentTaskPositionEnum ==
								TaskLevelPositionEnum::$inner) {
		
							DrawingHelper::debug("inner found!");
									
							return true;
									
						}
					}
				}
			}
		}
		
		return false;
	}
}

class StartMilestoneDependencyProxy {
	var $pippo = 5;

	public function getInfo() {
		return $this;
	}

	public function getTaskID() {
		return DependencyType::$start;
	}

	public function isAtomic() {
		return false;
	}

	public function getCollapsed() {
		return false;
	}

	public function getWBSId() {
		return "0";
	}
	
	public function getTaskName() {
		return "start";
	}
	
}


interface ITaskDataDrawer {

	/**
	 * return the PointInfo object that point to the entry point of the representation of the TaskData
	 * @param PointInfo $initialTopLeftCorner
	 * @return PointInfo
	 */
//	public function computeEntryPoint($initialTopLeftCorner);
//	public function computeExitPoint($initialTopLeftCorner);
	public function computeHeight();

	/**
	 *
	 * @param GifImage $gifImage
	 * @param PointInfo $initialPoint
	 * @return void
	 */
	public function drawOn(& $gifImage, $initialPoint);
}

abstract class AbstractTaskDataDrawer implements ITaskDataDrawer {
	public static $width;
	public static $singleRowHeight;
	public static $composedVerticalLineLength = 10;
	public static $userOptionChoice;

	var $_dependency;

	public function __construct($dependency) {
		$this->_dependency = $dependency;
	}

	public function computeHeight() {
		
		return $this->computeTaskBoxHeight();
		
//		$height = GifTaskBox::getEffectiveHeightOfTaskBox(
//			$this->_dependency->getNeededTask(), 
//			AbstractTaskDataDrawer::$singleRowHeight, 
//			AbstractTaskDataDrawer::$userOptionChoice);
//			
//			DrawingHelper::debug("Height for " . 
//			$this->_dependency->getNeededTask()->getInfo()->getTaskID() . " is " . $height);
//			
//		return $height;
	}

	public function drawOn(& $gifImage, $initialPoint) {
		$drawingPoint = clone $initialPoint;
		$drawingPoint = $this->onEntryDependencySegmentDrawing($gifImage, $drawingPoint);
		$drawingPoint = $this->onTaskBoxDrawing($gifImage, $drawingPoint);
		$drawingPoint = $this->onExitDependencySegmentDrawing($gifImage, $drawingPoint);
	}

	protected function onEntryDependencySegmentDrawing(& $gifImage, $initialPoint) {
		return $initialPoint;
	}
	
	protected function onExitDependencySegmentDrawing(& $gifImage, $initialPoint) {
		return $initialPoint;
	}

	private function onTaskBoxDrawing(& $gifImage, $initialPoint) {

		$gifTaskbox = new GifTaskBox($gifImage, 
				$initialPoint->horizontal, 
				$initialPoint->vertical, 
				AbstractTaskDataDrawer::$width, 
				AbstractTaskDataDrawer::$singleRowHeight, 
				$this->_dependency->getNeededTask(), 
				AbstractTaskDataDrawer::$userOptionChoice);

		$gifTaskbox->drawOn();
		
		return new PointInfo($initialPoint->horizontal, 
			$initialPoint->vertical + $this->computeTaskBoxHeight());
	}

	private function computeTaskBoxHeight() {
		return GifTaskBox::getEffectiveHeightOfTaskBox(
			$this->_dependency->getNeededTask(), 
			AbstractTaskDataDrawer::$singleRowHeight, 
			AbstractTaskDataDrawer::$userOptionChoice);
	}
}

class CommonTaskDataDrawer extends AbstractTaskDataDrawer {
	public function __construct($dependency) {
		parent::__construct($dependency);
	}
	
	public function computeHeight() { 
		$innersCount = 0;
		
		if($this->_dependency->hasInnerNeededTasks()) {
			$innersCount = $innersCount + 1;	
		}
		
		if($this->_dependency->hasInnerDependentTasks()) {
			$innersCount = $innersCount + 1;	
		}
		
		return parent::computeHeight() + 
			($innersCount * AbstractTaskDataDrawer::$composedVerticalLineLength);
	}
	
	public function computeInnerExitPoint($initialPoint) {
		$clonedPoint = $this->computeInnerEntryPoint($initialPoint);
		
		$clonedPoint->vertical += AbstractTaskDataDrawer::$composedVerticalLineLength;
		
		return $clonedPoint;
	}
	
	public function computeInnerEntryPoint($initialPoint) {
		$clonedPoint = clone $initialPoint;
		
		$clonedPoint->horizontal += AbstractTaskDataDrawer::$width / 2;
		
		return $clonedPoint;
	}
	
	public function computeStartingEntryPoint($initialPoint) {
		$clonedPoint = clone $initialPoint;
		
		$clonedPoint->vertical += $this->computeHeight() / 2;
		
		return $clonedPoint;
	}
	
	public function computeEndingExitPoint($initialPoint) {
		$clonedPoint = clone $initialPoint;

		$clonedPoint->horizontal += AbstractTaskDataDrawer::$width;
		$clonedPoint->vertical += $this->computeHeight() / 2;
		
		return $clonedPoint;
	}
	
	protected function onEntryDependencySegmentDrawing(& $gifImage, $initialPoint) {
		$returnPoint = clone $initialPoint;
		DrawingHelper::debug("ma almeno ci arrivo?");
		if($this->_dependency->hasInnerNeededTasks()) {
			DrawingHelper::debug("ho almeno un needed task che entra nel mio top?");	
			$startingPoint = $this->computeInnerEntryPoint($initialPoint);
			
			$endPoint = new PointInfo(
				$startingPoint->horizontal,
				$startingPoint->vertical + 
					AbstractTaskDataDrawer::$composedVerticalLineLength);
					
			DrawingHelper::LineFromTo(
				$startingPoint->horizontal, 
				$startingPoint->vertical, 
				$endPoint->horizontal, 
				$endPoint->vertical,
				$gifImage);
				
			DrawingHelper::drawArrow($endPoint->horizontal, $endPoint->vertical, 
				10, 10, "DOWN", $gifImage);
				
			$returnPoint->vertical = $endPoint->vertical;//AbstractTaskDataDrawer::$composedVerticalLineLength;
		}
		
		return $returnPoint;
	}
	
	protected function onExitDependencySegmentDrawing(& $gifImage, $initialPoint) {
		$returnPoint = clone $initialPoint;
		DrawingHelper::debug("ma almeno ci arrivo?");
		if($this->_dependency->hasInnerDependentTasks()) {
			DrawingHelper::debug("ho almeno un dependent task che esce dal mio bottom?");	
			$startingPoint = $this->computeInnerEntryPoint($initialPoint);
			
			$endPoint = $this->computeInnerExitPoint($initialPoint);
					
			DrawingHelper::LineFromTo(
				$startingPoint->horizontal, 
				$startingPoint->vertical, 
				$endPoint->horizontal, 
				$endPoint->vertical,
				$gifImage);
				
			$returnPoint->vertical = $endPoint->vertical;//AbstractTaskDataDrawer::$composedVerticalLineLength;
		}
		
		return $returnPoint;
	}
}

class StartMilestoneDrawer extends AbstractTaskDataDrawer {
	public static $circleDiameter = 20;
	
	public function __construct($dependency) {
		parent::__construct($dependency);
	}
	
	public function computeEntryPoint($initialTopLeftCorner) {
		DrawingHelper::debug("Called the computeEntryPoint on proxy for " . 
			"start milestone. Impossible to reach this point");
	}

	public function computeExitPoint($initialTopLeftCorner) {
		return new PointInfo($initialTopLeftCorner->horizontal +
			StartMilestoneDrawer::$width,
			$initialTopLeftCorner->vertical + ($this->computeHeight() / 2));
	}
	
	public function computeHeight() {
		return StartMilestoneDrawer::$circleDiameter;
	}
	
	public function getWidth() {
		return StartMilestoneDrawer::$circleDiameter;
	}
	
}


//class AtomicTaskDataDrawer extends AbstractTaskDataDrawer {
//	public function __construct($dependency) {
//		parent::__construct($dependency);
//	}
//
//	public function computeEntryPoint($initialTopLeftCorner) {
//		return new PointInfo($initialTopLeftCorner->horizontal,
//		$initialTopLeftCorner->vertical + ($this->computeHeight() / 2));
//	}
//
//	public function computeExitPoint($initialTopLeftCorner) {
//		return new PointInfo($initialTopLeftCorner->horizontal +
//		AbstractTaskDataDrawer::$width,
//		$initialTopLeftCorner->vertical + ($this->computeHeight() / 2));
//	}
//}
//
//class ComposedTaskDataDrawer extends AbstractTaskDataDrawer {
//	public function __construct($dependency) {
//		parent::__construct($dependency);
//	}
//
//	public function computeEntryPoint($initialTopLeftCorner) {
//		return new PointInfo($initialTopLeftCorner->horizontal +
//		(AbstractTaskDataDrawer::$width / 2), $initialTopLeftCorner->vertical);
//	}
//
//	public function computeExitPoint($initialTopLeftCorner) {
//		return new PointInfo($initialTopLeftCorner->horizontal +
//		(AbstractTaskDataDrawer::$width / 2), $initialTopLeftCorner->vertical + $this->computeHeight());
//	}
//
//	public function computeheight() {
//		// taking the standard height of the task box
//		$result = parent::computeHeight();
//
//		// adding the top segment to represent the entry dependency if this exists
//		$result += $this->_dependency->hasFathersDependency() ? AbstractTaskDataDrawer::$composedVerticalLineLength : 0;
//
//		// adding the bottom segment to represent the exit dependency if this exists
//		$result += $this->_dependency->hasDependentTasks() ? AbstractTaskDataDrawer::$composedVerticalLineLength : 0;
//
//		return $result;
//	}
//
//	protected function onDependencySegmentDrawing($gifImage, $initialPoint) {
//		$arrivePointVerticalComponent = $initialPoint->vertical + AbstractTaskDataDrawer::$composedVerticalLineLength;
//		$horizontal = $initialPoint->horizontal +
//			(AbstractTaskDataDrawer::$width / 2);
//		DrawingHelper::LineFromTo(horizontal, $initialPoint->vertical, horizontal, $arrivePointVerticalComponent, $gifImage);
//		return new PointInfo($initialPoint->horizontal, $arrivePointVerticalComponent);
//	}
//}
//
?>