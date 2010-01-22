<?php
//ini_set('display_errors', 0);
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
$file=fopen("./tnerrors.txt","w");
function myerrorhandler($errno,$errstr,$errfile,$errline)
{
	global $file;
	fwrite($file,$errno . " " . $errstr . " " . $errfile  . " " . $errline . "\n");
	return true;
}
set_error_handler("myerrorhandler");

//--------------------------------------------------
$baseDir = dirname(__FILE__)."/../../../..";
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


require_once dirname(__FILE__)."/TaskNetworkChartGenerator.php";
$debugging = false;
$tng = new TaskNetworkChartGenerator();
$tng->generateChart();


?>