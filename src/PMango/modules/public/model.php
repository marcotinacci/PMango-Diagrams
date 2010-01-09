<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      model functions 

 File:       model.php
 Location:   pmango\modules\public
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   First version, created to implement model functions.

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


require_once( "$baseDir/classes/ui.class.php" );
/*require_once( "$baseDir/modules/calendar/calendar.class.php" );*/

$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$model = dpGetParam( $_GET, 'model', null );
$deliveryDay = dpGetParam( $_GET, 'deliveryDay', null );
$project_id = dpGetParam( $_GET, 'project_id', 0 );
$task_id = dpGetParam( $_GET, 'task_id', 0 );

// if $date is empty, set to null
$deliveryDay = $deliveryDay !== '' ? $deliveryDay : null;

$deliveryDay = new CDate( $deliveryDay );
$df = $AppUI->getPref('SHDATEFORMAT');
$df2 = FMT_TIMESTAMP_DATE;
$q = new DBQuery();
$dates = array();
$count=0;
if ($task_id == 0 && $project_id > 0) {
	$q->addQuery('project_start_date, project_finish_date');
	$q->addTable('projects');
	$q->addWhere('project_id = '.$project_id);
	$r1 = $q->loadList();
	
	$q->addQuery('task_finish_date');
	$q->addTable('tasks');
	$q->addWhere('task_parent = task_id && task_project = '.$project_id);
	$r2 = $q->loadColumn();
	
	foreach ($r2 as $d) {
		if (intval($d)) {
			$d2 = new Cdate($d);
			$dates[$d2->format($df2)] = $d2->format($df);
		}
	}
	$dates = array_unique($dates);
	$count = count($dates);
	
	if (intval($r1[0]['project_start_date']) && intval($r1[0]['project_finish_date'])) {
		$psd = new CDate($r1[0]['project_start_date']);
		$pfd = new CDate($r1[0]['project_finish_date']);
				
		$dif = (int)($pfd->dateDiff($psd)/5); 
		$psd->addDays((int)($dif/4*7));// 35%
		$dates[$psd->format($df2)] = $psd->format($df);
		$psd->addDays($dif);// 50%
		$dates[$psd->format($df2)] = $psd->format($df);
		$psd->addDays($dif);// 70%
		$dates[$psd->format($df2)] = $psd->format($df);
		$dates[$psd->format($df2)] = $pfd->format($df);
	}
	$dates = array_unique($dates);
} elseif ($project_id == 0 && $task_id > 0) {
	$q->addQuery('task_start_date, task_finish_date');
	$q->addTable('tasks');
	$q->addWhere('task_id = '.$task_id);
	$r1 = $q->loadList();
	
	$q->addQuery('task_finish_date');
	$q->addTable('tasks');
	$q->addWhere('task_parent ='. $task_id);
	$r2 = $q->loadColumn();
	
	foreach ($r2 as $d) {
		if (intval($d)) {
			$d2 = new Cdate($d);
			$dates[$d2->format($df2)] = $d2->format($df);
		}
	}
	$dates = array_unique($dates);
	$count = count($dates);
	
	if (intval($r1[0]['task_start_date']) && intval($r1[0]['task_finish_date'])) {
		$psd = new CDate($r1[0]['task_start_date']);
		$pfd = new CDate($r1[0]['task_finish_date']);
				
		$dif = (int)($pfd->dateDiff($psd)/5); 
		$psd->addDays((int)($dif/4*7));// 35%
		$dates[$psd->format($df2)] = $psd->format($df);
		$psd->addDays($dif);// 50%
		$dates[$psd->format($df2)] = $psd->format($df);
		$psd->addDays($dif);// 70%
		$dates[$psd->format($df2)] = $psd->format($df);
		$dates[$psd->format($df2)] = $pfd->format($df);
	}
	$dates = array_unique($dates);
}

//echo"<pre>";
/*print_r($r1);
print_r($r2);*/
//echo"</pre>";
?>

<script language="javascript">
/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
	function insertModel() {
		if (document.frmModel.model[0].checked)
			model = 1;
		else 
			model = 2;
		window.opener.<?php echo $callback;?>(model,document.frmModel.task_delivery_day.value);
		window.close();
	}
	
	function closeWindow() {
		window.close();
	}
	
	function popCalendar2(){
		idate = document.frmModel.task_delivery_day.value;
		window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setModelDate&date=' + idate, 'calwin', 'top=250,left=250,width=251, height=220, scollbars=false' );
	}
	
	function setModelDate( idate, fdate ) {
		document.frmModel.task_delivery_day.value = idate;
		document.frmModel.delivery_day.value = fdate;
	}
	
</script>

<table border="0" cellspacing="0" cellpadding="3" width="100%">
<form name="frmModel" action="./index.php?m=tasks" method="post">
	<tr>
		<td align="left" nowrap="nowrap">
			<input type="radio" name="model" <?php if ($model<>2) echo "checked"?>/>
			<?php echo $AppUI->_( 'Uniform model' ); ?>
		</td>
	</tr>
	<tr>
		<td align="left" nowrap="nowrap">
			<input type="radio" name="model"  <?php if ($model==2) echo "checked"?>/>
			<?php echo $AppUI->_( 'Early phase effort model' ).":";?>
		</td>
	</tr>
	<tr>
		<td align="center">
			<table border="0" cellspacing="0" cellpadding="3" width="50%" align="center">
				<tr>
					<td align="right" nowrap="nowrap">
						<?php echo $AppUI->_( 'Delivery date' ).":";?>&nbsp;
					</td>
					<td nowrap="nowrap" align="left">
						<!--<input type="hidden" name="task_delivery_day" id="task_delivery_day" value="<?php //echo $deliveryDay->format( FMT_TIMESTAMP_DATE );?>">-->
						<!--<input type="text" name="delivery_day" id="delivery_day" value="<?php //echo $deliveryDay->format( $AppUI->getPref('SHDATEFORMAT')); ?>" class="text" disabled="disabled">
						<a href="#" onClick="popCalendar2()">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php //echo $AppUI->_('Calendar');?>" border="0" />
						</a>-->
						<select name="task_delivery_day" class="text">
							<?php 
								$i=0;
								foreach ($dates as $d2 => $d) {
									$i++;
									if ($i > $count) {
										if ($deliveryDay->format($df2) == $d2)
											echo "<option selected value=\"".$d2."\" style=\"color: red\">".$d."</option>";
										else
											echo "<option value=\"".$d2."\" style=\"color: red\">".$d."</option>";
									} else {
										if ($deliveryDay->format($df2) == $d2)
											echo "<option selected value=\"".$d2."\">".$d."</option>";
										else
											echo "<option value=\"".$d2."\">".$d."</option>";
									}
								}
							?>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td valign="bottom" align="left">
			<input type="button" class="button" value="<?php echo $AppUI->_('close');?>" onclick="closeWindow()" />
		</td>
		<td valign="bottom" align="right">
			<input type="button" class="button" value="<?php echo $AppUI->_('submit');?>" onclick="insertModel()" />
		</td>
	</tr>
</form>
</table>

