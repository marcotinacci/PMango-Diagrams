<?php

require_once "./GifImage.php";
require_once "./GifTaskBox.php";
require_once "./DrawingHelper.php";

$gif = new GifImage(800,550);

$areas=array();

$areas[] = new GifTaskBox(300,50,100,100,null);

$areas[] = new GifTaskBox(0,350,100,100,null);
$areas[] = new GifTaskBox(200,350,100,100,null);
$areas[] = new GifTaskBox(400,350,100,100,null);
$areas[] = new GifTaskBox(600,350,100,100,null);

$xs[]=50; $ys[]=350;
$xs[]=250; $ys[]=350;
$xs[]=450; $ys[]=350;
$xs[]=650; $ys[]=350;

$s = new LineStyle();
$s->style = "longdashed";
$s->weight = 2;
$s->color = "black";

//DrawingHelper::ExplodedLineFromTo(350,100,$xs,$ys,$gif);
DrawingHelper::ExplodedUpRectangularLineFromTo(350,150,$xs,$ys,$gif,$s);

foreach($areas as $a)
	$a->drawOn($gif);
	
DrawingHelper::drawArrow(50,350,30,30,0,$gif);

$gif->draw();

?>