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
	private $task;
	
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
		$finishDate, $task, $uoc)
	{
		parent::__construct($xStart, $y, $xFinish - $xStart, $height);
		$windowWidth = $xFinish - $xStart;
		$startTS = toTimeStamp($startDate);
		$finishTS = toTimeStamp($finishDate);
		$windowDuration = $finishTS - $startTS;
		$xPlanned = $windowWidth * (toTimeStamp(
			$task->getPlannedTimeFrame()['start_date']) - $startTS) 
			/ $windowDuration;
		$wPlanned = ($windowWidth * (toTimeStamp(
			$task->getPlannedTimeFrame()['finish_date']) - $startTS) 
			/ $windowDuration) - $xPlanned;
 	// Sotto aree
		$this->subAreas['Planned'] = new GifBox(
			$xPlanned, // x
			0, // y
			$wPlanned, // width
			$height // height
			);
		$this->subAreas['ActualProgress'] = new GifProgressBar();
		$this->subAreas['Resources'] = new GifLabel();
 	// TODO: wait for triangle generator
		$this->subAreas['leftTriangle'] = new GifBox();
		$this->subAreas['rightTriangle'] = new GifBox();
		
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