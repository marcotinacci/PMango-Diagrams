<?php

require_once "$baseDir/lib/jpgraph/src/jpgraph.php";
require_once "$baseDir/lib/jpgraph/src/jpgraph_utils.inc";

if(!isset($debugging))
$debugging = false;
/* This class is the conceptual idea of the gif, it contains a MultiGraph (used to compose
 *  the canvas of the gifareas) and has methods to save it to file or to print it directly.
 */
class GifImage
{
	//It's a MGraph of the jpGraph library
	private $mgraph;
	private $width;
	private $height;
	
	function __construct($width,$height)
	{
		$this->width = $width;
		$this->height = $height;
		$this->mgraph = new CanvasGraph($width,$height);
		$this->mgraph->img->SetImgFormat("gif");
	}	
	
	//Add the specified canvas to the image at position ($x, $y)
	/*
	function addCanvas($canvas,$x,$y)
	{
		$this->mgraph->add($canvas,$x,$y);
	}
	*/
	
	//Save the gif to file $FileName
	function saveToFile($fileName)
	{
		global $debugging;
		if(!$debugging)
		$this->mgraph->Stroke($fileName);
	}
	
	function &getCanvas()
	{
		return $this->mgraph;
	}
	
	//Draw the gif directly in the webpage
	function draw()
	{
		global $debugging;
		if(!$debugging)
		$this->mgraph->Stroke();
	}
	
	public function getWidth()
	{
		return $this->width;
	}

	public function getHeight()
	{
		return $this->height;
	}
	
}

?>