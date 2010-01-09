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
	//require_once dirname(__FILE__)."/classes/query.class.php";

class TaskDataTreeGenerator{

	/**
	 * Questo metodo genera un TaskDataTree utilizzando getData() per 
	 * recuperare i dati dal DB (considerando le uoc).
	 * @return TaskDataTree
	 */	
	public function stubGenerateTaskDataTree(){
		return new StubTaskDataTree();
	}
	
	/**
	 * Questo metodo genera un TaskDataTree utilizzando getData() per 
	 * recuperare i dati dal DB (considerando le uoc).
	 * @param UserOptionsChoice $uoc
	 * @return TaskDataTree $tdt
	 */
	public function generateTaskDataTree($uoc){
		//$tasks contiene tutti i dati costruiti dal DataArrayBuilder
		$tasks = $this->getData();
		$root = new TaskData();
		/////cerco il wbsID più lungo per sapere il livello massimo
		$max = 0;
		for($i=0; $i<sizeOf($tasks); $i++){
			$wbs_id = $tasks[i]->getWBSId();
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
					$arr_curr = explode(".", $curr_level[$j]->getWBSId());
					$arr_task = explode(".", $tasks[$k]->getWBSId());
					//ciclo sulla parte significativa dell'Id, delimitata da $i
					//il controllo $i<sizeOf(arr_task) evita i
					if($i<sizeOf($arr_task)){
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
						//TODO settare (controllando le uoc) collapsed
						$son[] = $task_data;
						$next_level[] = $task_data;
					}
					else{
						$son_of=true;
					}
				}
				$curr_level[$j]->setChildren($son);
				$son = array();
			}
			$curr_level = $next_level;
		}
		
		$tdt = new TaskDataTree();
		$tdt->setRoot($root);
		$tdt->setAllDependencies();
		return $tdt;
	}
	
	/**
	 * Metodo per l'accesso ai dati dei task. Si prende i task_id (differenza tra task_id e wbs_id)
	 * e si utilizza il metodo makeTask passando gli id dei task per ritornare un array di Task.
	 * @return $recovered_data sono i dati recuperati riguardanti i task
	 */
	public function getData(){
		$recovered_data = array();
		$task_ids = array();
		//TODO aggiungere la WHERE nella quale si fa riferimento al progetto corrente
		$sql = 'SELECT task_id FROM tasks';
		$task_ids = db_loadList($sql);
		for ($i=0; $i<sizeOf($task_ids); $i++){
			$current_task = Task::makeTask($task_ids[$i]);
			$recovered_data[] = $current_task;
		}
		return $recovered_data;
	}
}

