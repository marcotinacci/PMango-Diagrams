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
 * @version: 0.6
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
	
	// TODO: wTri in opzioni di conf
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
		parent::__construct($xStart, $y, $xFinish - $xStart, $height);

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
		$xPlanned = intval($windowWidth * ($startPlannedTS-$startTS) / $windowDuration);
		$wPlanned = intval($windowWidth * ($finishPlannedTS-$startTS) 
			/ $windowDuration) - $xPlanned;

		// dati task actual (possono non esserci)
		$actual = $this->td->getInfo()->getActualTimeFrame();
		if($actual['start_date'] == null){
			$startActualTS = null;
			$finishActualTS = null;
			// coordinate actual
			$xActual = null;
			$wActual = null;
			$this->actualStarted = false;	
		}else if($actual['finish_date'] == null){
			$startActualTS = strtotime($actual['start_date']);
			$finishActualTS = null;
			// coordinate actual			
			$xActual = intval($windowWidth * ($startActualTS-$startTS) 
				/ $windowDuration);
			$wActual = intval($windowWidth * ($todayTS-$startTS) 
				/ $windowDuration) - $xActual;
			$this->actualStarted = true;
		}else{
			$startActualTS = strtotime($actual['start_date']);
			$finishActualTS = strtotime($actual['finish_date']);
			// coordinate actual			
			$xActual = intval($windowWidth * ($startActualTS-$startTS) 
				/ $windowDuration);
			$wActual = intval($windowWidth * ($finishActualTS-$startTS) 
				/ $windowDuration) - $xActual;			
			$this->actualStarted = true;
		}
		
		// coordinate di supporto (non vengono modificate)
		$trueXPlanned = $xPlanned;
		$trueYPlanned = $yPlanned;
		$trueWPlanned = $wPlanned;
		$trueXActual = $xActual;
		$trueYActual = $yActual;
		$trueWActual = $wActual;

		// caso foglia e caso nodo interno
		if(sizeOf($this->td->getChildren()) == 0){
			// se il task è una foglia
			$hPlanned = intval(2*$height/3);
			$hActual = $height - $hPlanned;
			$cPlanned = 'white';
			$isLeaf = true;
		}else{
			// se il task ha figli
			$hPlanned = intval($height/3);
			$cPlanned = 'black';
			$hActual = $hPlanned;
			$isLeaf = false;
		}
		
		// se il planned task è visibile nella finestra
		if(($xPlanned + $wPlanned > 0) && ($xPlanned < $xFinish - $xStart)){
			$plannedVisible = true;	
			// controlli uscita dalla griglia a sinistra
			if($xPlanned < 0){
				$wPlanned += $xPlanned;
				$xPlanned = 0;
				$plannedOutLeft = true;
			}else{
				$plannedOutLeft = false;
			}
			
			// controlli uscita dalla griglia a destra
			if($xPlanned + $wPlanned > $xFinish - $xStart){
				$wPlanned = $xFinish - $xStart - $xPlanned;
				$plannedOutRight = true;
			}else{
				$plannedOutRight = false;
			}
				
			// costruzione del planned
			$plannedGifBox = new GifBox(
				$xPlanned, // x
				0, // y
				$wPlanned, // width
				$hPlanned // height
				);
			$plannedGifBox->setForeColor($cPlanned);
		}else{
			// se visible è falsa gli outRight e outLeft non sono settati!			
			$plannedVisible = false;			
		}
		
		// se l'actual task è visibile nella finestra
		if($this->actualStarted && 
			($xActual + $wActual > 0) && 
			($xActual < $xFinish - $xStart))
			{
			$actualVisible = true;	
			// controlli uscita dalla griglia a sinistra
			if($xActual < 0){
				$wActual += $xActual;
				$xActual = 0;
				$actualOutLeft = true;
			}else{
				$actualOutLeft = false;
			}
			
			// controlli uscita dalla griglia a destra
			if($xActual + $wActual > $xFinish - $xStart){
				$wActual = $xFinish - $xStart - $xActual;
				$actualOutRight = true;
			}else{
				$actualOutRight = false;
			}
			
			// TODO: riadattare percentuale
			// costruzione actual
			$actualGifProgressBar = new GifProgressBar(
				$xActual,
				$hPlanned,
				$wActual,
				intval($height/3),
				$this->td->getInfo()->getPercentage()
			);
		}else{
			// se visible è falsa gli outRight e outLeft non sono settati!
			$actualVisible = false;								
		}
		
		// costruzione triangoli se il task non è foglia ed è visibile
		if((!$isLeaf) && ($plannedVisible || $actualVisible)){
			// altezza triangoli
			$hTri = $height - $hPlanned;
			$leftVisible = true;
			$rightVisible = true;
			
			// sinistra
			$xLeft = ($trueXActual < $trueXPlanned) ? 
				$trueXActual : $trueXPlanned;
			if($xLeft < 0){
				$xLeft = null;
				$leftVisible = false;					
			}
			// destra
			$xRight = ($trueXActual + $trueWActual > $trueXPlanned + $trueWPlanned) ? 
				$trueXActual + $trueWActual : $trueXPlanned + $trueWPlanned;
			if($xRight > $xFinish - $xStart){
				$xRight = null;
				$rightVisible = false;
			}


			if($leftVisible){
				// genero triangolo sinistro
				$leftGifTriangle = new GifTriangle(
					$xLeft, $hPlanned, $this->wTri, $hTri, 'left');
				$leftGifTriangle->setForeColor($cPlanned);
			}
			if($rightVisible){
				// genero triangolo destro
				$rightGifTriangle = new GifTriangle(
					$xRight - $this->wTri, $hPlanned, $this->wTri, $hTri, 'right');
				$rightGifTriangle->setForeColor($cPlanned);
			}
		}
		
		// TODO: troncare riga
		// riga di collegamento actual-planned
		$this->xP = $xPlanned;
		$this->wP = $wPlanned;
		$this->hP = $hPlanned;
		$this->xA = $xActual;
		$this->wA = $wActual;
		
		// TODO: inserire se presenti in opzioni utente
		// label risorse
		//$this->subAreas['Resources'] = new GifLabel(,,);
		
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
	
	
	public function setVisiblesFromOptionsChoice($userOptionsChoice)
	{
		// TODO: not implemented yet
	}
	
	public function getFontsize()
	{
		// TODO: not implemented yet		
	}
	
	public function setFontSize($size)
	{
		// TODO: not implemented yet
	}
}

?>