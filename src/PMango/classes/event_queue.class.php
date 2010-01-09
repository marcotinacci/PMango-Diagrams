<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      event queue class

 File:       event_queue.class.php
 Location:   pmango/classes
 Started:    2005.09.30
 Author:     dotProject Team (ajdonnison)
 Type:       class

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   First version, unmodified from dotProject 2.0.1.

---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is
 released under GNU GPL, further information at: http://www.dotproject.net
 Other libraries used by PMango are redistributed under their own license.
 See ReadMe in the root folder for details.

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

---------------------------------------------------------------------------*/

/**
 * Event handling queue class.
 *
 * The event queue uses the table event_queue to manage
 * event notifications and other timed events, as well as
 * outgoing emails.
 *
 * Copyright 2005, the dotProject team.
 */

class EventQueue {

	var $table = 'event_queue';
	var $update_list = array();
	var $delete_list = array();
	var $event_count = 0;

	function EventQueue()
	{
	}

	/**
	 * Add an event to the queue.
	 *
	 * The callback can either be the name of a global function or the
	 * name of a class
	 * @param mixed $callback function to call when this event is due.
	 * @param mixed $args Arguments to pass to the callback
	 * @param string $module module, or originator of the event
	 * @param string $type type of event (to allow searching)
	 * @param integer $id id of originating event.
	 * @param integer $date Seconds since 1970 to trigger event.
	 * @param integer $repeat_interval seconds to repeat
	 * @param integer $repeat_count number of times to repeat
	 * @return integer queue id
	 */
	function add($callback, &$args, $module, $sysmodule = false, $id = 0, $type = '', $date = 0, $repeat_interval = 0, $repeat_count = 1)
	{
		global $AppUI;

		if (! isset($AppUI))
			$user_id = 0;
		else
			$user_id = $AppUI->user_id;

		if (is_array($callback)) {
			list($class, $method) = $callback;
			if (is_object($class))
				$class = get_class($class);
			$caller = $class . '::' . $method;
		} else {
			$caller = $callback;
		}

		$q = new DBQuery;
		$q->addTable($this->table);
		$q->addInsert('queue_owner', $user_id);
		$q->addInsert('queue_start', $date);
		$q->addInsert('queue_callback', $caller);
		$q->addInsert('queue_data', serialize($args));
		$q->addInsert('queue_repeat_interval', $repeat_interval);
		$q->addInsert('queue_repeat_count', $repeat_count);
		$q->addInsert('queue_module', $module);
		$q->addInsert('queue_type', $type);
		$q->addInsert('queue_origin_id', $id);
		if ($sysmodule)
			$q->addInsert('queue_module_type', 'system');
		else
			$q->addInsert('queue_module_type', 'module');
		if ($q->exec())
			$return =  db_insert_id();
		else
			$return =  false;
		$q->clear();
		return $return;
	}

	/**
	 * Remove the event from the queue. 
	 * 
	 */
	function remove($id)
	{
		$q = new DBQuery;
		$q->setDelete($this->table);
		$q->addWhere("queue_id = '$id'");
		$q->exec();
		$q->clear();
	}

	/**
	 * Find a queue record (or records) based upon the
	 * 
	 */
	function find($module, $type, $id = null)
	{
		$q = new DBQuery;
		$q->addTable($this->table);
		$q->addWhere("queue_module = '$module'");
		$q->addWhere("queue_type = '$type'");
		if (isset($id))
			$q->addWhere("queue_origin_id = '$id'");
		$found = $q->loadHashList();
		$q->clear();
		return $found;
	}

	/**
	 * Execute a queue entry.  This involves resolving the
	 * method to execute and passing the arguments to it.
	 */
	function execute(&$fields)
	{
		global $AppUI;

		if (isset($fields['queue_module_type'])
		&& $fields['queue_module_type'] == 'system')
			include_once $AppUI->getSystemClass($fields['queue_module']);
		else
			include_once $AppUI->getModuleClass($fields['queue_module']);

		$args = unserialize($fields['queue_data']);
		if (strpos($fields['queue_callback'], '::') !== false) {
			list($class, $method) = explode('::', $fields['queue_callback']);
			$object = new $class;
			return $object->$method($fields['queue_module'], $fields['queue_type'], $fields['queue_origin_id'], $fields['queue_owner'], $args);
		} else {
			$method = $fields['queue_callback'];
			return $method($fields['queue_module'], $fields['queue_type'], $fields['queue_origin_id'], $fields['queue_owner'], $args);
		}
	}

	/**
	 * Scans the queue for entries that are older than current date.
	 * If it finds one it tries to execute the attached function.
	 * If successful, the entry is removed from the queue, or if
	 * it is a repeatable event the repeat time is added to the
	 * start time and the repeat count (if set) is decremented.
	 */
	function scan()
	{
		$q = new DBQuery;
		$q->addTable($this->table);
		$now = time();
		$q->addWhere('queue_start < ' . $now);
		$rid = $q->exec();

		$this->event_count = 0;
		for ($rid; ! $rid->EOF; $rid->moveNext()) {
			if ($this->execute($rid->fields)) {
				$this->update_event($rid->fields);
				$this->event_count++;
			}
		}
		$q->clear();

		$this->commit_updates();
	}

	function update_event(&$fields)
	{
		if ($fields['queue_repeat_interval'] > 0 && $fields['queue_repeat_count'] > 0) {
			$fields['queue_start'] += $fields['queue_repeat_interval'];
			$fields['queue_repeat_count']--;
			$this->update_list[] = $fields;
		} else {
			$this->delete_list[] = $fields['queue_id'];
		}
	}

	function commit_updates()
	{
		$q = new DBQuery;
		if (count($this->delete_list)) {
			$q->setDelete($this->table);
			$q->addWhere("queue_id in (" . implode(',', $this->delete_list) . ")");
			$q->exec();
			$q->clear();
		}
		$this->delete_list = array();

		foreach ($this->update_list as $fields) {
			$q->addTable($this->table);
			$q->addUpdate('queue_repeat_count', $fields['queue_repeat_count']);
			$q->addUpdate('queue_repeat_interval', $fields['queue_repeat_interval']);
			$q->addWhere('queue_id = ' . $fields['queue_id']);
			$q->exec();
			$q->clear();
		}
		$this->update_list = array();
	}

}
?>
