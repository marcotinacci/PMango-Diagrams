<?php

/* This class print a progress bar and is an example of high level composition, infacts
 * there is no override of the method canvasDraw.
 */
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
	
	/* Set the forecolor of the filled bar */
	public function setFilledForeColor($color)
	{
		$this->subAreas[0]->setForeColor($color);
	}
	
	/* Set the forecolor of the empty bar */
	public function setEmptyForeColor($color)
	{
		$this->subAreas[1]->setForeColor($color);
	}
	
	/* Calculate the completed pixels in proportion */
	private function getPercentagePixels($percentage,$maxPixels)
	{
		return ($percentage*$maxPixels)/100;
	}
}

?>