<?php 
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      view Gantt

 File:       viewgantt.php
 Location:   pmango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango Gantt.
 - 2006.07.30 Lorenzo
   First version, unmodified from dotProject 2.0.1.

---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team.

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

GLOBAL  $group_id, $min_view, $m, $a, $AppUI;

ini_set('memory_limit', $dPconfig['reset_memory_limit']);
$min_view = defVal( @$min_view, false);
$project_id = defVal( @$_GET['project_id'], 0);

require_once dirname(__FILE__)."/lib/useroptionschoice/UserOptionEnumeration.php"; 
require_once dirname(__FILE__)."/lib/useroptionschoice/UserOptionsChoice.php";
require_once dirname(__FILE__)."/lib/chartgenerator/ChartTypesEnum.php";

//$uoc = new UserOptionsChoice();
$uoc = UserOptionsChoice::GetInstance(ChartTypesEnum::$WBS);
$uoc->saveOnSession();

$produceReport = dPgetParam( $_POST, 'addreport', '' );
if($produceReport==1)
{
	$textUoc = $uoc->saveToString();
	$sql="UPDATE
			reports
		  SET
		  	wbs_user_options='$textUoc'
		  WHERE 
		  	reports.project_id=".$project_id." 
		  AND 
		  	reports.user_id=".$AppUI->user_id;
	$db_roles = db_loadList($sql);
}

?>

<script language="javascript">

function getPageWidth()
{
	//IE
	if(!window.innerWidth)
	{
		return document.body.clientWidth-45;
	}
	//w3c
	return window.innerWidth-45;
}

