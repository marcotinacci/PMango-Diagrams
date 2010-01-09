<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifTriangle.php";
require_once dirname(__FILE__)."/../utils/TimeUtils.php";

// TODO: gestire caso task molto stretto coi triangoli


/**
 * Questa classe implementa la generazione grafica del task nel diagramma Gantt
 *
 * @author: Marco Tinacci
 * @version: 0.1
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
		$startTS = toTimeStamp($startDate);
		$finishTS = toTimeStamp($finishDate);
		$windowDuration = $finishTS - $startTS;
		
		// dai odierni
		$todayTS = toTimeStamp($today);
				
		// dati task planned
		$planned = $this->td->getInfo()->getPlannedTimeFrame();
		$startPlannedTS = toTimeStamp($planned['start_date']);
		$finishPlannedTS = toTimeStamp($planned['finish_date']);
		
		// coordinate planned
		$xPlanned = $windowWidth * ($startPlannedTS-$startTS) / $windowDuration;
		$wPlanned = ($windowWidth * ($finishPlannedTS-$startTS) 
			/ $windowDuration) - $xPlanned;
		
		// dati task actual (possono non esserci)
		$actual = $this->td->getInfo()->getActualTimeFrame();
		if($actual['start_date'] == null){
			$startActualTS = null;
			$finishActualTS = null;
			// coordinate actual
			$xActual = null;
			$wActual = null;			
		}else if($actual['finish_date'] == null){
			$startActualTS = toTimeStamp($actual['start_date']);
			$finishActualTS = null;
			// coordinate actual			
			$xActual = $windowWidth * ($startActualTS-$startTS) / $windowDuration;
			$wActual = ($windowWidth * ($todayTS-$startTS) 
				/ $windowDuration) - $xActual;			
		}else{
			$startActualTS = toTimeStamp($actual['start_date']);
			$finishActualTS = toTimeStamp($actual['finish_date']);
			// coordinate actual			
			$xActual = $windowWidth * ($startActualTS-$startTS) / $windowDuration;
			$wActual = ($windowWidth * ($finishActualTS-$startTS) 
				/ $windowDuration) - $xActual;			
		}
		
		// caso foglia e caso nodo interno
		if(sizeOf($this->td->getChildren()) == 0){
			// se il task è una foglia
			$hPlanned = intval(2*$height/3);
			$hActual = $height - $hPlanned;
			$cPlanned = 'white';
		}else{
			// se il task ha figli
			$hPlanned = intval($height/3);
			$cPlanned = 'black';
			$hActual = $hPlanned;
			$hTri = $height - $hPlanned;
		
			// cerco inizio minore tra planned e actual
			$xMin = $xActual == null ? 
				$xPlanned : ($xActual < $xPlanned ? $xActual : $xPlanned);
			// cerco fine maggiore tra planned e actual							
			$xMax = $xActual + $wActual == null ? 
				$xPlanned + $wPlanned : 
				($xActual + $wActual > $xPlanned + $wPlanned ? 
				$xActual + $wActual : $xPlanned + $wPlanned);
			
			/* DEBUG
			echo "x actual: $xActual <br>";
			echo "width actual: $wActual <br>";
			echo "x max: $xMax <br>";
			echo "x + w: ".($xActual+$wActual)."<br><br>";
			*/
			
			// genero triangoli
			$this->subAreas['leftTriangle'] = new GifTriangle(
				$xMin, $hPlanned, $this->wTri, $hTri, 'left');
			$this->subAreas['leftTriangle']->setForeColor($cPlanned);
						
			$this->subAreas['rightTriangle'] = new GifTriangle(
				$xMax-$this->wTri, $hPlanned, $this->wTri, $hTri, 'right');
			$this->subAreas['rightTriangle']->setForeColor($cPlanned);
		}

		// costruzione del planned
		
		$this->subAreas['Planned'] = new GifBox(
			$xPlanned, //< $xStart ? 0 : $xPlanned, // x
			0, // y
			$wPlanned, // width
			$hPlanned // height
			);
		$this->subAreas['Planned']->setForeColor($cPlanned);

		$this->subAreas['ActualProgress'] = new GifProgressBar(
			$xActual,
			$hPlanned,
			$wActual,
			$height/3,
			$this->td->getInfo()->getPercentage()
			);
//		$this->subAreas['Resources'] = new GifLabel();

		
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