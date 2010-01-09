<?php
/**
-------------------------------------------------------------------------------------------

 PMango Project

 Title:      reports page.

 File:       view.php
 Location:   PMango\modules\report
 Started:    2007.05.08
 Author:     Riccardo Nicolini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2007.10.20 Marco
   Now the report's pages it's opened in a new windows.
 - 2007.05.08 Riccardo
   First version, created to manage .pdf files generation.
   
-------------------------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi, Riccardo Nicolini
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

-------------------------------------------------------------------------------------------
*/
$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );
$projects = $AppUI->getState('Projects');

$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('projects.project_id, project_color_identifier, project_name, project_description,
	project_start_date, project_finish_date');
$q->addWhere("projects.project_id = '$project_id'");

$project = $q->loadList();
$q  = new DBQuery;
$q->addTable('groups');
$q->addTable('projects');
$q->addQuery('groups.group_name');
$q->addWhere("projects.project_group = groups.group_id and projects.project_id = '$project_id'");

$group = $q->loadList();

foreach ($project as $p){
	$p_color=$p['project_color_identifier'];
	$name=$p['project_name'];
}

foreach ($group as $g){
	$group_name=$g['group_name'];
}

$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ReportIdxTab', $_GET['tab'] );
}

$AppUI->setState( 'report_page', dPgetParam($_POST, 'page', 'P') );
$AppUI->setState( 'report_color', dPgetParam($_POST, 'color', 1) );
$AppUI->setState( 'report_border', dPgetParam($_POST, 'border', 1) );

$page = $AppUI->getState( 'report_page');
$color = $AppUI->getState( 'report_color' );
$border = $AppUI->getState( 'report_border' );

$tab = $AppUI->getState( 'ReportIdxTab' ) !== NULL ? $AppUI->getState( 'ReportIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ReportIdxTab' ) );

$titleBlock = new CTitleBlock( 'Project Reports', 'applet-report.png', $m, "$m.$a" );
$titleBlock->addCell();

$titleBlock->show();

GLOBAL $AppUI, $canRead, $canEdit, $m;

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$user_id = $AppUI->user_id;

$sql="SELECT * FROM reports WHERE project_id=".$project_id." AND user_id=".$user_id;
$exist=db_loadList($sql);

if(count($exist)==0){
$sql="INSERT INTO `reports` ( `report_id` , `project_id` , `user_id` , `p_is_incomplete` , `p_report_level` , `p_report_roles` , `p_report_sdate` , `p_report_edate` , `p_report_opened` , `p_report_closed` , `a_is_incomplete` , `a_report_level` , `a_report_roles` , `a_report_sdate` , `a_report_edate` , `a_report_opened` , `a_report_closed` , `l_hide_inactive` , `l_hide_complete` , `l_user_id` , `l_report_sdate` , `l_report_edate` , `properties`, `prop_summary` )
VALUES ( NULL , ".$project_id." , ".$user_id." , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL , NULL, NULL);";		
		
db_exec( $sql ); db_error();
}


if($_GET['reset']){
	if($_GET['reset']=='actual'){
		$sql="UPDATE reports SET a_is_incomplete = NULL ,a_report_level = NULL ,a_report_roles = NULL ,a_report_sdate = NULL ,a_report_edate = NULL ,a_report_opened = NULL ,a_report_closed = NULL WHERE reports.project_id =".$project_id." AND reports.user_id=".$user_id;}
		
	if($_GET['reset']=='planned'){
		$sql="UPDATE reports SET p_is_incomplete = NULL ,p_report_level = NULL ,p_report_roles = NULL ,p_report_sdate = NULL ,p_report_edate = NULL ,p_report_opened = NULL ,p_report_closed = NULL WHERE reports.project_id =".$project_id." AND reports.user_id=".$user_id;}
		
	if($_GET['reset']=='properties'){
		$sql="UPDATE reports SET properties = NULL ,prop_summary = NULL WHERE reports.project_id =".$project_id." AND reports.user_id=".$user_id;}
		
	if($_GET['reset']=='log'){
		$sql="UPDATE reports SET l_hide_inactive = NULL ,l_hide_complete = NULL ,l_user_id = NULL ,l_report_sdate = NULL ,l_report_edate = NULL WHERE reports.project_id =".$project_id." AND reports.user_id=".$user_id;}			
	
	db_exec( $sql ); db_error();	
	
}


