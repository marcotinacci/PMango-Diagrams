<?php

require_once "./lib/jpgraph/src/jpgraph.php";
require_once "./lib/jpgraph/src/jpgraph_utils.inc";

class GifImage
{
	private $mgraph;
	
	function __construct($width,$height)
	{
		$this->mgraph = new MGraph($width,$height);
	}	
	
	function addCanvas($canvas,$x,$y)
	{
		$this->mgraph->add($canvas,$x,$y);
	}
	
	function saveToFile($fileName)
	{
		$this->mgraph->Stream($fileName);
	}
	
	function draw()
	{
		$this->mgraph->Stroke();
	}
	
}

?>