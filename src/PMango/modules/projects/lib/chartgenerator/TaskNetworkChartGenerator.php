<?php
require_once dirname(__FILE__) . "/ChartGenerator.php";
require_once dirname(__FILE__) . "/CriticalPathDomainObject.php";
require_once dirname(__FILE__) . "/PointInfo.php";
require_once dirname(__FILE__) . "/../taskdatatree/TaskData.php";
require_once dirname(__FILE__) . "/../gifarea/GifTaskBox.php";
require_once dirname(__FILE__) . "/../gifarea/DrawingHelper.php";
require_once dirname(__FILE__) . "/DependencyLineInfo.php";

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
		return UserOptionsChoice::GetInstance();
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
		$this->tdtGenerator->generateTaskDataTree()->getVisibleTree(),
		$userOptionChoice, 12, FF_VERDANA);

		AbstractTaskDataDrawer::$userOptionChoice = $userOptionChoice;

		print "<br>the best width for taskboxes is " . AbstractTaskDataDrawer::$width;

		// building the canvas
		$this->chart = new GifImage(1000, 800);

		// start the generation from (5, 5) point
		$this->vertical = 5;
		$this->internalGenerateChart($root, new CriticalPathDomainObject(), 5);
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
			print "<br>freezed vertical $freezeVertical";

			$entryPointArray = array();

			foreach ($dependency->getDependentTasks() as $dependentTask) {

				$dependantTaskId =	$dependentTask->getNeededTask()->getInfo()->getTaskID();

				print "<br>drawing dependant task " . $dependantTaskId;

				/*
				 * cloning the critical path domain object to duplicate the info
				 * to have one object foreach fork of the path
				 */
				$cpdoClone = $this->buildNewCriticalPathDomainObjectFrom(
				$criticalPathDomainObject,
				$dependency->getNeededTask()->getInfo(),
				$dependentTask->getNeededTask()->getInfo());

				if (array_key_exists($dependantTaskId, $this->drawedTasksMap)) {
					$existingDependencyLineInfo =& $this->drawedTasksMap[$dependantTaskId];
					print "$dependantTaskId already exists in the drawed task map";

					$existingDependencyLineInfo->horizontal -= $this->getHorizontalGapForExistingDependency();
					$this->appendDependencyLine(clone $existingDependencyLineInfo);
				} else {
					print "$dependantTaskId not exists into drawed task map";
					$drawer = $dependentTask->getDrawer();

					print "<br> drawer found for " . $dependantTaskId;
					$entryPoint = $drawer->computeEntryPoint(new PointInfo($horizontal, $this->vertical));

					// the following method draw a line only if is necessary in presence of composed task
					//$entryPoint = $this->connectEntryPointToCurrentVertical($entryPoint);

					$entryPointArray[$dependantTaskId] = $entryPoint;
					print " now reach this point";

					$this->drawedTasksMap[$dependantTaskId] = $dependentTask;
				}

				/*
				 * Move the recursive call before the above conditional logic, keep the result point and manage
				 * the dependency line info respect to that point
				 */
				$this->internalGenerateChart($dependentTask, $cpdoClone, $horizontal + $this->getGapBetweenHorizonalTasks()
				+ AbstractTaskDataDrawer::$width);

			}
				
			if (!($dependency->getNeededTask() instanceof StartMilestoneDependencyProxy)) {

				$freezeVertical += $this->calculateSpan($dependency);

				$exitPoint = $dependency->getDrawer()->computeExitPoint(new PointInfo($horizontal, $freezeVertical));
				foreach($entryPointArray as $task_id => $entryPoint) {
					print "<br>" . $dependency->getNeededTask()->getInfo()->getTaskID() .
				" dependency is start milestone? " . $dependency->neededTaskIsStartMilestone() . " / is end milestone? " . $dependency->neededTaskIsEndMilestone();
					if ($dependency->getNeededTask() instanceof StartMilestoneDependencyProxy) {
						print "<br>needed task is a proxy: " . $dependency->getNeededTask()->getInfo()->pippo;

					}
					$dependencyLine = $this->buildDependencyLine($dependency->getNeededTask()->getInfo()->getTaskID(), $task_id, $exitPoint, $entryPoint);
				}


					
				$dependency->getDrawer()->drawOn($this->getChart(), new PointInfo($horizontal, $freezeVertical));
			}
		}
		else {
			$this->appendCriticalPathDomainObject($criticalPathDomainObject, $dependency);

			if (!array_key_exists($dependency->getNeededTask()->getInfo()->getTaskID(), $this->drawedTasksMap)) {
					
				print "<br>printing the taskbox for " . $dependency->getNeededTask()->getInfo()->getTaskID();

				$taskDrawer = $dependency->getDrawer();
				$taskDrawer->drawOn($this->getChart(), new PointInfo($horizontal, $this->vertical));

				print "<br>Task box drawed for " . $dependency->getNeededTask()->getInfo()->getTaskID();

				// the last taskbox put a empty space, but he haven't the information necessary to know that he
				// is the last and not put the separator gap
				$this->vertical += $taskDrawer->computeHeight() + $this->getGapBetweenVerticalTasks();
			}
		}
	}

	private function getGapBetweenVerticalTasks() {
		return 20;
	}

	private function getGapBetweenHorizonalTasks() {
		return AbstractTaskDataDrawer::$width + 50;
	}

	private function buildDependencyTree($tdt) {
		$analizedDependency = array();

		// building the internal graph relation
		foreach ($tdt->computeDependencyRelationOnVisibleTasks() as
		$neededTaskId => $dependantTasksIds) {
			$this->checkDependencyExistence($tdt->selectTask($neededTaskId),
			$analizedDependency);

			foreach ($dependantTasksIds as $dependantId) {
				$this->checkDependencyExistence($tdt->selectTask($dependantId),
				$analizedDependency);

				// update the needed task adding a child
				$analizedDependency[$neededTaskId]->_dependencies[] =
				$analizedDependency[$dependantId];

				// update the dependant task adding a father
				$analizedDependency[$dependantId]->_fathersDependencies[] =
				$analizedDependency[$neededTaskId];
			}
		}

		$root = new DefaultDependency(null);
		$root->_dependencyType = DependencyType::$start;

		$end = new DefaultDependency(null);
		$end->_dependencyType = DependencyType::$end;

		foreach ($analizedDependency as $dependency) {
			print "<br>dependency for: " . $dependency->_taskData->getInfo()->getTaskID();
			foreach($dependency->_dependencies as $dep) {
				print ", " . $dep->_taskData->getInfo()->getTaskID();
			}
			if(!$dependency->hasFathersDependency()) {
				$dependency->_fathersDependencies[] = $root;
				$root->_dependencies[] = $dependency;
				print "<br>added root for: " . $dependency->_taskData->getInfo()->getTaskID();
			}
				
			// the end node it's not necessary because he can't be drawed within the drawing of
			// the others node. Only at the end he know the vertical and horizontal position
			// that can stay on.
			// remove the following code
			if(!$dependency->hasDependentTasks()) {
				$end->_fathersDependencies[] = $dependency;
				$dependency->_dependencies[] = $end;
				print "<br>added end for: " . $dependency->_taskData->getInfo()->getTaskID();
			}
		}

		return $root;

	}


	private function checkDependencyExistence($taskData, & $analizedDependency) {
		$neededTaskId = $taskData->getInfo()->getTaskID();
		if(!array_key_exists($neededTaskId, $analizedDependency)) {
			print "<br>adding data to analizedDependency array: " . $neededTaskId .
			" / " . $taskData->getInfo()->getTaskID();
			$analizedDependency[$neededTaskId] = new DefaultDependency($taskData);
		}
	}


	private function getHorizontalGapForExistingDependency() {
		// return a constant
		return 3;
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

	private function calculateSpan($dependency) {
		$span = ($this->vertical - $dependency->getDrawer()->computeHeight()) / 2;

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
		print "<br>original duration: " . $criticalPathDomainObject->getDuration();
		$result = clone $criticalPathDomainObject;

		$result->increaseDurationOf($this->ComputeDuration($dependentTask));

		print " / still original duration: " .
		$criticalPathDomainObject->getDuration();

		print " / cloned duration " . $result->getDuration();

		$result->increaseDurationOf($this->ComputeTimeGap($dependency, $dependentTask));
		$result->increaseTotalEffortOf($this->ComputeTotalEffort($dependentTask));
		$result->increaseTotalCostOf($this->computeTotalCost($dependentTask));
		$result->appendTaskNode($dependentTask->task_id);

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

		$this->initDrawer();
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

	private function initDrawer() {
		/*
		 * add a if branch to check if the needed task is the start
		 * milestone. Then allocate the right drawer
		 */

		if($this->neededTaskIsAtomic()) {
			$this->_drawer = new AtomicTaskDataDrawer($this);
		}

		if ($this->neededTaskIsComposed()) {
			$this->_drawer = new ComposedTaskDataDrawer($this);
		}
	}

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
		if (isset($this->_drawer)) {
			return $this->_drawer;
		}
		else{
			die ("No drawer are implemented for the kind of the undarlying task data with id = " .
			$this->getNeededTask()->getInfo()->getTaskID());
		}
	}
}

