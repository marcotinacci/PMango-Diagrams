<?php
		
	require_once "TaskDataTreeGenerator.php";
	require_once "TaskDataTree.php";
	require_once "TaskData.php";

	$treeg = new TaskDataTreeGenerator();
	$tdt = $treeg->generateTaskDataTree(null);

	echo "<h3>WBS</h3>";
	
	$lev1 = $tdt->getRoot()->getChildren();
	echo "livello 1";
	echo " (numero task = ".sizeOf($lev1).")<br>";
	for ($i=0; $i<sizeOf($lev1); $i++){
		echo "<b>".$lev1[$i]->getInfo()->getWBSId()."->".$lev1[$i]->getInfo()->getTaskName()."</b><br>";
	}

	$lev2 = array();
	for($i=0; $i<sizeOf($lev1); $i++){
		$tmp = $lev1[$i]->getChildren();
		for($j=0; $j<sizeOf($tmp); $j++){
			$lev2[] = $tmp[$j];
		}
	}
	echo "livello 2";
	echo " (numero task = ".sizeOf($lev2).")<br>";
	for ($i=0; $i<sizeOf($lev2); $i++){
		echo "<b>".$lev2[$i]->getInfo()->getWBSId()."->".$lev2[$i]->getInfo()->getTaskName()."</b><br>";
	}

	$lev3 = array();
	for($i=0; $i<sizeOf($lev2); $i++){
		$tmp = $lev2[$i]->getChildren();
		for($j=0; $j<sizeOf($tmp); $j++){
			$lev3[] = $tmp[$j];
		}
	}
	echo "livello 3";
	echo " (numero task = ".sizeOf($lev3).")<br>";
	for ($i=0; $i<sizeOf($lev3); $i++){
		echo "<b>".$lev3[$i]->getInfo()->getWBSId()."->".$lev3[$i]->getInfo()->getTaskName()."</b><br>";
	}
	
	//VISITA IN PROFONDITA'
	$deep = $tdt->deepVisit();
	echo "<h3>Visita in profondità</h3>";

	for($i=0; $i<sizeOf($deep); $i++){
		echo "<b>".$deep[$i]->getInfo()->getWBSId()."</b>: <b>".$deep[$i]->getInfo()->getTaskName()."</b> --- ";
	}
	echo " <b>END</b><br>";

	
	$wide = $tdt->wideVisit();
	echo "<h3>Visita in ampiezza</h3>";
	for($i=0; $i<sizeOf($wide); $i++){
		echo "<b>".$wide[$i]->getInfo()->getWBSId()."</b>: <b>".$wide[$i]->getInfo()->getTaskName()."</b> --- ";
	}
	echo " <b>END</b><br>";
	
	
	$leaves = $tdt->getLeaves();
	echo "<h3>Foglie</h3>";
	for($i=0; $i<sizeOf($leaves); $i++){
		echo "<b>".$leaves[$i]->getInfo()->getWBSId()."</b>: <b>".$leaves[$i]->getInfo()->getTaskName()."</b> --- ";
	}
	echo " <b>END</b><br>";
	
	$str = "1.2.3";
	echo "La stringa esaminata: ".$str."<br>";
	echo "substr(str, 0, 0) ritorna: ".substr($str,0,0)."<br>";
	echo "substr(str, 0, 1) ritorna: ".substr($str,0,1)."<br>";
	echo "substr(str, 0, 2) ritorna: ".substr($str,0,2)."<br>";
	
	$str1 = "222";
	echo $str1." è una stringa senza punti.<br>";
	echo "La funzione explode() con separatore punto, ritorna ".explode(".", $str1);
	
	$arr_curr = explode('.', $str);
	echo "la stringa esplosa con separatore .: "; 
	for($s=0; $s<sizeOf($arr_curr); $s++){
		echo $arr_curr[$s];
		$str_curr .= $arr_curr[$s];
	}

	echo "<br>La stringa presa passo passo dall'array viene: ".$str_curr;
?>