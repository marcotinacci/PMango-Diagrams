<?php

class GifTaskBox extends GifArea
{
	function __construct($x, $y, $width, $height)
	{
		parent::__construct($x, $y, $width, $height);
		
		$row=$height/5;
		
		$this->subAreas[0]= new GifBox(0,0,$width, $row);
		$this->subAreas[1]= new GifLabel(2,2,$width-2, $row-2, "TaskBox");
		
		$this->subAreas[2]= new GifBox(0,$row,$width, $row);
		$this->subAreas[3]= new GifBox(0,2*$row,$width, $row);
		$this->subAreas[4]= new GifBox(0,3*$row,$width, $row);
		$this->subAreas[5]= new GifProgressBar(0, 4*$row ,$width, $row,30);
	}
}

?>