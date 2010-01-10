<?php

/**
 * Questa classe accede alla LibDB già presente e recupera le informazioni
 * dei task secondo il parametro UserOptionChoice.
 * Infine richiede la costruzione della struttura ad albero per la
 * gestione dei dati ricavati.
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

require_once dirname(__FILE__)."/TaskDataTree.php";
require_once dirname(__FILE__)."/TaskData.php";
require_once dirname(__FILE__)."/Task.php";
require_once dirname(__FILE__).'/StubTaskDataTree.php';
require_once dirname(__FILE__).'/../../../../includes/db_connect.php';
require_once dirname(__FILE__).'/../../../../includes/main_functions.php';

class TaskDataTreeGenerator{

	/**
	 * Questo metodo genera un TaskDataTree utilizzando getData() per
	 * recuperare i dati dal DB (considerando le uoc).
	 * @return TaskDataTree
	 */
	public function stubGenerateTaskDataTree(){
		return new StubTaskDataTree();
	}

	private function findTaskDataIdInArray($array,$id)
	{
		foreach($array as $a)
		{
			if($a->getInfo()->getWbsId() == $id)
			return $a;
		}
		die("Node '$id' not found!");
	}

	public function generate() {
		$tasks = $this->getData();

		print "<br>Tasks count: " . count($tasks);
		
		$taskDatasMap = array();

		global $AppUI;
		$visibleTasks = $visible_tasks = UserOptionsChoice::GetInstance()->retrieveDrawableTasks(
		$AppUI->getState('ExplodeTasks', '1'),
		$AppUI->getState("tasks_opened"),
		$AppUI->getState("tasks_closed"))->getDrawableTasks();

		// for each task build a TaskData
		foreach ($tasks as $task) {
			$task_id = $task->getTaskID();
			print "<br>" . $task_id;
			
			$taskData = new TaskData($task);
			$taskDatasMap[$task_id] = $taskData;
				
			if(in_array($task_id, $visibleTasks)) {
				$taskData->setVisibility(true);
				print "task " . $task_id . " is visible";
			}
		}

		$root = new TaskData();
		foreach ($taskDatasMap as $task_id => $taskData) {
			if ($taskData->getInfo()->isChildOfRoot()) {
				print "<br>task " . $task_id . " is child of the root";
				$root->addChild($taskData);
			}
			else {
				$taskDatasMap[$taskData->getInfo()->getCTask()->task_parent]->addChild($taskData);
			}
		}
		
		foreach ($taskDatasMap as $key => $value) {
			print "<br>" . $key . " ";
			foreach ($value->getChildren() as $child) {
				print "child: " . $child->getInfo()->getTaskID() . " ";
			}
		}

		return $root;

	}

	/**
	 * Questo metodo genera un TaskDataTree utilizzando getData() per
	 * recuperare i dati dal DB (considerando le uoc).
	 * @param UserOptionsChoice $uoc
	 * @return TaskDataTree $tdt
	 */
	public function generateTaskDataTree(){
		//print "start tree generation<br>";
		//$tasks contiene tutti i dati costruiti dal DataArrayBuilder
		$tasks = $this->getData();
		$root = new TaskData();
		global $AppUI;
		/*$visible_tasks = UserOptionsChoice::GetInstance()->retrieveDrawableTasks(
			$AppUI->getState('ExplodeTasks', '1'),
			$AppUI->getState("tasks_opened"),
			$AppUI->getState("tasks_closed"))->getDrawableTasks();*/
		/////cerco il wbsID più lungo per sapere il livello massimo
		$max = 0;
		for($i=0; $i<sizeOf($tasks); $i++){
			$wbs_id = $tasks[$i]->getWBSId();
			$level = explode(".", $wbs_id);
			if($max<sizeOf($level)){
				$max = sizeOf($level);
			}
		}

		/////costruzione primo livello
		$first_level = array();
		for($i=0; $i<sizeOf($tasks); $i++){
			if($tasks[$i]->getLevel()==1){
				$first_level[] = new TaskData($tasks[$i]);
			}
		}
		$root->setChildren($first_level);

		/////inizializzazione variabili necessarie alla costruzione
		//next_level viene costruito via via, e verrà utilizzato per il ciclo successivo
		$next_level = array();
		//curr_level contiene il livello corrente di cui stiamo individuando i sottotask
		$curr_level = $first_level;
		//conterrà i sottotask di uno specifico task del livello corrente
		$son = array();
		//variabile booleana per il controllare se un task è sottotask di un altro
		$descendant_of = true;
		for($i=1; $i<$max; $i++){
			for($j=0; $j<sizeOf($curr_level); $j++){
				for($k=0; $k<sizeOf($tasks); $k++){
					//prendo gli array contenenti i vari pezzi dell'Id del current e del task
					$arr_curr = explode(".", $curr_level[$j]->getInfo()->getWBSId());
					$arr_task = explode(".", $tasks[$k]->getWBSId());
					//ciclo sulla parte significativa dell'Id, delimitata da $i
					//il controllo $i<sizeOf(arr_task) evita i
					if($i<=sizeOf($arr_task)){
						for($s=0; $s<$i; $s++){
							if($arr_curr[$s]!=$arr_task[$s]){
								$descendant_of = false;
							}
						}
					}
					else{
						$descendant_of = false;
					}
					//controllo il booleano e la dimensione dell'id
					//infatti per essere figlio deve avere l'id più lungo del padre di una sola posizione.
					if($descendant_of && sizeOf($arr_task)==sizeOf($arr_curr)+1){
						$task_data = new TaskData($tasks[$k]);
						//controlla se il task è effettivamente tra i task da mostrare
						for($s=0; $s<sizeOf($visible_tasks); $s++){
							if($tasks[$k]->getTaskID()==$visible_tasks[$s]){
								$task_data->setVisibility(true);
							}
						}
						$son[] = $task_data;
						$next_level[] = $task_data;
					}
					else{
						$son_of=true;
					}
				}
				$curr_level[$j]->setChildren($son);
				//se in $son i taskData non sono visibili
				//(in questo caso si fa il controllo solo sul primo, se c'è,
				//logicamente dovrebbe essere la stessa cosa anche per gli altri.
				//si setta il parent come collapsed
				if(sizeOf($son)>0){
					if($son[0]->getVisibility()==false){
						$curr_level[$j]->setCollapsed(true);
					}
				}
				$son = array();
			}
			$curr_level = $next_level;
		}

		$tdt = new TaskDataTree();
		$tdt->setRoot($root);
		$tdt->setAllDependencies();
		//print "end tree generation";
		return $tdt;
	}

	/**
	 * Metodo per l'accesso ai dati dei task. Si prende i task_id (differenza tra task_id e wbs_id)
	 * e si utilizza il metodo makeTask passando gli id dei task per ritornare un array di Task.
	 * @return $recovered_data sono i dati recuperati riguardanti i task
	 */
	public function getData(){
		//	$recovered_data=array(array("wbsIdentifier"=>"1", "name"=>"Analisi"),array("wbsIdentifier"=>"2", "name"=>"Sviluppo"),array("wbsIdentifier"=>"1.1", "name"=>"Use Case"),array("wbsIdentifier"=>"1.2", "name"=>"Domain Model"),array("wbsIdentifier"=>"2.1", "name"=>"Progettazione"),array("wbsIdentifier"=>"2.2", "name"=>"Codifica"),array("wbsIdentifier"=>"2.1.1", "name"=>"TaskBox"), array("wbsIdentifier"=>"2.1.2", "name"=>"Gantt"));
		$recovered_data = array();
		$task_ids = array();
		$project_id = defVal(@$_REQUEST['project_id'], 0);
		if($project_id==0){
			die("ERROR: project not found!");
		}
		// $project_id = 0; // ?? stub ?? non esiste il progetto zero
		$sql = 'SELECT task_id FROM tasks WHERE task_project ='.$project_id;
		$task_ids = db_loadList($sql);
		for ($i=0; $i<sizeOf($task_ids); $i++){
			$current_task = Task::makeTask($task_ids[$i]['task_id']);
			$recovered_data[] = $current_task;
		}
		return $recovered_data;
	}
}