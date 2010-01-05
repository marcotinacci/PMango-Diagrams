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

	public function generateChart()	{

	}

	/**
	 * @var PointInfo
	 */
	private $vertical;

	/**
	 * internal method that takes a parameter the root dependency node
	 * @param IDependency $dependency
	 * @param CriticalPathDomainObject $criticalPathDomainObject
	 * @param PointInfo $currentPoint
	 * @return void
	 */
	private function internalGenerateChart($dependency, $criticalPathDomainObject, $horizontal) {
		if ($dependency->hasDependentTasks()) {

			$freezeVertical = $this->vertical;

			$entryPointArray = array();

			foreach ($dependency->getDependentTasks() as $dependentTask) {

				/*
				 * cloning the critical path path domain object to duplicate the info
				 * to have one clone foreach fork of the path
				 */
				$cpdoClone = $this->BuildNewCriticalPathDomainObjectFrom(
				$criticalPathDomainObject,
				$dependency->getNeededTask()->getTask(),
				$dependentTask->getNeededTask()->getTask());

				$entryPoint = $this->GetEntryPoint($dependentTask, new PointInfo($horizontal, $this->vertical));

				// the following method draw a line only if is necessary in presence of composed task
				$entryPoint = $this->connectEntryPointToCurrentVertical($entryPoint);

				$entryPointArray[$dependantTask->getNeededTask()->getTask()->task_id] = $entryPoint;

				$this->internalGenerateChart($dependentTask, $cpdoClone, $horizontal + $this->getGapBetweenHorizonalTasks()
				+ $this->getTaskBoxWidth());

			}

			$exitPoint = $dependency->getDrawer()->computeExitPoint();
			foreach($entryPointArray as $task_id => $entryPoint) {
				$dependencyLine = $this->buildDependencyLine($dependency, $task_id, $exitPoint, $entryPoint);
			}

			$freezeVertical += $this->calculateSpan($dependency);

			$dependency->getTaskDrawer()->drawOn(new PointInfo($horizontal, $freezeVertical));
		}
		else {
			// calculating the last time gap and add this information to the domain object
			$criticalPathDomainObject->setLastGap($dependency->getNeededTask()->getTask());

			// adding the path to the table
			$this->criticalPathTable[$criticalPathDomainObject->getImplodedChain(" - ")] = $criticalPathDomainObject;

			$taskDrawer = $dependency->getTaskDrawer();
			$taskDrawer->drawOn(new PointInfo($horizontal, $this->vertical));
			$this->vertical += $taskDrawer->getHeight() +
			(2 * $this->getCompositeDependencyArrowHeight()) + $this->getGapBetweenVerticalTasks();
		}
	}

	private function connectEntryPointToCurrentVertical($entryPoint) {
		if($entryPoint->vertical > $this->vertical) {
			DrawingHelper::LineFromTo($entryPoint->horizontal, $this->vertical,
			$entryPoint->horizontal, $entryPoint->vertical, $this->getChart());

			$copyOfVertical = $this->vertical;
			$this->vertical = $entryPoint->vertical;
			$entryPoint->vertical = $copyOfVertical;
		}
		return $entryPoint;
	}

	private function calculateSpan($dependency) {
		$span = ($this->vertical - $dependency->getTaskDrawer()->getHeight()) / 2;

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
	private function BuildNewCriticalPathDomainObjectFrom($criticalPathDomainObject, $dependency, $dependentTask) {
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
	//public function neededTaskAlreadyDrawed();
}



?>