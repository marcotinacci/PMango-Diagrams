<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      translation management.

 File:       translate.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango translation.
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

if (!$perms->checkModule('system','edit','',$AppUI->user_groups[-1],1)) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$module = isset( $_REQUEST['module'] ) ? $_REQUEST['module'] : 'admin';
$lang = isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : 'en';

$AppUI->savePlace( "m=system&a=translate&module=$module&lang=$lang" );

// read the installed modules
$modules = arrayMerge( array( 'common', 'styles' ), $AppUI->readDirs( 'modules' ));

// read the installed languages
$locales = $AppUI->readDirs( 'locales' );

ob_start();
// read language files from module's locale directory preferrably
	if ( file_exists( "{$dPconfig['root_dir']}/modules/$modules[$module]/locales/en.inc" ) )
	{
		@readfile( "{$dPconfig['root_dir']}/modules/$modules[$module]/locales/en.inc" );
	}
	else
	{
		@readfile( "{$dPconfig['root_dir']}/locales/en/$modules[$module].inc" );
	}
	eval( "\$english=array(".ob_get_contents()."\n'0');" );
ob_end_clean();

$trans = array();
foreach( $english as $k => $v ) {
	if ($v != "0") {
		$trans[ (is_int($k) ? $v : $k) ] = array(
			'english' => $v
		);
	}
}

//echo "<pre>";print_r($trans);echo "</pre>";die;

if ($lang != 'en') {
	ob_start();
// read language files from module's locale directory preferrably
		if ( file_exists( "{$dPconfig['root_dir']}/modules/$modules[$module]/locales/$lang.inc" ) )
		{
			@readfile( "{$dPconfig['root_dir']}/modules/$modules[$module]/locales/$lang.inc" );
		}
		else
		{
			@readfile( "{$dPconfig['root_dir']}/locales/$lang/$modules[$module].inc" );
		}
		eval( "\$locale=array(".ob_get_contents()."\n'0');" );
	ob_end_clean();

	foreach( $locale as $k => $v ) {
		if ($v != "0") {
			$trans[$k]['lang'] = $v;
		}
	}
}
ksort($trans);

$titleBlock = new CTitleBlock( 'Translation Management', 'rdf2.png', $m, "$m.$a" );
$titleBlock->addCell(
	$AppUI->_( 'Module' ), '',
	'<form action="?m=system&a=translate" method="post" name="modlang">', ''
);
$titleBlock->addCell(
	arraySelect( $modules, 'module', 'size="1" class="text" onchange="document.modlang.submit();"', $module )
);
$titleBlock->addCell(
	$AppUI->_( 'Language' )
);
$temp = $AppUI->setWarning( false );
$titleBlock->addCell(
	arraySelect( $locales, 'lang', 'size="1" class="text" onchange="document.modlang.submit();"', $lang, true ), '',
	'', '</form>'
);
$AppUI->setWarning( $temp );

$titleBlock->addCrumb( "?m=system", "System admin" );
$titleBlock->show();
?>

<table width="100%" border="0" cellpadding="1" cellspacing="1" class="tbl">
<tr>
	<th width="15%" nowrap><?php echo $AppUI->_( 'Abbreviation' );?></th>
	<th width="40%" nowrap>English <?php echo $AppUI->_( 'String' );?></th>
	<th width="40%" nowrap><?php echo $AppUI->_( $locales[$lang] ).' '.$AppUI->_( 'String' );?></th>
	<th width="5%" nowrap><?php echo $AppUI->_( 'delete' );?></th>
</tr>
<form action="?m=system&a=translate_save" method="post" name="editlang">
<input type="hidden" name="module" value="<?php echo $modules[$module];?>" />
<input type="hidden" name="lang" value="<?php echo $lang;?>" />
<?php
$index = 0;
if ($lang == 'en') {
	echo "<tr>\n";
	echo "<td><input type=\"text\" name=\"trans[$index][abbrev]\" value=\"\" size=\"20\" class=\"text\" /></td>\n";
	echo "<td><input type=\"text\" name=\"trans[$index][english]\" value=\"\" size=\"40\" class=\"text\" /></td>\n";
	echo "<td colspan=\"2\">New Entry</td>\n";
	echo "</tr>\n";
}

$index++;
foreach ($trans as $k => $langs){
?>
<tr>
	<td><?php
		if ($k != @$langs['english']) {
			$k = dPformSafe( $k, true );
			if ($lang == 'en') {
				echo "<input type=\"text\" name=\"trans[$index][abbrev]\" value=\"$k\" size=\"20\" class=\"text\" />";
			} else {
				echo $k;
			}
		} else {
			echo '&nbsp;';
		}
	?></td>
	<td><?php
		//$langs['english'] = htmlspecialchars( @$langs['english'], ENT_QUOTES );
			$langs['english'] = dPformSafe( @$langs['english'], true );
		if ($lang == 'en') {
			if (strlen($langs['english']) < 40) {
				echo "<input type=\"text\" name=\"trans[$index][english]\" value=\"{$langs['english']}\" size=\"40\" class=\"text\" />";
			} else {
			  $rows = round(strlen($langs['english']/35)) +1 ;
			  echo "<textarea name=\"trans[$index][english]\"  cols=\"40\" class=\"small\" rows=\"$rows\">".$langs['english']."</textarea>";
			}
		} else {
			echo $langs['english'];
			echo "<input type=\"hidden\" name=\"trans[$index][english]\" value=\""
				.($k ? $k : $langs['english'])
				."\" size=\"20\" class=\"text\" />";
		}
	?></td>
	<td><?php
		if ($lang != 'en') {
			$langs['lang'] = dPformSafe( @$langs['lang'], true );
			if (strlen($langs['lang']) < 40) {
				echo "<input type=\"text\" name=\"trans[$index][lang]\" value=\"{$langs['lang']}\" size=\"40\" class=\"text\" />";
			} else {
			  $rows = round(strlen($langs['lang']/35)) +1 ;
			  echo "<textarea name=\"trans[$index][lang]\"  cols=\"40\" class=\"small\" rows=\"$rows\">".$langs['lang']."</textarea>";
			}
		}
	?></td>
	<td align="center"><?php echo "<input type=\"checkbox\" name=\"trans[$index][del]\" />";?></td>
</tr>
<?php
	$index++;
}
?>
<tr>
	<td colspan="4" align="right">
		<input type="submit" value="<?php echo $AppUI->_( 'submit' );?>" class="button" />
	</td>
</tr>
</form>
</table>
