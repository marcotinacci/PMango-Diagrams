<?php
require_once dirname(__FILE__) . "/../commons/DateComparer.php";
require_once dirname(__FILE__) . "/../taskdatatree/Task.php";

class CriticalPathDomainObject {
	public static $lastGap;
	public static $firstGap;
	var $chain = array();
	var $isValid = false;
	
	public function getClone() {
		$clone = new CriticalPathDomainObject();
		
		$clone->chain = array();
		
		foreach ($this->chain as $task_id) {
			$clone->chain[] = $task_id;
		}
		
		return $clone;
	}

	public function getDuration() {
		
		if(count($this->chain) == 0) {
			$dateComparer = new DateComparer(CriticalPathDomainObject::$lastGap);
			return $dateComparer->substract(CriticalPathDomainObject::$firstGap);
		}
		
		$result = 0;
		$previousDate = CriticalPathDomainObject::$firstGap;
		
		for ($runner = 0; $runner < count($this->chain); $runner++) {
			$task_id = $this->chain[$runner];
			$task = Task::MakeTask($task_id);
			$currentDate = $task->getCTask()->task_finish_date;
			
			$comparer = new DateComparer($currentDate);
			$result += $comparer->substract($previousDate); 
			
			$previousDate = $currentDate;	
		}
		
		$dateComparer = new DateComparer(CriticalPathDomainObject::$lastGap);
		return $dateComparer->substract($previousDate);
		
		return $result;
	}

	public function getTotalEffort() {
		$result = 0;
		foreach ($this->chain as $task_id) {
			$task = Task::MakeTask($task_id);
			$result += $task->getPlannedEffort();
		}
		return $result;
	}

	public function getTotalCost() {
		$result = 0;
		foreach ($this->chain as $task_id) {
			$task = Task::MakeTask($task_id);
			$result += $task->getPlannedCost();
		}
		return $result;
	}

	public function getLastGap() {
		if(count($this->chain) == 0) {
			$dateComparer = new DateComparer(CriticalPathDomainObject::$lastGap);
			return $dateComparer->substract(CriticalPathDomainObject::$firstGap);
		}
		else {
			$dateComparer = new DateComparer(CriticalPathDomainObject::$lastGap);
			$lastTask = Task::MakeTask($this->chain[count($this->chain) - 1]);
			return $dateComparer->substract($lastTask->getCTask()->task_finish_date);
		}
	}

	public function getChain() {
		return $this->chain;
	}
	
	public function getImplodedChain($glue) {
		return implode($glue, $this->chain);
	}
	
	public function getWBSChain() {
		$array = array();
		foreach ($this->chain as $task_id) {
			$task = Task::MakeTask($task_id);
			$array[] = $task->getWBSId();
		}
		return implode(" -> ", $array);
	}
	
	public function pairIsOntoCriticalPath($needed_task_id, $dependent_task_id) {
		$ret = false;
		for($runner = 0; $runner < count($this->chain) - 1; $runner++) {
			if($this->chain[$runner] == $needed_task_id &&
				$this->chain[$runner + 1] == $dependent_task_id) {
				return true;		
			}
		}	
		return $ret;
	}
}
?>