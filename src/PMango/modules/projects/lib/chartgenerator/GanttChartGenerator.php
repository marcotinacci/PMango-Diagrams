<?php
require_once dirname(__FILE__).'/ChartGenerator.php';
require_once dirname(__FILE__).'/../gifarea/GifBox.php';
require_once dirname(__FILE__).'/../gifarea/GifLabel.php';
require_once dirname(__FILE__).'/../gifarea/GifBoxedLabel.php';
require_once dirname(__FILE__).'/../gifarea/GifGanttTask.php';
require_once dirname(__FILE__).'/../gifarea/DrawingHelper.php';
require_once dirname(__FILE__).'/../gifarea/LineStyle.php';
require_once dirname(__FILE__).'/../utils/TimeUtils.php';
require_once dirname(__FILE__).'/../useroptionschoice/UserOptionsChoice.php';

/**
 * Questa classe implementa il metodo di generazione del diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.7
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
	protected $labelHeight = 20;

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
	
	/**
	 * Livello della granularità
	 * @var int
	 */
	protected $grainLevel;

	/**
	 * Larghezza della grana
	 * @var int
	 */
	protected $grainWidth = 10;

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
	 * x punto centrale
	 * @var int
	 */
	protected $xCenter;
	
	/**
	 * y punto centrale
	 * @var int
	 */
	protected $yCenter;	
	
	/**
	 * vettore dei task
	 * @var tasks[]
	 */
	protected $tasks = array();

	/**
	 * vettore di dipendenze
	 * @var coppie (task_id, array(task_dipendenze_id))
	 */
	protected $dep = array();
	
	/**
	 * vettore di task grafici
	 * @var GifGanttTask[]
	 */
	protected $gTasks = array();
	
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
		// genera l'albero dei task
		$this->tdt = $this->tdtGenerator->generateTaskDataTree();
		
		// visita l'albero visibile
		$this->tasks = $this->tdt->getVisibleTree()->deepVisit();
