<?php

require_once dirname(__FILE__)."/GifArea.php";

/* This is class print a rectangle to the gif */
class GifBox extends GifArea
{
	private $foreColor = "magenta"; //default è trasparente
	private $borderColor = "black";
	private $borderThickness = 1;

	public function __construct($gifImage, $x, $y, $width, $height)
	{
		parent::__construct($gifImage, $x,$y,$width,$height);
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
		//$this->canvas->img->Bevel($this->x,$this->y, $this->x+$this->width, $this->y+$this->height,2,"black","black");
		for($i=0;$i<$this->borderThickness;$i++)
		{
			$this->canvas->img->Rectangle($this->x+$i,$this->y+$i, $this->x+$this->width-$i, $this->y+$this->height-$i);
		}
		if($this->foreColor != "magenta")
		{
			$this->canvas->img->SetColor($this->foreColor);
			$this->canvas->img->FilledRectangle($this->x+$this->borderThickness, $this->y+$this->borderThickness, $this->x+$this->width-$this->borderThickness, $this->y+$this->height-$this->borderThickness);
		}
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