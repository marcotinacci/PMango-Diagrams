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
	//@TODOrequire_once "query.class.php";

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
		/*
		//$tasks contiene tutti i dati costruiti dal DataArrayBuilder
		$tasks = $this->getData();
		$root = new TaskData();
		//cerco il wbsID più lungo per sapere il livello massimo
		//IMPORTANTE: la funzione explode() restituisce qualcosa se non trova separatori???
		$max = 0;
		for($i=0; $i<sizeOf($tasks); $i++){
			$wbs_id = $tasks[i]->getWBSId();
			$level = explode(".", $wbs_id);
			if($max<sizeOf($level)){
				$max = sizeOf($level);
			}
		}
		
		//costruisco il primo livello
		$first_level = array();
		for($i=0; $i<sizeOf($tasks); $i++){
			if($tasks[$i]->getLevel()==1){
				$first_level[] = new TaskData($tasks[$i]);
			}
		}
		$root->setChildren($first_level);
		
		//composizione dell'albero
		$next_level = array();
		$curr_level = $first_level;
		for($i=1; $i<$max+1; $i++){
			for($j=0; $j<sizeOf($curr_level); $j++){
				for($k=0; $k<sizeOf($tasks); $k++{
					$arr_curr = explode(".", $curr_level[$j]->getWBSId());
					for($s=0; $s<sizeOf($arr_curr); $s++){
						$str_curr .= arr_curr[$s];
					}
					$arr_task = explode(".", $curr_task[$k]->getWBSId());
					for($s=0; $s<sizeOf($arr_task); $s++){
						$str_task .= arr_curr[$s];
					}
					if(substr($str_task,0,$i)==substr($str_curr,0,$i){
						
					}
				}
			}
		}
		
		$tdt = new TaskDataTree();
		$tdt->setRoot($root);
		return $tdt;
		*/
		$tasks = $this->getData(); //preleva le info
		$root = new TaskData();
		
		
		$d1 = new Task();
		$d1->setData($tasks[0]);
		
		$d2 = new Task();
		$d2->setData($tasks[1]);
		
		$d1_1 = new Task();
		$d1_1->setData($tasks[2]);
		
		$d1_2 = new Task();
		$d1_2->setData($tasks[3]);
		
		$d2_1 = new Task();
		$d2_1->setData($tasks[4]);
		
		$d2_2 = new Task();
		$d2_2->setData($tasks[5]);
		
		$d2_1_1 = new Task();
		$d2_1_1->setData($tasks[6]);

		$d2_1_2 = new Task();
		$d2_1_2->setData($tasks[7]);
		//le informazioni sono incapsulate nei task
		//---------------------------//
		$td1 = new TaskData();
		$td1->setInfo($d1);
		
		$td2 = new TaskData();
		$td2->setInfo($d2);
		
		$td1_1 = new TaskData();
		$td1_1->setInfo($d1_1);
		
		$td1_2 = new TaskData();
		$td1_2->setInfo($d1_2);
		
		$td2_1 = new TaskData();
		$td2_1->setInfo($d2_1);
		
		$td2_2 = new TaskData();
		$td2_2->setInfo($d2_2);
		
		$td2_1_1 = new TaskData();
		$td2_1_1->setInfo($d2_1_1);

		$td2_1_2 = new TaskData();
		$td2_1_2->setInfo($d2_1_2);	
		//i task vengono incapsulati nei nodi task_data
		
		 //costruzione dell'albero
		$td2_1->setChildren(array($td2_1_1, $td2_1_2));
		$td2->setChildren(array($td2_1, $td2_2));
		$td1->setChildren(array($td1_1, $td1_2));
		$root->setChildren(array($td1, $td2));
		
		$tdt = new TaskDataTree();
		$tdt->setRoot($root);
		return $tdt;
	}
	
	/**
	 * Metodo per l'accesso ai dati dei task. Si prende i task_id (differenza tra task_id e wbs_id)
	 * e si utilizza il metodo makeTask passando gli id dei task per ritornare un array di Task.
	 * @return $recovered_data sono i dati recuperati riguardanti i task
	 */
	public function getData(){
		$recovered_data=array(array("wbsIdentifier"=>"1", "name"=>"Analisi"),array("wbsIdentifier"=>"2", "name"=>"Sviluppo"),array("wbsIdentifier"=>"1.1", "name"=>"Use Case"),array("wbsIdentifier"=>"1.2", "name"=>"Domain Model"),array("wbsIdentifier"=>"2.1", "name"=>"Progettazione"),array("wbsIdentifier"=>"2.2", "name"=>"Codifica"),array("wbsIdentifier"=>"2.1.1", "name"=>"TaskBox"), array("wbsIdentifier"=>"2.1.2", "name"=>"Gantt"));
		/*
		$recovered_data = array();
		$task_ids = array();
		//@TODO query per tirare su dal DB i task_id,
		//da mettere nella variabile $task_ids.
		//la query dovrebbe risultare
		
		//SELECT task_id
		//FROM nome tabella AS alias
		
		$sql = 'SELECT task_id FROM tasks';
		$task_ids = db_loadList($sql);
		for ($i=0; $i<sizeOf($task_ids); $i++){
			$current_task = Task::makeTask($task_ids[$i]);
			$recovered_data[] = $current_task;
		}
		return $recovered_data;
		*/
		return $recovered_data;
	}
}

