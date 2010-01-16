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

	/**
	 *
	 * @return vettore di TaskData
	 */
	public function visibleDeepVisit(){
		$res = $this->root->visibleDeepVisit();
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
	
	public function getVisibleLeaves() {
		return $this->root->getVisibleLeaves();
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
		$nodes = $this->wideVisit();
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
	//var $leaves;
	public function computeDependencyRelationOnVisibleTasks() {
		//$this->leaves = $this->getVisibleTree()->getLeaves();
		$result = array();
		
		foreach ($this->getVisibleLeaves() as $leaf) {
			$deepChildren = $leaf->getInfo()->getCTask()->getDeepChildren();
			//			print "<br>deep children of " . $leaf->getInfo()->getCTask()->task_id .
			//			": " . implode(", ", $deepChildren);

			foreach ($deepChildren as $child) {
				//print "<br>analizing dependency of " . $child;
				$this->analizeDependencies($child, $deepChildren, $leaf, $result);
			}

			// the following line is correct only if the $leaf is atomic
			$this->analizeDependencies($leaf->getInfo()->getTaskID(), array(), $leaf, $result);
		}
		return $result;
	}

	private function analizeDependencies($task_id, $deepChildren, $leaf, & $result) {
		$dependencies = CTask::staticGetDependencies($task_id);
		$array = explode(",", $dependencies);
		//print_r($array);
		//var_dump($dependencies);
		//print " that have this dependencies: " . implode(", ", (array)$dependencies);
		//$array =(array)$dependencies;
		//print " imploded dependencies " . implode(", ", (array)$dependencies);
			
		// every element that belong to $array is a string
		foreach($array as $stringDependency) {
			//	var_dump( $stringDependency);
			if ($stringDependency == "") {
				continue;
			}

			// parsing the integer
			$dependency = intval($stringDependency);
			//print " $dependency ";
			if(in_array($dependency, $deepChildren)) {
				//print $dependency . " is a deep child of " . $leaf->getInfo()->getTaskName();
				//continue;
			}
			else {
				$neededLeaf = $this->searchNeededLeafThatHaveInDeepChildren($dependency);

				if(!$neededLeaf) {
					print "no parent left found for : " . $dependency;
				}
				//print " the parent needed leaf is: " . $neededLeaf->getInfo()->getTaskName();
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

	/**
	 * Metodo che ritorna la foglia contenente il task necessario in una relazione di dipendenza
	 * @param integer $dependency the task identifier of the needed task
	 * @param TaskData $butLeaf la foglia da saltare per evitare confronti che sicuramente si conosce che
	 * 	la foglia non sia presente
	 * @return TaskData if the leaf is found, else false
	 */
	public function searchNeededLeafThatHaveInDeepChildren($dependency) {
		//print " saerching parent leaf of " . $dependency . " ";
		foreach ($this->getVisibleLeaves() as $leaf) {
			//			if ($leaf->getInfo()->getTaskID() == $butLeaf->getInfo()->getTaskID() &&
			//			$butLeaf != null) {
			//				continue;
			//			}

			if (in_array($dependency, $leaf->getInfo()->getCTask()->getDeepChildren()) ||
			$dependency == $leaf->getInfo()->getTaskID()) {
				//print " Leaf that contains $dependency is " . $leaf->getInfo()->getTaskID();
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