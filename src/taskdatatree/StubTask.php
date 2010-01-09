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
		$this->data[DataArrayKeyEnumeration::$wbsIdentifier]="1.111".StubTask::$id;
		$this->data[DataArrayKeyEnumeration::$name]="Task_".StubTask::$id;
		
		StubTask::$id++;
		
		$this->data[DataArrayKeyEnumeration::$planned_start_date]="12.09.10";
		
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][0]['PlannedEffort']="20";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][0]['ResourceName']="Pippo";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][0]['Role']="Administrator";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][1]['PlannedEffort']="50";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][1]['ResourceName']="Pippo";
		$this->data[DataArrayKeyEnumeration::$assigned_to_task][1]['Role']="Administrator";

		$this->data[DataArrayKeyEnumeration::$plan_duration]="20";
		$this->data[DataArrayKeyEnumeration::$plan_effort]="20";
		$this->data[DataArrayKeyEnumeration::$plan_cost]="20";
		$this->data[DataArrayKeyEnumeration::$planned_start_date]="2010-01-01 01:00:00";
		$this->data[DataArrayKeyEnumeration::$planned_finish_date]="2010-01-01 03:00:00";
		$this->data[DataArrayKeyEnumeration::$act_duration]="20";
		$this->data[DataArrayKeyEnumeration::$act_effort]="20";
		$this->data[DataArrayKeyEnumeration::$act_cost]="20";
		$this->data[DataArrayKeyEnumeration::$actual_start_date]="2009-01-01 01:00:00";
		$this->data[DataArrayKeyEnumeration::$actual_finish_date]="2010-01-19 01:00:00";
		$this->data[DataArrayKeyEnumeration::$level]="1";
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
		$planned_data = array ("duration"=>$this->data[DataArrayKeyEnumeration::$plan_duration], "effort"=>$this->data[DataArrayKeyEnumeration::$plan_effort], "cost"=>$this->data[DataArrayKeyEnumeration::$plan_cost]);
		return $planned_data;
	}

	public function getPlannedTimeFrame(){
		$planned_time_frame = array("start_date"=>$this->data[DataArrayKeyEnumeration::$planned_start_date], "finish_date"=>$this->data[DataArrayKeyEnumeration::$planned_finish_date]);
		return $planned_time_frame;
	}

	public function getActualData(){
		$actual_data = array ("duration"=>$this->data[DataArrayKeyEnumeration::$act_duration], "effort"=>$this->data[DataArrayKeyEnumeration::$act_effort], "cost"=>$this->data[DataArrayKeyEnumeration::$act_cost]);
		return $actual_data;
	}

	public function getActualTimeFrame(){
		$actual_time_frame = array("start_date"=>$this->data[DataArrayKeyEnumeration::$actual_start_date], "finish_date"=>$this->data[DataArrayKeyEnumeration::$actual_finish_date]);
		return $actual_time_frame;
	}
	
	public function getLevel(){
		return $this->data[DataArrayKeyEnumeration::$level];
	}

	// funzione solo di stub
	public function setLevel($l){
		$this->data[DataArrayKeyEnumeration::$level] = $l ;
	}
	
	private function calculatePercentage(){
		return $this->data[DataArrayKeyEnumeration::$percentage];
	}
	
	public function getPercentage(){
		return $this->data[DataArrayKeyEnumeration::$percentage];
	}
}

