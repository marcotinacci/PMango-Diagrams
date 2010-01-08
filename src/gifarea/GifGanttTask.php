<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
// require_once dirname(__FILE__)."/GifTriangle.php";


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
	
	/**
	 * Costruttore
	 * @param xStart: x iniziale
	 * @param xFinish: x finale
	 * @param y: quota
	 * @param height: altezza
	 * @param startDate: data inizale della finestra
	 * @param finishDate: data finale della finestra
	 * @param task: informazioni da rappresentare (Task)
	 * @param uoc: opzioni utente (UserOptionsChoice)
	 */
	function __construct($xStart, $xFinish, $y, $height, $startDate, 
		$finishDate, $taskData, $uoc)
	{
		parent::__construct($xStart, $y, $xFinish - $xStart, $height);

		// dati finestra
		$windowWidth = $xFinish - $xStart;
		$startTS = $this->toTimeStamp($startDate);
		$finishTS = $this->toTimeStamp($finishDate);
		$windowDuration = $finishTS - $startTS;
		
		// dati task
		$this->td = $taskData;
		$planned = $this->td->getInfo()->getPlannedTimeFrame();
		$xPlanned = $windowWidth * ($this->toTimeStamp(
			$planned['start_date']) - $startTS) 
			/ $windowDuration;
		$wPlanned = ($windowWidth * ($this->toTimeStamp(
			$planned['finish_date']) - $startTS) 
			/ $windowDuration) - $xPlanned;
		$actual = $this->td->getInfo()->getActualTimeFrame();
		$xActual = $windowWidth * ($this->toTimeStamp(
			$actual['start_date']) - $startTS) 
			/ $windowDuration;
		$wActual = ($windowWidth * ($this->toTimeStamp(
			$actual['finish_date']) - $startTS) 
			/ $windowDuration) - $xActual;
		
		// caso foglia e caso nodo interno
		if(sizeOf($this->td->getChildren()) == 0){
			// se il task è una foglia
			$hPlanned = 2*$height/3;
			$cPlanned = 'white';
		}else{
			// se il task ha figli
			$hPlanned = $height/3;
			$cPlanned = 'black';
//			$this->subAreas['leftTriangle'] = new GifBox();
//			$this->subAreas['rightTriangle'] = new GifBox();
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

	/**
	 * la funzione converte dal formato mySQL datetime
	 * al formato unix timestamp
	 * @param str
	 * la data (YYYY-MM-DD HH:MM:SS) da convertire in timestamp
	 */
	private function toTimeStamp($str) {
		list($date, $time) = explode(' ', $str);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		return $timestamp;
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