class StartMilestoneDependencyProxy {
	var $pippo = 5;

	public function getInfo() {
		print "<br>start milestone proxy activated!";
		return $this;
	}

	public function getTaskID() {
		print "<br>start milestone proxy activated!";
		return DependencyType::$start;
	}

	public function isAtomic() {
		print "<br>start milestone proxy activated!";
		return false;
	}

	public function getCollapsed() {
		print "<br>start milestone proxy activated!";
		return false;
	}

}


interface ITaskDataDrawer {

	/**
	 * return the PointInfo object that point to the entry point of the representation of the TaskData
	 * @param PointInfo $initialTopLeftCorner
	 * @return PointInfo
	 */
	public function computeEntryPoint($initialTopLeftCorner);
	public function computeExitPoint($initialTopLeftCorner);
	public function computeHeight();

	/**
	 *
	 * @param GifImage $gifImage
	 * @param PointInfo $initialPoint
	 * @return void
	 */
	public function drawOn($gifImage, $initialPoint);
}

abstract class AbstractTaskDataDrawer implements ITaskDataDrawer {
	public static $width;
	public static $composedVerticalLineLength = 5;
	public static $userOptionChoice;

	protected $_dependency;

	public function __construct($dependency) {
		$this->_dependency = $dependency;
	}

