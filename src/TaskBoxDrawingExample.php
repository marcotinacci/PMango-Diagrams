<?php

require_once dirname(__FILE__)."/taskdatatree/StubTask.php";
require_once dirname(__FILE__)."/taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/gifarea/GifImage.php";
require_once dirname(__FILE__)."/gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/gifarea/DrawingHelper.php";
require_once dirname(__FILE__)."/gifarea/GifBoxedLabel.php";

$gif = new GifImage(800,550);

/*
$boxedLabel = new GifBoxedLabel(6,6,100,20,"Ciao",10);
$boxedLabel->drawOn($gif);

$boxedLabel1 = new GifBox(6,6,100,20);
$boxedLabel1->drawOn($gif);

$boxedLabel2 = new GifBox(6,6,100,20);
$boxedLabel2->drawOn($gif);
*/



$areas=array();

$task = new StubTask();
$taskData = new TaskData();
$taskData->setInfo($task);


$areas[] = new GifTaskBox(300,50,150,30,$taskData);
$areas[0]->setFontsize(14);

$areas[] = new GifTaskBox(0,350,200,10,$taskData);
$areas[] = new GifTaskBox(200,350,200,10,$taskData);
$areas[] = new GifTaskBox(400,350,200,10,$taskData);
$areas[] = new GifTaskBox(600,350,200,10,$taskData);

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