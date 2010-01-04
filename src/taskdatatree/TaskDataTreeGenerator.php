<?php

/**
 * Questa classe accede alla LibDB gi� presente e recupera le informazioni
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
	//@TODOrequire_once "query.class.php";

class TaskDataTreeGenerator{
	
	/**
	 * Questo metodo genera un TaskDataTree utilizzando getData() per 
	 * recuperare i dati dal DB (considerando le uoc).
	 * @param UserOptionsChoice $uoc
	 * @return TaskDataTree $tdt
	 */
	public function generateTaskDataTree($uoc){
		$tasks = $this->getData(); //preleva le info
		$root = new TaskData();
		
		
		$d1 = new Task();
		$d1->setData($array[0]);
		
		$d2 = new Task();
		$d2->setData($array[1]);
		
		$d1_1 = new Task();
		$d1_1->setData($array[2]);
		
		$d1_2 = new Task();
		$d1_2->setData($array[3]);
		
		$d1_3 = new Task();
		$d1_3->setData($array[4]);
		
		$d2_1 = new Task();
		$d2_1->setData($array[5]);
		
		$d2_2 = new Task();
		$d2_2->setData($array[6]);
		
		$d2_1_1 = new Task();
		$d2_1_1->setData($array[7]);

		$d2_1_2 = new Task();
		$d2_1_2->setData($array[8]);//le informazioni sono incapsulate nei task
		//---------------------------//
		$td1 = new TaskData();
		$td1->setInfo($d1);
		
		$td2 = new TaskData();
		$td2->setInfo($d2);
		
		$td1_1 = new TaskData();
		$td1_1->setInfo($d1_1);
		
		$td1_2 = new TaskData();
		$td1_2->setInfo($d1_2);
		
		$td1_3 = new TaskData();
		$td1_3->setInfo($d1_3);
		
		$td2_1 = new TaskData();
		$td2_1->setInfo($d2_1);
		
		$td2_2 = new TaskData();
		$td2_2->setInfo($d2_2);
		
		$td2_1_1 = new TaskData();
		$td2_1_1->setInfo($d2_1_1);		
		
		$td2_1_2 = new TaskData();
		$td2_1_2->setInfo($d2_1_2);//i task vengono incapsulati nei nodi task_data
		
		 //costruzione dell'albero
		$td2_1->setChildren(array($td2_1_1, $td2_1_2));
		$td2->setChildren(array($td2_1, $td2_2));
		$td1->setChildren(array($td1_1, $td1_2, $td1_3));
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
		$recovered_data = array();
		$task_ids = array();
		//@TODO query per tirare su dal DB i task_id,
		//da mettere nella variabile $task_ids.
		//la query dovrebbe risultare
		
		//SELECT task_id
		//FROM nome tabella AS alias
		
		$q =& new DBQuery();
		$q->addTable(/*nome tabella, alias*/);
		$q->addQuery(/*'task_id'*/);
		$task_ids = $q->exec();
		for ($i=0; $i<sizeOf($task_ids); $i++){
			$current_task = Task::makeTask($task_ids[$i]);
			$recovered_data[sizeOf($recovered_data)] = $current_task;
		}
		return $recovered_data;
	}
}

