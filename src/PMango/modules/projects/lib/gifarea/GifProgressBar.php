<?php

/* This class print a progress bar and is an example of high level composition, infacts
 * there is no override of the method canvasDraw.
 */

require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";

class GifProgressBar extends GifArea
{	
	function __construct($x, $y, $width, $height, $completed)
	{
		parent::__construct($x, $y, $width, $height);
		$p = $this->getPercentagePixels($completed,$width);
		if($completed)
		{
			$this->subAreas[0]=new GifBox(0, 0, $p, $height);
			$this->subAreas[0]->setForeColor("#7F7F7F");
			$this->subAreas[1]=new GifBox($p, 0, $width-$p, $height);
			$this->subAreas[1]->setForeColor("white");
		}
		else
		{
			$this->subAreas[0]=new GifBox(0, 0, $width, $height);
			$this->subAreas[0]->setForeColor("#7F7F7F");
			//$this->subAreas[1]=new GifBox($p, 0, 0, $height);
			//$this->subAreas[1]->setForeColor("white");
		}
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