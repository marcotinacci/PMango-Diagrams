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
	private $gif;
	
	private $numlevel;
	
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
	
	private function setNumLeaves($numleaves)
	{
		$this->numleaves=$numleaves;
	}
	private function getNumLeaves()
	{
		return $this->numleaves;
	}
	
	private function setNumLevel($numlevel)
	{
		$this->numlevel=$numlevel;
	}
	private function getNumLevel()
	{
		return $this->numlevel;
	}
	
	/**
	 * Funzione che crea i nodi delle WBS e li posiziona secondo
	 * la gerarchia richiesta
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSTaskNode(){
						
		$areas=array();
				
		$nodi=array();
		
		$tree = new TaskDataTreeGenerator();
		$treeData = $tree->generateTaskDataTree();
		
		//Imposta la larghezza del box
		$this->boxWidth=GifTaskBox::getTaskBoxesBestWidth($treeData,null,10,FF_VERDANA);
				
		
		//vettore di foglie dell'albero
		$leav=array();
		
		//array di nodi dell'albero
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
		$this->setNumLevel($CLiv);
		$larghezza=$this->getNumLeaves()*($this->boxWidth+50);
		
		
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
		
		/*
		 *  I seguenti cicli for scandiscono tutti i task data per scoprire
		 *  quello ad altezza maggiore, che verrà usato come modello per 
		 *  spaziare i box per livello 
		 */
		$max=0;
		for($i=$this->getNumLevel()-1;$i>=0;$i--)
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
		$height=($this->getNumLevel()+($this->getNumLevel()+1))*$max+50;
		
		$this->gif = new GifImage($this->getWidth(),$height); 
		
		//Spazio tra un livello ed un altro
		$alt=$height-2*$max;
		
		/*
		 *  matrice usata per memorizzare i task inseriti, in modo da poter 
		 *  essere collegati tra loro secondo la corretta gerarchia
		*/
		
		$Link=array(array());
		
		//Indice usato per il riempimento del vettore areas (contentente task node)
		$l=0;		
		
		//Il seguente blocco di codice esegue in un'unica passata il posizionamento
		//dei tasknode e salva gli stessi nella matrice Link
		for($i=$this->getNumLevel();$i>=0;$i--)
		{			
			for($j=0;$j<$this->getNumLeaves();$j++)
			{
				/*
				 *  Controlla il livello, se è lo zero (posto per la radice) allora
				 *  crea un nodo fittizio dallo stub (nodo del progetto) per posizionarlo 
				 *  a capo dei sottoalberi
				 */
				
				if($Livello==0)
				{
					
					$occorrenze = $this->getOccorrence($leav,$leav[$j]);
					//$cord1 = ((($occorrenze)*$dimBlocco)/2);
					//$cord2 = ((($j)*$dimBlocco));
					
					//Nodo radice creato dallo stub task 
					$t = new StubTask();
					$td = new TaskData($t);
					
					/*
					 *  Per posizionare il nodo radice prende le coordinate del primo e
					 *  dell'ultimo nodo di livello 1 e si calcola il punto di mezzo
					 */
					$cordinata1=$Link[1][0]->getX()+$this->boxWidth;
					$cordinata2=$Link[1][Count($Link[1])-1]->getX();
					$cordinata=$cordinata1+($cordinata2-$cordinata1)/2;
																		
					$areas[$l] = new GifTaskBox($this->gif,$cordinata-($this->boxWidth/2),$alt,$this->boxWidth,30,$td);
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
						//Verifica che il nodo puntato sia del livello corretto per il posizionamento
						if(($leav[$j]->getInfo()->getLevel())==$Livello)
						{		
							$occorrenze = $this->getOccorrence($leav,$leav[$j]);
							//Se vi è una solo occorrenza allora posiziona lo "scatolotto" esattamente sopra il figlio
							if($occorrenze == 1)
							{
								$areas[$l] = new GifTaskBox($this->gif,((($j+1)*$dimBlocco)-($dimBlocco/2))-($this->boxWidth/2),$alt,$this->boxWidth,30,$leav[$j]);
								$Link[$i][$j]=$areas[$l];
								$leav[$j]=$leav[$j]->getParent();
								$l++;
							}
							//Altrimenti se il padre ha più figli viene messo al centro
							else if($occorrenze > 1)
							{
																
								/*
					 			 *  Per posizionare il nodo padre prende le coordinate del suo primo 
					 			 *  figlio e dell'ultimo, e calcola il punto di mezzo
					             */
								
								$cord1=$Link[$i+1][$j]->getX()+$this->boxWidth;;
								$cord2=$Link[$i+1][$j+$occorrenze-1]->getX();
								
								$cordinata=$cord1+(($cord2-$cord1)/2);		
								$areas[$l] = new GifTaskBox($this->gif,($cordinata-($this->boxWidth/2)),$alt,$this->boxWidth,30,$leav[$j]);
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
		$this->makeWBSDependencies($Link,$areas,$height);
}
	
	/**
	 * Funzione che assegna le dipendenze ai nodi della WBS
	 * @see chartgenerator/ChartGenerator#generateChart()
	 */
	protected function makeWBSDependencies($Link,$areas,$height){
		
		$s = new LineStyle();
		$s->style = "solid";
		$s->weight = 2;
		$s->color = "black";
		

		$arrayDrawLine=array();
		$XToDraw=array();
		$YToDraw=array();
		
		for($i=0;$i<$this->getNumLevel()-1;$i++)
		{
			$arrayDrawLine=$Link[$i];
						
						
			for($j=0;$j<=$this->getNumLeaves()-1;$j++)
			{
				
				if(isset($arrayDrawLine[$j]) && $arrayDrawLine[$j]!=""){
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
							DrawingHelper::ExplodedUpRectangularLineFromTo($Link[$i][$j]->getX()+($this->boxWidth/2),$Link[$i][$j]->getY()+$hspace,$XToDraw,$YToDraw,$this->gif,$s);
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
							DrawingHelper::LineFromTo($Link[$i][$j]->getX()+($this->boxWidth/2),$Link[$i][$j]->getY()+$hspace,$Link[$i+1][$j]->getX()+($this->boxWidth/2),$Link[$i+1][$j]->getY(),$this->gif,$s);
						}	
					}
				}	
			}
			$arrayDrawLine=array();
		}
		
		
		foreach($areas as $a)
			$a->drawOn();
		$this->gif->draw();
		$this->gif->saveToFile("./WBSTree.gif");
		
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
		for($h=0; $h<$this->getNumLeaves(); $h++)
		{
			if(isset($array[$h]) && $array[$h]==$valore)
			{
				$contatore++;		
			}
		}
		return $contatore;
	}
}
?>