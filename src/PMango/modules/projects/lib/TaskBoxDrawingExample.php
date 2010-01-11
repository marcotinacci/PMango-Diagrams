<?php

require_once dirname(__FILE__)."/taskdatatree/StubTask.php";
require_once dirname(__FILE__)."/taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/gifarea/GifImage.php";
require_once dirname(__FILE__)."/gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/gifarea/GifGanttTask.php";
require_once dirname(__FILE__)."/gifarea/DrawingHelper.php";
require_once dirname(__FILE__)."/gifarea/GifBoxedLabel.php";
require_once dirname(__FILE__)."/gifarea/GifTriangle.php";
require_once dirname(__FILE__).'/taskdatatree/StubTaskDataTree.php';
require_once dirname(__FILE__)."/useroptionschoice/UserOptionsChoice.php";



$gif = new GifImage(800,550);

//DrawingHelper::segmentedOffsetLine(50,50,20,-10,70,40,$gif);

/*
$pStyle = new LineStyle();
$pStyle->patterNumberOfDots = 3;
$pStyle->patternInitialFinalLength = 10;
$pStyle->weight = 2;
DrawingHelper::segmentedOffsetLine(100,100,80,-80,180,20,$gif,$pStyle);
DrawingHelper::segmentedOffsetLine(100,100,80,80,180,180,$gif,$pStyle);
DrawingHelper::segmentedOffsetLine(100,100,-80,-80,20,20,$gif,$pStyle);
DrawingHelper::segmentedOffsetLine(100,100,-80,80,20,180,$gif,$pStyle);

//DrawingHelper::LineFromTo(0,0,100,200,$gif,$pStyle);

$c = new GifCircle(100,200,5);
$c->drawOn($gif);
//DrawingHelper::LineFromTo(100,100,200,0,$gif,$pStyle);
//DrawingHelper::LineFromTo(200,0,400,0,$gif,$pStyle);
//DrawingHelper::LineFromTo(0,0,100,100,$gif,$pStyle);

$areas=array();
*/

$uoc = UserOptionsChoice::GetInstance(); 
//$uoc->setFromArray($_GET);

$task = new StubTask();
$taskData = new TaskData();
$taskData->setInfo($task);

$areas[] = new GifTaskBox(275,50,170,30,$taskData);
$start_point = $areas[0]->getBottomMiddlePoint();
//$areas[0]->setFontsize(14);

$areas[] = new GifTaskBox(0,350,170,30,$taskData);
$areas[] = new GifTaskBox(200,350,170,30,$taskData);
$areas[] = new GifTaskBox(400,350,170,30,$taskData);
$areas[] = new GifTaskBox(600,350,170,30,$taskData);

$xs[]=50; $ys[]=350;
$xs[]=250; $ys[]=350;
$xs[]=450; $ys[]=350;
$xs[]=650; $ys[]=350;

$s = new LineStyle();
$s->style = "longdashed";
$s->weight = 4;
$s->color = "black";

//DrawingHelper::ExplodedLineFromTo(350,100,$xs,$ys,$gif);
DrawingHelper::ExplodedUpRectangularLineFromTo($start_point['x'],$start_point['y'],$xs,$ys,$gif,$pStyle);

foreach($areas as $a)
	$a->drawOn($gif);


/*
$task = new StubTask();
$taskData = new TaskData($task);

$gTask = new GifGanttTask(
				0, // x start
				1000, // x finish
				100, // y start
				20, // height
				"2010-01-01 01:00:00", // startDate
				"2010-02-01 01:00:00", // finishDate
				$taskData->getInfo(), // task
				null // opzioni utente
				);	
$gTask->drawOn($gif);
*/

/*
$gifTriangle = new GifTriangle(0,350,20,60,"left");
$gifTriangle->drawOn($gif);
*/

//DrawingHelper::GanttDependencyLine(100,100,140,140,10,true,$gif,null);

/*
DrawingHelper::GanttFTSLine(100,100,150,150,10,true,true,false,$gif,null);
DrawingHelper::GanttFTSLine(100,100,150,50,10,true,true,false,$gif,null);
DrawingHelper::GanttFTSLine(100,100,50,50,10,true,true,false,$gif,null);
DrawingHelper::GanttFTSLine(100,100,50,150,10,true,true,false,$gif,null);
*/

$gif->draw();


?>