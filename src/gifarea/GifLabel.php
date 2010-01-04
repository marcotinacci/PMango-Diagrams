<?php

require_once "./GifArea.php";

/* This class print a text label on the gif. */
class GifLabel extends GifArea
{
	private $color = "black";
	private $text = "";
	private $size = 10;

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
		
		$t = new Text( $txt,$xc,$yc-2 );
		$t->SetFont( FF_ARIAL, FS_NORMAL,$this->size);
		$t->SetColor($this->color);
		$t->Align('center','center');
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