<?php

	require_once "TaskDataTreeGenerator.php";

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
	
	private $data;
	
	public function setData($data){
		//@TODO vedere come sistemare le informazioni tirate su da
		//TaskDataTreeGenerator in $data.
		$this->data = $data;
	}
	
	public function getTaskName(){
		//@TODO
	}
		
	public function getEffort(){
		//@TODO
	}	
	
	public function getPlannedData(){
		//@TODO
	}	
	
	public function getPlannedTimeFrame(){
		//@TODO
	}	
	
	public function getActualData(){
		//@TODO
	}	
	
	public function getLevel(){
		//@TODO
	}	

	private function calculatePercentage(){
		//@TODO lavorerà sul campo di percentage (v.Analisi)
		//per calcolare lo stato di avanzamento del task,
		//reso come intero $percent
		return $percent;
	}
}