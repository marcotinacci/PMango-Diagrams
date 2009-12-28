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
	
	
	$secondo_livello = $figli[1]->getChildren();
	$terzo_livello = $secondo_livello[0]->getChildren();
	$info_t_l = $terzo_livello[0]->getInfo();
	
	echo "<br><br>Terzo livello: attività ".$info_t_l->getWBSId().", ".$info_t_l->getTaskName();

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