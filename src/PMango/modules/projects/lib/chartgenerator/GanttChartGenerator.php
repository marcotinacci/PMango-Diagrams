<?php
require_once dirname(__FILE__).'/ChartGenerator.php';
require_once dirname(__FILE__).'/../gifarea/GifBox.php';
require_once dirname(__FILE__).'/../gifarea/GifLabel.php';
require_once dirname(__FILE__).'/../gifarea/GifBoxedLabel.php';
require_once dirname(__FILE__).'/../gifarea/GifGanttTask.php';
require_once dirname(__FILE__).'/../gifarea/DrawingHelper.php';
require_once dirname(__FILE__).'/../gifarea/LineStyle.php';
require_once dirname(__FILE__).'/../utils/TimeUtils.php';
//require_once dirname(__FILE__).'/../useroptionschoice/UserOptionsChoice.php';

/**
 * Questa classe implementa il metodo di generazione del diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.5
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
	protected $labelHeight = 25;

	/**
	 * Altezza dello spazio dedicato alla label del menu della grana
	 * @var int
	 */
	protected $labelGrainHeight = 15;

	/**
	 * Dimensione del font usato
	 * @var int
	 */	
	protected $fontSize = 8;
	
	/**
	 * Misura in pixel del tab di indentazione dei nomi dei task
	 * @var int
	 */
	protected $horizontalSpace = 10;
	
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

	// TODO: prendere date di inizio e fine da uoc
	/**
	 * Data di inizio visualizzazione
	 * @var datetime
	 */
	protected $sDate;

	/**
	 * Data di fine visualizzazione
	 * @var datetime
	 */
	protected $fDate;

	/**
	 * Numero dei Tasks
	 * @var int
	 */
	protected $numTasks = 0;
	
	/**
	 * Albero dei task
	 * @var TaskDataTree
	 */
	protected $tdt;
	
	/**
	 * Data attuale
	 * @var datetime
	 */
	protected $today;
	
	
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
		$this->tdt = $this->tdtGenerator->generateTaskDataTree();
		
		// TODO: stub date, prenderle dalle uoc
		$this->sDate = date('Y-m-d H:i:s',mktime(12,0,0,1,9,2010));
		$this->fDate = date('Y-m-d H:i:s',mktime(12,0,0,1,10,2010));
		$this->today = date('Y-m-d H:i:s',mktime(0,0,0,1,10,2010));
		
		// calcola una sola volta il numero dei task dell'albero
		$this->numTasks = sizeOf($this->tdt->deepVisit());
		$this->makeCanvas();
		$this->makeBorder();
		$this->makeRightColumn();
		$this->makeLeftColumn();
		$this->chart->draw();
		//echo "ciao";
	}
	
	/**
	 * Funzione di generazione del canvas
	 */	
	protected function makeCanvas(){
		// TODO: prendere la larghezza dalla dimensione della finestra
		$this->chart = new GifImage(800, 
			$this->granLevel * $this->labelGrainHeight + $this->numTasks*($this->verticalSpace +
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
		// TODO: spostare a var di istanza
		// titolo progetto
		$titleWidth = $this->chart->getWidth()*$this->leftColumnSpace;
		$frontWidth = $this->chart->getWidth() - $titleWidth;
		
		// generazione calendario
		$xCal = $titleWidth + $this->tol;
		$yCal = $this->tol;
		$wCal = $this->chart->getWidth() - $xCal - $this->tol;
		$startTS = toTimeStamp($this->sDate);
		$finishTS = toTimeStamp($this->fDate);
		
		// TODO: stub, granularità da uoc		
//		switch($this->uoc->showTimeGrain()){
		switch('HourlyGrainUserOption'){	
		case 'HourlyGrainUserOption':
			$xPrec = $xCal;
			$currentTS = toTimeStamp(date('Y-m-d H:i:s',$startTS)) 
				- date('i',$startTS)*60 - date('s',$startTS) + 60*60;
			$xCurrent = intval($xCal+ $wCal * 
				($currentTS-$startTS)/($finishTS-$startTS));
			// per ogni ora
			while($currentTS < $finishTS){
				$slice = new GifBoxedLabel(
					$xPrec, // x
					$this->tol + 4*$this->labelGrainHeight, // y
					$xCurrent-$xPrec, // larghezza
					$this->labelGrainHeight, // altezza
					date('H',$currentTS).'', // data
					$this->fontSize // dim font
				);
				$slice->getBox()->setForeColor('white');
				$slice->drawOn($this->chart);
				$xPrec = $xCurrent;
				$currentTS += 60*60;
				$xCurrent = intval($xCal+ $wCal*($currentTS-$startTS)/($finishTS-$startTS));
			}
			$slice = new GifBoxedLabel(
				$xPrec, // x
				$this->tol + 4*$this->labelGrainHeight, // y
				$xCal+$wCal-$xPrec-1, // larghezza
				$this->labelGrainHeight, // altezza
				date('H',$currentTS).'', // data
				$this->fontSize // dim font
			);
			$slice->getBox()->setForeColor('white');			
			$slice->drawOn($this->chart);
			
			case 'DailyGrainUserOption':
			
			case 'WeaklyGrainUserOption':
			
			case 'MonthlyGrainUserOption':
			
			case 'AnnuallyGrainUserOption':
			
		}
		
		// TODO: stub anno
		$anno = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelGrainHeight, // altezza	
			"2010", // data
			$this->fontSize // dim font			
		);
		$anno->drawOn($this->chart);
		
		// TODO: stub mese
		$mese = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol + $this->labelGrainHeight, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelGrainHeight, // altezza	
			"Gennaio", // data
			$this->fontSize // dim font			
		);
		$mese->drawOn($this->chart);
		
		// TODO: stub settimana
		$sett = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol + 2*$this->labelGrainHeight, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelGrainHeight, // altezza	
			"Settimane", // data
			$this->fontSize // dim font			
		);
		$sett->drawOn($this->chart);
		
		// TODO: stub giorno
		$giorno = new GifBoxedLabel(
			$this->tol + $titleWidth, // x
			$this->tol + 3*$this->labelGrainHeight, // y
			$frontWidth - 2*$this->tol -1, // larghezza
			$this->labelGrainHeight, // altezza
			"Giorni", // data
			$this->fontSize // dim font			
		);
		$giorno->drawOn($this->chart);
		
		$this->makeTitle();
	}
	
	/**
	 * Funzione di generazione grafica della label del titolo del progetto
	 */
	protected function makeTitle(){
		// TODO: spostare a var di istanza
		// titolo progetto
		$titleWidth = $this->chart->getWidth()*$this->leftColumnSpace;
		$frontWidth = $this->chart->getWidth() - $titleWidth;
		// TODO: titolo progetto
		$title = new GifBoxedLabel(
			$this->tol, // x
			$this->tol, // y
			$titleWidth, // larghezza
			$this->granLevel*$this->labelGrainHeight, // altezza
			"Project Title", // titolo
			$this->fontSize // dim font
			);
		$title->getBox()->setForeColor('green');
		$title->drawOn($this->chart);		
	}
	
	/**
	 * Funzione di generazione grafica della lista di task sulla colonna di 
	 * sinistra
	 */
	protected function makeLeftColumn(){
		// larghezza della colonna sinistra
		$wLeftCol = $this->chart->getWidth()*$this->leftColumnSpace;
		$xLeftCol = $this->tol;
		$yLeftCol = $this->tol + $this->granLevel * $this->labelGrainHeight;
		// disegno il box della colonna sinistra
		$leftCol = new GifBox(
			$xLeftCol, // x
			$yLeftCol, // y
			$wLeftCol, // larghezza 
			$this->chart->getHeight() - $this->granLevel * $this->labelGrainHeight 
			- 2*$this->tol // altezza
		);
		
// TODO: commentato per vedere i task sottostanti, decommentare poi
//		$leftCol->setForeColor('white');
		$leftCol->drawOn($this->chart);

		// TODO: spostare a variabili di istanza
		$visit = $this->tdt->deepVisit();

		for($i = 0; $i < sizeOf($visit); $i++)
		{
			$label = $visit[$i]->getInfo()->getWBSiD();
			// mostra il nome del task se specificato nelle opzioni utente
			// TODO: stub, attendere popolamento opzioni utente
//			if($this->uoc->showEffortInformationUserOption()){
				$label = $label.' '.$visit[$i]->getInfo()->getTaskName();
//			}
			
			// profondità indentatura
			$indent = $visit[$i]->getInfo()->getLevel() * $this->horizontalSpace;
			$label = new GifLabel(
				$xLeftCol + $indent, // x
				$this->verticalSpace + $yLeftCol + 
				($i * ($this->verticalSpace + $this->labelHeight)), // y
				$wLeftCol - $indent, // width
				$this->labelHeight, // height
				$label, // label
				$this->fontSize // size
				);
			$label->setHAlign('left');
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
		$yGrid = $this->granLevel * $this->labelGrainHeight + $this->tol;
		$xfGrid = $this->chart->getWidth() - $this->tol;
		$yfGrid = $this->chart->getHeight() - $this->tol;
		
		// TODO: stub granularità (stessa di make front!) da uoc
		$granWidth = 41;
		
		for($i=$xGrid; $i < $xfGrid; $i = $i + $granWidth){
			DrawingHelper::LineFromTo($i,$yGrid,$i,$yfGrid,$this->chart,
				new LineStyle('gray'));
		}

		
	}
	
	/**
	 * Funzione di generazione grafica dei task box
	 */
	protected function makeGanttTaskBox(){
		$xGrid = $this->chart->getWidth()*$this->leftColumnSpace + $this->tol;
		$yGrid = $this->granLevel * $this->labelGrainHeight + $this->tol;
		$xfGrid = $this->chart->getWidth() - $this->tol;
		$yfGrid = $this->chart->getHeight() - $this->tol;
		
		$hBox = ($this->labelHeight * 2 )/ 3;
		$hProgress = $this->labelHeight -$hBox;
		
		// TODO: spostare a variabili di istanza
		$visit = $this->tdt->deepVisit();

		for($i = 0; $i < sizeOf($visit); $i++)
		{
			$dt = $visit[$i];                   			

			$gTask = new GifGanttTask(
				$xGrid, // x start
				$xfGrid-1, // x finish
				$yGrid + $this->verticalSpace + 
				($i * ($this->verticalSpace + $this->labelHeight)), // y start
				$this->labelHeight, // height
				$this->sDate, // startDate
				$this->fDate, // finishDate
				$dt, // task data
				$this->today, // today
				$this->uoc // opzioni utente
				);	
			$gTask->drawOn($this->chart);
		}

	}
	
	/**
	 * Funzione di generazione grafica di una dipendenza tra due task
	 */
	protected function makeGanttDependencies(){
		// TODO: not implemented yet
	}
}