<?php
require_once "../gifarea/GifTaskBox.php";
require_once "../gifarea/GifAreaTests.php";

class WBSChartGenerator extends ChartGenerator{
	
	public function generateChart(){
		// parent o this??
		// TODO: tdt var di istanza
		$tdt = $this->$tdtGenerator->generateTaskDataTree($_SESSION["useroptionschoice"]);
		$this->makeWBSTaskNode();
		$this->makeWBSDependencies();
		$chart->draw();
	}
	
	protected function makeWBSTaskNode(){
		$draw = new GifTaskBox();					
	}
	
	protected function makeWBSDependencies(){
		
	}
}


?>