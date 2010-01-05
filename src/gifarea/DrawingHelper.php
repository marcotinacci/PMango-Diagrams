<?php

require_once dirname(__FILE__).'/LineStyle.php';

class DrawingHelper
{
	/* This method draw a line from the point ($x1,$y1) to point ($x2,$y2). */
	public static function LineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{
		$x_min = min(array($x1,$x2));
		$y_min = min(array($y1,$y2));
		$x_max = max(array($x1,$x2));
		$y_max = max(array($y1,$y2));
		
		$canvas = new CanvasGraph ($x_max-$x_min+30, $y_max-$y_min+30, 'auto');
		
		DrawingHelper::initLineStyle($canvas,$lineStyle);

		$canvas->img->StyleLine(abs($x1-$x_min) ,abs($y1-$y_min) ,abs($x2-$x_min) ,abs($y2-$y_min));
		$gifImage->addCanvas($canvas,$x_min,$y_min);
	}
	
	/* This method draw a line that pass trough the points described by 
	 * the $xs array and $ys array. */
	public static function LineTrough($xs ,$ys ,$gifImage ,$lineStyle = null)
	{
		$lastX = $xs[0];
		$lastY = $ys[0];
		for($i=1;$i<sizeof($xs);$i++)
		{
			DrawingHelper::LineFromTo($lastX ,$lastY ,$xs[$i] ,$ys[$i],$gifImage,$lineStyle);
			$lastX = $xs[$i];
			$lastY = $ys[$i];
		}
	}
	
	/* This method draw an up rectangular line that pass trough the points described by 
	 * the $xs array and $ys array. */
	public static function UpRectangularLineTrough($xs ,$ys ,$gifImage ,$lineStyle = null)
	{
		$lastX = $xs[0];
		$lastY = $ys[0];
		for($i=1;$i<sizeof($xs);$i++)
		{
			DrawingHelper::UpRectangularLineFromTo($lastX ,$lastY ,$xs[$i] ,$ys[$i],$gifImage,$lineStyle);
			$lastX = $xs[$i];
			$lastY = $ys[$i];
		}
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
			DrawingHelper::UpRectangularLineFromTo($x_half ,$y_half ,$xs[$i] ,$ys[$i],$gifImage, $lineStyle);
		}
	}
	
	private function initLineStyle(&$canvas,$lineStyle)
	{
		$w = $canvas->img->width;
		$h = $canvas->img->height;
		$canvas->img->SetColor("magenta");
		$canvas->img->FilledRectangle(0,0,$w, $h);
		$canvas->img->SetTransparent("magenta");
		
		if($lineStyle == null)
			$lineStyle = new LineStyle();
			
		//$canvas->img->SetAntiAliasing(false);
		$canvas->img->SetColor($lineStyle->color);
		$canvas->img->SetLineStyle($lineStyle->style);
		$canvas->img->SetLineWeight($lineStyle->weight);
	}
	
	public static function segmentedOffsetLine($x,$y,$hOffset,$vOffset,$xEnd,$yEnd,$gifImage,$lineStyle=null)
	{
		$ys[] = $y;
		$ys[] = $yEnd;
		$xs[] = $x;
		$xs[] = $xEnd;
		
		$y_max = max($ys);
		$y_min = min($ys);
		$x_max = max($xs);
		$x_min = min($xs);
		
		DrawingHelper::LineFromTo($x,$y,$x+$hOffset,$y,$gifImage,$lineStyle);
		DrawingHelper::LineFromTo($x+$hOffset,$y,$x+$hOffset,$y+$vOffset,$gifImage,$lineStyle);
		DrawingHelper::LineFromTo($x+$hOffset,$y+$vOffset,$xEnd,$yEnd,$gifImage,$lineStyle);
	}
	
	public static function drawArrow($x,$y,$width,$height,$angle,$gifImage ,$lineStyle = null)
	{	
		$canvas = new CanvasGraph ($width, $height, 'auto');
		
		$w = $canvas->img->width;
		$h = $canvas->img->height;
		$canvas->img->SetColor("magenta");
		$canvas->img->FilledRectangle(0,0,$w, $h);
		$canvas->img->SetTransparent("magenta");
		
		$xoffset = $width/2;
		$yoffset = $height/2;
		
		$points[0]=0+$xoffset;			$points[1]=0+$yoffset;
		$points[2]=0+$width/2+$xoffset; $points[3]=$height/2+$yoffset;
		$points[4]=0-$width/2+$xoffset;	$points[5]=$height/2+$yoffset;
		
		$canvas->img->SetColor("black");
		//$canvas->img->Rectangle(0,0,$width,$height);
		//$canvas->img->Circle(0+$width/2,0+$height,$width/2);
		$canvas->img->FilledPolygon($points);
		$gifImage->addCanvas($canvas,$x-$width/2,$y-$height/2);
	}
}

?>