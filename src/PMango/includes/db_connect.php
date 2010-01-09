<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      database connection funtions

 File:       db_connect.php
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
* Generic functions based on library function (that is, non-db specific)
*
* @todo Encapsulate into a database object
*/

// load the db specific handlers
//require_once( "{$dPconfig['root_dir']}/includes/db_{$dPconfig['dbtype']}.php" );
//require_once( "./includes/db_adodb.php" );
require_once "$baseDir/includes/db_adodb.php";

// make the connection to the db
db_connect( $dPconfig['dbhost'], $dPconfig['dbname'],
	$dPconfig['dbuser'], $dPconfig['dbpass'], $dPconfig['dbpersist'] );

	
/*
* Having successfully established the database connection now,
* we will hurry up to load the system configuration details from the database.
*/

$sql = "SELECT config_name, config_value, config_type FROM config";
$rs = $db->Execute($sql);

if ($rs) { // Won't work in install mode.
	$rsArr = $rs->GetArray();

	foreach ($rsArr as $c) {
		if ($c['config_type'] == 'checkbox') {
			$c['config_value'] = ($c['config_value'] == 'true') ? true : false;
		}
		$dPconfig["{$c['config_name']}"] = $c['config_value'];
	}
}



/**
* This global function loads the first field of the first row returned by the query.
*
* @param string The SQL query
* @return The value returned in the query or null if the query failed.
*/
function db_loadResult( $sql ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$ret = null;
	if ($row = db_fetch_row( $cur )) {
		$ret = $row[0];
	}
	db_free_result( $cur );
	return $ret;
}

/**
* This global function loads the first row of a query into an object
*
* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
* If <var>object</var> has a value of null, then all of the returned query fields returned in the object. 
* @param string The SQL query
* @param object The address of variable
*/
function db_loadObject( $sql, &$object, $bindAll=false , $strip = true) {
	if ($object != null) {
		$hash = array();
		if( !db_loadHash( $sql, $hash ) ) {
			return false;
		}

		bindHashToObject( $hash, $object, null, $strip, $bindAll );
		return true;
	} else {
		$cur = db_exec( $sql );
		$cur or exit( db_error() );
		if ($object = db_fetch_object( $cur )) {
			db_free_result( $cur );
			return true;
		} else {
			$object = null;
			return false;
		}
	}
}

/**
* This global function return a result row as an associative array 
*
* @param string The SQL query
* @param array An array for the result to be return in
* @return <b>True</b> is the query was successful, <b>False</b> otherwise
*/
function db_loadHash( $sql, &$hash ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hash = db_fetch_assoc( $cur );
	db_free_result( $cur );
	if ($hash == false) {
		return false;
	} else {
		return true;
	}
}

/**
* Document::db_loadHashList()
*
* { Description }
*
* @param string $index
*/
function db_loadHashList( $sql, $index='' ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hashlist = array();
	while ($hash = db_fetch_array( $cur )) {
		$hashlist[$hash[$index ? $index : 0]] = $index ? $hash : $hash[1];
	}
	db_free_result( $cur );
	return $hashlist;
}

