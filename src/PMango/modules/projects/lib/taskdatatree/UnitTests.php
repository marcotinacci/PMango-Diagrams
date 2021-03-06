<?php 
require_once dirname(__FILE__).'/Task.php';
require_once dirname(__FILE__).'/../useroptionschoice/UserOptionsChoice.php';
require_once dirname(__FILE__) . '/TaskDataTreeGenerator.php';
require_once dirname(__FILE__) . '/../chartgenerator/TaskNetworkChartGenerator.php';

global $AppUI;
$tasks_closed = $AppUI->getState("tasks_closed");
$tasks_opened = $AppUI->getState("tasks_opened");
// in $open_task there is a ID of a task, for each open task
foreach($tasks_opened as $open_task) {
	$task = Task::MakeTask($open_task);
	print "WBS identifier of task with id = " . $open_task . " is equals to " .
	$task->getWBSId() . "<br>";
}

print "note that task ids = {" . implode(",", $tasks_opened) . "} are the task ids" .
" of the tasks that are exploded " . "into the view tab that you have modified<br>";

foreach($tasks_closed as $close_task) {
	print $close_task . " - ";
}

print "<br>The wbs plan was exploded at " . $AppUI->getState('ExplodeTasks', '1') . " level";

print "I'm going to check the wbs explosion:<br>";
print "The following id will be draw: {" . implode(" - ", 
	UserOptionsChoice::GetInstance()->retrieveDrawableTasks(
		$AppUI->getState('ExplodeTasks', '1'), 
		$AppUI->getState("tasks_opened"),
		$AppUI->getState("tasks_closed"))->getDrawableTasks()) . "}";

print "<br>generating the tree";
$tdtGenerator = new TaskDataTreeGenerator();
$tdt = $tdtGenerator->generateTaskDataTree();

print "<br>Getting the leaves...";
$leaves = $tdt->getLeaves();
foreach ($leaves as $leaf) {
	print " " . $leaf->getInfo()->getTaskID();	
}

print "<br>Getting the visible leaves...";
$visibleleaves = $tdt->getVisibleLeaves();
foreach ($visibleleaves as $leaf) {
	print "<br>visible leaf " . $leaf->getInfo()->getTaskID() .
		" is collapsed " . $leaf->getCollapsed() . " / is atomic " . 
		$leaf->isAtomic();	
}

print "<br>analising deep dependency...";
$dependencyMap = $tdt->computeDependencyRelationOnVisibleTasks();
foreach ($dependencyMap as $needed => $dependants) {
	print "<br>Needed leaf task: " . $needed; 
//	" that has this dependants: " . implode(", ", $dependants);
	foreach ($dependants as $depDescriptorId => $descriptorsArray) {
		print "<br> / for " . $depDescriptorId . " exists these descriptors: ";
		foreach($descriptorsArray as $descriptor) {
			print "<br>" . $descriptor;
		}
	}
}

print("almeno sono arrivato fino a qui");

//print "<br>Puning the unvisible tasks...";
//$tdt->getVisibleTree();
//print "<br>The new leaves are: ";
//$leaves = $tdt->getLeaves();
//foreach ($leaves as $leaf) {
//	print " " . $leaf->getInfo()->getTaskID();	
//}

//print "<br>preparing the generating process for tn graph: ";
//$tnGenerator = new TaskNetworkChartGenerator();
//$tnGenerator->generateChart();
//print "i task sul chart dovrebbero essere tutti presenti.";
//$tnGenerator->getChart()->draw();
//print "<img src=\"". $tnGenerator->getChart() . "\">";
//$tnGenerator->getChart()->saveToFile("/Users/massimonocentini/tmp/tn.gif");

?>