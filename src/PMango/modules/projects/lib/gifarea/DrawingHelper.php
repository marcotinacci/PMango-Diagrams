<?php

require_once dirname(__FILE__).'/LineStyle.php';

$debugging = false;

class DrawingHelper
{
	/* This method draw a line from the point ($x1,$y1) to point ($x2,$y2). */
	public static function LineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{
		if($lineStyle == null || $lineStyle->patterNumberOfDots == 0)
		{
			DrawingHelper::NormalLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle);
		}
		else
		{
			if( $x2==$x1 )
			{
				DrawingHelper::VerticalPatternLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle);
			}
			else if( $y1==$y2 )
			{
				DrawingHelper::HorizzontalPatternLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle);
			}
			else
			{
				DrawingHelper::PatternLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle);
			}
		}
	}
	
	/* This method draw a normal line from the point ($x1,$y1) to point ($x2,$y2). */
	private static function NormalLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{
		$x_min = min(array($x1,$x2));
		$y_min = min(array($y1,$y2));
		$x_max = max(array($x1,$x2));
		$y_max = max(array($y1,$y2));
		
		$canvas = new CanvasGraph ($x_max-$x_min+30, $y_max-$y_min+30, 'auto');
		
		DrawingHelper::initLineStyle($canvas,$lineStyle);

		//DrawingHelper::debug($lineStyle->style!=null?$lineStyle->style:"null");
		
		$canvas->img->StyleLine(abs($x1-$x_min) ,abs($y1-$y_min) ,abs($x2-$x_min) ,abs($y2-$y_min));
		$gifImage->addCanvas($canvas,$x_min,$y_min);
	}
	
	private static function calculateHOffset($m,$d)
	{
		return (sqrt(1+pow($m,2)))/$d;
	}
	
	/* This method draw a pattern line from the point ($x1,$y1) to point ($x2,$y2). */
	private static function PatternLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{	
		DrawingHelper::debug("PatternLineFrom ($x1,$y1) to ($x2,$y2)");
		
		$dottedStyle = DrawingHelper::getDottedStyle($lineStyle);
		
		//Calcolo m e q
		$m = 1;
		$q = 0;
		if( ($x2-$x1)!=0)
		{
			$m = ($y2-$y1)/($x2-$x1);
			$q = (($x2*$y1)-($x1*$y2))/($x2-$x1);
		}
		DrawingHelper::debug("m:".$m);
		DrawingHelper::debug("q:".$q);
		
		//Calcolo la distanza del segmento dotted
		$dottedLength = 2.5 * $lineStyle->patterNumberOfDots;
		
		//Calcolo gli HOffset per disegnare la retta a pezzetti
		$hDottedOffset = DrawingHelper::calculateHOffset($m,$dottedLength);
		DrawingHelper::debug("hDottedOffset = ".$hDottedOffset);
		$hNormalOffset = DrawingHelper::calculateHOffset($m,$lineStyle->patternInitialFinalLength);
		DrawingHelper::debug("hNormalOffset = ".$hNormalOffset);
		
		//Calcolo quante volte ripetere il pattern per raggiungere la distanza
		$parts = intval(($x2-$x1)/(2*$hNormalOffset+$hDottedOffset));
		DrawingHelper::debug("parts:".$parts);
		
		//Preparo i dati per il primo lineto
		$curStartX = $x1;
		$curStartY = $y1;
		$curEndX   = $curStartX+$hNormalOffset;
		$curEndY   = $m*$curEndX+$q;
		//Avvio i lineto
		for($i=0; $i<$parts; $i++)
		{			
			DrawingHelper::debug("normal | x1=$curStartX , y1=$curStartY => x2=$curEndX , y2=$curEndY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curEndX,$curEndY,$gifImage,$lineStyle);
			
			$curStartX = $curEndX;
			$curStartY = $curEndY;
			$curEndX   = $curStartX+$hDottedOffset;
			$curEndY   = $m*$curEndX+$q;
			DrawingHelper::debug("dotted | x1=$curStartX , y1=$curStartY => x2=$curEndX , y2=$curEndY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curEndX,$curEndY,$gifImage,$dottedStyle);
			
			$curStartX = $curEndX;
			$curStartY = $curEndY;
			$curEndX   = $curStartX+$hNormalOffset;
			$curEndY   = $m*$curEndX+$q;
			DrawingHelper::debug("normal | x1=$curStartX , y1=$curStartY => x2=$curEndX , y2=$curEndY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curEndX,$curEndY,$gifImage,$lineStyle);
			
			$curStartX = $curEndX;
			$curStartY = $curEndY;
			$curEndX   = $curStartX+$hNormalOffset;
			$curEndY   = $m*$curEndX+$q;
		}
	}
	
	/* This method draw a vertical pattern line from the point ($x1,$y1) to point ($x2,$y2). */
	private static function VerticalPatternLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{	
		DrawingHelper::debug("VerticalPatternLineFrom ($x1,$y1) to ($x2,$y2)");
		
		$dottedStyle = DrawingHelper::getDottedStyle($lineStyle);
		
		//Calcolo la distanza del segmento dotted
		$dottedLength = 5 * $lineStyle->patterNumberOfDots;
		$vOffset = $lineStyle->patternInitialFinalLength;
		
		//Calcolo offset verticale
		if($y1>$y2)
		{
			$vOffset = -$lineStyle->patternInitialFinalLength;
			$dottedLength = -$dottedLength;
		}
		
		//Calcolo quante volte ripetere il pattern per raggiungere la distanza
		$parts = intval((abs($y2-$y1))/(2*abs($vOffset)+abs($dottedLength)));
		DrawingHelper::debug("parts:".$parts);
		
		//Preparo i dati per il primo lineto
		$curStartX = $x1;
		if($y1>$y2)
		$curStartY = $y1;
		else
		$curStartY = $y1;
		$curEndY   = $curStartY+$vOffset;
		
		//Avvio i lineto
		for($i=0; $i<$parts; $i++)
		{			
			DrawingHelper::debug("normal | x1=$curStartX , y1=$curStartY => x2=$curStartX , y2=$curEndY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curStartX,$curEndY,$gifImage,$lineStyle);
			
			$curStartY = $curEndY;
			$curEndY   = $curEndY+$dottedLength;
			DrawingHelper::debug("dotted | x1=$curStartX , y1=$curStartY => x2=$curStartX , y2=$curEndY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curStartX,$curEndY,$gifImage,$dottedStyle);
			
			$curStartY = $curEndY;
			$curEndY   = $curEndY+$vOffset;
			DrawingHelper::debug("normal | x1=$curStartX , y1=$curStartY => x2=$curStartX , y2=$curEndY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curStartX,$curEndY,$gifImage,$lineStyle);
			
			$curStartY = $curEndY;
			$curEndY   = $curEndY+$vOffset;
		}
		//Aggiuntina per i restanti pixellini
		DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$x2,$y2,$gifImage,$lineStyle);
	}
	
/* This method draw a vertical pattern line from the point ($x1,$y1) to point ($x2,$y2). */
	private static function HorizzontalPatternLineFromTo($x1 ,$y1 ,$x2 ,$y2 ,$gifImage ,$lineStyle = null)
	{	
		DrawingHelper::debug("HorizzontalPatternLineFrom ($x1,$y1) to ($x2,$y2)");
		
		$dottedStyle = DrawingHelper::getDottedStyle($lineStyle);
		
		//Calcolo la distanza del segmento dotted
		$dottedLength = 5 * $lineStyle->patterNumberOfDots;
		//Calcolo offset orizzontale
		$hOffset = $lineStyle->patternInitialFinalLength;
		if($x1>$x2)
		{
			$dottedLength = -$dottedLength;
			$hOffset = -$hOffset;
		}
		
		//Calcolo quante volte ripetere il pattern per raggiungere la distanza
		$parts = intval((abs($x2-$x1))/(2*abs($hOffset)+abs($dottedLength)));
		DrawingHelper::debug("parts:".$parts);
		
		//Preparo i dati per il primo lineto
		$curStartX = $x1;
		$curStartX = $x1;
		$curEndX   = $curStartX+$hOffset;
		$curStartY = $y1;
		//Avvio i lineto
		for($i=0; $i<$parts; $i++)
		{			
			DrawingHelper::debug("normal | x1=$curStartX , y1=$curStartY => x2=$curEndX , y2=$curStartY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curEndX,$curStartY,$gifImage,$lineStyle);
			
			$curStartX = $curEndX;
			$curEndX   = $curEndX+$dottedLength;
			DrawingHelper::debug("dotted | x1=$curStartX , y1=$curStartY => x2=$curEndX , y2=$curStartY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curEndX,$curStartY,$gifImage,$dottedStyle);
			
			$curStartX = $curEndX;
			$curEndX   = $curEndX+$hOffset;
			DrawingHelper::debug("normal | x1=$curStartX , y1=$curStartY => x2=$curEndX , y2=$curStartY");
			DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$curEndX,$curStartY,$gifImage,$lineStyle);
			
			$curStartX = $curEndX;
			$curEndX   = $curEndX+$hOffset;
		}
		//Aggiuntina per i restanti pixellini
		DrawingHelper::NormalLineFromTo($curStartX,$curStartY,$x2,$y2,$gifImage,$lineStyle);
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
	
	private function getDottedStyle($lineStyle)
	{
		$dottedStyle = new LineStyle();
		$dottedStyle->style = "dotted";
		$dottedStyle->weight = $lineStyle->weight;
		$dottedStyle->color = $lineStyle->color = "red";
		return $dottedStyle;
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
	
	public static function drawArrow($x,$y,$width,$height,$direction,$gifImage)
	{	
		$canvas = new CanvasGraph ($width+30, $height+30, 'auto');
		
		$w = $canvas->img->width;
		$h = $canvas->img->height;
		$canvas->img->SetColor("magenta");
		$canvas->img->FilledRectangle(0,0,$w, $h);
		$canvas->img->SetTransparent("magenta");
		
		if(strtoupper($direction)=="UP")
			$points = DrawingHelper::getUpArrowPoints($x,$y,$width,$height);
		if(strtoupper($direction)=="DOWN")
			$points = DrawingHelper::getDownArrowPoints($x,$y,$width,$height);
		if(strtoupper($direction)=="LEFT")
			$points = DrawingHelper::getLeftArrowPoints($x,$y,$width,$height);
		if(strtoupper($direction)=="RIGHT")
			$points = DrawingHelper::getRightArrowPoints($x,$y,$width,$height);
			
		$canvas->img->SetColor("black");
		$canvas->img->FilledPolygon($points);
		$gifImage->addCanvas($canvas,$x-$width/2,$y-$height/2);
	}
	
	public static function GanttDependencyLine($x1,$y1,$x2,$y2,$offset,$endWithArrow,$gifImage,$lineStyle = null)
	{
		$halfQuote = ($y2-$y1)/2;
		$halfX = ($x2-$x1)/2;
		if($x1<=$x2)
		{
			DrawingHelper::LineFromTo($x1,$y1,$x1+$halfX,$y1,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x1+$halfX,$y1,$x1+$halfX,$y2,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x1+$halfX,$y2,$x2,$y2,$gifImage,$lineStyle);
			if($endWithArrow)
				DrawingHelper::drawArrow($x2,$y2,10,10,"right",$gifImage);
		}
		else
		{
			DrawingHelper::LineFromTo($x1,$y1,$x1+$offset,$y1,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x1+$offset,$y1,$x1+$offset,$y1+$halfQuote,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x1+$offset,$y1+$halfQuote,$x2-$offset,$y1+$halfQuote,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x2-$offset,$y1+$halfQuote,$x2-$offset,$y2,$gifImage,$lineStyle);
			DrawingHelper::LineFromTo($x2-$offset,$y2,$x2,$y2,$gifImage,$lineStyle);
			if($endWithArrow)
				DrawingHelper::drawArrow($x2,$y2,10,10,"right",$gifImage);
		}
	}
	
	private static function getUpArrowPoints($x,$y,$width,$height)
	{	
		$xoffset = $width/2;
		$yoffset = $height/2;
		
		$points[0]=0+$xoffset;			$points[1]=0+$yoffset;
		$points[2]=0+$width/2+$xoffset; $points[3]=$height/2+$yoffset;
		$points[4]=0-$width/2+$xoffset;	$points[5]=$height/2+$yoffset;
		return $points;
	}
	
	private static function getDownArrowPoints($x,$y,$width,$height)
	{	
		$xoffset = $width/2;
		$yoffset = $height/2;
		
		$points[0]=0+$xoffset;			$points[1]=0+$yoffset;
		$points[2]=0+$width/2+$xoffset; $points[3]=-$height/2+$yoffset;
		$points[4]=0-$width/2+$xoffset;	$points[5]=-$height/2+$yoffset;
		return $points;
	}
	
	private static function getLeftArrowPoints($x,$y,$width,$height)
	{	
		$xoffset = $width/2;
		$yoffset = $height/2;
		
		$points[0]=0+$xoffset;			$points[1]=0+$yoffset;
		$points[2]=0+$width/2+$xoffset; $points[3]=-$height/2+$yoffset;
		$points[4]=0+$width/2+$xoffset;	$points[5]=$height/2+$yoffset;
		return $points;
	}
	
	private static function getRightArrowPoints($x,$y,$width,$height)
	{	
		$xoffset = $width/2;
		$yoffset = $height/2;
		
		$points[0]=0+$xoffset;			$points[1]=0+$yoffset;
		$points[2]=0-$width/2+$xoffset; $points[3]=-$height/2+$yoffset;
		$points[4]=0-$width/2+$xoffset;	$points[5]=$height/2+$yoffset;
		return $points;
	}
	
	private function debug($msg)
	{
		global $debugging;
		if($debugging)
		print $msg."<br>";
	}
}

?>