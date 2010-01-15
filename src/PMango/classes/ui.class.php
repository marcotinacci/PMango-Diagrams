<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      user interface class

 File:       ui.class.php
 Location:   pmango/classes
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       class

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   Second version, modified to create new interface.
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

---------------------------------------------------------------------------*/

/**
* @package PMango
* @subpackage core
* @license http://opensource.org/licenses/gpl-license.php GPL License Version 2
*/

// Message No Constants
define( 'UI_MSG_OK', 1 );
define( 'UI_MSG_ALERT', 2 );
define( 'UI_MSG_WARNING', 3 );
define( 'UI_MSG_ERROR', 4 );
define( 'UI_MSG_PROP_OK', 5 );
define( 'UI_MSG_PROP_KO', 6 );

// global variable holding the translation array
$GLOBALS['translate'] = array();

define( "UI_CASE_MASK", 0x0F );
define( "UI_CASE_UPPER", 1 );
define( "UI_CASE_LOWER", 2 );
define( "UI_CASE_UPPERFIRST", 3 );

define ("UI_OUTPUT_MASK", 0xF0);
define ("UI_OUTPUT_HTML", 0);
define ("UI_OUTPUT_JS", 0x10);
define ("UI_OUTPUT_RAW", 0x20);

// $baseDir is set in index.php and fileviewer.php and is the base directory
// of the PMango installation.
require_once "$baseDir/classes/permissions.class.php";
/**
* The Application User Interface Class.
*
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
* @version $Revision: 1.85 $
*/
class CAppUI {
/** @var array generic array for holding the state of anything */
	var $state=null;
/** @var int */
	var $user_id=null;
/** @var string */
	var $user_first_name=null;
/** @var string */
	var $user_last_name=null;
/** @var string */
	var $user_groups=null;// l'indice � il gruppo e il valore � il set di cap.
/** @var int */
	var $user_day_hours=null;
/** @var string */
	var $user_email=null;
/** @var int */
	//var $user_type=null;
/** @var array */
	var $user_prefs=null;
/** @var int Unix time stamp */
	var $day_selected=null;

// localisation
/** @var string */
	var $user_locale=null;
/** @var string */
	var $base_locale = 'en'; // do not change - the base 'keys' will always be in english

/** @var string Message string*/
	var $msg = '';
/** @var string */
	var $msgNo = '';
/** @var string Properties string*/
	var $properties = null;
/** @var string */
	var $prpNo = null;
/** @var string Default page for a redirect call*/
	var $defaultRedirect = '';

/** @var array Configuration variable array*/
	var $cfg=null;

/** @var integer Version major */
	var $version_major = null;

/** @var integer Version minor */
	var $version_minor = null;

/** @var integer Version patch level */
	var $version_patch = null;

/** @var string Version string */
	var $version_string = null;

/** @var integer for register log ID */
            var $last_insert_id = null;	
/**
* CAppUI Constructor
*/
	function CAppUI() {
		global $dPconfig;

		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		// Come indice ha il gruppo_id a cui l'user appartiene e come valore il set di capabilities
		// corrispondente (setcap_id)
		$this->user_groups = array(); 
		//$this->user_department = 0;
		//$this->user_type = 0;

		// cfg['locale_warn'] is the only cfgVariable stored in session data (for security reasons)
		// this guarants the functionality of this->setWarning
		$this->cfg['locale_warn'] = $dPconfig['locale_warn'];
		
		$this->project_id = 0;

		$this->defaultRedirect = "";
// set up the default preferences
		$this->setUserLocale($this->base_locale);
		$this->user_prefs = array();
		$this->properties = array();
		$this->prpNo = array();
	}
/**
* Used to load a php class file from the system classes directory
* @param string $name The class root file name (excluding .class.php)
* @return string The path to the include file
 */
	function getSystemClass( $name=null ) {
		global $baseDir;
		if ($name) {
			return "$baseDir/classes/$name.class.php";
		}
	}

/**
* Used to load a php class file from the lib directory
*
* @param string $name The class root file name (excluding .class.php)
* @return string The path to the include file
*/
	function getLibraryClass( $name=null ) {
		global $baseDir;
		if ($name) {
			return "$baseDir/lib/$name.php";
		}
	}

/**
* Used to load a php class file from the module directory
* @param string $name The class root file name (excluding .class.php)
* @return string The path to the include file
 */
	function getModuleClass( $name=null ) {
		global $baseDir;
		if ($name) {
			return "$baseDir/modules/$name/$name.class.php";
		}
	}

/**
* Determines the version.
* @return String value indicating the current PMango version
*/
	function getVersion() {
		global $dPconfig;
		global $baseDir;
		if ( ! isset($this->version_major)) {
			include_once $baseDir . '/includes/version.php';
			$this->version_major = $dp_version_major;
			$this->version_minor = $dp_version_minor;
			$this->version_patch = $dp_version_patch;
			$this->version_string = $this->version_major . "." . $this->version_minor;
			if (isset($dp_version_prepatch))
			  $this->version_string .= "-" . $dp_version_prepatch;
			if (isset($this->version_patch))
			  $this->version_string .= "." . $this->version_patch;
		}
		return $this->version_string;
	}

/**
* Checks that the current user preferred style is valid/exists.
*/
	function checkStyle() {
		global $dPconfig;
		global $baseDir;
		// check if default user's uistyle is installed
		$uistyle = $this->getPref("UISTYLE");

		if ($uistyle && !is_dir("$baseDir/style/$uistyle")) {
			// fall back to host_style if user style is not installed
			$this->setPref( 'UISTYLE', $dPconfig['host_style'] );
		}
	}

/**
* Utility function to read the 'directories' under 'path'
*
* This function is used to read the modules or locales installed on the file system.
* @param string The path to read.
* @return array A named array of the directories (the key and value are identical).
*/
	function readDirs( $path ) {
		global $baseDir;
		$dirs = array();
		$d = dir( "$baseDir/$path" );
		while (false !== ($name = $d->read())) {
			if(is_dir( "$baseDir/$path/$name" ) && $name != "." && $name != ".." && $name != "CVS") {
				$dirs[$name] = $name;
			}
		}
		$d->close();
		return $dirs;
	}

/**
* Utility function to read the 'files' under 'path'
* @param string The path to read.
* @param string A regular expression to filter by.
* @return array A named array of the files (the key and value are identical).
*/
	function readFiles( $path, $filter='.' ) {
		$files = array();

		if (is_dir($path) && ($handle = opendir( $path )) ) {
			while (false !== ($file = readdir( $handle ))) {
				if ($file != "." && $file != ".." && preg_match( "/$filter/", $file )) { 
					$files[$file] = $file; 
				} 
			}
			closedir($handle); 
		}
		return $files;
	}


/**
* Utility function to check whether a file name is 'safe'
*
* Prevents from access to relative directories (eg ../../dealyfile.php);
* @param string The file name.
* @return array A named array of the files (the key and value are identical).
*/
	function checkFileName( $file ) {
		global $AppUI;

		// define bad characters and their replacement
		$bad_chars = ";/\\";
		$bad_replace = "...."; // Needs the same number of chars as $bad_chars

		// check whether the filename contained bad characters
		if ( strpos( strtr( $file, $bad_chars, $bad_replace), '.') !== false ) {
			$AppUI->redirect( "m=public&a=access_denied" );
		}
		else {
			return $file;
		}

	}



/**
* Utility function to make a file name 'safe'
*
* Strips out mallicious insertion of relative directories (eg ../../dealyfile.php);
* @param string The file name.
* @return array A named array of the files (the key and value are identical).
*/
	function makeFileNameSafe( $file ) {
		$file = str_replace( '../', '', $file );
		$file = str_replace( '..\\', '', $file );
		return $file;
	}

/**
* Sets the user locale.
*
* Looks in the user preferences first.  If this value has not been set by the user it uses the system default set in config.php.
* @param string Locale abbreviation corresponding to the sub-directory name in the locales directory (usually the abbreviated language code).
*/
	function setUserLocale( $loc='', $set = true ) {
		global $dPconfig, $locale_char_set;

		$LANGUAGES = $this->loadLanguages();

		if (! $loc) {
			$loc = @$this->user_prefs['LOCALE'] ? $this->user_prefs['LOCALE'] : $dPconfig['host_locale'];
		}

		if (isset($LANGUAGES[$loc]))
			$lang = $LANGUAGES[$loc];
		else {
			// Need to try and find the language the user is using, find the first one
			// that has this as the language part
			if (strlen($loc) > 2) {
				list ($l, $c) = explode('_', $loc);
				$loc = $this->findLanguage($l, $c);
			} else {
				$loc = $this->findLanguage($loc);
			}
			$lang = $LANGUAGES[$loc];
		}
		list($base_locale, $english_string, $native_string, $default_language, $lcs) = $lang;
		if (! isset($lcs))
			$lcs = (isset($locale_char_set)) ? $locale_char_set : 'utf-8';

		if (version_compare(phpversion(), '4.3.0', 'ge'))
			$user_lang = array( $loc . '.' . $lcs, $default_language, $loc, $base_locale);
		else {
			if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
				$user_lang = $default_language;
			} else {
				$user_lang = $loc . '.' . $lcs;
			}
		}
		if ($set) {
			$this->user_locale = $base_locale;
			$this->user_lang = $user_lang;
			$locale_char_set = $lcs;
		} else {
			return $user_lang;
		}
	}

	function findLanguage($language, $country = false)
	{
		$LANGUAGES = $this->loadLanguages();
		$language = strtolower($language);
		if ($country) {
			$country = strtoupper($country);
			// Try constructing the code again
			$code = $language . '_' . $country;
			if (isset($LANGUAGES[$code]))
				return $code;
		}

		// Just use the country code and try and find it in the
		// languages list.
		$first_entry = null;
		foreach ($LANGUAGES as $lang => $info) {
			list($l, $c) = explode('_', $lang);
			if ($l == $language) {
				if (! $first_entry)
					$first_entry = $lang;
				if ($country && $c == $country)
					return $lang;
			}
		}
		return $first_entry;
	}

