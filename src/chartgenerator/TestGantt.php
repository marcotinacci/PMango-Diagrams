<?php
//error_reporting(E_ALL & ~E_NOTICE);

require_once dirname(__FILE__)."/GanttChartGenerator.php";

$gcg = new GanttChartGenerator();
$gcg->generateChart();
?>