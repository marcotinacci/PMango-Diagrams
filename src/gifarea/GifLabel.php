<?php

require_once "./GifArea.php";

class GifLabel extends GifArea
{
	private $color = "black";
	private $text = "";

	public function __construct($x, $y, $width, $height, $text)
	{
		parent::__construct($x,$y,$width,$height);
		$this->text = $text;
	}

	protected function canvasDraw()
	{
		$t = new Text( $this->text,$this->width/2,$this->height/2 );
		$t->SetFont( FF_VERDANA, FS_NORMAL,10);
		$t->SetColor($this->color);
		$t->Align('center','center');
		$t->ParagraphAlign( 'center');
		$this->canvas->add($t);
	}
}

?>