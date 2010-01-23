<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifTriangle.php";
require_once dirname(__FILE__)."/../utils/TimeUtils.php";

// TODO: dividere il codice in sotto procedure
// TODO: label risorse

/**
 * Questa classe implementa la generazione grafica del task nel diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.8
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class GifGanttTask extends GifArea
{
	/**
	 * TaskData da disegnare
	 * @var td
	 */
	private $td;
	
	/**
	 * Misura larghezza triangolo
	 * @var wTri
	 */
	protected $wTri = 4;

	/**
	 * coordinata x del box planned
	 * @var xP
	 */
	protected $xP;

	/**
	 * larghezza del box planned
	 * @var wP
	 */	
	protected $wP;

	/**
	 * altezza del box planned
	 * @var hP
	 */
	protected $hP;

	/**
	 * coordinata x del box actual
	 * @var xA
	 */
	protected $xA;

	/**
	 * larghezza del box actual
	 * @var wA
	 */	
	protected $wA;
	
	/**
	 * flag che segnala se l'attività actual è iniziata
	 * @var actualStarted
	 */	
	protected $actualStarted = false;

	/**
	 * dimensione dei font
	 * @var fontSize
	 */	
	protected $fontSize = 8;
	
	/**
	 * coordinata x reale del box planned	
	 * @var int
	 */
	protected $trueXPlanned;

	/**
	 * coordinata y reale del box planned
	 * @var int
	 */
	protected $trueYPlanned;
	
	/**
	 * larghezza reale del box planned
	 * @var int
	 */
	protected $trueWPlanned;
	
	/**
	 * coordinata x reale del box planned
	 * @var int
	 */
	protected $trueXActual;

	/**
	 * coordinata x reale del box planned
	 * @var int
	 */
	protected $trueYActual;

	/**
	 * coordinata x reale del box planned
	 * @var int
	 */
	protected $trueWActual;

	/**
	 * Costruttore
	 * @param xStart: x iniziale
	 * @param xFinish: x finale
	 * @param y: quota
	 * @param height: altezza
	 * @param startDate: data inizale della finestra
	 * @param finishDate: data finale della finestra
	 * @param task: informazioni da rappresentare (Task)
	 * @param today: data odierna
	 * @param uoc: opzioni utente (UserOptionsChoice)
	 */
	function __construct($gifImage, $xStart, $xFinish, $y, $height, $startDate, 
		$finishDate, $taskData, $today, $uoc)
	{
		parent::__construct($gifImage, $xStart, $y,  $xFinish - $xStart, $height);

		// task data
		$this->td = $taskData;

		// dati finestra
		$windowWidth = $xFinish - $xStart;
		$startTS = strtotime($startDate);
		$finishTS = strtotime($finishDate);
		$windowDuration = $finishTS - $startTS;
		
		// dati odierni
		$todayTS = strtotime($today);
				
		// dati task planned
		$planned = $this->td->getInfo()->getPlannedTimeFrame();
		$startPlannedTS = strtotime($planned['start_date']);
		$finishPlannedTS = strtotime($planned['finish_date']);
		
		// coordinate planned
		if($windowDuration == 0){
			$xPlanned = 0;
			$wPlanned = 0;			
		}else{
			$xPlanned = intval($windowWidth * ($startPlannedTS-$startTS) / $windowDuration);
			$wPlanned = intval($windowWidth * ($finishPlannedTS-$startTS) 
				/ $windowDuration) - $xPlanned;			
		}		

		// dati task actual (possono non esserci)
		$actual = $this->td->getInfo()->getActualTimeFrame();
//		echo "task: ".$this->td->getInfo()->getWBSId()."<br>";
//		echo "start date: ".$actual['start_date']."<br>";
//		echo "finish date: ".$actual['finish_date']."<br>";
//		echo "today: $today<br><br>";
		// controllo se l'actual non è iniziato
		if($actual['start_date'] == ''){
			$this->actualStarted = false;			
			$startActualTS = null;
			$finishActualTS = null;
			// coordinate actual
			$xActual = null;
			$wActual = null;
		}else{
			$this->actualStarted = true;
			$startActualTS = strtotime($actual['start_date']);
			if($actual['finish_date'] == null){
				$finishActualTS = $todayTS;
			}else{
				$finishActualTS = strtotime($actual['finish_date']);
			}
//			echo "start actual ts: $startActualTS <br>";
//			echo "finish actual ts: $finishActualTS <br><br>";
			// coordinate actual	
			$xActual = intval($windowWidth * ($startActualTS-$startTS) 
				/ $windowDuration);
			if($this->td->getInfo()->getPercentage() > 99){ // per errori di approssimazione
				// caso task completato
				$wActual = intval($windowWidth * ($finishActualTS-$startActualTS) 
					/ $windowDuration);
			}else if($todayTS-$startActualTS > 0){
				// caso task in prosecuzione
				$wActual = intval($windowWidth * ($todayTS-$startActualTS) 
					/ $windowDuration);
			}else
			{
				// caso task anticipato
				$wActual = intval($windowWidth * ($finishActualTS-$startActualTS) 
					/ $windowDuration);
			}
//			echo "startActualTS: $startActualTS - start date: ".date('Y-m-d H:i:s',$startActualTS)."<br>";
//			echo "finishActualTS: $finishActualTS - finish date: ".date('Y-m-d H:i:s',$finishActualTS)."<br>";
//			echo "todayTS: $todayTS - today: $today<br>";
//			echo "just w actual: $wActual <br><br>";
		}
		
		// coordinate di supporto (non vengono modificate)
		$this->trueXPlanned = $xPlanned;
		$this->trueYPlanned = $yPlanned;
		$this->trueWPlanned = $wPlanned;
		$this->trueXActual = $xActual;
		$this->trueYActual = $yActual;
		$this->trueWActual = $wActual;
				
		// caso foglia e caso nodo interno
		if($this->td->isAtomic()){
			// se il task è una foglia
			$hPlanned = intval(2*$height/3);
			$hActual = $height - $hPlanned;
			$cPlanned = 'white';
		}else{
			// se il task è nodo interno
			$hPlanned = intval($height/3);
			$cPlanned = 'black';
			$hActual = $hPlanned;
		}
		
		// se il task è visibile
		if(($xPlanned + $wPlanned > 0) && ($xPlanned < $xFinish - $xStart)){
			// costruzione del planned
			$plannedGifBox = new GifBox(
				$gifImage,
				$this->x + $xPlanned, // x
				$this->y, // y
				$wPlanned, // width
				$hPlanned // height
				);
			$plannedGifBox->setForeColor($cPlanned);
		}
		
		// se l'actual è visibile
		if(($xActual + $wActual > 0) && ($xActual < $xFinish - $xStart) && 
				$this->actualStarted){
			// costruzione actual
			$actualGifProgressBar = new GifProgressBar(
				$gifImage,				
				$this->x + $xActual,
				$this->y + $hPlanned,
				$wActual,
				intval($height/3),
				$this->td->getInfo()->getPercentage()
			);
		}
		
		// costruzione triangoli se il task non è foglia ed è visibile
		if(!$this->td->isAtomic()){
			if($this->actualStarted){
				$xLeft = min($xActual, $xPlanned);
				$xRight = max($xActual+$wActual, $xPlanned+$wPlanned);
			}else{
				$xLeft = $xPlanned;
				$xRight = $xPlanned+$wPlanned;
			}

			// adatta larghezza triangoli in casi degeneri
			if($xRight - $xLeft < 2 * $this->wTri){
				$this->wTri = intval(($xRight - $xLeft)/2);
			}
			
			// altezza triangoli			
			$hTri = $height - $hPlanned;

			// se il triangolo sinistro è visibile
			if($xLeft >= 0){
				// genero triangolo sinistro
				$leftGifTriangle = new GifTriangle(
					$gifImage, $this->x + $xLeft, $this->y + $hPlanned, $this->wTri, $hTri, 'left');
				$leftGifTriangle->setForeColor($cPlanned);
			}
			// se il triangolo destro è visibile
			if($xRight - $this->wTri <= $xFinish - $xStart){
				// genero triangolo destro 
				$rightGifTriangle = new GifTriangle(
				$gifImage, $this->x + $xRight - $this->wTri, $this->y + $hPlanned, $this->wTri, $hTri, 'right');
				$rightGifTriangle->setForeColor($cPlanned);
			}
		}
		
		// riga di collegamento actual-planned
		$this->xP = $xPlanned; //$xPlanned > $xFinish - $xStart ? $xFinish - $xStart : $xPlanned;
		$this->wP = $wPlanned;
		$this->hP = $hPlanned;
		$this->xA = $xActual; //$xActual > $xFinish - $xStart ? $xFinish - $xStart : $xActual;
		$this->wA = $wActual;
		
		// label risorse
		//($x, $y, $width, $height, $text, $size)
		if(UserOptionsChoice::GetInstance(ChartTypesEnum::$Gantt)->showResourcesUserOption()){
			$res = $this->td->getInfo()->getResources();
			$str = "";
			for($i = 0; $i < count($res); $i++){
				 //struttura risorsa: (effort, name, rule)
				$str .= $res[$i]['Effort'].' ph '.$res[$i]['LastName'].' '.$res[$i]['Role'];
				if($i<count($res)-1){
					$str .= ', ';
				}
			}
			// se la label comincia dentro al gantt stampala
			$xLabel = $this->x + $xPlanned + $wPlanned + 5;
			if($xLabel >= $this->x){
				$this->subAreas['Resources'] = new GifLabel(
					$gifImage,				
					$xLabel,
					$this->y + ($this->td->isAtomic() ? 0 : -intval($height/3)),
					$xFinish - $xLabel,
					$hPlanned,
					$str,
					$this->fontSize
					);
				$this->subAreas['Resources']->setVAlign('top');
				$this->subAreas['Resources']->setHAlign('left');
			}
		}
		
		// DEBUG
		/*
		echo "x planned: $xPlanned <br>";
		echo "w planned: $wPlanned <br>";
		echo "x actual: $xActual <br>";
		echo "w actual: $wActual <br>";
		*/
		// stampa le componenti nel giusto ordine
		if(isset($plannedGifBox)){
			$this->subAreas['planned'] = $plannedGifBox;
		}
		if(isset($leftGifTriangle)){
			$this->subAreas['leftTriangle'] = $leftGifTriangle;
		}
		if(isset($rightGifTriangle)){
			$this->subAreas['rightTriangle'] = $rightGifTriangle;
		}
		if(isset($actualGifProgressBar)){
			$this->subAreas['actual'] = $actualGifProgressBar;
		}		
	}
	
	/**
	 * override
	 */
	protected function canvasDraw(){
		// riga di collegamento actual-planned
//		echo "task: ".$this->td->getInfo()->getWBSId()."<br>";
//		echo "actual started? ".($this->actualStarted?"si":"no")."<br>";
//		echo "x planned:".$this->xP."<br>";
//		echo "h planned:".$this->hP."<br>";
//		echo "w planned:".$this->wP."<br>";	
//		echo "x actual:".$this->xA."<br>";
//		echo "w actual:".$this->wA."<br><br>";
		
		if($this->actualStarted){
			if(($this->xP + $this->wP) < $this->xA){
				$this->canvas->img->setColor('black');
				$this->canvas->img->line(
					$this->x + $this->xP+$this->wP,
					$this->y + $this->hP,
					$this->x + $this->xA,
					$this->y + $this->hP
				);
			}else if(($this->xA + $this->wA)< $this->xP){
				$this->canvas->img->setColor('black');
				$this->canvas->img->line(
					$this->x + $this->xA+$this->wA,
					$this->y + $this->hP,
					$this->x + $this->xP,
					$this->y + $this->hP
				);			
			}
		}
	}
	
	/**
	 * getter del planned box
	 */
	public function getPlannedBox()
	{
		return $this->subAreas['planned'];
	}
	
	/**
	 * getter dell'actual progress bar
	 */	
	public function getActualProgressBar()
	{
		return $this->subAreas['actual'];
	}	
	
	public function setVisiblesFromOptionsChoice($userOptionsChoice)
	{
		// TODO: not implemented yet
	}
	
	public function getFontsize()
	{
		return $this->fontSize;
	}
	
	public function setFontSize($size)
	{
		$this->fontSize = $size;
	}
	
	public function getPlannedTopMiddlePoint()
	{
		$point = array();
		$point['x']=$this->getX() + intval($this->trueXPlanned+($this->trueWPlanned/2));
		$point['y']=$this->getY() + $this->trueYPlanned;
		return $point;
	}
	
	public function getPlannedBottomMiddlePoint()
	{
		$point = array();
		$point['x']=$this->getX() + $this->trueXPlanned+($this->trueWPlanned/2);
		$point['y']=$this->getY() + $this->trueYPlanned+($this->hP);
		return $point;
	}
	
	public function getPlannedLeftMiddlePoint()
	{
		$point = array();
		$point['x']=$this->getX() + $this->trueXPlanned;
		$point['y']=$this->getY() + $this->trueYPlanned+($this->hP/2);
		return $point;
	}
	
	public function getPlannedRightMiddlePoint()
	{
		$point = array();
		$point['x']=$this->getX() + $this->trueXPlanned+($this->trueWPlanned);
		$point['y']=$this->getY() + $this->trueYPlanned+($this->hP/2);
		return $point;
	}
	
}

?>