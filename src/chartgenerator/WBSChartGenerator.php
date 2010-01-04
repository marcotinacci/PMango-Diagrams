<?php

require_once dirname(__FILE__)."/../gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/../gifarea/DrawingHelper.php";
require_once dirname(__FILE__)."/./ChartGenerator.php";
require_once dirname(__FILE__)."/../useroptionschoice/UserOptionsChoice.php";

/**
 * Questa classe implementa il metodo di generazione delle WBS
 *
 * @author: Daniele Poggi
 * @version: 0.3
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
		//AL MOMENTO NON TIENE CONTO DELLE DIPENDENZE E QUINDI STAMPA I NODI SOLO PER LIVELLO
		
		$tdt = $this->$tdtGenerator->generateTaskDataTree($_SESSION["useroptionschoice"]);
		
		$gif = new GifImage(800,800);
		$areas=array();
		
		/*
		$nodi = array();
		$nodi=$tdt->deepVisit();
		for($i=0;$i<Count($nodi);$i++)
		{
			 $livello[$i]=$nodi[$i]->getLevel();
		}		
		*/

		// Questa parte serve per avere un input utile a visualizzare qualcosa,
		// in particolar modo per la visualizzazione su livelli.
		$height=800;
		$levelMax=4;

		$nodi=array('A','B','C','D','E','F','G','H','I',"L");
		$livello=array('1','2','2','3','3','3','4','4','4','4');
		
		$nliv=array();
		for($i=0;$i<Count($livello);$i++)
		{
			$nlive[$livello[$i]-1]++;	
		}	
		
		$lev=1;
		$h=1;
		$alt=50;
		///////////FINE SEZIONE TEST/////////////////////////////////////////////
		
		/*
		 * Questo ciclo for al momento si occupa di stampare per livelli i TaskNode.
		 * Appena sarà possibile ottenere le dipendenze verrà ampliato per stampare correttamente
		 * i blocchi 
		 */
		
		for($i=0;$i<Count($nodi);$i++)
		{	
			if($livello[$i]==$lev)
			{
				$areas[] = new GifTaskBox(($h*$height)/($nlive[($livello[$i]-1)]+1)-75,$alt,150,100,null);	
				$h++;		
			}
			else
			{
				$alt+=150;
				$lev++;
				$h=1;
				$areas[] = new GifTaskBox($h*$height/($nlive[($livello[$i]-1)]+1)-75,$alt,150,100,null);
				$h++;
			}	
		}		
		foreach($areas as $a)
			$a->drawOn($gif);
	
		$gif->draw();
		$gif->saveToFile("./provagrossa.gif");
	}
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies(){
			
	}
}

?>