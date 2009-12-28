<?php
		
	require_once "TaskDataTreeGenerator.php";
	require_once "TaskDataTree.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);
	$figli = $tdt->getRoot()->getChildren();
	echo $figli[0];
?>