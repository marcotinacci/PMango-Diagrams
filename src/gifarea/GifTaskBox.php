<?php

require_once("./../taskdatatree/Task.php");

class GifTaskBox extends GifArea
{
	private $task;
	private $marked;
	private $collapsed;
	
	function __construct($x, $y, $width, $height, $task)
	{
		parent::__construct($x, $y, $width, $height);
		
		$row=$height/6;
		
		$this->subAreas['TaskName_box'] = new GifBox(0,0,$width,$row);
		$this->subAreas['TaskName_label'] = new GifLabel(2,2,$width-2,$row-2,"TaskName");
		
		$this->subAreas['Effort_box'] = new GifBox(0,$row,$width,$row);
		$this->subAreas['Effort_label'] = new GifLabel(2,$row+2,$width-2,$row-2,"Effort");
		
		$this->subAreas['PlannedData_box'] = new GifBox(0,2*$row,$width,$row);
		$this->subAreas['PlannedData_label'] = new GifLabel(2,2*$row+2,$width-2,$row-2,"PlannedData");
		
		$this->subAreas['PlannedTimeFrame_box'] = new GifBox(0,3*$row,$width,$row);
		$this->subAreas['PlannedTimeFrame_label'] = new GifLabel(2,3*$row+2,$width-2,$row-2,"PlannedTimeFrame");
		
		$this->subAreas['ActualData_box'] = new GifBox(0,4*$row,$width,$row);
		$this->subAreas['ActualData_label'] = new GifLabel(2,4*$row+2,$width-2,$row-2,"ActualData");
		
		$this->subAreas['Percentage']= new GifProgressBar(0, 5*$row ,$width, $row,30);
		
		$this->task = $task;
	}

	public function showTaskName($bool){
		$this->subAreas['TaskName_box']->visible = $bool;
		$this->subAreas['TaskName_label']->visible = $bool;
	}
		
	public function showEffort($bool){
		$this->subAreas['Effort_box']->visible = $bool;
		$this->subAreas['Effort_label']->visible = $bool;
	}	
	
	public function showPlannedData($bool){
		$this->subAreas['PlannedData_box']->visible = $bool;
		$this->subAreas['PlannedData_label']->visible = $bool;
	}	
	
	public function showPlannedTimeFrame($bool){
		$this->subAreas['PlannedTimeFrame_box']->visible = $bool;
		$this->subAreas['PlannedTimeFrame_label']->visible = $bool;	
	}
	
	public function showActualData($bool){
		$this->subAreas['ActualData_box']->visible = $bool;
		$this->subAreas['ActualData_label']->visible = $bool;
	}	

	private function showPercentage($bool){
		$this->subAreas['Percentage']->visible = $bool;
	}
	
}

?>