$sql="SELECT p_report_sdate, a_report_sdate, l_report_sdate, properties FROM reports WHERE reports.project_id=".$project_id." AND user_id=".$user_id;
$disable_report = db_loadList($sql);
?>

<script language="javascript">
var state = 'hidden';

function showhide(layer_ref) {
	if (state == '') {
		state = 'none';
	}
	else {
		state = '';
	}
	if (document.all) { //IS IE 4 or 5 (or 6 beta)
		eval( "document.all." + layer_ref + ".style.display = state");
	}
	if (document.layers) { //IS NETSCAPE 4 or below
		document.layers[layer_ref].display = state;
	}
	if (document.getElementById && !document.all) {
		maxwell_smart = document.getElementById(layer_ref);
		maxwell_smart.style.display = state;
	}
}

function check(){
	if((!document.make_pdf_options.add_properties.checked)&&(!document.make_pdf_options.add_planned.checked)&&(!document.make_pdf_options.add_actual.checked)&&(!document.make_pdf_options.add_log.checked)){
			alert("Please, select a Report.");
	}else {document.make_pdf_options.submit();}
}
</script>

<form name='make_pdf_options' method='POST' action=<? echo '?m=report&a=view&project_id='.$project_id;?> enctype="multipart/form-data">
<table border="0" cellpadding="1" cellspacing="0" width="100%" class="std">
<tr>
	<td >
	<table border='0' cellpadding='1' cellspacing='0' width='100%'>
			<tr style="border: outset #d1d1cd 1px;background-color:#<?php echo $p_color;?>" >
				<td nowrap='nowrap' colspan='2'>
					<?php 
					echo '<font color="' . bestColor( $p_color ) . '"><strong>'. $name .'<strong></font>';
					?>
				</td>
				<td colspan='2'>
				</td>
				<td nowrap='nowrap' align="center">
					<?echo '<font color="'. bestColor( $p_color ) . '">'.$AppUI->_('Append Order').'</font>';?>&nbsp;
				</td>
				<td nowrap='nowrap' align="center">
					<?echo '<font color="'. bestColor( $p_color ) . '">'.$AppUI->_('New Page').'</font>';?>
				</td>
			</tr>
			<tr>
				<td colspan='6'>
				<br>
				</td>
			</tr>
			<tr>
				<td nowrap='nowrap' align="left">
					<input type="checkbox" name="add_properties" <?echo ($_POST['add_properties'])?"checked":"";echo ($disable_report[0]['properties'])?"":"disabled";?> >
				</td>
				<td nowrap="nowrap">
					<strong><?php echo $AppUI->_( 'Project Properties' );?></strong>
				</td>
				<td width='100%'>
				</td>
				<td nowrap='nowrap' align='left'>
				<?php echo "<a href='./index.php?m=report&a=view&reset=properties&project_id=$project_id'>".$AppUI->_('Reset')."</a>";?>&nbsp;&nbsp;&nbsp;
				<?php echo "<a href='./index.php?m=projects&a=view&tab=0&project_id=$project_id'>".$AppUI->_('Modify')."</a>";?>&nbsp;&nbsp;&nbsp;
				</td>
				<td nowrap="nowrap" align="center">
				<select name="append_order_a" class="text">
					<option value="1" <?echo ($_POST['append_order_a']=="1")? "selected":""?>>1
					<option value="2" <?echo ($_POST['append_order_a']=="2")? "selected":""?>>2
					<option value="3" <?echo ($_POST['append_order_a']=="3")? "selected":""?>>3
					<option value="4" <?echo ($_POST['append_order_a']=="4")? "selected":""?>>4
				</select>
				</td>
				<td nowrap="nowrap" align="center">
				<input type="checkbox" name="new_page_a" <?echo ($_POST['new_page_a'])?"checked":"";?>>
				</td>
			</tr>
			<tr >
				<td nowrap='nowrap'>
				</td>
				<td nowrap='nowrap'>
					<? $task_properties = CReport::getWBSReport($project_id); ?>
				</td>
				<td nowrap='nowrap' colspan='4'width='100%'>
				</td>
			</tr>
			<tr>
				<td colspan='6'>
				<br>
				</td>
			</tr>
			<tr>
				<td nowrap='nowrap' align="left" style="border-top: outset #d1d1cd 1px">
				<input type="checkbox" name="add_planned" <?echo ($_POST['add_planned'])?"checked":"";echo ($disable_report[0]['p_report_sdate'])?"":"disabled";?>>
				</td>
				<td nowrap="nowrap" style="border-top: outset #d1d1cd 1px">
				<strong><?echo $AppUI->_( 'Planned Tasks' );?></strong>
				</td>
				<td width='100%' style="border-top: outset #d1d1cd 1px">
				&nbsp;
				</td>
				<td nowrap='nowrap' align='left' style="border-top: outset #d1d1cd 1px">
				<?php echo "<a href='./index.php?m=report&a=view&reset=planned&project_id=$project_id'>".$AppUI->_('Reset')."</a>";?>&nbsp;&nbsp;&nbsp;
				<?php echo "<a href='./index.php?m=projects&a=view&tab=0&project_id=$project_id'>".$AppUI->_('Modify')."</a>";?>&nbsp;&nbsp;&nbsp;
				</td>
				<td nowrap="nowrap" align="center" style="border-top: outset #d1d1cd 1px">
				<select name="append_order_b" class="text">
					<option value="2" <?echo ($_POST['append_order_b']=="2")? "selected":""?>>2
					<option value="1" <?echo ($_POST['append_order_b']=="1")? "selected":""?>>1
					<option value="3" <?echo ($_POST['append_order_b']=="3")? "selected":""?>>3
					<option value="4" <?echo ($_POST['append_order_b']=="4")? "selected":""?>>4
				</select>
				</td>
				<td nowrap="nowrap" align="center" style="border-top: outset #d1d1cd 1px">
				<input type="checkbox" name="new_page_b" <?echo ($_POST['new_page_b'])?"checked":"";?>>
				</td>
			</tr>
			<tr >
				<td nowrap='nowrap'>
				</td>
				<td nowrap='nowrap'>
					<? $task_planned = CReport::getTaskReport($project_id, 1); ?>
				</td>
				<td nowrap='nowrap' colspan="4">
				</td>
			</tr>
			<tr>
				<td colspan='6'>
				<br>
				</td>
			</tr>
			<tr>
				<td nowrap='nowrap' align="left" style="border-top: outset #d1d1cd 1px">
				<input type="checkbox" name="add_actual" <?echo ($_POST['add_actual'])?"checked":"";echo ($disable_report[0]['a_report_sdate'])?"":"disabled";?>>
				</td>
				<td nowrap="nowrap" style="border-top: outset #d1d1cd 1px">
				<strong><?echo $AppUI->_( 'Actual Tasks' );?></strong>
				</td>
				<td width="100%" style="border-top: outset #d1d1cd 1px">
				&nbsp;
				</td>
				<td nowrap="nowrap" align="left" style="border-top: outset #d1d1cd 1px">
				<?php echo "<a href='./index.php?m=report&a=view&reset=actual&project_id=$project_id'>".$AppUI->_('Reset')."</a>";?>&nbsp;&nbsp;&nbsp;
				<?php echo "<a href='./index.php?m=projects&a=view&tab=1&project_id=$project_id'>".$AppUI->_('Modify')."</a>";?>&nbsp;&nbsp;&nbsp;
				</td>
				<td nowrap="nowrap" align="center" style="border-top: outset #d1d1cd 1px">
				<select name="append_order_c" class="text">
					<option value="3" <?echo ($_POST['append_order_c']=="3")? "selected":""?>>3
					<option value="1" <?echo ($_POST['append_order_c']=="1")? "selected":""?>>1
					<option value="2" <?echo ($_POST['append_order_c']=="2")? "selected":""?>>2
					<option value="4" <?echo ($_POST['append_order_c']=="4")? "selected":""?>>4
				</select>
				</td>
				<td nowrap='nowrap' align="center" style="border-top: outset #d1d1cd 1px">
				<input type="checkbox" name="new_page_c" <?echo ($_POST['new_page_c'])?"checked":"";?>>
				</td>
			</tr>
			<tr >
				<td nowrap='nowrap'>
				</td>
				<td nowrap='nowrap'>
					<? $task_actual = CReport::getTaskReport($project_id, 2); ?>
				</td>
				<td nowrap='nowrap' colspan="4">
				</td>
			</tr>
			<tr>
				<td colspan='6'>
				<br>
				</td>
			</tr>
			<tr>
				<td nowrap='nowrap' align="left" style="border-top: outset #d1d1cd 1px">
					<input type="checkbox" name="add_log" <?echo ($_POST['add_log'])?"checked":"";echo ($disable_report[0]['l_report_sdate'])?"":"disabled";?>>
				</td>
				<td nowrap="nowrap" style="border-top: outset #d1d1cd 1px">
					<strong><?php echo $AppUI->_( 'Task Logs' );?></strong>
				</td>
				<td width="100%" style="border-top: outset #d1d1cd 1px">
				&nbsp;
				</td>
				<td nowrap="nowrap" align="left" style="border-top: outset #d1d1cd 1px">
				<?php echo "<a href='./index.php?m=report&a=view&reset=log&project_id=$project_id'>".$AppUI->_('Reset')."</a>";?>&nbsp;&nbsp;&nbsp;
				<?php echo "<a href='./index.php?m=projects&a=view&tab=3&project_id=$project_id'>".$AppUI->_('Modify')."</a>";?>&nbsp;&nbsp;&nbsp;
				</td>
				<td nowrap="nowrap" align="center" style="border-top: outset #d1d1cd 1px">
				<select name="append_order_d" class="text">
					<option value="4" <?echo ($_POST['append_order_d']=="4")? "selected":""?>>4
					<option value="1" <?echo ($_POST['append_order_d']=="1")? "selected":""?>>1
					<option value="2" <?echo ($_POST['append_order_d']=="2")? "selected":""?>>2
					<option value="3" <?echo ($_POST['append_order_d']=="3")? "selected":""?>>3
				</select>
				</td>
				<td nowrap='nowrap' align="center" style="border-top: outset #d1d1cd 1px">
				<input type="checkbox" name="new_page_d" <?echo ($_POST['new_page_d'])?"checked":"";?>>
				</td>
			</tr>
			<tr >
				<td nowrap='nowrap'>
				</td>
				<td nowrap='nowrap'>
					<? $task_log = CReport::getLogReport($project_id); ?>
				</td>
				<td nowrap='nowrap' colspan="4">
				</td>
			</tr>
			<tr>
				<td colspan='6'>
				<br>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?php
