<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view user information

 File:       vw_usr.php
 Location:   pmango\modules\admin
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango users.
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
<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">
<tr>
	<td width="60" align="right">
		&nbsp; <?php echo $AppUI->_('sort by');?>:&nbsp;
	</td>
	<?php if (dPgetParam($_REQUEST, "tab", 0) == 0){ ?>
	<th width="200">
	           <?php echo $AppUI->_('Login History');?>
	</th>
	<?php } ?>
	<th width="150">
		<a href="?m=admin&a=index&orderby=user_username" class="hdr"><?php echo $AppUI->_('Login Name');?></a>
	</th>
	<th>
		<a href="?m=admin&a=index&orderby=user_last_name" class="hdr"><?php echo $AppUI->_('Real Name');?></a>
	</th>
</tr>
<?php 
$aru = array();
$perms =& $AppUI->acl();
foreach ($users as $row) {
	if ($perms->isUserPermitted($row['user_id'],null, $AppUI->user_groups[-1]) != $canLogin)
		continue;
?>
<tr>
	<td align="center" nowrap="nowrap">
<?php if ($perms->checkModule('admin', 'edit', null, $AppUI->user_groups[-1])) { ?>
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td>
				<a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row["user_id"];?>" title="<?php echo $AppUI->_('edit');?>">
					<?php echo dPshowImage( './images/icons/stock_edit-16.png', 16, 16, '' ); ?>
				</a>
			</td>
			<td>
				<a href="?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>&tab=1" title="">
					<img src="images/obj/lock.gif" width="16" height="16" border="0" alt="<?php echo $AppUI->_('edit sets of capabilities');?>">
				</a>
			</td>
			<td>
<?php }
	 if ($perms->checkModule('admin', 'delete', null, $AppUI->user_groups[-1])) {
$user_display = addslashes($row["user_first_name"] . " " . $row["user_last_name"]);

$user_display = trim($user_display);
if (empty($user_display))
        $user_display = $row['user_username'];
?>
				<a href="javascript:delMe(<?php echo $row["user_id"];?>, '<?php echo $user_display;?>')" title="<?php echo $AppUI->_('delete');?>">
					<?php echo dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' ); ?>
				</a>
			</td>
		</tr>
		</table>
<?php } ?>
	</td>
	<?php if (dPgetParam($_REQUEST, "tab", 0) == 0){ ?>
	<td nowrap>
	       <?php 
	        $q  = new DBQuery;
			$q->addTable('user_access_log', 'ual');
			$q->addQuery("user_access_log_id, ( unix_timestamp( now( ) ) - unix_timestamp( date_time_in ) ) / 3600 as 		hours, ( unix_timestamp( now( ) ) - unix_timestamp( date_time_last_action ) ) / 3600 as 		idle, if(isnull(date_time_out) or date_time_out ='0000-00-00 00:00:00','1','0') as online");
			$q->addWhere("user_id ='". $row["user_id"]."'");
			$q->addOrder('user_access_log_id DESC');
			$q->setLimit(1);
			$user_logs = $q->loadList();
            if ($user_logs)
	           foreach ($user_logs as $row_log) {
	               if ($row_log["online"] == '1'){
	                   echo '<span style="color: blue">'.$row_log["hours"]." ".$AppUI->_('hrs.'). "( ".$row_log["idle"]." ". $AppUI->_('hrs.')." ".$AppUI->_('idle'). ") - " . $AppUI->_('Online');  
	                   if (($row_log["idle"] * 3600) > dPsessionConvertTime('idle_time'))
	           				$aru[] = $row["user_id"];
	               } else {
	                   echo '<span style="color: brown">'.$AppUI->_('Offline');
	               }
				} 
            else
              echo '<span style="color: orange">'.$AppUI->_('Never Visited');
        echo '</span>';
	}?>
	</td>
	<td align="center">
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>"><?php echo $row["user_username"];?></a>
	</td>
	<td align="center">
		<a href="mailto:<?php echo $row["user_email"];?>"><img src="images/obj/email.gif" width="16" height="16" border="0" alt="email"></a>
<?php
if ($row['user_last_name'] && $row['user_first_name'])
        echo $row["user_last_name"].', '.$row["user_first_name"];
else
        echo '<span style="font-style: italic">unknown</span>';
?>
	</td>
</tr>
<?php }
	$rel = intval( dPgetParam( $_GET, 'reload', 0) );
	if ($rel != 0) { 
		$sql = "DELETE FROM sessions WHERE (unix_timestamp(now()) - unix_timestamp(session_updated)) > ". dPsessionConvertTime('idle_time');
		db_exec($sql);
		
		if (count($aru)>0) {
			$q->clear();
			$q->addTable('user_access_log');
			$q->addUpdate('date_time_out', date("Y-m-d H:i:s"));
			$q->addWhere("user_id IN (".implode(',',array_values($aru)).") and (date_time_out='0000-00-00 00:00:00' or isnull(date_time_out)) ");
			$q->exec();
			$q->clear();
		}
		$AppUI->redirect('m=admin&tab=0');
	}
?>

</table>
