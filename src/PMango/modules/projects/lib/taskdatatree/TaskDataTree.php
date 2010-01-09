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
	
	/**
	 * 
	 * @return vettore di TaskData
	 */
	public function deepVisit(){
		$res = $this->root->deepVisit();
		return $res;
	}
	
	public function wideVisit(){
		$res = $this->root->wideVisit();
		return $res;
	}
	
	public function getLeaves(){
		$leaves = $this->root->getLeaves();
		return $leaves;
	}
	
	public function selectTask($task_id){
		$nodes = $this->wideVisit();
		for($i=0; $i<sizeOf($nodes); $i++){
			if($task_id == $nodes[$i]->getInfo()->getTaskID()){
				return $nodes[$i];
			}
		}
	}
	
	public function setAllDependencies(){
		//prendo tutti i nodi dell'albero
		$nodes = wideVisit();
		$current_dep = array();
		//ciclo sui nodi
		for($i=0; $i<sizeOf($nodes); $i++){
			//$dep adesso è una stringa di task_id separati da virgole
			$dep = $nodes[$i]->getInfo()->getDependencies();
			//$dep è diventato un array di stringhe=task_id
			$dep = explode(",", $dep);
			//per ognuna di queste stringhe seleziono il task corrispondente nell'albero
			//aggiornando current_dep (vettore di taskdata
			foreach($dep as $task_id){
				$current_dep[] = $this->selectTask($task_id);
			}
			//setto le dipendenze
			$nodes[$i]->setFtsDependencies($current_dep);
			//re-inizializzo current_dep
			$current_dep = array();
		}
	}
	
	/**
	 * Metodo invocato su un albero, che ne restituisce una copia contenente solo i nodi visibili
	 * @return TaskDataTree
	 */
	public function getVisibleTree(){
		$v_tdt = $this;
		$v_tdt->getRoot()->visibilityCheck();
		return $v_tdt; 
	}
}