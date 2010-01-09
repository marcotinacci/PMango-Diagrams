<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      project roles.

 File:       prole.php
 Location:   pmango\modules\system
 Started:    2006.02.01
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history. 
 - 2006.07.30 Lorenzo
   First version, created to manage PMango project roles.
   
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

// Check permissions
$perms =& $AppUI->acl();
$canEdit = $perms->checkModule($m, 'edit','',$AppUI->user_groups[-1]);
if (!$canEdit && $transmit_user_id != $AppUI->user_id) {
  $AppUI->redirect("m=public&a=access_denied" );
}

$sql = "
SELECT
        proles_id,
        proles_name,
        proles_description,
        proles_hour_cost
FROM project_roles
WHERE proles_status=0
ORDER BY proles_hour_cost DESC
";

$proles = NULL;
$ptrc=db_exec($sql);
$nums=db_num_rows($ptrc);
echo db_error();
for ($x=0; $x < $nums; $x++) {
        $row = db_fetch_assoc ( $ptrc ) ;
        $proles[] = $row;
}

function showcodes(&$a) {
        global $AppUI;

        $s = "\n<tr height=20>";
        $s .= "<td width=40 align=\"center\"><a href=\"javascript:delIt2({$a['proles_id']});\" title=\"".$AppUI->_('delete')."\"><img src=\"./images/icons/stock_delete-16.png\" border=\"0\" alt=\"Delete\"></a></td>";
        $alt = htmlspecialchars( $a["proles_description"] );
        $s .= "<td align=left>&nbsp;".$a["proles_name"]."</td>";
        $s .= '<td nowrap="nowrap" align=center>'.$a["proles_hour_cost"].'</td>';
        $s .= '<td align="center" nowrap="nowrap">'.$a["proles_description"].'</td>';
        $s .= "</tr>\n";
        echo $s;
}

$titleBlock = new CTitleBlock( 'Edit Project Roles', 'myevo-weather.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "System admin" );
$titleBlock->show();

?>
<script language="javascript">
function submitIt(){
        var form = document.changeuser;
        form.submit();
}

function changeIt() {
        var f=document.changeMe;
        var msg = '';
        f.submit();
}


function delIt2(id) {
        document.frmDel.proles_id.value = id;
        document.frmDel.submit();
}
</script>

<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<form name="frmDel" action="./index.php?m=system" method="post">
        <input type="hidden" name="dosql" value="do_prole_aed" />
        <input type="hidden" name="del" value="1" />
        <input type="hidden" name="proles_id" value="" />
</form>

<form name="changeuser" action="./index.php?m=system" method="post">
        <input type="hidden" name="dosql" value="do_prole_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="proles_status" value="0" />

<tr height="20">
        <th width="40">&nbsp;</th>
        <th align="center" width="120"><?php echo $AppUI->_('Project Role');?></th>
        <th align="center" width="50"><?php echo $AppUI->_('Hour Cost');?></th>
        <th align="center"><?php echo $AppUI->_('Description');?></th>      
</tr>

<?php
        for ($s=0; $s < count($proles); $s++) {
                showcodes( $proles[$s],1);
        }
?>

<tr>
        <td>&nbsp;</td>
        <td width="120"><input type="text" name="proles_name" value=""></td>
        <td width="50"><input type="text" name="proles_hour_cost" size="4" value=""></td>
        <td align="center"><input type="text" name="proles_description" size="80" value=""></td>
</tr>
<tr>
        <td align="left"><input class="button" type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" /></td>
        <td colspan="3" align="right"><input class="button" type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" /></td>
</tr>
</form>
</table>

