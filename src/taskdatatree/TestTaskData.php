<?php
		
	require_once "TaskDataTreeGenerator.php";
	require_once "TaskDataTree.php";
	require_once "TaskData.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);

	echo "<b><h3>WBS</h3></b><br>";
	echo "livello 1<br>";
	$lev1 = $tdt->getRoot()->getChildren();
	echo "<b>".$lev1[0]->getInfo()->getWBSId()."->".$lev1[0]->getInfo()->getTaskName()."</b><br>";
	echo "<b>".$lev1[1]->getInfo()->getWBSId()."->".$lev1[1]->getInfo()->getTaskName()."</b><br>";

	$lev2 = $lev1[0]->getChildren();
	echo "livello 2<br>";
	echo "<b>".$lev2[0]->getInfo()->getWBSId()."->".$lev2[0]->getInfo()->getTaskName()."</b><br>";
	echo "<b>".$lev2[1]->getInfo()->getWBSId()."->".$lev2[1]->getInfo()->getTaskName()."</b><br>";
	$lev2 = $lev1[1]->getChildren();
	echo "<b>".$lev2[0]->getInfo()->getWBSId()."->".$lev2[0]->getInfo()->getTaskName()."</b><br>";
	echo "<b>".$lev2[1]->getInfo()->getWBSId()."->".$lev2[1]->getInfo()->getTaskName()."</b><br>";


	$lev3 = $lev2[0]->getChildren();
	echo "livello 3<br>";
	echo "<b>".$lev3[0]->getInfo()->getWBSId()."->".$lev3[0]->getInfo()->getTaskName()."</b><br>";
	echo "<b>".$lev3[1]->getInfo()->getWBSId()."->".$lev3[1]->getInfo()->getTaskName()."</b><br>";
	
	$deep = $tdt->deepVisit();
	echo "<br><br>Visita in profondità<br>";

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
	echo " END<br>";
	*/
	
	$leaves = $tdt->getLeaves();
	echo "<br><br>Solo le foglie<br>";
	for($i=0; $i<sizeOf($leaves); $i++){
		$current_info = $leaves[$i]->getInfo();
		echo "Task ".$current_info->getWBSId().": ".$current_info->getTaskName()."  ->";
	}
	
?>