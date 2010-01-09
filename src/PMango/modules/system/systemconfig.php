<?php  
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      system configuration.

 File:       systemconfig.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.11.05 Lorenzo
   Third version, modified to manage two parameters to setting width and height image.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango system configuration.
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
// check permissions
if (!$perms->checkModule('system','edit','',$AppUI->user_groups[-1],1)) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$dPcfg = new CConfig();

// retrieve the system configuration data
$rs = $dPcfg->loadAll('config_group');

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ConfigIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ConfigIdxTab' ) !== NULL ? $AppUI->getState( 'ConfigIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ConfigIdxTab' ) );

$titleBlock = new CTitleBlock('System Configuration', 'control-center.png', $m);
$titleBlock->addCrumb( "?m=system", "System admin" );
//$titleBlock->addCrumb( "?m=system&a=addeditpref", "Default user preferences" );
$titleBlock->show();

if (is_dir("$baseDir/install")) {
	$AppUI->setMsg("You have not removed your install directory, this is a major security risk!", UI_MSG_ALERT);
	echo "<span class='error'>" . $AppUI->getMsg() . "</span>\n";
}

echo $AppUI->_("syscfg_intro");
echo "<br />&nbsp;<br />";


// prepare the automated form fields based on db system configuration data
$output  = null;
//array che contiene gli id dei campi di configurazione che non sono visualizzati quindi non modificabili
//perchè non implementati in PMango mentre in dot2 si
$notDisplay = array(49,53,60,65,66,82,83,84,85,86,87,91,92,93,94,95,90,106,107,108,98,99,100,101,102,103,104,89,105,74,75,76);
$last_group = '';	   	  		   
foreach ($rs as $c) {
 if (!in_array($c['config_id'],$notDisplay)) {
 	$tooltip = "title='".$AppUI->_($c['config_name'].'_tooltip')."'";
	// extraparse the checkboxes and the select lists
	$value = '';
	switch ($c['config_type']) {
		case 'select':
			// Build the select list.
			$entry = "<select class='text' name='dPcfg[{$c['config_name']}]'>\n";
			// Find the detail relating to this entry.
			$children = $dPcfg->getChildren($c['config_id']);
			foreach ($children as $child) {
				$entry .= "<option value='{$child['config_list_name']}'";
				if ($child['config_list_name'] == $c['config_value'])
					$entry  .= " selected='selected'";
				$entry .= ">" . $AppUI->_($child['config_list_name'] . '_item_title') . "</option>\n";
			}
			$entry .= "</select>";
			break;
		case 'checkbox':
			$extra = ($c['config_value'] == 'true') ? "checked='checked'" : '';
			$value = 'true';
			// allow to fallthrough
		default:
			if (! $value)
				$value = $c['config_value'];
			$entry = "<input class='text' type='{$c['config_type']}' name='dPcfg[{$c['config_name']}]' value='$value' $tooltip $extra/>";
			break;
	}

	if ($c['config_group'] != $last_group) {
		$output .="<tr><td colspan='2'><b>" . $AppUI->_($c['config_group'] .'_group_title') . "</b></td></tr>\n";
		$last_group = $c['config_group'];
	}//.$c['config_id']deve essere inserito prima di $AppUI->_($c['config_name']
	$output .= "<tr>
			<td class='item' width='20%'>".$AppUI->_($c['config_name'].'_title')."</td>
            		<td align='left'>
				$entry
				<a href='#' onClick=\"javascript:window.open('?m=system&a=systemconfig_help&dialog=1&cn={$c['config_name']}', 'contexthelp', 'width=600, height=200, left=50, top=50, scrollbars=yes, resizable=yes')\" $tooltip>(?)</a>
				<input class='text' type='hidden'  name='dPcfgId[{$c['config_name']}]' value='{$c['config_id']}' />
			</td>
        </tr>
	";

	}
}
echo '<form name="cfgFrm" action="index.php?m=system&a=systemconfig" method="post">';
?>
<input type="hidden" name="dosql" value="do_systemconfig_aed" />
<table cellspacing="0" cellpadding="3" border="0" class="std" width="100%" align="center">
	<?php
	echo $output;
	?>
	<tr>
 		<td align="right" colspan="2"><input class="button" type="submit" name="do_save_cfg" value="<?php echo $AppUI->_('Save');?>" /></td>
	</tr>
</table></form>
