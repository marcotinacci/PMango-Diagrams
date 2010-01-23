<?php

require_once dirname(__FILE__)."/GifArea.php";

/* This class print a text label on the gif. */
class GifLabel extends GifArea
{
	private $color = "black";
	private $text = "";
	private $size = 10;
	private $bold = false;
	private $vAlign = "center";
	private $hAlign = "center";
	private $truncate = true;
	private $underlined = false;

	public function __construct($gifImage, $x, $y, $width, $height, $text, $size)
	{
		parent::__construct($gifImage, $x,$y,$width,$height);
		$this->text = $text;
		$this->size = $size;
		$this->transparent = false;
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
		$txt=$this->text;
		if($this->truncate)
		$txt = $this->TruncateText($this->text,$this->width,$this->size);
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

		$style=FS_NORMAL;
		if($this->bold)
		$style=FS_BOLD;

		$t = new Text( $txt,$this->x+$xc,$this->y+$yc );
		$t->SetFont( FF_VERDANA, $style,$this->size);
		$t->SetColor($this->color);
		$t->Align($this->hAlign,$this->vAlign);
		//$t->ParagraphAlign($this->vAlign);
		$this->canvas->add($t);
		$c = new CanvasGraph();
		if($this->underlined)
		{
			$this->canvas->img->SetColor($this->color);
			$cy = $this->y+intval(($this->height/2)+($this->size/2))+3;
			$textWidth=GifLabel::getPixelWidthOfText($txt,$this->size,FF_VERDANA);
			if(strtoupper($this->hAlign)=="CENTER")
			{
				$cx1 = $this->x+intval(($this->width/2)-($textWidth/2));
				$cx2 = $this->x+intval(($this->width/2)+($textWidth/2));
				$this->canvas->img->line(2+$cx1,$cy,$cx2-3,$cy);
			}
			else if(strtoupper($this->hAlign)=="LEFT")
			{
				$this->canvas->img->line(2,$cy,$textWidth-3,$cy);
			}
			else
			{
				$this->canvas->img->line($this->width-$textWidth+3,$cy,$this->width-2,$cy);
			}
		}
	}

	private function TruncateText($txt,$width,$size)
	{
		$clear = $this->DeleteSpecialCharacters($txt);

		if( ($width - GifLabel::getPixelWidthOfText($clear,$size)) > 0)
		return $txt;

		$offset = $size*(8/10);
		$optimal = $width/$offset;
		$ret = substr($txt,0,$optimal-2);
		return $ret."...";
	}

	private static function DeleteSpecialCharacters($txt)
	{
		$res = preg_replace("/(&#[0-9]+;)/"," ",$txt);
		return $res;
	}

	public static function getPixelWidthOfText($txt,$fontSize=10,$font=FF_VERDANA)
	{
		$offset = $fontSize*(8/10);
		return intval(strlen(GifLabel::DeleteSpecialCharacters($txt))*$offset);
	}

}

?>