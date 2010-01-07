<?php
require_once dirname(__FILE__)."/WBSChartGenerator.php";

error_reporting(E_ALL & ~E_NOTICE);

$WBS = new WBSChartGenerator();
$WBS->generateChart();

?>