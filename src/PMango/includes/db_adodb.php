<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      adodb funtions

 File:       db_adodb.php
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

	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer
	-----------------------
	ADODB VERSION
	-----------------------
	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/
require_once( "$baseDir/lib/adodb/adodb.inc.php" );

$db = NewADOConnection($dPconfig['dbtype']);

function db_connect( $host='localhost', $dbname, $user='root', $passwd='', $persist=false ) {
        global $db, $ADODB_FETCH_MODE;

	if ($persist) {
                $db->PConnect($host, $user, $passwd, $dbname)
			or die( 'FATAL ERROR: Connection to database server failed' );
	} else {
                $db->Connect($host, $user, $passwd, $dbname)
			or die( 'FATAL ERROR: Connection to database server failed' );
	}

        $ADODB_FETCH_MODE=ADODB_FETCH_BOTH;
}

function db_error() {
        global $db;
	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	return $db->ErrorMsg();
}

function db_errno() {
        global $db;
	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	return $db->ErrorNo();
}

function db_insert_id() {
        global $db;
	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	return $db->Insert_ID();
}

function db_exec( $sql ) {
        global $db, $baseDir;

	if (! is_object($db))
	  dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	$qid = $db->Execute( $sql );
	dprint(__FILE__, __LINE__, 10, $sql);
	if ($msg = db_error())
        {
                global $AppUI;
                dprint(__FILE__, __LINE__, 0, "Error executing: <pre>$sql</pre>");
		// Useless statement, but it is being executed only on error, 
		// and it stops infinite loop.
		$db->Execute( $sql );
		if (!db_error())
			echo '<script language="JavaScript"> location.reload(); </script>';
        }
        if ( ! $qid && preg_match('/^\<select\>/i', $sql) )
	  dprint(__FILE__, __LINE__, 0, $sql);
	return $qid;
}

function db_free_result($cur ) {
        // TODO
        //	mysql_free_result( $cur );
        // Maybe it's done my Adodb
	if (! is_object($cur))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_free_result");
        $cur->Close();
}

function db_num_rows( $qid ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_num_rows");
	return $qid->RecordCount();
        //return $db->Affected_Rows();
}

function db_fetch_row( &$qid ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_row");
	return $qid->FetchRow();
}

function db_fetch_assoc( &$qid ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_assoc");
        return $qid->FetchRow();
}

function db_fetch_array( &$qid  ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_array");
        $result = $qid->FetchRow();
	// Ensure there are numerics in the result.
	if ($result && ! isset($result[0])) {
	  $ak = array_keys($result);
	  foreach ($ak as $k => $v) {
	    $result[$k] = $result[$v];
	  }
	}
	return $result;
}

function db_fetch_object( $qid  ) {
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_object");
	return $qid->FetchNextObject(false);
}

function db_escape( $str ) {
        global $db;
	return substr($db->qstr( $str ), 1, -1);
}

function db_version() {
        return "ADODB";
}

function db_unix2dateTime( $time ) {
        global $db;
        return $db->DBDate($time);
}

function db_dateTime2unix( $time ) {
        global $db;

        return $db->UnixDate($time);

        // TODO - check if it's used anywhere...
//	if ($time == '0000-00-00 00:00:00') {
//		return -1;
//	}
}
?>
