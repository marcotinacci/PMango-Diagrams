<?php

require_once("./LineStyle.php");

class DrawingHelper
{
	//TODO Adjust the method to draw good for every coordinate
	public static function LineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{
		
		$x_min = min(array($x1,$x2));
		$y_min = min(array($y1,$y2));
		$x_max = max(array($x1,$x2));
		$y_max = max(array($y1,$y2));
		
		$canvas = new CanvasGraph ($x_max-$x_min+30, $y_max-$y_min+30, 'auto');
		
		DrawingHelper::initLineStyle($canvas,$lineStyle);
		
		$canvas->img->Line(abs($x1-$x_min) ,abs($y1-$y_min) ,abs($x2-$x_min) ,abs($y2-$y_min));
		$gifImage->addCanvas($canvas,$x_min,$y_min);
	}
	
	/* This method draw a line from the point ($x1,$y1) to every point described by 
	 * the $xs array and $ys array.
	 */
	public static function ExplodedLineFromTo($x1 ,$y1 ,$xs, $ys ,$gifImage ,$lineStyle = null)
	{		
		$ys[] = $y1;
		$xs[] = $x1;
		$y_max = max($ys);
		$y_min = min($ys);
		$x_max = max($xs);
		$x_min = min($xs);
		unset($ys[sizeOf($ys)-1]);
		unset($xs[sizeOf($xs)-1]);
		
		//print "max:".$y_max;
		//print "min:".$y_min;
		$y_half = (($y_max-$y_min) / 2) + $y_min;
		$x_half = (($x_max-$x_min) / 2) + $x_min;
		//print "y_half:".$y_half;
		//print "x_half:".$x_half;
		
		DrawingHelper::LineFromTo($x1 ,$y1 ,$x_half ,$y_half,$gifImage,$lineStyle);
		for($i=0; $i<sizeOf($xs); $i++)
		{
			DrawingHelper::LineFromTo($x_half ,$y_half ,$xs[$i] ,$ys[$i],$gifImage,$lineStyle);
		}
	}
	
	/* This method draw a line from the point ($x1,$y1) to the point ($x2,$y2) with 
	 * an angle of 90°
	 */
	public static function UpRectangularLineFromTo($x1 ,$y1 ,$x2, $y2 ,$gifImage ,$lineStyle = null)
	{		
		$x_min = min(array($x1,$x2));
		$y_min = min(array($y1,$y2));
		$x_max = max(array($x1,$x2));
		$y_max = max(array($y1,$y2));
		
		if($x1 <= $x2 && $y1 <= $y2)
		{
			DrawingHelper::LineFromTo($x1 ,$y1 ,$x2 ,$y1,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x2 ,$y1 ,$x2 ,$y2,$gifImage,$lineStyle);
		}
		else if($x1 >= $x2 && $y1 >= $y2)
		{
			DrawingHelper::LineFromTo($x2 ,$y2 ,$x1 ,$y2,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x1 ,$y2 ,$x1 ,$y1,$gifImage,$lineStyle);
		}
		else if($x1 <= $x2 && $y1 >= $y2)
		{
			DrawingHelper::LineFromTo($x1 ,$y1 ,$x1 ,$y2,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x1 ,$y2 ,$x2 ,$y2,$gifImage,$lineStyle);
		}
		else
		{
			DrawingHelper::LineFromTo($x2 ,$y2 ,$x2 ,$y1,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x2 ,$y1 ,$x1 ,$y1,$gifImage,$lineStyle);
		}
		
	}
	
	/* This method draw an uprectangular line from the point ($x1,$y1) to every point described by 
	 * the $xs array and $ys array.
	 */
	public static function ExplodedUpRectangularLineFromTo($x1 ,$y1 ,$xs, $ys ,$gifImage ,$lineStyle = null)
	{		
		$ys[] = $y1;
		$xs[] = $x1;
		$y_max = max($ys);
		$y_min = min($ys);
		$x_max = max($xs);
		$x_min = min($xs);
		unset($ys[sizeOf($ys)-1]);
		unset($xs[sizeOf($xs)-1]);
		
		$y_half = (($y_max-$y_min) / 2) + $y_min;
		$x_half = (($x_max-$x_min) / 2) + $x_min;
		
		DrawingHelper::LineFromTo($x1 ,$y1 ,$x_half ,$y_half,$gifImage,$lineStyle);
		for($i=0; $i < sizeOf($xs); $i++)
		{
			DrawingHelper::UpRectangularLineFromTo($x_half ,$y_half ,$xs[$i] ,$ys[$i],$gifImage,$lineStyle);
		}
	}
	
	private function initLineStyle(&$canvas,&$lineStyle)
	{
		$w = $canvas->img->width;
		$h = $canvas->img->height;
		$canvas->img->SetColor("magenta");
		$canvas->img->FilledRectangle(0,0,$w, $h);
		$canvas->img->SetTransparent("magenta");
		
		if($lineStyle == null)
			$lineStyle = new LineStyle();
			
		$canvas->img->SetColor($lineStyle->color);
		$canvas->img->SetLineStyle($lineStyle->style);
		$w = $canvas->img->width;
		$h = $canvas->img->height;
		
	}
}

?>