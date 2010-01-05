<?php

require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/GifArea.php";
require_once dirname(__FILE__)."/GifBox.php";
require_once dirname(__FILE__)."/GifLabel.php";
require_once dirname(__FILE__)."/GifProgressBar.php";
require_once dirname(__FILE__)."/GifTriangle.php";

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
	
	function __construct($xStart, $xFinish, $y, $height, $startDate, 
		$finishDate, $windowWidth, $task, $useroptionchoise = null)
	{
		parent::__construct($xStart, $y, $xFinish - $xStart, $height);
		
		// Sotto aree
		$this->subAreas['ActualProgress'] = new GifProgressBar();
		$this->subAreas['Planned'] = new GifBox();
		$this->subAreas['Resources'] = new GifLabel();
		// TODO: wait for triangle generator
		//$this->subAreas['leftTriangle'] = new GifTriangle();
		//$this->subAreas['rightTriangle'] = new GifTriangle();
		
		
		
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