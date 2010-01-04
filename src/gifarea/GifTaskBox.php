<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifMark.php";

class GifTaskBox extends GifArea
{
	private $task;
	private $marked;
	
	function __construct($x, $y, $width, $singleRowHeight, $task)
	{
		parent::__construct($x, $y, $width, $height);
		
		$row=$singleRowHeight;
		//$module = $height%6;
		$fontHeight = $row-6;
		
		$curY = 0;
		
		$this->subAreas['TaskName_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['TaskName_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,$task->getInfo()->getTaskName(),$fontHeight);
		
		$curY += $row;
		$tripleSubBoxWidth = int_val($width/3);
		$tripleSubBoxPixelCarry = $width%3;
		
		$planned = $task->getInfo()->getPlannedData();
		$Planned_D = $planned["duration"];
		$Planned_PH = $planned["effort"];
		$Planned_Money = $planned["cost"];
		$this->subAreas['PlannedData_box_D'] = new GifBox(0,$curY,$tripleSubBoxWidth,$row);
		$this->subAreas['PlannedData_box_PH'] = new GifBox($tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row);
		$this->subAreas['PlannedData_box_Money'] = new GifBox(2*$tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row);
		
		$curY += $row;
		$this->subAreas['PlannedTimeFrame_box'] = new GifBox(0,$curY,$width,$row);
		$this->subAreas['PlannedTimeFrame_label'] = new GifLabel(2,$curY+2,$width-2,$row-2,"PlannedTimeFrame",$fontHeight);
		
		$curY += $row;
		$actual = $task->getInfo()->getActualData();
		$actual_D = $actual["duration"];
		$actual_PH = $actual["effort"];
		$actual_Money = $actual["cost"];
		$this->subAreas['ActualData_box_D'] = new GifBox(0,$curY,$tripleSubBoxWidth,$row);
		$this->subAreas['ActualData_label_D'] = new GifLabel(2,$curY+2,$tripleSubBoxWidth-2,$row-2,$actual_D,$fontHeight);
		$this->subAreas['ActualData_box_PH'] = new GifBox($tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row);
		$this->subAreas['ActualData_label_PH'] = new GifLabel($tripleSubBoxWidth+2,$curY+2,$tripleSubBoxWidth-2,$row-2,$actual_PH,$fontHeight);
		$this->subAreas['ActualData_box_Money'] = new GifBox(2*$tripleSubBoxWidth,$curY,$width,$row);
		$this->subAreas['ActualData_label_Money'] = new GifLabel(2,$curY+2,$tripleSubBoxWidth-2,$row-2,$actual_Money,$fontHeight);
		
		$curY += $row;
		$this->subAreas['Percentage']= new GifProgressBar(0, $curY ,$width, int_val($row/2),30);

		$this->subAreas['Mark']= new GifMark($width, 0 ,$width/5, 1);
		
		$this->task = $task;
	}
	
	public function setVisiblesFromOptionsChoice($userOptionsChoice)
	{
		//TODO: Appena Ema ha fatto setta la roba basandosi su userOptionsChoice
		
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