<?php

require_once dirname(__FILE__)."/../gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/../gifarea/DrawingHelper.php";
require_once dirname(__FILE__)."/./ChartGenerator.php";
require_once dirname(__FILE__)."/../useroptionschoice/UserOptionsChoice.php";

/**
 * Questa classe implementa il metodo di generazione delle WBS
 *
 * @author: Daniele Poggi
 * @version: 0.8
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Kiwi Team
 */

class WBSChartGenerator extends ChartGenerator{
	
	private  $width=800;	
	
	/**
	 * Funzione di generazione grafica delle WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function __construct()
	{
		
		//Costruttore di default con risoluzione di default
		$this->setWidth(800);
			
	}
	
	
	public function generateChart()
	{
	
		
		
		$this->makeWBSTaskNode();		
}
	
	/**
	 * Funzione che crea i nodi delle WBS e li posiziona secondo
	 * la gerarchia richiesta
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSTaskNode(){
		
		$areas=array();
		
		$tdt=new StubTask();
		$taskData=new TaskData();
		$taskData->setInfo($tdt);
		
		$nodi=array();
		
		$tree=new TaskDataTreeGenerator();
		$treeData=$tree->stubGenerateTaskDataTree();
		
		//vettore di foglie dell'albero
		$leav=array();//('D','H','F','I');
		
		$nodi=array();
		
		//Il vettore $nodi viene riempito con tutti i nodi del tree presi con una visita in profondità
		$nodi=$treeData->deepVisit();
		//Il vettore $leav viene riempito con tutte le foglie del tree
		$leav = $treeData->getLeaves();
		
		
		//Viene contato e memorizzato il numero di foglie presenti
		$numleaves=Count($leav);
		
		$CLiv=2;
		
		foreach($nodi as $n){
			if($CLiv < $n->getInfo()->getLevel())
				$CLiv = $n->getInfo()->getLevel();
		}
		$Livello=$CLiv;
		
		//Altezza della pagina, calcolata dinamicamente	
		$height=($CLiv+1)*250;

		//Spazio tra un livello ed un altro
		$alt=$height-220;

		$dimBlocco=$this->getWidth()/$numleaves;
		
		$LinkX=array(array());
		$LinkY=array(array());
		
		$cord1=0;
		$cord2=0;
		
		$occorrenze=1;
		
		for($i=$CLiv;$i>=0;$i--)
		{
			//IL SECONDO FOR INVECE DEVE SCANDIRE IL VETTORE DELLE FOGLIE (COLONNE)
			for($j=0;$j<$numleaves;$j++)
			{
				if($leav[$j]!=null)
				{
					if(($leav[$j]->getInfo()->getLevel())==$Livello)
					{					
						$occorrenze = $this->getOccorrence($leav,$leav[$j]);
						if($occorrenze == 1)
						{
							$areas[] = new GifTaskBox(((($j+1)*$dimBlocco)-($dimBlocco/2))-75,$alt,150,30,$taskData);
							$leav[$j]=$leav[$j]->getParent();
							$LinkX[$i][$j]=((($j+1)*$dimBlocco)-($dimBlocco/2))-75;
							$LinkY[$i][$j]=$alt;
						}
						else if($occorrenze > 1)
						{
							$cord1= ((($occorrenze)*$dimBlocco)/2);
							$cord2= ((($j)*$dimBlocco));					
							$areas[] = new GifTaskBox((($cord2+$cord1)-75),$alt,150,30,$taskData);
						
							for($k=0;$k<$occorrenze;$k++)
							{
								$leav[$j+$k]=$leav[$j+$k]->getParent();
								$LinkX[$i][$j+$k]=(($cord2+$cord1)-75);
								$LinkY[$i][$j+$k]=$alt;
							}
							$j+=$occorrenze-1;
						}
					}				
				}		
		}
		$Livello--;
		$alt-=250;
	}
	$this->makeWBSDependencies($LinkX,$LinkY,$CLiv,$numleaves,$areas,$height);
}
	
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies($LinkX,$LinkY,$CLiv,$numleaves,$areas,$height){
		$s = new LineStyle();
		$s->style = "longdashed";
		$s->weight = 2;
		$s->color = "black";
		
		$gif = new GifImage($this->getWidth(),$height);

		for($i=0;$i<$CLiv;$i++)
		{
			//IL SECONDO FOR INVECE DEVE SCANDIRE IL VETTORE DELLE FOGLIE (COLONNE)
			for($j=0;$j<$numleaves;$j++)
			{
				if($LinkX[$i+1][$j]!=null)
				{
					//MODIFICARE IL LINEFROMTO
					DrawingHelper::LineFromTo($LinkX[$i][$j]+75,$LinkY[$i][$j]+200,$LinkX[$i+1][$j]+75,$LinkY[$i+1][$j],$gif,$s);
				}
			}
		}

		foreach($areas as $a)
			$a->drawOn($gif);
		
		$gif->draw();
		$gif->saveToFile("./WBSTree.gif");
		
	}
	
	public function setWidth($width)
	{
		$this->width=$width;	
	}
	public function getWidth()
	{
		return $this->width;		
	}
	
	/*
 	* Funzione che dato in input un criterio di ricerca trova il numero delle
 	* occorrenze in un array  
 	*/
	protected function getOccorrence($array,$valore)
	{
		$contatore=0;
		for($i=0;$i<Count($array);$i++)
		{
			if($array[$i]==$valore)
			{
				$contatore++;		
			}
		}
		return $contatore;
	}
	
	
}

?>