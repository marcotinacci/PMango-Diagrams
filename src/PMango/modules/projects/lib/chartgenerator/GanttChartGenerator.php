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
require_once dirname(__FILE__).'/../taskdatatree/Project.php';
require_once dirname(__FILE__).'/ChartTypesEnum.php';

/**
 * Questa classe implementa il metodo di generazione del diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.11
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
	protected $minGrainWidth = 20;

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
/*	DEBUG
	protected function testVisit(){
		foreach($this->tasks as $task){
			echo "testVisit<br>";			
			echo "task ".$task->getInfo()->getWBSId()."<br>";
			echo "atomico? ".($task->isAtomic()?"si":"no")."<br>";
			echo "numero figli: ".count($task->getChildren())."<br><br>";
		}
	}
*/	
	/**
	 * Funzione di generazione grafica del diagramma Gantt
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function generateChart(){
		// FIX: gestire caso senza task

		// genera l'albero dei task
		$this->tdt = $this->tdtGenerator->generateTaskDataTree();
		
		// visita l'albero visibile
		$this->tasks = $this->tdt->visibleDeepVisit();

		// prendi le dipendenze
		$this->dep = $this->tdt->computeDependencyRelationOnVisibleTasks();

		// calcola una sola volta il numero dei task dell'albero visibile
		$this->numTasks = sizeOf($this->tasks);

		// acquisizione tipo di grana
		$this->setGrain();
		
		// imposta date start, finish e today
		$this->setTimeRange();

		// costruisci il canvas (necessita grana)
		$this->makeCanvas();

		// fissa le coordinate del punto centrale (più una tolleranza)
		$this->xCenter = $this->getLeftColumnWidth() + $this->tol + 8;
		$this->yCenter = $this->chart->getHeight() - 
			$this->numTasks*($this->verticalSpace + $this->labelHeight) - 
			$this->verticalSpace - $this->tol;		
				
		// generazione grafica
		$this->makeFront();
		$this->makeTitle();
		$this->makeGanttTaskBox();
		$this->makeGanttDependencies();
		$this->makeTodayLine();
		$this->makeLeftColumn();
		$this->borderRemark();
		// stampa
		$this->chart->draw();		
	}
	
	/**
	 * calcola lo spazio occupato dalla colonna di sinistra, necessita la gifImage 
	 * chart generata
	 */
	protected function getLeftColumnWidth($optimal = false){
		$maxlen = 0;
		foreach($this->tasks as $task){
			$str = $task->getInfo()->getWBSId();
			if(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->showTaskNameUserOption()){
				$str = $str.$task->getInfo()->getTaskName();
			}
			$maxlen = max($maxlen,GifLabel::getPixelWidthOfText($str,$this->fontSize));
		}
		if(!$optimal && $maxlen > intval($this->leftColumnSpace * $this->chart->getWidth())+1){
			return intval($this->leftColumnSpace * $this->chart->getWidth()+1);
		}else{
			return $maxlen + $this->tol;
		}
	}
	
	/**
	 * acquisisce la grana dalle opzioni utente e le adatta alla 
	 * visualizzazione
	 */
	protected function setGrain(){
		// acquisizione tipo di grana
		switch(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getTimeGrainUserOption()){
			default:
			case TimeGrainEnum::$HourlyGrainUserOption: $this->grainLevel = 5; break;
			case TimeGrainEnum::$DailyGrainUserOption: $this->grainLevel = 4; break;
			case TimeGrainEnum::$WeaklyGrainUserOption: $this->grainLevel = 3; break;
			case TimeGrainEnum::$MonthlyGrainUserOption: $this->grainLevel = 2; break;
			case TimeGrainEnum::$AnnuallyGrainUserOption: $this->grainLevel = 1; break;
		}		
	}
	
	/**
	 * funzione che imposta le date start, finish e today
	 */
	protected function setTimeRange(){
		// setta la tolleranza
		$tolH = 0; $tolD = 0; $tolM = 0; $tolY = 0;
		switch($this->grainLevel){
			default:
			case 5: $tolH = 1; break;
			case 4: $tolD = 1; break;
			case 3: $tolD = 7; break;
			case 2: $tolM = 1; break;
			case 1: $tolY = 1; break;						
		}
		
		// ricava date start finish e today da opzioni utente
		switch(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getTimeRangeUserOption()){
			
 			// inizio e fine custom			
			case TimeRange::$CustomRangeUserOption:
				$dates = UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getCustomRangeValues();
				$start = mangoToGanttDate($dates['start']);
				$end = add_date(mangoToGanttDate($dates['end']),0,1);
			break;
			
			default:
			// inizio e fine del progetto
			case TimeRange::$WholeProjectRangeUserOption: 
				$dates = UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getCustomRangeValues();
				$tempToday = mangoToGanttDate($dates['today']);
//				echo "today: ".$dates['today']."<br>";
				$sProj = null;
				$fProj = null;
				for($i=0; $i<$this->numTasks; $i++){
					$td = $this->tasks[$i];
					$planned = $td->getInfo()->getPlannedTimeFrame();
					$actual = $td->getInfo()->getActualTimeFrame();
					if($actual['start_date'] == null && $sProj == null){
						$sProj = $planned['start_date'];
					}else if($actual['start_date'] == null && $sProj != null){
						$sProj = min($planned['start_date'],$sProj);
					}else if($actual['start_date'] != null && $sProj == null){
						$sProj = min($planned['start_date'],$actual['start_date']);
					}else{
						$sProj = min($planned['start_date'],$actual['start_date'],$sProj);
					}
					if($actual['finish_date'] == null || $this->tasks[$i]->getInfo()->getPercentage() <= 99){
//						echo "max: ".$planned['finish_date'].",$tempToday,$fProj <br>";
						$fProj = max($planned['finish_date'],$tempToday,$fProj);
					}else{
//						echo "max: ".$planned['finish_date'].",".$actual['finish_date'].",$fProj <br>";						
						$fProj = max($planned['finish_date'],$actual['finish_date'],$fProj);
					}
				}
//				echo "inizio $sProj - fine $fProj <br>";
				if($sProj == null){
					$start = add_date($dates['today'],-$tolH,-$tolD,-$tolM,-$tolY);
				}else{
					$start = add_date($sProj,-$tolH,-$tolD,-$tolM,-$tolY);
				}
				$end = add_date($fProj,$tolH,$tolD,$tolM,$tolY);					
			break;
			
			// inizio project e fine today
			case TimeRange::$FromStartToNowRangeUserOption:
				$dates = UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getCustomRangeValues();
				$sProj = $dates['today'];
				for($i=0; $i<$this->numTasks; $i++){
					$td = $this->tasks[$i];
					$planned = $td->getInfo()->getPlannedTimeFrame();
					$actual = $td->getInfo()->getActualTimeFrame();
					if($actual['start_date'] == null && $sProj == null){
						$sProj = $planned['start_date'];
					}else if($actual['start_date'] == null && $sProj != null){
						$sProj = min($planned['start_date'],$sProj);
					}else if($actual['start_date'] != null && $sProj == null){
						$sProj = min($planned['start_date'],$actual['start_date']);
					}else{
						$sProj = min($planned['start_date'],$actual['start_date'],$sProj);
					}
				}
				$start = add_date($sProj,-$tolH,-$tolD,-$tolM,-$tolY);				
				$end = add_date($dates['today'],$tolH,$tolD,$tolM,$tolY);
			break;
			
			// inizio today e fine project
			case TimeRange::$FromNowToEndRangeUserOption:
				$dates = UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getCustomRangeValues();
				$fProj = mangoToGanttDate($dates['today']);;
				for($i=0; $i<$this->numTasks; $i++){
					$td = $this->tasks[$i];
					$planned = $td->getInfo()->getPlannedTimeFrame();
					$actual = $td->getInfo()->getActualTimeFrame();
					if($actual['finish_date'] == null || $this->tasks[$i]->getInfo()->getPercentage() <= 99){
						$fProj = max($planned['finish_date'],$fProj);
					}else{				
						$fProj = max($planned['finish_date'],$actual['finish_date'],$fProj);
					}
				}
				$start = add_date($dates['today'],-$tolH,-$tolD,-$tolM,-$tolY);
				$end = add_date($fProj,$tolH,$tolD,$tolM,$tolY);				
			break;
		}
		
		$this->sDate = $start;
		$this->fDate = $end;
		$this->today = mangoToGanttDate($dates['today']);	
	}
	
	/**
	 * Funzione di generazione del canvas
	 */	
	protected function makeCanvas(){
		switch(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getImageDimensionUserOption()){
			case ImageDimension::$CustomDimUserOption:
				$values = UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getCustomDimValues();
				$width = $values['width'];
				if($width == ''){
					// TODO: prendere il valore di default da opzioni di configurazione
					$width = 800;
				}
			break;			
			case ImageDimension::$FitInWindowDimUserOption:
				$width = $_GET[UserOptionEnumeration::$FitInWindowWidthUserOption] - 40;
			break;			
			case ImageDimension::$OptimalDimUserOption:
//			echo "inizio ".$this->sDate." - fine ".$this->fDate." <br>";	
//			echo "inizio ".strtotime($this->sDate)." - fine ".strtotime($this->fDate)." <br>";		
						
				$diff = diff_date(strtotime($this->fDate),strtotime($this->sDate));
/* DEBUG:
				echo "<br>numero secondi: ".$diff['second'];
				echo "<br>numero minuti: ".$diff['minute'];								
				echo "<br>numero ore: ".$diff['hour'];
				echo "<br>numero giorni: ".$diff['day'];
				echo "<br>numero mesi: ".$diff['month'];
				echo "<br>numero anni: ".$diff['year'];
*/
 // (year, month, day, hour, minute, second)	
				$time = mktime($diff['hour'],$diff['minute'],$diff['second'],$diff['month']+1,$diff['day']+1,$diff['year']+1970);
//				echo "time: $time <br>";
				switch($this->grainLevel){
					case 5: // ore
						$width = ($time / (60*60)) * $this->minGrainWidth + $this->getLeftColumnWidth(true) + 2*$this->tol;
					break;
					case 4: // giorni
						$width = ($time / (24*60*60)) * $this->minGrainWidth + $this->getLeftColumnWidth(true) + 2*$this->tol;
					break;
					case 3: // settimane
						$width = ($time / (7*24*60*60)) * $this->minGrainWidth + $this->getLeftColumnWidth(true) + 2*$this->tol;					
					break;
					case 2: // mesi
						$width = ($time / (31*24*60*60)) * $this->minGrainWidth + $this->getLeftColumnWidth(true) + 2*$this->tol;										
					break;
					case 1: // anni
						$width = ($time / (365*24*60*60)) * $this->minGrainWidth + $this->getLeftColumnWidth(true) + 2*$this->tol;										
					break;					
				}
			break;
			default:
			case ImageDimension::$DefaultDimUserOption:
			// TODO: acquisire da file di configurazione
				$width =	800;
			break;
		}		
		// se non viene scelta la optimal view adatta la grana
		if(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->getImageDimensionUserOption() != 
			ImageDimension::$OptimalDimUserOption){
			// adatta la grana				
			do{
				switch($this->grainLevel){
					default:
					case 5: $timeGrain = 60*60; break;
					case 4: $timeGrain = 24*60*60; break;
					case 3: $timeGrain = 7*24*60*60; break;
					case 2: $timeGrain = 30*24*60*60; break; // approssimato un mese a 30 giorni
					case 1: $timeGrain = 365*24*60*60; break; // approssimato un anno a 365
				}
				$grainWidth = ($width * $timeGrain) / (strtotime($this->fDate) - strtotime($this->sDate));
				if($grainWidth < $this->minGrainWidth && $this->grainLevel > 1){
					$this->grainLevel--;
				}
			}while($grainWidth < $this->minGrainWidth && $this->grainLevel > 1);
		}
		$this->chart = new GifImage(
			$width, 
			$this->grainLevel * $this->labelGrainHeight + 2*$this->tol + 
			$this->numTasks*($this->verticalSpace + $this->labelHeight) + 
			$this->verticalSpace);
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
		$p = new Project();
		$p->loadProjectInfo();
		$title = new GifBoxedLabel(
			$this->chart,			
			$this->tol, // x
			$this->tol, // y
			$this->xCenter - $this->tol, // larghezza
			$this->yCenter - $this->tol, // altezza
			$p->getProjectName(), // titolo
			$this->fontSize // dim font
			);
//		$title->getBox()->setForeColor('white');
		$title->drawOn();				
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
			$this->chart,			
			$xLeftCol, // x
			$yLeftCol, // y
			$this->xCenter - $this->tol, // larghezza 
			$this->chart->getHeight() - $this->yCenter - $this->tol // altezza
		);
		
		$leftCol->setForeColor('white');
		$leftCol->drawOn();


		for($i = 0; $i < $this->numTasks; $i++)
		{
			$label = $this->tasks[$i]->getInfo()->getWBSiD();
			// mostra il nome del task se specificato nelle opzioni utente
			if(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->showTaskNameUserOption()){
				$label = $label.' '.$this->tasks[$i]->getInfo()->getTaskName();
			}
			
			// profondità indentatura
			//$indent = $this->tasks[$i]->getInfo()->getLevel() * $this->horizontalSpace;
			$label = new GifLabel(
				$this->chart,				
				$xLeftCol + $this->tol, //+ $indent, // x
				$this->verticalSpace + $yLeftCol + 
					($i * ($this->verticalSpace + $this->labelHeight)), // y
				$this->xCenter - $xLeftCol, // width
				$this->labelHeight, // height
				$label, // label
				$this->fontSize // size
				);
			$label->setHAlign('left');
			$label->drawOn();
		}
	}

	/**
	 * Funzione di generazione della barra today
	 */
	protected function makeTodayLine(){
		$currentTS = strtotime($this->today);
		$startTS = strtotime($this->sDate);
		$finishTS = strtotime($this->fDate);
		$wCal = $this->chart->getWidth() - $this->xCenter - $this->tol;
		
		$x = intval($this->xCenter + ($wCal) * 
			($currentTS-$startTS)/($finishTS-$startTS));
		$y = $this->yCenter;
		$yf = $this->chart->getHeight() - $this->tol;
		
//		echo "x: $x - y: $y";		
		DrawingHelper::LineFromTo($x,$y,$x,$yf,$this->chart,new LineStyle('black',1,'longdashed'));
	}
	
	/**
	 * Funzione di generazione grafica dei task box
	 */
	protected function makeGanttTaskBox(){
		$xGrid = $this->getLeftColumnWidth() + $this->tol;
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
				$this->chart,				
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
			$this->gTasks[$i]->drawOn();
		}

	}
	
	/**
	 * Funzione di generazione grafica di una dipendenza tra due task, è 
	 * nessario eseguire 
	 * @see chartgenerator/GanttChartGenerator#makeGanttTaskBox()	
	 */
	protected function makeGanttDependencies(){
//		DEBUG:
//		foreach($this->dep as $key => $d){
//			echo "key: $key <br>";
//		}
		foreach($this->tasks as $needKey => $needTask){
			if(!array_key_exists($needTask->getInfo()->getTaskID(),$this->dep)){
				continue;
			}
//			echo "need: ".$needTask->getInfo()->getTaskID()."<br>";			
			$deps = $this->dep[$needTask->getInfo()->getTaskID()];
			foreach($this->tasks as $depKey => $depTask){
//				echo "-need: ".$needTask->getInfo()->getTaskID()."<br>";
				if(!array_key_exists($depTask->getInfo()->getTaskID(),$deps)){
					continue;
				}
				$descs = $deps[$depTask->getInfo()->getTaskID()];
				foreach($descs as $desc){
//					echo "dep pos: ".$desc->dependentTaskPositionEnum."<br>";
//					echo "need pos: ".$desc->neededTaskPositionEnum."<br>";
					if($desc->neededTaskPositionEnum == TaskLevelPositionEnum::$ending){
						// ending
						$point1 = $this->gTasks[$needKey]->getPlannedRightMiddlePoint();
						$middleOut = false;						
					}else{
						// inner
						$point1 = $this->gTasks[$needKey]->getPlannedBottomMiddlePoint();
						$middleOut = true;						
					}
					if($desc->dependentTaskPositionEnum == TaskLevelPositionEnum::$starting){
						// starting
						$point2 = $this->gTasks[$depKey]->getPlannedLeftMiddlePoint();
						$middleIn = false;
					}else{
						// inner
						$point2 = $this->gTasks[$depKey]->getPlannedTopMiddlePoint();
						$middleIn = true;						
					}

					DrawingHelper::GanttFTSLine(
						$point1['x'],
						$point1['y'],
						$point2['x'],
						$point2['y'],
						10,
						true,
						$middleIn,
						$middleOut,
						$this->chart,
						new LineStyle('#7F7F7F')
					);					
				}
			}
		}
	}
	
	/**
	 * funzione di disegno di una riga del calendario
	 */
	private function makeCalLine($startTS,$finishTS,$level,$grid=false,$yfGrid=null){
		// FIX: strip weeks
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
// mktime($diff['hour'],$diff['minute'],$diff['second'],$diff['month']+1,$diff['day']+1,$diff['year']+1970);				
				$beginDate = date('Y-m-d',($startTS - ((date('w',$startTS)-1)%7)*24*60*60)).' 00:00:00';
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
				$this->chart,				
				$xPrec, // x
				$this->tol + ($level-1)*$this->labelGrainHeight, // y
				$xCurrent - $xPrec, // larghezza
				$this->labelGrainHeight, // altezza
				$formatDate == 'D' ? substr(date($formatDate,$precTS),0,1) : date($formatDate,$precTS), // data
				$this->fontSize // dim font
			);
			$slice->getLabel()->setHAlign($HAlign);
			$slice->getBox()->setForeColor('white');
			$slice->drawOn();
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
					new LineStyle('#7F7F7F',1,'dotted')
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
			$this->chart,			
			$xPrec, // x
			$this->tol + ($level-1)*$this->labelGrainHeight, // y
			$this->xCenter + $wCal - $xPrec, // larghezza
			$this->labelGrainHeight, // altezza
			$formatDate == 'D' ? substr(date($formatDate,$precTS),0,1) : date($formatDate,$precTS), // data
			$this->fontSize // dim font
		);
		$slice->getLabel()->setHAlign($HAlign);	
		$slice->getBox()->setForeColor('white');			
		$slice->drawOn();
	}

	/**
	 * funzione di decorazione del diagramma
	 */	
	protected function borderRemark(){
		// bordo esterno
		$box = new GifBox(
			$this->chart,
			$this->tol,
			$this->tol,
			$this->chart->getWidth() - 2*$this->tol,
			$this->chart->getHeight() - 2*$this->tol
			);
		$box->setBorderThickness(2);
		$box->drawOn();	

		// margine sinistro
		$mLeft = new GifBox(
			$this->chart,
			0,
			0,
			$this->tol -1,
			$this->chart->getHeight()-1
			);
		$mLeft->setForeColor('white');
		$mLeft->setBorderThickness(0);
		$mLeft->drawOn();
		
		// margine destro
		$mRight = new GifBox(
			$this->chart,
			$this->chart->getWidth() - $this->tol +1,
			0,
			$this->tol,
			$this->chart->getHeight()-1
			);
		$mRight->setForeColor('white');
		$mRight->setBorderThickness(0);
		$mRight->drawOn();
		
		// cornice
		$ext = new GifBox(
			$this->chart,
			0,
			0,
			$this->chart->getWidth()-1,
			$this->chart->getHeight()-1
		);
		$ext->setBorderThickness(2);		
		$ext->drawOn();		
		
		// riga verticale
		DrawingHelper::LineFromTo(
			$this->xCenter,
			$this->tol,
			$this->xCenter,
			$this->chart->getHeight() - $this->tol,
			$this->chart,
			new LineStyle('black',2)
		);
		
		// riga orizzontale
		DrawingHelper::LineFromTo(
			$this->tol,
			$this->yCenter,
			$this->chart->getWidth() - $this->tol,
			$this->yCenter,
			$this->chart,
			new LineStyle('black',2)
		);		
	}
}