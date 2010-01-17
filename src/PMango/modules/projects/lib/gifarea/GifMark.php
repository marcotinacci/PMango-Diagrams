<?php

/* This class print a progress bar and is an example of high level composition, infacts
 * there is no override of the method canvasDraw.
 */

require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifCircle.php";
require_once dirname(__FILE__)."/GifLabel.php";

class GifMark extends GifArea
{	
	function __construct($gifImage, $x, $y, $width, $priority)
	{
		parent::__construct($gifImage, $x, $y, $width, $width);
	
		$this->subAreas[0]=new GifCircle($gifImage,$this->x,$this->y,intval($width/2));
		$this->subAreas[0]->setForeColor("white");
		$this->subAreas[0]->setBorderThickness(2);
		
		$delta = '&#916;';
		
		$fontsize = ($width/2);
		
		$txt = $delta;
		if($priority==1)
			$txt.="!";
		
		$xc = $this->x+(0-$width/2)-6;
		$yc = $this->y+(0-$width/2)-2;
		
		$this->subAreas[1]=new GifLabel($gifImage,$xc,$yc,$width+10,$width,$txt,$fontsize);
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

}

?>