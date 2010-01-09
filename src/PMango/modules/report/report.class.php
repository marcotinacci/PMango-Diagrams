<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      report functions.

 File:       report.class.php
 Location:   PMango\modules\report
 Started:    2005.09.30
 Author:     Riccardo Nicolini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
   First version, created to manage PDF reports. 
   
-------------------------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi, Riccardo Nicolini
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (C) 2003-2005 The dotProject Development Team.

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

-------------------------------------------------------------------------------------------
*/

require_once( $AppUI->getSystemClass ('dp' ) );
$AppUI->savePlace();
/**
 * The Reoprt Class
 */
class CReport extends CDpObject {


	var $report_id = NULL;

	// the constructor
	function CReport() {
		$this->CDpObject( 'report', 'report_id' );
	}

	function delete() {
		$sql = "DELETE FROM reports WHERE report_id = $this->report_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function getWBS($tid, $isRootChildren = false) {
			if (is_null($tid))
				return "";
			$sql = "SELECT task_parent, task_wbs_index FROM tasks WHERE $tid = task_id";
			$r = db_loadList($sql);
			
			$currentTask = $tid;
			$wbs = $r[0]['task_wbs_index'];
			if ($r[0]['task_parent']==$tid && $isRootChildren)
				return "";
				
			while ($currentTask != $r[0]['task_parent']) {
				$currentTask = $r[0]['task_parent'];
				if (!is_null($currentTask)) {
					$sql = "SELECT task_wbs_index, task_parent FROM tasks WHERE $currentTask = task_id";
					$r = db_loadList($sql);
					if ($r[0]['task_wbs_index'] == "")
						return strrev($wbs);
					$wbs .= ".".$r[0]['task_wbs_index'];
				}
			}
			return strrev($wbs);
		}


	function getTaskReport($pid, $report_id=1){
		
		GLOBAL $AppUI;
		$user_id = $AppUI->user_id;
		$project_id=$pid;
		$rep_id=$report_id;
		

		if($rep_id==1){
		$sql="SELECT p_is_incomplete as is_incomplete, p_report_level as report_level, p_report_roles as report_roles, p_report_sdate as report_sdate, p_report_edate as report_edate, p_report_opened as report_opened, p_report_closed as report_closed FROM reports WHERE reports.project_id=".$project_id." AND user_id=".$user_id;}
		else if($rep_id==2){
		$sql="SELECT a_is_incomplete as is_incomplete, a_report_level as report_level, a_report_roles as report_roles, a_report_sdate as report_sdate, a_report_edate as report_edate, a_report_opened as report_opened, a_report_closed as report_closed FROM reports WHERE reports.project_id=".$project_id." AND user_id=".$user_id;	
		}
		$param = db_loadList($sql);
		
		
		$lev=$param[0]['report_level'];
		$role=$param[0]['report_roles'];
		if($role!=null){
		switch($role){
			case "N": $showrole="Person Number";
			break;
			case "P": $showrole="Person Name";
			break;
			case "R": $showrole="Person Role";
			break;
			case "A": $showrole="Person Name and Role";
			break;
		}
		
		$tasks_opened=explode("/",$param[0]['report_opened']);
		$tasks_closed=explode("/",$param[0]['report_closed']);
		
		$opened_wbs=array();
		$closed_wbs=array();
		$opened_name=array();
		$closed_name=array();
		
		for($i=0;$i<count($tasks_opened);$i++){
		if($tasks_opened[$i]!=""){
		  	$sql="SELECT task_name FROM tasks WHERE task_id=".$tasks_opened[$i];
			$name = db_loadList($sql);
		  	$opened_name[]=$name[0][0];
			$opened_wbs[]=CReport::getWBS($tasks_opened[$i]);
			}
		}
	
		for($i=0;$i<count($tasks_closed);$i++){
		if($tasks_closed[$i]!=""){
			$sql="SELECT task_name FROM tasks WHERE task_id=".$tasks_closed[$i];
			$name = db_loadList($sql);
		  	$closed_name[]=$name[0][0];
			$closed_wbs[]=CReport::getWBS($tasks_closed[$i]);
			}
		}

		$sdate=new CDate($param[0]['report_sdate']);
		$edate=new CDate($param[0]['report_edate']);	
	
		$s="<table border='0' cellpadding='1' cellspacing='2'><tr><td nowrap='nowrap'>Show Incomplete</td><td nowrap='nowrap'>";
		$s .= ($param[0]['is_incomplete']) ? "<img border='0' src='./images/icons/stock_ok-16.gif'>" : "<img border='0' src='./images/icons/stock_cancel-16.gif'>";
		$s .="</td></tr><tr><td nowrap='nowrap'>Explode Task</td><td nowrap='nowrap'>Level ".$lev."</td></tr>";
		$s .="<tr><td nowrap='nowrap'>Show Roles</td><td nowrap='nowrap'>".$showrole."</td></tr>";
		$s .="<tr><td nowrap='nowrap'>Date Period</td><td nowrap='nowrap'>".$sdate->format( FMT_REGULAR_DATE ).' - '.$edate->format( FMT_REGULAR_DATE )."</td></tr>";
		$s .="<tr><td valign='top'nowrap='nowrap'>Exploded Tasks</td><td>";
							
		if(count($opened_wbs)>0){
		 	$s .="<table>";
			for($i=0;$i<count($opened_wbs);$i++){
			 	$s .="<tr><td nowrap='nowrap'>";
				$s .=$opened_wbs[$i]."</td><td nowrap='nowrap'>- ".$opened_name[$i];
				$s .="</td></tr>";
				}
			$s .="</table>";
			}else {$s .="<img border='0' src='./images/icons/stock_cancel-16.gif'>"; }
			 
		$s .="</td></tr><tr><td valign='top'>Closed Tasks</td><td>";
		
		if(count($closed_wbs)>0){
		 	$s .="<table>";
			for($i=0;$i<count($closed_wbs);$i++){
			 	$s .="<tr><td nowrap='nowrap'>";
				$s .=$closed_wbs[$i]."</td><td nowrap='nowrap'>- ".$closed_name[$i];
				$s .="</td></tr>";
				}
			$s .="</table>";
			}else {$s .="<img border='0' src='./images/icons/stock_cancel-16.gif'>" ;}
			 
		$s .="</td></tr><tr><td nowrap='nowrap' colspan='4'></td></tr></table>";}
		else {
		 	if($rep_id==1) $s="<br>No Task Planned Report defined";
			if($rep_id==2) $s="<br>No Task Actual Report defined";
			}
		
		echo $s;
		if($role!=null){
			$is_incomplete=$param[0]['is_incomplete'];
			$values=array($tasks_opened, $tasks_closed, $sdate, $edate, $role, $lev, $is_incomplete);
			return $values;}
		else return 0;
	}

