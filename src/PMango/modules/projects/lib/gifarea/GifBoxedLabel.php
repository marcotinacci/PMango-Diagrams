<?php

require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";

/* This is class print a rectangle to the gif */
class GifBoxedLabel extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";

	public function __construct($gifImage, $x, $y, $width, $height, $text, $fontSize=10)
	{
		parent::__construct($gifImage, $x,$y,$width,$height);
		
		$this->subAreas['Box']= new GifBox($gifImage, $this->x, $this->y, $width, $height);
		$this->subAreas['Label']= new GifLabel($gifImage, $this->x+2, $this->y+2, $width-2, $height-2, $text, $fontSize);
	}

	public function getBox()
	{
		return $this->subAreas['Box'];
	}
	
	public function getLabel()
	{
		return $this->subAreas['Label'];
	}

}

?>