$image_path='modules/report/logos/';

if($_POST['delete_image']){
	if(file_exists($image_path.$project_id.'.gif')) unlink($image_path.$project_id.".gif");
	if(file_exists($image_path.$project_id.'.jpg')) unlink($image_path.$project_id.".jpg");
	if(file_exists($image_path.$project_id.'.png')) unlink($image_path.$project_id.".png");	
}

do {
  if (is_uploaded_file($_FILES['image']['tmp_name'])) {
    // Ottengo le informazioni sull'immagine
    list($width, $height, $type, $attr) = getimagesize($_FILES['image']['tmp_name']);
    // Controllo che le dimensioni (in pixel)
    if (($width > 45) || ($height > 45)) {
      $mesg = "<p>Dimensioni non corrette!!</p>";
      break;
    }
    // Controllo che il file sia in uno dei formati GIF, JPG o PNG
    if (($type!=1) && ($type!=2) && ($type!=3)) {
      $mesg = "<p>Formato non corretto!!</p>";
      break;
    }
    switch($type){
		case 1: $ext='.gif';
				$img = imagecreatefromgif($_FILES['image']['tmp_name']);
				if(!imagejpeg($img, 'modules/report/logos/'.$project_id.'.jpg'))
				$mesg = "<p>Errore nel caricamento dell'immagine!!</p>";
		break;
		case 2: $ext='.jpg';
				if (!move_uploaded_file($_FILES['image']['tmp_name'], 'modules/report/logos/'.$project_id.$ext))
      			$mesg = "<p>Errore nel caricamento dell'immagine!!</p>";
		break;
		case 3: $ext='.png';
				$img = imagecreatefrompng($_FILES['image']['tmp_name']);
				if(!imagejpeg($img, 'modules/report/logos/'.$project_id.'.jpg'))
				$mesg = "<p>Errore nel caricamento dell'immagine!!</p>";
		break;
	}
    
	}
} while (false);
echo $mesg;

