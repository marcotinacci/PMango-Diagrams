<?php

require_once "../gifarea/GifImage.php";
require_once "../gifarea/GifTaskBox.php";
require_once "../gifarea/DrawingHelper.php";

/**
 * Questa classe implementa il metodo di generazione delle WBS
 *
 * @author: Daniele Poggi
 * @version: 0.1
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class WBSChartGenerator extends ChartGenerator{
	
	/**
	 * Funzione di generazione grafica delle WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function generateChart(){
		
		//$tdt = $this->$tdtGenerator->generateTaskDataTree($_SESSION["useroptionschoice"]);
		$this->makeWBSTaskNode();
		//$this->makeWBSDependencies();
		//$chart->draw();
		
	}
	/**
	 * Funzione che crea i nodi delle WBS e li posiziona secondo
	 * la gerarchia richiesta
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function makeWBSTaskNode(){

		$gif = new GifImage(800,550);

$areas=array();

$areas[] = new GifTaskBox(300,50,100,100,null);

$areas[] = new GifTaskBox(0,350,100,100,null);
$areas[] = new GifTaskBox(200,350,100,100,null);
$areas[] = new GifTaskBox(400,350,100,100,null);
$areas[] = new GifTaskBox(600,350,100,100,null);

$xs[]=50; $ys[]=350;
$xs[]=250; $ys[]=350;
$xs[]=450; $ys[]=350;
$xs[]=650; $ys[]=350;

$s = new LineStyle();
$s->style = "longdashed";
$s->weight = 2;
$s->color = "black";

//DrawingHelper::ExplodedLineFromTo(350,100,$xs,$ys,$gif);
DrawingHelper::ExplodedUpRectangularLineFromTo(350,150,$xs,$ys,$gif,$s);

foreach($areas as $a)
	$a->drawOn($gif);
	
DrawingHelper::drawArrow(50,350,30,30,0,$gif);

$gif->draw();
$gif->saveToFile("./prova.gif");
	}
	
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies(){
		
	}
}
$prova = new WBSChartGenerator();
$prova->makeWBSTaskNode();


?>