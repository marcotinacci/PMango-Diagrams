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
		$this->canvas->img->SetTransparent("white");
		$t = new Text( $this->text,$this->width/2,$this->height/2 );
		$t->SetFont( FF_VERDANA, FS_NORMAL,10);
		$t->SetColor($this->color);
		$t->Align('center','center');
		$t->ParagraphAlign( 'center');
		$this->canvas->add($t);
	}
}

?>