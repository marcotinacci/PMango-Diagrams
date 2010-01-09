<?php
class CriticalPathDomainObject {
	private $duration;
	private $totalEffort;
	private $totalCost;
	private $lastGap;
	private $isShowed;
	private $chain = array();

	public function increaseDurationOf($days) {
		$this->duration += $days;
	}

	public function increaseTotalEffortOf($efforts) {
		$this->totalEffort += $efforts;
	}

	public function increaseTotalCostOf($cost) {
		$this->totalCost += $cost;
	}

	public function setLastGap($lastGap) {
		$this->lastGap = $lastGap;
	}

	public function appendTaskNode($task_id) {
		$this->chain[] = $task_id;
	}

	public function getDuration() {
		return $this->duration;
	}

	public function getTotalEffort() {
		return $this->totalEffort;
	}

	public function getTotalCost() {
		return $this->totalCost;
	}

	public function getLastGap() {
		return $this->lastGap;
	}

	public function getChain() {
		return $this->chain;
	}

	public function isShowed() {
		return $this->isShowed;
	}

	public function setIsShowed($isShowed) {
		$this->isShowed = $isShowed;
	}

	public function getImplodedChain($glue) {
		return implode($glue, $this->chain);
	}
}
?>