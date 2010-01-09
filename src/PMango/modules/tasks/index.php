<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      task module main page.

 File:       index.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to view PMango task.
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

$AppUI->savePlace();
$perms =& $AppUI->acl();
// retrieve any state parameters
$user_id = $AppUI->user_id;

if (isset( $_POST['f'] )) {
	$AppUI->setState( 'TaskIdxFilter', $_POST['f'] );
}
$f = $AppUI->getState( 'TaskIdxFilter' ) ? $AppUI->getState( 'TaskIdxFilter' ) : 'all';

if (isset( $_POST['f2'] )) {
	$AppUI->setState( 'CompanyIdxFilter', $_POST['f2'] );
}
$f2 = $AppUI->getState( 'CompanyIdxFilter' ) ? $AppUI->getState( 'CompanyIdxFilter' ) : 'all';

if (isset( $_GET['project_id'] )) {
	$AppUI->setState( 'TaskIdxProject', $_GET['project_id'] );
}
$project_id = $AppUI->getState( 'TaskIdxProject' ) ? $AppUI->getState( 'TaskIdxProject' ) : 0;

// get CCompany() to filter tasks by company
require_once( $AppUI->getModuleClass( 'groups' ) );
$obj = new CGroup();
//display the select list
$q = new DBQuery();
$q->addTable('groups');
$q->addQuery('group_id, group_name');
$q->addWhere('group_id IN ('.implode(',', array_keys($AppUI->user_groups)) . ')');
$companies = $q->loadHashList();
$q->clear();
$filters2 = arrayMerge(  array( 'all' => $AppUI->_('All Groups', UI_OUTPUT_RAW) ), $companies );

// setup the title block
?>

<?php
$titleBlock = new CTitleBlock( 'Tasks', 'applet-48.png', $m, "$m.$a" );

/*
if (dPgetParam($_GET, 'pinned') == 1)
	$titleBlock->addCell('<a href="?m=tasks">Implode tasks</a>');
else
	$titleBlock->addCell('<a href="?m=tasks&pinned=1">My pinned tasks</a>');*/


$titleBlock->addCell();
$titleBlock->addCell( $AppUI->_('Group') . ':' );
$titleBlock->addCell(
	arraySelect( $filters2, 'f2', 'size=1 class=text onChange="document.companyFilter.submit();"', $f2, false ), '',
	'<form action="?m=tasks" method="post" name="companyFilter">', '</form>'
);



$titleBlock->addCell();

if ( dPgetParam( $_GET, 'inactive', '' ) == 'toggle' )
	$AppUI->setState( 'inactive', $AppUI->getState( 'inactive' ) == -1 ? 0 : -1 );
$in = $AppUI->getState( 'inactive' ) == -1 ? '' : 'in';

$titleBlock->addCell( $AppUI->_('Task Filter') . ':' );
$titleBlock->addCell(
	arraySelect( $filters, 'f', 'size=1 class=text onChange="document.taskFilter.submit();"', $f, true ), '',
	'<form action="?m=tasks" method="post" name="taskFilter">', '</form>'
);
//$titleBlock->addCell();

/*if (dPgetParam($_GET, 'pinned') == 1)
        $titleBlock->addCrumb( '?m=tasks', 'All tasks' );
else
        $titleBlock->addCrumb( '?m=tasks&pinned=1', 'My pinned tasks' );
$titleBlock->addCrumb( "?m=tasks&inactive=toggle", "Show ".$in."active tasks" );*/
$u='';
$u2='&actual=1';
$m1='Planned view';
if (dPgetParam($_GET, 'actual') != 1) {
	$u .= $u2;
	$u2 ='';
	$m1 = 'Actual view';
}
$u3='';
$u4='&explode=1';
$m2='Implode tasks';
if (dPgetParam($_GET, 'explode') != 1) {
	$u3 .= $u4;
	$u4 ='';
	$m2 = 'Explode tasks';
}	

$titleBlock->addCrumb( '?m=tasks'.$u.$u4,$m1  );
$titleBlock->addCrumb( '?m=tasks'.$u3.$u2,$m2  );
$titleBlock->show();

// include the re-usable sub view
	$min_view = false;
	include("{$dPconfig['root_dir']}/modules/tasks/tasks.php");

?>