/**
 * Load the known language codes for loaded locales
 *
 */
	function loadLanguages() {
		global $baseDir;

		if ( isset($_SESSION['LANGUAGES'])) {
			$LANGUAGES =& $_SESSION['LANGUAGES'];
		} else {
			$LANGUAGES = array();
			$langs = $this->readDirs('locales');
			foreach ($langs as $lang) {
				if (file_exists("$baseDir/locales/$lang/lang.php")) {
					include_once "$baseDir/locales/$lang/lang.php";
				}
			}
			@$_SESSION['LANGUAGES'] =& $LANGUAGES;
		}
		return $LANGUAGES;
	}

/**
* Translate string to the local language [same form as the gettext abbreviation]
*
* This is the order of precedence:
* <ul>
* <li>If the key exists in the lang array, return the value of the key
* <li>If no key exists and the base lang is the same as the local lang, just return the string
* <li>If this is not the base lang, then return string with a red star appended to show
* that a translation is required.
* </ul>
* @param string The string to translate
* @param int Option flags, can be case handling or'd with output styles
* @return string
*/
	function _( $str, $flags= 0 ) {
		if (is_array($str)) {
			$translated = array();
			foreach ($str as $s)
				$translated[] = $this->__($s, $flags);
			return implode(' ', $translated);
		} else {
			return $this->__($str, $flags);
		}
	}

	function __( $str, $flags = 0) {
		global $dPconfig;
		$str = trim($str);
		if (empty( $str )) {
			return '';
		}
		$x = @$GLOBALS['translate'][$str];
		
		if ($x) {
			$str = $x;
		} else if (@$dPconfig['locale_warn']) {
			if ($this->base_locale != $this->user_locale ||
				($this->base_locale == $this->user_locale && !in_array( $str, @$GLOBALS['translate'] )) ) {
				$str .= @$dPconfig['locale_alert'];
			}
		}
		switch ($flags & UI_CASE_MASK) {
			case UI_CASE_UPPER:
				$str = strtoupper( $str );
				break;
			case UI_CASE_LOWER:
				$str = strtolower( $str );
				break;
			case UI_CASE_UPPERFIRST:
				$str = ucwords( $str );
				break;
		}
		/* Altered to support multiple styles of output, to fix
		 * bugs where the same output style cannot be used succesfully
		 * for both javascript and HTML output.
		 * PLEASE NOTE: The default is currently UI_OUTPUT_HTML,
		 * which is different to the previous version (which was
		 * effectively UI_OUTPUT_RAW).  If this causes problems,
		 * and they are localised, then use UI_OUTPUT_RAW in the
		 * offending call.  If they are widespread, change the
		 * default to UI_OUTPUT_RAW and use the other options
		 * where appropriate.
		 * AJD - 2004-12-10
		 */
                global $locale_char_set;

		if (! $locale_char_set) {
			$locale_char_set = 'utf-8';
		}
                
		switch ($flags & UI_OUTPUT_MASK) {
			case UI_OUTPUT_HTML:
				$str = htmlentities(stripslashes($str), ENT_COMPAT, $locale_char_set);
				break;
			case UI_OUTPUT_JS:
				$str = addslashes(stripslashes($str)); //, ENT_COMPAT, $locale_char_set);
				break;
			case UI_OUTPUT_RAW: 
				$str = stripslashes($str);
				break;
		}
		return $str;
	}
