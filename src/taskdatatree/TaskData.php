<?php

/**
 * Questa classe rappresenta i singoli nodi della struttura TaskDataTree.
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */
class TaskData{
	
	/**
	 * Variabile di tipo Task, rappresenta le informazioni contenute nel nodo.
	 * @var Task
	 */
	private $info;
	
	/**
	 * Variabile di tipo TaskData, riferimento al nodo padre.
	 * @var TaskData
	 */
	private $parent;
	
	/**
	 * Variabile vettore di tipo TaskData,
	 * riferimento all'insieme dei nodi figli.
	 * @var TaskData[]
	 */
	private $children = array();
	
	/**
	 * Variabile vettore di tipo TaskData,
	 * riferimento ai task da cui il this dipende, nel senso delle
	 * finish-to-start dependencies.
	 * @var TaskData[]
	 */
	private $ftsDependencies = array();
	
	public function __construct(){
		$parent=null;
		$info=null;
		$children=null;
		$ftsDependencies=null;
	}
	
	public function setParent($parent){
		$this->parent = $parent;
	}
	
	public function setInfo($info){
		$this->info = $info;
	}
	
	public function setChildren($children){
		for($i=0; $i<sizeOf($children); $i++){
			$children[$i]->setParent($this);
		}
		$this->children = $children;
	}
	
	public function setFtsDependencies($ftsDependencies){
		$this->ftsDependencies = $ftsDependencies;
	}
	
	public function getParent(){
		return $this->parent;
	}

	public function getInfo(){
		return $this->info;
	}

	public function getChildren(){
		return $this->children;
	}

	public function getFtsDependencies(){
		return $this->ftsDependencies;
	}
	
	/**
	 * Metodo che consente di aggiungere un figlio alla lista dei figli del this.
	 * @param TaskData
	 */
	public function addChild($td){
		$td->setParent($this);
		$this->children[sizeOf($this->children)] = $td;
	}
	
	public function deepVisit(){
		$res = array();
		foreach($this->getChildren() as $son){
			$res[sizeOf($res)] = $son;
			$add = $son->deepVisit();
		}
		for ($i=0; $i<sizeOf($add); $i++){
			$res[sizeOf($res)] = $add[$i];
		}
		return $res;
	}
	
	public function wideVisit(){
		$res = array();
		$step = $this->getChildren();
		for($i=0; $i<sizeOf($step); $i++){
			$res[sizeOf($res)] = $step[$i];
		}
		for($i=0; $i<sizeOf($step); $i++){
			$current_node = $step[$i];
			$add = $current_node->wideVisit();
			for ($i=0; $i<sizeOf($add); $i++){
				$res[sizeOf($res)] = $add[$i];
			}
		}
		return $res;
	}
	
	public function getLeaves(){
		$leaves = array();
		if($this->getChildren()==null){
			$leaves[sizeOf($leaves)]=$this;
		}
		else {
			foreach($this->getChildren() as $son){
				$add = $son->getLeaves();
				for ($i=0; $i<sizeOf($add); $i++){
					$leaves[sizeOf($leaves)] = $add[$i];
				}
			}	
		}
		return $leaves;
	}

}