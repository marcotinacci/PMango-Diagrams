<?php

if(!isset($_REQUEST['CHART_TYPE']))
die("ERROR! NO CHART SPECIFIED");

$chart_type = $_REQUEST['CHART_TYPE'];

//ErrorHandler to ensure the image drawing when there is no fatal errors
$file=fopen("./".$chart_type."_ImageGenerationErrors.txt","w");
function myerrorhandler($errno,$errstr,$errfile,$errline)
{
	global $file;
	fwrite($file,$errno . " " . $errstr . " " . $errfile  . " " . $errline . "\n");
	return true;
}
set_error_handler("myerrorhandler");
$debugging=false;

//ErrorHandler to ensure the image drawing when there is no fatal errors
$baseDir = dirname(__FILE__)."/../../../..";
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

if (! isset($GLOBALS['OS_WIN']))
$GLOBALS['OS_WIN'] = (stristr(PHP_OS, "WIN") !== false);

require_once "$baseDir/includes/db_adodb.php";
require_once "$baseDir/includes/db_connect.php";
require_once "$baseDir/includes/main_functions.php";
require_once "$baseDir/classes/ui.class.php";
require_once "$baseDir/classes/permissions.class.php";
require_once "$baseDir/includes/session.php";

// don't output anything. Usefull for fileviewer.php, gantt.php, etc.
$suppressHeaders = dPgetParam( $_GET, 'suppressHeaders', false );

// manage the session variable(s)
dPsessionStart(array('AppUI'));

// write the HTML headers
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");	// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");	// always modified
header ("Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] )) {
	$_SESSION['AppUI'] = new CAppUI;
}
$AppUI =& $_SESSION['AppUI'];
$last_insert_id =$AppUI->last_insert_id;

$AppUI->checkStyle();

// load the commonly used classes
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getSystemClass( 'query' ) );

require_once "$baseDir/misc/debug.php";

//Function for update lost action in user_access_log
$AppUI->updateLastAction($last_insert_id);

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];

// clear out main url parameters
$m = '';
$a = '';
$u = '';

$AppUI->setUserLocale();
include_once "$baseDir/locales/$AppUI->user_locale/locales.php";
include_once "$baseDir/locales/core.php";

setlocale( LC_TIME, $AppUI->user_lang );
$AppUI->setUserLocale();
// bring in the rest of the support and localisation files
require_once "$baseDir/includes/permissions.php";


$def_a = 'index';
if (! isset($_GET['m']) && !empty($dPconfig['default_view_m'])) {
	$m = $dPconfig['default_view_m'];
	$def_a = !empty($dPconfig['default_view_a']) ? $dPconfig['default_view_a'] : $def_a;
	$tab = $dPconfig['default_view_tab'];
} else {
	// set the module from the url
	$m = $AppUI->checkFileName(dPgetParam( $_GET, 'm', getReadableModule() ));
}
// set the action from the url
$a = $AppUI->checkFileName(dPgetParam( $_GET, 'a', $def_a));

/* This check for $u implies that a file located in a subdirectory of higher depth than 1
 * in relation to the module base can't be executed. So it would'nt be possible to
 * run for example the file module/directory1/directory2/file.php
 * Also it won't be possible to run modules/module/abc.zyz.class.php for that dots are
 * not allowed in the request parameters.
 */

$u = $AppUI->checkFileName(dPgetParam( $_GET, 'u', '' ));

// load module based locale settings
@include_once "$baseDir/locales/$AppUI->user_locale/locales.php";
@include_once "$baseDir/locales/core.php";

setlocale( LC_TIME, $AppUI->user_lang );
$m_config = dPgetConfig($m);
//--------------------------------------------------


require_once dirname(__FILE__)."/ChartTypesEnum.php";

if($chart_type == ChartTypesEnum::$Gantt)
{
	require_once dirname(__FILE__)."/GanttChartGenerator.php";
	$chart = new GanttChartGenerator();
}
else if($chart_type == ChartTypesEnum::$WBS)
{
	require_once dirname(__FILE__)."/WBSChartGenerator.php";
	$chart = new WBSChartGenerator();
}
if($chart_type == ChartTypesEnum::$TaskNetwork)
{
	require_once dirname(__FILE__)."/TaskNetworkChartGenerator.php";
	$chart = new TaskNetworkChartGenerator();
}
$chart->generateChart();
$chart->getChart()->saveToFile("report_gif_".$chart_type."_".$AppUI->user_id.".gif");