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
	private $numleaves;
	
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
	
	public function setNumLeaves($numleaves)
	{
		$this->numleaves=$numleaves;
	}
	public function getNumLeaves()
	{
		return $this->numleaves;
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
		$this->setNumLeaves(Count($leav));
		
		$CLiv=-1;

		//CLiv contiene il numero del livello maggiore
		foreach($nodi as $n){
			if($CLiv < $n->getInfo()->getLevel())
				$CLiv = $n->getInfo()->getLevel();
		}
		
		$CLiv+=1;
		$Livello=$CLiv;
				
		$larghezza=$this->getNumLeaves()*$this->boxWidth+100;
		
		
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
				
		$dimBlocco=$this->getWidth()/$this->getNumLeaves();
		
		
		$cord1=0;
		$cord2=0;
		
		$occorrenze=1;
		
		//I seguenti cicli for scandiscono tutti i task data per scoprire 
		//quello ad altezza maggiore
		for($i=$CLiv-1;$i>=0;$i--)
		{			
			for($j=0;$j<$this->getNumLeaves();$j++)
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
			for($j=0;$j<$this->getNumLeaves();$j++)
			{
				if($Livello==0)
				{
					$occorrenze = $this->getOccorrence($leav,$leav[$j]);
					$cord1 = ((($occorrenze)*$dimBlocco)/2);
					$cord2 = ((($j)*$dimBlocco));
						
					$t = new StubTask();
					$td = new TaskData($t);
					
					$cordinata1=$Link[1][0]->getX()+$this->boxWidth;
					$cordinata2=$Link[1][Count($Link[1])-1]->getX();
					$cordinata=$cordinata1+($cordinata2-$cordinata1)/2;
																		
					$areas[$l] = new GifTaskBox($cordinata-($this->boxWidth/2),$alt,$this->boxWidth,30,$td);
					for($k = 0;$k < $occorrenze;$k++)
					{
						$leav[$j+$k]=$leav[$j+$k]->getParent();
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
							$occorrenze = $this->getOccorrence($leav,$leav[$j],$this->getNumLeaves);
							//Se vi è una solo occorrenza allora posiziona lo scatolotto esattamente sopra il figlio
							if($occorrenze == 1)
							{
								$areas[$l] = new GifTaskBox(((($j+1)*$dimBlocco)-($dimBlocco/2))-($this->boxWidth/2),$alt,$this->boxWidth,30,$leav[$j]);
								$Link[$i][$j]=$areas[$l];
								$leav[$j]=$leav[$j]->getParent();
								$l++;
							}
							//Altrimenti se il padre ha più figli viene messo al centro
							else if($occorrenze > 1)
							{
								//prende i figli del padre
								//cerca occorrenze del primo, cerca occorrenze fino all'ennesimo figlio
								$cord1=$Link[$i+1][$j]->getX()+$this->boxWidth;;
								$cord2=$Link[$i+1][$j+$occorrenze-1]->getX();
								
								$cordinata=$cord1+(($cord2-$cord1)/2);		
								$areas[$l] = new GifTaskBox(($cordinata-($this->boxWidth/2)),$alt,$this->boxWidth,30,$leav[$j]);
								for($k=0;$k<$occorrenze;$k++)
								{
									$leav[$j+$k]=$leav[$j+$k]->getParent();
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
		$this->makeWBSDependencies($Link,$CLiv,$areas,$height);
}
	
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies($Link,$CLiv,$areas,$height){
		
		$s = new LineStyle();
		$s->style = "solid";
		$s->weight = 2;
		$s->color = "black";
		
		$gif = new GifImage($this->getWidth(),$height);

		$arrayDrawLine=array();
		$XToDraw=array();
		$YToDraw=array();
		
		
		for($i=0;$i<$CLiv-1;$i++)
		{
			$arrayDrawLine=$Link[$i];
						
						
			for($j=0;$j<=$this->getNumLeaves()-1;$j++)
			{
				
				if($arrayDrawLine[$j]!=""){
					$occorrenze=$this->getOccorrence($arrayDrawLine,$arrayDrawLine[$j]);
						
					if($occorrenze>1)
					{
						$XToDraw=array();
						$YToDraw=array();
					
						for($k=0;$k<$occorrenze;$k++)
						{
							if(isset($Link[$i+1][$j+$k]))
							{
								$XToDraw[$k]=$Link[$i+1][$j+$k]->getX()+($this->boxWidth/2);
								$YToDraw[$k]=$Link[$i+1][$j+$k]->getY();
							}
						}	
						if(isset($Link[$i][$j])){
							$hspace=$Link[$i][$j]->getEffectiveHeight();	
						}
						//echo Count($XToDraw);			
						//if($LinkX[$i+1][$j]!=null){
							DrawingHelper::ExplodedUpRectangularLineFromTo($Link[$i][$j]->getX()+($this->boxWidth/2),$Link[$i][$j]->getY()+$hspace,$XToDraw,$YToDraw,$gif,$s);
						//}
						//echo "<br>";	
					
						$j+=$occorrenze-1;	
					
					}
				
					else if($occorrenze==1)
					{					
						if(isset($Link[$i][$j])){
							$hspace=$Link[$i][$j]->getEffectiveHeight();
						}					
						if(isset($Link[$i+1][$j])){
							DrawingHelper::LineFromTo($Link[$i][$j]->getX()+($this->boxWidth/2),$Link[$i][$j]->getY()+$hspace,$Link[$i+1][$j]->getX()+($this->boxWidth/2),$Link[$i+1][$j]->getY(),$gif,$s);
						}	
					}
				}	
			}
			$arrayDrawLine=array();
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
		for($h=0;$h<$this->getNumLeaves();$h++)
		{
			if($array[$h]==$valore)
			{
				$contatore++;		
			}
		}
		return $contatore;
	}
}
?>