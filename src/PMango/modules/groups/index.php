<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      home page of group module

 File:       index.php
 Location:   pmango\modules\groups
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, modified to manage PMango groups.
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

$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'CompIdxOrderDir' ) ? ($AppUI->getState( 'CompIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'CompIdxOrderDir', $orderdir);
}
$orderby         = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'group_name';
$orderdir        = $AppUI->getState( 'CompIdxOrderDir' ) ? $AppUI->getState( 'CompIdxOrderDir' ) : 'asc';

if(isset($_REQUEST["owner_filter_id"])){
	$AppUI->setState("owner_filter_id", $_REQUEST["owner_filter_id"]);
	$owner_filter_id = $_REQUEST["owner_filter_id"];
} else {
	$owner_filter_id = $AppUI->getState( 'owner_filter_id');
	if (! isset($owner_filter_id)) {
		$owner_filter_id = $AppUI->_("All", UI_OUTPUT_RAW);
		$AppUI->setState('owner_filter_id', $owner_filter_id);
	}
}
// load the company types
$types = dPgetSysVal( 'GroupType' );

// get any records denied from viewing
$obj = new CGroup();
//$deny = $obj->getDeniedRecords( $AppUI->user_id ); 


$perms =& $AppUI->acl();
$owner_list = array( 0 => $AppUI->_("All", UI_OUTPUT_RAW)) + $perms->getPermittedUsers("groups", $AppUI->user_groups[-1]); // db_loadHashList($sql);
$owner_combo = arraySelect($owner_list, "owner_filter_id", "class='text' onchange='javascript:document.searchform.submit()'", $owner_filter_id, false);

// setup the title block
$titleBlock = new CTitleBlock( 'Groups', 'handshake.png', $m, "$m.$a" );
$titleBlock->addCell("<form name='searchform' action='?m=groups' method='post'>						
								<td align='right' nowrap='nowrap'>".$AppUI->_("Owner filter:")." </td>
								<td align='right' nowrap='nowrap'>".$owner_combo." </td>							
                      </form>");
				
$canAddGroup = 0;
foreach ($AppUI->user_groups as $g => $sc)
	if (!$canAddGroup)
		$canAddGroup = $perms->checkModule('groups', 'add','',$sc);
	else
		break;
if ($canAddGroup) {
	$titleBlock->addCrumb("?m=groups&a=addedit",$AppUI->_('New group'));
}

$titleBlock->show();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompaniesIdxTab', $_GET['tab'] );
}
$companiesTypeTab = defVal( $AppUI->getState( 'CompaniesIdxTab' ),  0 );

// $tabTypes = array(getCompanyTypeID('Client'), getCompanyTypeID('Supplier'), 0);
$companiesType = $companiesTypeTab;

$tabBox = new CTabBox( "?m=groups", dPgetConfig('root_dir')."/modules/groups/", $companiesTypeTab );
if ($tabbed = $tabBox->isTabbed()) {
	$add_na = true;
	if (isset($types[0])) { // They have a Not Applicable entry.
		$add_na = false;
		$types[] = $types[0];
	}
	$types[0] = "All Groups";
	if ($add_na)
		$types[] = "Not Applicable";
}
$type_filter = array();
foreach($types as $type => $type_name){
	$type_filter[] = $type;
	$tabBox->add('vw_companies', $type_name);
}

$tabBox->show();
?>
		