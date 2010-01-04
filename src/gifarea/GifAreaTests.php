<?php

require_once dirname(__FILE__)."/gifarea/GifImage.php";
require_once dirname(__FILE__)."/gifarea/GifBox.php";
require_once dirname(__FILE__)."/gifarea/GifLabel.php";
require_once dirname(__FILE__)."/gifarea/GifProgressBar.php";
require_once dirname(__FILE__)."/gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/gifarea/DrawingHelper.php";

$gif = new GifImage(800,550);

$areas=array();

/*
$areas[] = new GifBox(10,10,10,10);
$areas[] = new GifBox(20,20,30,30);
$areas[] = new GifLabel(12,12,50,10,"Ciao");

$areas[2]->setTextColor("green");

$areas[] = new BoxedLabel(50,50,50,30,"Bona!");

$bWidth=100;

$areas[] = new GifProgressBar(200,200,$bWidth,10,20);
$areas[] = new GifProgressBar(200,210,$bWidth,10,30);
$areas[] = new GifProgressBar(200,220,$bWidth,10,40);
$areas[] = new GifProgressBar(200,230,$bWidth,10,50);

$areas[6]->setFilledForeColor("red");
$areas[7]->setFilledForeColor("green");

$areas[] = new GifProgressBar(200,240,$bWidth,10,60);

$areas[] = new GifTaskBox(200,300,100,80,null);
*/

/*
DrawingHelper::LineFromTo(10,100,100,10,$gif);
DrawingHelper::LineFromTo(100,10,10,100,$gif);
DrawingHelper::LineFromTo(100,100,10,10,$gif);
DrawingHelper::LineFromTo(10,10,100,100,$gif);
*/

//DrawingHelper::UpRectangularLineFromTo(100,100,120,120,$gif);
$xs[0]=120; $ys[0]=120;
$xs[1]=160; $ys[1]=120;
$xs[2]=180; $ys[2]=120;

//DrawingHelper::ExplodedLineFromTo((180-120)/2+120,60,$xs,$ys,$gif);
DrawingHelper::ExplodedUpRectangularLineFromTo((180-120)/2+120,60,$xs,$ys,$gif);

//DrawingHelper::UpRectangularLineFromTo(10,60 , 60,10 ,$gif);
//DrawingHelper::UpRectangularLineFromTo(60,10,10,60,$gif);
//DrawingHelper::UpRectangularLineFromTo(0,0,120,120,$gif);
//DrawingHelper::UpRectangularLineFromTo(120,120,0,0,$gif);

foreach($areas as $a)
	$a->drawOn($gif);

//chmod("./","777");
//$gif->saveToFile("./pippo.gif");
$gif->draw();

?>