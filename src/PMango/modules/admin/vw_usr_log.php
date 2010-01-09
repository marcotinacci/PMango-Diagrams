<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view user log

 File:       vw_usr_log.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to view PMango user log.
 - 2006.07.26 Lorenzo
   First version, unmodified from dotProject 2.0.1.
   
---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team

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
 Foundation Inc., 51 Franklin St, 5th Floor, Boston, MA 02110-1301 USA

---------------------------------------------------------------------------
*/
?>
<script languaje="JavaScript">
var calendarField = '';
var calWin = null;


function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.frmDate.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=451, height=220, scollbars=false' );
}

function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.frmDate.log_' + calendarField );
	fld_fdate = eval( 'document.frmDate.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function checkDate(){
           if (document.frmDate.log_start_date.value == "" || document.frmDate.log_end_date.value== ""){
                alert("<?php echo $AppUI->_('You must fill fields', UI_OUTPUT_JS) ?>");
                return false;
           } 
           return true;
}
</script>

<?php
$date_reg = date("Y-m-d");
$start_date = intval( $date_reg) ? new CDate( $date_reg ) : null;
$end_date = intval( $date_reg) ? new CDate( $date_reg ) : null;

$df = $AppUI->getPref('SHDATEFORMAT');
global $currentTabId;
if ($a = dPgetParam($_REQUEST, "a", "") == ""){
    $a = "&tab={$currentTabId}&showdetails=1";
} else {
    $user_id = dPgetParam($_REQUEST, "user_id", 0);
    $a = "&a=viewuser&user_id={$user_id}&tab={$currentTabId}&showdetails=1";
}

?>

<table align="center">
	<tr>
		<td>
			<h1><?php echo $AppUI->_('User Log');?></h1>
		</td>
	</tr>
</table>

<form action="index.php?m=admin<?php echo $a; ?>" method="post" name="frmDate">
<table align="center" " width="100%">
	<tr align="center">
		<td align="right" width="45%" ><?php echo $AppUI->_( 'Start Date' );?></td>
			<td width="55%" align="left">
				<input type="hidden" name="log_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
				<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : "" ;?>" class="text" readonly disabled="disabled" />
				<a href="#" onClick="popCalendar('start_date')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" ></a>
			</td>
	</tr>
	<tr align="center">
		<td align="right" width="45%"><?php echo $AppUI->_( 'End Date' );?></td>
			<td width="55%" align="left">
				<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
				<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" readonly disabled="disabled" />
				<a href="#" onClick="popCalendar('end_date')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0"></a>
		</td>
	</tr>
</table>
<table align="center">
	<tr align="center">
		<td><input type="submit" class="button" value="<?php echo $AppUI->_('Submit');?>" onClick="return checkDate('start','end')"></td>
	</tr>
</table>
</form>

<?php 
if (dPgetParam($_REQUEST, "showdetails", 0) == 1 ) {  
    $start_date = date("Y-m-d", strtotime(dPgetParam($_REQUEST, "log_start_date", date("Y-m-d") )));
    $end_date   = date("Y-m-d 23:59:59", strtotime(dPgetParam($_REQUEST, "log_end_date", date("Y-m-d") )));
    
    	$q  = new DBQuery;
	$q->addTable('user_access_log', 'ual');
	$q->addTable('users', 'u');
	$q->addQuery('ual.*, u.*');
	$q->addWhere('ual.user_id = u.user_id');
	if($user_id != 0) { $q->addWhere("ual.user_id='$user_id'"); }
	$q->addWhere("ual.date_time_in >='$start_date'");
	$q->addWhere("ual.date_time_out <='$end_date'");
	$q->addGroup('ual.date_time_last_action DESC');//echo $q->prepare();
	$logs = $q->loadList();
?>
<table align="center" class="tbl" width="50%" border="1" cellspacing="0">
    <th nowrap="nowrap"  ><?php echo $AppUI->_('Name(s)');?></th>
    <th nowrap="nowrap"  ><?php echo $AppUI->_('Last Name');?></th>
    <th nowrap="nowrap"  ><?php echo $AppUI->_('Internet Address');?></th>
    <th nowrap="nowrap"  ><?php echo $AppUI->_('Date Time IN');?></th>
    <th nowrap="nowrap"  ><?php echo $AppUI->_('Date Time OUT');?></th>
<?php foreach ($logs as $detail){?>
	<tr>
		<td align="center"><?php echo $detail["user_first_name"];?></td>
		<td align="center"><?php echo $detail["user_last_name"];?></td>
		<td align="center"><?php echo $detail["user_ip"];?></td>
		<td align="center"><?php echo $detail["date_time_in"];?></td>
		<td align="center"><?php echo $detail["date_time_out"];?></td>
	</tr>
<?php } ?>
</table>
<?php } ?>