/**
* Set the display of warning for untranslated strings
* @param string
*/
	function setWarning( $state=true ) {
		$temp = @$this->cfg['locale_warn'];
		$this->cfg['locale_warn'] = $state;
		return $temp;
	}
/**
* Save the url query string
*
* Also saves one level of history.  This is useful for returning from a delete
* operation where the record more not now exist.  Returning to a view page
* would be a nonsense in this case.
* @param string If not set then the current url query string is used
*/
	function savePlace( $query='' ) {
		if (!$query) {
			$query = @$_SERVER['QUERY_STRING'];
		}
		if ($query != @$this->state['SAVEDPLACE']) {
			$this->state['SAVEDPLACE-1'] = @$this->state['SAVEDPLACE'];
			$this->state['SAVEDPLACE'] = $query;
		}
	}
/**
* Resets the internal variable
*/
	function resetPlace() {
		$this->state['SAVEDPLACE'] = '';
	}
/**
* Get the saved place (usually one that could contain an edit button)
* @return string
*/
	function getPlace() {
		return @$this->state['SAVEDPLACE'];
	}
/**
* Redirects the browser to a new page.
*
* Mostly used in conjunction with the savePlace method. It is generally used
* to prevent nasties from doing a browser refresh after a db update.  The
* method deliberately does not use javascript to effect the redirect.
*
* @param string The URL query string to append to the URL
* @param string A marker for a historic 'place, only -1 or an empty string is valid.
*/
	function redirect( $params='', $hist='' ) {
		$session_id = SID;

		session_write_close();
	// are the params empty
		if (!$params) {
		// has a place been saved
			$params = !empty($this->state["SAVEDPLACE$hist"]) ? $this->state["SAVEDPLACE$hist"] : $this->defaultRedirect;
		}
		// Fix to handle cookieless sessions
		if ($session_id != "") {
		  if (!$params)
		    $params = $session_id;
		  else
		    $params .= "&" . $session_id;
		}
		ob_implicit_flush(); // Ensure any buffering is disabled.
		header( "Location: index.php?$params" );
		exit();	// stop the PHP execution
	}
/**
* Set the page message.
*
* The page message is displayed above the title block and then again
* at the end of the page.
*
* IMPORTANT: Please note that append should not be used, since for some
* languagues atomic-wise translation doesn't work. Append should be
* deprecated.
*
* @param mixed The (untranslated) message
* @param int The type of message
* @param boolean If true, $msg is appended to the current string otherwise
* the existing message is overwritten with $msg.
*/
	function setMsg( $msg, $msgNo=0, $append=false ) {
		$msg = $this->_( $msg );
		$this->msg = $append ? $this->msg.' '.$msg : $msg;
		$this->msgNo = $msgNo;
	}
