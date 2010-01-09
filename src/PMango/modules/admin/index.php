<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      home page for PMango user

 File:       index.php
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

$perms =& $AppUI->acl();
if (! $perms->checkModule($m, 'view','',$AppUI->user_groups[-1]))
	$AppUI->redirect('m=public&a=access_denied');
if (! $perms->checkModule('users', 'view','',$AppUI->user_groups[-1]))
	$AppUI->redirect('m=public&a=access_denied');

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'UserIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserIdxTab' ) !== NULL ? $AppUI->getState( 'UserIdxTab' ) : 0;

if (isset( $_GET['stub'] )) {
    $AppUI->setState( 'UserIdxStub', $_GET['stub'] );
    $AppUI->setState( 'UserIdxWhere', '' );
} else if (isset( $_POST['where'] )) { 
    $AppUI->setState( 'UserIdxWhere', $_POST['where'] );
    $AppUI->setState( 'UserIdxStub', '' );
}
$stub = $AppUI->getState( 'UserIdxStub' );
$where = $AppUI->getState( 'UserIdxWhere' );

if (isset( $_GET['orderby'] )) {
    $AppUI->setState( 'UserIdxOrderby', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'UserIdxOrderby' ) ? $AppUI->getState( 'UserIdxOrderby' ) : 'user_username';

// Pull First Letters
$let = ":";
$q  = new DBQuery;
$q->addTable('users','u');
$q->addQuery('DISTINCT UPPER(SUBSTRING(user_username, 1, 1)) AS L');
$arr = $q->loadList();
foreach( $arr as $L ) {
    $let .= $L['L'];
}

$q  = new DBQuery;
$q->addTable('users','u');
$q->addQuery('DISTINCT UPPER(SUBSTRING(user_first_name, 1, 1)) AS L');
$arr = $q->loadList();
foreach( $arr as $L ) {
    if ($L['L'])
	$let .= strpos($let, $L['L']) ? '' : $L['L'];
}

$q  = new DBQuery;
$q->addTable('users','u');
$q->addQuery('DISTINCT UPPER(SUBSTRING(user_last_name, 1, 1)) AS L');
$arr = $q->loadList();
foreach( $arr as $L ) {
    if ($L['L'])
	$let .= strpos($let, $L['L']) ? '' : $L['L'];
}

$a2z = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$a2z .= "\n<tr>";
$a2z .= '<td width="100%" align="right">' . $AppUI->_('Show'). ': </td>';
$a2z .= '<td><a href="./index.php?m=admin&stub=0">' . $AppUI->_('All') . '</a></td>';
for ($c=65; $c < 91; $c++) {
	$cu = chr( $c );
	$cell = strpos($let, "$cu") > 0 ?
		"<a href=\"?m=admin&stub=$cu\">$cu</a>" :
		"<font color=\"#999999\">$cu</font>";
	$a2z .= "\n\t<td>$cell</td>";
}
$a2z .= "\n</tr>\n</table>";

// setup the title block
$titleBlock = new CTitleBlock( 'User Management', 'helix-setup-users.png', $m, "$m.$a");
if ($perms->checkModule('admin', 'add', '', $AppUI->user_groups[-1]))
	$titleBlock->addCrumb("?m=admin&a=addedituser","Add user");
$where = dPformSafe( $where, true );
$titleBlock->addCrumb("?m=admin&reload=1","Reload user");

$titleBlock->addCell( $a2z );
$titleBlock->show();

?>
<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($perms->checkModule('admin', 'delete','',$AppUI->user_groups[-1])) {
?>
function delMe( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('User', UI_OUTPUT_JS);?> " + y + "?" )) {
		document.frmDelete.user_id.value = x;
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<?php
$extra = '<td align="right" width="100%"><input type="button" class=button value="'.$AppUI->_('add user').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>';

// tabbed information boxes
$tabBox = new CTabBox( "?m=admin", "{$dPconfig['root_dir']}/modules/admin/", $tab );
$tabBox->add( 'vw_active_usr', 'Active Users' );
$tabBox->add( 'vw_inactive_usr', 'Inactive Users' );
$tabBox->add( 'vw_usr_log', 'User Log' );
$tabBox->show( $extra );

?>

<form name="frmDelete" action="./index.php?m=admin" method="post">
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user_id" value="0" />
</form>