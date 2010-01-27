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

GLOBAL  $group_id, $min_view, $m, $a;

ini_set('memory_limit', $dPconfig['reset_memory_limit']);
$min_view = defVal( @$min_view, false);
$project_id = defVal( @$_GET['project_id'], 0);

require_once dirname(__FILE__)."/lib/useroptionschoice/UserOptionEnumeration.php"; 
require_once dirname(__FILE__)."/lib/useroptionschoice/UserOptionsChoice.php";

//$uoc = new UserOptionsChoice();
$uoc = UserOptionsChoice::GetInstance(ChartTypesEnum::$TaskNetwork);
$uoc->saveOnSession();

$produceReport = dPgetParam( $_POST, 'addreport', '' );
if($produceReport==1)
{
	$textUoc = $uoc->saveToString();
	$sql="UPDATE
			reports
		  SET
		  	tasknet_user_options='$textUoc'
		  WHERE 
		  	reports.project_id=".$project_id." 
		  AND 
		  	reports.user_id=".$AppUI->user_id;
	$db_roles = db_loadList($sql);
}

function ordinalize( $number )
{
    if ( is_numeric( $number ) && 0 <> $number )
    {
        if ( in_array( $number % 100, range( 11, 13 ) ) )
        {
            return $number . 'th';
        }
        switch ( $number % 10 )
        {
            case 1:  return $number . 'st';
            case 2:  return $number . 'nd';
            case 3:  return $number . 'rd';
            default: return $number . 'th';
        }
    }
    return $number;
}  

function getCriticalPathsComboBox($numberOfPaths,&$uoc)
{
	$selected = 0;
    if($uoc->getSelectedCriticalPathNumberUserOption()!="")
    	$selected = $uoc->getSelectedCriticalPathNumberUserOption();
	$string = "<select name='".UserOptionEnumeration::$SelectedCriticalPathNumberUserOption."'>\n";
	for($i=0;$i<$numberOfPaths;$i++)
	{
		$string .= "<option value='$i'";
		if($i==$selected)
			$string .= " selected";
		$string .= ">".ordinalize($i+1)."</option>\n";
	}
	$string .= "\n</select>";
	return $string;
}
?>

<script language="javascript">

function getPageWidth()
{
	//IE
	if(!window.innerWidth)
	{
		return document.body.clientWidth-40;
	}
	//w3c
	return window.innerWidth-40;
}

function getPageHeight()
{
	//IE
	if(!window.innerWidth)
	{
		return document.body.clientHeight-45;
	}
	//w3c
	return window.innerHeight-45;
}

function BuildImage(placeHolder)
{
	var divImage = document.getElementById(placeHolder);
	divImage.innerHTML = "<img id='generatedImage' src='<?php echo "./modules/projects/lib/chartGenerator/ChartImageGenerator.php?CHART_TYPE=".ChartTypesEnum::$TaskNetwork.($produceReport==1?"&CREATE_REPORT=1":"")."&project_id=".$_REQUEST['project_id']."&".UserOptionEnumeration::$FitInWindowWidthUserOption."="; ?>"+getPageWidth()+"' onLoad=\"adjustWidth();\">";
}

function OpenInNewWindow()
{
	var stile = "top=10, left=10, width="+getPageWidth()+", height="+getPageWidth()+", status=no, menubar=no, toolbar=no, scrollbars=yes";
    window.open("<?php echo "./modules/projects/lib/chartGenerator/ChartImageGenerator.php?CHART_TYPE=".ChartTypesEnum::$TaskNetwork."&project_id=".$_REQUEST['project_id']."&".UserOptionEnumeration::$FitInWindowWidthUserOption."="; ?>"+getPageWidth()+"", "", stile);
}

