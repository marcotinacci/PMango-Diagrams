<?php

require_once dirname(__FILE__)."/../gifarea/GifImage.php";
require_once dirname(__FILE__)."/../gifarea/GifTaskBox.php";
require_once dirname(__FILE__)."/../gifarea/DrawingHelper.php";
require_once dirname(__FILE__)."/../taskdatatree/StubTask.php";
require_once dirname(__FILE__)."/../taskdatatree/TaskData.php";

$areas=array();

$width=800;

$levelMax=4;

$tdt=new StubTask();
$taskData=new TaskData();
$taskData->setInfo($tdt);

$nodi=array('A','B','C','D','E','F','G','H','I','L','M');

//vettore di foglie dell'albero
$leav=array('D','H','F','I');

$numleaves=Count($leav);

//Conta il livello
$CLiv=1;

foreach($nodi as $n){
	if($CLiv < getLevel($n))
		$CLiv = getLevel($n);
}

//Altezza della pagina, calcolata dinamicamente	
$height=($CLiv+1)*200;

//Spazio tra un livello ed un altro
$alt=$height-220;

$dimBlocco=$width/$numleaves;

for($i=0;$i<2;$i++)
{
	for($j=0;$j<$numleaves;$j++){
		if(getLevel($leav[$j])==$CLiv)
		{
			$areas[] = new GifTaskBox(((($j+1)*$dimBlocco)-($dimBlocco/2))-75,$alt,150,30,$taskData);
		}
		if($leav[$j]=='H')
			$leav[$j]='E';
		if($leav[$j]=='I')
			$leav[$j]='G';
	}
	$CLiv--;
	$alt-=250;
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

$gif = new GifImage(800,$height);

foreach($areas as $a)
	$a->drawOn($gif);
		
//DrawingHelper::drawArrow(50,350,30,30,0,$gif);

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

?>