//		$this->tasks = $this->tdt->visibleDeepVisit();
		// FIX: gestire caso senza task

		// prendi le dipendenze
		$this->deps = $this->tdt->computeDependencyRelationOnVisibleTasks();
		// calcola una sola volta il numero dei task dell'albero
		$this->numTasks = sizeOf($this->tasks);
		
		// ricava date start finish e today da opzioni utente
		switch(UserOptionsChoice::GetInstance()->getTimeRangeUserOption()){
			
 			// inizio e fine custom			
			case TimeRange::$CustomRangeUserOption:
				$dates = UserOptionsChoice::GetInstance()->getCustomRangeValues();
				$start = mangoToGanttDate($dates['start']);
				$end = add_date(mangoToGanttDate($dates['end']),0,1);
			break;
			
			default:
			// inizio e fine del progetto
			case TimeRange::$WholeProjectRangeUserOption: 
				$dates = UserOptionsChoice::GetInstance()->getCustomRangeValues();
				$tempToday = mangoToGanttDate($dates['today']);
//				echo $dates['today'];					
				// TODO: ricavare inizio e fine del progetto
				// FIX: gestire il caso in cui actual non termina o non esiste
				$sProj = null;
				$fProj = null;
				for($i=0; $i<$this->numTasks; $i++){
					$td = $this->tasks[$i];
					$planned = $td->getInfo()->getPlannedTimeFrame();
					$actual = $td->getInfo()->getActualTimeFrame();
					if($actual['start_date'] == null && $sProj == null){
						// do nothing
					}else if($actual['start_date'] == null && $sProj != null){
						$sProj = min($planned['start_date'],$sProj);
					}else if($actual['start_date'] != null && $sProj == null){
						$sProj = min($planned['start_date'],$actual['start_date']);
					}else{
						$sProj = min($planned['start_date'],$actual['start_date'],$sProj);
					}
					if($actual['finish'] == null){
//						echo "max: ".$planned['finish_date'].",$tempToday,$fProj <br>";
						$fProj = max($planned['finish_date'],$tempToday,$fProj);
					}else{
						$fProj = max($planned['finish_date'],$actual['finish_date'],$fProj);
					}
				}
//				echo "inizio $sProj - fine $fProj";
				if($sProj == null){
					$start = $dates['today'];
					$end = add_date($start,0,1);
				}
				
				$start = add_date($sProj,0,-1);
				$end = add_date($fProj,0,1);
			break;
			
			// inizio custom e fine today
			case TimeRange::$FromStartToNowRangeUserOption:
				$dates = UserOptionsChoice::GetInstance()->getCustomRangeValues();
				$start = mangoToGanttDate($dates['start']);
				$end = add_date(mangoToGanttDate($dates['today']),0,1);
			break;
			
			// inizio today e fine custom
			case TimeRange::$FromNowToEndRangeUserOption:
				$dates = UserOptionsChoice::GetInstance()->getCustomRangeValues();
				$start = add_date(mangoToGanttDate($dates['today']),0,-1);
				$end = add_date(mangoToGanttDate($dates['end']),0,1);
			break;
		}
		
		//echo "inizio $start - fine $end";		
		$this->sDate = $start;
		$this->fDate = $end;
		$this->today = mangoToGanttDate($dates['today']);

		// FIX: adattare la grana
		// acquisizione tipo di grana
		switch(UserOptionsChoice::GetInstance()->getTimeGrainUserOption()){
			case TimeGrainEnum::$HourlyGrainUserOption: $this->grainLevel = 5; break;
			case TimeGrainEnum::$DailyGrainUserOption: $this->grainLevel = 4; break;
			default:			
			case TimeGrainEnum::$WeaklyGrainUserOption: $this->grainLevel = 3; break;
			case TimeGrainEnum::$MonthlyGrainUserOption: $this->grainLevel = 2; break;
			case TimeGrainEnum::$AnnuallyGrainUserOption: $this->grainLevel = 1; break;
		}
		
		// costruisci il canvas
		$this->makeCanvas();
				
		// fissa le coordinate del punto centrale (più una tolleranza)
		$this->xCenter = $this->getLeftColumnWidth() + $this->tol + 8;
		$this->yCenter = $this->chart->getHeight() - 
			$this->numTasks*($this->verticalSpace + $this->labelHeight) - 
			$this->verticalSpace - $this->tol;

		$this->makeBorder();
		$this->makeRightColumn();
		$this->makeLeftColumn();
		$this->chart->draw();		
	}
	
	/**
	 * calcola lo spazio occupato dalla colonna di sinistra, necessita la gifImage 
	 * chart generata
	 */
	protected function getLeftColumnWidth(){
		$maxlen = 0;
		foreach($this->tasks as $task){
			$str = $task->getInfo()->getWBSId();
			if(UserOptionsChoice::GetInstance()->showTaskNameUserOption()){
				$str = $str.$task->getInfo()->getTaskName();
			}
			$maxlen = max($maxlen,GifLabel::getPixelWidthOfText($str,$this->fontSize));
		}
		if($maxlen > intval($this->leftColumnSpace * $this->chart->getWidth())+1){
			return intval($this->leftColumnSpace * $this->chart->getWidth()+1);
		}else{
			return $maxlen;
		}
	}
	
	/**
	 * Funzione di generazione del canvas
	 */	
	protected function makeCanvas(){
		switch(UserOptionsChoice::GetInstance()->getImageDimensionUserOption()){
			case ImageDimension::$CustomDimUserOption:
				$values = UserOptionsChoice::GetInstance()->getCustomDimValues();
				$width = $values['width'];
			break;			
			case ImageDimension::$FitInWindowDimUserOption:
				$width = $_GET[UserOptionEnumeration::$FitInWindowWidthUserOption] - 40;
				//$width = $values['width'];
			break;			
			case ImageDimension::$OptimalDimUserOption:
			// FIX: non funziona
				$diff = date_diff(mktime($this->fDate),mktime($this->sDate));
				switch($this->grainLevel){
					case 5: // ore
						$width = $diff[3] * $this->grainWidth + $this->getLeftColumnWidth() + 2*$this->tol;
					break;
					case 4: // giorni
						$width = $diff[2] * $this->grainWidth + $this->getLeftColumnWidth() + 2*$this->tol;						
					break;
					case 3: // settimane
						$width = intval($diff[2] * $this->grainWidth / 7) + $this->getLeftColumnWidth() + 2*$this->tol;					
					break;
					case 2: // mesi
						$width = $diff[1] * $this->grainWidth + $this->getLeftColumnWidth() + 2*$this->tol;											
					break;
					case 1: // anni
						$width = $diff[0] * $this->grainWidth + $this->getLeftColumnWidth() + 2*$this->tol;											
					break;					
				}
			break;
			case ImageDimension::$DefaultDimUserOption:
				$width =	800;
			break;
		}
		
		$this->chart = new GifImage(
			$width, 
			$this->grainLevel * $this->labelGrainHeight + 2*$this->tol + 
			$this->numTasks*($this->verticalSpace + $this->labelHeight) + 
			$this->verticalSpace);
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
		// titolo progetto
		$frontWidth = $this->chart->getWidth() - $this->xCenter;
		
		// generazione calendario
		$startTS = strtotime($this->sDate);
		$finishTS = strtotime($this->fDate);
		
		for($i = $this->grainLevel ; $i > 0 ; $i--){
			if($i ==  $this->grainLevel){
				$this->makeCalLine(
					$startTS,
					$finishTS,
					$i,
					true,
					$this->chart->getHeight() - $this->tol
				);
			}else{
				$this->makeCalLine(
					$startTS,
					$finishTS,
					$i
				);				
			}
		}
	}
	
	/**
	 * Funzione di generazione grafica della label del titolo del progetto
	 */
	protected function makeTitle(){
		// titolo progetto
		// TODO: titolo progetto
		$title = new GifBoxedLabel(
			$this->tol, // x
			$this->tol, // y
			$this->xCenter - $this->tol, // larghezza
			$this->yCenter - $this->tol, // altezza
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
		$xLeftCol = $this->tol;
		$yLeftCol = $this->yCenter;//$this->tol + $this->grainLevel * $this->labelGrainHeight;
		// disegno il box della colonna sinistra
		$leftCol = new GifBox(
			$xLeftCol, // x
			$yLeftCol, // y
			$this->xCenter - $this->tol, // larghezza 
			$this->chart->getHeight() - $this->yCenter - $this->tol // altezza
		);
		
// TODO: commentato per vedere i task sottostanti, decommentare poi
		$leftCol->setForeColor('white');
		$leftCol->drawOn($this->chart);


		for($i = 0; $i < $this->numTasks; $i++)
		{
			$label = $this->tasks[$i]->getInfo()->getWBSiD();
			// mostra il nome del task se specificato nelle opzioni utente
			if(UserOptionsChoice::GetInstance()->showTaskNameUserOption()){
				$label = $label.' '.$this->tasks[$i]->getInfo()->getTaskName();
			}
			
			// profondità indentatura
			//$indent = $this->tasks[$i]->getInfo()->getLevel() * $this->horizontalSpace;
			$label = new GifLabel(
				$xLeftCol + $this->tol, //+ $indent, // x
				$this->verticalSpace + $yLeftCol + 
					($i * ($this->verticalSpace + $this->labelHeight)), // y
				$this->xCenter - $xLeftCol, // width
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
		$wRightCol = $this->chart->getWidth() - $this->xCenter - $this->tol;
	
		// disegno il box della colonna destra
		$rightCol = new GifBox(
			$this->xCenter,
			$this->tol,
			$wRightCol,
			$this->chart->getHeight() - 2*$this->tol
		);
		$rightCol->drawOn($this->chart);

		$this->makeFront();
		$this->makeTitle();
		$this->makeGanttTaskBox();
		$this->makeGanttDependencies();
		$this->makeTodayLine();
	}

	/**
	 * Funzione di generazione della barra today
	 */
	protected function makeTodayLine(){
		$currentTS = strtotime($this->today);
		$startTS = strtotime($this->sDate);
		$finishTS = strtotime($this->fDate);
		$xCal = $titleWidth + $this->tol;
		$wCal = $this->chart->getWidth() - $xCal - $this->tol;
		
		$x = intval($this->xCenter + 
			($this->chart->getWidth() - $this->xCenter - $this->tol) * 
			($currentTS-$startTS)/($finishTS-$startTS));
		$y = $this->yCenter;
		$yf = $this->chart->getHeight() - $this->tol;
		
		DrawingHelper::LineFromTo($x,$y,$x,$yf,$this->chart,new LineStyle('black',1,'longdashed'));
	}
	
	/**
	 * Funzione di generazione grafica dei task box
	 */
	protected function makeGanttTaskBox(){
		$xGrid = $this->getLeftColumnWidth() + $this->tol;
		//$xGrid = $this->chart->getWidth()*$this->leftColumnSpace + $this->tol;
		$yGrid = $this->grainLevel * $this->labelGrainHeight + $this->tol;
		$xfGrid = $this->chart->getWidth() - $this->tol;
		$yfGrid = $this->chart->getHeight() - $this->tol;
		
		$hBox = ($this->labelHeight * 2 )/ 3;
		$hProgress = $this->labelHeight -$hBox;
		
		$this->gTasks = array();
		// per ogni task
		for($i = 0; $i < $this->numTasks; $i++)
		{
			$dt = $this->tasks[$i];
			$this->gTasks[$i] = new GifGanttTask(
				$this->xCenter, // x start
				$this->chart->getWidth() - $this->tol -1, // x finish
				$this->yCenter + $this->verticalSpace + $i*($this->verticalSpace + $this->labelHeight), // y start
				$this->labelHeight, // height
				$this->sDate, // startDate
				$this->fDate, // finishDate
				$dt, // task data
				$this->today, // today
				$this->uoc // opzioni utente
				);	
			$this->gTasks[$i]->drawOn($this->chart);
		}

	}
	
	/**
	 * Funzione di generazione grafica di una dipendenza tra due task, è 
	 * nessario eseguire 
	 * @see chartgenerator/GanttChartGenerator#makeGanttTaskBox()	
	 */
	protected function makeGanttDependencies(){
		$this->deps;
//		echo "begin scanning visible tasks<br>";
		// per ogni task visibile
		for($i=0 ; $i<$this->numTasks; $i++){
			// prendi il vettore delle dipendenze
			$taskDeps = $this->deps[$this->tasks[$i]->getInfo()->getTaskID()];
			// per ogni dipendenza del task $i
			$numDep = sizeOf($taskDeps);
//			echo "task_id i ".$this->tasks[$i]->getInfo()->getTaskID()."<br>";			
//			echo "begin scanning deps ($numDep)<br>";
			for($j=0 ; $j < $numDep; $j++){
				// prendi l'id della dipendenza
				$id_dep = $taskDeps[$j];
				// cercala nei nodi visibili
//				echo "begin scanning visible tasks in deps<br>";
//				echo "task_id j ".$id_dep."<br>";				
				for($k=0 ; $k < $this->numTasks; $k++){
					// se $k è la dipendenza di $i
//					echo "task_id k ".$this->tasks[$k]->getInfo()->getTaskID()."<br>";
					if($this->tasks[$k]->getInfo()->getTaskID() == $id_dep){
//						echo " dep $id_dep <br>";
						$point1 = $this->gTasks[$i]->getPlannedRightMiddlePoint();
						$point2 = $this->gTasks[$k]->getPlannedLeftMiddlePoint();
//						echo "p1: (".$point1['x'].",".$point1['y'].")<br>";
//						echo "p2: (".$point2['x'].",".$point2['y'].")<br>";						
						DrawingHelper::GanttDependencyLine(
							$point1['x'],
							$point1['y'],
							$point2['x'],
							$point2['y'],
							10,
							true,
							$this->chart,
							new LineStyle('#7F7F7F')
						);
						break;
					}
				}
			}
		}
	}
	
	/**
	 * funzione di disegno di una riga del calendario
	 */
	private function makeCalLine($startTS,$finishTS,$level,$grid=false,$yfGrid=null){
		$hour = 0;
		$days = 0;
		$mounth = 0;
		$year = 0;
		
		switch($level){
			case 5:
				$hour = 1;
				$formatDate = 'H';
				$beginDate = date('Y-m-d H',$startTS).':00:00';
				$HAlign = 'left';
			break;
			case 4:
				$days = 1;
				$formatDate = 'D';
				$beginDate = date('Y-m-d',$startTS).' 00:00:00';
				$HAlign = 'center';
			break;
			case 3:	
				$days = 7;
				$formatDate = 'd/m';	
				$beginDate = date('Y-m-d',$startTS).' 00:00:00';
				$HAlign = 'left';
			break;
			case 2:
				$mounth = 1;
				$formatDate = 'M';	
				$beginDate = date('Y-m',$startTS).'-01 00:00:00';
				$HAlign = 'center';
			break;
			case 1:
				$year = 1;
				$formatDate = 'Y';	
				$beginDate = date('Y',$startTS).'-01-01 00:00:00';	
				$HAlign = 'center';
			break;
		}

		$wCal = $this->chart->getWidth() - $this->xCenter - $this->tol;
		$xPrec = $this->xCenter;
		$precTS = $startTS; 
		$currentTS = strtotime(add_date($beginDate,$hour,$days,$mounth,$year));
		$xCurrent = intval($this->xCenter+ $wCal * 
			($currentTS-$startTS)/($finishTS-$startTS));
		// per ogni intervallo
		while($currentTS < $finishTS){
			// costruisci il box
			$slice = new GifBoxedLabel(
				$xPrec, // x
				$this->tol + ($level-1)*$this->labelGrainHeight, // y
				$xCurrent - $xPrec, // larghezza
				$this->labelGrainHeight, // altezza
				date($formatDate,$precTS), // data
				$this->fontSize // dim font
			);
			$slice->getLabel()->setHAlign($HAlign);
			$slice->getBox()->setForeColor('white');
			$slice->drawOn($this->chart);
			// se c'è la griglia, scrivi il tratto
//	echo "xcurrent $xCurrent , xprec $xPrec <br>";
			if($grid){
				if($yfGrid == null){
					$yfGrid = $this->chart->getHeight() - $this->tol;
				}
				DrawingHelper::LineFromTo(
					$xCurrent,
					$this->yCenter,
					$xCurrent,
					$yfGrid,
					$this->chart,
					new LineStyle('#7F7F7F')
				);
			}
			// passa all'intervallo successivo
			$xPrec = $xCurrent;
			$precTS = $currentTS;
			$currentTS = strtotime(add_date(date('Y-m-d H:i:s',$currentTS),$hour,$days,$mounth,$year));
			$xCurrent = intval($this->xCenter + $wCal*($currentTS-$startTS)/($finishTS-$startTS));
		}
		// costruisci l'ultimo box
		$slice = new GifBoxedLabel(
			$xPrec, // x
			$this->tol + ($level-1)*$this->labelGrainHeight, // y
			$this->xCenter + $wCal - $xPrec, // larghezza
			$this->labelGrainHeight, // altezza
			date($formatDate,$precTS), // data
			$this->fontSize // dim font
		);
		$slice->getLabel()->setHAlign($HAlign);		
		$slice->getBox()->setForeColor('white');			
		$slice->drawOn($this->chart);
	}
}