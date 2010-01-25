<?php

require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";

/* This is class print a rectangle to the gif */
class GifResourcesLabel extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";

	public function __construct($gifImage, $x, $y, $width, $height, $fontSize, $resource)
	{
		parent::__construct($gifImage, $x,$y,$width,$height);
		
		$x_start = $this->x;
		$act_w = 0;
		if(isset($resource['ActualEffort']) && $resource['ActualEffort']!="")
		{
			$act_w = GifLabel::getPixelWidthOfText($resource['ActualEffort']."/",$fontSize)+1;
			$this->subAreas['Label_Actual']= new GifLabel($gifImage, $this->x, $this->y, $act_w, $height, $resource['ActualEffort']."/", $fontSize);
			$this->subAreas['Label_Actual']->setUnderline(true);
			$this->subAreas['Label_Actual']->setHAlign("left");
			$x_start+=$act_w;
		}
		$text = $this->truncateResourceData($resource,$width-$act_w);
		$this->subAreas['Label_Data']= new GifLabel($gifImage, $x_start, $this->y, $width-$act_w, $height, $text, $fontSize);
		$this->subAreas['Label_Data']->setHAlign("left");
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
		return $txt;
	}

}

?>