	public function computeHeight() {
		// not yet implemented
	}

	public function drawOn($gifImage, $initialPoint) {
		$drawingPoint = $initialPoint;
		$drawingPoint = $this->onDependencySegmentDrawing($gifImage, $drawingPoint);
		$drawingPoint = $this->onTaskBoxDrawing($gifImage, $drawingPoint);
		$drawingPoint = $this->onDependencySegmentDrawing($gifImage, $drawingPoint);
	}

	protected function onDependencySegmentDrawing($gifImage, $initialPoint) {
		return $initialPoint;
	}

	protected function onTaskBoxDrawing($gifImage, $initialPoint) {
		// the size of each row is correct to be hard coded like this fashion??
		$gifTaskBox = new GifTaskBox($initialPoint->horizontal, $initialPoint->vertical,
		AbstractTaskDataDrawer::$width, 30, $this->_dependency->getNeededTask());

		$gifTaskBox->drawOn($gifImage);

		return new PointInfo($initialPoint->horizontal, $initialPoint->vertical + $this->computeTaskBoxHeight($gifTaskBox));
	}

	private function computeTaskBoxHeight($gifTaskBox) {
		GifTaskBox::getEffectiveHeightOfTaskBox($this->_dependency,
		30, self::$userOptionChoice);
	}
}

class AtomicTaskDataDrawer extends AbstractTaskDataDrawer {
	public function __construct($dependency) {
		parent::__construct($dependency);
	}

	public function computeEntryPoint($initialTopLeftCorner) {
		return new PointInfo($initialTopLeftCorner->horizontal,
		$initialTopLeftCorner->vertical + ($this->computeHeight() / 2));
	}

	public function computeExitPoint($initialTopLeftCorner) {
		return new PointInfo($initialTopLeftCorner->horizontal +
		AbstractTaskDataDrawer::$width,
		$initialTopLeftCorner->vertical + ($this->computeHeight() / 2));
	}
}

class ComposedTaskDataDrawer extends AbstractTaskDataDrawer {
	public function __construct($dependency) {
		parent::__construct($dependency);
	}

	public function computeEntryPoint($initialTopLeftCorner) {
		return new PointInfo($initialTopLeftCorner->horizontal +
		(AbstractTaskDataDrawer::$width / 2), $initialTopLeftCorner->vertical);
	}

	public function computeExitPoint($initialTopLeftCorner) {
		return new PointInfo($initialTopLeftCorner->horizontal +
		(AbstractTaskDataDrawer::$width / 2), $initialTopLeftCorner->vertical + $this->computeHeight());
	}

	public function computeheight() {
		// taking the standard height of the task box
		$result = parent::computeHeight();

		// adding the top segment to represent the entry dependency if this exists
		$result += $this->_dependency->hasFathersDependency() ? AbstractTaskDataDrawer::$composedVerticalLineLength : 0;

		// adding the bottom segment to represent the exit dependency if this exists
		$result += $this->_dependency->hasDependentTasks() ? AbstractTaskDataDrawer::$composedVerticalLineLength : 0;

		return $result;
	}

	protected function onDependencySegmentDrawing($gifImage, $initialPoint) {
		$arrivePointVerticalComponent = $initialPoint->vertical + AbstractTaskDataDrawer::$composedVerticalLineLength;
		$horizontal = $initialPoint->horizontal +
		(AbstractTaskDataDrawer::$width / 2);
		DrawingHelper::LineFromTo(horizontal, $initialPoint->vertical, horizontal, $arrivePointVerticalComponent, $gifImage);
		return new PointInfo($initialPoint->horizontal, $arrivePointVerticalComponent);
	}
}

?>