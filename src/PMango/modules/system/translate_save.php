<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      save translate information.

 File:       translate_save.php
 Location:   pmango\modules\system
 Started:    2005.09.30
 Author:     dotProject Team (Andrew Eddie)
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
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

$module = isset( $HTTP_POST_VARS['module'] ) ? $HTTP_POST_VARS['module'] : 0;
$lang = isset( $HTTP_POST_VARS['lang'] ) ? $HTTP_POST_VARS['lang'] : 'en';

$trans = isset( $HTTP_POST_VARS['trans'] ) ? $HTTP_POST_VARS['trans'] : 0;
//echo '<pre>';print_r( $trans );echo '</pre>';die;

// save to core locales if a translation exists there, otherwise save
// into the module's local locale area if it exists.  If not then
// the core table is updated.
$core_filename = "$baseDir/locales/$lang/$module.inc";
if ( file_exists( $core_filename ) ) {
	$filename = $core_filename;
} else {
	$mod_locale = "$baseDir/modules/$module/locales";
	if ( is_dir($mod_locale))
		$filename = "$baseDir/modules/$module/locales/$lang.inc";
	else
		$filename = $core_filename;
}

$fp = fopen ($filename, "wt");

if (!$fp) {
	$AppUI->setMsg( "Could not open locales file ($filename) to save.", UI_MSG_ERROR );
	$AppUI->redirect( "m=system" );
}

$txt = "##\n## DO NOT MODIFY THIS FILE BY HAND!\n##\n";

if ($lang == 'en') {
// editing the english file
	foreach ($trans as $langs) {
		if ( (@$langs['abbrev'] || $langs['english']) && empty($langs['del']) ) {
			$langs['abbrev'] = addslashes( stripslashes( @$langs['abbrev'] ) );
			$langs['english'] = addslashes( stripslashes( $langs['english'] ) );
			if (!empty($langs['abbrev'])) {
				$txt .= "\"{$langs['abbrev']}\"=>";
			}
			$txt .= "\"{$langs['english']}\",\n";
		}
	}
} else {
// editing the translation
	foreach ($trans as $langs) {
		if ( empty($langs['del']) ) {
			$langs['english'] = addslashes( stripslashes( $langs['english'] ) );
			$langs['lang'] = addslashes( stripslashes( $langs['lang'] ) );
			//fwrite( $fp, "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n" );
			$txt .= "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n";
		}
	}
}
//echo "<pre>$txt</pre>";
fwrite( $fp, $txt );
fclose( $fp );

$AppUI->setMsg( "Locales file saved", UI_MSG_OK );
$AppUI->redirect();
?>