/**
* Display the formatted message and icon
* @param boolean If true the current message state is cleared.
*/
	function getMsg( $reset=true, $show=false) {
		$img = '';
		$class = '';
		$msg = $this->msg;

		switch( $this->msgNo ) {
		case UI_MSG_OK:
			$img = dPshowImage( dPfindImage( 'stock_ok-16.png' ), 16, 16, '' );
			$class = "message";
			break;
		case UI_MSG_ALERT:
			$img = dPshowImage( dPfindImage( 'rc-gui-status-downgr.png' ), 16, 16, '' );
			$class = "message";
			break;
		case UI_MSG_WARNING:
			$img = dPshowImage( dPfindImage( 'rc-gui-status-downgr.png' ), 16, 16, '' );
			$class = "warning";
			break;
		case UI_MSG_ERROR:
			$img = dPshowImage( dPfindImage( 'stock_cancel-16.png' ), 16, 16, '' );
			$class = "error";
			break;
		case UI_MSG_PROP_OK:
			$img = dPshowImage( dPfindImage( 'greenSem.jpg' ), 18, 18, '' );
			$class = "message";
			break;
		case UI_MSG_PROP_KO:
			$img = dPshowImage( dPfindImage( 'redSem.jpg' ), 18, 18, '' );
			$class = "error";
			break;
		default:
			$class = "message";
			break;
		}
		if ($reset) {
			$this->msg = '';
			$this->msgNo = 0;
		}
		if ($show)
			return $msg ? ''
				. "<td >$img</td>"
				. "<td nowrap=\"nowrap\" class=\"$class\">$msg</td>"
				: '';
		else {
			return $msg ? '<table cellspacing="0" cellpadding="1" border="0"><tr>'
				. "<td>$img</td>"
				. "<td class=\"$class\">$msg</td>"
				. '</tr></table>'
				: '';
		}
	}
	
	function setProperties( $msg, $msgNo=0) {
		if (!is_array($this->prpNo)) 
			$this->prpNo=array();
		if (!is_array($this->properties))
			$this->properties=array();
		$msg = $msg;//$this->_( $msg );
		$this->properties[] = $msg;
		$this->prpNo[] = $msgNo;
	}
/**
* Display the formatted message and icon
* @param boolean If true the current message state is cleared.
*/
	function getProperties( $reset=true) {
		$s = '';
		$msg = $this->properties;
		$msgNo = $this->prpNo;//print_r($this->prpNo);
		if (!is_array($msg)||!is_array($msgNo)||count($msg) == 0)
			return '';
		if ($reset) {
			$this->properties = array();
			$this->prpNo = array();
		}
		foreach ($msgNo as $i => $n) {
			switch( $n ) {
				case UI_MSG_PROP_OK:
					$s.= "<font color=\"#003300\">".$msg[$i]."</font><br>";
					break;
				case UI_MSG_PROP_KO:
					$s.= "<font color=\"red\">".$msg[$i]."</font><br>";
					break;
				default:
					$s.=$msg[$i]."<br>";
					break;
			}
		}
		return rtrim($s,'<br>');
	}
/**
* Set the value of a temporary state variable.
*
* The state is only held for the duration of a session.  It is not stored in the database.
* Also do not set the value if it is unset.
* @param string The label or key of the state variable
* @param mixed Value to assign to the label/key
*/
	function setState( $label, $value = null) {
		if (isset($value))
			$this->state[$label] = $value;
	}
/**
* Get the value of a temporary state variable.
* If a default value is supplied and no value is found, set the default.
* @return mixed
*/
	function getState( $label, $default_value = null ) {
		if (array_key_exists( $label, $this->state)) {
			return $this->state[$label];
		} else if (isset($default_value)) {
			$this->setState($label, $default_value);
			return $default_value;
		} else  {
			return NULL;
		}
	}

	function checkPrefState($label, $value, $prefname, $default_value = null) {
		// Check if we currently have it set
		if (isset($value)) {
			$result = $value;
			$this->state[$label] = $value;
		} else if (array_key_exists($label, $this->state)) {
			$result = $this->state[$label];
		} else if (($pref = $this->getPref($prefname)) !== null) {
			$this->state[$label] = $pref;
			$result = $pref;
		} else if (isset($default_value)) {
			$this->state[$label] = $default_value;
			$result = $default_value;
		} else {
			$result = null;
		}
		return $result;
	}
/**
*  function
*
* A number of things are done in this method to prevent illegal entry:
* <ul>
* <li>The username and password are trimmed and escaped to prevent malicious
*     SQL being executed
* </ul>
* The schema previously used the MySQL PASSWORD function for encryption.  This
* Method has been deprecated in favour of PHP's MD5() function for database independance.
* The check_legacy_password option is no longer valid
*
* Upon a successful username and password match, several fields from the user
* table are loaded in this object for convenient reference.  The style, localces
* and preferences are also loaded at this time.
*
* @param string The user login name
* @param string The user password
* @return boolean True if successful, false if not
*/
	function login( $username, $password ) {
		global $dPconfig, $baseDir;

		require_once "$baseDir/classes/authenticator.class.php";

		$auth_method = isset($dPconfig['auth_method']) ? $dPconfig['auth_method'] : 'sql';
		if (@$_POST['login'] != 'login' && @$_POST['login'] != $this->_('login') && $_REQUEST['login'] != $auth_method)
			die("You have chosen to log in using an unsupported or disabled login method '$_REQUEST[login]'");
		$auth =& getauth($auth_method);
		
		$username = trim( db_escape( $username ) );
		$password = trim( db_escape( $password ) );

		if (!$auth->authenticate($username, $password)) {
			return false;
		}
	
		$user_id = $auth->userId($username);
		$username = $auth->username; // Some authentication schemes may collect username in various ways.
		// Now that the password has been checked, see if they are allowed to
		// access the system
		if (! isset($GLOBALS['acl']))
		  $GLOBALS['acl'] = new dPacl;
		if ( ! $GLOBALS['acl']->checkLogin($user_id)) {
		  dprint(__FILE__, __LINE__, 1, "Permission check failed");
		  return false;
		}

		$q  = new DBQuery;
		$q->addTable('users');
		$q->addQuery('user_id, user_first_name, user_last_name, user_email, user_day_hours');
		$q->addWhere("user_id = $user_id AND user_username = '$username'");
		$sql = $q->prepare();
		$q->clear();
		dprint(__FILE__, __LINE__, 7, "Login SQL: $sql");

		if( !db_loadObject( $sql, $this ) ) {
			dprint(__FILE__, __LINE__, 1, "Failed to load user information");
			return false;
		}

		$q  = new DBQuery;
		$q->addTable('user_setcap', 'us');
		$q->addQuery('us.group_id, ga.value');//manca da inserire i groups e i set cap
		$q->addJoin('gacl_aro_groups', 'ga', 'ga.id = us.setcap_id');
		$q->addWhere("us.setcap_id <> 0 && us.user_id = $user_id");
		$this->user_groups = $q->loadHashList();// c'� inserito anche ALL GROUPS
		foreach ($this->user_groups as $g => $sc)
			$this->user_groups[$g]="'".$sc."'";
// load the user preferences
		$this->loadPrefs( $this->user_id );
		$this->setUserLocale();
		$this->checkStyle();
		return true;
	}
	
	function loadGroups() {
		$q  = new DBQuery;
		$q->addTable('user_setcap', 'us');
		$q->addQuery('us.group_id, ga.value');//manca da inserire i groups e i set cap
		$q->addJoin('gacl_aro_groups', 'ga', 'ga.id = us.setcap_id');
		$q->addWhere("us.setcap_id <> 0 && us.user_id = $this->user_id");
		$this->user_groups = $q->loadHashList();// c'� inserito anche ALL GROUPS
		foreach ($this->user_groups as $g => $sc)
			$this->user_groups[$g]="'".$sc."'";
	}
