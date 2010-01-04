<?php

//require_once dirname(__FILE__)."/../taskdatatree/Task.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifMark.php";

class GifTaskBox extends GifArea
{
	private $task;
	private $marked;
	private $collapsed;
	
	function __construct($x, $y, $width, $height, $task)
	{
		parent::__construct($x, $y, $width, $height);
		
		$row=intval($height/6);
		$module = $height%6;
		$fontHeight = $row-6;
		
		$curY = 0;
		
		$this->subAreas['TaskName_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['TaskName_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,"TaskName",$fontHeight);
		
		$curY += $row;
		$this->subAreas['Effort_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['Effort_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,"Effort",$fontHeight);
		
		$curY += $row;
		$this->subAreas['PlannedData_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['PlannedData_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,"PlannedData",$fontHeight);
		
		$curY += $row;
		$this->subAreas['PlannedTimeFrame_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['PlannedTimeFrame_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,"PlannedTimeFrame",$fontHeight);
		
		$curY += $row;
		$this->subAreas['ActualData_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['ActualData_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,"ActualData",$fontHeight);
		
		$curY += $row;
		$this->subAreas['Percentage']= new GifProgressBar(0, $curY ,$width, $row+$module,30);

		$this->subAreas['Mark']= new GifMark($width, 0 ,$width/5, 1);
		
		$this->task = $task;
	}
	
	public function getFontsize()
	{
		return $this->subAreas['TaskName_label']->getFontSize();
	}
	
	public function setFontSize($size)
	{
		$this->subAreas['TaskName_label']->setFontSize($size);
		$this->subAreas['Effort_label']->setFontSize($size);
		$this->subAreas['PlannedData_label']->setFontSize($size);
		$this->subAreas['PlannedTimeFrame_label']->setFontSize($size);
		$this->subAreas['ActualData_label']->setFontSize($size);
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

	public function showPercentage($bool){
		$this->subAreas['Percentage']->visible = $bool;
	}
	
	public function showMark($bool,$priority=0){
		$this->subAreas['Mark']->visible = $bool;
	}
}

?>