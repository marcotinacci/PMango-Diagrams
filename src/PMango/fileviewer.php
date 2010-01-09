<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      file viewer.

 File:       fileviewer.php
 Location:   pmango
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

//file viewer
$baseDir = dirname(__FILE__);
$baseUrl = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
if (@$pathInfo) {
  $baseUrl .= dirname($pathInfo);
} else {
  $baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
}

$dPconfig = array();

require_once "$baseDir/includes/config.php";
require_once "$baseDir/includes/main_functions.php";
require_once "$baseDir/classes/ui.class.php";
require_once "$baseDir/includes/db_adodb.php";
require_once "$baseDir/includes/db_connect.php";
require_once "$baseDir/includes/session.php";

$loginFromPage = 'fileviewer.php';

dPsessionStart();

// check if session has previously been initialised
// if no ask for logging and do redirect
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    $_SESSION['AppUI'] = new CAppUI();
	$AppUI =& $_SESSION['AppUI'];
	$AppUI->setConfig( $dPconfig );
	$AppUI->checkStyle();
	 
	require_once( $AppUI->getSystemClass( 'dp' ) );
	require_once( "$baseDir/misc/debug.php" );

	if ($AppUI->doLogin()) $AppUI->loadPrefs( 0 );
	// check if the user is trying to log in
	if (isset($_REQUEST['login'])) {
		$username = dPgetParam( $_POST, 'username', '' );
		$password = dPgetParam( $_POST, 'password', '' );
		$redirect = dPgetParam( $_REQUEST, 'redirect', '' );
		$ok = $AppUI->login( $username, $password );
		if (!$ok) {
			//display login failed message 
			$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
			$AppUI->setMsg( 'Login Failed' );
			require "$baseDir/style/$uistyle/login.php";
			session_unset();
			exit;
		}
		header ( "Location: fileviewer.php?$redirect" );
		exit;
	}	

	$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $dPconfig['host_style'];
	// check if we are logged in
	if ($AppUI->doLogin()) {
	    $AppUI->setUserLocale();
		@include_once( "$baseDir/locales/$AppUI->user_locale/locales.php" );
		@include_once( "$baseDir/locales/core.php" );
		setlocale( LC_TIME, $AppUI->user_locale );
		
		$redirect = @$_SERVER['QUERY_STRING'];
		if (strpos( $redirect, 'logout' ) !== false) $redirect = '';	
		if (isset( $locale_char_set )) header("Content-type: text/html;charset=$locale_char_set");
		require "$baseDir/style/$uistyle/login.php";
		session_unset();
		session_destroy();
		exit;
	}	
}
$AppUI =& $_SESSION['AppUI'];

require_once "$baseDir/includes/permissions.php";

$perms =& $AppUI->acl();

$canRead = $perms->checkModule( 'files' , 'view' );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;

if ($file_id) {
	// projects tat are denied access
	require_once($AppUI->getModuleClass("projects"));
	require_once($AppUI->getModuleClass("files"));
	$project =& new CProject;
	$allowedProjects = $project->getAllowedRecords($AppUI->user_id, 'project_id, project_name');
	$fileclass =& new CFile;
	$allowedFiles = $fileclass->getAllowedRecords($AppUI->user_id, 'file_id, file_name');
	
	if (count($allowedFiles) && ! array_key_exists($file_id, $allowedFiles)) {
		$AppUI->redirect( 'm=public&a=access_denied' );
	}

	if (count($allowedProjects)) {
		$allowedProjects[0] = 'All Projects';
	}

	$q = new DBQuery;
	$q->addTable('files');
	$project->setAllowedSQL($AppUI->user_id, $q, 'file_project');
	$q->addWhere("file_id = '$file_id'");
	/*
	$sql = "SELECT *
	FROM files
	WHERE file_id=$file_id"
	  . (count( $allowedProjects ) > 0 ? "\nAND file_project IN (" . implode(',', array_keys($allowedProjects) ) . ')' : '');
	*/
	$sql = $q->prepare();

	if (!db_loadHash( $sql, $file )) {
		$AppUI->redirect( "m=public&a=access_denied" );
	};

	/*
	 * DISABLED LINES TO FIX A NEWER BUG 914075 WITH IE 6 (GREGORERHARDT 20040612)

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	// END extra headers to resolve IE caching bug
	*/

	$fname = "$baseDir/files/{$file['file_project']}/{$file['file_real_filename']}";
	if (! file_exists($fname)) {
		$AppUI->setMsg("fileIdError", UI_MSG_ERROR);
		$AppUI->redirect();
	}

	header("MIME-Version: 1.0");
	header( "Content-length: {$file['file_size']}" );
	header( "Content-type: {$file['file_type']}" );
	header( "Content-transfer-encoding: 8bit");
	header( "Content-disposition: inline; filename=\"{$file['file_name']}\"" );

	// read and output the file in chunks to bypass limiting settings in php.ini
	$handle = fopen("{$dPconfig['root_dir']}/files/{$file['file_project']}/{$file['file_real_filename']}", 'rb');
	while ( !feof($handle) ) {
		print fread($handle, 8192);
	}
	fclose($handle);
} else {
	$AppUI->setMsg( "fileIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>
