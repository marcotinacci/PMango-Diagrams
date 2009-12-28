<?php

/**
 * Questa classe è la struttura dati per la gestione delle informazioni dei task
 * La struttura dati usata è un albero in cui ogni nodo contiene delle informazioni
 * e una lista di nodi figli, per permettere facilmente la visita della struttura.
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */
class TaskDataTree {
	
	/**
	 * Variabile di tipo TaskData, è la radice della struttura
	 * @var TaskData
	 */
	private $root;
	
	 public function __construct(){
		$root = new TaskData();
	}
	
	/**
	 * Metodo accessore alla struttura
	 * @return TaskData
	 */
	public function getRoot(){
		return $this->root;
	}
	
	public function setRoot($root){
		$this->root = $root;
	}
	
	public function deepVisit(){
		$res = array();
		foreach($this->root->getChildren() as $son){
			$res[sizeOf($res)] = $son;
			$add = $son->deepVisit();
			for ($i=0; $i<sizeOf($add); $i++){
				$res[sizeOf($res)] = $add[$i];
			}
		}
		return $res;
	}
	
	public function wideVisit(){
		$res = array();
		$first_step = $this->root->getChildren();
		for($i=0; $i<sizeOf($first_step); $i++){
			$res[sizeOf($res)] = $first_step[$i];
		}
		for($i=0; $i<sizeOf($first_step); $i++){
			$current_node = $first_step[$i];
			$add = $current_node->wideVisit();
			for ($i=0; $i<sizeOf($add); $i++){
				$res[sizeOf($res)] = $add[$i];
			}
		}
		return $res;
	}
	
	public function getLeaves(){
		$leaves = $this->getRoot()->getLeaves();
		return $leaves;
	}
}