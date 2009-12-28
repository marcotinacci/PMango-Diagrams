<?php
		
	require_once "TaskDataTreeGenerator.php";
	require_once "TaskDataTree.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);
	$root = $tdt->getRoot();
	$figli = $root->getChildren();
	
	$info_1 = $figli[0]->getInfo();
	$data_id_1 = $info_1->getWBSId();
	$data_name_1 = $info_1->getTaskName();

	$info_2 = $figli[1]->getInfo();
	$data_id_2 = $info_2->getWBSId();
	$data_name_2 = $info_2->getTaskName();
	
	echo "L'id della prima attività: ".$data_id_1;
	echo "<br> Il nome della prima attività :".$data_name_1;
	
	echo "<br><br>L'id della seconda attività: ".$data_id_2;
	echo "<br> Il nome della seconda attività :".$data_name_2;

?>