<?php

/* This class print a progress bar and is an example of high level composition, infacts
 * there is no override of the method canvasDraw.
 */

require_once "./GifArea.php";
require_once "./GifCircle.php";
require_once "./GifLabel.php";

class GifMark extends GifArea
{	
	function __construct($x, $y, $width, $priority)
	{
		parent::__construct($x, $y, $width, $width);
	
		$this->subAreas[0]=new GifCircle(0,0,$width/2);
		$this->subAreas[0]->setForeColor("white");
		
		$delta = '&#916;';
		
		$fontsize = ($width/2);
		
		$txt = $delta;
		if($priority==1)
			$txt.="!";
		
		$xc = (0-$width/2)-6;
		$yc = (0-$width/2)+2;
		
		$this->subAreas[1]=new GifLabel($xc,$yc,$width+10,$width,$txt,$fontsize);
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