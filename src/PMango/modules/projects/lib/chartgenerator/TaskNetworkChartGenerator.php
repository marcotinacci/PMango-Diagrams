<?php
function __autoload($classname) {
	require_once "./modules/MangoGanttCPM/chartgenerator/ChartGenerator.php";
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

	public function generateChart()	{

	}

	/**
	 * @var PointInfo
	 */
	private $vertical;

	/**
	 * internal method that takes as parameter the root dependency node.
	 * This method is recursion ready
	 * @param IDependency $dependency
	 * @param CriticalPathDomainObject $criticalPathDomainObject
	 * @param int $horizontal
	 * @return void
	 */
	private function internalGenerateChart($dependency, $criticalPathDomainObject, $horizontal) {
		if ($dependency->hasDependentTasks()) {

			// freeze the current vertical quote
			$freezeVertical = $this->vertical;

			$entryPointArray = array();

			foreach ($dependency->getDependentTasks() as $dependentTask) {

				$dependantTaskId =	$dependantTask->getNeededTask()->getTask()->task_id;
				/*
				 * cloning the critical path domain object to duplicate the info
				 * to have one clone foreach fork of the path
				 */
				$cpdoClone = $this->buildNewCriticalPathDomainObjectFrom(
				$criticalPathDomainObject,
				$dependency->getNeededTask()->getTask(),
				$dependentTask->getNeededTask()->getTask());

				if (array_key_exists($dependantTaskId, $this->drawedTasksMap)) {
					$existingDependencyLineInfo =& $this->drawedTasksMap[$dependantTaskId];
					$existingDependencyLineInfo->horizontal -= $this->getHorizontalGapForExistingDependency();
					$this->appendDependencyLine(clone $existingDependencyLineInfo);
				} else {
					$entryPoint = $dependentTask->getDrawer()->computeEntryPoint(new PointInfo($horizontal, $this->vertical));

					// the following method draw a line only if is necessary in presence of composed task
					//$entryPoint = $this->connectEntryPointToCurrentVertical($entryPoint);

					$entryPointArray[$dependantTask->getNeededTask()->getTask()->task_id] = $entryPoint;
				}


				$this->internalGenerateChart($dependentTask, $cpdoClone, $horizontal + $this->getGapBetweenHorizonalTasks()
				+ $this->getTaskBoxWidth());

			}

			$exitPoint = $dependency->getDrawer()->computeExitPoint();
			foreach($entryPointArray as $task_id => $entryPoint) {
				$dependencyLine = $this->buildDependencyLine($dependency->getNeededTask()->getTask()->task_id, $task_id, $exitPoint, $entryPoint);
			}

			$freezeVertical += $this->calculateSpan($dependency);

			$dependency->getDrawer()->drawOn($this->getChart(), new PointInfo($horizontal, $freezeVertical));
		}
		else {
			$this->appendCriticalPathDomainObject($criticalPathDomainObject, $dependency);

			$taskDrawer = $dependency->getDrawer();
			$taskDrawer->drawOn($this->getChart(), new PointInfo($horizontal, $this->vertical));

			// the last taskbox put a empty space, but he haven't the information necessary to know that he
			// is the last and not put the separator gap
			$this->vertical += $taskDrawer->getHeight() + $this->getGapBetweenVerticalTasks();
		}
	}
	
	private function getHorizontalGapForExistingDependency() {
		// return a constant
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
		$criticalPathDomainObject->setLastGap($dependency->getNeededTask()->getTask());

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
		$span = ($this->vertical - $dependency->getDrawer()->getHeight()) / 2;

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
	private function buildNewCriticalPathDomainObjectFrom($criticalPathDomainObject, $dependency, $dependentTask) {
		$result = clone $criticalPathDomainObject;
		$result->increaseDurationOf($this->ComputeDuration($dependentTask));
		$result->increaseDurationOf($this->ComputeTimeGap($dependency, $dependentTask));
		$result->increaseTotalEffortOf($this->ComputeTotalEffort($dependentTask));
		$result->increaseTotalCostOf($this->computeTotalCost($dependentTask));
		$result->appendTaskNode($dependentTask->task_id);
		return $result;
	}

	private function ComputeDuration($dependentTask) {

	}

	private function ComputeTimeGap($dependency, $dependentTask) {

	}

	private function ComputeTotalEffort($dependentTask) {

	}

	private function computeTotalCost($dependentTask) {

	}
}

interface IDependency {
	public function getNeededTask();
	public function hasDependentTasks();
	public function getDependentTasks();
	public function getEndPointDrawer();
	public function neededTaskIsStartMilestone();
	public function getDrawer();
	public function hasFatherDependency();
	//public function neededTaskAlreadyDrawed();
}

class DefaultDependency implements IDependency {
	var $_taskData;
	var $_dependencies = array();
	var $_drawer;
	var $_fatherDependency;

	/**
	 * constructor
	 * @param TaskData $taskData
	 * @param array of IDependency $dependencies
	 */
	public function __construct($taskData, $dependencies, $fatherDependency) {
		$this->_taskData = $taskData;

		$this->_fatherDependency = $fatherDependency;

		foreach ($dependencies as $dependency) {
			$this->_dependencies[] = $dependency;
		}

		$this->initDrawer();
	}

	public function __construct($taskData, $dependencies) {
		self::__construct($taskData, $dependencies, null);
	}

	private function initDrawer() {
		if($this->neededTaskIsAtomic()) {
			$this->_drawer = new AtomicTaskDataDrawer($this->getNeededTask());
		}

		if ($this->neededTaskIsComposed()) {
			$this->_drawer = new ComposedTaskDataDrawer($this->getNeededTask());
		}
	}

	public function hasFatherDependency() {
		return isset($this->_fatherDependency);
	}

	private function neededTaskIsAtomic() {
		// not yet implemented
	}

	private function neededTaskIsComposed() {
		// not yet implemented
	}

	/**
	 * getter of the underlyng task data
	 * @return TaskData
	 */
	public function getNeededTask() {
		return $this->_taskData;
	}

	/**
	 * return if this dependency has deep dependency or not
	 * return boolean
	 */
	public function hasDependentTasks() {
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
			die ("No drawer are implemented for the kind of the undarlying task data.");
		}
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
		AbstractTaskDataDrawer::$width, 30,$taskData);

		$gifTaskBox->drawOn($gifImage);

		return new PointInfo($initialPoint->horizontal, $initialPoint->vertical + $this->computeTaskBoxHeight($gifTaskBox));
	}

	private function computeTaskBoxHeight($gifTaskBox) {

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
		$result += $this->_dependency->hasFatherDependency() ? AbstractTaskDataDrawer::$composedVerticalLineLength : 0;

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