<?php

$file=fopen("./errors.txt","w");
function myerrorhandler($errno,$errstr,$errfile,$errline)
{
	global $file;
	fwrite($file,$errno . " " . $errstr . " " . $errfile  . " " . $errline . "\n");
	return true;
}
set_error_handler("myerrorhandler");
date_default_timezone_set("Europe/Rome");
//--------------------------------------------------
$baseDir = dirname(__FILE__)."/../../..";
// automatically define the base url
$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
if (@$pathInfo) {
  $baseUrl .= dirname($pathInfo);
} else {
  $baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
}
require_once "$baseDir/includes/config.php";
require_once "$baseDir/includes/session.php";
// manage the session variable(s)
dPsessionStart(array('AppUI'));

// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    if (isset($_GET['logout']) && isset($_SESSION['AppUI']->user_id))
    {
        $AppUI =& $_SESSION['AppUI'];
		$user_id = $AppUI->user_id;
        addHistory('login', $AppUI->user_id, 'logout', $AppUI->user_first_name . ' ' . $AppUI->user_last_name);
    }

	$_SESSION['AppUI'] = new CAppUI;
}
$AppUI =& $_SESSION['AppUI'];
//--------------------------------------------------


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



$gif = new GifImage(800,750);

//DrawingHelper::segmentedOffsetLine(50,50,20,-10,70,40,$gif);

$pStyle = new LineStyle();
$pStyle->patterNumberOfDots = 3;
$pStyle->patternInitialFinalLength = 10;
$pStyle->weight = 2;
/*
DrawingHelper::segmentedOffsetLine(100,100,80,-80,180,20,$gif,$pStyle);
DrawingHelper::segmentedOffsetLine(100,100,80,80,180,180,$gif,$pStyle);
DrawingHelper::segmentedOffsetLine(100,100,-80,-80,20,20,$gif,$pStyle);
DrawingHelper::segmentedOffsetLine(100,100,-80,80,20,180,$gif,$pStyle);
*/

//DrawingHelper::LineFromTo(0,0,100,200,$gif,$pStyle);

//$c = new GifCircle($gif,100,200,5);
//$c->drawOn();
//DrawingHelper::LineFromTo(100,100,200,0,$gif,$pStyle);
//DrawingHelper::LineFromTo(200,0,400,0,$gif,$pStyle);
//DrawingHelper::LineFromTo(0,0,100,100,$gif,$pStyle);

$areas=array();


//$uoc = UserOptionsChoice::GetInstance(); 
//$uoc->setFromArray($_GET);

$task = new StubTask();
$taskData = new TaskData();
$taskData->setInfo($task);

/*
$g = new GifBox($gif,10,10,100,20);
$g->drawOn();
*/

//$points = $g->getBottomMiddlePoint();

/*$c = new GifCircle($gif,$points['x'],$points['y'],4);
$c->drawOn();*/

/*
$l = new GifLabel($gif,100,100,40,20,"Ciao",10);
$l->drawOn();*/

$areas[] = new GifTaskBox($gif,275,50,170,30,$taskData);
$start_point = $areas[0]->getBottomMiddlePoint();
//$areas[0]->setFontsize(14);

/*
$areas[] = new GifTaskBox($gif,0,350,170,30,$taskData);
$areas[] = new GifTaskBox($gif,200,350,170,30,$taskData);
$areas[] = new GifTaskBox($gif,400,350,170,30,$taskData);
$areas[] = new GifTaskBox($gif,600,350,170,30,$taskData);
*/

$xs[]=50; $ys[]=350;
$xs[]=250; $ys[]=350;
$xs[]=450; $ys[]=350;
$xs[]=650; $ys[]=350;

$s = new LineStyle();
$s->style = "longdashed";
$s->weight = 1;
$s->color = "black";

//DrawingHelper::ExplodedLineFromTo(350,100,$xs,$ys,$gif);
//DrawingHelper::LineFromTo(0,0,100,100,$gif);
//DrawingHelper::ExplodedUpRectangularLineFromTo(300,0,$xs,$ys,$gif);
//DrawingHelper::LineFromTo(0,0,100,100,$gif);
foreach($areas as $a)
	$a->drawOn();


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


$gifTriangle = new GifTriangle($gif,100,350,20,60,"left");
$gifTriangle->drawOn();

/*
DrawingHelper::GanttFTSLine(100,100,150,150,10,true,true,false,$gif,null);
DrawingHelper::GanttFTSLine(100,100,150,50,10,true,true,false,$gif,null);
DrawingHelper::GanttFTSLine(100,100,50,50,10,true,true,false,$gif,null);
DrawingHelper::GanttFTSLine(100,100,50,150,10,true,true,false,$gif,null);
*/

$gif->draw();


?>