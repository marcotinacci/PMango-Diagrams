<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      read functions for language options

 File:       core.php
 Location:   pmango\locales\en
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       PHP

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
ob_start();
	@readfile( "{$dPconfig['root_dir']}/locales/$AppUI->user_locale/common.inc" );
	
// language files for specific locales and specific modules (for external modules) should be 
// put in modules/[the-module]/locales/[the-locale]/[the-module].inc
// this allows for module specific translations to be distributed with the module
	
	if ( file_exists( "{$dPconfig['root_dir']}/modules/$m/locales/$AppUI->user_locale.inc" ) )
	{
		@readfile( "{$dPconfig['root_dir']}/modules/$m/locales/$AppUI->user_locale.inc" );
	}
	else
	{
		@readfile( "{$dPconfig['root_dir']}/locales/$AppUI->user_locale/$m.inc" );
	}
	
	switch ($m) {
	case 'departments':
		@readfile( "{$dPconfig['root_dir']}/locales/$AppUI->user_locale/companies.inc" );
		break;
	case 'system':
		@readfile( "{$dPconfig['root_dir']}/locales/{$dPconfig['host_locale']}/styles.inc" );
		break;
	}
	eval( "\$GLOBALS['translate']=array(".ob_get_contents()."\n'0');" );
ob_end_clean();
?>
