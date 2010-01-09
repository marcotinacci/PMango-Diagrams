<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      effort profiles creation 

 File:       effort_analysis.php
 Location:   pmango\modules\projects
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   First version, created to generate effort profiles.

---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team.

 Other libraries used by PMango are redistributed under their own license.
 See ReadMe.txt in the root folder for details. 

 PMango is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation Inc., 51 Franklin St, 5th Floor, Boston, MA 02110-1301 USA.

---------------------------------------------------------------------------
*/

include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph.php");
include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph_line.php");
include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph_bar.php");
include ("{$dPconfig['root_dir']}/lib/jpgraph/src/jpgraph_scatter.php");

$project_id = dPgetParam( $_REQUEST, 'project_id');
$width      = dPgetParam( $_GET, 'width', 980 );
$le      	= dPgetParam( $_REQUEST, 'level', 0 );

//print_r($_REQUEST);
$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery("projects.*, m.model_type, m.model_delivery_day");
$q->addWhere('project_id = '.$project_id);
$q->addJoin('models','m','model_association=1 && model_pt ='. $project_id);
$sql = $q->prepare();
$q->clear();
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$q->addQuery('tl.task_log_finish_date');
$q->addTable('tasks','t');
$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
$q->addWhere("t.task_project = $project_id AND !isnull( tl.task_log_finish_date ) AND tl.task_log_finish_date !=  '0000-00-00 00:00:00'");
$q->addOrder('tl.task_log_finish_date DESC');
$t = $q->loadResult();
$q->clear();

$df = $AppUI->getPref('SHDATEFORMAT');

$startDate = intval($obj->project_start_date) ? new CDate($obj->project_start_date) : null;
$startDate = $startDate ? $startDate->format($df) : $AppUI->_('not defined');

$finishDate = intval($obj->project_finish_date) ? new CDate($obj->project_finish_date) : null;
$finishDate = $finishDate ? $finishDate->format($df) : $AppUI->_('not defined');

$today = new CDate();
$today = $today ? $today->format($df) : $AppUI->_('not defined');

$t = intval($t) ? new CDate($t) : null;
$t = $t ? $t->format($df) : $AppUI->_('not defined');

$ts = explode ('/', $startDate);
$ts = $ts[2]."-".$ts[1]."-".$ts[0];
$ts = strtotime($ts);

$te = explode ('/', $finishDate);
$te = $te[2]."-".$te[1]."-".$te[0];
$te = strtotime($te);//echo $finishDate."<br>";
$te = ($te-$ts)/86400;

if (!($obj->project_current == '0')) {
	$q->addQuery('project_vdate');
	$q->addTable('passive_project_versions');
	$q->addWhere("project_id = ".substr($obj->project_current,strrpos($obj->project_current,"p")+1,strrpos($obj->project_current,"v")-strrpos($obj->project_current,"p")-1)." && project_version = ".substr($obj->project_current,strrpos($obj->project_current,"v")+1));
	$observerDate = $q->loadResult();
	$observerDate = intval($observerDate) ? new CDate($observerDate) : null;
	$observerDate = $observerDate ? $observerDate->format($df) : $AppUI->_('not defined');
}
else 
	$observerDate = $today;

$tt = explode ('/', $observerDate);
$tt = $tt[2]."-".$tt[1]."-".$tt[0];
$tt = strtotime($tt);//echo $today."<br>";
$tt = ($tt-$ts)/86400;

$t = explode ('/', $t);
$t = $t[2]."-".$t[1]."-".$t[0];
$t = strtotime($t);
$t = ($t-$ts)/86400;


$te = round($te);		//Time End (data di fine progetto).
//$td = round($td);		//Time Delivery (data di consegna progetto).
$t = round($t);			//Time (data ultimo log consuntivato).
$tt = round($tt);

// devo ricavare le informazioni sui modelilii

$q->addQuery('tl.task_log_start_date,tl.task_log_finish_date,tl.task_log_hours,tl.task_log_task');
$q->addTable('tasks','t');
$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
$q->addWhere("t.task_project = $project_id && !isnull(task_log_finish_date) && task_log_finish_date != '0000-00-00 00:00:00'");
$logs = $q->loadList();
$q->clear();
/****************************** CREAZIONE GRAFICO *******************************************/


