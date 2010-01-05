<?php

require_once dirname(__FILE__).'/ChartGenerator.php';
require_once dirname(__FILE__).'/../gifarea/GifBox.php';
require_once dirname(__FILE__).'/../gifarea/GifLabel.php';
require_once dirname(__FILE__).'/../gifarea/GifBoxedLabel.php';
require_once dirname(__FILE__).'/../gifarea/DrawingHelper.php';
require_once dirname(__FILE__).'/../gifarea/LineStyle.php';
require_once dirname(__FILE__).'/../useroptionschoice/UserOptionsChoice.php';

// TODO: eliminare require stubs quando non servono più
require_once dirname(__FILE__).'/../taskdatatree/StubTaskDataTree.php';

/**
 * Questa classe implementa il metodo di generazione del diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.4
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
	protected $labelHeight = 15;

	/**
	 * Dimensione del font usato
	 * @var int
	 */	
	protected $fontSize = 8;
	
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
	
	// TODO: prendere grain level da uoc
	/**
	 * Livello della granularità
	 * @var int
	 */
	protected $granLevel = 5;

	/**
	 * Numero dei Tasks
	 * @var int
	 */
	protected $numTasks = 0;
	
	/**
	 * Costruttore
	 */
	public function __construct(){

	}
	
	/**
	 * Funzione di generazione grafica del diagramma Gantt
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function generateChart(){
		// TODO: stub tree generator
		// $tdt = $this->$tdtGenerator->generateTaskDataTree($_SESSION["useroptionschoice"]);
		$this->tdt = new StubTaskDataTree();
		
		// calcola una sola volta il numero dei task dell'albero
		$this->numTasks = sizeOf($this->tdt->deepVisit());
		
		$this->makeCanvas();
		$this->makeBorder();
		$this->makeRightColumn();
		$this->makeLeftColumn();
		$this->chart->draw();
	}
	
	/**
	 * Funzione di generazione del canvas
	 */	
	protected function makeCanvas(){
		// TODO: stub granLevel
		$granLevel = 5;
		// TODO: prendere la larghezza dalla dimensione della finestra
		$this->chart = new GifImage(800, 
			$granLevel * $this->labelHeight + $this->numTasks*($this->verticalSpace +
			$this->labelHeight) + $this->verticalSpace + 2*$this->tol);
	}

	/**
	 * Funzione di generazione grafica del bordo dell'immagine
	 */	
	protected function makeBorder(){
		$box = new GifBox(
			0,
			0,
			$this->chart->getWidth()-1,
			$this->chart->getHeight()-1
		);
		$box->drawOn($this->chart);
	}
	
	/**
	 * Funzione di generazione della testata del diagramma
	 */	
	protected function makeFront(){
		// TODO: granularità da uoc
		// TODO: stub ampiezza grana
		$granWidth = 41;
		
		$titleWidth = $this->chart->getWidth()*$this->leftColumnSpace;
		$frontWidth = $this->chart->getWidth() - $titleWidth;
		
		$title = new GifBoxedLabel(
			$this->tol, // x
			$this->tol, // y
			$titleWidth, // larghezza
			$this->granLevel*$this->labelHeight, // altezza
			"Project Title", // titolo
			$this->fontSize // dim font
			);
		$title->getBox()->setForeColor("green");
		$title->drawOn($this->chart); 
		
		// TODO: stub anno
		$anno = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelHeight, // altezza	
			"2010", // data
			$this->fontSize // dim font			
		);
		$anno->drawOn($this->chart);
		
		// TODO: stub mese
		$mese = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol + $this->labelHeight, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelHeight, // altezza	
			"Gennaio", // data
			$this->fontSize // dim font			
		);
		$mese->drawOn($this->chart);
		
		// TODO: stub settimana
		$sett = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol + 2*$this->labelHeight, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelHeight, // altezza	
			"Settimane", // data
			$this->fontSize // dim font			
		);
		$sett->drawOn($this->chart);
		
		// TODO: stub giorno
		$giorno = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol + 3*$this->labelHeight, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelHeight, // altezza
			"Giorni", // data
			$this->fontSize // dim font			
		);
		$giorno->drawOn($this->chart);		
		
		// TODO: stub ore
		for( $i = 0 ; $i < $frontWidth -2*$this->tol -1 - $granWidth ; $i=$i+$granWidth){
			$slice = new GifBoxedLabel(
				$this->tol + $titleWidth + $i, // x
				$this->tol + 4*$this->labelHeight, // y
				$granWidth, // larghezza
				$this->labelHeight, // altezza
				($i / $granWidth)."", // data
				$this->fontSize // dim font
			);
			$slice->drawOn($this->chart);
		}
		$slice = new GifBoxedLabel(
			$this->tol + $titleWidth + $i, // x
			$this->tol + 4*$this->labelHeight, // y
			$this->chart->getWidth() - 2*$this->tol -$i - $titleWidth -1, // larghezza
			$this->labelHeight, // altezza
			($i / $granWidth)."", // data
			$this->fontSize // dim font
		);
		$slice->drawOn($this->chart);
	}
	
	/**
	 * Funzione di generazione grafica della lista di task sulla colonna di 
	 * sinistra
	 */
	protected function makeLeftColumn(){
		// larghezza della colonna sinistra
		$wLeftCol = $this->chart->getWidth()*$this->leftColumnSpace;
		$xLeftCol = $this->tol;
		$yLeftCol = $this->tol + $this->granLevel * $this->labelHeight;
		// disegno il box della colonna sinistra
		$leftCol = new GifBox(
			$xLeftCol, // x
			$yLeftCol, // y
			$wLeftCol, // larghezza 
			$this->chart->getHeight() - $this->granLevel * $this->labelHeight 
			- 2*$this->tol // altezza
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
				$xLeftCol + $indent, // x
				$this->verticalSpace + $yLeftCol + 
				($i * ($this->verticalSpace + $this->labelHeight)), // y
				$wLeftCol - $indent, // width
				$this->labelHeight, // height
				// TODO: stub stringa
				// $visit[$i]->getInfo()->getID() + $visit[$i]->getInfo()->getName()
				($i+1) . ". task numero task numero task numero " . $i, //label
				$this->fontSize //size
				);
			$label->drawOn($this->chart);
		}
	}
	
	/**
	 * Funzione di generazione grafica della parte grafica del Gantt nella parte 
	 * destra
	 */
	protected function makeRightColumn(){
		// larghezza della colonna destra
		$wRightCol = $this->chart->getWidth()*(1-$this->leftColumnSpace);

		$this->makeGrid();		
		// disegno il box della colonna destra
		$rightCol = new GifBox(
			$this->chart->getWidth() - $wRightCol + $this->tol,
			$this->tol,
			$wRightCol - 2*$this->tol - 1,
			$this->chart->getHeight() - 2*$this->tol
		);
		$rightCol->drawOn($this->chart);

		$this->makeFront();
		$this->makeGanttDependencies();		
		$this->makeGanttTaskBox();
	}
	
	/**
	 * Funzione di generazione grafica della griglia
	 */	
	protected function makeGrid(){
		$xGrid = $this->chart->getWidth()*$this->leftColumnSpace + $this->tol;
		$yGrid = $this->granLevel * $this->labelHeight + $this->tol;
		$xfGrid = $this->chart->getWidth() - $this->tol;
		$yfGrid = $this->chart->getHeight() - $this->tol;
		
		// TODO: stub granularità (stessa di make front!) da uoc
		$granWidth = 41;
		
		for($i=$xGrid; $i < $xfGrid; $i = $i + $granWidth){
			DrawingHelper::LineFromTo($i,$yGrid,$i,$yfGrid,$this->chart,
				new LineStyle("gray"));
		}
		
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