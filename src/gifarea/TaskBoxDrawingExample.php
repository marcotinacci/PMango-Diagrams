<?php

require_once "./GifImage.php";
require_once "./GifBox.php";
require_once "./GifLabel.php";
require_once "./BoxedLabel.php";
require_once "./GifProgressBar.php";
require_once "./GifTaskBox.php";
require_once "./DrawingHelper.php";

$gif = new GifImage(800,550);

$areas=array();

$areas[] = new GifTaskBox(0,300,100,80,null);
$areas[] = new GifTaskBox(200,300,100,80,null);
$areas[] = new GifTaskBox(400,300,100,80,null);
$areas[] = new GifTaskBox(600,300,100,80,null);

$xs[]=50; $ys[]=300;
$xs[]=250; $ys[]=300;
$xs[]=450; $ys[]=300;
$xs[]=650; $ys[]=300;

//DrawingHelper::ExplodedLineFromTo(350,100,$xs,$ys,$gif);
DrawingHelper::ExplodedUpRectangularLineFromTo(350,100,$xs,$ys,$gif);

foreach($areas as $a)
	$a->drawOn($gif);

$gif->draw();

?>