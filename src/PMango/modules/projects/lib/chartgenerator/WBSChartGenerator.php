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
	
	private  $width=1200;	
	private $boxWidth;
	
	/**
	 * Funzione di generazione grafica delle WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	public function __construct()
	{
				
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
		//$UOC = new UserOptionsChoice();
				
		$areas=array();
		
		$tdt=new StubTask();
		$taskData=new TaskData();
		$taskData->setInfo($tdt);
		
		$nodi=array();
		
		$tree = new TaskDataTreeGenerator();
		$treeData = $tree->generateTaskDataTree();
		
		$this->boxWidth=GifTaskBox::getTaskBoxesBestWidth($treeData,null,10,FF_VERDANA);
				
		//vettore di foglie dell'albero
		$leav=array();
		
		$nodi=array();
		
		//Il vettore $nodi viene riempito con tutti i nodi del tree presi con una visita in profondità
		$nodi=$treeData->getVisibleTree()->deepVisit();
		//Il vettore $leav viene riempito con tutte le foglie del tree
		$leav = $treeData->getLeaves();
				
		//Viene contato e memorizzato il numero di foglie presenti
		$numleaves=Count($leav);
		
		$CLiv=-1;

		//CLiv contiene il numero del livello maggiore
		foreach($nodi as $n){
			if($CLiv < $n->getInfo()->getLevel())
				$CLiv = $n->getInfo()->getLevel();
		}
		
		$CLiv+=1;
		$Livello=$CLiv;
				
		$larghezza=$numleaves*$this->boxWidth+100;
		//$this->setWidth($larghezza);
		
		//Configura la dimensioni dell'immagine, secondo le scelte dell'utente
		$UOC=UserOptionsChoice::GetInstance();
		$dimension=$UOC->getImageDimensionUserOption();
		if($dimension==ImageDimension::$CustomDimUserOption)
		{
			$dim=$UOC->getCustomDimValues();
			$this->setWidth($dim['width']);
		}
		if($dimension==ImageDimension::$OptimalDimUserOption)
		{
			$this->setWidth($larghezza);
		}	
		if($dimension==ImageDimension::$DefaultDimUserOption)
		{
			$this->setWidth(800);
		}	
		if($dimension==ImageDimension::$FitInWindowDimUserOption)
		{
			$this->setWidth($_GET[UserOptionEnumeration::$FitInWindowWidthUserOption]);	
		}
				
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
		for($i=$CLiv-1;$i>=0;$i--)
		{			
			for($j=0;$j<$numleaves;$j++)
			{
				if ($max < GifTaskBox::getEffectiveHeightOfTaskBox($nodi[$j],30,null))
				{
					$max=GifTaskBox::getEffectiveHeightOfTaskBox($nodi[$j],30,null);	
				}				
			}
		}
		
		//Altezza della pagina, calcolata dinamicamente	
		$height=($CLiv+($CLiv+1))*$max+50;
		//Spazio tra un livello ed un altro
		$alt=$height-2*$max;
		
		$Link=array(array());
		$l=0;		//Indice per vettore areas
		
		//Il seguente blocco di codice esegue in un'unica passata il posizionamento
		//dei tasknode e salva nelle due matrici LinkX e LinkY le coordinate
		for($i=$CLiv;$i>=0;$i--)
		{			
			for($j=0;$j<$numleaves;$j++)
			{
				if($Livello==0)
				{
					$occorrenze = $this->getOccorrence($leav,$leav[$j]);
					$cord1 = ((($occorrenze)*$dimBlocco)/2);
					$cord2 = ((($j)*$dimBlocco));
						
					$t = new StubTask();
					$td = new TaskData($t);
					
					$cordinata1=$LinkX[1][0]+$this->boxWidth;
					$cordinata2=$LinkX[1][Count($LinkX[1])-1];
					$cordinata=$cordinata1+($cordinata2-$cordinata1)/2;
																		
					$areas[$l] = new GifTaskBox($cordinata-($this->boxWidth/2),$alt,$this->boxWidth,30,$td);
					for($k = 0;$k < $occorrenze;$k++)
					{
						$leav[$j+$k]=$leav[$j+$k]->getParent();
						
						$LinkX[$i][$j+$k]=$cordinata-($this->boxWidth/2);
						$LinkY[$i][$j+$k]=$alt;
						$Link[$i][$j+$k]=$areas[$l];								
					}	
					$l++;
					$j+=$occorrenze-1;
				}
				else
				{
					if($leav[$j]!=null)
					{
						if(($leav[$j]->getInfo()->getLevel())==$Livello)
						{		
							$occorrenze = $this->getOccorrence($leav,$leav[$j]);
							//Se vi è una solo occorrenza allora posiziona lo scatolotto esattamente sopra il figlio
							if($occorrenze == 1)
							{
								$areas[$l] = new GifTaskBox(((($j+1)*$dimBlocco)-($dimBlocco/2))-($this->boxWidth/2),$alt,$this->boxWidth,30,$leav[$j]);
								$Link[$i][$j]=$areas[$l];
								$leav[$j]=$leav[$j]->getParent();
								$LinkX[$i][$j]=((($j+1)*$dimBlocco)-($dimBlocco/2))-($this->boxWidth/2);
								$LinkY[$i][$j]=$alt;
								$l++;
							}
							//Altrimenti se il padre ha più figli viene messo al centro
							else if($occorrenze > 1)
							{
								//prende i figli del padre
								//cerca occorrenze del primo, cerca occorrenze fino all'ennesimo figlio
								$cord1=$LinkX[$i+1][$j]+$this->boxWidth;;
								$cord2=$LinkX[$i+1][$j+$occorrenze-1];
								
								$cordinata=$cord1+(($cord2-$cord1)/2);		
								$areas[$l] = new GifTaskBox(($cordinata-($this->boxWidth/2)),$alt,$this->boxWidth,30,$leav[$j]);
								for($k=0;$k<$occorrenze;$k++)
								{
									$leav[$j+$k]=$leav[$j+$k]->getParent();
									$LinkX[$i][$j+$k]=$cordinata-($this->boxWidth/2);
									$LinkY[$i][$j+$k]=$alt;
									$Link[$i][$j+$k]=$areas[$l];								
								}
								$l++;
								$j+=$occorrenze-1;
							}						
						}				
					}
				}		
		}
		$Livello--;
		$alt-=$max + $max/2;
	}
	
	//Viene richiamata la funzione che stampa le linee di dipendenza dei box
	if(Count($areas)>0)
		$this->makeWBSDependencies($LinkX,$LinkY,$Link,$CLiv,$numleaves,$areas,$height);
}
	
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies($LinkX,$LinkY,$Link,$CLiv,$numleaves,$areas,$height){
		
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
				if($occorrenze>1)
				{
					for($k=0;$k<$occorrenze;$k++)
					{
						if($LinkX[$i+1][$j+$k]!="")
						{
						$XToDraw[$k]=$LinkX[$i+1][$j+$k]+($this->boxWidth)/2;
						$YToDraw[$k]=$LinkY[$i+1][$j+$k];
						}
					}	
					$j+=$occorrenze-1;
					if(isset($Link[$i][$j])){
						$hspace=$Link[$i][$j]->getEffectiveHeight();	
					}			
					if($LinkX[$i+1][$j]!=null)
						DrawingHelper::ExplodedUpRectangularLineFromTo($LinkX[$i][$j]+($this->boxWidth/2),$LinkY[$i][$j]+$hspace,$XToDraw,$YToDraw,$gif,$s);
					
					$XToDraw=array();
					$YToDraw=array();
				}
				else if($occorrenze==1)
				{					
					if(isset($Link[$i][$j])){
						$hspace=$Link[$i][$j]->getEffectiveHeight();
					}					
					if($LinkX[$i+1][$j]!=null)
					DrawingHelper::LineFromTo($LinkX[$i][$j]+($this->boxWidth/2),$LinkY[$i][$j]+$hspace,$LinkX[$i+1][$j]+($this->boxWidth/2),$LinkY[$i+1][$j],$gif,$s);	
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