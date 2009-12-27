<?php

require_once "./GifArea.php";

/* This is class print a rectangle to the gif */
class GifCircle extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";

	public function __construct($x, $y, $r)
	{
		parent::__construct($x-$r,$y-$r,$r*2,$r*2);
	}

	public function getForeColor()
	{
		return $this->foreColor;
	}
	
	public function setForeColor($color)
	{
		$this->foreColor=$color;
	}
	
	public function getBorderColor()
	{
		return $this->foreColor;
	}
	
	public function setBorderColor($color)
	{
		$this->foreColor=$color;
	}
	
	protected function canvasDraw()
	{
		$xc = intval($this->width/2,10);
		$yc = intval($this->height/2,10);
		
		$this->canvas->img->SetColor($this->borderColor);
		$this->canvas->img->Circle($xc,$yc,$xc);
		$this->canvas->img->SetColor($this->foreColor);
		$this->canvas->img->FilledCircle($xc, $yc, $xc-1);
	}
}

?>