<?php

class BoxedLabel extends GifArea
{

	public function __construct($x, $y, $width, $height, $text)
	{
		parent::__construct($x,$y,$width,$height);
		$this->subAreas[0]= new GifBox(0,0,$width,$height);
		$this->subAreas[1]= new GifLabel(2,2,$width-2,$height-2, $text);
		$this->subAreas[2]= new GifLabel(2,12,$width-2,$height-12, $text);
	}
	
}

?>