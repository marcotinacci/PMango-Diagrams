<?php

/**
 * Questa classe � la struttura dati per la gestione delle informazioni dei task
 * La struttura dati usata � un albero in cui ogni nodo contiene delle informazioni
 * e una lista di nodi figli, per permettere facilmente la visita della struttura.
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */
class TaskDataTree {
	
	/**
	 * Variabile di tipo TaskData, � la radice della struttura
	 * @var TaskData
	 */
	private $root;
	
	/*
	 * public function __construct(){
		$root = new TaskData();
	}
	*/
	
	
	/**
	 * Metodo accessore alla struttura
	 * @return TaskData
	 */
	public function getTaskDataTree(){
		return $root;
	}
	
	public function setRoot($root){
		$this->root = $root;
	}
	
	public function deepVisit(){
		$res = array();
		$currentNode = $root;
		foreach($currentNode->getChildren() as $son){
			$res[sizeOf($res)] = $son;
		}
		return $res;
	}
}