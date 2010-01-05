<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifMark.php";
require_once dirname(__FILE__)."/GifBoxedLabel.php";

class GifTaskBox extends GifArea
{
	private $task;
	private $marked;
	
	function __construct($x, $y, $width, $singleRowHeight, $task, $useroptionchoise=null)
	{
		parent::__construct($x, $y, $width, $singleRowHeight*7);
		
		$row=$singleRowHeight;
		//$module = $height%6;
		$fontHeight = $row/3;
		
		$curY = 0;
		
		$this->subAreas['TaskName_box'] = new GifBoxedLabel(0,$curY,$width,$row,$task->getInfo()->getWBSId()." ".$task->getInfo()->getTaskName(),$fontHeight);
		$this->subAreas['TaskName_box']->getLabel()->setBold(true);
		
		$doubleSubBoxWidth = intval($width/2);
		$doubleSubBoxPixelCarry = $width%2;
		
		$tripleSubBoxWidth = intval($width/3);
		$tripleSubBoxPixelCarry = $width%3;
		
		$curY += $row;
		$ptf = $task->getInfo()->getPlannedTimeFrame();
		$PlannedTimeFrame_start = $ptf['start_date'];
		$PlannedTimeFrame_finish = $ptf['finish_date'];
		$this->subAreas['PlannedTimeFrame_box_start'] = new GifBoxedLabel(0,$curY,$doubleSubBoxWidth,$row,$PlannedTimeFrame_start,$fontHeight);
		$this->subAreas['PlannedTimeFrame_box_finish'] = new GifBoxedLabel($doubleSubBoxWidth,$curY,$doubleSubBoxWidth,$row,$PlannedTimeFrame_finish,$fontHeight);
		
		$curY += $row;
		$planned = $task->getInfo()->getPlannedData();
		$Planned_D = $planned["duration"];
		$Planned_PH = $planned["effort"];
		$Planned_Money = $planned["cost"];
		$this->subAreas['PlannedData_box_D'] = new GifBoxedLabel(0,$curY,$tripleSubBoxWidth,$row,$Planned_D,$fontHeight);
		$this->subAreas['PlannedData_box_PH'] = new GifBoxedLabel($tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row,$Planned_PH,$fontHeight);
		$this->subAreas['PlannedData_box_Money'] = new GifBoxedLabel(2*$tripleSubBoxWidth,$curY,$tripleSubBoxWidth+$tripleSubBoxPixelCarry,$row,$Planned_Money,$fontHeight);
		
		$curY += $row;
		$res=$task->getInfo()->getResources();
		$resRowSize = $row-($row/3);
		$resHeight = $resRowSize*sizeOf($res);
		$this->subAreas['ResourcesBox'] = new GifBox(0,$curY,$width,$resHeight);
		$curY += 4;
		for($i=0; $i<sizeOf($res); $i++)
		{
			$txt = $res[$i]['PlannedEffort'].", ".$res[$i]['ResourceName'].", ".$res[$i]['Role'];
			
			$index = "ResourceLabel_".$i;
			$this->subAreas[$index] = new GifLabel(2,$curY,$width-2,$resRowSize,$txt,$fontHeight);
			$curY += $resRowSize-4;
		}
		$curY += 4;
		$atf = $task->getInfo()->getActualTimeFrame();
		$ActualTimeFrame_start = $atf['start_date'];
		$ActualTimeFrame_finish = $atf['finish_date'];
		$this->subAreas['ActualTimeFrame_box_start'] = new GifBoxedLabel(0,$curY,$doubleSubBoxWidth,$row,$ActualTimeFrame_start,$fontHeight);
		$this->subAreas['ActualTimeFrame_box_finish'] = new GifBoxedLabel($doubleSubBoxWidth,$curY,$doubleSubBoxWidth,$row,$ActualTimeFrame_finish,$fontHeight);
		
		$curY += $row;
		$actual = $task->getInfo()->getActualData();
		$actual_D = $actual["duration"];
		$actual_PH = $actual["effort"];
		$actual_Money = $actual["cost"];
		$this->subAreas['ActualData_box_D'] = new GifBoxedLabel(0,$curY,$tripleSubBoxWidth,$row,$actual_D,$fontHeight);
		$this->subAreas['ActualData_box_PH'] = new GifBoxedLabel($tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row,$actual_PH,$fontHeight);
		$this->subAreas['ActualData_box_Money'] = new GifBoxedLabel(2*$tripleSubBoxWidth,$curY,$tripleSubBoxWidth+$tripleSubBoxPixelCarry,$row,$actual_Money,$fontHeight);
		
		$curY += $row;
		$this->subAreas['Percentage']= new GifProgressBar(0, $curY ,$width, intval($row/4),$task->getInfo()->getPercentage());
		
		$this->subAreas['CompleteBox'] = new GifBox(0,0,$width,$curY+intval($row/4));
		$this->subAreas['CompleteBox']->setBorderThickness(2);
		
		$this->subAreas['Mark']= new GifMark($width, 0 ,$row, 1);
		
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
		$this->subAreas['TaskName_box']->getLabel()->setFontSize($size);
		$this->subAreas['PlannedTimeFrame_box_start']->getLabel()->setFontSize($size);
		$this->subAreas['PlannedTimeFrame_box_finish']->getLabel()->setFontSize($size);
		$this->subAreas['PlannedData_box_D']->getLabel()->setFontSize($size);
		$this->subAreas['PlannedData_box_PH']->getLabel()->setFontSize($size);
		$this->subAreas['PlannedData_box_Money']->getLabel()->setFontSize($size);
		$this->subAreas['ActualTimeFrame_box_start']->getLabel()->setFontSize($size);
		$this->subAreas['ActualTimeFrame_box_finish']->getLabel()->setFontSize($size);
		$this->subAreas['ActualData_box_D']->getLabel()->setFontSize($size);
		$this->subAreas['ActualData_box_PH']->getLabel()->setFontSize($size);
		$this->subAreas['ActualData_box_Money']->getLabel()->setFontSize($size);
	}
}

?>