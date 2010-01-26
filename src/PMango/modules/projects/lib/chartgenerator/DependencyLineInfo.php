<?php
require_once dirname(__FILE__) . "/PointInfo.php"; 

class DependencyLineInfo {
	/**
	 * 
	 * @var PointInfo
	 */
	var $neededTaskboxDrawInformation;
	
	/**
	 * 
	 * @var PointInfo
	 */
	var $dependentTaskboxDrawInformation;
	
	var $dependencyDescriptor;
	
	public static $gapForFirstBackwardEntry = 3;
	
	public function isSwapped() {
		return $this->computeNeededExitPointInfo()->horizontal >=
			$this->computeDependentEntryPointInfo()->horizontal;
	}
	
	public function computeHorizontal() {
		
		if(!$this->isSwapped()) {
				
			$neededhorizontal = $this->neededTaskboxDrawInformation->pointInfo->horizontal;
			
			$neededhorizontal += AbstractTaskDataDrawer::$width;
			
			$gap = ($this->dependentTaskboxDrawInformation->pointInfo->horizontal - 
				$neededhorizontal) / 2;
	
			return $neededhorizontal + $gap;
		}
		else {
			$horizontal = null;
			
			if($this->dependencyDescriptor->dependentTaskPositionEnum == 
				TaskLevelPositionEnum::$starting) {

				$horizontal = $this->dependentTaskboxDrawInformation->pointInfo->horizontal -
					(TaskNetworkChartGenerator::$gapBetweenHorizontalAdiacentTaskBoxes / 2);
					
			}
			else {

				$horizontal = $this->dependentTaskboxDrawInformation->pointInfo->horizontal +
					(AbstractTaskDataDrawer::$width / 2);
				
			}
			
			return $horizontal;
		}
		
	}
	
	public function computeBackwardEntryPointInfo() {
		$vertical = null;
		if($this->dependencyDescriptor->dependentTaskPositionEnum == 
				TaskLevelPositionEnum::$starting) {
			
			if($this->dependentTaskboxDrawInformation->pointInfo->vertical > 
				$this->neededTaskboxDrawInformation->pointInfo->vertical) {
				
				$vertical = $this->dependentTaskboxDrawInformation->pointInfo->vertical - 
					DependencyLineInfo::$gapForFirstBackwardEntry;
			}
			else {
				$vertical = $this->dependentTaskboxDrawInformation->pointInfo->vertical +
					$this->dependentTaskboxDrawInformation->dependency->getDrawer()->computeHeight() + 
					DependencyLineInfo::$gapForFirstBackwardEntry;
			}
		}
		else {
			$vertical = $this->dependentTaskboxDrawInformation->pointInfo->vertical;	
		}
		
		return new PointInfo($this->computeHorizontal(), $vertical);
	}
	
	public function computeBackwardExitPointInfo() {
		$vertical = null;
		$horizontal = null;
		if($this->dependencyDescriptor->neededTaskPositionEnum == 
				TaskLevelPositionEnum::$ending) {
			
			$horizontal = $this->neededTaskboxDrawInformation->pointInfo->horizontal + 
				AbstractTaskDataDrawer::$width + DependencyLineInfo::$gapForFirstBackwardEntry;
				
			if($this->dependentTaskboxDrawInformation->pointInfo->vertical > 
				$this->neededTaskboxDrawInformation->pointInfo->vertical) {
				
				$vertical = $this->neededTaskboxDrawInformation->pointInfo->vertical +
					$this->neededTaskboxDrawInformation->dependency->getDrawer()->computeHeight() + 
					DependencyLineInfo::$gapForFirstBackwardEntry;
			}
			else {
				$vertical = $this->neededTaskboxDrawInformation->pointInfo->vertical - 
					DependencyLineInfo::$gapForFirstBackwardEntry;
			}
		}
		else {
			
			$horizontal = $this->neededTaskboxDrawInformation->pointInfo->horizontal + 
				(AbstractTaskDataDrawer::$width / 2);
			$vertical = $this->neededTaskboxDrawInformation->pointInfo->vertical +
					$this->neededTaskboxDrawInformation->dependency->getDrawer()->computeHeight();	
		}
		
		return new PointInfo($horizontal, $vertical);
	}
	
	public function computeNeededExitPointInfo() {
		if ($this->dependencyDescriptor->neededTaskPositionEnum == 
			TaskLevelPositionEnum::$ending) {
			return $this->neededTaskboxDrawInformation->dependency->getDrawer()->
				computeEndingExitPoint($this->neededTaskboxDrawInformation->pointInfo);		
		}
		else if($this->dependencyDescriptor->neededTaskPositionEnum == 
			TaskLevelPositionEnum::$inner) {
			return new PointInfo($this->neededTaskboxDrawInformation->pointInfo->horizontal +
				(AbstractTaskDataDrawer::$width / 2),
				$this->neededTaskboxDrawInformation->pointInfo->vertical +
				$this->neededTaskboxDrawInformation->dependency->getDrawer()->computeHeight());
		}
		else {
			DrawingHelper::debug("DependencyLineInfo::computeNeededExitPointInfo(): the execution shouldn't " . 
				"reach this point.");
		}
	}
	
	public function computeDependentEntryPointInfo() {
		if ($this->dependencyDescriptor->dependentTaskPositionEnum == 
			TaskLevelPositionEnum::$starting) {
			return $this->dependentTaskboxDrawInformation->dependency->getDrawer()->
				computeStartingEntryPoint($this->dependentTaskboxDrawInformation->pointInfo);		
		}
		else if($this->dependencyDescriptor->dependentTaskPositionEnum == 
			TaskLevelPositionEnum::$inner) {
			return new PointInfo($this->dependentTaskboxDrawInformation->pointInfo->horizontal +
				(AbstractTaskDataDrawer::$width / 2),
				$this->dependentTaskboxDrawInformation->pointInfo->vertical);
		}
		else {
			DrawingHelper::debug("DependencyLineInfo::computeDependentEntryPointInfo(): the execution shouldn't " . 
				"reach this point.");
		}
	}
	
	/**
	 * pixel
	 * @var int
	 */
	var $horizontalOffset;
	
	/**
	 * pixel
	 * @var int
	 */
	var $verticalOffset;
	
	/**
	 * task id of the needed task
	 * @var int
	 */
	var $neededTaskId;
	
	/**
	 * task id of the dependent task
	 * @var int
	 */
	var $dependentTaskId;
	
	/**
	 * number of dots to distinguish between a two dependency line
	 * @var integer
	 */
	var $dotsInPattern;
	
	var $replicateArrow;
}
?>