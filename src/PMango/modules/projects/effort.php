<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      load information to create effort profiles

 File:       effort.php
 Location:   pmango\modules\projects
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   First version, created to load information to generate effort profiles.

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

$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );
$le = dPgetParam( $_POST, "level", 0 );

$df = $AppUI->getPref('SHDATEFORMAT');
$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery("projects.*, m.model_type, m.model_delivery_day");
$q->addWhere('project_id = '.$project_id);
$q->addJoin('models','m','model_association=1 && model_pt ='. $project_id);
$q->addGroup('project_id');
$sql = $q->prepare();
$q->clear();
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// check permissions to archive projects. It is necessary add and edit permission
$perms =& $AppUI->acl();
$canRead = $perms->checkModule($m, 'view','',intval($obj->project_group),1);

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$title = new CTitleBlock('Effort Analisys', 'graph.gif', $m, "$m.$a");

$title->addCrumb( "?m=projects&a=view&project_id=$project_id", "View project" );

$title->show();

// C'è da effettuare la query in base ad il livello
$q->clear();
$q->addQuery('task_id, task_parent, task_name, task_start_date, task_finish_date, m.model_type, m.model_delivery_day');
$q->addTable('tasks','t');
$q->addJoin('models','m','model_association=2 && model_pt = t.task_id');
$q->addWhere('task_project ='. $project_id);
$q->addOrder('task_wbs_index');
$tasks = $q->loadList();
/*echo"<pre>";
print_r($tasks);
echo"</pre>";*/

function findTasksLevel($ar, $ar2) {
	$l =  array();//echo"<br>";print_r($ar);echo "<br>----".$tp2;
	foreach ($ar2 as $t2) {
		foreach ($ar as $t) {//echo"<br>".$tp;echo "-".$tp2;echo "-".$tid;
			if ($t['task_parent'] == $t2['task_id'] && $t['task_id'] <> $t['task_parent'])
				$l[] = $t;
		}
	}
	return($l);
}

foreach ($tasks as $t) {
	if ($t['task_id'] == $t['task_parent'])
		$levels[0][] = $t;
}

$i=0;
while (count($levels[$i]) > 0) {
	$levels[$i+1] = findTasksLevel($tasks,$levels[$i]);	
	$i++;
}

/*echo count($levels[$i]);
echo"<pre>";
print_r($levels);
echo"</pre>";*/

$num_lev = count($levels);
$lev = array();
for($i=0; $i < $num_lev; $i++)
	$lev[$i]=$i;
	
function findTaskNextLevel($tLevel,$tid) {
	foreach ($tLevel as $t) {
		if ($t['task_parent'] == $tid)
			return true;
	}
	return false;
}

$l=0;
$add = array();
while ($l < $le-1) {
	foreach ($levels[$l] as $t) {
		if (!findTaskNextLevel($levels[$l+1],$t['task_id'])) {
			$add[] = $t['task_id'];
		}
	}
	$l++;
}
$sTasks ="0";

