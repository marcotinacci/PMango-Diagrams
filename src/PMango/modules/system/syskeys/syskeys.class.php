<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      system keys functions.

 File:       syskeys.class.php
 Location:   pmango\modules\system\syskeys
 Started:    2005.09.30
 Author:     dotProject Team
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


include_once( $AppUI->getSystemClass ('dp' ) );

##
## CSysKey Class
##

class CSysKey extends CDpObject {
	var $syskey_id = NULL;
	var $syskey_name = NULL;
	var $syskey_label = NULL;
	var $syskey_type = NULL;
	var $syskey_sep1 = NULL;
	var $syskey_sep2 = NULL;

	function CSysKey( $name=null, $label=null, $type='0', $sep1="\n", $sep2 = '|' ) {
		$this->CDpObject( 'syskeys', 'syskey_id' );
		$this->syskey_name = $name;
		$this->syskey_label = $label;
		$this->syskey_type = $type;
		$this->syskey_sep1 = $sep1;
		$this->syskey_sep2 = $sep2;
	}
}

##
## CSysVal Class
##

class CSysVal extends CDpObject {
	var $sysval_id = NULL;
	var $sysval_key_id = NULL;
	var $sysval_title = NULL;
	var $sysval_value = NULL;

	function CSysVal( $key=null, $title=null, $value=null ) {
		$this->CDpObject( 'sysvals', 'sysval_id' );
		$this->sysval_key_id = $key;
		$this->sysval_title = $title;
		$this->sysval_value = $value;
	}
}

?>