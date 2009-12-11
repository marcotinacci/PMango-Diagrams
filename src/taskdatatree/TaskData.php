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
	private $children;
	
	/**
	 * Variabile vettore di tipo TaskData,
	 * riferimento ai task da cui il this dipende, nel senso delle
	 * finish-to-start dependencies.
	 * @var TaskData[]
	 */
	private $ftsDependencies;
	
	/**
	 * Metodo che ritorna l'int pari al livello del nodo nella struttura.
	 * Il livello è calcolato in base alla lunghezza dell'identifier di un task.
	 * @return int 
	 */
	public function getLevel(){
		//@TODO calcolo del livello
		return $level;
	}
	
	/**
	 * Metodo che consente di aggiungere un figlio alla lista dei figli del this.
	 * @param TaskData
	 */
	public function addChild($td){
		//@TODO (ricordarsi di settare opportunamente anche parent di $td
	}
}