function BuildImage(placeHolder)
{
	var divImage = document.getElementById(placeHolder);
	//divImage.innerHTML = "<img style='max-width:"+(getPageWidth()-45)+"px;' src='<?php echo "./modules/projects/lib/chartGenerator/Test.php?project_id=".$_REQUEST['project_id']."&".UserOptionEnumeration::$FitInWindowWidthUserOption."="; ?>"+getPageWidth()+"'>";
	divImage.innerHTML = "<img style='max-width:"+(getPageWidth()-45)+"px;' src='<?php echo "./modules/projects/lib/chartGenerator/ChartImageGenerator.php?CHART_TYPE=".ChartTypesEnum::$WBS.($produceReport==1?"&CREATE_REPORT=1":"")."&project_id=".$_REQUEST['project_id']."&".UserOptionEnumeration::$FitInWindowWidthUserOption."="; ?>"+getPageWidth()+"'>";
}
</script>
<table width="100%" border="0" cellpadding="4" cellspacing="0">
<tr>
    <td>
         <table border="0" cellpadding="4" cellspacing="0" width='100%'>
			<tr>
				<form name="editFrm" method="POST" action="?<?php echo "m=$m&a=$a&project_id=$project_id"; ?>">
                <td valign="top" align="left" nowrap="nowrap">
                	<b>Show:</b>&nbsp;
                </td>
                <td valign="top" align="left" nowrap="nowrap">                          
                    <input type="checkbox" value='4' name="<?php echo UserOptionEnumeration::$TaskNameUserOption ?>" <?php echo $uoc->showTaskNameUserOption()?"checked":""; ?>> <?php echo "TaskNames"; ?><br>
                	<input type="checkbox" value='7' name="<?php echo UserOptionEnumeration::$AlertMarkUserOption ?>" <?php echo $uoc->showAlertMarkUserOption()?"checked":""; ?>> <?php echo "AlertMarks"; ?>
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                    <input type="checkbox" value='1' name="<?php echo UserOptionEnumeration::$PlannedDataUserOption ?>" <?php echo $uoc->showPlannedDataUserOption()?"checked":""; ?>> <?php echo "Planned Data"; ?><br>
                    <input type="checkbox" value='2' name="<?php echo UserOptionEnumeration::$PlannedTimeFrameUserOption ?>" <?php echo $uoc->showPlannedTimeFrameUserOption()?"checked":""; ?>> <?php echo "Planned TimeFrame"; ?>      
                </td>   
                <td valign="top" align="left" nowrap="nowrap">      
                	<input type="checkbox" value='5' name="<?php echo UserOptionEnumeration::$ActualDataUserOption ?>" <?php echo $uoc->showActualDataUserOption()?"checked":""; ?>> <?php echo "Actual Data"; ?><br>
                	<input type="checkbox" value='6' name="<?php echo UserOptionEnumeration::$ActualTimeFrameUserOption ?>" <?php echo $uoc->showActualTimeFrameUserOption()?"checked":""; ?>> <?php echo "Actual TimeFrame"; ?>
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                	<input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$ResourcesUserOption ?>" <?php echo $uoc->showResourcesUserOption()?"checked":""; ?>> <?php echo "Resources"; ?>
                	<input type="hidden" name="<?php echo  UserOptionEnumeration::$TodayDateUserOption;?>" value="<?php echo date("Ymd"); ?>"/>
                </td>
                <td>&nbsp;&nbsp;</td>
                <td valign="top" align="left" nowrap="nowrap">
                	<b>Image options:</b>&nbsp;
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                	<?php
						$v = $uoc->getImageDimensionUserOption();
					?>
                	<select name="<?php echo UserOptionEnumeration::$ImageDimensionsUserOption;?>">
						<option value="<?php echo ImageDimension::$FitInWindowDimUserOption; ?>" <?php echo $v==ImageDimension::$FitInWindowDimUserOption?"selected=\"selected\"":"";?>>Fit window</option>
						<option value="<?php echo ImageDimension::$CustomDimUserOption; ?>" <?php echo $v==ImageDimension::$CustomDimUserOption?"selected=\"selected\"":"";?>>Custom</option>
						<option value="<?php echo ImageDimension::$OptimalDimUserOption; ?>" <?php echo $v==ImageDimension::$OptimalDimUserOption?"selected=\"selected\"":"";?>>Optimal</option>
						<option value="<?php echo ImageDimension::$DefaultDimUserOption; ?>" <?php echo $v==ImageDimension::$DefaultDimUserOption?"selected=\"selected\"":"";?>>Default</option>
					</select>
					<br>
					<?php $wh = $uoc->getCustomDimValues(); ?>
					width: <input size="4" type="text" name="<?php echo UserOptionEnumeration::$CustomWidthUserOption; ?>" value="<?php echo $wh['width'];?>"/> px <br>
					<input type="hidden" name="<?php echo UserOptionEnumeration::$FitInWindowWidthUserOption; ?>" value="0"/>
					<input type="hidden" name="<?php echo UserOptionEnumeration::$FitInWindowHeightUserOption; ?>" value="0"/>
					<?php print $uoc->getRefreshHiddenField();?>
                </td>
                <td width="100%"></td>
                <td valign="bottom" align="right">
                	<input type="button" class="button"
					value="<?php echo $AppUI->_( 'submit' );?>"
					onclick='submit();'>
                </td>		
                </form>  
                <!-- REPORT -->		
				<td align="right" valign='bottom'>
				<form name='pdf_options' method='POST' action='<?php echo $query_string; ?>'>
				<?if ($_POST['make_pdf']=="true")	{
					include('modules/report/makePDF.php');

					$q  = new DBQuery;
					$q->addQuery('projects.project_name');
					$q->addTable('projects');
					$q->addWhere("project_id = $project_id ");
					$name = $q->loadList();
					
					$q  = new DBQuery;
					$q->addTable('groups');
					$q->addTable('projects');
					$q->addQuery('groups.group_name');
					$q->addWhere("projects.project_group = groups.group_id and projects.project_id = '$project_id'");
					$group = $q->loadList();
					
					foreach ($group as $g){
						$group_name=$g['group_name'];
					}
					
					$pdf = PM_headerPdf($name[0]['project_name'],'P',1,$group_name);
					PM_makeWBSPdf($pdf);
					$filename=PM_footerPdf($pdf, $name[0]['project_name'], 1);
					?>
					<a href="<?echo $filename;?>"><img src="./modules/report/images/pdf_report.gif" alt="PDF Report" border="0" align="absbottom"></a><?
				}?>
				
					<input type="hidden" name="make_pdf" value="false" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Make PDF ' );?>" onclick='document.pdf_options.make_pdf.value="true"; document.pdf_options.submit();'>
					<br><br>
					<input type="hidden" name="addreport" value="-1" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Add to Report ' );?>" onclick='document.pdf_options.addreport.value="1"; document.pdf_options.submit();'>
				</td>
			</tr>
		</table> 
				
				<?php 
				
				//uoc debug
				/*
				echo UserOptionEnumeration::$PlannedDataUserOption.":".$uoc->showPlannedDataUserOption()."<br>";
				echo UserOptionEnumeration::$PlannedTimeFrameUserOption .":".$uoc->showPlannedTimeFrameUserOption()."<br>";
				echo UserOptionEnumeration::$TaskNameUserOption.":".$uoc->showTaskNameUserOption()."<br>";
				echo UserOptionEnumeration::$ActualDataUserOption.":".$uoc->showActualDataUserOption()."<br>";
				echo UserOptionEnumeration::$ActualTimeFrameUserOption.":".$uoc->showActualTimeFrameUserOption()."<br>";
				echo UserOptionEnumeration::$AlertMarkUserOption.":".$uoc->showAlertMarkUserOption()."<br>";
				echo UserOptionEnumeration::$ResourcesUserOption.":".$uoc->showResourcesUserOption()."<br>";
				*/
				 
				?>
				
				<!-- Generated Image -->
                <table width="100%" cellspacing="0" cellpadding="0" border="1"
					align="center" class="tbl">
				<tr>
					<td align="center">
						<div id="imagePlaceHolder">
						</div>
					</td>
				</tr>
				</table>
	</td>
</tr>
</table>
<script language="javascript">
	BuildImage('imagePlaceHolder');
</script>
<?php ini_restore('memory_limit');?>