	function getLogReport($pid){
		
		GLOBAL $AppUI;
		$user_id = $AppUI->user_id;
		$project_id=$pid;

		$sql="SELECT l_hide_complete, l_hide_inactive, l_user_id, l_report_sdate, l_report_edate FROM reports WHERE reports.project_id=".$project_id." AND user_id=".$user_id;
		$log_param = db_loadList($sql);
		
		
		$ruser_id=$log_param[0]['l_user_id'];
		if($log_param[0]['l_user_id']!=null){
		$sql="SELECT concat(user_first_name,' ',user_last_name) FROM users WHERE user_id=$ruser_id";
		$ruser = db_loadList($sql);
		if($ruser_id==-2) $ruser[0][0]= "Grouped by User";
		if($ruser_id==-1) $ruser[0][0]= "All Users";
	
		$sdate=new CDate($log_param[0]['l_report_sdate']);
		$edate=new CDate($log_param[0]['l_report_edate']);	
	
		$s="<table border='0' cellpadding='1' cellspacing='2'><tr><td nowrap='nowrap'>Hide Inactive</td><td nowrap='nowrap'>";
		$s .= ($log_param[0]['l_hide_inactive']) ? "<img border='0' src='./images/icons/stock_ok-16.gif'>" : "<img border='0' src='./images/icons/stock_cancel-16.gif'>";
		$s .="</td></tr>";
		$s .="<tr><td nowrap='nowrap'>Incomplete Tasks</td><td nowrap='nowrap'>";
		$s .= ($log_param[0]['l_hide_complete']) ? "<img border='0' src='./images/icons/stock_ok-16.gif'>" : "<img border='0' src='./images/icons/stock_cancel-16.gif'>";
		$s .="</td></tr>";
		$s .="<tr><td nowrap='nowrap'>User Filter</td><td nowrap='nowrap'>".$ruser[0][0]."</td></tr>";
		$s .="<tr><td nowrap='nowrap'>Date Period</td><td nowrap='nowrap'>".$sdate->format( FMT_REGULAR_DATE ).' - '.$edate->format( FMT_REGULAR_DATE )."</td></tr>";
		$s .="<tr><td nowrap='nowrap' colspan='4'></td></tr></table>";}
		else $s="<br>No Task Log Report defined";
		
		echo $s;
		
		if($log_param[0]['l_user_id']!=null){
			$values=array($ruser_id, $log_param[0]['l_hide_inactive'], $log_param[0]['l_hide_complete'], $sdate, $edate);
			return $values;}
		else return 0;
	}
	
	function getWBSReport($pid){
		
		GLOBAL $AppUI;
		$user_id = $AppUI->user_id;
		$project_id=$pid;
		
		$sql="SELECT properties, prop_summary FROM reports WHERE reports.project_id=".$project_id." AND user_id=".$user_id;
		$wbs_param = db_loadList($sql);

		if($wbs_param[0]['properties']!=null){
				$string=str_replace("@","'",$wbs_param[0]['properties']);
				$summary=explode("|",$wbs_param[0]['prop_summary']);
				$string2="Project isn't ";
				for($i=0;$i<count($summary);$i++){
				$string2.=str_replace("Project isn't","",$summary[$i]);
				if($i<count($summary)-2)
					$string2.=" and ";
				}
			if($wbs_param[0]['prop_summary']!=null){	
				$s ="<table><tr><td nowrap='nowrap'>";
				$s .="<font color='red'><strong>".$string2."</strong></font>&nbsp;&nbsp;";
				$s.="<a href=\"javascript:showhide('agent99')\"><img src='./modules/report/images/details.gif' alt='Show Details' title='Show Details' border='0'></a>";
				$s.="<div id='agent99' style='display:none;'>".$string."></div>";
				$s .="</td></tr></table>";}
			else{
				$s ="<table><tr><td nowrap='nowrap'>";
				$s.=$string;
				$s .="</td></tr></table>";	
			}
		}
		else $s="<br>No Properties Computed";
		echo $s;
		
		return $string;	
	
	}
	

}
?>