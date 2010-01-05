<?php

class LineStyle
{
	public $color = "black";
	public $weight = 1;
	public $style = "solid"; //solid,dotted,dashed,longdashed
	public $patterNumberOfDots = 0;
	public $patternInitialFinalLength = 10;
	//public $endArrow = true;
	//public $startArrow = true;
	
	public function __construct($color = "black", $weight = 1, $style = "solid", 
	$patterNumberOfDots = 0, $patternInitialFinalLength = 0)
	{
		$this->color = $color;
		$this->weight = $weight;
		$this->style = $style; 
		$this->patterNumberOfDots = $patterNumberOfDots;
		$this->patternInitialFinalLength = $patternInitialFinalLength;
	}
}

?>