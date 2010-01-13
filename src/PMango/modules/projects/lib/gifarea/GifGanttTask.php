<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifTriangle.php";
require_once dirname(__FILE__)."/../utils/TimeUtils.php";

// TODO: gestire caso task molto stretto coi triangoli
// TODO: label risorse

/**
 * Questa classe implementa la generazione grafica del task nel diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.7
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
	protected $fontSize = 10;
	
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
	function __construct($xStart, $xFinish, $y, $height, $startDate, 
		$finishDate, $taskData, $today, $uoc)
	{
		parent::__construct($xStart, $y,  $xFinish - $xStart, $height);

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
		if($actual['start_date'] == null){
			$startActualTS = null;
			$finishActualTS = null;
			// coordinate actual
			$xActual = null;
			$wActual = null;
			$this->actualStarted = false;
		}else{
			$startActualTS = strtotime($actual['start_date']);
			$finishActualTS = strtotime($actual['finish_date']);
			// coordinate actual			
			$xActual = intval($windowWidth * ($startActualTS-$startTS) 
				/ $windowDuration);
			if($this->td->getInfo()->getPercentage() > 99){ // per errori di approssimazione
				// caso task completato
				$wActual = intval($windowWidth * ($finishActualTS-$startActualTS) 
					/ $windowDuration);
			}else if($todayTS-$startActualTS > 0){
				// caso task in prosecuzione
				$wActual = ($windowWidth * ($todayTS-$startActualTS) 
					/ $windowDuration);
			}else{
				// caso task anticipato
				$wActual = ($windowWidth * ($finishActualTS-$startActualTS) 
					/ $windowDuration);				
			}
			$this->actualStarted = true;
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
				$xPlanned, // x
				0, // y
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
				$xActual,
				$hPlanned,
				$wActual,
				intval($height/3),
				$this->td->getInfo()->getPercentage()
			);
		}
		
		// costruzione triangoli se il task non è foglia ed è visibile
		if(!$this->td->isAtomic() && !$this->td->getCollapsed()){
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
					$xLeft, $hPlanned, $this->wTri, $hTri, 'left');
				$leftGifTriangle->setForeColor($cPlanned);
			}
			// se il triangolo destro è visibile
			if($xRight <= $xFinish - $xStart){
				// genero triangolo destro
				$rightGifTriangle = new GifTriangle(
					$xRight - $this->wTri, $hPlanned, $this->wTri, $hTri, 'right');
				$rightGifTriangle->setForeColor($cPlanned);
			}
		}
		
		// riga di collegamento actual-planned
		$this->xP = $xPlanned > $xFinish - $xStart ? $xFinish - $xStart : $xPlanned;
		$this->wP = $wPlanned;
		$this->hP = $hPlanned;
		$this->xA = $xActual > $xFinish - $xStart ? $xFinish - $xStart : $xActual;
		$this->wA = $wActual;
		
		// TODO: inserire se presenti in opzioni utente
		// label risorse
		//$this->subAreas['Resources'] = new GifLabel(,,);
		
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

		if($this->actualStarted){
			if(($this->xP + $this->wP) < $this->xA){
				$this->canvas->img->setColor('black');
				$this->canvas->img->line(
					$this->xP+$this->wP,
					$this->hP,
					$this->xA,
					$this->hP
				);
			}else if(($this->xA + $this->wA)< $this->xP){
				$this->canvas->img->setColor('black');
				$this->canvas->img->line(
					$this->xA+$this->wA,
					$this->hP,
					$this->xP,
					$this->hP
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