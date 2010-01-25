<?php

require_once dirname(__FILE__)."/GifArea.php";

/* This class print a text label on the gif. */
class GifLabel extends GifArea
{
	private static $c; //Used for textWidth
	
	private $color = "black";
	private $text = "";
	private $size = 10;
	private $bold = false;
	private $vAlign = "center";
	private $hAlign = "center";
	private $truncate = true;
	private $underlined = false;

	private $font;
	private $fontStyle;

	public function __construct($gifImage, $x, $y, $width, $height, $text, $size)
	{
		parent::__construct($gifImage, $x,$y,$width,$height);
		$this->text = $text;
		$this->size = $size;
		$this->transparent = false;

		$this->font = FF_ARIAL;
		$this->fontStyle = FS_NORMAL;
	}

	public function getFontsize()
	{
		return $this->size;
	}

	public function setFontSize($size)
	{
		$this->size=$size;
	}

	public function getTruncate()
	{
		return $this->truncate;
	}

	public function setTruncate($bool)
	{
		$this->truncate=$bool;
	}

	public function setText($txt)
	{
		$this->text=$txt;
	}

	public function getText()
	{
		return $this->text;
	}

	public function setVAlign($align)
	{
		$this->vAlign=$align;
	}

	public function getVAlign()
	{
		return $this->vAlign;
	}

	public function setHAlign($align)
	{
		$this->hAlign=$align;
	}

	public function getHAlign()
	{
		return $this->hAlign;
	}

	public function setBold($bold)
	{
		$this->bold=$bold;
	}

	public function getBold()
	{
		return $this->bold;
	}

	public function setUnderline($bool)
	{
		$this->underlined=$bool;
	}

	public function getUnderline()
	{
		return $this->underlined;
	}

	public function setTextColor($color)
	{
		$this->color=$color;
	}

	public function getTextColor()
	{
		return $this->color;
	}

	protected function canvasDraw()
	{
		$style=FS_NORMAL;
		if($this->bold)
		$style=FS_BOLD;

		$this->fontStyle = $style;

		$txt=$this->text;
		if($this->truncate)
		$txt = $this->TruncateText($this->text,$this->width);
		//$this->canvas->img->SetTransparent("white");

		$xc = intval($this->width/2);
		$yc = intval($this->height/2);
		if(strtoupper($this->hAlign)=="LEFT")
		$xc = 0;
		if(strtoupper($this->hAlign)=="RIGHT")
		$xc = $this->width;
		if(strtoupper($this->vAlign)=="TOP")
		$yc = 0;
		if(strtoupper($this->vAlign)=="BOTTOM")
		$yc = $this->height;

		$t = new Text( $txt,$this->x+$xc,$this->y+$yc );
		$t->SetFont( $this->font, $style,$this->size);
		$t->SetColor($this->color);
		$t->Align($this->hAlign,$this->vAlign);
		//$t->ParagraphAlign($this->vAlign);
		$this->canvas->add($t);
		if($this->underlined)
		{
			$this->canvas->img->SetColor($this->color);
			$cy = $this->y+intval(($this->height/2)+($this->size/2))+3;
			$textWidth=GifLabel::getPixelWidthOfText($txt,$this->size,$this->font,$this->fontStyle);
			if(strtoupper($this->hAlign)=="CENTER")
			{
				$cx1 = $this->x+intval(($this->width/2)-($textWidth/2));
				$cx2 = $this->x+intval(($this->width/2)+($textWidth/2));
				$this->canvas->img->line(2+$cx1,$cy,$cx2-3,$cy);
			}
			else if(strtoupper($this->hAlign)=="LEFT")
			{
				$this->canvas->img->line($this->x+2,$cy-1,$this->x+$textWidth-3,$cy-1);
			}
			else
			{
				$this->canvas->img->line($this->width-$textWidth+3,$cy,$this->width-2,$cy);
			}
		}
	}

	private function TruncateText($txt,$width)
	{
		$size = $this->size;
		$clear = $this->DeleteSpecialCharacters($txt);

		if( ($width - GifLabel::getPixelWidthOfText($clear,$this->size,$this->font,$this->fontStyle)) > 0)
		return $txt;

		$width -= GifLabel::getPixelWidthOfText("00",$this->size,$this->font,$this->fontStyle);

		$i=0;
		//Cerco l'ottimo con un binary search
		$optimalString=$clear;
		$optimal = GifLabel::getPixelWidthOfText($optimalString,$this->size,$this->font,$this->fontStyle);
		$half = intval(strlen($optimalString)/2);
		DrawingHelper::debug("<b>Optimal width of $txt to fit $width</b>");
		while(abs($width-$optimal)>5)
		{
			$half = intval($half/2);
			if($half==0)
			{
				DrawingHelper::debug("Aborted for half to small");
				break;
			}
			//se optimal è maggiore sottraggo la metà dell'optimal
			if($optimal > $width)
			{
				$optimalString = substr($clear,0,strlen($optimalString)-$half);
				DrawingHelper::debug("Subtracted $half and had: $optimalString");
			}
			//se è minore aggiungo la metà dell'optimal
			if($optimal < $width)
			{
				$optimalString = substr($clear,0,strlen($optimalString)+$half);
				DrawingHelper::debug("Added $half and had: $optimalString");
			}
			$optimal = GifLabel::getPixelWidthOfText($optimalString,$this->size,$this->font,$this->fontStyle);
			DrawingHelper::debug("Optimal pixels is $optimal and optimal strlen is(".strlen($optimalString).");");
			$i++;
			if($i>10)
			{
				DrawingHelper::debug("Aborted for too many cicles");
				break;
			}
		}
		//Final adjustments
		if($optimal > $width)
		{
			while($optimal > $width && strlen($optimalString)!=0)
			{
				$optimalString = substr($clear,0,strlen($optimalString)-1);
				DrawingHelper::debug("Subtracted 1 and had: $optimalString");
				$optimal = GifLabel::getPixelWidthOfText($optimalString,$this->size,$this->font,$this->fontStyle);
				DrawingHelper::debug("Optimal pixels is $optimal and optimal strlen is(".strlen($optimalString).");");
			}
		}
		else if($optimal < $width)
		{
			while($optimal < $width && strlen($optimalString)!=0)
			{
				$optimalString = substr($clear,0,strlen($optimalString)+1);
				DrawingHelper::debug("Added 1 and had: $optimalString");
				$optimal = GifLabel::getPixelWidthOfText($optimalString,$this->size,$this->font,$this->fontStyle);
				DrawingHelper::debug("Optimal pixels is $optimal and optimal strlen is(".strlen($optimalString).");");
			}	
			$optimalString = substr($clear,0,strlen($optimalString)-1);
		}
		return substr($txt,0,strlen($optimalString))."...";
	}

	private static function DeleteSpecialCharacters($txt)
	{
		$res = preg_replace("/(&#[0-9]+;)/"," ",$txt);
		return $res;
	}

	public static function getPixelWidthOfText($txt,$fontSize=10,$font=FF_ARIAL,$fontStyle=FS_NORMAL)
	{
		$txt.="";
		if(!isset(GifLabel::$c))
		{
			DrawingHelper::debug("Singleton pixel calculator created");
			GifLabel::$c = new CanvasGraph(30,30);
		}
		GifLabel::$c->img->SetFont($font,$fontStyle,$fontSize);
		$w = GifLabel::$c->img->GetTextWidth(GifLabel::DeleteSpecialCharacters($txt));
		//$c->img->Destroy();
		//unset($c);
		return $w;
	}

}

?>