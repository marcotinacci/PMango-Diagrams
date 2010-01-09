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
	private $boxWidth;
	
	/**
	 * Funzione di generazione grafica delle WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function __construct($width)
	{
		//Costruttore di default con risoluzione di default
		$this->setWidth($width);
				
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
		
		$this->boxWidth=GifTaskBox::getTaskBoxesBestWidth($treeData,null,10,FF_VERDANA);
				
		//vettore di foglie dell'albero
		$leav=array();
		
		$nodi=array();
		
		//Il vettore $nodi viene riempito con tutti i nodi del tree presi con una visita in profondità
		$nodi=$treeData->deepVisit();
		//Il vettore $leav viene riempito con tutte le foglie del tree
		$leav = $treeData->getLeaves();
				
		//Viene contato e memorizzato il numero di foglie presenti
		$numleaves=Count($leav);
		
		$CLiv=2;

		//CLiv contiene il numero del livello maggiore
		foreach($nodi as $n){
			if($CLiv < $n->getInfo()->getLevel())
				$CLiv = $n->getInfo()->getLevel();
		}
		
		$Livello=$CLiv;
		
		$dimBlocco=$this->getWidth()/$numleaves;
		
		//Le seguenti matrici vengono usate per stampare le coordinate per le linee
		//di collegamento dei box
		$LinkX=array(array());
		$LinkY=array(array());
		
		$cord1=0;
		$cord2=0;
		
		$occorrenze=1;
		
		//I seguenti cicli for scandiscono tutti i task data per scoprire 
		//quello ad altezza maggiore
		for($i=$CLiv;$i>=0;$i--)
		{			
			for($j=0;$j<$numleaves;$j++)
			{
				//CICLI FOR CHE SCANDISCONO I TASKDATA PER TROVARNE QUELLO PIU ALTO
				if ($max < GifTaskBox::getEffectiveHeightOfTaskBox($taskData,30,null))
				{
					$max=GifTaskBox::getEffectiveHeightOfTaskBox($taskData,30,null);	
				}				
			}
		}
			
		//Altezza della pagina, calcolata dinamicamente	
		$height=($CLiv+1)*$max+150;
		//Spazio tra un livello ed un altro
		$alt=$height-220;
		
		$NodeForHeight=array(array());
		
		//Il seguente blocco di codice esegue in un'unica passata il posizionamento
		//dei tasknode e salva nelle due matrici LinkX e LinkY le coordinate
		for($i=$CLiv;$i>=0;$i--)
		{			
			for($j=0;$j<$numleaves;$j++)
			{
				if($leav[$j]!=null)
				{
					if(($leav[$j]->getInfo()->getLevel())==$Livello)
					{					
						$occorrenze = $this->getOccorrence($leav,$leav[$j]);
						//Se vi è una solo occorrenza allora posiziona lo scatolotto esattamente sopra il figlio
						if($occorrenze == 1)
						{
							$areas[] = new GifTaskBox(((($j+1)*$dimBlocco)-($dimBlocco/2))-($this->boxWidth/2),$alt,$this->boxWidth,30,$taskData);
							$leav[$j]=$leav[$j]->getParent();
							$LinkX[$i][$j]=((($j+1)*$dimBlocco)-($dimBlocco/2))-($this->boxWidth/2);
							$LinkY[$i][$j]=$alt;
							
						}
						//Altrimenti se il padre ha più figli viene messo al centro
						else if($occorrenze > 1)
						{
							$cord1 = ((($occorrenze)*$dimBlocco)/2);
							$cord2 = ((($j)*$dimBlocco));					
							$areas[] = new GifTaskBox((($cord2+$cord1)-($this->boxWidth/2)),$alt,$this->boxWidth,30,$taskData);
						
							for($k=0;$k<$occorrenze;$k++)
							{
								$leav[$j+$k]=$leav[$j+$k]->getParent();
								$LinkX[$i][$j+$k]=(($cord2+$cord1)-($this->boxWidth/2));
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
	//Viene richiamata la funzione che stampa le linee di dipendenza dei box
	$this->makeWBSDependencies($LinkX,$LinkY,$CLiv,$numleaves,$areas,$height,$taskData);
}
	
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies($LinkX,$LinkY,$CLiv,$numleaves,$areas,$height,$taskData){
		
		$s = new LineStyle();
		$s->style = "solid";
		$s->weight = 2;
		$s->color = "black";
		
		$gif = new GifImage($this->getWidth(),$height);

		$arrayDrawLine=array();
		$XToDraw=array();
		$YToDraw=array();
		
		for($i=0;$i<$CLiv;$i++)
		{
			$arrayDrawLine=$LinkX[$i];
			for($j=0;$j<$numleaves;$j++)
			{
				$occorrenze=$this->getOccorrence($arrayDrawLine,$arrayDrawLine[$j]);
				
				for($k=0;$k<$occorrenze;$k++)
				{
					if($LinkX[$i+1][$j+$k]!="")
					{
					$XToDraw[$k]=$LinkX[$i+1][$j+$k]+($this->boxWidth)/2;
					$YToDraw[$k]=$LinkY[$i+1][$j+$k];
					}
				}	
				$j+=$occorrenze-1;	
				$hspace=GifTaskBox::getEffectiveHeightOfTaskBox($taskData,30,null);
				DrawingHelper::ExplodedUpRectangularLineFromTo($LinkX[$i][$j]+($this->boxWidth/2),$LinkY[$i][$j]+$hspace,$XToDraw,$YToDraw,$gif,$s);
				
				$XToDraw=array();
				$YToDraw=array();
					
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