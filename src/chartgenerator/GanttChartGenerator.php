<?php

require_once dirname(__FILE__)."/ChartGenerator.php";
require_once dirname(__FILE__)."/../gifarea/GifBox.php";
require_once dirname(__FILE__)."/../gifarea/GifLabel.php";
/**
 * Questa classe implementa il metodo di generazione del diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.2
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */
class GanttChartGenerator extends ChartGenerator{
	
	/**
	 * Pixel che spaziano in verticale i tasks
	 * @var int
	 */
	protected $verticalSpace = 10;
	
	/**
	 * Altezza dello spazio dedicato alla label del titolo dei task
	 * @var int
	 */
	protected $labelHeight = 10;
	
	/**
	 * Misura in pixel del tab di indentazione dei nomi dei task
	 * @var int
	 */
	protected $horizontalSpace = 5;
	
	/**
	 * Frazione dello spazio orizzontale dedicato alla colonna sinistra (il 
	 *	complementare è dedicato alla colonna di destra), assume un valore
	 * compreso tra 0 e 1
	 * @var float
	 */
	protected $leftColumnSpace = 0.2;
	
	/**
	 * Tolleranza
	 * @var int
	 */
	protected $tol = 5;
	
	/**
	 * Costruttore
	 */
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * Funzione di generazione grafica del diagramma Gantt
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function generateChart(){
		// parent o this??
		// TODO: stub tree generator
		// $tdt = $this->$tdtGenerator->generateTaskDataTree($_SESSION["useroptionschoice"]);
		
		$this->makeLeftColumn();
		$this->makeRightColumn();
		$this->chart->draw();
	}
	
	/**
	 * Funzione di generazione grafica della lista di task sulla colonna di 
	 * sinistra
	 */
	protected function makeLeftColumn(){
		// larghezza della colonna sinistra
		// TODO: stub larghezza
		$lcWidth = $this->chart->getWidth()*$this->leftColumnSpace;
		
		// disegno il box della colonna sinistra
		// TODO: stub altezza
		$leftCol = new GifBox(
			$this->tol,
			$this->tol,
			$lcWidth,
			$this->chart->getHeight() - 2*$this->tol
		);
		$leftCol->drawOn($this->chart);
		
		// TODO: visita in profondità: attendere implementazione
		//$visit = $tdt->deepFirstVisit();
		// contatore della riga
		$row = 1;
		// TODO: stub numero elementi
		//for($i = 0; $i < sizeOf($visit); $i++, $row++)
		for($i = 0; $i < 5; $i++, $row++)
		{
			// profondità indentatura
			// TODO: stub livello
			//$indent = $visit[$i]->getLevel()*$this->horizontalSpace;
			$indent = ($i%3)*$this->horizontalSpace;
			// TODO: modellare metodi getter di id e name in classe Task
			$label = new GifLabel(
				$indent, // x
				$this->verticalSpace + ($i * ($this->verticalSpace + $this->labelHeight)), // y
				$lcWidth - $indent, // width
				$this->labelHeight, // height
				// TODO: stub stringa
				// $visit[$i]->getInfo()->getID() + $visit[$i]->getInfo()->getName()
				($i+1) . ". task numero " . $i, //label
				$this->labelHeight-2 //size
				);
			$label->drawOn($this->chart);
		}
	}
	
	/**
	 * Funzione di generazione grafica della parte grafica del Gantt nella parte 
	 * destra
	 */
	protected function makeRightColumn(){
		// TODO: not implemented yet
	}
	
	/**
	 * Funzione di generazione grafica di un singolo task 
	 */
	protected function makeGanttTaskBox(){
		// TODO: not implemented yet
	}
	
	/**
	 * Funzione di generazione grafica di una dipendenza tra due task
	 */
	protected function makeGanttDependencies(){
		// TODO: not implemented yet		
	}
}