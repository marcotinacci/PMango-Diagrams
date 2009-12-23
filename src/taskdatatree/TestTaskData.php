<?php
		
	require_once "TaskDataTreeGenerator.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);
	echo $tdt
?>