/**
* Document::db_loadList()
*
* { Description }
*
* @param [type] $maxrows
*/
function db_loadList( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	while ($hash = db_fetch_assoc( $cur )) {
		$list[] = $hash;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/**
* Document::db_loadColumn()
*
* { Description }
*
* @param [type] $maxrows
*/
function db_loadColumn( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	$row_index = null;
	while ($row = db_fetch_row( $cur )) {
		if (! isset($row_index)) {
			if (isset($row[0])) {
				$row_index = 0;
			} else {
				$row_indices = array_keys($row);
				$row_index = $row_indices[0];
			}
		}
		$list[] = $row[$row_index];
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/* return an array of objects from a SQL SELECT query
 * class must implement the Load() factory, see examples in Webo classes
 * @note to optimize request, only select object oids in $sql
 */
function db_loadObjectList( $sql, $object, $maxrows = NULL ) {
	$cur = db_exec( $sql );
	if (!$cur) {
		die( "db_loadObjectList : " . db_error() );
	}
	$list = array();
	$cnt = 0;
	$row_index = null;
	while ($row = db_fetch_array( $cur )) {
		if (! isset($row_index)) {
			if (isset($row[0]))
				$row_index = 0;
			else {
				$row_indices = array_keys($row);
				$row_index = $row_indices[0];
			}
		}
		$object->load( $row[$row_index] );
		$list[] = $object;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}


/**
* Document::db_insertArray()
*
* { Description }
*
* @param [type] $verbose
*/
function db_insertArray( $table, &$hash, $verbose=false ) {
	$fmtsql = "insert into $table ( %s ) values( %s ) ";
	foreach ($hash as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . db_escape(strip_tags( $v )) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );

	($verbose) && print "$sql<br />\n";

	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	return true;
}

/**
* Document::db_updateArray()
*
* { Description }
*
* @param [type] $verbose
*/
function db_updateArray( $table, &$hash, $keyName, $verbose=false ) {
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	foreach ($hash as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) // internal or NA field
			continue;

		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . db_escape( $v ) . "'";
			continue;
		}
		if ($v == '') {
			$val = 'NULL';
		} else {
			$val = "'" . db_escape(strip_tags( $v )) . "'";
		}
		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	($verbose) && print "$sql<br />\n";
	$ret = db_exec( $sql );
	return $ret;
}

/**
* Document::db_delete()
*
* { Description }
*
*/
function db_delete( $table, $keyName, $keyValue ) {
	$keyName = db_escape( $keyName );
	$keyValue = db_escape( $keyValue );
	$ret = db_exec( "DELETE FROM $table WHERE $keyName='$keyValue'" );
	return $ret;
}


/**
* Document::db_insertObject()
*
* { Description }
*
* @param [type] $keyName
* @param [type] $verbose
*/
function db_insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
	$fmtsql = "INSERT INTO `$table` ( %s ) VALUES ( %s ) ";
	foreach (get_object_vars( $object ) as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		if ($k[0] == '_') { // internal field
			continue;
		}
		$fields[] = $k;
		//$values[] = "'" . db_escape(htmlentities(strip_tags( $v ), ENT_COMPAT ,$locale_char_set)) . "'";
		$values[] = "'" . db_escape(strip_tags( $v )) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	($verbose) && print "$sql<br />\n";
	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	($verbose) && print "id=[$id]<br />\n";
	if ($keyName && $id)
		$object->$keyName = $id;
	return true;
}

/**
* Document::db_updateObject()
*
* { Description }
*
* @param [type] $updateNulls
*/
function db_updateObject( $table, &$object, $keyName, $updateNulls=true ) {
	$fmtsql = "UPDATE `$table` SET %s WHERE %s";
	foreach (get_object_vars( $object ) as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
			continue;
		}
		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . db_escape( $v ) . "'";
			continue;
		}
		if ($v === NULL && !$updateNulls) {
			continue;
		}
		if( $v == '' ) {
			$val = "''";
		} else {
			$val = "'" . db_escape(strip_tags( $v )). "'";
		}
		$tmp[] = "$k=$val";
	}
	if (count ($tmp)) {
		$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
		return db_exec( $sql );
	} else {
		return true;
	}
}

/**
* Document::db_dateConvert()
*
* { Description }
*
*/
function db_dateConvert( $src, &$dest, $srcFmt ) {
	$result = strtotime( $src );
	$dest = $result;
	return ( $result != 0 );
}

/**
* Document::db_datetime()
*
* { Description }
*
* @param [type] $timestamp
*/
function db_datetime( $timestamp = NULL ) {
	if (!$timestamp) {
		return NULL;
	}
	if (is_object($timestamp)) {
		return $timestamp->toString( '%Y-%m-%d %H:%M:%S');
	} else {
		return strftime( '%Y-%m-%d %H:%M:%S', $timestamp );
	}
}

/**
* Document::db_dateTime2locale()
*
* { Description }
*
*/
function db_dateTime2locale( $dateTime, $format ) {
	if (intval( $dateTime)) {
		$date = new CDate( $dateTime );
		return $date->format( $format );
	} else {
		return null;
	}
}

/*
* copy the hash array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
* @param boolean
*/
function bindHashToObject( $hash, &$obj, $prefix=NULL, $checkSlashes=true, $bindAll=false ) {
	is_array( $hash ) or die( "bindHashToObject : hash expected" );
	is_object( $obj ) or die( "bindHashToObject : object expected" );

	if ($bindAll) {
		foreach ($hash as $k => $v) {
			$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
		}
	} else if ($prefix) {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$prefix . $k ])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		}
	} else {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$k])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		}
	}
	//echo "obj="; print_r($obj); echo"\n\n";exit;
}
?>
