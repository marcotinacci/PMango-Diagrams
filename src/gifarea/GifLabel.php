<?php

require_once "./GifArea.php";

/* This class print a text label on the gif. */
class GifLabel extends GifArea
{
	private $color = "black";
	private $text = "";

	public function __construct($x, $y, $width, $height, $text)
	{
		parent::__construct($x,$y,$width,$height);
		$this->text = $text;
		$this->transparent = false;
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
		$txt = $this->TruncateText($this->text,$this->width,10);
		$this->canvas->img->SetTransparent("white");
		$t = new Text( $txt,$this->width/2,$this->height/2 );
		$t->SetFont( FF_VERDANA, FS_NORMAL,10);
		$t->SetColor($this->color);
		$t->Align('center','center');
		$t->ParagraphAlign( 'center');
		$this->canvas->add($t);
	}
	
	private function TruncateText($txt,$width,$size)
	{
		$offset = $size*(8/10);
		if( ($width - strlen($txt)*$offset) > 0)
			return $txt;
		
		$optimal = $width/$offset;
		$ret = substr($txt,0,$optimal-2);
		return $ret."...";
	}
}

?>