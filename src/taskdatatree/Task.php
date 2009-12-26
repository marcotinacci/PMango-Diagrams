<?php

/**
 * Questa classe organizza le informazioni da incapsulare nei nodi della struttura,
 * Per ogni task.
 * 
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class Task{
	
	private $data = array();
	
	public function setData($data){
		$this->data = $data;
	}
	
	public function getAll(){
		return $data;
	}
	
	public function getWBSId(){
		return $data["id"];
	}
	
	public function getTaskName(){
		return $data["name"];
	}
		
	public function getEffort(){
		return $data["plan_effort"];
	}
	
	/*
	 * Si pensa alla strutturazione dei dati delle risorse assegnate al task,
	 * come un vettore di vettori interno a $data.
	 * Avremo il vettore delle risorse assegnate in $data["assigned_to_task"].
	 * $data["assigned_to_task"][0] è il vettore di informazioni (personal_effort, name, role)
	 * riguardante la prima risorsa assegnata al task.
	 */
	public function getResources(){
		return $data["assigned_to_task"];
	}
	
	public function getPlannedData(){
		$planned_data = array ("duration"=>$data["plan_duration"], "effort"=>$data["plan_effort"], "cost"=>$data["plan_cost"]);
		return $planned_data;
	}	
	
	public function getPlannedTimeFrame(){
		$planned_time_frame = array("start_date"=>$data["start_date"], "finish_date"=>$data["finish_date"]);
	}	
	
	public function getActualData(){
		$actual_data = array ("duration"=>$data["act_duration"], "effort"=>$data["act_effort"], "cost"=>$data["act_cost"]);
		return $actual_data;
	}	
	
	public function getLevel(){
		//@TODO calcolare la lunghezza dell'identifier
		//$level = length($this->getWBSId())- floor(length($this->getWBSId())/2).
		//dove length conta i caratteri, floor prende la parte intera del valore tra parentesi,
		//(considerando i punti divisori degli id come caratteri:
		//es: 1.2.3 è un attività di terzo livello; length() = 5; length()/2 = 2.5; floor(2.5) = 2;
		//length - floor = 3 = livello del task.
		return $level;
	}	

	private function calculatePercentage(){
		//@TODO se necessario, calcolare lo stato di avanzamento del task dalla comparazione
		//di actual/planned start e finish date.
		return $data["percentage"];
	}
}