<?php
		
	require_once "TaskDataTreeGenerator.php";
	require_once "taskDataTree.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);
	$figli = $tdt->getRoot()->getChildren();
	echo $figli[0];
?>