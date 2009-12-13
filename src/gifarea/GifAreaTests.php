<?php

require_once "./GifImage.php";
require_once "./GifBox.php";
require_once "./GifLabel.php";
require_once "./BoxedLabel.php";
require_once "./GifProgressBar.php";
require_once "./GifTaskBox.php";

$gif = new GifImage(800,550);

$areas=array();


$areas[sizeOf($areas)] = new GifBox(10,10,10,10);
$areas[sizeOf($areas)] = new GifBox(20,20,30,30);
$areas[sizeOf($areas)] = new GifLabel(12,12,50,10,"Ciao");
$areas[sizeOf($areas)] = new BoxedLabel(50,50,50,30,"Bona!");

$bWidth=100;

$areas[sizeOf($areas)] = new GifProgressBar(200,200,$bWidth,10,20);
$areas[sizeOf($areas)] = new GifProgressBar(200,210,$bWidth,10,30);
$areas[sizeOf($areas)] = new GifProgressBar(200,220,$bWidth,10,40);
$areas[sizeOf($areas)] = new GifProgressBar(200,230,$bWidth,10,50);
$areas[sizeOf($areas)] = new GifProgressBar(200,240,$bWidth,10,60);

$areas[sizeOf($areas)] = new GifTaskBox(200,300,100,100);

for($i=0; $i<sizeOf($areas); $i++)
	$areas[$i]->drawOn($gif);

//chmod("./","777");
//$gif->saveToFile("./pippo.gif");
$gif->draw();

?>