<?php

class GifProgressBar extends GifArea
{
	function __construct($x, $y, $width, $height, $completed)
	{
		parent::__construct($x, $y, $width, $height);
		$p = $this->getPercentagePixels($completed,$width);
		$this->subAreas[0]=new GifBox(0, 0, $p, $height);
		$this->subAreas[0]->setForeColor("blue");
		$this->subAreas[1]=new GifBox($p, 0, $width-$p, $height);
		$this->subAreas[1]->setForeColor("gray");
	}
	
	private function getPercentagePixels($percentage,$maxPixels)
	{
		return ($percentage*$maxPixels)/100;
	}
}

?>