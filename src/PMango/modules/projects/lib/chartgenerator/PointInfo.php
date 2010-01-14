<?php 
class PointInfo {
	var $vertical;
	var $horizontal;
	
	public function __toString() {
		return "(" . $this->horizontal . ", " . $this->vertical . ")";
	}
}
?>