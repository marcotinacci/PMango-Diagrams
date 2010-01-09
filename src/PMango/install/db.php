<?php /**
---------------------------------------------------------------------------

 PMango Project

 Title:      layout for database operations to install PMango

 File:       db.php
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
?>
<html>
<head>
	<title>PMango Installer</title>
	<meta name="Description" content="PMango Installer">
 	<link rel="stylesheet" type="text/css" href="../style/default/main.css">
</head>
<body>
<h1><img src="mango.png" align="middle" alt="PMango Logo"/>&nbsp;PMango Installer</h1>
<?php 
if ( $_POST['mode'] == 'upgrade')
	@include_once "../includes/config.php";
else if (is_file( "../includes/config.php" ))
	die("Security Check: pmango seems to be already configured. Install aborted!");
else
	@include_once "../includes/config-dist.php";

?>
<form name="instFrm" action="do_install_db.php" method="post">
<input type='hidden' name='mode' value='<?php echo $_POST['mode']; ?>' />
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="center">
        <tr>
            <td class="title" colspan="2">Database Settings</td>
        </tr>
         <tr>
            <td class="item">Database Server Type: <span class='warning'>Note - currently only MySQL is known to work correctly</span></td>
            <td align="left">
		<select name="dbtype" size="1" style="width:200px;" class="text">
<?php
   if (strstr('WIN', strtoupper(PHP_OS)) !== false) {
?>
			<option value="access">MS Access</option>
			<option value="ado">Generic ADO</option>
			<option value="ado_access">ADO to MS Access Backend</option>
			<option value="ado_mssql">ADO to MS SQL Server</option>

			<option value="vfp">MS Visual FoxPro</option>
			<option value="fbsql">FrontBase</option>
<?php
}
?>
			<option value="db2">IBM DB2</option>
			<option value="ibase">Interbase 6 or earlier</option>
			<option value="firebird">Firebird</option>
			<option value="borland_ibase">Borland Interbase 6.5 and Later</option>

			<option value="informix">Informix 7.3 or later</option>
			<option value="informix72">Informix 7.2 or earlier</option>
			<option value="ldap">LDAP</option>
			<option value="mssql">MS SQL Server 7 and later</option>
			<option value="mssqlpro">Portable MS SQL Server</option>
			<option value="mysql" selected="selected">MySQL - Recommended</option>

			<option value="mysqlt">MySQL With Transactions</option>
			<option value="maxsql">MySQL MaxDB</option>
			<option value="oci8">Oracle 8/9</option>
			<option value="oci805">Oracle 8.0.5</option>
			<option value="oci8po">Oracle 8/9 Portable</option>
			<option value="odbc">ODBC</option>

			<option value="odbc_mssql">MS SQL Server via ODBC</option>
			<option value="odbc_oracle">Oracle via ODBC</option>
			<option value="odbtp">Generic Odbtp</option>
			<option value="odbtp_unicode">Odbtp With Unicode Support</option>
			<option value="oracle">Older Oracle</option>
			<option value="netezza">Netezza</option>

			<option value="postgres">Generic PostgreSQL</option>
			<option value="postgres64">PostreSQL 6.4 and earlier</option>
			<option value="postgres7">PostgreSQL 7</option>
			<option value="sapdb">SAP DB</option>
			<option value="sqlanywhere">Sybase SQL Anywhere</option>
			<option value="sqlite">SQLite</option>

			<option value="sqlitepo">Portable SQLite</option>
			<option value="sybase">Sybase</option>
		</select>
	   </td>
  	 </tr>
         <tr>
            <td class="item">Database Host Name:</td>
            <td align="left"><input class="insText" type="text" name="dbhost" value="<?php echo $dPconfig['dbhost']; ?>" title="The Name of the Host the Database Server is installed on " /></td>
          </tr>
           <tr>
            <td class="item">Database Name:</td>
            <td align="left"><input class="insText" type="text" name="dbname" value="<?php echo  $dPconfig['dbname']; ?>" title="The Name of the Database PMango will use and/or install" /></td>
          </tr>
          <tr>
            <td class="item">Database User Name:</td>
            <td align="left"><input class="insText" type="text" name="dbuser" value="<?php echo $dPconfig['dbuser']; ?>" title="The Database User that PMango uses for Database Connection" /></td>
          </tr>
          <tr>
            <td class="item">Database User Password:</td>
            <td align="left"><input class="insText" type="password" name="dbpass" value="<?php echo $dPconfig['dbpass']; ?>" title="The Password according to the above User." /></td>
          </tr>
           <!--<tr>
            <td class="item">Use Persistent Connection?</td>
            <td align="left"><input type="checkbox" name="dbpersist" value="1" <?php /*echo ($dPconfig['dbpersist']==true) ? 'checked="checked"' : '';*/ ?> title="Use a persistent Connection to your Database Server." /></td>
          </tr>-->
<?php if ($_POST['mode'] == 'install') { ?>
        <tr>
            <td class="item">Drop Existing Database?</td>
            <td align="left"><input type="checkbox" checked name="dbdrop" value="1" title="Deletes an existing Database before installing a new one. This deletes all data in the given database. Data cannot be restored." /><span class="item"> If checked, existing Data will be lost!</span></td>
        </tr>
        <tr>
            <td class="item">Import data:</td>
            <td align="left"><input type="file" onchange="javascript:
				instFrm.sql.value=instFrm.sql_file.value.slice(0,2)+'/'+instFrm.sql_file.value.slice(3);" 
				 name="sql_file" class="insText" title="Insert sql file path downloaded with backup module. Leave blank this field, if you want initialize PMango with only table structure and no data." ></td>
            <input type="hidden" id="sql" name="sql">
        </tr>
<?php } ?>
       
      <!--    <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
          <tr>
            <td class="title" colspan="2">Download existing Data (Recommended)</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Download a XML Schema File containing all Tables for the database entered above
            by clicking on the Button labeled 'Download XML' below. This file can be used with the Backup module to restore a previous system. Depending on database size and system environment this process can take some time.
	    <br/>PLEASE CHECK THE RECEIVED FILE IMMEDIATELY FOR CONTENT AND CONSISTENCY AS ERROR MESSAGES ARE PRINTED INTO THIS FILE.<br/><br /><b>THIS FILE CAN ONLY BE RESTORED WITH A WORKING PMango 2.x SYSTEM WITH THE BACKUP MODULE INSTALLED. DO NOT RELY ON THIS AS YOUR ONLY BACKUP.</b></td>
        </tr>
        <tr>
            <td class="item">Receive XML Backup Schema File</td>
            <td align="left"><input class="button" type="submit" name="dobackup" value="Download XML" title="Click here to retrieve a XML file containing your data that can be stored on your local system." /></td>
        </tr>-->
          <tr>
            <td align="left"><br /><input class="button" type="submit" name="do_db" value="<?php //echo $_POST['mode']; ?> db only" title="Try to set up the database with the given information." />
	    &nbsp;<input class="button" type="submit" name="do_cfg" value="write config file only" title="Write a config file with the details only." /></td>
	  <td align="right" class="item"><br />(Recommended) &nbsp;<input class="button" type="submit" name="do_db_cfg" value="<?php //echo $_POST['mode']; ?> db & write cfg" title="Write config file and setup the database with the given information." />
   		</td>
          </tr>
        </table>
</form>
</body>
</html>
