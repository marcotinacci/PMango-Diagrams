<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      functions to PMango install

 File:       install.inc.php
 Location:   pmango\install
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.26 Lorenzo
   Second version, new procedure to install PMango.
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
// Provide fake interface classes and installation functions
// so that most db shortcuts will work without, for example, an AppUI instance.

// Defines required by setMsg, these are different to those used by the real CAppUI.

define( 'UI_MSG_OK', '');
define('UI_MSG_ALERT', 'Warning: ');
define('UI_MSG_WARNING', 'Warning: ');
define('UI_MSG_ERROR', 'ERROR: ');
#
# function to output a message
# currently just outputs it expecting there to be a pre block.
# but could be changed to format it better - and only needs to be done here.
# The flush is called so that the user gets progress as it occurs. It depends
# upon the webserver/browser combination though.
#
function dPmsg($msg)
{
 echo $msg . "\n";
 flush();
}

#
# function to return a default value if a variable is not set
#

function InstallDefVal($var, $def) {
 return isset($var) ? $var : $def;
}

/**
* Utility function to return a value from a named array or a specified default
*/
function dPInstallGetParam( &$arr, $name, $def=null ) {
 return isset( $arr[$name] ) ? $arr[$name] : $def;
}

/**
* Utility function to get last updated dates/versions for the
* system.  The default is to 
*/
function InstallGetVersion($mode, $db) {
 $result = array(
  'last_db_update' => '',
  'last_code_update' => '',
  'code_version' => '3.0',
  'db_version' => '2'
 );
 if ($mode == 'upgrade') {
  $res = $db->Execute('SELECT * FROM dpversion LIMIT 1');
  if ($res && $res->RecordCount() > 0) {
   $row = $res->FetchRow();
   $result['last_db_update'] = str_replace('-', '', $row['last_db_update']);
   $result['last_code_update'] = str_replace('-', '', $row['last_code_update']);
   $result['code_version'] = $row['code_version'] ? $row['code_version'] : '3.0';
   $result['db_version'] = $row['db_version'] ? $row['db_version'] : '2';
  }
 }
 return $result;

}

/*
* Utility function to split given SQL-Code
* @param $sql string SQL-Code
* @param $last_update string last update that has been installed
*/
function InstallSplitSql($sql, $last_update) {
 global $lastDBUpdate;

 $buffer = array();
 $ret = array();

 $sql = trim($sql);

 $matched =  preg_match_all('/\n#\s*(\d{8})\b/', $sql, $matches);
 if ($matched) {
	 // Used for updating from previous versions, even if the update
	 // is not correctly set.
	 $len = count($matches[0]);
   $lastDBUpdate = $matches[1][$len-1];
 }
 
 if ($last_update && $last_update != '00000000') {
  // Find the first occurrance of an update that is
  // greater than the last_update number.
  dPmsg("Checking for previous updates");
  if ($matched) {
   for ($i = 0; $i < $len; $i++) {
    if ((int)$last_update < (int)$matches[1][$i]) {
     // Remove the SQL up to the point found
     $match = '/^.*' . trim($matches[0][$i]) . '/Us';
     $sql = preg_replace($match, "", $sql);
     break;
    }
   }
   // If we run out of indicators, we need to debunk, otherwise we will reinstall
   if ($i == $len)
    return $ret;
  }
 }
 $sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

 $in_string = false;

 for($i=0; $i<strlen($sql)-1; $i++) {
  if($sql[$i] == ";" && !$in_string) {
   $ret[] = substr($sql, 0, $i);
   $sql = substr($sql, $i + 1);
   $i = 0;
  }

  if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
   $in_string = false;
  }
  elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
   $in_string = $sql[$i];
  }
  if(isset($buffer[1])) {
   $buffer[0] = $buffer[1];
  }
  $buffer[1] = $sql[$i];
 }

 if(!empty($sql)) {
  $ret[] = $sql;
 }
 return($ret);
}

function InstallLoadSQL($sqlfile, $last_update = null, $display =true)
{
 global $dbErr, $dbMsg, $db;
 // Don't complain about missing files.
 if (! file_exists($sqlfile))
	return;
 $mqr = @get_magic_quotes_runtime();
 @set_magic_quotes_runtime(0);

 $pieces = array();
 if ($sqlfile) {
  $query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
  $pieces  = InstallSplitSql($query, $last_update);
 }
 @set_magic_quotes_runtime($mqr);
 $errors = 0;
 $piece_count = count($pieces);

 for ($i=0; $i<$piece_count; $i++) {
  $pieces[$i] = trim($pieces[$i]);
  if(!empty($pieces[$i]) && $pieces[$i] != "#") {
   if (!$result = $db->Execute($pieces[$i])) {
    $errors++;
    $dbErr = true;
    $dbMsg .= $db->ErrorMsg().'<br>';
   // echo $dbMsg;
   }
  }
 }
 if ($display)
 	dPmsg("There were $errors errors in $piece_count SQL statements");
}

class InstallerUI {

	var $user_id = 0;

	function setMsg($msg, $msgno = '', $append=false)
	{
		return dPmsg($msgno . $msg);
	}
}

?>
