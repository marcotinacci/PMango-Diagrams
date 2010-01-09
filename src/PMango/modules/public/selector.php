<?php 
/**
 ---------------------------------------------------------------------------

 PMango Project

 Title:      selector.

 File:       selector.php
 Location:   pmango\modules\public
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango selector.
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


function selPermWhere( $obj, $idfld, $namefield, $prefix = '' ) {
	global $AppUI;

	$allowed  = $obj->getAllowedRecords($AppUI->user_id, "$idfld, $namefield");
	if (count($allowed)) {
		$prfx = $prefix ? "$prefix." : "";
		return " $prfx$idfld IN (" . implode(",", array_keys($allowed)) . ") ";
	} else {
		return null;
	}
}

$debug = false;
$callback = dPgetParam( $_GET, 'callback', 0 );
$table = dPgetParam( $_GET, 'table', 0 );
$user_id = dPgetParam( $_GET, 'user_id', 0 );

$ok = $callback & $table;

$title = "Generic Selector";

$modclass = $AppUI->getModuleClass($table);
if ($modclass && file_exists ($modclass))
	require_once $modclass;

$q =& new DBQuery;
$q->addTable($table, 'a');

switch ($table) {
case 'groups':
	$obj =& new CGroup;
	$title = 'Group';
	$q->addQuery('group_id, group_name');
	$q->addOrder('group_name');
	$q->addWhere( selPermWhere( $obj, 'group_id', 'group_name' ));
	break;
/*case 'departments':
// known issue: does not filter out denied companies
	$title = 'Department';
	$company_id = dPgetParam( $_GET, 'company_id', 0 );
	//$ok &= $company_id;  // Is it safe to delete this line ??? [kobudo 13 Feb 2003]
	//$where = selPermWhere( 'companies', 'company_id' );
	$obj =& new CDepartment;
	$q->addWhere( selPermWhere( $obj, 'dept_id', 'dept_name' ));
	$q->addWhere( "dept_company = company_id ");
	$q->addTable('companies', 'b');

	$hide_company = dPgetParam( $_GET, 'hide_company', 0 );
	$q->addQuery('dept_id');
	if ( $hide_company == 1 ){
		$q->addQuery("dept_name");
	}else{
		$q->addQuery("CONCAT_WS(': ',company_name,dept_name) AS dept_name");
	}
	if ($company_id) {
		$q->addWhere("dept_company = $company_id");
		$q->addOrder("dept_name");
	} else {
		$q->addOrder("company_name, dept_name");
	}
	break;
case 'files':
	$title = 'File';
	$q->addQuery( 'file_id,file_name');
	$q->addOrder('file_name');
	break;
case 'forums':
	$title = 'Forum';
	$q->addQuery('forum_id,forum_name');
	$q->addOrder('forum_name');
	break;*/
case 'projects':
	$project_group = dPgetParam( $_GET, 'project_group', 0 );

	$title = 'Project';
	$obj =& new CProject;
	$q->addQuery('a.project_id, project_name');
	$q->addOrder('project_name');
	if ($user_id > 0) {
		$q->addTable('user_projects', 'b');
		$q->addWhere('b.project_id = a.project_id');
		$q->addWhere("b.user_id = $user_id");
	}
	$q->addWhere( selPermWhere( $obj, 'project_id', 'project_name', 'a' ));
	if ($project_group) {
		$q->addWhere( "project_group = $project_group");
	}
	break;
	
case "tasks":
	$task_project = dPgetParam( $_GET, 'task_project', 0 );
	$title = 'Task';
	$q->addQuery( 'task_id,task_name');
	$q->addOrder('task_name');
	if ($task_project)
		$q->addWhere("task_project = $task_project");
	break;
case 'users':
	$title = 'User';
	$q->addQuery("user_id, CONCAT_WS(' ',user_first_name,user_last_name)");
	$q->addOrder('user_first_name');
	break;
case 'SGD':
	$title = 'Document';
	$q->addQuery('SGD_id, SGD_name');
	$q->addOrder('SGD_name');
	break;
default:
	$ok = false;
	break;
}

if (!$ok) {
	echo "Incorrect parameters passed\n";
	if ($debug) {
		echo "<br />callback = $callback \n";
		echo "<br />table = $table \n";
		echo "<br />ok = $ok \n";
	}
} else {
	$list = arrayMerge( array( 0=>$AppUI->_( '[none]' )), $q->loadHashList( ) );
	echo db_error();
?>
<script language="javascript">
	function setClose(key, val){
		window.opener.<?php echo $callback;?>(key,val);
		window.close();
	}

	window.onresize = window.onload = function setHeight(){

		if (document.compatMode && document.compatMode != "BackCompat" && document.documentElement.clientHeight)
			var wh = document.documentElement.clientHeight;
		else
			var wh = document.all ? document.body.clientHeight : window.innerHeight;
   
		var selector = document.getElementById("selector");
		var count = 0;
		obj = selector;
		while(obj!=null){
			count += obj.offsetTop;
			obj = obj.offsetParent;
		}
		selector.style.height = (wh - count - 5) + "px";

	}

</script>
<form name="frmSelector">
<b><?php echo $AppUI->_( 'Select' ).' '.$AppUI->_( $title ).':'?></b>
<table width="100%">
<tr>
	<td>
		<div style="white-space:normal; overflow:auto; "  id="selector">
		<ul style="padding-left:0px">
		<?php
			if (count( $list ) > 1) {
		//		echo arraySelect( $list, 'list', ' size="8"', 0 );
				foreach ($list as $key => $val) {
					echo "<li><a href=\"javascript:setClose('$key','".addslashes($val)."');\">$val</a></li>\n";
				}
			} else {
				echo $AppUI->_( "no$table" );
			}
		?>
		</ul>
		</div>
	</td>
	<td valign="bottom" align="right">
				<input type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()" />
	</td>
</tr>
</table>
</form>

<?php } ?>

