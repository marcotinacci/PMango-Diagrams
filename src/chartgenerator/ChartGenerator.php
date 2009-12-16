<?php

require_once "../gifarea/GifImage.php"
require_once "../taskdatatree/TaskDataTreeGenerator.php"
require_once "../useroptionschoice/UserOptionsChoice.php"

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
 * @version: 0.2
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

abstract class ChartGenerator{
	
	/**
	 * Contiene l'ultimo diagramma generato
	 * @var GifImage
	 */
	protected $chart;
	
	/**
	 * Generatore di struttura che contiene tutti i dati richiesti dei task del 
	 * progetto
	 * @var TaskDataTreeGenerator
	 */
	protected $tdtGenerator;
	
	/**
	 * Costruttore
	 */
	protected function __construct()
	{
		$tdtGenerator = new TaskDataTreeGenerator();
		// TODO: dimensioni Gif da configurazione?
		$chart = new GifImage(800,550);
		
	}
	
	/**
	 * Metodo astratto di generazione del diagramma, come side effect questo 
	 * metodo disegna in $chart il diagramma richiesto
	 * @abstract
	 * @see $chart
	 */
	abstract public function generateChart();
	
	/**
	 * Metodo get della gif elaborata
	 * @return GifImage
	 */
	public function getChart(){
		return $chart;
	}
	
}