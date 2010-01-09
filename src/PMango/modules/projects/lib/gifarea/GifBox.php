<?php

require_once dirname(__FILE__)."/GifArea.php";

/* This is class print a rectangle to the gif */
class GifBox extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";
	private $borderThickness = 1;

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
	
	public function getBorderThickness()
	{
		return $this->borderThickness;
	}
	
	public function setBorderThickness($borderThickness)
	{
		$this->borderThickness=$borderThickness;
	}
	
	protected function canvasDraw()
	{
		$this->canvas->img->SetColor($this->borderColor);
		$this->canvas->img->Bevel(0,0, $this->width, $this->height,2,"black","black");
		//$this->canvas->img->Rectangle(0, 0, $this->width, $this->height);
		$this->canvas->img->SetColor($this->foreColor);
		$this->canvas->img->FilledRectangle($this->borderThickness, $this->borderThickness, $this->width-$this->borderThickness, $this->height-$this->borderThickness);
	}
	
	public function getTopMiddlePoint()
	{
		$point = array();
		$point['x']=$this->x+($this->width/2);
		$point['y']=$this->y;
		return $point;
	}
	
	public function getBottomMiddlePoint()
	{
		$point = array();
		$point['x']=$this->x+($this->width/2);
		$point['y']=$this->y+($this->height);
		return $point;
	}
	
	public function getLeftMiddlePoint()
	{
		$point = array();
		$point['x']=$this->x;
		$point['y']=$this->y+($this->height/2);
		return $point;
	}
	
	public function getRightMiddlePoint()
	{
		$point = array();
		$point['x']=$this->x+($this->width);
		$point['y']=$this->y+($this->height/2);
		return $point;
	}
}

?>