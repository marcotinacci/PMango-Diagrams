<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      project functions

 File:       project.class.php
 Location:   pmango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango projects.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.

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

/**
 *	@package PMango
 *	@subpackage modules
 *	@version $Revision: 1.29 $
*/

require_once( $AppUI->getSystemClass ('dp' ) );
require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'groups' ) );

/**
 * The Project Class
 */
class CProject extends CDpObject {
	var $project_id = NULL;
	var $project_group = NULL;
	var $project_name = NULL;
	var $project_short_name = NULL;
	//var $project_owner = NULL;
	var $project_url = NULL;
	var $project_start_date = NULL;
	var $project_today = NULL;
	var $project_finish_date = NULL;
	var $project_status = NULL;
	var $project_effort = NULL;
	var $project_color_identifier = NULL;
	var $project_description = NULL;
	var $project_target_budget = NULL;
	var $project_actual_cost = NULL;
	var $project_hard_budget = NULL;
	var $project_creator = NULL;
	var $project_active = NULL;
	var $project_current = NULL;
	var $project_priority = NULL;
	var $project_type = NULL;

	function CProject() {
		$this->CDpObject( 'projects', 'project_id', 'project_group' );
	}

	function check() {
	// ensure changes of state in checkboxes is captured
		$this->project_active = intval( $this->project_active );
		//$this->project_private = intval( $this->project_private );

		return NULL; // object is ok
	}

// overload canDelete
	function canDelete( &$msg, $oid=null ) {
		// TODO: check if user permissions are considered when deleting a project
		global $AppUI;
		$perms =& $AppUI->acl();
		
		return $perms->checkModule( 'projects', 'delete','',intval($this->project_group),1);
		
		// NOTE: I uncommented the dependencies check since it is
		// very anoying having to delete all tasks before being able
		// to delete a project.
		
		/*		
		$tables[] = array( 'label' => 'Tasks', 'name' => 'tasks', 'idfield' => 'task_id', 'joinfield' => 'task_project' );
		// call the parent class method to assign the oid
		return CDpObject::canDelete( $msg, $oid, $tables );
		*/
	}

	function delete() {
        $this->load($this->project_id);
		addHistory('projects', $this->project_id, 'delete', $this->project_name, $this->project_id);
		$q = new DBQuery;
		$q->addTable('tasks');
		$q->addQuery('task_id');
		$q->addWhere("task_project = $this->project_id");
		$sql = $q->prepare();
		$q->clear();
		$tasks_to_delete = db_loadColumn ( $sql );
		foreach ( $tasks_to_delete as $task_id ) {
			$q->setDelete('task_log');
			$q->addWhere('task_log_task ='.$task_id);
			$q->exec();
			$q->clear();
			$q->setDelete('user_tasks');
			$q->addWhere('task_id ='.$task_id);
			$q->exec();
			$q->clear();
			$q->setDelete('task_dependencies');
			$q->addWhere('dependencies_task_id = '.$task_id.' || dependencies_req_task_id ='.$task_id);
			$q->exec();
			$q->clear();
		}
		$q->setDelete('tasks');
		$q->addWhere('task_project ='.$this->project_id);
		$q->exec();
		$q->clear();
		
		$q->setDelete('models');
		$q->addWhere('model_association = 1 && model_pt ='.$this->project_id);
		$q->exec();
		$q->clear();
		
		$q->setDelete('user_projects');
		$q->addWhere('project_id ='.$this->project_id);
		$q->exec();
		$q->clear();
		$q->setDelete('projects');
		$q->addWhere('project_id ='.$this->project_id);
		
        if (!$q->exec()) {
			$result = db_error();
		} else {
			$result =  NULL;
		}
		$q->clear();
		return $result;
	}

