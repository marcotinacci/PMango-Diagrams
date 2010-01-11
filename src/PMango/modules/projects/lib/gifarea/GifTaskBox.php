<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/../taskdatatree/DeltaInfoEnum.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifMark.php";
require_once dirname(__FILE__)."/GifBoxedLabel.php";
require_once dirname(__FILE__)."/../useroptionschoice/UserOptionsChoice.php";

class GifTaskBox extends GifArea
{
	private $task;
	private $marked;
	private $effectiveHeight=0;

	function __construct($x, $y, $width, $singleRowHeight, $task)
	{
		parent::__construct($x, $y, $width, $singleRowHeight*7);

		$row=$singleRowHeight;
		//$module = $height%6;
		$fontHeight = $row/3;

		$curY = 0;

		//$uoc = new UserOptionsChoice();
		$uoc = UserOptionsChoice::GetInstance();
		$tName = $task->getInfo()->getWBSId();
		if($uoc->showTaskNameUserOption())
		$tName .= " ".$task->getInfo()->getTaskName();
		$this->subAreas['TaskName_box'] = new GifBoxedLabel(0,$curY,$width,$row,$tName,$fontHeight);
		$this->subAreas['TaskName_box']->getLabel()->setBold(true);
		$curY += $row;

		$doubleSubBoxWidth = intval($width/2);
		$doubleSubBoxPixelCarry = $width%2;

		$tripleSubBoxWidth = intval($width/3);
		$tripleSubBoxPixelCarry = $width%3;

		$date_format = "o.m.d";
		
		if($uoc->showPlannedTimeFrameUserOption())
		{
			$ptf = $task->getInfo()->getPlannedTimeFrame();
			$PlannedTimeFrame_start = "".date($date_format,strtotime($ptf['start_date']));
			$PlannedTimeFrame_finish = "".date($date_format,strtotime($ptf['finish_date']));
			$this->subAreas['PlannedTimeFrame_box_start'] = new GifBoxedLabel(0,$curY,$doubleSubBoxWidth,$row,$PlannedTimeFrame_start,$fontHeight);
			$this->subAreas['PlannedTimeFrame_box_finish'] = new GifBoxedLabel($doubleSubBoxWidth,$curY,$doubleSubBoxWidth,$row,$PlannedTimeFrame_finish,$fontHeight);
			$curY += $row;
		}

		if($uoc->showPlannedDataUserOption())
		{
			$planned = $task->getInfo()->getPlannedData();
			$Planned_D = "".$planned["duration"];
			$Planned_PH = "".$planned["effort"];
			$Planned_Money = "".$planned["cost"];
			$this->subAreas['PlannedData_box_D'] = new GifBoxedLabel(0,$curY,$tripleSubBoxWidth,$row,$Planned_D,$fontHeight);
			$this->subAreas['PlannedData_box_PH'] = new GifBoxedLabel($tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row,$Planned_PH,$fontHeight);
			$this->subAreas['PlannedData_box_Money'] = new GifBoxedLabel(2*$tripleSubBoxWidth,$curY,$tripleSubBoxWidth+$tripleSubBoxPixelCarry,$row,$Planned_Money,$fontHeight);
			$curY += $row;
		}

		if($uoc->showResourcesUserOption())
		{
			$res=$task->getInfo()->getResources();
			$resRowSize = $row-($row/3);
			$resHeight = $resRowSize*sizeOf($res);
			$this->subAreas['ResourcesBox'] = new GifBox(0,$curY,$width,$resHeight);
			$curY += 4;
			for($i=0; $i<sizeOf($res); $i++)
			{
				$txt = $res[$i]['PlannedEffort'].", ".$res[$i]['ResourceName'].", ".$res[$i]['Role'];
					
				$index = "ResourceLabel_".$i;
				$this->subAreas[$index] = new GifLabel(6,$curY,$width-6,$resRowSize,$txt,$fontHeight);
				$this->subAreas[$index]->setHAlign("left");
				$curY += $resRowSize-4;
			}
			$curY += 4;
		}
		if($uoc->showActualTimeFrameUserOption())
		{
			$atf = $task->getInfo()->getActualTimeFrame();
			$ActualTimeFrame_start = "".date($date_format,strtotime($atf['start_date']));
			$ActualTimeFrame_finish = "".date($date_format,strtotime($atf['finish_date']));
			
			$this->subAreas['ActualTimeFrame_box_start'] = new GifBoxedLabel(0,$curY,$doubleSubBoxWidth,$row,$ActualTimeFrame_start,$fontHeight);
			$this->subAreas['ActualTimeFrame_box_finish'] = new GifBoxedLabel($doubleSubBoxWidth,$curY,$doubleSubBoxWidth,$row,$ActualTimeFrame_finish,$fontHeight);
			$curY += $row;
		}

		if($uoc->showActualDataUserOption())
		{
			$actual = $task->getInfo()->getActualData();
			$actual_D = "".$actual["duration"];
			$actual_PH = "".$actual["effort"];
			$actual_Money = "".$actual["cost"];
			$this->subAreas['ActualData_box_D'] = new GifBoxedLabel(0,$curY,$tripleSubBoxWidth,$row,$actual_D,$fontHeight);
			$this->subAreas['ActualData_box_PH'] = new GifBoxedLabel($tripleSubBoxWidth,$curY,$tripleSubBoxWidth,$row,$actual_PH,$fontHeight);
			$this->subAreas['ActualData_box_Money'] = new GifBoxedLabel(2*$tripleSubBoxWidth,$curY,$tripleSubBoxWidth+$tripleSubBoxPixelCarry,$row,$actual_Money,$fontHeight);
			$curY += $row;
		}

		if($uoc->showActualDataUserOption())
		{
			$this->subAreas['Percentage']= new GifProgressBar(0, $curY ,$width, intval($row/4),$task->getInfo()->getPercentage());
			$curY += intval($row/4);
		}
		$this->subAreas['CompleteBox'] = new GifBox(0,0,$width,$curY);
		$this->subAreas['CompleteBox']->setBorderThickness(2);

		if($uoc->showAlertMarkUserOption())
		{
			if($task->isMarked() == DeltaInfoEnum::$good_news)
			$this->subAreas['Mark']= new GifMark($width, 0 ,$row, 0);
			else if($task->isMarked() == DeltaInfoEnum::$bad_news)
			$this->subAreas['Mark']= new GifMark($width, 0 ,$row, 1);
		}
		$this->task = $task;

		$this->effectiveHeight=$curY;
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

		$index = "ResourceLabel_0";
		while(isset($this->subAreas[$index]))
		{
			$this->subAreas[$index]->setFontSize($size);
			$i++;
			$index = "ResourceLabel_".$i;
		}
	}