if (sizeof($logs)<=1) {
	// ERRORE: non ci sono abbastanza log per fare l'analisi!
	$ydata = array(0,0);
	$graph = new Graph(485,300);	
	$graph->SetScale("textlin");
	$graph->img->SetMargin(40,30,30,40);
	$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
	$graph->title->Set($AppUI->_('Warning: no analysis is possible, not enough logs for this task.'));
	$lineplot=new LinePlot($ydata);
	$graph->Add($lineplot);
	$graph->Stroke();
}
else {
	// Definisco il grafico.
	$graph = new Graph($width,595);
	$graph->legend->Pos(0.05, 0.35, "left", "bottom");
	$graph->img->SetMargin(40,30,35,90);
	$graph->SetScale("textlin");
	//$ydata = array(0,0);
	// Imposto il fondoscala del grafico.
	/*if ($show=="Yes") {
		$n = max($t, $te);
	}
	else {
		$n = $t;
	}*/
	$n = $te;

	// Creo il rettangoloide dell'impegno istantaneo consuntivato.
	for ($i=0; $i<$n; $i++) {
		$ydata[$i] = 0;
		$xdata[$i] = 0;
	}
	foreach ($logs as $row) {
		$tdif = (int)((strtotime($row['task_log_finish_date'])-strtotime($row['task_log_start_date']))/86400);//number day
		if ($tdif <= 0) {
			$in = (int)((strtotime($row['task_log_finish_date'])-$ts)/86400);
			if ($in < $n)
				$ydata[$in] += $row['task_log_hours'];
		}
		else {
			$tdh = $row['task_log_hours'] / ($tdif+1);
			for($i=0; $i<=$tdif; $i++) {
				$in = (int)(((strtotime($row['task_log_finish_date'])-$ts)/86400)-$i);
				if ($in < $n)
					$ydata[$in] += $tdh;
			}
		}
	}
	// Disegno il rettangoloide dell'impegno istantaneo.
	$bplot = new BarPlot($ydata);
	$bplot->SetFillColor("orange");
	$bplot->SetWidth(1);
	$bplot->SetLegend($AppUI->_('Actual Effort Profile'));
	$graph->Add($bplot);
	
	//Definisco le etichette delle assi.
	if ($n<=35) {
		for ($i=0; $i<=$n; $i++) {
			$etichettax[$i] = date("j/n/Y", ($i+(round($ts/86400)))*86400);
		}
	}
	if (($n>35) && ($n<=245)){
		for ($i=0; $i<=$n; $i=$i+7) {
			$etichettax[$i] = date("j/n/Y", ($i+(round($ts/86400)))*86400);
			for ($j=1; $j<=6; $j++) {
				$etichettax[$i+$j] = "-";
			}
		}
	}
	if ($n>245) {
		for ($i=0; $i<=$n; $i=$i+31) {
			$etichettax[$i] = date("j/n/Y", ($i+(round($ts/86400)))*86400);
			for ($j=1; $j<=30; $j++) {
				$etichettax[$i+$j] = "-";
			}
		}
	}
	
	// Disegno della curva
	if ($le == 0) {// curva secondo il progetto
		// Creo la curva di attività in base alle informazioni pianificate ed al time delivery fissato...
		$totalHours = 0;
		foreach ($logs as $l)
			$totalHours += $l['task_log_hours'];
		if ($obj->model_type == 2) {
			$deliveryDate = intval($obj->model_delivery_day) ? new CDate($obj->model_delivery_day) : null;
			$deliveryDate = $deliveryDate ? $deliveryDate->format($df) : $AppUI->_('not defined');
			$td = explode ('/', $deliveryDate);
			$td = $td[2]."-".$td[1]."-".$td[0];
			$td = strtotime($td);//echo $today."<br>";
			$td = ($td-$ts) / 86400;
			$td = round($td);
		} else {
			$ef = $obj->project_effort / ($n+1);
			$af = $totalHours / ($t+1);
		}
		for ($i=0; $i<=$n; $i++) {
			$xdata[$i] = $i;
			if ($obj->model_type == 2) {
				if ($i==0) 
					$ydata[$i] = 0.0001;
				$temp=1-exp(-($te*$te)/(2*$td*$td));
				if ($temp == 0)
					$temp = 1;
				$KWF = $obj->project_effort/($temp);
				$ydata[$i] = $KWF*($i/($td*$td))*exp(-($i*$i)/(2*$td*$td));
			} else {
				$ydata[$i] = $ef; // Uniform distribution
			}
		}
		// Disegno la curva di attività.
		$lineplot = new linePlot($ydata, $xdata);
		$lineplot->SetColor("blue");
		$lineplot->SetWeight(2);
		$lineplot->SetLegend($AppUI->_('Planned Effort Profile'));
		$graph->Add($lineplot);
		//Marco il giorno di inizio del progetto.
		$datay = array($ydata[0], $ydata[0]);
		$datax = array($xdata[0], $xdata[0]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("green");
		$sp1->mark->SetWidth(3);
		$graph->Add($sp1);
		//Marco il giorno di rilascio.
		if ($obj->model_type == 2) {
			$datay = array($ydata[$td], $ydata[$td]);
			$datax = array($xdata[$td], $xdata[$td]);
			$sp1 = new linePlot($datay,$datax);
			$sp1->mark->SetType(MARK_FILLEDCIRCLE);
			$sp1->mark->SetFillColor("yellow");
			$sp1->mark->SetWidth(3);
			$graph->Add($sp1);
		}
		//Marco il giorno di fine.
		$datay = array($ydata[$te], $ydata[$te]);
		$datax = array($xdata[$te], $xdata[$te]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("red");
		$sp1->mark->SetWidth(3);
		$graph->Add($sp1);
		//Marco il giorno di osservazione (date scenery).
//				$datay = array(($ydata[$tt]+$ydata[$tt+1])/2, ($ydata[$tt]+$ydata[$tt+1])/2);
//				$datax = array(($xdata[$tt]+$xdata[$tt+1])/2, ($xdata[$tt]+$xdata[$tt+1])/2);
		$datay = array($ydata[$tt], $ydata[$tt]);
		$datax = array($xdata[$tt], $xdata[$tt]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("black");
		$sp1->mark->SetWidth(3);
		$graph->Add($sp1);

		// Creo la curva PREVISTA di attività... in base al consuntivato ed al time delivery impostato...
		for ($i=0; $i<=$n; $i++) {
			$xdata[$i] = $i;
			if ($obj->model_type == 2) {
				if ($i==0) 
					$ydata[$i] = 0.0001;
				else {
					$temp=1-exp(-($t*$t)/(2*$td*$td));
					if ($temp == 0)
						$temp = 1;
					$K = $t!=0 ? $totalHours/($temp) : 0;
					$ydata[$i] = $K*($i/($td*$td))*exp(-($i*$i)/(2*$td*$td));
				}
			} else {
				$ydata[$i] = $af; // Uniform distribution
			}
		}
		
		// Disegno la curva di previsione.
		$lineplot = new linePlot($ydata, $xdata);
		$lineplot->SetColor("red");
		$lineplot->SetWeight(2);
		$lineplot->SetLegend($AppUI->_('Foreseen Effort Profile'));
		$graph->Add($lineplot);
		//Marco il giorno di inizio del progetto.
		$datay = array($ydata[0], $ydata[0]);
		$datax = array($xdata[0], $xdata[0]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("green");
		$sp1->mark->SetWidth(3);
		$sp1->SetLegend($AppUI->_('Start Dates'));// (T-SD: ').date("j/n/Y", $ts).")");
		$graph->Add($sp1);
		//Marco il giorno di rilascio del prodotto.
		if ($obj->model_type == 2) {
			$datay = array($ydata[$td], $ydata[$td]);
			$datax = array($xdata[$td], $xdata[$td]);
			$sp1 = new linePlot($datay,$datax);
			$sp1->mark->SetType(MARK_FILLEDCIRCLE);
			$sp1->mark->SetFillColor("yellow");
			$sp1->mark->SetWidth(3);
			$sp1->SetLegend($AppUI->_('Delivery Dates'));// (T-DD: ').date("j/n/Y", $ts+$td*86400).")");
			$graph->Add($sp1);
		}
		//Marco il giorno di fine del progetto.
		$datay = array($ydata[$te], $ydata[$te]);
		$datax = array($xdata[$te], $xdata[$te]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("red");
		$sp1->mark->SetWidth(3);
		$sp1->SetLegend($AppUI->_('Finish Dates'));// (T-FD: ').date("j/n/Y", $ts+$te*86400).")");
		$graph->Add($sp1);
		//Marco il giorno di osservazione (date scenery).
//				$datay = array(($ydata[$tt]+$ydata[$tt+1])/2, ($ydata[$tt]+$ydata[$tt+1])/2);
//				$datax = array(($xdata[$tt]+$xdata[$tt+1])/2, ($xdata[$tt]+$xdata[$tt+1])/2);
		$datay = array($ydata[$tt], $ydata[$tt]);
		$datax = array($xdata[$tt], $xdata[$tt]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("black");
		$sp1->mark->SetWidth(3);
		$sp1->SetLegend($AppUI->_('Observer Date'));//." (".date("j/n/Y", $ts+$tt*86400).")");
		$graph->Add($sp1);
	}
	elseif ($le > 0) {
		$sTasks		= dPgetParam( $_REQUEST, 'tasks', 0 );
		//$tasks = explode(',',$sTasks);
		$totalHours = 0;
		
		$q->clear();
		$q->addQuery('t.task_id, t.task_start_date, t.task_finish_date, SUM(ut.effort) as task_effort, m.model_type, m.model_delivery_day');
		$q->addTable('tasks','t');
		$q->addJoin('models','m','model_association = 2 && model_pt = t.task_id');
		$q->addJoin('user_tasks','ut','t.task_id = ut.task_id');
		$q->addWhere('t.task_project = '.$project_id.' && t.task_id IN ('. $sTasks.')');
		$q->addGroup('t.task_id');
		$tasks = $q->loadList();
		for ($i=0; $i<=$n; $i++) {
			$xdata[$i] = $i;
			$ydata[$i] = 0.0001;
			$ydataf[$i] = 0.0001;
		}
		
		$beginProject = $ts;$sum = 0;
		foreach ($tasks as $ta) {
			//if ($ta['task_id']==20){
			//$sum += $ta['task_effort'];	
			$startDate = intval($ta['task_start_date']) ? new CDate($ta['task_start_date']) : null;
			$startDate = $startDate ? $startDate->format($df) : $AppUI->_('not defined');
			
			$finishDate = intval($ta['task_finish_date']) ? new CDate($ta['task_finish_date']) : null;
			$finishDate = $finishDate ? $finishDate->format($df) : $AppUI->_('not defined');
			
			$totalHours = 0;
			$tobj = new CTask();
			$childs = $tobj->getChild($ta['task_id'],$project_id);
			$totalHours = $tobj->getActualEffort($ta['task_id'],$childs);
			
			$t=0;
			$t = $tobj->getActualFinishDate($ta['task_id'],$childs);
			$t = intval($t['task_log_finish_date']) ? new CDate($t['task_log_finish_date']) : null;
			$t = $t ? $t->format($df) : $AppUI->_('not defined');
			
			$ts = explode ('/', $startDate);
			$ts = $ts[2]."-".$ts[1]."-".$ts[0];
			$ts = strtotime($ts);
			if ($ts > $beginProject)
				$ba = round(($ts-$beginProject)/86400);
			else 
				$ba = 0;
			$ar_ba[] = $ba;
			
			$te = explode ('/', $finishDate);
			$te = $te[2]."-".$te[1]."-".$te[0];
			$te = strtotime($te);//echo $finishDate."<br>";
			$te = ($te-$ts)/86400;
			$te = round($te);
			$ar_te[] = $ba+$te;
			
			$t = explode ('/', $t);
			$t = $t[2]."-".$t[1]."-".$t[0];
			$t = strtotime($t);
			$t = ($t-$ts)/86400;
			$t = round($t);	
			if ($t < 0)
				$t = 0;
			
			if ($ta['model_type'] == 2) {
				$deliveryDate = intval($ta['model_delivery_day']) ? new CDate($ta['model_delivery_day']) : null;
				$deliveryDate = $deliveryDate ? $deliveryDate->format($df) : $AppUI->_('not defined');
				$td = explode ('/', $deliveryDate);
				$td = $td[2]."-".$td[1]."-".$td[0];
				$td = strtotime($td);//echo $today."<br>";
				$td = ($td-$ts) / 86400;
				$td = round($td);
				if ($td == 0)
					$td = 1;
				$ar_td[] = $ba+$td;
			} else {
				$ef = $ta['task_effort'] / ($te+1);
				$af = $totalHours / ($t+1);
			}
			for ($i=0; $i<=$te; $i++) {
				$ii = $i + $ba;
				if ($ii <= $n) {
					if ($ta['model_type'] == 2) {
						if ($i > 0) {
							$temp=1-exp(-($te*$te)/(2*$td*$td));
							if ($temp == 0)
								$temp = 1;
							$KWF = $ta['task_effort']/($temp);
							$ydata[$ii] += $KWF*($i/($td*$td))*exp(-($i*$i)/(2*$td*$td));
						}
					} else {//$sum += $ef;
						$ydata[$ii] += $ef; // Uniform distribution
					}
				}
			}
			
			for ($i=0; $i<=$te; $i++) {
				$ii = $i + $ba;
				if ($ii <= $n) {
					if ($ta['model_type'] == 2) {
						if ($i > 0) {
							$temp=1-exp(-($t*$t)/(2*$td*$td));
							if ($temp == 0)
								$temp = 1;
							$K = $totalHours/($temp);
							$ydataf[$ii] += $K*($i/($td*$td))*exp(-($i*$i)/(2*$td*$td));
						}
					} else {$sum += $af;//if ($af  > 0)
						$ydataf[$ii] += $af; // Uniform distribution
					}
				}
			}
		}
		// Disegno la curva di attività.
		$lineplot = new linePlot($ydata, $xdata);
		$lineplot->SetColor("blue");
		$lineplot->SetWeight(2);
		$lineplot->SetLegend($AppUI->_('Planned Effort Profile'));
		$graph->Add($lineplot);
		
		$lineplot = new linePlot($ydataf, $xdata);
		$lineplot->SetColor("red");
		$lineplot->SetWeight(2);
		$lineplot->SetLegend($AppUI->_('Foreseen Effort Profile'));
		
		$graph->Add($lineplot);
		if (count($ar_ba) > 0) {
			foreach ($ar_ba as $r) {
				$sp1 = new linePlot(array($ydata[$r],$ydata[$r]),array($xdata[$r],$xdata[$r]));
				$sp1->mark->SetType(MARK_FILLEDCIRCLE);
				$sp1->mark->SetFillColor("green");
				$sp1->mark->SetWidth(3);
				$graph->Add($sp1);
				$sp1 = new linePlot(array($ydataf[$r],$ydataf[$r]),array($xdata[$r],$xdata[$r]));
				$sp1->mark->SetType(MARK_FILLEDCIRCLE);
				$sp1->mark->SetFillColor("green");
				$sp1->mark->SetWidth(3);
				$graph->Add($sp1);
			}
			$sp1->SetLegend($AppUI->_("Start Dates"));
		}
		if (count($ar_te) > 0) {
			foreach ($ar_te as $r) {
				$sp1 = new linePlot(array($ydata[$r],$ydata[$r]),array($xdata[$r],$xdata[$r]));
				$sp1->mark->SetType(MARK_FILLEDCIRCLE);
				$sp1->mark->SetFillColor("red");
				$sp1->mark->SetWidth(3);
				$graph->Add($sp1);
				$sp1 = new linePlot(array($ydataf[$r],$ydataf[$r]),array($xdata[$r],$xdata[$r]));
				$sp1->mark->SetType(MARK_FILLEDCIRCLE);
				$sp1->mark->SetFillColor("red");
				$sp1->mark->SetWidth(3);
				$graph->Add($sp1);
			}
			$sp1->SetLegend($AppUI->_("Finish Dates"));
		}
		if (count($ar_td) > 0) {
			foreach ($ar_td as $r) {
				$sp1 = new linePlot(array($ydata[$r],$ydata[$r]),array($xdata[$r],$xdata[$r]));
				$sp1->mark->SetType(MARK_FILLEDCIRCLE);
				$sp1->mark->SetFillColor("yellow");
				$sp1->mark->SetWidth(3);
				$graph->Add($sp1);
				$sp1 = new linePlot(array($ydataf[$r],$ydataf[$r]),array($xdata[$r],$xdata[$r]));
				$sp1->mark->SetType(MARK_FILLEDCIRCLE);
				$sp1->mark->SetFillColor("yellow");
				$sp1->mark->SetWidth(3);
				$graph->Add($sp1);
			}
			$sp1->SetLegend($AppUI->_("Delivery Dates"));
		}
		
		$datay = array($ydata[$tt], $ydata[$tt]);
		$datax = array($xdata[$tt], $xdata[$tt]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("black");
		$sp1->mark->SetWidth(3);
		$graph->Add($sp1);
		$datay = array($ydataf[$tt], $ydataf[$tt]);
		$datax = array($xdata[$tt], $xdata[$tt]);
		$sp1 = new linePlot($datay,$datax);
		$sp1->mark->SetType(MARK_FILLEDCIRCLE);
		$sp1->mark->SetFillColor("black");
		$sp1->mark->SetWidth(3);
		$sp1->SetLegend($AppUI->_('Observer Date'));//." (".date("j/n/Y", $ts+$tt*86400).")");
		$graph->Add($sp1);
	
	
	
	
	
	
	
	
	
	
	
	
	
	}	
	$graph->title->Set("Project: $obj->project_name at $observerDate - Planned effort profile level: $le");
	$graph->title->SetFont(FF_FONT1,FS_BOLD);

	$graph->xaxis->title->Set($AppUI->_('Day'));
	$graph->xaxis->SetTickLabels($etichettax);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->SetLabelAngle(90);
	$graph->yaxis->title->Set($AppUI->_('Hours of work'));
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);

	// Disegno il grafico.
	$graph->Stroke();
}
			
/********************************************************************************************/

/*$AppUI->setMsg( 'Graph displayed', UI_MSG_OK);
$AppUI->redirect();*/
?>
