<?php

require_once "./GifArea.php";

class GifBox extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";

	public function __construct($x, $y, $width, $height)
	{
		parent::__construct($x,$y,$width,$height);
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
		$this->canvas->img->SetColor($this->borderColor);
		$this->canvas->img->Rectangle(0, 0, $this->width, $this->height);
		$this->canvas->img->SetColor($this->foreColor);
		$this->canvas->img->FilledRectangle(1, 1, $this->width-1, $this->height-1);
	}
}

?>