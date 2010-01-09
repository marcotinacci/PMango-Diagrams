<?php

require_once dirname(__FILE__)."/GifArea.php";

/* This is class print a rectangle to the gif */
class GifTriangle extends GifArea
{
	private $foreColor = "black";
	private $hOrientation = "left";

	public function __construct($x, $y, $width, $height, $hOrientation)
	{
		parent::__construct($x,$y,$width,$height);
		$this->hOrientation = $hOrientation;
	}

	public function getForeColor()
	{
		return $this->foreColor;
	}
	
	public function setForeColor($color)
	{
		$this->foreColor=$color;
	}
	
	protected function canvasDraw()
	{
		$points = array();
		$points[0] = 0; 			$points[1] = 0;
		$points[2] = $this->width; 	$points[3] = 0;
		if($this->hOrientation == "left")
		{
			$points[4] = 0; 			$points[5] = $this->height;
		}
		else
		{
			$points[4] = $this->width; 	$points[5] = $this->height;
		}
		$this->canvas->img->SetColor($this->foreColor);
		$this->canvas->img->FilledPolygon($points);
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