if(file_exists($image_path.$project_id.'.jpg')) $image_file=$image_path.$project_id.'.jpg';
else $image_file=$image_path.'nologo.gif';


?>	
<tr>

	<td align="right" nowrap="nowrap" style="border-top: outset #d1d1cd 1px">
	
	<table border="0" cellpadding="3" cellspacing="1">
	<tr>
		<td align="left" rowspan="2" nowrap="nowrap">
			<img src="<? echo $image_file;?>">
		</td>
		<td align="left" nowrap="nowrap" colspan="2">
		<input  name="image" type="file" size="18" /><br>
		</td>
		<td align="left" width="100%" nowrap="nowrap">
		</td>
		<td align="left" nowrap="nowrap">
			<input type="radio" name="page" value="P" <?echo ($page=="P")? "checked":""?>> Portrait	
		</td>
		<td align="left" nowrap="nowrap">
		</td>
  		<td nowrap="nowrap">
		  	<input type="hidden" name="do" value="1">
		  	<input type="button" class="button" value="<?php echo $AppUI->_( 'Make PDF' );?>" onclick='check();'>
  		</td>
  	</tr>
  	<tr>
  	<td align="left" nowrap="nowrap">
  			<input type="hidden" name="load_image" value="">
			<input  class="button" name="upload" type="button" value="Load Image" onclick='document.make_pdf_options.load_image.value=1;submit();'/>
		</td>
		<td align="right" nowrap="nowrap">
			<input type="hidden" name="delete_image" value="">
			<input  class="button" name="upload" type="button" value="Delete Image" onclick='document.make_pdf_options.delete_image.value=1;submit();'/>
		</td>
		<td align="left" width="100%" nowrap="nowrap">
		</td>
		<td align="left" nowrap="nowrap">
	  		<input type="radio" name="page" value="L" <?echo ($page=="L")? "checked":""?>> Landscape
		</td>
  		<td align="left" nowrap="nowrap">
		</td>
  		<td align='center' nowrap="nowrap">
		  	<?

