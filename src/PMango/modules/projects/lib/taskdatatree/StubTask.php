<?php

require_once dirname(__FILE__).'/DataArrayKeyEnumeration.php';

/* Questa classe è u fake di Task serve solo per chi deve testare della roba 
 * che ha bisogno del task, basta istanziare stubtask e si ha praticamente un
 * task con questi dati finti
 */

class StubTask{

	private $data = array();

	private static $id=0;
	
	public function __construct()
	{
		$this->data[DataArrayKeyEnumeration::$wbsIdentifier]="1.".StubTask::$id;
		$this->data[DataArrayKeyEnumeration::$name]="Task_".StubTask::$id;
		
		StubTask::$id++;
		
		$this->data[DataArrayKeyEnumeration::$planned_start_date]="2010-01-12 02:00:00";
		
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][0]['PlannedEffort']="20";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][0]['ResourceName']="Pippo";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][0]['Role']="Administrator";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][1]['PlannedEffort']="50";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][1]['ResourceName']="Pippo";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][1]['Role']="Administrator";
		$this->data[DataArrayKeyEnumeration::$plan_effort]="20";
		$this->data[DataArrayKeyEnumeration::$plan_cost]="20";
		$this->data[DataArrayKeyEnumeration::$planned_start_date]="2010-01-12 02:00:00";
		$this->data[DataArrayKeyEnumeration::$planned_finish_date]="2010-01-17 16:00:00";
		$this->data[DataArrayKeyEnumeration::$act_effort]="20";
		$this->data[DataArrayKeyEnumeration::$act_cost]="20";
		$this->data[DataArrayKeyEnumeration::$actual_start_date]= "2010-01-13 05:00:00";
		$this->data[DataArrayKeyEnumeration::$actual_finish_date]= "2010-01-14 10:00:00";
		$this->data[DataArrayKeyEnumeration::$percentage]="20";
	}

	/**
	 * Static method that create a correct task, querying a db
	 * @param int $task_id
	 * @return Task a correct task to be handle
	 */

	public function setData($data){
		$this->data = $data;
	}

	public function getAll(){
		return $this->data;
	}

	public function getWBSId(){
		return $this->data[DataArrayKeyEnumeration::$wbsIdentifier];
	}

	public function getTaskName(){
		return $this->data[DataArrayKeyEnumeration::$name];
	}

	public function getPlannedEffort(){
		return $this->data[DataArrayKeyEnumeration::$plan_effort];
	}
	
	public function getActualEffort(){
		return $this->data[DataArrayKeyEnumeration::$act_effort];
	}

	public function getPlannedCost(){
		return $this->data[DataArrayKeyEnumeration::$plan_cost];
	}
	
	public function getActualCost(){
		return $this->data[DataArrayKeyEnumeration::$act_cost];
	}
	/*
	 * Si pensa alla strutturazione dei dati delle risorse assegnate al task,
	 * come una matrice interna a $data.
	 * Avremo il vettore delle risorse assegnate in $data["assigned_to_task"].
	 * $data["assigned_to_task"][0] è il vettore di informazioni (personal_effort, name, role)
	 * riguardante la prima risorsa assegnata al task.
	 */
	public function getResources(){
		return $this->data[DataArrayKeyEnumeration::$assigned_to_task];
	}

	public function getPlannedData(){
		$planned_data = array ("duration"=>$this->getPlannedDuration(), "effort"=>$this->data[DataArrayKeyEnumeration::$plan_effort], "cost"=>$this->data[DataArrayKeyEnumeration::$plan_cost]);
		return $planned_data;
	}

	public function getPlannedTimeFrame(){
		$planned_time_frame = array("start_date"=>$this->data[DataArrayKeyEnumeration::$planned_start_date], "finish_date"=>$this->data[DataArrayKeyEnumeration::$planned_finish_date]);
		return $planned_time_frame;
	}

	public function getActualData(){
		$actual_data = array ("duration"=>$this->getActualDuration(), "effort"=>$this->data[DataArrayKeyEnumeration::$act_effort], "cost"=>$this->data[DataArrayKeyEnumeration::$act_cost]);
		return $actual_data;
	}

	public function getActualTimeFrame(){
		$actual_time_frame = array("start_date"=>$this->data[DataArrayKeyEnumeration::$actual_start_date], "finish_date"=>$this->data[DataArrayKeyEnumeration::$actual_finish_date]);
		return $actual_time_frame;
	}

	// funzione solo di stub
	public function setLevel($l){

	}
	
	private function calculatePercentage(){
		return $this->data[DataArrayKeyEnumeration::$percentage];
	}
	
	public function getPercentage(){
		return $this->data[DataArrayKeyEnumeration::$percentage];
	}
	
	public function getActualDuration(){
		$s_f = $this->getActualTimeFrame();
		$act_duration = strtotime($s_f["start_date"])-strtotime($s_f["finish_date"]);
		return intval((($act_duration/60)/60)/24)+1;
	}
	
	public function getPlannedDuration(){
		$s_f = $this->getPlannedTimeFrame();
		$plan_duration = strtotime($s_f["start_date"])-strtotime($s_f["finish_date"]);
		return intval((($plan_duration/60)/60)/24)+1;
	}
	
	public function getLevel(){
		//esplodo il wbsId separando elementi con separatore "."; ottengo l'array $level
		//che avrà tanti elementi, quanto è il livello del task stesso. 
		$wbs_id = $this->getWBSId();
		$level = explode(".", $wbs_id);
		return sizeOf($level);
	}
}