/************************************************************************************************************************	
/**
*@Function for regiser log in dotprojet table "user_access_log"
*/
	   function registerLogin(){
		$q  = new DBQuery;
		$q->addTable('user_access_log');
		$q->addInsert('user_id', "$this->user_id");
		$q->addInsert('date_time_in', 'now()', false, true);
		$q->addInsert('user_ip', $_SERVER['REMOTE_ADDR']);
        $q->exec();
        $this->last_insert_id = db_insert_id();
		$q->clear();
		
       }

/**
*@Function for register log out in PMango table "user_acces_log"
*/
          function registerLogout($user_id){
		$q  = new DBQuery;
		$q->addTable('user_access_log');
		$q->addUpdate('date_time_out', date("Y-m-d H:i:s"));
		$q->addWhere("user_id = '$user_id' and (date_time_out='0000-00-00 00:00:00' or isnull(date_time_out)) ");
		if ($user_id > 0){
			$q->exec();
			$q->clear();
		}
          }
          
/**
*@Function for update table user_acces_log in field date_time_lost_action
*/
          function updateLastAction($last_insert_id){
		$q  = new DBQuery;
		$q->addTable('user_access_log');
		$q->addUpdate('date_time_last_action', date("Y-m-d H:i:s"));
		$q->addWhere("user_access_log_id = $last_insert_id");
                if ($last_insert_id > 0){
                    $q->exec();
                    $q->clear();
                }
          }
/************************************************************************************************************************
/**
* @deprecated
*/
	function logout() {
	}
/**
* Checks whether there is any user logged in.
*/
	function doLogin() {
		return ($this->user_id < 0) ? true : false;
	}
/**
* Gets the value of the specified user preference
* @param string Name of the preference
*/
	function getPref( $name ) {
		return @$this->user_prefs[$name];
	}
/**
* Sets the value of a user preference specified by name
* @param string Name of the preference
* @param mixed The value of the preference
*/
	function setPref( $name, $val ) {
		$this->user_prefs[$name] = $val;
	}
/**
* Loads the stored user preferences from the database into the internal
* preferences variable.
* @param int User id number
*/
	function loadPrefs( $uid=0 ) {
		$q  = new DBQuery;
		$q->addTable('user_preferences');
		$q->addQuery('pref_name, pref_value');
		$q->addWhere("pref_user = $uid");
		$prefs = $q->loadHashList();
		$this->user_prefs = array_merge( $this->user_prefs, $prefs );
	}

// --- Module connectors

/**
* Gets a list of the installed modules
* @return array Named array list in the form 'module directory'=>'module name'
*/
	function getInstalledModules() {
		$q  = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name');
		$q->addOrder('mod_directory');
		return ($q->loadHashList());
	}
/**
* Gets a list of the active modules
* @return array Named array list in the form 'module directory'=>'module name'
*/
	function getActiveModules() {
		$q  = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name');
		$q->addWhere('mod_active > 0');
		$q->addOrder('mod_directory');
		return ($q->loadHashList());
	}
/**
* Gets a list of the modules that should appear in the menu
* @return array Named array list in the form
* ['module directory', 'module name', 'module_icon']
*/
	function getMenuModules() {
		$q  = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name, mod_ui_icon');
		$q->addWhere("mod_active > 0 AND mod_ui_active > 0 AND mod_directory <> 'public'");
		$q->addOrder('mod_ui_order');
		return ($q->loadList());
	}

	function isActiveModule($module) {
		$q  = new DBQuery;
		$q->addTable('modules');
		$q->addQuery('mod_active');
		$q->addWhere("mod_directory = '$module'");
		$sql = $q->prepare();
		$q->clear();
		return db_loadResult($sql);
	}

/**
 * Returns the global dpACL class or creates it as neccessary.
 * @return object dPacl
 */
	function &acl() {
		if (! isset($GLOBALS['acl'])) {
			$GLOBALS['acl'] = new dPacl;
	  	}
	  	return $GLOBALS['acl'];
	}

