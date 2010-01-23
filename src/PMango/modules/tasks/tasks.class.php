<?php
/**
 ---------------------------------------------------------------------------

 PMango Project

 Title:      task functions.

 File:       task.class.php
 Location:   PMango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.05.08 Riccardo
 Third version, modified to create PDF reports.
 - 2006.07.30 Lorenzo
 Second version, modified to manage Mango task.
 - 2006.07.30 Lorenzo
 First version, unmodified from dotProject 2.0.1.

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

require_once( $AppUI->getSystemClass( 'libmail' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );

// user based access
$task_access = array(
	'0'=>'Public',
//'1'=>'Protected',
	'2'=>'Participant',
	'3'=>'Private'
);


/*
 * CTask Class
 */
class CTask extends CDpObject {
	/** @var int */
	var $task_id = NULL;
	/** @var string */
	var $task_name = NULL;
	/** @var int */
	var $task_parent = NULL;
	var $task_milestone = NULL;
	var $task_project = NULL;
	var $task_wbs_index = NULL;
	var $task_start_date = NULL;
	var $task_today = NULL;

	/** @deprecated */
	var $task_finish_date = NULL;
	var $task_status = NULL;
	var $task_priority = NULL;
	var $task_description = NULL;
	var $task_related_url = NULL;
	var $task_creator = NULL;

	var $task_order = NULL;
	var $task_access = NULL;
	var $task_custom = NULL;
	var $task_type   = NULL;


	function CTask() {
		$this->CDpObject( 'tasks', 'task_id' );
	}

	// overload check
	function check() {
		global $AppUI;

		if ($this->task_id === NULL)
		return 'task id is NULL';

		// ensure changes to checkboxes are honoured
		$this->task_milestone = intval( $this->task_milestone );

		if (!$this->task_creator) {
			$this->task_creator = $AppUI->user_id;
		}

		if (!$this->task_related_url) {
			$this->task_related_url = '';
		}

		/*
		 * Check for bad or circular task relationships (dep or child-parent).
		 * These checks are definately not exhaustive it is still quite possible
		 * to get things in a knot.
		 * Note: some of these checks may be problematic and might have to be removed
		 */
		static $addedit;
		if (!isset($addedit))
		$addedit = dPgetParam($_POST, 'dosql', '') == 'do_task_aed' ? true : false;
		$this_dependencies = array();

		/*
		 * If we are called from addedit then we want to use the incoming
		 * list of dependencies and attempt to stop bad deps from being created
		 */
		if ($addedit) {
			$hdependencies = dPgetParam($_POST, 'hdependencies', '0');
			if ($hdependencies)
			$this_dependencies = explode(',', $hdependencies);
		} else {
			$this_dependencies = explode(',', $this->getDependencies());
		}
		// Set to false for recursive updateDynamic calls etc.
		$addedit = false;
		foreach ($this_dependencies as $i => $td)
		$this_dependencies[$i] = substr($td,0,strpos($td,'-'));
		/*print_r($this_dependencies);*/
		// Have deps
		if (array_sum($this_dependencies)) {

			$this_dependents = $this->task_id ? explode(',', $this->dependentTasks()) : array();
			/*print_r($this_dependents);*/
			// If the dependents' have parents add them to list of dependents
			foreach ($this_dependents as $dependent) {
				$dependent_task = new CTask();
				$dependent_task->load($dependent);
				if ( $dependent_task->task_id != $dependent_task->task_parent )
				$more_dependents = explode(',', $this->dependentTasks($dependent_task->task_parent));
			}
			if (!is_array($more_dependents))
			$more_dependents = array();
			$this_dependents = array_merge($this_dependents, $more_dependents);

			// Task dependencies can not be dependent on this task
			$intersect = array_intersect( $this_dependencies, $this_dependents );
			if (array_sum($intersect)) {
				$ids = "(".implode(',', $intersect).")";
				return array('BadDep_CircularDep', $ids);
			}
		}

		// Has a parent
		if ( $this->task_id && $this->task_id != $this->task_parent ) {
			$this_children = $this->getChildren();
			$this_parent = new CTask();
			$this_parent->load($this->task_parent);
			$parents_dependents = explode(',', $this_parent->dependentTasks());

			if (in_array($this_parent->task_id, $this_dependencies))
			return 'BadDep_CannotDependOnParent';

			// Task parent cannot be child of this task
			if (in_array($this_parent->task_id, $this_children))
			return 'BadParent_CircularParent';

			if ( $this_parent->task_parent != $this_parent->task_id ) {

				// ... or parent's parent, cannot be child of this task. Could go on ...
				if (in_array($this_parent->task_parent, $this_children))
				return array('BadParent_CircularGrandParent', '('.$this_parent->task_parent.')');

				// parent's parent cannot be one of this task's dependencies
				if (in_array($this_parent->task_parent, $this_dependencies))
				return array('BadDep_CircularGrandParent', '('.$this_parent->task_parent.')');

			} // grand parent

			if (!$this->isLeaf()) {//if ( $this_parent->task_dynamic == '1' ) {
				$intersect = array_intersect( $this_dependencies, $parents_dependents );
				if (array_sum($intersect)) {
					$ids = "(".implode(',', $intersect).")";
					return array('BadDep_CircularDepOnParentDependent', $ids);
				}
			}

			if (!$this->isLeaf()) {//	if ( $this->task_dynamic == '1' ) {
				// then task's children can not be dependent on parent
				$intersect = array_intersect( $this_children, $parents_dependents );
				if (array_sum($intersect))
				return 'BadParent_ChildDepOnParent';
			}
		} // parent

		return NULL;
	}


	/**
	 *	Copy the current task
	 *
	 *	@author	handco <handco@users.sourceforge.net>
	 *	@param	int		id of the destination project
	 *	@return	object	The new record object or null if error
	 **/
	function copy($destProject_id = 0, $destTask_id = -1) {
		$newObj = $this->duplicate();

		// Copy this task to another project if it's specified
		if ($destProject_id != 0)
		$newObj->task_project = $destProject_id;

		if ($destTask_id == 0)
		$newObj->task_parent = $newObj->task_id;
		else if ($destTask_id > 0)
		$newObj->task_parent = $destTask_id;
		if ($newObj->task_parent == $this->task_id)
		$newObj->task_parent = '';

		$newObj->store();

		return $newObj;
	}// finish of copy()

	function deepCopy($destProject_id = 0, $destTask_id = 0) {
		$newObj = $this->copy($destProject_id, $destTask_id);
		$new_id = $newObj->task_id;
		$children = $this->getChildren();
		if (!empty($children))
		{
			$tempTask = new CTask();
			foreach ($children as $child)
			{
				$tempTask->load($child);
				$newChild = $tempTask->deepCopy($destProject_id, $new_id);
				$newChild->store();
			}
		}

		return $newObj;
	}

	function move($destProject_id = 0, $destTask_id = -1) {
		if ($destProject_id != 0)
		$this->task_project = $destProject_id;
		if ($destTask_id == 0)
		$this->task_parent = $this->task_id;
		else if ($destTask_id > 0)
		$this->task_parent = $destTask_id;
	}

	function deepMove($destProject_id = 0, $destTask_id = 0) {
		$this->move($destProject_id, destTask_id);
		$children = $this->getDeepChildren();
		if (!empty($children))
		{
			$tempChild = new $CTask();
			foreach ($children as $child)
			{
				$tempChild->load($child);
				$tempChild->move($destProject_id);
				$tempChild->store();
			}
		}
	}
	/**
	 * @todo Parent store could be partially used
	 */
	function store($res_ar) {
		GLOBAL $AppUI;

		$msg = $this->check();
		if( $msg ) {
			$return_msg = array(get_class($this) . '::store-check',  'failed',  '-');
			if (is_array($msg))
			return array_merge($return_msg, $msg);
			else {
				array_push($return_msg, $msg);
				return $return_msg;
			}
		}
		if( $this->task_id ) {
			if (!$this->task_parent || $this->task_parent=="^") {
				$this->task_parent = $this->task_id;
			}
			addHistory('tasks', $this->task_id, 'update', $this->task_name, $this->task_project);
			$this->_action = 'updated';
			// Load the old task from disk
			$oTsk = new CTask();
			$oTsk->load ($this->task_id);

			// if task_status changed, then update subtasks
			if ($this->task_status != $oTsk->task_status)
			$this->updateSubTasksStatus($this->task_status);

			// Moving this task to another project?
			if ($this->task_project != $oTsk->task_project)
			$this->updateSubTasksProject($this->task_project);

			$ret = db_updateObject( 'tasks', $this, 'task_id', false );
		} else {
			$this->_action = 'added';
			$ret = db_insertObject( 'tasks', $this, 'task_id' );
			addHistory('tasks', $this->task_id, 'add', $this->task_name, $this->task_project);

			if (!$this->task_parent || $this->task_parent=="^" || $this->task_parent == $this->task_id) {
				$sql = "UPDATE tasks SET task_parent = $this->task_id WHERE task_id = $this->task_id";
				db_exec( $sql );
			}

			if (!is_null($res_ar) && count($res_ar) > 0)
			foreach ($res_ar as $i => $res) {
				$sql = 'INSERT INTO user_tasks (user_id, proles_id, task_id, effort, perc_effort) VALUES ('.$res[0].','.$res[1].','.$this->task_id.','.$res[2].','.$res[3].')';
				db_exec( $sql );
			}
		}
			
		// update dependencies
		if (!empty($this->task_id))
		$this->updateDependencies($this->getDependencies());
		else
		print_r($this);

		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	/**
	 * @todo Parent store could be partially used
	 * @todo Can't delete a task with children
	 */
	function delete() {
		$this->_action = 'deleted with children';
		// delete the tasks...what about orphans?
		// delete task with parent is this task
		$childrenlist = $this->getChild();

		if (empty($this->task_id)||empty($this->task_parent)||empty($this->task_wbs_index)||empty($this->task_project))
		return NULL;
		// delete linked user tasks
		if ($this->task_id == $this->task_parent) //project child
		$sql="UPDATE tasks SET task_wbs_index = task_wbs_index - 1 WHERE task_parent = task_id && task_wbs_index > $this->task_wbs_index && task_id <> $this->task_id && task_project = $this->task_project";
		else {
			$sql="UPDATE tasks SET task_wbs_index = task_wbs_index - 1 WHERE task_parent = $this->task_parent && task_id <> task_parent && task_wbs_index > $this->task_wbs_index && task_id <> $this->task_id && task_project = $this->task_project";
		}
		if (!db_exec( $sql )) {
			return db_error();
		}
			
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		if (!empty($childrenlist)) {// delete children form user_tasks
			$sql = "DELETE FROM user_tasks WHERE task_id IN ($childrenlist)";
			if (!db_exec( $sql ))
			return db_error();
		}

		//load it before deleting it because we need info on it to update the parents later on
		$this->load($this->task_id);
		//addHistory('tasks', $this->task_id, 'delete', $this->task_name, $this->task_project);

		$sql = "DELETE FROM tasks WHERE task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		if (!empty($childrenlist)) 	{// delete children from tasks
			$sql = "DELETE FROM tasks WHERE task_id IN ($childrenlist)";
			if (!db_exec( $sql ))
			return db_error();
		}

		$sql = "DELETE FROM models WHERE model_association = 2 && model_pt = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		if (!empty($childrenlist)) 	{// delete children from tasks
			$sql = "DELETE FROM models WHERE model_association = 2 && model_pt IN ($childrenlist)";
			if (!db_exec( $sql ))
			return db_error();
		}

