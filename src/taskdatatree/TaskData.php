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
		$td.setParent($this);
		$this->children->append($td -> $td->getInfo->getTaskName());
		//@TODO (ricordarsi di settare opportunamente anche parent di $td
	}
}