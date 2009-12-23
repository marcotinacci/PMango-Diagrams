<?php

/**
 * Questa classe accede alla LibDB già presente e recupera le informazioni
 * dei task secondo il parametro UserOptionChoice.
 * Infine richiede la costruzione della struttura ad albero per la
 * gestione dei dati ricavati.
 *
 * @author: Francesco Calabri
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class TaskDataTreeGenerator{
	
	/**
	 * Questo metodo genera un TaskDataTree passando i dati
	 * recuperati dal DB considerando le uoc.
	 * @param UserOptionsChoice $uoc
	 * @return TaskDataTree $tdt
	 */
	public function generateTaskDataTree($uoc){
		//@TODO
		$tdt = new TaskDataTree(/*...*/);
		return $tdt;
	}
	
	/**
	 * Metodo per l'accesso ai dati dei task.
	 * @return $dataRecovered sono i dati recuperati riguardanti i task
	 */
	public function getData(){
		//@TODO
		return $dataRecoverd;
	}
}