<?php

require_once dirname(__FILE__)."/DeltaInfoEnum.php";
require_once dirname(__FILE__)."/../utils/TimeUtils.php";

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
	// private $collapsed;

	/**
	 * Variabile settata a vero quando il task è visibile
	 * nella tipologia di esplosione richiesta dall'utente.
	 * @var boolean
	 */
	private $visible;

	public function __construct($info = null){

		$this->parent = null;
		$this->info = $info;
		//$this->children = null;
		//$this->ftsDependencies = null;
		$this->visible = false;
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
		if($this->getInfo()==null){
			//		print "Setting children of root:<br>";
		}else{
			//		print "Setting children of ".$this->getInfo()->getWBSId().":<br>";
		}
		for($i=0; $i<sizeOf($children); $i++){
			//			print $children[$i]->getInfo()->getWBSId()."<br>";
			$children[$i]->setParent($this);
		}
		$this->children = $children;
	}

	public function setFtsDependencies($ftsDependencies = null){
		$this->ftsDependencies = $ftsDependencies;
	}

	//	public function setCollapsed($collapsed){
	//		$this->collapsed = $collapsed;
	//	}

	public function setVisibility($visible){
		$this->visible = $visible;
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

	public function getCollapsed() {
		if ($this->getVisibility()) {
			
			if($this->isAtomic()) {
				return false;
			}
				
			foreach($this->getChildren() as $child) {
				if($child->getVisibility()) {
					return false;
				}
			}
				
			return true;
		}
		else {
			return false;
		}
	}

	public function isAtomic() {
		return count($this->getChildren()) < 1;
	}

	public function getVisibility(){
		return $this->visible;
	}

	/**
	 * Metodo che consente di aggiungere un figlio alla lista dei figli del this.
	 * @param TaskData
	 */
	public function addChild($td) {
		$td->setParent($this);
		//$this->children[sizeOf($this->children)] = $td;
		$this->children[] = $td; // stessa cosa, append più leggibile
	}
	/**
	 * Il metodo controlla vari campi del task.
	 * A seconda della risposta si può settare l'alert mark più appropriato (se risulta necessario)
	 * @return DeltaInfoEnum
	 */
	public function isMarked($today){
		$actual_time = $this->info->getActualTimeFrame();
		$planned_time = $this->info->getPlannedTimeFrame();
		$actual_eff = $this->info->getActualEffort();
		$planned_eff = $this->info->getPlannedEffort();
		$actual_cost = $this->info->getActualCost();
		$planned_cost = $this->info->getPlannedCost();
		
		$planned_time["start_date"]=getDateOnly($planned_time["start_date"]);
		$planned_time["finish_date"]=getDateOnly($planned_time["finish_date"]);
		$actual_time["start_date"]=getDateOnly($actual_time["start_date"]);
		$actual_time["finish_date"]=getDateOnly($actual_time["finish_date"]);
		
		/*DEBUG
		print "<b>TaskData: ".$this->info->getTaskName()."</b><br>";
		print "planned_start_date: ".$planned_time["start_date"]."<br>";
		print "planned_finish_date: ".$planned_time["finish_date"]."<br>";
		print "actual_start_date: ".$actual_time["start_date"]."<br>";
		print "actual_finish_date: ".$actual_time["finish_date"]."<br>";
		
		print "actual_eff: ".$actual_eff."<br>";
		print "planned_eff: ".$planned_eff."<br>";
		
		print "actual_cost: ".$actual_cost."<br>";
		print "planned_cost: ".$planned_cost."<br>";
		*/
		
		DrawingHelper::debug($actual_time["start_date"]." è l'actual start date di ".$this->getInfo()->getTaskID());
		DrawingHelper::debug($actual_time["finish_date"]." è l'actual finish date di ".$this->getInfo()->getTaskID());
		DrawingHelper::debug($actual_cost." è l'actual cost di ".$this->getInfo()->getTaskID());
		DrawingHelper::debug($actual_eff." è l'actual effort di ".$this->getInfo()->getTaskID());
		
		if($actual_time["start_date"]!=""){
			if($actual_time["start_date"]>$planned_time["start_date"]){
				//print "ActualStart > PlannedStart BAD NEWS<br><br>";
				return DeltaInfoEnum::$bad_news;
			}
			if($actual_time["start_date"]<$planned_time["start_date"]){
				//print "ActualStart < PlannedStart GOOD NEWS<br><br>";
				return DeltaInfoEnum::$good_news;
			}
		}
		else{
			if($today>$planned_time["start_date"]){
				return DeltaInfoEnum::$bad_news;
			}
		}
		
		if($actual_time["finish_date"]!=""){
			if($actual_time["finish_date"]>$planned_time["finish_date"]){
				return DeltaInfoEnum::$bad_news;
			}
			if($actual_time["finish_date"]<$planned_time["finish_date"]){
				//print "ActualFinish < PlannedFinish GOOD NEWS<br><br>";
				return DeltaInfoEnum::$good_news;
			}
		}
		else{
			if($today>$planned_time["finish_date"]){
				return DeltaInfoEnum::$bad_news;
			}
		}
		
		if($actual_eff>$planned_eff){
			//print "ActualEffort > PlannedEffort BAD NEWS<br><br>";
			return DeltaInfoEnum::$bad_news;
		}
		
		if($actual_cost>$planned_cost){
			//print "ActualCost > PlannedCost BAD NEWS<br><br>";
			return DeltaInfoEnum::$bad_news;
		}
		
		if($actual_eff<$planned_eff){
			//print "ActualEffort < PlannedEffort GOOD NEWS<br><br>";
			return DeltaInfoEnum::$good_news;
		}
		
		if($actual_cost<$planned_cost){
			//print "ActualCost < PlannedCost GOOD NEWS<br><br>";
			return DeltaInfoEnum::$good_news;
		}
		//print "NO MARKS<br><br>";
		return DeltaInfoEnum::$no_mark;
	}

	public function deepVisit(){
		$res = array();
		if($this->children != null){
			foreach($this->children as $son){
				$res[] = $son;
				$add = $son->deepVisit();
				for ($i=0; $i<sizeOf($add); $i++){
					$res[] = $add[$i];
				}
			}
		}
		return $res;
	}

	public function visibleDeepVisit(){
		$res = array();
		if($this->children != null){
			foreach($this->children as $son){
//				echo "visibleDeepVisit<br>";				
//				echo "task ".$son->getInfo()->getWBSId()."<br>";
//				echo "atomico? ".($son->isAtomic()?"si":"no")."<br>";
//				echo "numero figli: ".count($son->getChildren())."<br>";
				if($son->getVisibility()){
//					echo "task visibile!<br><br>";
					$res[] = $son;
					$add = $son->visibleDeepVisit();
					for ($i=0; $i<sizeOf($add); $i++){
						$res[] = $add[$i];
					}
				}
			}
		}
		return $res;
	}

	public function wideVisit(){
		$res = array();
		$add = array();
		if($this->children != null){
			$step = $this->children;
			for($i=0; $i<sizeOf($step); $i++){
				$res[] = $step[$i];
			}
			for($i=0; $i<sizeOf($step); $i++){
				$add = $step[$i]->wideVisit();
				for ($j=0; $j<sizeOf($add); $j++){
					$res[] = $add[$j];
				}
			}
		}
		return $res;
	}

	public function getLeaves(){
		$leaves = array();
		$add = array();
		if($this->children == null){
			$leaves[]=$this;
		}
		else {
			foreach($this->children as $son){
				$add = $son->getLeaves();
				for ($i=0; $i<sizeOf($add); $i++){
					$leaves[] = $add[$i];
				}
			}
		}
		return $leaves;
	}
	
	public function getVisibleLeaves(){
		$leaves = array();
		if($this->getVisibility())
		{
			$isLeaf = true;
			foreach($this->children as $son){
				if($son->getVisibility()){
					$isLeaf = false;
					break;
				}
			}
			if($isLeaf){
				$leaves[] = $this;
			}else{
				foreach($this->children as $son){
					$add = $son->getVisibleLeaves();
					foreach($add as $x){
						$leaves[] = $x;
					}
				}
			}
		}
		return $leaves;		
	}	

	public function visibilityCheck(){
		$result = array();

		for($index = 0; $index < count($this->getChildren()); $index++) {
			if($this->children[$index]->getVisibility()) {
				$result[] = $this->children[$index];
				$this->children[$index]->visibilityCheck();
			}
			//			else {
			//				print "<br>task before deleting " . $this->children[$index]->getInfo()->getTaskID();
			//				//array_splice($this->children, $index, $index);
			//				unset($this->children[$index]);
			//				print "<br>task after deleting " . $this->children[$index]->getInfo()->getTaskID();
			//				$index++;
			//				//$this->children[$index] = null;
			//			}
		}

		$this->children = $result;
		return;
		if($this->getCollapsed() || $this->isAtomic()) {
			print $this->getInfo()->getTaskID() . " is collapsed?" . $this->getCollapsed();
			print "<br>pruning children of: " . $this->getInfo()->getTaskID();
			$this->children = null;
		}
		else {//if(!$this->getCollapsed()){
			// if a haven't children the method doesn't cut anything
			foreach($this->children as $son){
				//print "<br>leaf: " . $son->getInfo()->getTaskID();
				$son->visibilityCheck();
			}
		}
	}
}