/**
 * Find and add to output the file tags required to load module-specific
 * javascript.
 */
	function loadJS() {
	  global $m, $a, $dPconfig, $baseDir;
	  // Search for the javascript files to load.
	  if (! isset($m))
	    return;
	  $root = $baseDir;
	  if (substr($root, -1) != '/')
	    $root .= '/';

	  $base = $dPconfig['base_url'];
	  if ( substr($base, -1) != '/')
	    $base .= '/';
	  // Load the basic javascript used by all modules.
	  $jsdir = dir("{$root}js");

	  $js_files = array();
	  while (($entry = $jsdir->read()) !== false) {
	    if (substr($entry, -3) == '.js'){
		    $js_files[] = $entry;
	    }
	  }
	  asort($js_files);
	  while(list(,$js_file_name) = each($js_files)){
		  echo "<script type=\"text/javascript\" src=\"{$base}js/$js_file_name\"></script>\n";
		  }
		$this->getModuleJS($m, $a, true);
	}

	function getModuleJS($module, $file=null, $load_all = false) {
		global $dPconfig, $baseDir;
		$root = $baseDir;
		if (substr($root, -1) != '/');
			$root .= '/';
		$base = $dPconfig['base_url'];
		if (substr($base, -1) != '/') 
			$base .= '/';
		if ($load_all || ! $file) {
			if (file_exists("{$root}modules/$module/$module.module.js"))
				echo "<script type=\"text/javascript\" src=\"{$base}modules/$module/$module.module.js\"></script>\n";
		}
	  if (isset($file) && file_exists("{$root}modules/$module/$file.js"))
	    echo "<script type=\"text/javascript\" src=\"{$base}modules/$module/$file.js\"></script>\n";
	}

}

/**
* Tabbed box abstract class
*/
class CTabBox_core {
/** @var array */
	var $tabs=NULL;
/** @var int The active tab */
	var $active=NULL;
/** @var string The base URL query string to prefix tab links */
	var $baseHRef=NULL;
/** @var string The base path to prefix the include file */
	var $baseInc;
/** @var string A javascript function that accepts two arguments,
the active tab, and the selected tab **/
	var $javascript = NULL;

/**
* Constructor
* @param string The base URL query string to prefix tab links
* @param string The base path to prefix the include file
* @param int The active tab
* @param string Optional javascript method to be used to execute tabs.
*	Must support 2 arguments, currently active tab, new tab to activate.
*/
	function CTabBox_core( $baseHRef='', $baseInc='', $active=0, $javascript = null ) {
		$this->tabs = array();
		$this->active = $active;
		$this->baseHRef = ($baseHRef ? "$baseHRef&" : "?");
		$this->javascript = $javascript;
		$this->baseInc = $baseInc;
	}
/**
* Gets the name of a tab
* @return string
*/
	function getTabName( $idx ) {
		return $this->tabs[$idx][1];
	}
/**
* Adds a tab to the object
* @param string File to include
* @param The display title/name of the tab
*/
	function add( $file, $title, $translated = false ) {
		$this->tabs[] = array( $file, $title, $translated );
	}

	function isTabbed() {
		global $AppUI;
		if ($this->active < 0 || @$AppUI->getPref( 'TABVIEW' ) == 2 )
			return false;
		return true;
	}

/**
* Displays the tabbed box
*
* This function may be overridden
*
* @param string Can't remember whether this was useful
*/
	function show( $extra='', $js_tabs = false ) {
		GLOBAL $AppUI, $currentTabId, $currentTabName;
		reset( $this->tabs );
		$s = '';
	// tabbed / flat view options
		if (@$AppUI->getPref( 'TABVIEW' ) == 0) {
			$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td nowrap="nowrap">';
			$s .= '<a href="'.$this->baseHRef.'tab=0">'.$AppUI->_('tabbed').'</a> : ';
			$s .= '<a href="'.$this->baseHRef.'tab=-1">'.$AppUI->_('flat').'</a>';
			$s .= '</td>'.$extra.'</tr></table>';
			echo $s;
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>'.$extra.'</tr></table>';
			} else {
				echo '<img src="./images/shim.gif" height="10" width="1" />';
			}
		}

