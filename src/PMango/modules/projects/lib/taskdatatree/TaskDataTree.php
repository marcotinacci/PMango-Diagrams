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
	 * This method implement a dependency relation between visible tasks
	 * The relation is implemented in the natural way to define a relation: a set of
	 * paired object. A pair has this model: (neededTask, dependencyTask)
	 *
	 * @return array of pair described above
	 */
	public function computeDependencyRelationOnVisibleTasks() {
		$leaves = $this->getVisibleTree()->getLeaves();
		$result = array();
		foreach ($leaves as $leaf) {
			$deepChildren = $leaf->getInfo()->getCTask()->getDeepChildren();
			foreach ($deepChildren as $child) {
				$dependencies = CTask::staticGetDependencies($child);
				foreach($dependencies as $dependency) {
					if(in_array($dependency, $deepChildren)) {
						continue;
					}
					else {
						$neededLeaf = $this->searchNeededLeafThatHaveInDeepChildren($dependency, $leaf);
						if($neededLeaf != false) {
							if(!isset($result[$neededLeaf->getInfo()->getTaskID()])) {
								$result[$neededLeaf->getInfo()->getTaskID()] = array();
							}
							$leafId = $leaf->getInfo()->getTaskID();
							if(!in_array($leafId, $result[$neededLeaf->getInfo()->getTaskID()])) {
								$result[$neededLeaf->getInfo()->getTaskID()][] = $leafId;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Metodo che ritorna la foglia contenente il task necessario in una relazione di dipendenza
	 * @param integer $dependency the task identifier of the needed task
	 * @param TaskData $butLeaf la foglia da saltare per evitare confronti che sicuramente si conosce che 
	 * 	la foglia non sia presente
	 * @return TaskData if the leaf is found, else false
	 */
	public function searchNeededLeafThatHaveInDeepChildren($dependency, $butLeaf = null) {
		foreach ($this->getVisibleTree()->getLeaves() as $leaf) {
			if ($butLeaf != null && $leaf->getInfo()->getTaskID() == $butLeaf->getInfo()->getTaskID()) {
				continue;
			}

			if (in_array($dependency, $leaf->getInfo()->getCTask()->getDeepChildren())) {
				return $leaf;
			}
		}
		return false;
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