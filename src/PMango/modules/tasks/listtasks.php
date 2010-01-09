<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view task list.

 File:       listtask.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango task list.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.
   
---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
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

---------------------------------------------------------------------------
*/
$proj = $_GET['project'];
if ($proj > 0) {
	$perms =& $AppUI->acl();
	$q = new DBQuery();
	$q->addQuery('project_group');
	$q->addTable('projects');
	$q->addWhere('project_id ='.$proj);
	$pg = $q->loadResult();
	$q->clear();
	if (! $perms->checkModule('tasks', 'view','',intval($pg),1))
		$AppUI->redirect("m=public&a=access_denied");
	
	
	$sql = 'SELECT task_id, task_name
	        FROM tasks';
	if ($proj != 0)
	  $sql .= ' WHERE task_project = ' . $proj;
	$tasks = db_loadList($sql);
}
?>

<script language="JavaScript">
function loadTasks()
{
  var tasks = new Array();
  var sel = parent.document.forms['form'].new_task;
  while ( sel.options.length )
    sel.options[0] = null;
    
  sel.options[0] = new Option('[top task]', 0);
  <?php
    $i = 0;
    foreach($tasks as $task)
    {
      ++$i;
    ?>
  sel.options[<?php echo $i; ?>] = new Option('<?php echo $task['task_name']; ?>', <?php echo $task['task_id']; ?>);
    <?php
    }
    ?>
  }
  
  loadTasks();
</script>
