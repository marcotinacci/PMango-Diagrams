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

	require_once "TaskDataTree.php";
	require_once "TaskData.php";
	require_once "Task.php";

class TaskDataTreeGenerator{
	
	/**
	 * Questo metodo genera un TaskDataTree utilizzando getData() per 
	 * recuperare i dati dal DB (considerando le uoc).
	 * @param UserOptionsChoice $uoc
	 * @return TaskDataTree $tdt
	 */
	public function generateTaskDataTree($uoc){
		//@TODO
		$array = $this->getData(); //preleva le info
		$root = new TaskData();
		//----------------------------//
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
	 * Metodo per l'accesso ai dati dei task.
	 * @return $recovered_data sono i dati recuperati riguardanti i task
	 */
	public function getData(){
		//@TODO
		$recovered_data = array(array("id"=>"1", "name"=> "Analisi"), array("id"=>"2", "name"=>"Sviluppo"), array("id"=>"1.1", "name"=>"Incontri con il committente"), array("id"=>"1.2", "name"=>"Definizione dei requisiti"), array("id"=>"1.3", "name"=>"Preparazione offerta"), array("id"=>"2.1", "name"=>"Progettazione"), array("id"=>"2.2", "name"=>"Progettazione delle prove"), array("id"=>"2.1.1", "name"=>"Prima riunione organizzativa progettisti"), array("id"=>"2.1.2", "name"=>"Progettazione WBS"));
		return $recovered_data;
	}
}

