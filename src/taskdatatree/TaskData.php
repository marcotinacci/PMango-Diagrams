<?php

require_once dirname(__FILE__)."/DeltaInfoEnum.php";

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
	
	/**
	 * Variabile boolean settata a vero quando il task è collassato.
	 * @var boolean
	 */
	private $collapsed;
	
	public function __construct($info = null){

		$this->parent = null;
		$this->info = $info;
		$this->children = null;
		$this->ftsDependencies = null;
		$this->collapsed = false;
	}
	
	/**
	 * 
	 * @param TaskData $parent
	 */
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
	
	public function setCollapsed($collapsed){
		$this->collapsed = $collapsed;
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
	
	public function getCollapsed(){
		return $this->collapsed;
	}
	
	/**
	 * Metodo che consente di aggiungere un figlio alla lista dei figli del this.
	 * @param TaskData
	 */
	public function addChild($td){
		$td->setParent($this);
		//$this->children[sizeOf($this->children)] = $td; 
		$this->children[] = $td; // stessa cosa, append più leggibile
	}
	/**
	 * Il metodo controlla vari campi del task.
	 * A seconda della risposta si può settare l'alert mark più appropriato (se risulta necessario)
	 * @return DeltaInfoEnum
	 */
	public function isMarked(){
		$actual_time = $this->info->getActualTimeFrame();
		$planned_time = $this->info->getPlannedTimeFrame();
		$actual_eff = $this->info->getActualEffort();
		$planned_eff = $this->info->getPlannedEffort();
		$actual_cost = $this->info->getActualCost();
		$planned_cost = $this->info->getPlannedCost();
		
		if($actual_time["start_date"]>$planned_time["start_date"]){
			return DeltaInfoEnum::$bad_news;
		}
		if($actual_time["start_date"]>$planned_time["start_date"]){
			return DeltaInfoEnum::$bad_news;
		}
		if($actual_eff>$planned_eff){
			return DeltaInfoEnum::$bad_news;
		}
		if($actual_cost>$planned_cost){
			return DeltaInfoEnum::$bad_news;
		}		
		
		if($actual_time["start_date"]<$planned_time["start_date"]){
			return DeltaInfoEnum::$good_news;
		}
		if($actual_time["finish_date"]<$planned_time["finish_date"]){
			return DeltaInfoEnum::$good_news;
		}
		if($actual_eff<$planned_eff){
			return DeltaInfoEnum::$good_news;
		}
		if($actual_cost<$planned_cost){
			return DeltaInfoEnum::$good_news;
		}
		return DeltaInfoEnum::$no_marks;
	}
	
	public function deepVisit(){
		$res = array();
		$add = array();
		foreach($this->getChildren() as $son){
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
		$add = array();
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
		$add = array();
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