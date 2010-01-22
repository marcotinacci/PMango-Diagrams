<?php 
class PointInfo {
	var $vertical;
	var $horizontal;
	
	public function __construct($horizontal, $vertical) {
		$this->horizontal = $horizontal;
		$this->vertical = $vertical;
	}
	
	public function __toString() {
		return "(" . $this->horizontal . ", " . $this->vertical . ")";
	}
}
?>