	/**	Import tasks from another project
	*
	*	@param	int		Project ID of the tasks come from.
	*	@return	bool	
	**/
	function importTasks ($from_project_id) {
		
		// Load the original
		$origProject = new CProject ();
		$origProject->load ($from_project_id);
		$q = new DBQuery;
		$q->addTable('tasks');
		$q->addQuery('task_id');
		$q->addWhere('task_project ='.$from_project_id);
		$sql = $q->prepare();
		$q->clear();
		$tasks = array_flip(db_loadColumn ($sql));

		$origDate = new CDate( $origProject->project_start_date );

		$destDate = new CDate ($this->project_start_date);

		$timeOffset = $destDate->getTime() - $origDate->getTime();

		$objTask = new CTask();
		
		// Dependencies array
		$deps = array();
		
		// Copy each task into this project and get their deps
		foreach ($tasks as $orig => $void) {
			$objTask->load ($orig);
			$destTask = $objTask->copy($this->project_id);
			$tasks[$orig] = $destTask;
			$deps[$orig] = $objTask->getDependencies ();
		}

		// Fix record integrity 
		foreach ($tasks as $old_id => $newTask) {

			// Fix parent Task
			// This task had a parent task, adjust it to new parent task_id
			if ($newTask->task_id != $newTask->task_parent)
				$newTask->task_parent = $tasks[$newTask->task_parent]->task_id;

			// Fix task start date from project start date offset
			$origDate->setDate ($newTask->task_start_date);
			$destDate->setDate ($origDate->getTime() + $timeOffset , DATE_FORMAT_UNIXTIME ); 
			$destDate = $newTask->next_working_day( $destDate );
			$newTask->task_start_date = $destDate->format(FMT_DATETIME_MYSQL);   
			
			// Fix task finish date from start date + work duration
			$newTask->calc_task_finish_date();
			
			// Dependencies
			if (!empty($deps[$old_id])) {
				$oldDeps = explode (',', $deps[$old_id]);
				// New dependencies array
				$newDeps = array();
				foreach ($oldDeps as $dep) 
					$newDeps[] = $tasks[$dep]->task_id;
					
				// Update the new task dependencies
				$csList = implode (',', $newDeps);
				$newTask->updateDependencies ($csList);
			} // end of update dependencies 

			$newTask->store();

		} // end Fix record integrity	

			
	} // end of importTasks

	/**
	 *	Overload of the dpObject::getDeniedRecords 
	 *	to ensure that the projects owned by denied companies are denied.
	 *
	 *	@author	handco <handco@sourceforge.net>
	 *	@see	dpObject::getAllowedRecords
	 */
	/*function getDeniedRecords( $uid ) {
		$aBuf1 = parent::getDeniedRecords ($uid);
		
		$oCpy = new CCompany ();
		// Retrieve which projects are allowed due to the company rules 
		$aCpiesAllowed = $oCpy->getAllowedRecords ($uid, "company_id,company_name");
		
		$q = new DBQuery;
		$q->addTable('projects');
		$q->addQuery('project_id');
		If (count($aCpiesAllowed))
			$q->addWhere("NOT (project_company IN (" . implode (',', array_keys($aCpiesAllowed)) . '))');
		$sql = $q->prepare();
		$q->clear();
		$aBuf2 = db_loadColumn ($sql);
		
		return array_merge ($aBuf1, $aBuf2); 
		
	}*/