		if ($this->active < 0 || @$AppUI->getPref( 'TABVIEW' ) == 2 ) {
		// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $k => $v) {
				echo '<tr><td><strong>'.($v[2] ? $v[1] : $AppUI->_($v[1])).'</strong></td></tr>';
				echo '<tr><td>';
				$currentTabId = $k;
				$currentTabName = $v[1];
				include $this->baseInc.$v[0].".php";
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
		// tabbed view
			$s = "<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\n<tr>";
			if ( count($this->tabs)-1 < $this->active ) {
				//Last selected tab is not available in this view. eg. Child tasks
				$this->active = 0;
			}
			foreach( $this->tabs as $k => $v ) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$s .= "\n\t<td width=\"1%\" nowrap=\"nowrap\" class=\"tabsp\">";
				$s .= "\n\t\t<img src=\"./images/shim.gif\" height=\"1\" width=\"1\" alt=\"\" />";
				$s .= "\n\t</td>";
				$s .= "\n\t<td id=\"toptab_" . $k . "\" width=\"1%\" nowrap=\"nowrap\"";
				if ($js_tabs)
					$s .= " class=\"$class\"";
				$s .= ">";
				$s .= "\n\t\t<a href=\"";
				if ($this->javascript)
					$s .= "javascript:" . $this->javascript . "({$this->active}, $k)";
				else if ($js_tabs)
					$s .= 'javascript:show_tab(' . $k . ')';
				else
					$s .= $this->baseHRef . "tab=$k";
				$s .= "\">". ($v[2] ? $v[1] : $AppUI->_($v[1])). "</a>";
				$s .= "\n\t</td>";
			}
			$s .= "\n\t<td nowrap=\"nowrap\" class=\"tabsp\">&nbsp;</td>";
			$s .= "\n</tr>";
			$s .= "\n<tr>";
			$s .= '<td width="100%" colspan="'.(count($this->tabs)*2 + 1).'" class="tabox">';
			echo $s;
			//Will be null if the previous selection tab is not available in the new window eg. Children tasks
			if ( $this->baseInc.$this->tabs[$this->active][0] != "" ) {
				$currentTabId = $this->active;
				$currentTabName = $this->tabs[$this->active][1];
				if (!$js_tabs)
					require $this->baseInc.$this->tabs[$this->active][0].'.php';
			}
			if ($js_tabs)
			{
				foreach( $this->tabs as $k => $v ) 
				{
					echo '<div class="tab" id="tab_'.$k.'">';
					require $this->baseInc.$v[0].'.php';
					echo '</div>';
				}
			}
			echo "\n</td>\n</tr>\n</table>";
		}
	}

	function loadExtras($module, $file = null) {
		global $AppUI;
		if (! isset($_SESSION['all_tabs']) || ! isset($_SESSION['all_tabs'][$module]))
			return false;

		if ($file) {
			if (isset($_SESSION['all_tabs'][$module][$file]) && is_array($_SESSION['all_tabs'][$module][$file])) {
				$tab_array =& $_SESSION['all_tabs'][$module][$file];
			} else {
				return false;
			}
		} else {
			$tab_array =& $_SESSION['all_tabs'][$module];
		}
		$tab_count = 0;
		foreach ($tab_array as $tab_elem) {
			if (isset($tab_elem['module']) && $AppUI->isActiveModule($tab_elem['module'])) {
				$tab_count++;
				$this->add($tab_elem['file'], $tab_elem['name']);
			}
		}
		return $tab_count;
	}

	function findTabModule($tab) {
		global $AppUI, $m, $a;

		if (! isset($_SESSION['all_tabs']) || ! isset($_SESSION['all_tabs'][$m]))
			return false;

		if (isset($a)) {
			if (isset($_SESSION['all_tabs'][$m][$a]) && is_array($_SESSION['all_tabs'][$m][$a]))
				$tab_array =& $_SESSION['all_tabs'][$m][$a];
			else
				$tab_array =& $_SESSION['all_tabs'][$m];
		} else {
			$tab_array =& $_SESSION['all_tabs'][$m];
		}

		list($file, $name) = $this->tabs[$tab];
		foreach ($tab_array as $tab_elem) {
			if (isset($tab_elem['name']) && $tab_elem['name'] == $name && $tab_elem['file'] == $file)
				return $tab_elem['module'];
		}
		return false;
	}
}

/**
* Title box abstract class
*/
class CTitleBlock_core {
/** @var string The main title of the page */
	var $title='';
/** @var string The name of the icon used to the left of the title */
	var $icon='';
/** @var string The name of the module that this title block is displaying in */
	var $module='';
/** @var array An array of the table 'cells' to the right of the title block and for bread-crumbs */
	var $cells=null;
/** @var string The reference for the context help system */
	var $helpref='';
/** @var string The menu voices of th module */	
	var $menuVoices=null;
/** @var string See above */	
	var $baseHRef=null;

	
/** The constructor
*
* Assigns the title, icon, module and help reference.  If the user does not
* have permission to view the help module, then the context help icon is
* not displayed.
*/
	function CTitleBlock_core( $title, $icon='', $module='', $helpref='', $baseHRef=null) {
		$this->title = $title;
		$this->icon = $icon;
		$this->module = $module;
		$this->helpref = $helpref;
		$this->menuVoices = array();
		$this->baseHRef = $baseHRef;
		$this->cells1 = array();
		$this->cells2 = array();
		$this->crumbs = array();
		$this->showhelp = !getDenyRead( 'help' );
	}
/**
* Adds a table 'cell' beside the Title string
*
* Cells are added from left to right.
*/
	function addCell( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->cells1[] = array( $attribs, $data, $prefix, $suffix );
	}
	
	function addLink2( $data) {
		$this->links2[] = $data;
	}
	
	function addMenu( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->menuVoices[] = array( $attribs, $data, $prefix, $suffix );
	}
/**
* Adds a table 'cell' to left-aligned bread-crumbs
*
* Cells are added from left to right.
*/
	function addCrumb( $link, $label, $icon='' ) {
		$this->crumbs[$link] = array( $label, $icon );
	}
/**
* Adds a table 'cell' to the right-aligned bread-crumbs
*
* Cells are added from left to right.
*/
	function addCrumbRight( $data='', $attribs='', $prefix='', $suffix='' ) {
		$this->cells2[] = array( $attribs, $data, $prefix, $suffix );
	}
/**
* Creates a standarised, right-aligned delete bread-crumb and icon.
*/
	function addCrumbDelete( $title, $canDelete='', $msg='' ) {
		global $AppUI;
		/*$this->addCrumbRight(
			''
			. '<a href="javascript:delIt()" title="'.($canDelete?'':$msg).'">'
			. dPshowImage( './images/icons/'.($canDelete?'stock_delete-16.png':'stock_trash_full-16.png'), '16', '16',  '' )
			. '</a>'
			. '</td><td>&nbsp;'
			. '<a href="javascript:delIt()" title="'.($canDelete?'':$msg).'">' . $AppUI->_( $title ) . '</a>'
			. ''
		);*/
		$this->addCrumb("javascript:delIt()",$title);
	}
/**
*	View links tabbed : flat
*/

