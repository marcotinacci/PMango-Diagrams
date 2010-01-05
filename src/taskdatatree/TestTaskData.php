<?php
		
	require_once "TaskDataTreeGenerator.php";
	require_once "TaskDataTree.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);
	
	$deep = $tdt->deepVisit();
	echo "<br><br>Visita in profondità<br>";

	echo "a mano:";
	$info_0 = $deep[0]->getInfo();
	$name_0 = $info_0->getTaskName();
	echo $name_0." ... ";	
	$info_6 = $deep[6]->getInfo();
	$name_6 = $info_6->getTaskName();
	echo $name_6.".";
	echo " La settima non la prende (la 2.1.1).. come mai???<br><br>";
	
	echo "col ciclo...<br>";
	for($i=0; $i<sizeOf($deep); $i++){
		$current_info = $deep[$i]->getInfo();
		echo "Task ".$current_info->getWBSId().": ".$current_info->getTaskName()."  ->";
	}
	echo " END<br>";

	/*
	$wide = $tdt->wideVisit();
	echo "<br><br>Visita in ampiezza<br>";
	for($i=0; $i<sizeOf($wide); $i++){
		$current_info = $wide[$i]->getInfo();
		echo "Task ".$current_info->getWBSId().": ".$current_info->getTaskName()."  ->";
	}
	
	$leaves = $tdt->getLeaves();
	echo "<br><br>Solo le foglie<br>";
	for($i=0; $i<sizeOf($leaves); $i++){
		$current_info = $leaves[$i]->getInfo();
		echo "Task ".$current_info->getWBSId().": ".$current_info->getTaskName()."  ->";
	}
	*/
?>