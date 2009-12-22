<?php

require_once "./lib/jpgraph/src/jpgraph.php";
require_once "./lib/jpgraph/src/jpgraph_canvas.php";

/*
 * GifArea class is the base to draw something to a gif, all the inherited classes can be
 * just a comosition of other extensions of GifAreas or a lowlevel graphic extension obtained
 * ovverriding the method canvasDraw().
*/
class GifArea
{
	protected $transparencyColor = "magenta";
	
	/* Transparency enabled or not */
	protected $transparent = true;
	
	/* Visibile or not */
	protected $visible = true;
	
	/* Coordinate X of the point where the area should be printed on the target image */
	protected $x;
	/* Coordinate Y of the point where the area should be printed on the target image */
	protected $y;
	/* Width of the area */
	protected $width;
	/* Height of the area */
	protected $height;
	
	/* The canvas is a CanvasGraph of the jpGraph library, is the plae where we can draw 
	* and it should be used in the method canvasDraw()
	*/
	protected $canvas;
	
	/* This is an array of GifArea, can be used to realize composition of extended GifArea */
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
	
	/* in this method we use the canvas of jpGraph library to draw */
	protected function canvasDraw(){}
	
	/* this is the method that add the area to the specified GifImage, it's recursive and add 
	 * all the sub areas to the gif, so if the developer adds some subareas he will be shure 
	 * that will be added to the gif. And the order of drawing is from the bottom level to
	 * the top level of the inheritance.
	*/
	public final function drawOn($gifImage)
	{
		$this->subDrawOn($gifImage,$this->getX(),$this->getY());
	}
	
	/* this is just to start the recursive process with specified x and y */
	private function subDrawOn($gifImage,$x,$y)
	{
		if($this->transparent)
			$this->enableTransparency();
		if($this->visible==false)
			return;
		foreach ( $this->subAreas as $sub )
		{
			//if($sub->transparent)
				//$this->enableTransparency();
			$sub->subDrawOn($gifImage,$x+$sub->getX(),$y+$sub->getY());
		}
		$this->canvasDraw();
		$gifImage->addCanvas($this->canvas,$x,$y);
	}
	
	/* This is a method to enable transparency in the drawing, is protected beacouse it's 
	 * not an option for the developer user but for the developer extender.
	*/
	protected function enableTransparency()
	{
		$this->canvas->img->SetColor($this->transparencyColor);
		$this->canvas->img->FilledRectangle(0,0,$this->width+30, $this->height+30);
		$this->canvas->img->SetTransparent($this->transparencyColor);
		$this->canvas->img->SetColor("black");
	}
	
}

?>