<?php 
require_once dirname(__FILE__).'/Task.php';
require_once dirname(__FILE__).'/../useroptionschoice/UserOptionsChoice.php';

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
?>