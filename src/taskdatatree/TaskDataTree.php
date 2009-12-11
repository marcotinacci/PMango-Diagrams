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
	
	/**
	 * Metodo accessore alla struttura
	 * @return TaskData
	 */
	public function getTaskDataTree(){
		return $root;
	}
}