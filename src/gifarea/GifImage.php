<?php

require_once "./lib/jpgraph/src/jpgraph.php";
require_once "./lib/jpgraph/src/jpgraph_utils.inc";

/* This class is the conceptual idea of the gif, it contains a MultiGraph (used to compose
 *  the canvas of the gifareas) and has methods to save it to file or to print it directly.
 */
class GifImage
{
	//It's a MGraph of the jpGraph library
	private $mgraph;
	
	function __construct($width,$height)
	{
		$this->mgraph = new MGraph($width,$height);
	}	
	
	//Add the specified canvas to the image at position ($x, $y)
	function addCanvas($canvas,$x,$y)
	{
		$this->mgraph->add($canvas,$x,$y);
	}
	
	//Save the gif to file $FileName
	function saveToFile($fileName)
	{
		$this->mgraph->Stream($fileName);
	}
	
	//Draw the gif directly in the webpage
	function draw()
	{
		$this->mgraph->Stroke();
	}
	
}

?>