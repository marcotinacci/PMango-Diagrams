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

class GifRootTaskBox extends GifArea
{
	private $task;
	private $marked;
	private $effectiveHeight=0;

	function __construct($gifImage,$x, $y, $width, $singleRowHeight, $project, &$uoc)
	{
		parent::__construct($gifImage, $x, $y, $width, $singleRowHeight);

		$row=$singleRowHeight;
		//$module = $height%6;
		$fontHeight = $row/3;

		$curY = $this->y;

		//$uoc = new UserOptionsChoice();
		
		$tName = $project->getProjectName();
		$this->subAreas['ProjectName_box'] = new GifBoxedLabel($gifImage,$this->x,$curY,$width,$row,$tName,$fontHeight);
		$this->subAreas['ProjectName_box']->getLabel()->setBold(true);
		$curY += $row;

		$this->subAreas['CompleteBox'] = new GifBox($gifImage,$this->x,$this->y,$width,$curY-$this->y);
		$this->subAreas['CompleteBox']->setBorderThickness(2);

		$this->effectiveHeight=$curY-$this->y;
	}

	public function getFontsize()
	{
		return $this->subAreas['TaskName_label']->getFontSize();
	}

	public function setFontSize($size)
	{
		$this->subAreas['ProjectName_box']->getLabel()->setFontSize($size);
	}

	public function getEffectiveHeight()
	{
		return $this->effectiveHeight;
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
		$g = new GifTaskBox(null,0,0,100,$singleRowHeight,$taskData,$useroption);
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