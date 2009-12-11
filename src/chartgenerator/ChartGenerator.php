<?php
/**
 * Questa classe astratta raccoglie i comportamenti comuni delle classi che 
 * generano i diagrammi Gantt, WBS e TasknetWork
 * 
 * @abstract
 * 
 * @see GanttChartGenerator
 * @see WBSChartGenerator
 * @see TaskNetworkChartGenerator
 * 
 * @author: Marco Tinacci
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

abstract class ChartGenerator{
	
	/**
	 * Variabile di tipo Gif, contiene l'ultimo diagramma generato
	 * @var Gif
	 */
	protected $chart;
	
	/**
	 * Struttura che contiene tutti i dati richiesti dei task del progetto
	 * @var TaskDataTree
	 */
	protected $taskDataTree;
	
	/**
	 * Metodo astratto di generazione del diagramma, come side effect questo 
	 * metodo disegna in $chart il diagramma richiesto
	 * @abstract
	 * @see $chart
	 */
	abstract public function generateChart();
	
	/**
	 * metodo get della gif elaborata
	 * @return Gif
	 */
	public function getChart(){
		return $chart;
	}
	
}