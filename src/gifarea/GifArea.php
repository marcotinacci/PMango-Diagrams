<?php

require_once "./lib/jpgraph/src/jpgraph.php";
require_once "./lib/jpgraph/src/jpgraph_canvas.php";

class GifArea
{
	protected $x;
	protected $y;
	protected $width;
	protected $height;
	
	protected $canvas;
	
	protected $subAreas;
	
	protected function __construct($x, $y, $width, $height)
	{
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
		$this->subAreas = array();
		$this->canvas = new CanvasGraph ($width+30, $height+30, 'auto');
		$this->canvas->SetMargin(0,0,0,0);
		$this->canvas->img->SetMargin(0,0,0,0);
	}
	
	public function getX()
	{
		return $this->x;
	}
	
	public function getY()
	{
		return $this->y;
	}
	
	public function getWidth()
	{
		return $this->width;
	}

	public function getHeight()
	{
		return $this->height;
	}
	
	public final function getCanvas()
	{
		return $this->canvas;
	}
	
	protected function canvasDraw(){}
	
	public function drawOn($gifImage)
	{
		$this->enableTransparency();
		for($i=0; $i<sizeOf($this->subAreas); $i++)
		{
			$sub=$this->subAreas[$i];
			$sub->subDrawOn($gifImage,$this->x+$sub->getX(),$this->y+$sub->getY());
			/*
			$sub->enableTransparency();
			$sub->canvasDraw();
			$gifImage->addCanvas($sub->getCanvas(),$this->x+$sub->getX(),$this->y+$sub->getY());
			*/
		}
		$this->canvasDraw();
		$gifImage->addCanvas($this->canvas,$this->x,$this->y);
	}
	
	private function subDrawOn($gifImage,$x,$y)
	{
		$this->enableTransparency();
		for($i=0; $i<sizeOf($this->subAreas); $i++)
		{
			$sub=$this->subAreas[$i];
			$sub->subDrawOn($gifImage,$x+$sub->getX(),$y+$sub->getY());
		}
		$this->canvasDraw();
		$gifImage->addCanvas($this->canvas,$x,$y);
	}
	
	protected function enableTransparency()
	{
		$this->canvas->img->SetColor("magenta");
		$this->canvas->img->FilledRectangle(0,0,$this->width+30, $this->height+30);
		$this->canvas->img->SetTransparent("magenta");
		$this->canvas->img->SetColor("black");
	}
	
}

?>