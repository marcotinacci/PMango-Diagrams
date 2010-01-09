<?php

require_once dirname(__FILE__)."/../gifarea/GifImage.php";
require_once dirname(__FILE__)."/../gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/../gifarea/DrawingHelper.php";
require_once dirname(__FILE__)."/../taskdatatree/StubTask.php";
require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";
require_once dirname(__FILE__)."/../taskdatatree/TaskDataTreeGenerator.php";

$areas=array();

$width=800;

$levelMax=3;

$tdt=new StubTask();
$taskData=new TaskData();
$taskData->setInfo($tdt);

$nodi=array(); //('A','B','C','D','E','F','G','H','I','L','M');

$tree=new TaskDataTreeGenerator();
$treeData=$tree->stubGenerateTaskDataTree();


//vettore di foglie dell'albero
$leav=array();//('D','H','F','I');


$nodi=array();
$nodi=$treeData->deepVisit();

$leav = $treeData->getLeaves();

$numleaves=Count($leav);

 
//Conta il livello
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

$dimBlocco=$width/$numleaves;
$LinkX=array(array());
$LinkY=array(array());
$cord1=0;
$cord2=0;
$occorrenze=1;


//IL PRIMO FOR DEVE SCANDIRE LE RIGHE, CIOE' PER LIVELLO
for($i=$CLiv;$i>=0;$i--)
{
	//IL SECONDO FOR INVECE DEVE SCANDIRE IL VETTORE DELLE FOGLIE (COLONNE)
	for($j=0;$j<$numleaves;$j++)
	{
		if($leav[$j]!=null)
			{
				if(($leav[$j]->getInfo()->getLevel())==$Livello)
				{					
					$occorrenze = getOccorrence($leav,$leav[$j]);
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

$s = new LineStyle();
$s->style = "longdashed";
$s->weight = 2;
$s->color = "black";

$gif = new GifImage($width,$height);

for($i=0;$i<$CLiv;$i++)
{
	//IL SECONDO FOR INVECE DEVE SCANDIRE IL VETTORE DELLE FOGLIE (COLONNE)
	for($j=0;$j<$numleaves;$j++)
	{
		if($LinkX[$i+1][$j]!=null)
		{
			DrawingHelper::LineFromTo($LinkX[$i][$j]+75,$LinkY[$i][$j]+200,$LinkX[$i+1][$j]+75,$LinkY[$i+1][$j],$gif,$s);
		}
	}
}

foreach($areas as $a)
	$a->drawOn($gif);
		
$gif->draw();
$gif->saveToFile("./provagrossa.gif");

?>
<?php 
function getLevel($elemento)
{
		switch ($elemento) {
	    case 'A':
	        return '0';
    	case 'B':
        	return '1';
	    case 'C':
	        return '1';
		case 'D':
			return '2';		
	    case 'E':
	    	return '2';    	
    	case 'F':
	    	return '2';
    	case 'G':
    		return '2';
	    case 'H':    
	    	return '3';
	    case 'I':
    		return '3';           	
	}
}
function getCountChildren($elemento)
{
		switch ($elemento) 
		{
	    case "A":
	        return '2';
    	case "B":
        	return '2';
	    case "C":
	        return '2';
		case "D":
			return '0';		
	    case "E":
	    	return '1';    	
    	case "F":
	    	return '0';
    	case "G":
    		return '1';
	    case "H":    
	    	return '0';
	    case "I":
    		return '0';           	
		}	
}
function getOccorrence($array,$valore)
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
/*
	
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
*/
?>