		// delete affiliated task_logs
		$sql = "DELETE FROM task_log WHERE task_log_task = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		if (!empty($childrenlist)) {// delete children form task_log
			$sql = "DELETE FROM task_log WHERE task_log_task IN ($childrenlist)";
			if (!db_exec( $sql ))
			return db_error();
		}

		// delete dependencies
		$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id || dependencies_req_task_id = $this->task_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		if (!empty($childrenlist)) {// delete children form task_log
			$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id IN ($childrenlist) || dependencies_req_task_id IN ($childrenlist)";
			if (!db_exec( $sql ))
			return db_error();
		}
		return NULL;
	}

	function updateDependencies( $cslist ) {
		// delete all current entries
		$sql = "DELETE FROM task_dependencies WHERE dependencies_task_id = $this->task_id";
		db_exec( $sql );

		// process dependencies
		$tarr = explode( ",", $cslist );
		foreach ($tarr as $task_id) {
			if (intval( $task_id ) > 0) {
				$sql = "REPLACE INTO task_dependencies (dependencies_task_id, dependencies_req_task_id) VALUES ($this->task_id, $task_id)";
				db_exec($sql);
			}
		}
	}

	/**
	 *	Retrieve the tasks dependencies
	 *
	 *	@author	handco	<handco@users.sourceforge.net>
	 *	@return	string	comma delimited list of tasks id's
	 **/
	function getDependencies () {
		// Call the static method for this object
		$result = $this->staticGetDependencies ($this->task_id);
		return $result;
	} // end of getDependencies ()

	//}}}

	//{{{ staticGetDependencies ()
	/**
	 *	Retrieve the tasks dependencies
	 *
	 *	@author	handco	<handco@users.sourceforge.net>
	 *	@param	integer	ID of the task we want dependencies
	 *	@return	string	comma delimited list of tasks id's
	 **/
	static function staticGetDependencies ($taskId) {
		if (empty($taskId))
		return '';
		$sql = "
            SELECT dependencies_req_task_id
            FROM task_dependencies td
            WHERE td.dependencies_task_id = $taskId
		";
		$list = db_loadColumn ($sql);
		$result = $list ? implode (',', $list) : '';

		return $result;
	} // end of staticGetDependencies ()

	//}}}

	/**
	 * @param Date Start date of the period
	 * @param Date finish date of the period
	 * @param integer The target company
	 */
	/*function getTasksForPeriod( $start_date, $finish_date, $company_id=0 ) {
		GLOBAL $AppUI;
		// convert to default db time stamp
		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_finish = $finish_date->format( FMT_DATETIME_MYSQL );

		// filter tasks for not allowed projects
		$tasks_filter = '';
		$proj =& new CProject;
		$task_filter_where = $proj->getAllowedSQL($AppUI->user_id, 'task_project');
		if (count($task_filter_where))
		$tasks_filter = ' AND (' . implode(' AND ', $task_filter_where) . ")";


		// assemble where clause
		$where = "task_project = project_id"
		. "\n\tAND ("
		. "\n\t\t(task_start_date <= '$db_finish' AND task_finish_date >= '$db_start')"
		. "\n\t\tOR task_start_date BETWEEN '$db_start' AND '$db_finish'"
		. "\n\t)"
		. "\n\t$tasks_filter";

		//		OR
		//	task_finish_date BETWEEN '$db_start' AND '$db_finish'
		//OR
		//			(DATE_ADD(task_start_date, INTERVAL task_duration HOUR)) BETWEEN '$db_start' AND '$db_finish'
		//		OR
		//	(DATE_ADD(task_start_date, INTERVAL task_duration DAY)) BETWEEN '$db_start' AND '$db_finish'

		$where .= $company_id ? "\n\tAND project_company = '$company_id'" : '';

		// exclude read denied projects
		$obj = new CProject();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		$where .= count($deny) > 0 ? "\n\tAND task_project NOT IN (" . implode( ',', $deny ) . ')' : '';

		// get any specifically denied tasks
		$obj = new CTask();
		$allow = $obj->getAllowedSQL( $AppUI->user_id );

		$where .= count($allow) > 0 ? "\n\tAND " . implode( ' AND ', $allow ) : '';

		// assemble query
		$sql = "SELECT DISTINCT task_id, task_name, task_start_date, task_finish_date,"
		. "\n\tproject_color_identifier AS color,"
		. "\n\tproject_name"
		. "\nFROM tasks,projects,companies"
		. "\nWHERE $where"
		. "\nORDER BY task_start_date";
			
		//echo "<pre>$sql</pre>";
		// execute and return
		return db_loadList( $sql );
		}*/

	/*function canAccess( $user_id ) {
		//echo intval($this->task_access);
		// Let's see if this user has admin privileges
		if(!getDenyRead("admin")){
		return true;
		}

		switch ($this->task_access) {
		case 0:
		// public
		return true;
		break;
		case 1:
		// protected
		$sql = "SELECT user_company FROM users WHERE user_id=$user_id";
		$user_company = db_loadResult( $sql );
		$sql = "SELECT user_company FROM users WHERE user_id=$this->task_owner";
		$owner_company = db_loadResult( $sql );
		//echo "$user_company,$owner_company";die;

		$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
		$count = db_loadResult( $sql );
		return (($owner_company == $user_company && $count > 0) || $this->task_owner == $user_id);
		break;
		case 2:
		// participant
		$sql = "SELECT COUNT(*) FROM user_tasks WHERE user_id=$user_id AND task_id=$this->task_id";
		$count = db_loadResult( $sql );
		return ($count > 0 || $this->task_owner == $user_id);
		break;
		case 3:
		// private
		return ($this->task_owner == $user_id);
		break;
		}
		}*/

	/**
	 *       retrieve tasks are dependent of another.
	 *       @param  integer         ID of the master task
	 *       @param  boolean         true if is a dep call (recurse call)
	 *       @param  boolean         false for no recursion (needed for calc_end_date)
	 **/
	function dependentTasks ($taskId = false, $isDep = false, $recurse = true) {
		static $aDeps = false;
		// Initialize the dependencies array
		if (($taskId == false) && ($isDep == false))
		$aDeps = array();

		// retrieve dependents tasks
		if (!$taskId)
		$taskId = $this->task_id;

		if (empty($taskId))
		return '';
		$sql = "
			SELECT dependencies_task_id
			FROM task_dependencies AS td, tasks AS t
			WHERE td.dependencies_req_task_id = $taskId
			AND td.dependencies_task_id = t.task_id
		";
		$aBuf = db_loadColumn($sql);
		$aBuf = !empty($aBuf) ? $aBuf : array();
		//$aBuf = array_values(db_loadColumn ($sql));

		if ($recurse) {
			// recurse to find sub dependents
			foreach ($aBuf as $depId) {
				// work around for infinite loop
				if (!in_array($depId, $aDeps)) {
					$aDeps[] = $depId;
					$this->dependentTasks ($depId, true);
				}
			}

		} else {
			$aDeps = $aBuf;
		}

		// return if we are in a dependency call
		if ($isDep)
		return;
			
		return implode (',', $aDeps);

	} // end of dependentTasks()

	// Return date obj for the start of next working day
	function next_working_day( $dateObj ) {
		global $AppUI;
		$end = intval(dPgetConfig('cal_day_end'));
		$start = intval(dPgetConfig('cal_day_start'));
		while ( ! $dateObj->isWorkingDay() || $dateObj->getHour() >= $end ) {
			$dateObj->addDays(1);
			$dateObj->setTime($start, '0', '0');
		}
		return $dateObj;
	}

	// Return date obj for the end of the previous working day
	function prev_working_day( $dateObj ) {
		global $AppUI;
		$end = intval(dPgetConfig('cal_day_end'));
		$start = intval(dPgetConfig('cal_day_start'));
		while ( ! $dateObj->isWorkingDay() || ( $dateObj->getHour() < $start ) ||
		( $dateObj->getHour() == $start && $dateObj->getMinute() == '0' ) ) {
			$dateObj->addDays(-1);
			$dateObj->setTime($end, '0', '0');
		}
		return $dateObj;
	}

	/*

	Get the last end date of all of this task's dependencies

	@param Task object
	returns FMT_DATETIME_MYSQL date

	*/

	function get_deps_max_end_date( $taskObj ) {

		$deps = $taskObj->getDependencies();
		$obj = new CTask();

		// Don't respect end dates of excluded tasks
		$sql = "SELECT MAX(task_finish_date) FROM tasks
				WHERE task_id IN ($deps)";

		$last_end_date = db_loadResult( $sql );

		if ( !$last_end_date ) {
			// Set to project start date
			$id = $taskObj->task_project;
			$sql = "SELECT project_start_date FROM projects
				WHERE project_id = $id";
			$last_end_date = db_loadResult( $sql );
		}

		return $last_end_date;
	}

	/*
	 * Calculate this task obj's end date. Based on start date
	 * and the task duration and duration type.
	 */
	/*function calc_task_end_date() {
		$e = $this->calc_end_date( $this->task_start_date, $this->task_duration, $this->task_duration_type );
		$this->task_finish_date = $e->format( FMT_DATETIME_MYSQL );
		}*/

	/*

	Calculate end date given start date and work time.
	Accounting for (non)working days and working hours.

	@param date obj or mysql time - start date
	@param int - number
	@param int - durnType 24=days, 1=hours
	returns date obj

	*/

	/*function calc_end_date( $start_date=null, $durn='8', $durnType='1' ) {
		GLOBAL $AppUI;

		$cal_day_start = intval(dPgetConfig( 'cal_day_start' ));
		$cal_day_end = intval(dPgetConfig( 'cal_day_end' ));
		$daily_working_hours = intval(dPgetConfig( 'daily_working_hours' ));

		$s = new CDate( $start_date );
		$e = $s;
		$inc = $durn;
		$full_working_days = 0;
		$hours_to_add_to_last_day = 0;
		$hours_to_add_to_first_day = $durn;

		// Calc the end date
		if ( $durnType == 24 ) { // Units are full days

		$full_working_days = ceil($durn);
		for ( $i = 0 ; $i < $full_working_days ; $i++ ) {
		$e->addDays(1);
		$e->setTime($cal_day_start, '0', '0');
		if ( !$e->isWorkingDay() )
		$full_working_days++;
		}
		$e->setHour( $s->getHour() );

		} else {  // Units are hours

		// First partial day
		if (( $s->getHour() + $inc ) > $cal_day_end ) {
		// Account hours for partial work day
		$hours_to_add_to_first_day = $cal_day_end - $s->getHour();
		if ( $hours_to_add_to_first_day > $daily_working_hours )
		$hours_to_add_to_first_day = $daily_working_hours;
		$inc -= $hours_to_add_to_first_day;
		$hours_to_add_to_last_day = $inc % $daily_working_hours;
		// number of full working days remaining
		$full_working_days = round(($inc - $hours_to_add_to_last_day) / $daily_working_hours);

		if ( $hours_to_add_to_first_day != 0 ) {
		while (1) {
		// Move on to the next workday
		$e->addDays(1);
		$e->setTime($cal_day_start, '0', '0');
		if ( $e->isWorkingDay() )
		break;
		}
		}
		} else {
		// less than one day's work, update the hour and be done..
		$e->setHour( $e->getHour() + $hours_to_add_to_first_day );
		}

		// Full days
		for ( $i = 0 ; $i < $full_working_days ; $i++ ) {
		$e->addDays(1);
		$e->setTime($cal_day_start, '0', '0');
		if ( !$e->isWorkingDay() )
		$full_working_days++;
		}
		// Last partial day
		if ( !($full_working_days == 0 && $hours_to_add_to_last_day == 0) )
		$e->setHour( $cal_day_start + $hours_to_add_to_last_day );

		}
		// Go to start of prev work day if current work day hasn't begun
		if ( $durn != 0 )
		$e = $this->prev_working_day( $e );

		return $e;

		} // End of calc_end_date*/

	/**
	 * Function that returns the amount of hours this
	 * task consumes per user each day
	 */
	/*function getTaskDurationPerDay($use_percent_assigned = false){
		$duration              = $this->task_duration*$this->task_duration_type;
		$task_start_date       = new CDate($this->task_start_date);
		$task_finish_date      = new CDate($this->task_finish_date);
		$assigned_users        = $this->getAssignedUsers();
		if ($use_percent_assigned) {
		$number_assigned_users = 0;
		foreach ($assigned_users as $u) {
		$number_assigned_users += ( $u['perc_effort'] / 100 );
		}
		} else {
		$number_assigned_users = count($assigned_users);
		}

		$day_diff              = $task_finish_date->dateDiff($task_start_date);
		$number_of_days_worked = 0;
		$actual_date           = $task_start_date;

		for($i=0; $i<=$day_diff; $i++){
		if($actual_date->isWorkingDay()){
		$number_of_days_worked++;
		}
		$actual_date->addDays(1);
		}
		// May be it was a Sunday task
		if($number_of_days_worked == 0) $number_of_days_worked = 1;
		if($number_assigned_users == 0) $number_assigned_users = 1;
		return ($duration/$number_assigned_users) / $number_of_days_worked;
		}*/

	// unassign a user from task
	function removeAssigned( $user_id ) {
		// delete all current entries
		$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id AND user_id = $user_id";
		db_exec( $sql );

	}

	// @return      returns the Names of the concerned Users if there occured an overAssignment, otherwise false
	function updateAssigned( $res_ar) {
		//print_r($res_ar);
		if (count($res_ar) > 0) {
			$sql = "SELECT CONCAT(user_id, proles_id, task_id), user_task_priority FROM user_tasks WHERE task_id = $this->task_id";
			$utp_ar = db_loadHashList($sql);

			$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
			db_exec( $sql );

			// user_id = res[0]
			foreach ($res_ar as $i => $res) {
				if (intval( $res[0] ) > 0) {
					$utp = 0;
					if (isset($utp_ar[$res[0].$res[1].$this->task_id]))
					$utp = $utp_ar[$res[0].$res[1].$this->task_id];
					$sql = "REPLACE INTO user_tasks (user_id, proles_id, task_id, effort, perc_effort, user_task_priority) VALUES (".$res[0].",".$res[1].",$this->task_id,".$res[2].",".$res[3].",$utp)";
					db_exec( $sql );
				}
			}
		}
		else {
			$sql = "DELETE FROM user_tasks WHERE task_id = $this->task_id";
			db_exec( $sql );
		}
	}


	function getAssignedUsers(){
		$sql="
		 SELECT users.user_last_name AS LastName, user_tasks.effort AS Effort, project_roles.proles_name AS Role
		 FROM users, user_tasks
		 LEFT OUTER JOIN task_log ON ( user_tasks.task_id = task_log.task_log_task
		 AND task_log.task_log_creator = user_tasks.user_id ) , project_roles
 		 WHERE user_tasks.task_id = ".$this->task_id."
		 AND user_tasks.user_id = users.user_id
		 AND project_roles.proles_id = user_tasks.proles_id
		 GROUP BY (users.user_id)";

		$list = db_loadList($sql);
		return $list;
	}
	
	
	/*Ritorna la lista di user_id delle risorse assegnate al task*/
	 
	private function getResourceList(){
	 	$sql="
		 SELECT users.user_id
		 FROM users, user_tasks
		 LEFT OUTER JOIN task_log ON ( user_tasks.task_id = task_log.task_log_task
		 AND task_log.task_log_creator = user_tasks.user_id ) , project_roles
 		 WHERE user_tasks.task_id = ".$this->task_id." 
		 AND user_tasks.user_id = users.user_id 
		 AND project_roles.proles_id = user_tasks.proles_id 
		 GROUP BY (users.user_id)";
	 	
		$list = db_loadList($sql);
		for($i=0; $i<sizeOf($list); $i++){
			for($j=0; $j<sizeOf($list[$i]); $j++){
				DrawingHelper::debug("Elem (".$i.", ".$j.") -> ".$list[$i][$j]);
			}
		}
		return $list;
	}
	
	
	/*
	 * Metodo per calcolare l'actual Effort personale di una risorsa in un task.
	 */
	function getResourceActualEffortInTask($rid = null){
	 	$list = $this->getResourceList();
	 	DrawingHelper::debug("Lunghezza della lista di user del task ".$this->task_id.": ".sizeOf($list));
	 	if($rid==null){
	 		for($i=0; $i<sizeOf($list);$i++){
	 			DrawingHelper::debug("Elemento della lista di user del task ".$this->task_id.": ".$list[$i][0]);
	 			$result = 0;
				$sql="
				 SELECT IF( task_log_creator IS NOT NULL ,
				 	   (SELECT SUM( task_log_hours )
						FROM task_log
						WHERE task_log_task =".$this->task_id."
						AND task_log_creator =".$list[$i][0]."),
				  'composed' )
				 FROM users, user_tasks
				 LEFT OUTER JOIN task_log ON ( user_tasks.task_id = task_log.task_log_task
				 AND task_log.task_log_creator = user_tasks.user_id )
				 WHERE user_tasks.task_id = ".$this->task_id."
				 AND user_tasks.user_id = users.user_id";
				
				$res = db_loadList($sql);
				DrawingHelper::debug("il Risultato della query nel primo foreach è ".$res[0][0]);
				
				if($res[0][0]=='composed'){
					
					$children = $this->getChildren();
					
					foreach($children as $son){
						DrawingHelper::debug("Propagazione del metodo da".$this->task_id." a ".$son);
						$CTask_son = new CTask();
						$CTask_son->load($son);
						$result += $CTask_son->getResourceActualEffortInTask($rid);
					}
					return $result;
				}
				
				else{
					$result += $res[0][0];
				}
			return $result;
			}
	 	}
		else{
			if(in_array($rid, $list)){
				DrawingHelper::debug($rid." è stato trovato nella lista di utenti del task ".$this->task_id);
				$sql="
				 SELECT SUM(task_log_hours)
			 	 FROM task_log
	 		 	 WHERE task_log_task = ".$this->task_id." 
			 	 AND task_log_creator = ".$rid[0][0]."
			 	 GROUP BY (task_log_creator)";
				$res = db_loadList($sql);
				DrawingHelper::debug("Risultato ".$res[0][0]);
				return $res[0][0];
			}
			else{
				DrawingHelper::debug($rid." NON è stato trovato nella lista di utenti del task ".$this->task_id);
				return 0;
			}
		}
	}
	/**
	 *  Calculate the extent of utilization of user assignments
	 *  @param string hash   a hash for the returned hashList
	 *  @param array users   an array of user_ids calculating their assignment capacity
	 *  @return array        returns hashList of extent of utilization for assignment of the users
	 */
	function getAllocation( $hash = NULL, $users = NULL ) {
		// use userlist if available otherwise pull data for all users
		$where = !empty($users) ? 'WHERE u.user_id IN ('.implode(",", $users).') ' : '';
		// retrieve the systemwide default preference for the assignment maximum
		$sql = "SELECT pref_value FROM user_preferences WHERE pref_user = 0 AND pref_name = 'TASKASSIGNMAX'";
		$result = db_loadHash($sql, $sysChargeMax);
		if (! $result)
		$scm = 0;
		else
		$scm = $sysChargeMax['pref_value'];
		// provide actual assignment charge, individual chargeMax and freeCapacity of users' assignments to tasks
		$sql = "SELECT u.user_id,
                        CONCAT(CONCAT_WS(' [', CONCAT_WS(' ',user_first_name,user_last_name), IF(IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_effort)),up.pref_value)>0,IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_effort)),up.pref_value),0)), '%]') AS userFC,
                        IFNULL(SUM(ut.perc_effort),0) AS charge, u.user_username,
                        IFNULL(up.pref_value,$scm) AS chargeMax,
                        IF(IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_effort)),up.pref_value)>0,IFNULL((IFNULL(up.pref_value,$scm)-SUM(ut.perc_effort)),up.pref_value),0) AS freeCapacity
                        FROM users u
                        LEFT JOIN user_tasks ut ON ut.user_id = u.user_id
                        LEFT JOIN user_preferences up ON (up.pref_user = u.user_id AND up.pref_name = 'TASKASSIGNMAX')".$where."
                        GROUP BY u.user_id
                        ORDER BY user_last_name, user_first_name";
		//               echo "<pre>$sql</pre>";
		return db_loadHashList($sql, $hash);
	}

	function getUserSpecificTaskPriority( $user_id = 0, $task_id = NULL ) {
		// use task_id of given object if the optional parameter task_id is empty
		$task_id = empty($task_id) ? $this->task_id : $task_id;
		$sql = "SELECT user_task_priority FROM user_tasks WHERE user_id = $user_id AND task_id = $task_id";
		$prio = db_loadHash($sql, $priority);
		return $prio ? $priority['user_task_priority'] : NULL;
	}

	function updateUserSpecificTaskPriority( $user_task_priority = 0, $user_id = 0, $task_id = NULL ) {
		// use task_id of given object if the optional parameter task_id is empty
		$task_id = empty($task_id) ? $this->task_id : $task_id;
		$sql = "REPLACE INTO user_tasks (user_id, task_id, user_task_priority) VALUES ($user_id, $task_id, $user_task_priority)";
		db_exec( $sql );
	}

	function getProject() {
		$sql = "SELECT project_name, project_short_name, project_color_identifier FROM projects WHERE project_id = '$this->task_project'";
		$proj = db_loadHash($sql, $projects);
		return $projects;
	}

	//Returns task children IDs
	function getChildren() {
		$sql = "select task_id from tasks where task_id != '$this->task_id'
				and task_parent = '$this->task_id'";
		return db_loadColumn($sql);
	}

	// Returns task deep children IDs
	function getDeepChildren()
	{
		return $this->internalGetDeepChildren();


		$children = db_loadColumn( "SELECT task_id FROM tasks WHERE task_parent = $this->task_id" );
		if ($children)
		{
			$deep_children = array();
			$tempTask = new CTask();
			foreach ($children as $child)
			{
				$tempTask->load($child);
				$deep_children = array_merge($deep_children, $this->getChildren());
			}

			return array_merge($children, $deep_children);
		}
		return array();
	}

	private function internalGetDeepChildren() {
		$children = $this->getChildren();
		$result = array_merge(array(), $children);
		$tempTask = new CTask();
		foreach ($children as $child) {
			$tempTask->load($child);
			$result = array_merge($result, $tempTask->internalGetDeepChildren());
		}

		return $result;
	}

	/**
	 * This function, recursively, updates all tasks status
	 * to the one passed as parameter
	 */
	function updateSubTasksStatus($new_status, $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}

		// get children
		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";

		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;

		// update status of children
		$sql = "update tasks set task_status = '$new_status' where task_parent = '$task_id'";

		db_exec($sql);
		// update status of children's children
		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksStatus($new_status, $id);
			}
		}
	}

	/**
	 * This function recursively updates all tasks project
	 * to the one passed as parameter
	 */
	function updateSubTasksProject($new_project , $task_id = null){
		if(is_null($task_id)){
			$task_id = $this->task_id;
		}
		$sql = "select task_id
		        from tasks
		        where task_parent = '$task_id'";

		$tasks_id = db_loadColumn($sql);
		if(count($tasks_id) == 0) return true;

		$sql = "update tasks set task_project = '$new_project' where task_parent = '$task_id'";
		db_exec($sql);

		foreach($tasks_id as $id){
			if($id != $task_id){
				$this->updateSubTasksProject($new_project, $id);
			}
		}
	}

	/**
	 *
	 * @return unknown_type
	 */
	function isLeaf() {
		return $this->isLeafSt($this->task_id);
	}

	/**
	 * REFACTORING: the logic within this method is duplicated into the previous
	 * isLeaf() method.
	 * SOL: call this method from the previous
	 *
	 * ERROR: if noone but me, have me like parent means that I'm a leaf
	 *
	 * @param unknown_type $tid
	 * @return unknown_type
	 */
	static function isLeafSt($tid) {
		$sql = "SELECT COUNT(*) FROM tasks WHERE $tid <> task_id && task_parent = $tid";
		$r = db_loadResult($sql);
		if ($r > 0)
		return false;// swap the result
		else
		return true;// swap the result
	}

	static function getWBSIndexFromParent($tid, $pid=null) {
		if (is_null($tid))
		return "";
		if ($tid == "^") {
			$sql = "SELECT MAX(task_wbs_index) FROM tasks WHERE task_parent = task_id && $pid = task_project";
			$r = db_loadResult($sql);
		} else {
			$sql = "SELECT MAX(task_wbs_index) FROM tasks WHERE $tid = task_parent && task_parent != task_id";
			$r = db_loadResult($sql);
		}
		return $r+1;
	}

	static function getWBS($tid, $isRootChildren = false) {
		if (is_null($tid))
		return "";
		$sql = "SELECT task_parent, task_wbs_index FROM tasks WHERE $tid = task_id";
		$r = db_loadList($sql);

		$currentTask = $tid;
		$wbs = $r[0]['task_wbs_index'];//if ($tid == 24) echo strrev($wbs);
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

		// exploding the wbs
		$splittedWBS = explode(".", $wbs);
		$splittedWBS = array_reverse($splittedWBS);

		return implode(".", $splittedWBS);
	}

	/**
	 * Fixed the logic to support task with id > 9
	 *
	 * @param unknown_type $tid
	 * @return unknown_type
	 */
	static function getTaskLevel($tid) {

		if (is_null($tid))
		return "";
			
		$wbs = CTask::getWBS($tid);
		$splittedWBS = explode(".", $wbs);

		return count($splittedWBS);
	}

	static function getLevel($pid) {
	 $level = array ();
	 $level[0]=1;

		if (is_null($pid))
		return "";
			
		$sql = "SELECT task_id, task_parent FROM tasks WHERE $pid = task_project ORDER BY task_parent ASC";
		$r = db_loadList($sql);

		for($i=0;$i<count($r);$i++){
			$level[$i]=CTask::getTaskLevel($r[$i]['task_id']);
		}
		/*	if($r[$i]['task_parent']==$r[$i]['task_id']){
		 $level[$i]=1;
			}else{
			for($j=0;$j<count($r);$j++){
			if($r[$j]['task_id']==$r[$i]['task_parent']) $level[$i]=$level[$j]+1;}
			}
			}
			print_r($level);*/
		return max($level);
	}

	//funzione di aggiornamento della wbs in seguito ad un cambiamento
	function updateWBS($oldParent,$oldWBSi) {
		if (empty($this->task_id) || empty($this->task_wbs_index) || empty($this->task_parent) || empty($this->task_project))
		return null;

		$sql="SELECT task_id FROM tasks WHERE task_id=$this->task_id";
		$a = $this->task_id;
		//echo "00";
		// devo discriminare da una mod o un ins
		if ($this->task_parent == $a) {//echo "111";
			if ($oldParent != 0 && $this->task_parent == $oldParent) {//cambio di posizione ma non di padre
				if ($oldWBSi < $this->task_wbs_index)
				$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index - 1 WHERE task_parent = task_id && task_wbs_index <= $this->task_wbs_index && task_id <> $this->task_id && task_wbs_index >= $oldWBSi && task_project = $this->task_project";
				elseif ($oldWBSi > $this->task_wbs_index)
				$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index + 1 WHERE task_parent = task_id && task_wbs_index >= $this->task_wbs_index && task_id <> $this->task_id && task_wbs_index <= $oldWBSi && task_project = $this->task_project";
			}
			else //modifica di un task che è figlio di project
			$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index + 1 WHERE task_parent = task_id && task_wbs_index >= $this->task_wbs_index && task_id <> $this->task_id && task_project = $this->task_project";
		}
		else {//echo "222";
			if ($oldParent != 0 && $this->task_parent == $oldParent) {//echo "333";//cambio di posizione ma non di padre
				//se lo sposto a destra
				if ($oldWBSi < $this->task_wbs_index)
				$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index - 1 WHERE task_parent = $this->task_parent && task_wbs_index <= $this->task_wbs_index && task_id <> $this->task_id && task_wbs_index >= $oldWBSi && task_parent <> task_id && task_project = $this->task_project";
				elseif ($oldWBSi > $this->task_wbs_index)// se lo sposto a sinistra
				$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index + 1 WHERE task_parent = $this->task_parent && task_wbs_index >= $this->task_wbs_index && task_id <> $this->task_id && task_wbs_index <= $oldWBSi && task_parent <> task_id && task_project = $this->task_project";
			}
			else
			$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index + 1 WHERE task_parent = $this->task_parent && task_wbs_index >= $this->task_wbs_index && task_id <> $this->task_id && task_parent <> task_id && task_project = $this->task_project";
		}
		db_exec( $sql );

		// MODIFICA DEL LIVELLO SUPERIORE
		if ($this->task_parent != $oldParent && $oldParent != 0 && $oldWBSi != 0) {
			if ($oldParent == $this->task_id) //era figlio di project
			$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index - 1 WHERE task_wbs_index >= $oldWBSi && task_parent = task_id && task_id <> $this->task_id && task_project = $this->task_project";
			else
			$sql = "UPDATE tasks SET task_wbs_index = task_wbs_index - 1 WHERE task_parent = $oldParent && task_wbs_index >= $oldWBSi && task_parent <> task_id && task_project = $this->task_project";
			db_exec( $sql );// gestire i figli id project*/
		}
	}

	function getEffort($tid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('SUM(effort)');
		$q->addTable('user_tasks');
		$q->addWhere("task_id  = $tid");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
		$r = 0;
		return round($r,2);
	}

	function getBudget($tid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('SUM(ut.effort * pr.proles_hour_cost)');
		$q->addTable('user_tasks','ut');
		$q->addJoin('project_roles','pr','ut.proles_id = pr.proles_id');
		$q->addWhere("task_id  = $tid");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
		$r = 0;
		return round($r,2);
	}


	function gChild($tid, &$ar) {
		$child =array();//echo "<br>".$tid."<br>";
		if (count($ar) > 0) {
			$temp = $ar;
			foreach ($temp as $t => $tparent) {//echo $t." ";
				if ($tparent == $tid && $tid != $t) {
					unset($ar[$t]);
					$child = array_merge($this->gChild($t,$ar),$child);
					$child[] = $t;//print_r($child);echo"<br>";
				}
			}
		}
		return ($child);
	}

	function getChild($tid = null, $pid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return '';
		$pid = !empty($pid) ? $pid : $this->task_project;
		$q = new DBQuery;
		$q->addQuery('task_id, task_parent');
		$q->addTable('tasks');
		$q->addWhere("task_project = $pid");
		$ar = $q->loadHashList();
//		print_r($ar);
		$child = array();
		foreach ($ar as $t => $tparent) {
			if ($tparent == $tid && $tid != $t) {
				unset($ar[$t]);
				$child = array_merge($this->gChild($t,$ar),$child);
				$child[] = $t;
			}
		}
		return (implode($child,','));
	}

	function getStartDateFromTask($tid = null, $setTid) {
		if (($setTid == null) || ($setTid == ''))
		return "-";
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		$q = new DBQuery;
		$q->addQuery('task_start_date, task_id');
		$q->addTable('tasks');
		$q->addWhere("task_id IN ($setTid) AND !isnull( task_start_date ) AND task_start_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('task_start_date ASC');
		$ar = $q->loadList();
		return $ar[0];
	}

	function getFinishDateFromTask($tid = null, $setTid) {
		if (($setTid == null) || ($setTid == ''))
		return "-";
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		$q = new DBQuery;
		$q->addQuery('task_finish_date, task_id');
		$q->addTable('tasks');
		$q->addWhere("task_id IN ($setTid) AND !isnull( task_finish_date ) AND task_finish_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('task_finish_date DESC');
		$ar = $q->loadList();
		return $ar[0];
	}

	function getEffortFromTask($tid = null, $setTid) {
		if (($setTid == null) || ($setTid == ''))
		return 0;
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('SUM(ut.effort)');
		$q->addTable('tasks','t');
		$q->addJoin('user_tasks','ut','ut.task_id = t.task_id');
		$q->addWhere("t.task_id IN ($setTid) && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
		$r = 0;
		return round($r,2);
	}

	function getBudgetFromTask($tid = null, $setTid) {
		if (($setTid == null) || ($setTid == ''))
		return 0;
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('SUM(ut.effort * pr.proles_hour_cost)');
		$q->addTable('tasks','t');
		$q->addJoin('user_tasks','ut','ut.task_id = t.task_id');
		$q->addJoin('project_roles','pr','pr.proles_id = ut.proles_id');
		$q->addWhere("t.task_id IN ($setTid) && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
		$r = 0;
		return round($r,2);
	}

	function getActualStartDate($tid = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		$q = new DBQuery;
		$q->addQuery('tl.task_log_start_date, t.task_id');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		if (($setTid == null) || ($setTid == ''))
		$q->addWhere("t.task_id = $tid && !isnull( tl.task_log_start_date ) AND tl.task_log_start_date !=  '0000-00-00 00:00:00'");
		else
		$q->addWhere("t.task_id IN ($setTid) && !isnull( tl.task_log_start_date ) AND tl.task_log_start_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('tl.task_log_start_date ASC');
		$ar = $q->loadList();
		if(isset($ar[0]))
		return $ar[0];
		return "in progress";
	}

	function getActualFinishDate($tid = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		$q = new DBQuery;
		$q->addQuery('tl.task_log_finish_date, t.task_id');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		if (($setTid == null) || ($setTid == ''))
		$q->addWhere("t.task_id = $tid && !isnull( tl.task_log_finish_date ) AND tl.task_log_finish_date !=  '0000-00-00 00:00:00'");
		else
		$q->addWhere("t.task_id IN ($setTid) && !isnull( tl.task_log_finish_date ) AND tl.task_log_finish_date !=  '0000-00-00 00:00:00'");
		$q->addOrder('tl.task_log_finish_date DESC');
		$ar = $q->loadList();
		if(isset($ar[0]))
		return $ar[0];
		return null;
	}

	function getActualEffort($tid = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('SUM(tl.task_log_hours)');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		if (($setTid == null) || ($setTid == ''))
		$q->addWhere("t.task_id = $tid");
		else
		$q->addWhere("t.task_id IN ($setTid) && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
		$r = 0;
		return round($r,2);
	}

	function getActualCost($tid = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('SUM(tl.task_log_hours * pr.proles_hour_cost)');
		$q->addTable('tasks','t');
		$q->addJoin('task_log','tl','tl.task_log_task = t.task_id');
		$q->addJoin('project_roles','pr','pr.proles_id = tl.task_log_proles_id');
		if (($setTid == null) || ($setTid == ''))
		$q->addWhere("t.task_id = $tid");
		else
		$q->addWhere("t.task_id IN ($setTid) && (SELECT COUNT(*) FROM tasks AS tt WHERE t.task_id <> tt.task_id && tt.task_parent = t.task_id) < 1");
		$r = $q->loadResult();
		if ($r < 0 || is_null($r))
		$r = 0;
		return round($r,2);
	}

	function getEffortPerformanceIndex($tid = null, $tae = null, $te = null, $pr = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		if ($tae == null)
		$tae = $this->getActualEffort($tid,$setTid);
		if ($te == null)
		$te = $this->getEffort($tid,$setTid);
		if ($pr == null)
		$pr = $this->getProgress($tid,$te);
		if ($te == 0 || is_null($tae) || is_null($te) || is_null($pr))
		return "-";

		if ($pr == 0)
		return "-";
		if ($te == 0 || $pr == 0)
		return "-";
		return round(($tae * 100)/($te * $pr),2);
	}

	function getCostPerformanceIndex($tid = null, $tac = null, $tb = null, $pr = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		if ($tac == null)
		$tac = $this->getActualCost($tid,$setTid);
		if ($tb == null)
		$tb = $this->getBudget($tid,$setTid);
		if ($pr == null)
		$pr = $this->getProgress($tid);
		if ($tb == 0 || is_null($tac) || is_null($tb) || is_null($pr))
		return "-";

		if ($pr == 0)
		return "-";
		if ($tb == 0 || $pr == 0)
		return "-";
		return round(($tac * 100)/($tb * $pr),2);
	}

	function getTimePerformanceIndex($tid = null, $ptd = null, $psd = null, $pfd = null, $pafd = null, $pr = null, $setTid) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return "-";
		if ($ptd == null)
		$ptd = new CDate();
		if ($psd == null) {
			$psd = intval($this->task_start_date) ? new CDate($this->task_start_date) : null;
		}
		if ($pfd == null) {
			$pfd = intval($this->task_finish_date) ? new CDate($this->task_finish_date) : null;
		}
		if ($pafd == null) {
			$d = $this->getActualFinishDate(null,$setTid);
			$pafd = intval($d['task_log_finish_date']) ? new CDate($d['task_log_finish_date']) : null;
		}
		if ($pr == null)
		$pr = $this->getProgress($tid);
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

	function getProgress($tid = null, $te = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 0;
		$tp = 0;
		if (is_null($te))
		$te = $this->getEffort();
		if ($te == 0)
		return 0;
		$q = new DBQuery;
		$q->addQuery('task_id');
		$q->addTable('tasks');
		$q->addWhere("task_parent = $tid && task_parent != task_id");
		$ar = $q->loadColumn();
		$q->clear();
		if (count($ar) > 0) {
			$temp = $ar;
			foreach ($temp as $t) {
				$tte = $this->getEffort($t);
				$tp += ($this->getProgress($t, $tte) * $tte);
			}
		} else {//is_leaf
			$q->addQuery('task_log_progress');
			$q->addTable('task_log');
			$q->addWhere("task_log_task = $tid && !isnull( task_log_finish_date ) AND task_log_finish_date !=  '0000-00-00 00:00:00'");
			$q->addOrder('task_log_finish_date DESC');
			$q->addOrder('task_log_creation_date DESC');
			$r = $q->loadResult();
			$q->clear();
			return intval($r);
		}
		if ($te==0)
		return 0;
		return round($tp/$te,2);
	}

	static function getPr($tid, $te = null) {
		$tid = !empty($tid) ? $tid : 0;
		if ($tid == 0)
		return 0;
		$tp = 0;
		$q = new DBQuery;
		if (is_null($te)) {
			$q->addQuery('SUM(effort)');
			$q->addTable('user_tasks');
			$q->addWhere("task_id  = $tid");
			$te = $q->loadResult();
			if ($te <= 0 || is_null($te))
			return 0;
			$q->clear();
		}
		$q->addQuery('task_id');
		$q->addTable('tasks');
		$q->addWhere("task_parent = $tid && task_parent != task_id");
		$ar = $q->loadColumn();
		$q->clear();
		if (count($ar) > 0) {
			$temp = $ar;
			foreach ($temp as $t) {
				$q->addQuery('SUM(effort)');
				$q->addTable('user_tasks');
				$q->addWhere("task_id  = $t");
				$tte = $q->loadResult();
				if ($tte < 0 || is_null($tte))
				return 0;
				$q->clear();
				$tp += (CTask::getPr($t, $tte) * $tte);
			}
		} else {//is_leaf
			$q->addQuery('task_log_progress');
			$q->addTable('task_log');
			$q->addWhere("task_log_task = $tid && !isnull( task_log_finish_date ) AND task_log_finish_date !=  '0000-00-00 00:00:00'");
			$q->addOrder('task_log_finish_date DESC');
			$q->addOrder('task_log_creation_date DESC');
			$r = $q->loadResult();
			$q->clear();
			return intval($r);
		}
		//return $te;

		return round($tp/$te,2);
	}

	function isDefined($tid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 'Error';
		$msg='';
		// Defined
		// 1.4.1.1 V
		if ($this->task_start_date == '' || is_null($this->task_start_date) || $this->task_start_date == '0000-00-00 00:00:00')
		$msg.="- Task start date is not defined\n";
		// 1.4.1.2 V
		if ($this->task_finish_date == '' || is_null($this->task_finish_date) || $this->task_finish_date == '0000-00-00 00:00:00')
		$msg.="- Task finish date is not defined\n";
		// 1.4.1.3 V
		if ($this->task_start_date > $this->task_finish_date)
		$msg.="- Task start date is after task finish date\n";
		// 1.4.1.4 V
		// Automatically calculated
		// 1.4.1.5 V
		$r = 0;
		$q = new DBQuery();
		$q->addQuery("count(user_id)");
		$q->addTable("user_tasks");
		$q->addWhere("task_id = $tid");
		$r = $q->loadResult();
		if ($r <= 0)
		$msg.="- There aren't resources assigned to task\n";

		return $msg;

	}

	function isWellFormed($tid = null, $setTid = null, $fromProj = false) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		$setTid = !empty($setTid) ? $setTid : $this->getChild();

		if ($tid == 0)
		return 'Error';
		$msg='';
		$q = new DBQuery();
		$q->addQuery("t.task_id, t.task_name");
		$q->addTable("tasks","t");
		$q->addJoin("task_dependencies","td","td.dependencies_req_task_id = t.task_id");
		$q->addWhere("td.dependencies_req_task_id > 0 && t.task_project = $this->task_project && task_finish_date > (SELECT task_start_date FROM tasks AS tt WHERE tt.task_id = td.dependencies_task_id && t.task_project = tt.task_project && tt.task_id = $tid)");
		$ar = $q->loadList();
		//print_r($ar);echo "2";
		if (is_array($ar))
		foreach ($ar as $t) {
			$msg .= "- Task start date is before <a href=\"?m=tasks&a=view&task_id=".$t['task_id']."\"><span style=\"color:#990000;text-decoration:underline;\">".CTask::getWBS($t['task_id'])." ".$t['task_name']."</span></a> finish date\n";
		}

		if($this->isLeaf())
		return $msg;

		$ts = $this->getStartDateFromTask($tid,$setTid);
		if ($this->task_start_date > $ts['task_start_date'])
		$msg.="- Task start date is after <a href=\"?m=tasks&a=view&task_id=".$ts["task_id"]."\"><span style=\"color:#990000;text-decoration:underline;\">start date from tasks</span></a>\n";
		$tf = $this->getFinishDateFromTask($tid,$setTid);
		if ($this->task_finish_date < $tf['task_finish_date'])
		$msg.="- Task finish date is before <a href=\"?m=tasks&a=view&task_id=".$tf["task_id"]."\"><span style=\"color:#990000;text-decoration:underline;\">finish date from tasks</span></a>\n";
		if ($this->getBudget($tid,$setTid) <> $this->getBudgetFromTask($tid,$setTid))
		$msg.="- Task budget is different from budget from tasks\n";
		$tasks = explode(",",$setTid);

		$leafs = array();
		//print_r($tasks);echo"<br>";print_r($child);echo"<br>";
		if (is_array($tasks) && !empty($tasks)) {
			$res=array();
			$q->clear();
			$q->addQuery("CONCAT_WS(',',ut.user_id,ut.proles_id)");
			$q->addTable("user_tasks","ut");
			$q->addWhere("ut.task_id = $tid");
			$res=$q->loadColumn();
			if (count($res)>0) {
				$q->clear();
				$q->addQuery("DISTINCT(ut.task_id), t.task_name");
				$q->addTable("user_tasks","ut");
				$q->addJoin("tasks","t","t.task_id=ut.task_id");
				$q->addWhere("t.task_project = $this->task_project && ut.task_id IN (".implode($tasks,',').") &&
		  		CONCAT_WS(',',ut.user_id,ut.proles_id) NOT IN (".implode($res,',').")");
				$ar = $q->loadList();
				if (is_array($ar) && count($ar)>0)
				foreach ($ar as $t)
				$msg.="- Task doesn't contain a resource assigned to <a href=\"?m=tasks&a=view&task_id=".$t['task_id']."\"><span style=\"color:#990000;text-decoration:underline;\">".CTask::getWBS($t['task_id'])." ".$t['task_name']."</span></a>\n";

			}
			foreach ($tasks as $ch)
			if (CTask::isLeafSt($ch))
			$leafs[] = $ch;
			//print_r($leafs);echo"<br><br>";
			if (is_array($leafs) && !empty($leafs)) {
				$q->clear();
				$q->addQuery("CONCAT_WS(' ',u.user_first_name,u.user_last_name,p.proles_name)");
				$q->addTable("user_tasks","ut");
				$q->addJoin("tasks","t","t.task_id=ut.task_id");
				$q->addJoin("users","u","u.user_id=ut.user_id");
				$q->addJoin("project_roles","p","p.proles_id=ut.proles_id");
				$q->addWhere("t.task_project = $this->task_project && ut.task_id = $tid &&
		  		((ut.effort != (SELECT SUM(ut2.effort) FROM user_tasks as ut2 WHERE ut2.task_id IN (".implode($leafs,',').") && CONCAT_WS(',',ut.user_id,ut.proles_id) = CONCAT_WS(',',ut2.user_id,ut2.proles_id)))||
		  		(0 = (SELECT COUNT(*) FROM user_tasks as ut2 WHERE ut2.task_id IN (".implode($leafs,',').") && CONCAT_WS(',',ut.user_id,ut.proles_id) = CONCAT_WS(',',ut2.user_id,ut2.proles_id))))");
				$ar = $q->loadColumn();
				if (is_array($ar))
				foreach ($ar as $t)
				$msg.="- Task effort resource $t is different from the sum of its parts\n";
			}
		}
		$q->clear();
		$q->addQuery("COUNT(task_id)");
		$q->addTable("tasks");
		$q->addWhere("task_parent <> task_id && task_parent = $tid && task_project = $this->task_project");
		$t = $q->loadResult();
		if ($t == 1)
		$msg.="- Task has only one child\n";

		$q->clear();
		$q->addQuery("COUNT(task_log_id)");
		$q->addTable("task_log");
		$q->addWhere("task_log_task = $tid");
		if ($q->loadResult() > 0)
		$msg.="- Task isn't leaf and there are task logs assigned to it\n";
			
		$q->clear();
		$q->addQuery('task_finish_date');
		$q->addTable('tasks');
		$q->addWhere("task_parent = $tid && task_project = $this->task_project");
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
		$q->addWhere("model_type = 2 && model_association = 2 && model_pt = $tid");
		$d = $q->loadResult();
		$d1 = 0;
		if (intval($d)) {
			$d2 = new Cdate($d);
			$d1 = $d2->format($df);
		}
		if ($d1 > 0)
		if (!array_search($d1,$dates))
		$msg .=" - Task delivery date doesn't match with a child task finish date\n";
		 
		if (!$fromProj) {
			if (is_array($tasks))
			foreach ($tasks as $t) {
				$obj = new CTask();
				if (!$obj->load($t)) {
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
		}
		return $msg;

	}

	function isCostEffective($tid = null, $setTid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 'Error';
		$tb = $this->getBudget($tid,$setTid);
		$tac = $this->getActualCost($tid,$setTid);
		if ($tb < $tac)
		$msg.="- Task budget is smaller than actual cost\n";
		if ($this->getCostPerformanceIndex($tid,$tac,$tb,'',$setTid) > 1)
		$msg.="- Task cost performance index is greater than 1\n";
		return $msg;
	}

	function isEffortEffective($tid = null, $setTid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 'Error';
		$te = $this->getEffort($tid,$setTid);
		$tae = $this->getActualEffort($tid,$setTid);
		if ($te < $tae)
		$msg.="- Task effort is smaller than actual effort\n";
		if ($this->getCostPerformanceIndex($tid,$tae,$te,'',$setTid) > 1)
		$msg.="- Task effort performance index is greater than 1\n";
		return $msg;
	}

	function isTimeEffective($tid = null, $setTid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 'Error';
		$msg='';

		if ($this->getTimePerformanceIndex($tid,'','','','','',$setTid) > 1)
		$msg.="- Task time performance index is greater than 1\n";
		return $msg;

	}

	function isPlanEffective($tid = null, $setTid = null) {
		$tid = !empty($tid) ? $tid : $this->task_id;
		if ($tid == 0)
		return 'Error';
		$msg='';


		return $msg;

	}



}

/*********************************************************************************************************/
/**
 * CTask Class
 */
class CTaskLog extends CDpObject {
	var $task_log_id = NULL;
	var $task_log_task = NULL;
	var $task_log_name = NULL;
	var $task_log_description = NULL;
	var $task_log_creator = NULL;
	var $task_log_hours = NULL;
	var $task_log_edit_date = NULL;
	var $task_log_creation_date = NULL;
	var $task_log_start_date = NULL;
	var $task_log_finish_date = NULL;
	var $task_log_proles_id = NULL;
	var $task_log_problem = NULL;
	var $task_log_progress = NULL;
	var $task_log_related_url = NULL;

	function CTaskLog() {
		$this->CDpObject( 'task_log', 'task_log_id' );

		// ensure changes to checkboxes are honoured
		$this->task_log_problem = intval( $this->task_log_problem );
	}

	function updateProgress() {
		//AGGIORNAMENTO DELLE PERCENTUALI
	}

	// overload check method
	function check() {
		$this->task_log_hours = (float) $this->task_log_hours;
		return NULL;
	}

	function canDelete( &$msg, $oid=null, $joins=null ) {//OK non viene mai chiamata perchè canDelete di CDobj ha 4 par quindi non c'è overriding
		global $AppUI;

		// First things first.  Are we allowed to delete?
		$acl =& $AppUI->acl();
		if ( ! $acl->checkModuleItem('task_log', "delete", $oid)) {
			$msg = $AppUI->_( "noDeletePermission" );
			return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = "$k";
			$join = "";
			foreach( $joins as $table ) {
				$select .= ",\nCOUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}";
				$join .= "\nLEFT JOIN {$table['name']} ON {$table['joinfield']} = $k";
			}
			$sql = "SELECT $select\nFROM $this->_tbl\n$join\nWHERE $k = ".$this->$k." GROUP BY $k";

			$obj = null;
			if (!db_loadObject( $sql, $obj )) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$msg = $AppUI->_( "noDeleteRecord" ) . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}
}




/*********************************************************************************************************/
function closeOpenedTask($task_id){
	global $tasks_opened;
	global $tasks_closed;

	if(in_array($task_id, $tasks_opened))
	unset($tasks_opened[array_search($task_id, $tasks_opened)]);
	if(!in_array($task_id, $tasks_closed))
	$tasks_closed[] = $task_id;
}

//This kludgy function echos children tasks as threads

function showTaskPlanned( &$a, $level=0, $is_opened = true, $today_view = false, $notOpen = false, $canEdit, $showIncomplete = false, $roles, $taskview=true, $sdate, $edate) {//echo "^".$canEdit;
	global $AppUI, $dPconfig, $done, $query_string, $userAlloc, $showEditCheckbox;
	global $tasks_opened;
	global $tasks_closed;

	if ($showIncomplete && intval(CTask::getPr($a["task_id"]) >= 100))
	return '';

	$obj = new CTask();
	$te = $obj->getEffort($a['task_id']);
	//inserrire controlla unfinished
	$tpr = intval( $obj->getProgress($a['task_id'],$te));

	$tc = $obj->getBudget($a['task_id']);
	$df = $AppUI->getPref('SHDATEFORMAT');
	$df .= " " . $AppUI->getPref('TIMEFORMAT');
	$perms =& $AppUI->acl();
	$show_all_assignees = @$dPconfig['show_all_task_assignees'] ? true : false;

	$done[] = $a['task_id'];

	$start_date = intval( $a["task_start_date"] ) ? new CDate( $a["task_start_date"] ) : null;
	$finish_date = intval( $a["task_finish_date"] ) ? new CDate( $a["task_finish_date"] ) : null;

	$task_sdate= $start_date->format( FMT_TIMESTAMP_DATE );
	$task_edate= $finish_date->format( FMT_TIMESTAMP_DATE );

	if($taskview){
		if(($sdate>$task_edate)||($edate<$task_sdate)){
			return '';	}
	}

	// prepare coloured highlight of task time information
	$sign = 1;
	$style = "";
	$s = "\n<tr>";
	// edit icon

	//$canEdit = !getDenyEdit( 'tasks', $a["task_id"] );
	if ($canEdit) {
		$s .= "\n\t<td>";
		$s .= "\n\t\t<a href=\"?m=tasks&a=addedit&task_id={$a['task_id']}\">"
		. "\n\t\t\t".'<img src="./images/icons/pencil.gif" alt="'.$AppUI->_( 'Edit Task' ).'" border="0" width="12" height="12">'
		. "\n\t\t</a>";
		$s .= "\n\t</td>";
	}

	// pinned
	/*    $pin_prefix = $a['task_pinned']?'':'un';
	 $s .= "\n\t<td align=\"center\">";
	 $s .= "\n\t\t<a href=\"?m=tasks&pin=" . ($a['task_pinned']?0:1) . "&task_id={$a['task_id']}\">"
	 . "\n\t\t\t".'<img src="./images/icons/' . $pin_prefix . 'pin.gif" alt="'.$AppUI->_( $pin_prefix . 'pin Task' ).'" border="0" width="12" height="12">'
	 . "\n\t\t</a>";
	 $s .= "\n\t</td>";*/
	// percent complete
	$s .= "\n\t<td align=\"right\">".$tpr.'%</td>';
	// priority
	$s .= "\n\t<td align='center' nowrap='nowrap'>";
	if ($a["task_priority"] < 0 ) {
		$s .= "\n\t\t<img src=\"./images/icons/priority-". -$a["task_priority"] .'.gif" width=13 height=16>';
	} else if ($a["task_priority"] > 0) {
		$s .= "\n\t\t<img src=\"./images/icons/priority+". $a["task_priority"] .'.gif" width=13 height=16>';
	}
	$s .= @$a["file_count"] > 0 ? "<img src=\"./images/clip.png\" alt=\"F\">" : "";
	$s .= "</td>";
	// wbs
	$s .= '<td align="left" nowrap="nowrap">';
	$s .= CTask::getWBS($a['task_id']);
	$s .= "</td>";

	// dots
	if ($today_view)
	$s .= '<td width="50%">';
	else
	$s .= '<td width="90%">';
	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .='&nbsp;&nbsp;';
			//$s .= '<img src="./images/blank_space.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}

	// name link
	$alt = strlen($a['task_description']) > 80 ? substr($a["task_description"],0,80) . '...' : $a['task_description'];
	// instead of the statement below
	$alt = str_replace("\"", "&quot;", $alt);
	//	$alt = htmlspecialchars($alt);
	$alt = str_replace("\r", ' ', $alt);
	$alt = str_replace("\n", ' ', $alt);

	if (!$notOpen)
	$open_link = $is_opened ? "<a href='index.php$query_string&close_task_id=".$a["task_id"]."&reset_level=1'><img src='images/icons/collapse.gif' border='0' align='center' /></a>" : "<a href='index.php$query_string&open_task_id=".$a["task_id"]."&reset_level=1'><img src='images/icons/expand.gif' border='0' /></a>";
	else
	$open_link = "<img src='images/icons/nothing.gif' border='0' />";

	/*	if ($is_opened){
	 if(in_array($a['task_id'], $tasks_opened)) $open_link = "<a href='index.php$query_string&close_task_id=".$a["task_id"]."'><img src='images/icons/r-collapse.gif' border='0' align='center' /></a>";
	 else $open_link = "<a href='index.php$query_string&close_task_id=".$a["task_id"]."'><img src='images/icons/collapse.gif' border='0' align='center' /></a>";
	 }
	 else{
		if(in_array($a['task_id'], $tasks_closed)) $open_link = "<a href='index.php$query_string&open_task_id=".$a["task_id"]."'><img src='images/icons/r-expand.gif' border='0' align='center' /></a>";
		else $open_link = "<a href='index.php$query_string&open_task_id=".$a["task_id"]."'><img src='images/icons/expand.gif' border='0' align='center' /></a>";
		}*/

	if ($a["task_milestone"] > 0 ) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '&child_task=1" title="' . $alt . '"><b><i>' . $a["task_name"] . '</i></b></a> <img src="./images/icons/milestone.gif" border="0"></td>';
	} else if (!CTask::isLeafSt($a["task_id"])) {
		if (! $today_view)
		$s .= $open_link;
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '&child_task=1" title="' . $alt . '"><b>' . $a["task_name"] . '</b></a></td>';
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '&child_task=1" title="' . $alt . '">' . $a["task_name"] . '</a></td>';
	}
	if ($today_view) { // Show the project name
		$s .= '<td width="50%">';
		$s .= '<a href="./index.php?m=projects&a=view&project_id=' . $a['task_project'] . '">';
		$s .= '<span style="padding:2px;background-color:#' . $a['project_color_identifier'] . ';color:' . bestColor($a['project_color_identifier']) . '">' . $a['project_name'] . '</span>';
		$s .= '</a></td>';
	}

	//	$s .= '<td nowrap="nowrap" align="center">'. $a["user_username"] .'</td>';
	if ( isset($a['task_assigned_users']) && ($assigned_users = $a['task_assigned_users'])) {
		$a_u_tmp_array = array();
		$s .= '<td align="center" nowrap="nowrap">';
		$id = $a['task_id'];

		$sql="SELECT users.user_last_name, project_roles.proles_name FROM users,project_roles,user_tasks
	WHERE user_tasks.user_id=users.user_id AND user_tasks.proles_id=project_roles.proles_id AND user_tasks.task_id=".$a['task_id'];
		$db_roles = db_loadList($sql);

		if(count( $db_roles )>0){
			if($roles=="A"){
				$s .=  $db_roles[0][0].": ".$db_roles[0][1];
				for ( $i = 1; $i < count( $db_roles ); $i++) {
					$s .= '<br />';
					$s .= $db_roles[$i][0].": ".$db_roles[$i][1];
				}
			}

			if($roles=="N"){
				$s .= " <a href=\"javascript: void(0);\"  onClick=\"toggle_users('users_$id');\" title=\"" . join ( ', ', $a_u_tmp_array ) ."\">persons: ". (count( $db_roles )) ."</a>";
			}

			if($roles=="R"){
				$s .= $db_roles[0][1];
				for ( $i = 1; $i < count( $db_roles ); $i++) {
					$s .= '<br />';
					$s .= $db_roles[$i][1];
				}
			}
		}else{$s .="No Worker";}

		if(count( $assigned_users )>0){
			if($roles=="P"){
				$s .= $assigned_users[0]['user_first_name']." ".$assigned_users[0]['user_last_name'] ;
				for ( $i = 1; $i < count( $assigned_users ); $i++) {
					$s .= '<br />';
					$s .= $assigned_users[$i]['user_first_name']." ".$assigned_users[$i]['user_last_name'] ;
				}
			}
		}else{$s .="No Worker";}

		$s .= '<span style="display: none" id="users_' . $id . '">';
		$a_u_tmp_array[] = $assigned_users[0]['user_last_name'];
		for ( $i = 0; $i < count( $assigned_users ); $i++) {
			$a_u_tmp_array[] = $assigned_users[$i]['user_last_name'];
			$s .= '<br />';
			$s .= $assigned_users[$i]['user_last_name'] ;
		}
		$s .= '</span>';
		$s .= '</td>';
	} else if (! $today_view) {
		// No users asigned to task
		$s .= '<td align="center">-</td>';
	}

	$s .= '<td nowrap="nowrap" align="center" style="'.$style.'">'.($start_date ? $start_date->format( $df ) : '-').'</td>';
	// duration or milestone
	$s .= '<td nowrap="nowrap" align="center" style="'.$style.'">'.($finish_date ? $finish_date->format( $df ) : '-').'</td>';
	// effort
	$s .= '<td align="right" nowrap="nowrap">';
	$s .= $te." ph";
	$s .= "</td>";
	//cost
	$s .= '<td align="right" nowrap="nowrap">';
	$s .= $tc." ".$dPconfig['currency_symbol'];
	$s .= '</td>';

	// Assignment checkbox
	if ($showEditCheckbox) {
		$s .= "\n\t<td align='center'><input type=\"checkbox\" name=\"selected_task[{$a['task_id']}]\" value=\"{$a['task_id']}\"/></td>";
	}
	$s .= '</tr>';
	echo $s;
}

function showTaskActual( &$a, $level=0, $is_opened = true, $today_view = false, $notOpen = false, $canEdit, $showIncomplete = false,$roles, $sdate, $edate) {
	global $AppUI, $dPconfig, $done, $query_string, $userAlloc, $showEditCheckbox;

	$obj = new CTask();//echo $re;
	$te = $obj->getEffort($a['task_id']);
	//inserrire controlla unfinished
	$tpr = intval( $obj->getProgress($a['task_id'],$te));//$showIncomplete=false;
	if ($showIncomplete && intval($tpr) >= 100)
	return '';

	$tc = $obj->getBudget($a['task_id']);
	$now = new CDate();
	$df = $AppUI->getPref('SHDATEFORMAT');
	$df .= " " . $AppUI->getPref('TIMEFORMAT');
	$perms =& $AppUI->acl();
	$show_all_assignees = @$dPconfig['show_all_task_assignees'] ? true : false;

	$done[] = $a['task_id'];
	$childs = $obj->getChild($a['task_id'],$a['task_project']);

	$actual_start_date = $obj->getActualStartDate($a['task_id'], $childs);

	$task_date=substr($actual_start_date['task_log_start_date'],0,10);
	$task_date=str_replace("-","",$task_date);

	$start_date = intval( $a["task_start_date"] ) ? new CDate( $a["task_start_date"] ) : null;
	$finish_date = intval( $a["task_finish_date"] ) ? new CDate( $a["task_finish_date"] ) : null;

	$actual_start_date = intval( $actual_start_date['task_log_start_date'] ) ? new CDate( $actual_start_date['task_log_start_date'] ) : null;
	$actual_finish_date = $obj->getActualFinishDate($a['task_id'], $childs);
	$actual_finish_date = intval( $actual_finish_date['task_log_finish_date'] ) ? new CDate( $actual_finish_date['task_log_finish_date'] ) : null;

	$tae = $obj->getActualEffort($a['task_id'], $childs);//echo "-".$childs;
	$tac = $obj->getActualCost($a['task_id'], $childs);

	if((($sdate>$task_date)||($edate<$task_date))&&($task_date!=null))
	return '';

	// prepare coloured highlight of task time information
	$sign = 1;
	$style = "";

	if ((!$actual_finish_date && $now->after( $start_date ))||($now->after($finish_date)&& $tpr < 100)){
		$icon='images/icons/!t.png';
		$style = 'background-color:#cc6666;color:#ffffff';
	}

	if ($actual_start_date){
		if ($now->after( $actual_start_date ) && $tpr < 100) {//Running
			$icon='images/icons/!r.png';
			$style = 'background-color:#e6eedd';
		}

		if ((!$actual_finish_date && $now->after( $start_date ))||($now->after($finish_date)&& $tpr < 100)){
			$icon='images/icons/!t.png';
			$style = 'background-color:#cc6666;color:#ffffff';
		}

		if ($tpr == 100) {//Done
			$icon='images/icons/!v.png';
			$style = 'background-color:#aaddaa; color:#00000';
		}
	}

	$style3=$style;
	if (($tae > $te)||($tac > $tc)) {
		$icon='images/icons/!b.png';
		$style3='background-color:#bb0000; color:#ffffff;';
	}

	$s = "\n<tr>";
	// edit icon

	//$canEdit = !getDenyEdit( 'tasks', $a["task_id"] );
	if ($canEdit) {
		$s .= "\n\t<td>";
		$s .= "\n\t\t<a href=\"?m=tasks&a=addedit&task_id={$a['task_id']}\">"
		. "\n\t\t\t".'<img src="./images/icons/pencil.gif" alt="'.$AppUI->_( 'Edit Task' ).'" border="0" width="12" height="12">'
		. "\n\t\t</a>";
		$s .= "\n\t</td>";
	}

	// pinned
	/*$pin_prefix = $a['task_pinned']?'':'un';
	 $s .= "\n\t<td align=\"center\">";
	 $s .= "\n\t\t<a href=\"?m=tasks&pin=" . ($a['task_pinned']?0:1) . "&task_id={$a['task_id']}\">"
	 . "\n\t\t\t".'<img src="./images/icons/' . $pin_prefix . 'pin.gif" alt="'.$AppUI->_( $pin_prefix . 'pin Task' ).'" border="0" width="12" height="12">'
	 . "\n\t\t</a>";
	 $s .= "\n\t</td>";*/
	// percent complete
	$s .= '<td align="center" style="'.$style3.'">';
	if($icon) $s.='<img src="'.$icon.'">';
	$s.='</td>';
	$s .= "\n\t<td align=\"right\">".$tpr.'%</td>';
	// priority
	$s .= "\n\t<td align='center' nowrap='nowrap'>";
	if ($a["task_priority"] < 0 ) {
		$s .= "\n\t\t<img src=\"./images/icons/priority-". -$a["task_priority"] .'.gif" width=13 height=16>';
	} else if ($a["task_priority"] > 0) {
		$s .= "\n\t\t<img src=\"./images/icons/priority+". $a["task_priority"] .'.gif" width=13 height=16>';
	}
	$s .= @$a["file_count"] > 0 ? "<img src=\"./images/clip.png\" alt=\"F\">" : "";
	$s .= "</td>";
	// wbs
	$s .= "\n\t<td align='left' nowrap='nowrap'>";
	$s .= CTask::getWBS($a['task_id']);
	$s .= "</td>";

	// dots
	if ($today_view)
	$s .= '<td width="50%">';
	else
	$s .= '<td width="90%">';
	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .='&nbsp;&nbsp;';
			//$s .= '<img src="./images/blank_space.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}

	// name link
	$alt = strlen($a['task_description']) > 80 ? substr($a["task_description"],0,80) . '...' : $a['task_description'];
	// instead of the statement below
	$alt = str_replace("\"", "&quot;", $alt);
	//	$alt = htmlspecialchars($alt);
	$alt = str_replace("\r", ' ', $alt);
	$alt = str_replace("\n", ' ', $alt);
	if (!$notOpen)
	$open_link = ($is_opened)  ? "<a href='index.php$query_string&close_task_id=".$a["task_id"]."&actual=1'><img src='images/icons/collapse.gif' border='0' align='center' /></a>" : "<a href='index.php$query_string&open_task_id=".$a["task_id"]."&actual=1'><img src='images/icons/expand.gif' border='0' /></a>";
	else
	$open_link = "<img src='images/icons/nothing.gif' border='0' />";

	if ($a["task_milestone"] > 0 ) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '&child_task=1" title="' . $alt . '"><b><i>' . $a["task_name"] . '</i></b></a> <img src="./images/icons/milestone.gif" border="0"></td>';
	} else if (!CTask::isLeafSt($a["task_id"])) {
		if (! $today_view)
		$s .= $open_link;
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '&child_task=1" title="' . $alt . '"><b>' . $a["task_name"] . '</b></a></td>';
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '&child_task=1" title="' . $alt . '">' . $a["task_name"] . '</a></td>';
	}
	if ($today_view) { // Show the project name
		$s .= '<td width="50%">';
		$s .= '<a href="./index.php?m=projects&a=view&project_id=' . $a['task_project'] . '">';
		$s .= '<span style="padding:2px;background-color:#' . $a['project_color_identifier'] . ';color:' . bestColor($a['project_color_identifier']) . '">' . $a['project_name'] . '</span>';
		$s .= '</a></td>';
	}


	//	$s .= '<td nowrap="nowrap" align="center">'. $a["user_username"] .'</td>';
	if ( isset($a['task_assigned_users']) && ($assigned_users = $a['task_assigned_users'])) {
		$a_u_tmp_array = array();
		$s .= '<td align="center" nowrap="nowrap">';
		$id = $a['task_id'];

		$sql="SELECT users.user_last_name, project_roles.proles_name FROM users,project_roles,user_tasks
	WHERE user_tasks.user_id=users.user_id AND user_tasks.proles_id=project_roles.proles_id AND user_tasks.task_id=".$a['task_id'];
		$db_roles = db_loadList($sql);

		if(count( $db_roles )>0){
			if($roles=="A"){
				$s .=  $db_roles[0][0].": ".$db_roles[0][1];
				for ( $i = 1; $i < count( $db_roles ); $i++) {
					$s .= '<br />';
					$s .= $db_roles[$i][0].": ".$db_roles[$i][1];
				}
			}

			if($roles=="N"){
				$s .= " <a href=\"javascript: void(0);\"  onClick=\"toggle_users('users_$id');\" title=\"" . join ( ', ', $a_u_tmp_array ) ."\">persons: ". (count( $db_roles )) ."</a>";
			}

			if($roles=="R"){
				$s .= $db_roles[0][1];
				for ( $i = 1; $i < count( $db_roles ); $i++) {
					$s .= '<br />';
					$s .= $db_roles[$i][1];
				}
			}
		}else{$s .="No Worker";}

		if(count( $assigned_users )>0){
			if($roles=="P"){
				$s .= $assigned_users[0]['user_first_name']." ".$assigned_users[0]['user_last_name'] ;
				for ( $i = 1; $i < count( $assigned_users ); $i++) {
					$s .= '<br />';
					$s .= $assigned_users[$i]['user_first_name']." ".$assigned_users[$i]['user_last_name'] ;
				}
			}
		}else{$s .="No Worker";}

		$s .= '<span style="display: none" id="users_' . $id . '">';
		$a_u_tmp_array[] = $assigned_users[0]['user_last_name'];
		for ( $i = 0; $i < count( $assigned_users ); $i++) {
			$a_u_tmp_array[] = $assigned_users[$i]['user_last_name'];
			$s .= '<br />';
			$s .= $assigned_users[$i]['user_last_name'] ;
		}
		$s .= '</span>';
		$s .= '</td>';
	} else if (! $today_view) {
		// No users asigned to task
		$s .= '<td align="center">-</td>';
	}
	$s .= '<td nowrap="nowrap" align="center" >'/*style="'.$style.'">'*/.($actual_start_date ? $actual_start_date->format( $df ) : '-').'</td>';
	// duration or milestone
	$s .= '<td nowrap="nowrap" align="center" >'/*style="'.$style.'">'*/.($actual_finish_date ? $actual_finish_date->format( $df ) : '-').'</td>';
	// effort
	$style2="";//echo $tae. " ".$ta;
	//if ($tae > $te)
	//$style2 = 'background-color:#bb0000; color:#ffffff;';
	$s .= '<td align="right" nowrap="nowrap" style="'.$style2.'">';
	$s .= $tae." ph";
	$s .= "</td>";
	//cost
	$style2="";
	//if ($tac > $tc)
	//$style2 = 'background-color:#bb0000; color:#ffffff;';
	$s .= '<td align="right" nowrap="nowrap" style="'.$style2.'">';
	$s .= $tac." ".$dPconfig['currency_symbol'];
	$s .= "</td>";

	// Assignment checkbox
	if ($showEditCheckbox) {
		$s .= "\n\t<td align='center'><input type=\"checkbox\" name=\"selected_task[{$a['task_id']}]\" value=\"{$a['task_id']}\"/></td>";
	}
	$s .= '</tr>';
	echo $s;
}

function findchild( &$tarr, $parent, $level=0, $tview, $explodeTasks = false, $canEdit, $showIncomplete = false, $roles, $start, $end, $min_view = false){
	GLOBAL $projects;
	global $tasks_opened;
	global $tasks_closed;

	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {

		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
			if ((CTask::getTaskLevel($tarr[$x]["task_id"])<$explodeTasks&&(!in_array($tarr[$x]["task_id"], $tasks_closed)))) {
				$is_opened = true;}
				else
				$is_opened = in_array($tarr[$x]["task_id"], $tasks_opened);
				if ($tview)
				showTaskActual( $tarr[$x], $level, $is_opened,'','',$canEdit, $showIncomplete, $roles, $start, $end);
				else{
					if($min_view) showTaskPlanned( $tarr[$x], $level, $is_opened,'','',$canEdit, $showIncomplete, $roles, true, $start, $end);
					else showTaskPlanned( $tarr[$x], $level, $is_opened,'','',$canEdit, $showIncomplete, $roles, false, $start, $end);
				}
				if($is_opened) {// || !$tarr[$x]["task_dynamic"]){
					findchild( $tarr, $tarr[$x]["task_id"], $level, $tview, $explodeTasks,$canEdit, $showIncomplete, $roles, $start, $end);
				}
		}
	}
}
/* please throw this in an include file somewhere, its very useful */

function array_csort()   //coded by Ichier2003
{
	$args = func_get_args();
	$marray = array_shift($args);

	if ( empty( $marray )) return array();

	$i = 0;
	$msortline = "return(array_multisort(";
	$sortarr = array();
	foreach ($args as $arg) {
		$i++;
		if (is_string($arg)) {
			foreach ($marray as $row) {
				$sortarr[$i][] = $row[$arg];
			}
		} else {
			$sortarr[$i] = $arg;
		}
		$msortline .= "\$sortarr[".$i."],";
	}
	$msortline .= "\$marray));";

	eval($msortline);
	return $marray;
}

function sort_by_item_title( $title, $item_name, $item_type )
{
	global $AppUI,$project_id,$task_id,$min_view,$m;
	global $task_sort_item1,$task_sort_type1,$task_sort_order1;
	global $task_sort_item2,$task_sort_type2,$task_sort_order2;

	if ( $task_sort_item2 == $item_name ) $item_order = $task_sort_order2;
	if ( $task_sort_item1 == $item_name ) $item_order = $task_sort_order1;

	if ( isset( $item_order ) ) {
		if ( $item_order == SORT_ASC )
		echo '<img src="./images/icons/low.gif" width=13 height=16>';
		else
		echo '<img src="./images/icons/1.gif" width=13 height=16>';
	} else
	$item_order = SORT_DESC;

	/* flip the sort order for the link */
	$item_order = ( $item_order == SORT_ASC ) ? SORT_DESC : SORT_ASC;
	if ( $m == 'tasks' )
	{
		echo '<a href="./index.php?m=tasks&a=view&task_id='.$task_id;
	}
	else
	echo '<a href="./index.php?m=projects&a=view&project_id='.$project_id;

	echo '&task_sort_item1='.$item_name;
	echo '&task_sort_type1='.$item_type;
	echo '&task_sort_order1='.$item_order;
	if ( $task_sort_item1 == $item_name ) {
		echo '&task_sort_item2='.$task_sort_item2;
		echo '&task_sort_type2='.$task_sort_type2;
		echo '&task_sort_order2='.$task_sort_order2;
	} else {
		echo '&task_sort_item2='.$task_sort_item1;
		echo '&task_sort_type2='.$task_sort_type1;
		echo '&task_sort_order2='.$task_sort_order1;
	}
	echo '" class="hdr">';

	echo $AppUI->_($title);

	echo '</a>';
}

?>
