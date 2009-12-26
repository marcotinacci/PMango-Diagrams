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
		$array = $this->getData();
		$root = new TaskData();
		$d1 = new Task();
		$d1->setData($array[0]);
		$d2 = new Task();
		$d2->setData($array[1]);
		$td1 = new taskData();
		$td1->setInfo($d1);
		$td2 = new taskData();
		$td2->setInfo($d2);
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
		$recovered_data = array(array("id"=>1, "nome"=> "Analisi"), array("id"=>2, "nome"=>"Sviluppo"));
		return $recovered_data;
	}
}