	public function getEffectiveHeight()
	{
		return $this->effectiveHeight;
	}

	public static function getTaskBoxesBestWidth($taskDataTree,$userOptionChoise,$fontSize,$font)
	{
		$max = 0;
		$taskBoxes=$taskDataTree->deepVisit();
		foreach($taskBoxes as $taskBox)
		{
			$wbsIdWidth = GifLabel::getPixelWidthOfText($taskBox->getInfo()->getWBSId(),$fontSize,$font);

			$boxMax = $wbsIdWidth;
			//TODO: va moltiplicato per 2 se ha chiesto i nomi nelle useroptions

			//TODO: Anche questo va fatto solo se ha chiesto delle date nella useroptionchoice
			$dateWidth = GifLabel::getPixelWidthOfText("0000.00.00",$fontSize,$font);
			if($dateWidth > $boxMax)
			$boxMax = $dateWidth*2+5;

			//TODO: Anche questo va fatto solo se ha chiesto gli actual nella useroptionchoice
			$actualData = $taskBox->getInfo()->getActualData();
			$actualTripleW = GifTaskBox::getBestWidthOfMultipleDataRow($actualData,$fontSize,$font);
			if($actualTripleW > $boxMax)
			$boxMax = $actualTripleW;

			//TODO: Anche questo va fatto solo se ha chiesto gli actual nella useroptionchoice
			$plannedData = $taskBox->getInfo()->getPlannedData();
			$plannedTripleW = GifTaskBox::getBestWidthOfMultipleDataRow($plannedData,$fontSize,$font);
			if($plannedTripleW > $boxMax)
			$boxMax = $plannedTripleW;

			if($boxMax > $max)
			$max = $boxMax;
		}
		//TODO: moltiplicare per 2 se specifica ShowTaskboxName
		return $max+10;
	}

	private static function getBestWidthOfMultipleDataRow($data,$fontSize,$font)
	{
		$actW=array();
		foreach($data as $value)
		{
			$actW[] = GifLabel::getPixelWidthOfText($value,$fontSize,$font);
		}
		return sizeOf($actW)*(max($actW)+5);
	}

	public function getTopMiddlePoint()
	{
		$point = $this->subAreas['CompleteBox']->getTopMiddlePoint();
		$point['x']+=$this->x;
		$point['y']+=$this->y;
		return $point;
	}

	public function getBottomMiddlePoint()
	{
		$point = $this->subAreas['CompleteBox']->getBottomMiddlePoint();
		$point['x']+=$this->x;
		$point['y']+=$this->y;
		return $point;
	}

	public function getLeftMiddlePoint()
	{
		$point = $this->subAreas['CompleteBox']->getLeftMiddlePoint();
		$point['x']+=$this->x;
		$point['y']+=$this->y;
		return $point;
	}

	public function getRightMiddlePoint()
	{
		$point = $this->subAreas['CompleteBox']->getRightMiddlePoint();
		$point['x']+=$this->x;
		$point['y']+=$this->y;
		return $point;
	}

	public static function getEffectiveHeightOfTaskBox($taskData,$singleRowHeight,$useroption)
	{
		$g = new GifTaskBox(0,0,100,$singleRowHeight,$taskData,$useroption);
		$height = $g->getEffectiveHeight();
		unset($g);
		return $height;
	}

	public function getTaskData()
	{
		return $this->task;
	}

}

?>