<?php
require_once dirname(__FILE__)."/GanttChartGenerator.php";

error_reporting(E_ALL & ~E_NOTICE);

$gcg = new GanttChartGenerator();
$gcg->generateChart();
?>