if ($le > 0) {
	foreach ($levels[$le-1] as $t)
		$sTasks .= ",".$t['task_id'];
	foreach ($add as $t)
		$sTasks .= ",".$t;
}
//echo $sTasks;
?>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<form name="editFrm" method="post" action="?<?php echo "m=projects&a=effort&project_id=$project_id";?>">
	<input type="hidden" name="tasks" value="<?php echo $sTasks;?>"/>
	<tr>
		<td align="left" valign="middle"  nowrap="nowrap">
			<?php echo $AppUI->_('Today'); ?>:
			<?php 
				$today = new CDate();
				echo $today->format($AppUI->getPref('SHDATEFORMAT')); 
			?>
			<!--<input type="hidden" name="today" value="<?php //echo $today->format( FMT_TIMESTAMP_DATE );?>" />-->
		</td>
		<td align="center" valign="middle"  nowrap="nowrap">
			<?php echo $AppUI->_('Project name'); ?>:
			<?php echo "<b>".$obj->project_name."</b>"; ?>
		</td>
		<td align="right" valign="middle"  nowrap="nowrap">
			<?php echo $AppUI->_('Planned effort profile level'); ?>:
			<?php echo arraySelect($lev, "level",'size="1" onchange="document.editFrm.submit()" class="text"', $le );
				 ?>
		</td>
		<!--<td align="right">
			<input class="button" type="submit" name="btnFuseAction" value="<?php //echo $AppUI->_('submit');?>"/>	
		</td>-->
	</tr>
	<tr valign="top">
		<td colspan="3" align="center">
			<?php 
				$src="?m=$m&a=effort_analysis&suppressHeaders=1&project_id=$project_id&level=$le&tasks=$sTasks&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.94) + '";
				echo "<script>document.write('<img src=\"$src\">')</script>";
			?>
		</td>
	</tr>
	
	<!--// Legend creation-->
	<tr>
		<td colspan="3" align="center">
			<table cellspacing="0" cellpadding="3" border="1" width="100%" class="tbl2">
			<tr>
				<th width="50">
					<?php echo $AppUI->_('Level'); ?>:
				</th>
				<th nowrap>
					<?php echo $AppUI->_('Project / task name'); ?>:
				</th>
				<th width="90" nowrap>
					<?php echo $AppUI->_('Start Date'); ?>:
				</th>
				<th width="90" nowrap>
					<?php echo $AppUI->_('Finish Date'); ?>:
				</th>
				<th width="180">
					<?php echo $AppUI->_('Model'); ?>:
				</th>
				<th width="90" nowrap>
					<?php echo $AppUI->_('Delivery Date'); ?>:
				</th>
				
			</tr>
			<?php 
				$color = array("#ffeedd","#eeffee","#cceecc","#aaddaa","#88cc88","#77bb77","#55aa55","#339933","#117711","#225522");
				//$color2= "ffccff";
				echo "<tr bgcolor=\"".$color[0]."\">";
				echo "<td align=\"center\">0</td>";
				echo "<td align=\"left\"><b>".$obj->project_name."</b></td>";
				if (intval($obj->project_start_date)) {
					$d = new CDate($obj->project_start_date);
					echo "<td align=\"center\">".$d->format($df)."</td>";
				} else {
					echo "<td align=\"center\">-</td>";
				}
				if (intval($obj->project_finish_date)) {
					$d = new CDate($obj->project_finish_date);
					echo "<td align=\"center\">".$d->format($df)."</td>";
				} else {
					echo "<td align=\"center\">-</td>";
				}
				
				if ($obj->model_type == 1) {
					echo "<td align=\"center\">Uniform Model</td>";
					echo "<td align=\"center\">-</td>";
				}
				elseif ($obj->model_type == 2) {
					echo "<td align=\"center\">Early Phase Effort Model</td>";
					$d = new CDate($obj->model_delivery_day);
					echo "<td align=\"center\">".$d->format( $AppUI->getPref('SHDATEFORMAT'))."</td>";
				} else {
					echo "<td align=\"center\"><font color=\"#777777\">Uniform Model</font></td>";
					echo "<td align=\"center\">-</td>";
				}
				echo "</tr>";
				for ( $i = 1; $i < $num_lev; $i++) {
					foreach ($levels[$i-1] as $l) {
						echo "<tr bgcolor=\"".$color[$i]."\">";
						echo "<td align=\"center\">".$i."</td>";
						echo "<td align=\"left\">".CTask::getWBS($l['task_id'])." ".$l['task_name']."</td>";
						if (intval($l['task_start_date'])) {
							$d = new CDate($l['task_start_date']);
							echo "<td align=\"center\">".$d->format($df)."</td>";
						} else {
							echo "<td align=\"center\">-</td>";
						}
						if (intval($l['task_finish_date'])) {
							$d = new CDate($l['task_finish_date']);
							echo "<td align=\"center\">".$d->format($df)."</td>";
						} else {
							echo "<td align=\"center\">-</td>";
						}
						if ($l['model_type'] == 1) {
							echo "<td align=\"center\">Uniform Model</td>";
							echo "<td align=\"center\">-</td>";
						}
						elseif ($l['model_type'] == 2) {
							echo "<td align=\"center\">Early Phase Effort Model</td>";
							$d = new CDate($l['model_delivery_day']);
							echo "<td align=\"center\">".$d->format($df)."</td>";
						} else {
							echo "<td align=\"center\"><font color=\"#777777\">Uniform Model</font></td>";
							echo "<td align=\"center\">-</td>";
						}
						echo "</tr>";
					}
				}
			?>
			</table>
		</td>
	</tr>
</form>
</table>
<!-- prove-->
<?php

/*
echo $le."<br>";$q->clear();
$q->clear();
$q->addQuery('t.task_id, t.task_start_date, t.task_finish_date, SUM(ut.effort) as task_effort, m.model_type, m.model_delivery_day');
$q->addTable('tasks','t');
$q->addJoin('models','m','model_association = 2 && model_pt = t.task_id');
$q->addJoin('user_tasks','ut','t.task_id = ut.task_id');
$q->addWhere('t.task_project = '.$project_id.' && t.task_id IN ('. $sTasks.')');
$q->addGroup('t.task_id');
$tasks = $q->loadList();
$q->clear();
echo "<pre>";
print_r($tasks);
echo "</pre>";
$sum = 0;
foreach ($tasks as $ta) {
	$totalHours = 0;
	$tobj = new CTask();
	$childs = $tobj->getChild($ta['task_id'],$project_id);
	$totalHours = $tobj->getActualEffort($ta['task_id'],$childs);
	
	$startDate = intval($ta['task_start_date']) ? new CDate($ta['task_start_date']) : null;
	$startDate = $startDate ? $startDate->format($df) : $AppUI->_('not defined');
	
	$finishDate = intval($ta['task_finish_date']) ? new CDate($ta['task_finish_date']) : null;
	$finishDate = $finishDate ? $finishDate->format($df) : $AppUI->_('not defined');
	
	$t=0;
	$t = $tobj->getActualFinishDate($ta['task_id'],$childs);
	echo "<br>".$t['task_log_finish_date']."<br>";

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
					$temp=1-exp(-($t*$t)/(2*$td*$td));
					if ($temp == 0)
						$temp = 1;
					$K = $totalHours/($temp);
					$ydataf[$ii] += $K*($i/($td*$td))*exp(-($i*$i)/(2*$td*$td));
				}
			} else {
				$sum += $af;//if ($af  > 0)
				$ydataf[$ii] += $af; // Uniform distribution
			}
		}
	}
	echo CTask::getWBS($ta['task_id'])." ".$totalHours." $t<br> ";
}
echo $sum;*/
?>