if(($_POST['do']==1)&&(!$_POST['load_image'])){	
	include('modules/report/makePDF.php');
	include('modules/tasks/tasks.class.php');
	if($image_file==$image_path.'nologo.gif') $image_file='';
	$pdf = PM_headerPdf($name,$page,$border,$group_name,$image_file);
	$i=0;

	for($k=1;$k<=4;$k++){
	 
	 	if(isset($_POST['add_properties'])&&($_POST['append_order_a']==$k)){
			if($task_properties){
			 	$i++;
			 	if(isset($_POST['new_page_a'])) $pdf->AddPage($page);
				PM_makePropPdf($pdf, $task_properties, $project_id, $page);
				$pdf->Ln(8);
			} else $msg.="No Tasks Properties computed!  -  ";
		} 

		if(isset($_POST['add_planned'])&&($_POST['append_order_b']==$k)){
			if($task_planned!=0){
			 	$i++;
				if(isset($_POST['new_page_b'])) $pdf->AddPage($page);
				PM_makeTaskPdf($pdf, $project_id, $task_planned[5], $task_planned[1], $task_planned[0], $task_planned[4], false, $task_planned[2], $task_planned[3], $task_planned[6],$_POST['page']);
				$pdf->Ln(8);
			} else $msg.="No Planned Tasks Report defined!  -  ";
		} 
		
		if(isset($_POST['add_actual'])&&($_POST['append_order_c']==$k)){
			if($task_actual!=0){
			 	$i++;
			 	if(isset($_POST['new_page_c'])) $pdf->AddPage($page);
				PM_makeTaskPdf($pdf, $project_id, $task_actual[5], $task_actual[1], $task_actual[0], $task_actual[4], true, $task_actual[2], $task_actual[3], $task_actual[6],$_POST['page']);
				$pdf->Ln(8);
			} else $msg.="No Actual Tasks Report defined!  -  ";
		}
		
		if(isset($_POST['add_log'])&&($_POST['append_order_d']==$k)){
		 	if($task_log!=0){
			  $i++;
			  if(isset($_POST['new_page_d'])) $pdf->AddPage($page);
			  PM_makeLogPdf($pdf, $project_id, $task_log[0], $task_log[1], $task_log[2], $task_log[3], $task_log[4]);
			  $pdf->Ln(8);
			}else $msg.="No Tasks Log Report defined!";
		}
}	
	
	$filename = PM_footerPdf($pdf, $name, 0);
	
	if($msg!=null) $AppUI->setMsg($msg,6);
	
	if($i>0){?>
	<a href="<?echo $filename;?>" TARGET="_new"><img src="./modules/report/images/pdf_report.gif" alt="PDF Report" border="0" align="absbottom"></a><?}
}?>
  		</td>
  	</tr>
	</table>
	</td>
</tr>
</table>
</form>

