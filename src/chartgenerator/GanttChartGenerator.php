<?php

// TODO: includere drawer del box, quando presente
require_once "../gifarea/GifGanttTask.php";

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
	 * Funzione di generazione grafica del diagramma Gantt
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function generateChart(){
		// parent o this??
		// TODO: tdt var di istanza
		$tdt = $this->$tdtGenerator->generateTaskDataTree($_SESSION["useroptionschoice"]);
		$this->makeLeftColumn();
		$this->makeRightColumn();
		$chart->draw();
	}
	
	/**
	 * Funzione di generazione grafica della lista di task sulla colonna di 
	 * sinistra
	 */
	protected function makeLeftColumn(){
		// larghezza della colonna sinistra
		$lcWidth = $chart->getHeight()*$leftColumnSpace;
		
		// disegno il box della colonna sinistra
		$leftCol = new GifBox(0,0,$chart->getWidth(),
			$lcWidth);
		$leftCol->drawOn($chart);
		
		// TODO: visita in profondità: delegare a $tdt! $visit è vettore di TaskData
		$visit = $tdt->deepFirstVisit();
		// contatore della riga
		$row = 1;
		for($i = 0; $i < sizeOf($visit); $i++, $row++)
		{
			// profondità indentatura
			$indent = $visit[$i]->getLevel()*$horizontalSpace;
			// TODO: modellare metodi getter di id e name in classe Task
			$label = new GifLabel(
				$indent, // x
				$verticalSpace + ($i * ($verticalSpace + $labelHeight)), // y
				$lcWidth - $indent, // width
				$labelHeight, // height
				$visit[$i]->getInfo()->getID() + 
					$visit[$i]->getInfo()->getName() // label
				);
			$label->drawOn($chart);
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