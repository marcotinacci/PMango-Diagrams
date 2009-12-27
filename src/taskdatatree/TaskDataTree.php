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
	public function getTaskDataTree(){
		return $root;
	}
	
	public function setRoot($root){
		$this->root = $root;
	}
	
	public function deepVisit(){
		$res = array();
		foreach($root->getChildren() as $son){
			$res[sizeOf($res)] = $son;
			$add = $son->deepVisit();
		}
		for ($i=0; $i<sizeOf($add); $i++){
			$res[sizeOf($res)] = $add[$i];
		}
		return $res;
	}
	
	public function wideVisit(){
		$res = array(); //@TODO
	}
	
	public function show(){
		echo "Root -> ";
		if(sizeOF($root->getChildren())!= 0){
			echo "(";
		}
		foreach($root->getChildren() as $son){
			echo $son."-> ";
		}
		//@TODO completare
	}
	
	public function getLeaves(){
		$this->getTaskDataTree()->getLeaves();
	}
}