        /** Retrieve tasks with latest task_finish_dates within given project
        * @param int Project_id
        * @param int SQL-limit to limit the number of returned tasks
        * @return array List of criticalTasks
        */
    function getCriticalTasks($project_id = NULL, $limit = 1) {
                $project_id = !empty($project_id) ? $project_id : $this->project_id;
		$q = new DBQuery;
		$q->addTable('tasks');
		$q->addWhere("task_project = $project_id AND !isnull( task_finish_date ) AND task_finish_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('task_finish_date ASC');
		$q->setLimit($limit);

                return $q->loadList();
    }

	function store() {

		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed - $msg";
		}

		if( $this->project_id ) {
			$ret = db_updateObject( 'projects', $this, 'project_id', false );
        		addHistory('projects', $this->project_id, 'update', $this->project_name, $this->project_id);
		} else {
			$ret = db_insertObject( 'projects', $this, 'project_id' );
		        addHistory('projects', $this->project_id, 'add', $this->project_name, $this->project_id);
		}
		
		//split out related departments and store them seperatly.

		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}

	}
	
	function getStartDateFromTask($pid = null) {
        $pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
		$q = new DBQuery;
		$q->addQuery('task_start_date, task_id');
		$q->addTable('tasks');
		$q->addWhere("task_project = $pid AND !isnull( task_start_date ) AND task_start_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('task_start_date ASC');
        $ar = $q->loadList();
        return $ar[0];
    }
	
	function getFinishDateFromTask($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
		$q = new DBQuery;
		$q->addQuery('task_finish_date, task_id');
		$q->addTable('tasks');
		$q->addWhere("task_project = $pid AND !isnull( task_finish_date ) AND task_finish_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('task_finish_date DESC');
        $ar = $q->loadList();
        return $ar[0];
	}
	
	function getEffortFromTask($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "0";
		$q = new DBQuery;
		$q->addQuery('SUM(ut.effort)');
		$q->addTable('tasks','t');
		$q->addJoin('user_tasks','ut','ut.task_id = t.task_id');
		$q->addWhere("t.task_project = $pid && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
			$r = 0;
        return round($r,2);
	}
	
	function getBudgetFromTask($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "0";
		$q = new DBQuery;
		$q->addQuery('SUM(ut.effort * pr.proles_hour_cost)');
		$q->addTable('tasks','t');
		$q->addJoin('user_tasks','ut','ut.task_id = t.task_id');
		$q->addJoin('project_roles','pr','pr.proles_id = ut.proles_id');
		$q->addWhere("t.task_project = $pid && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
			$r = 0;
        return round($r,2);
	}
	
	function getActualStartDate($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
		$q = new DBQuery;
		$q->addQuery('tl.task_log_start_date, t.task_id');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		$q->addWhere("t.task_project = $pid AND !isnull( tl.task_log_start_date ) AND tl.task_log_start_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('tl.task_log_start_date ASC');
        $ar = $q->loadList();
        return $ar[0];
	}
	
	function getActualFinishDate($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
		$q = new DBQuery;
		$q->addQuery('tl.task_log_finish_date, t.task_id');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		$q->addWhere("t.task_project = $pid AND !isnull( tl.task_log_finish_date ) AND tl.task_log_finish_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('tl.task_log_finish_date DESC');
        $ar = $q->loadList();
        return $ar[0];
	}
	
	function getActualEffort($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "0";
		$q = new DBQuery;
		$q->addQuery('SUM(tl.task_log_hours)');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		$q->addWhere("t.task_project = $pid && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
			$r = 0;
        return round($r,2);
	}
	
	function getActualCost($pid = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "0";
		$q = new DBQuery;
		$q->addQuery('SUM(tl.task_log_hours * pr.proles_hour_cost)');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		$q->addJoin('project_roles','pr','pr.proles_id = tl.task_log_proles_id');
		$q->addWhere("t.task_project = $pid && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
			$r = 0;
        return round($r,2);
	}
	
	function getEffortPerformanceIndex($pid = null, $pae = null, $pe = null, $pr = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
        if ($pae == null)
        	$pae = $this->getActualEffort($pid);
        if ($pe == null)
        	$pe = is_null($this->project_effort) ? 0 : $this->project_effort;
        if ($pr == null)
        	$pr = $this->getProgress($pid);
        if (is_null($pae) || is_null($pe) || is_null($pr))
        	return "-";
        
        if ($pr == 0 || $pe == 0)
        	return "-";
        	
        return round(($pae * 100)/($pe * $pr),2);
	}
	
	function getCostPerformanceIndex($pid = null, $pac = null, $ptb = null, $pr = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
        if ($pac == null)
        	$pac = $this->getActualCost($pid);
        if ($ptb == null)
        	$ptb = $this->project_target_budget;
        if ($pr == null)
        	$pr = $this->getProgress($pid);
        if (is_null($pac) || is_null($ptb) || is_null($pr))
        	return "-";
        
        if ($pr == 0 || $ptb == 0)
        	return "-";
        	
        return round(($pac * 100)/($ptb * $pr),2);
	}
	
	function getTimePerformanceIndex($pid = null, $ptd = null, $psd = null, $pfd = null, $pafd = null, $pr = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "-";
        if ($ptd == null)
        	$ptd = new CDate();
        if ($psd == null) {
        	$psd = intval($this->project_start_date) ? new CDate($this->project_start_date) : null;
        }
        if ($pfd == null) {
        	$pfd = intval($this->project_finish_date) ? new CDate($this->project_finish_date) : null;
        }
        if ($pafd == null) {
        	$d = $this->getActualFinishDate();
        	$pafd = intval($d['task_log_finish_date']) ? new CDate($d['task_log_finish_date']) : null;
        } 
        if ($pr == null)
        	$pr = $this->getProgress($pid);
        if (is_null($ptd) || is_null($psd) || is_null($pfd) || is_null($pr))
        	return "-";
        $diff = $ptd->dateDiff($psd);
        if ($diff == 0)
        	$diff = 1;
        $diff2 = $pfd->dateDiff($psd);
        if ($diff2 == 0)
        	$diff2 = 1;
         if ($pr == 0 && $ptd->after($psd)) {
        	return round(1+$diff/$diff2,2);
        }
        if ($pafd == "-" || is_null($pafd))
        	$diff3 = 0;
        else 
        	$diff3 = $pafd->dateDiff($psd);
        if ($diff3 == 0)
        	$diff3 = 1;
        if ($pr == 100) {
        	return round($diff3/$diff2,2);
        }
        if ($ptd->after($psd)) {
        	return round($diff/($diff2*$pr/100),2);
        }
        else
        	return "-";
        	
        return round(($pac * 100)/($ptb * $pr),2);
	}
	
	function getProgress($pid = null, $pe = null) {
		$pid = !empty($pid) ? $pid : $this->project_id;
		if ($pid == 0)
        	return 0;
        $pp = 0;
        if (is_null($pe))
        	$pe = $this->project_effort;
        if ($pe == 0)
        	return 0;
        $q = new DBQuery;
		$q->addQuery('task_id');
		$q->addTable('tasks');
		$q->addWhere("task_project = $pid && task_parent = task_id");
		$ar = $q->loadColumn();	
		$q->clear();
		if (count($ar) > 0) {
			$obj2 = new CTask();
			foreach ($ar as $t) {
				if ($t > 0) {
					$tte = intval($obj2->getEffort($t));
					$pp += intval($obj2->getProgress($t, $tte)) * $tte;	
				}
			}
		}
		return round($pp/$pe,2);
	}
	
	static function getPr($pid) {
		$pid = !empty($pid) ? $pid : 0;
		if ($pid == 0)
        	return 0;
        $pp = 0;
        $q = new DBQuery;
		$q->addQuery('project_effort');
		$q->addTable('projects');
		$q->addWhere("project_id = $pid");
		$pe = $q->loadResult();
		$q->clear();
		if ($pe <= 0 || is_null($pe))
			return 0;
        $pe = round($pe,2);
		$q->addQuery('task_id');
		$q->addTable('tasks');
		$q->addWhere("task_project = $pid && task_parent = task_id");
		$ar = $q->loadColumn();	
		$q->clear();
		if (count($ar) > 0) {
			$obj2 = new CTask();
			foreach ($ar as $t) {
				if ($t > 0) {
					$tte = intval($obj2->getEffort($t));
					$pp += intval($obj2->getProgress($t, $tte)) * $tte;	
				}
			}
		}
		return round($pp/$pe,2);
	}
	
	
	function isDefined() {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return 'Error';
        $msg='';
        // Defined
        // 1.4.1.1 V
        if ($this->project_start_date == '' || is_null($this->project_start_date) || $this->project_start_date == '0000-00-00 00:00:00')
        	$msg.="- Project start date is not defined\n";
        // 1.4.1.2 V
        if ($this->project_finish_date == '' || is_null($this->project_finish_date) || $this->project_finish_date == '0000-00-00 00:00:00')
        	$msg.="- Project finish date is not defined\n";
        // 1.4.1.3 V
        if ($this->project_start_date > $this->project_finish_date)
        	$msg.="- Project start date is after finish date\n";  
        // 1.4.1.4 V
        if ($this->project_effort <= 0)    	
        	$msg.="- Project effort is not defined\n";	 
        // 1.4.1.5 V
        if ($this->project_target_budget <= 0)    	
        	$msg.="- Project target budget is not defined\n";
        // 1.4.1.6 V
        if ($this->project_hard_budget <= 0)    	
        	$msg.="- Project hard budget is not defined\n";
        // 1.4.1.7 V
        $r = 0;
        $q = new DBQuery();
        $q->addQuery("count(user_id)");
        $q->addTable("user_projects");
        $q->addWhere("project_id = $pid");
        $r = $q->loadResult();
        if ($r <= 0)
        	$msg.="- There aren't resources assigned to project\n";

        return $msg;
        
	}
	
	function isWellFormed() {
		$pid = $this->project_id;
        if ($pid == 0)
        	return "Error";
        $msg=''; 
        $ts = $this->getStartDateFromTask($pid);
        if ($this->project_start_date > $ts['task_start_date'])
        	$msg.="- Project start date is after <a href=\"?m=tasks&a=view&task_id=".$ts["task_id"]."\"><span style=\"color:#990000;text-decoration:underline;\">start date from tasks</span></a>\n";
        $tf = $this->getFinishDateFromTask($pid);
        if ($this->project_finish_date < $tf['task_finish_date'])
        	$msg.="- Project finish date is before <a href=\"?m=tasks&a=view&task_id=".$tf["task_id"]."\"><span style=\"color:#990000;text-decoration:underline;\">finish date from tasks</span></a>\n";
        if ($this->project_target_budget > $this->project_hard_budget)
        	$msg.="- Project target budget is greater than hard budget\n";
        if ($this->project_effort <> $this->getEffortFromTask())
        	$msg.="- Project effort is different from effort from tasks\n";
        if ($this->project_target_budget <> $this->getBudgetFromTask())
        	$msg.="- Project target budget is different from budget from tasks\n";
        $q = new DBQuery;
		$q->addQuery("DISTINCT(t.task_id), t.task_name");
		$q->addTable('tasks','t');
		$q->addJoin('user_tasks','ut','ut.task_id = t.task_id');
		//$q->addJoin('user_projects','up',"up.project_id = $pid");
		$q->addWhere("t.task_project = $pid && t.task_id = t.task_parent && ut.user_id <> '' && CONCAT_WS(',',ut.user_id,ut.proles_id) NOT IN (SELECT CONCAT_WS(',',up.user_id,up.proles_id) FROM user_projects as up WHERE up.project_id = $pid)");
		$ar = $q->loadList();
		if (is_array($ar))
	  		foreach ($ar as $t)
	  			$msg.="- Project doesn't contain a resource assigned to task <a href=\"?m=tasks&a=view&task_id=".$t['task_id']."\"><span style=\"color:#990000;text-decoration:underline;\">".CTask::getWBS($t['task_id'])." ".$t['task_name']."</span></a>\n";
	  			
	  	$q->clear();
		$q->addQuery('task_finish_date');
		$q->addTable('tasks');
		$q->addWhere("task_id = task_parent && task_project = $pid");
        $cTasks = $q->loadColumn();
        $dates = array(); $dates[0] = '-';
        $df = FMT_TIMESTAMP_DATE;
        foreach ($cTasks as $d) {
			if (intval($d)) {
				$d2 = new Cdate($d);
				$dates[] = $d2->format($df);
			}
        }
        
        $q->clear();
		$q->addQuery('model_delivery_day');
		$q->addTable('models');
		$q->addWhere("model_type = 2 && model_association = 1 && model_pt = $pid");
        $d = $q->loadResult();
        $d1 = 0;
        if (intval($d)) {
			$d2 = new Cdate($d);
			$d1 = $d2->format($df);
		}
		if ($d1 > 0)
			if (!array_search($d1,$dates))
				$msg .="- Project delivery date doesn't match with a child task finish date\n";
        
		$q->clear();
		$q->addQuery('task_id');
		$q->addTable('tasks');
		$q->addWhere("task_project = $pid");
        $tasks = $q->loadColumn();
        if (is_array($tasks))
	        foreach ($tasks as $tid) {
	        	$obj = new CTask();
	        	if (!$obj->load($tid)) {
					$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
					$AppUI->redirect();
				}
	        	$temp = $obj->isDefined();
	        	$temp .= $obj->isWellFormed('','',true);
	        	if ($temp <> '') {
	        		$msg.=" - Task: <a href=\"?m=tasks&a=view&task_id=$obj->task_id\"><span style=\"color:#990000;text-decoration:underline;\">".$obj->getWBS($obj->task_id)." $obj->task_name</span></a> isn't Well Formed\n";
	        		//$msg.=$temp;
	        	}
	        }
        //$msg = $this->project_start_date." ".$ts['task_start_date'];
        
        return $msg;
        
	}
	
	function isCostEffective() {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "Error";
        $msg='';
        $ptb = $this->project_target_budget;
        $pac = $this->getActualCost();
        if ($ptb < $pac)
        	$msg.="- Project budget is smaller than actual cost\n";
        if ($this->getCostPerformanceIndex($pid,$pac,$ptb,'') > 1)
        	$msg.="- Project cost performance index is greater than 1\n";
        return $msg;
	}
	
	function isEffortEffective() {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "Error";
        $msg='';
        $pe = $this->project_effort;
        $pae = $this->getActualEffort();
        if ($pe < $pae)
        	$msg.="- Project effort is smaller than actual effort\n";
        if ($this->getCostPerformanceIndex($pid,$pae,$pe,'') > 1)
        	$msg.="- Project effort performance index is greater than 1\n";
        return $msg;
        
	}
	
	function isTimeEffective() {
		$pid = !empty($pid) ? $pid : $this->project_id;
        if ($pid == 0)
        	return "Error";
        $msg='';
        if ($this->getTimePerformanceIndex($pid) > 1)
        	$msg.="- Project time performance index is greater than 1\n";
        return $msg;
	}
	
	
	function updateAssignedMembers( $roles, $rmUsers=false ) {
	
       	if (!is_null($roles)) {
                $sql = "DELETE FROM user_projects WHERE proles_id > 0 && project_id = $this->project_id";
                db_exec( $sql );
        }

		foreach ($roles as $user_id => $ar_role_id) { 
        	if (intval( $user_id ) > 0) 
        		foreach ($ar_role_id as $role_id) {
				   $s = is_null($sc[$user_id]) ? 0 : $sc[$user_id];
	               $sql = "REPLACE INTO user_projects (user_id, proles_id, project_id) VALUES ($user_id, $role_id, $this->project_id)";
	               db_exec( $sql );
			}
		}
	}
	
	
	function addAssignedMembers( $roles ) {
        foreach ($roles as $user_id => $ar_role_id) { 
        	if (intval( $user_id ) > 0) {
        		foreach ($ar_role_id as $role_id) {
					$sql = "INSERT INTO user_projects (user_id, proles_id, project_id) VALUES ($user_id, $role_id, $this->project_id)";
					db_exec( $sql );
        		}
        	}
        }	
        // Inserimento degli utenti esterni
		$sql = "SELECT user_id FROM user_setcap WHERE setcap_id > 0 && group_id = $this->project_group";
		$groupUsers = db_loadColumn($sql);
		$perms = new dPacl;
		if (is_array($groupUsers))
    		foreach ($groupUsers as $u)
    			if (intval( $u ) > 0)
					if (!$perms->checkModule('projects','res',$u,intval($this->project_group),1)) {//membro esterno
        				$sql = "INSERT INTO user_projects (user_id, proles_id, project_id) VALUES ($u, 0, $this->project_id)";
						db_exec( $sql );
					}	
	}
}
?>