	function showTabFlat() {
			global $AppUI;//.$AppUI->_('Flat').
			$s .= "<td nowrap=\"nowrap\">";
			$s .= "&nbsp;<a href=\"".$this->baseHRef."&tab=0\">".dPshowImage( dPFindImage( "tabbed.jpg" ),"","",$AppUI->_("Tabbed"))."</a>&nbsp;";
			$s .= "<a href=\"".$this->baseHRef."&tab=-1\">".dPshowImage( dPFindImage( "flat.jpg" ),"","",$AppUI->_("Flat"))."</a>";
			$s .= "</td>\n";
			return $s;
	}
	
/**
* The drawing function
*/
	function show() {
		global $AppUI;
		$dialog = dPgetParam( $_GET, 'dialog', 0 );
		if (!$dialog) {
			// top navigation menu
			$nav = $AppUI->getMenuModules();
			$perms =& $AppUI->acl();
		}
		$links = array();
		//$links[0]= '<b><a href="./index.php?m=tasks&a=todo">'.$AppUI->_('Todo').'</a></b>';
		foreach ($nav as $module) {
			foreach ($AppUI->user_groups as $g => $sc)
				if ($perms->checkModule($module['mod_directory'], 'access','',$sc)) {
					$links[] = '<a href="?m='.$module['mod_directory'].'">'.$AppUI->_($module['mod_ui_name']).'</a>';
					break;
				}
		}
		//$links[]= '<b>'.dPcontextHelp( 'Help' ).'</b>';
		
		$CR = "\n";
		$CT = "\n\t";
		
		$s = $CR . '<table width="100%" class="title">';
		$s .= $CR . '<tr height="26">';
		
		if ($this->icon) {
			$s .= $CR . '<td height="52" width="42" rowspan="2" >';
			$s .= dPshowImage( dPFindImage( $this->icon, $this->module ));
			$s .= '</td>';
		}
		$s .= $CR . '<td height="52" align="left" width="230" nowrap rowspan="2"><h1>' . $AppUI->_($this->title) . '</h1></td>';
		$s .=	'<td height="26" align="left" class="nav1" nowrap="nowrap">&nbsp;&nbsp;'.implode( ' | ', $links ).$CR.'
				 &nbsp;&nbsp;</td>';
		$s .= '<td rowspan="2" class="logout" height="52" width="52" align="center"><i><a href="./index.php?logout=-1">
				<img src="' . dPfindImage('logout2.jpg').'" align="center" border="" alt="'.$AppUI->_('Logout').'" /></a></i></td>
			</tr><tr height="26">';

		if (count( $this->crumbs ) || count( $this->cells2 )) {
			$crumbs = array();
			foreach ($this->crumbs as $k => $v) {
				$t = $v[1] ? '<img src="' . dPfindImage( $v[1], $this->module ) . '" border="" alt="" />&nbsp;' : '';
				$t .= $AppUI->_( $v[0] );
				$crumbs[] = "<a href=\"$k\">$t</a>";
			}
			/*$s .= "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
			$s .= "\n<tr>";*/
			$s .= "\n\t<td class=\"nav2\" nowrap=\"nowrap\">&nbsp;";
			$s .= "\n\t\t" . implode( ' <strong>|</strong> ', $crumbs );
			$s .= "\n\t</td>";

			foreach ($this->cells2 as $c) {
				$s .= $c[2] ? "\n$c[2]" : '';
				$s .= "\n\t<td  class=\"nav2\" align=\"left\" nowrap=\"nowrap\"" . ($c[0] ? " $c[0]" : '') . '>';
				$s .= $c[1] ? "\n\t$c[1]" : '&nbsp;';
				$s .= "\n\t</td>";
				$s .= $c[3] ? "\n\t$c[3]" : '';
			}

		//	$s .= "\n</td></tr>\n</table>";
		}
		
		if (!count( $this->crumbs ))
			$s .= "\n\t<td class=\"nav2\" nowrap=\"nowrap\">&nbsp;</td>";
		//	ELENCO DELLE VOCI DI OGNI MODULO;
		// DA ELIMINARE IN SEGUITO...E' solo per test non perdere roba a giro
		foreach ($this->menuVoices as $c) {
			$s .= $c[2] ? $CR . $c[2] : '';
			$s .= $CR . '<td nowrap="nowrap"' . ($c[0] ? " $c[0]" : '') . '>';
			$s .= $c[1] ? $CT . $c[1] : '&nbsp;&nbsp;';
			$s .= $CR . '</td>';
			$s .= $c[3] ? $CR . $c[3] : '';
		}
		
		$s .= '</tr></table>';
		$s .= '<table width="100%" border="0" cellpadding="1" cellspacing="1"><tr>';
		$s .= $AppUI->getMsg(true,true);// STAMPA ADDED o UPDATE o ERROR...
		$s .= '<td align="left" width="100%" nowrap="nowrap">&nbsp;</td>';

		if (!$dialog) {
			// top navigation menu
			$nav = $AppUI->getMenuModules();
			$perms =& $AppUI->acl();
		}
		
		
		// END showMenu 
		
		//  DA RIMETTERE A POSTO

		foreach ($this->cells1 as $c) {
			$s .= $c[2] ? $CR . $c[2] : '';
			$s .= $CR . '<td align="left" nowrap="nowrap"' . ($c[0] ? " $c[0]" : '') . '>';
			$s .= $c[1] ? $CT . $c[1] : '&nbsp;';
			$s .= $CR . '</td>';
			$s .= $c[3] ? $CR . $c[3] : '';
		}
		/*if ($this->showhelp) {
			$s .= '<td nowrap="nowrap" width="20" align="right">';
			//$s .= $CT . contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', $this->helpref );

			$s .= "\n\t<a href=\"#$this->helpref\" onClick=\"javascript:window.open('?m=help&dialog=1&hid=$this->helpref', 'contexthelp', 'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes')\" title=\"".$AppUI->_( 'Help' )."\">";
			$s .= "\n\t\t" . dPshowImage( './images/icons/stock_help-16.png', '16', '16', $AppUI->_( 'Help' ) );
			$s .= "\n\t</a>";
			$s .= "\n</td>";
		}*/
		if (!is_null($this->baseHRef))
		  $s .= $this->showTabFlat();
			
		$s .= "\n</tr>";
		$s .= "\n</table>";

		
		echo "$s";
	}
}
// !! Ensure there is no white space after this close php tag.
?>
