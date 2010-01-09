<?php
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      sendpass

 File:       sendpass.php
 Location:   pmango\includes
 Started:    2005.09.30
 Author:     dotProject Team
 Type:       php

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

/**
* @package PMango
* @subpackage core
* @license http://opensource.org/licenses/bsd-license.php BSD License
*/

require_once( $AppUI->getSystemClass( 'libmail' ) );

//
// New password code based oncode from Mambo Open Source Core
// www.mamboserver.com | mosforge.net
//
function sendNewPass() {
 global $AppUI, $dPconfig;

 $_live_site = $dPconfig['base_url'];
 $_sitename = $dPconfig['organization_name'];

 // ensure no malicous sql gets past
 $checkusername = trim( dPgetParam( $_POST, 'checkusername', '') );
 $checkusername = db_escape( $checkusername );
 $confirmEmail = trim( dPgetParam( $_POST, 'checkemail', '') );
 $confirmEmail = strtolower( db_escape( $confirmEmail ) );

 $query = "SELECT user_id FROM users"
 ." LEFT JOIN contacts ON user_contact = contact_id"
 . "\nWHERE user_username='$checkusername' AND LOWER(contact_email)='$confirmEmail'"
 ;
 if (!($user_id = db_loadResult($query)) || !$checkusername || !$confirmEmail) {
  $AppUI->setMsg( 'Invalid username or email.', UI_MSG_ERROR );
  $AppUI->redirect();
 }
 
 $newpass = makePass();
 $message = $AppUI->_('sendpass0', UI_OUTPUT_RAW)." $checkusername ". $AppUI->_('sendpass1', UI_OUTPUT_RAW) . " $_live_site  ". $AppUI->_('sendpass2', UI_OUTPUT_RAW) ." $newpass ". $AppUI->_('sendpass3', UI_OUTPUT_RAW);
 $subject = "$_sitename :: ".$AppUI->_('sendpass4', UI_OUTPUT_RAW)." - $checkusername";
 
 $m= new Mail; // create the mail
 $m->From( "dotProject" );
 $m->To( $confirmEmail );
 $m->Subject( $subject );
 $m->Body( $message, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );	// set the body
 $m->Send();	// send the mail

 $newpass = md5( $newpass );
 $sql = "UPDATE users SET user_password='$newpass' WHERE user_id='$user_id'";
 $cur = db_exec( $sql );
 if (!$cur) {
  die("SQL error" . $database->stderr(true));
 } else {
  $AppUI->setMsg( 'New User Password created and emailed to you' );
  $AppUI->redirect();
 }
}

function makePass(){
 $makepass="";
 $salt = "abchefghjkmnpqrstuvwxyz0123456789";
 srand((double)microtime()*1000000);
 $i = 0;
 while ($i <= 7) {
  $num = rand() % 33;
  $tmp = substr($salt, $num, 1);
  $makepass = $makepass . $tmp;
  $i++;
 }
 return ($makepass);
}
?>