function adjustWidth()
{
	var img = document.getElementById('generatedImage');
	var width = img.width;
	//alert(width);
	if(width=="0px" || width==null || width=="" || width==0)
	{
		//alert('call me later');
		setTimeout("adjustWidth()", 500);
	}
	else if(width > (getPageWidth()-45))
	{
		img.style.width = "100%";
		//alert('adjusted to '+img.style.width);
	}
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
                	<input type="checkbox" value='7' name="<?php echo UserOptionEnumeration::$AlertMarkUserOption ?>" <?php echo $uoc->showAlertMarkUserOption()?"checked":""; ?>> <?php echo "AlertMarks"; ?><br>
                	<input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$ResourcesUserOption ?>" <?php echo $uoc->showResourcesUserOption()?"checked":""; ?>> <?php echo "Resources"; ?><br>
                	<input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$TimeGapsUserOption ?>" <?php echo $uoc->showTimeGapsUserOption()?"checked":""; ?>> <?php echo "TimeGaps"; ?>
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                    <input type="checkbox" value='1' name="<?php echo UserOptionEnumeration::$PlannedDataUserOption ?>" <?php echo $uoc->showPlannedDataUserOption()?"checked":""; ?>> <?php echo "Planned Data"; ?><br>
                    <input type="checkbox" value='2' name="<?php echo UserOptionEnumeration::$PlannedTimeFrameUserOption ?>" <?php echo $uoc->showPlannedTimeFrameUserOption()?"checked":""; ?>> <?php echo "Planned TimeFrame"; ?><br>    
                	<input type="checkbox" value='5' name="<?php echo UserOptionEnumeration::$ActualDataUserOption ?>" <?php echo $uoc->showActualDataUserOption()?"checked":""; ?>> <?php echo "Actual Data"; ?><br>
                	<input type="checkbox" value='6' name="<?php echo UserOptionEnumeration::$ActualTimeFrameUserOption ?>" <?php echo $uoc->showActualTimeFrameUserOption()?"checked":""; ?>> <?php echo "Actual TimeFrame"; ?><br>
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                <input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$ShowCompleteDiagramDependencies; ?>" <?php echo $uoc->showShowCompleteDiagramDependencies()?"checked":""; ?>> <?php echo "Complete Dependencies"; ?><br>
                <input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$ReplicateArrowUserOption; ?>" <?php echo $uoc->showReplicateArrowUserOption()?"checked":""; ?>> <?php echo "Replicated Arrows"; ?><br>
                <input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$UseDifferentPatternForCrossingLinesUserOption; ?>" <?php echo $uoc->showUseDifferentPatternForCrossingLinesUserOption()?"checked":""; ?>> <?php echo "Use Different Pattern For Crossing Lines"; ?><br>	
                <input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$CriticalPathUserOption; ?>" <?php echo $uoc->showCriticalPathUserOption()?"checked":""; ?>> <?php echo "Critical Paths"; ?>
                (if yes show the: <?php echo getCriticalPathsComboBox(10,$uoc);?>) 	
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
					value="<?php echo $AppUI->_( 'refresh' );?>"
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
					
					$pdf = PM_headerPdf($name[0]['project_name'],'L',1,$group_name);
					PM_makeTaskNetworkPdf($pdf,"pdf_prj$project_id");
					$filename=PM_footerPdf($pdf, $name[0]['project_name'], 8);
					?>
					<a href="<?echo $filename;?>"><img src="./modules/report/images/pdf_report.gif" alt="PDF Report" border="0" align="absbottom"></a><?
				}?>
				
					<input type="hidden" name="make_pdf" value="false" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Make PDF ' );?>" onclick='document.pdf_options.make_pdf.value="true"; document.pdf_options.submit();'>
					<br><br>
					<input type="hidden" name="addreport" value="-1" />
					<input type="button" class="button" value="<?php echo $AppUI->_( 'Add to Report ' );?>" onclick='document.pdf_options.addreport.value="1"; document.pdf_options.submit();'>
					<br><br>
					<input type="button" class="button" value="New Window" onclick='OpenInNewWindow();'>
				</td>
			</tr>
		</table>
				
				<?php 
				
				//require_once dirname(__FILE__) . '/lib/taskdatatree/UnitTests.php';
				 
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