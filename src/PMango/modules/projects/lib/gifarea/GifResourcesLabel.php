<?php

require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";

/* This is class print a rectangle to the gif */
class GifResourcesLabel extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";
	
	private $effectiveWidth = 0;
	private $truncateRest = "...";
	
	private $separator="";
	
	private $act_w = 0;
	private $fontSize = 10;
	private $text;

	public function __construct($gifImage, $x, $y, $width, $height, $fontSize, $resource, $separator="")
	{
		parent::__construct($gifImage, $x,$y,$width,$height);
		
		$x_start = $this->x;
		$this->act_w = 0;
		$this->fontSize = $fontSize;
		if(isset($resource['ActualEffort']) && $resource['ActualEffort']!="")
		{
			$this->act_w = GifLabel::getPixelWidthOfText($resource['ActualEffort']."/",$fontSize)+1;
			$this->subAreas['Label_Actual']= new GifLabel($gifImage, $this->x, $this->y, $this->act_w, $height, $resource['ActualEffort']."/", $fontSize);
			$this->subAreas['Label_Actual']->setUnderline(true);
			$this->subAreas['Label_Actual']->setHAlign("left");
			$x_start+=$this->act_w;
		}
		$this->text = $this->truncateResourceData($resource,$width-$this->act_w).$separator;
		$this->subAreas['Label_Data']= new GifLabel($gifImage, $x_start, $this->y, $width-$this->act_w, $height, $this->text, $fontSize);
		$this->subAreas['Label_Data']->setHAlign("left");
		$this->subAreas['Label_Data']->setTruncate($this->truncateRest.$separator);
		$this->effectiveWidth = $this->act_w + GifLabel::getTruncatedPixelWidthOfText($this->text,$width-$this->act_w,$this->truncateRest,$fontSize);
		//$this->effectiveWidth = $act_w + GifLabel::getPixelWidthOfText($text,$fontSize);
	}
	
	public function getEffectiveWidth()
	{
		return $this->effectiveWidth;
	}
	
	public function setTruncate($value)
	{
		$this->subAreas['Label_Data']->setTruncate($value);
		$this->truncateRest = $value.$this->separator;
		$this->effectiveWidth = $this->act_w + GifLabel::getTruncatedPixelWidthOfText($this->text,$width-$this->act_w,$this->truncateRest,$this->fontSize);
		//$this->effectiveWidth = $this->act_w + GifLabel::getPixelWidthOfText($this->text,$this->fontSize);
		DrawingHelper::debug("<b>EffectiveWidth of resource ".$this->text." now is ".$this->effectiveWidth."</b>");
	}
	
	private function truncateResourceData($res,$width)
	{
		$txt = $res['Effort']." ".$res['LastName']." ".$res['FirstName']." ".$res['Role'];
		if(GifLabel::getPixelWidthOfText($txt)>$width)
		{
			$fName = substr($res['FirstName'],0,1);
			$txt = $res['Effort']." ".$res['LastName']." $fName. ".$res['Role'];
			if(GifLabel::getPixelWidthOfText($txt)>$width)
			{
				$lName = substr($res['LastName'],0,1);
				$txt = $res['Effort']." $lName. $fName. ".$res['Role'];
			}
		}
		DrawingHelper::debug("<b>Truncated resource to $width is $txt</b>");
		return $txt;
	}

}

?>