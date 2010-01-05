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
	
	public function __construct($x, $y, $width, $height, $text, $size)
	{
		parent::__construct($x,$y,$width,$height);
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
		$txt = $this->TruncateText($this->text,$this->width,$this->size);
		$this->canvas->img->SetTransparent("white");
		
		$xc = intval($this->width/2);
		$yc = intval($this->height/2);
		
		$style=FS_NORMAL;
		if($this->bold)
			$style=FS_BOLD;
		
		$t = new Text( $txt,$xc,$yc-2 );
		$t->SetFont( FF_VERDANA, $style,$this->size);
		$t->SetColor($this->color);
		$t->Align($this->hAlign,$this->vAlign);
		$t->ParagraphAlign( 'center');
		$this->canvas->add($t);
	}
	
	private function TruncateText($txt,$width,$size)
	{
		$clear = $this->DeleteSpecialCharacters($txt);
		$offset = $size*(8/10);
		if( ($width - strlen($clear)*$offset) > 0)
			return $txt;
		
		$optimal = $width/$offset;
		$ret = substr($txt,0,$optimal-2);
		return $ret."...";
	}
	
	private function DeleteSpecialCharacters($txt)
	{
		$res = preg_replace("/(&#[0-9]+;)/"," ",$txt);
		return $res;
	}
	
}

?>