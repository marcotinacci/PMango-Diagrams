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

// sdate and edate passed as unix time stamps
$sdate = dPgetParam( $_POST, UserOptionEnumeration::$CustomStartDateUserOption, 0 );
$edate = dPgetParam( $_POST, UserOptionEnumeration::$CustomEndDateUserOption, 0 );

$projectStatus = dPgetSysVal( 'ProjectStatus' );


// months to scroll
$scroll_date = 1;

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = intval( $sdate ) ? new CDate( $sdate ) : new CDate();
$end_date = intval( $edate ) ? new CDate( $edate ) : new CDate();

// setup the title block
if (!@$min_view) {
	$titleBlock = new CTitleBlock( 'Gantt Chart', 'applet-48.png', $m, "$m.$a" );
	//$titleBlock->addCrumb( "?m=$m", "projects list" );
	$titleBlock->show();
}
?>

<script language="javascript">

function getPageWidth()
{
	//IE
	if(!window.innerWidth)
	{
		return document.body.clientWidth;
	}
	//w3c
	return window.innerWidth;
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.' + calendarField );
	fld_fdate = eval( 'document.editFrm.show_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function scrollPrev() {
	f = document.editFrm;
<?php
	$new_start = $start_date;
	$new_end = $end_date;
	$new_start->addMonths( -$scroll_date );
	$new_end->addMonths( -$scroll_date );
	echo "f.".UserOptionEnumeration::$CustomStartDateUserOption.".value='".$new_start->format( FMT_TIMESTAMP_DATE )."';";
	echo "f.".UserOptionEnumeration::$CustomEndDateUserOption.".value='".$new_end->format( FMT_TIMESTAMP_DATE )."';";
?>
	f.submit();
}

function scrollNext() {
	f = document.editFrm;
<?php
	$new_start = $start_date;
	$new_end = $end_date;
	$new_start->addMonths( $scroll_date+1 );
	$new_end->addMonths( $scroll_date+1 );
	echo "f.".UserOptionEnumeration::$CustomStartDateUserOption.".value='".$new_start->format( FMT_TIMESTAMP_DATE )."';";
	echo "f.".UserOptionEnumeration::$CustomEndDateUserOption.".value='".$new_end->format( FMT_TIMESTAMP_DATE )."';";
?>
	f.submit();
}

function BuildImage(placeHolder)
{
	var divImage = document.getElementById(placeHolder);
	divImage.innerHTML = "<img src='<?php echo "./modules/projects/lib/chartGenerator/TestGantt.php?project_id=".$_REQUEST['project_id']."&".UserOptionEnumeration::$FitInWindowWidthUserOption."="; ?>"+getPageWidth()+"'>";
}

</script>


<?php
//$uoc = new UserOptionsChoice();
$uoc = UserOptionsChoice::GetInstance();
$uoc->setFromArray($_POST);
$_SESSION['uoc'] = serialize($uoc);
?>

<table width="100%" border="0" cellpadding="4"
	cellspacing="0">
	<tr>
		<td>	
		<table width="100%" align="left" border="0" cellpadding="4" cellspacing="0">
			<tr>
			<form name="editFrm" method="post"
				action="?<?php echo "m=$m&a=$a&project_id=$project_id";?>"><input type="hidden"
				name="display_option" value="<?php echo $display_option;?>" />
				<td valign="top" align="left" nowrap="nowrap">
                	<b>Show:</b>&nbsp;&nbsp;
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                	<input type="checkbox" value='4' name="<?php echo UserOptionEnumeration::$TaskNameUserOption; ?>" <?php echo $uoc->showTaskNameUserOption()?"checked":""; ?>> <?php echo "TaskName"; ?><br>
                	<input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$ResourcesUserOption; ?>" <?php echo $uoc->showResourcesUserOption()?"checked":""; ?>> <?php echo "Resources"; ?><br>
                	<input type="checkbox" value='8' name="<?php echo UserOptionEnumeration::$CriticalPathUserOption; ?>" <?php echo $uoc->showCriticalPathUserOption()?"checked":""; ?>> <?php echo "CriticalPath"; ?>
                </td>
                <td>&nbsp;&nbsp;</td>
                <td valign="top" align="left" nowrap="nowrap">
                	<b>View:</b>&nbsp;&nbsp;
                </td>
                <td valign="top" align="left" nowrap="nowrap">
                	<?php 
                		$v = $uoc->getTimeRangeUserOption(); 
                		$check = ""; 
                		if($v==TimeRange::$WholeProjectRangeUserOption || !isset($v)) 
                		$check="checked";
                	?>
                	<input type="radio" name="<?php echo UserOptionEnumeration::$TimeRangeUserOption; ?>" value="<?php echo TimeRange::$WholeProjectRangeUserOption;?>" <?php echo $check;?>> Whole project
					<input type="radio" name="<?php echo UserOptionEnumeration::$TimeRangeUserOption; ?>" value="<?php echo TimeRange::$CustomRangeUserOption;?>"<?php echo $v==TimeRange::$CustomRangeUserOption?"checked":""; ?>> StartDate to FinishDate
					<input type="radio" name="<?php echo UserOptionEnumeration::$TimeRangeUserOption; ?>" value="<?php echo TimeRange::$FromStartToNowRangeUserOption;?>"<?php echo $v==TimeRange::$FromStartToNowRangeUserOption?"checked":""; ?>> StartDate to Now
					<input type="radio" name="<?php echo UserOptionEnumeration::$TimeRangeUserOption; ?>" value="<?php echo TimeRange::$FromNowToEndRangeUserOption;?>"<?php echo $v==TimeRange::$FromNowToEndRangeUserOption?"checked":""; ?>> Now to FinishDate
				<br><br>
				<?php 
				$new_start->addMonths( -$scroll_date );
				$new_end->addMonths( -$scroll_date );
				?> 
				<a href="javascript:scrollPrev()">
				<img src="./images/prev.gif" width="16" height="16"
					alt="<?php echo $AppUI->_( 'previous' );?>" border="0"> 
				</a>

				<?php echo $AppUI->_( 'From' );?>:
				<input type="hidden" name="<?php echo UserOptionEnumeration::$CustomStartDateUserOption; ?>"
					value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
				<input type="text" class="text" name="show_<?php echo UserOptionEnumeration::$CustomStartDateUserOption; ?>"
					value="<?php echo $start_date->format( $df );?>" size="12"
					disabled="disabled" /> <a href="javascript:popCalendar('<?php echo UserOptionEnumeration::$CustomStartDateUserOption; ?>')"><img
					src="./images/calendar.gif" width="24" height="12" alt=""
					border="0"></a>

				<?php echo $AppUI->_( 'To' );?>:
				<input type="hidden" name="<?php echo UserOptionEnumeration::$CustomEndDateUserOption; ?>"
					value="<?php echo $end_date->format( FMT_TIMESTAMP_DATE );?>" /> <input
					type="text" class="text" name="show_<?php echo UserOptionEnumeration::$CustomEndDateUserOption; ?>"
					value="<?php echo $end_date->format( $df );?>" size="12"
					disabled="disabled" /> <a href="javascript:popCalendar('<?php echo UserOptionEnumeration::$CustomEndDateUserOption; ?>')"><img
					src="./images/calendar.gif" width="24" height="12" alt=""
					border="0"></a>
				<a href="javascript:scrollNext()"> <img src="./images/next.gif"
					width="16" height="16" alt="<?php echo $AppUI->_( 'next' );?>"
					border="0"> </a>
				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Grain: 
					<?php
						$v = $uoc->getTimeGrainUserOption();
					?>
					<select name="<?php echo UserOptionEnumeration::$TimeGrainUserOption;?>">
						<option value="<?php echo TimeGrainEnum::$HourlyGrainUserOption; ?>" <?php echo $v==TimeGrainEnum::$HourlyGrainUserOption?"selected=\"selected\"":"";?>>Hourly</option>
						<option value="<?php echo TimeGrainEnum::$DailyGrainUserOption; ?>" <?php echo $v==TimeGrainEnum::$DailyGrainUserOption?"selected=\"selected\"":"";?>>Daily</option>
						<option value="<?php echo TimeGrainEnum::$WeaklyGrainUserOption; ?>" <?php echo $v==TimeGrainEnum::$WeaklyGrainUserOption?"selected=\"selected\"":"";?>>Weakly</option>
						<option value="<?php echo TimeGrainEnum::$MonthlyGrainUserOption; ?>" <?php echo $v==TimeGrainEnum::$MonthlyGrainUserOption?"selected=\"selected\"":"";?>>Monthly</option>
						<option value="<?php echo TimeGrainEnum::$AnnuallyGrainUserOption; ?>" <?php echo $v==TimeGrainEnum::$AnnuallyGrainUserOption?"selected=\"selected\"":"";?>>Annually</option>
					</select>
					<input type="hidden" name="<?php echo  UserOptionEnumeration::$TodayDateUserOption;?>" value="<?php echo date("Ymd"); ?>";/>;
				</td>
				<td width="100%"></td>
				<td align="right" valign="bottom">
				<input type="button" class="button"
					value="<?php echo $AppUI->_( 'submit' );?>"
					onclick='if (document.editFrm.<?php echo UserOptionEnumeration::$CustomEndDateUserOption; ?>.value < document.editFrm.<?php echo UserOptionEnumeration::$CustomStartDateUserOption; ?>.value) alert("Start date must before end date"); else submit();'>
				</td>
				</form>
			</tr>

		</table>

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
		<?php
		/*
		include('modules/report/makePDF.php');
	
						$task_level=$explodeTasks;
						$q  = new DBQuery;
						$q->addQuery('projects.project_name');
						$q->addTable('projects');
						$q->addWhere("project_id = $project_id ");
						$name = $q->loadList();
						$pdf = PM_headerPdf($name[0]['project_name']);
						//PM_makeWbsPdf($pdf,$basedir."modules/projects/lib/chartGenerator/TestGantt.php?project_id=".$_REQUEST['project_id']."");
						PM_makeWbsPdf($pdf,$basedir."/modules/projects/lib/chartGenerator/WBSTree.gif");
						//PM_makeWbsPdf($pdf,"http://localhost:8080/Eclipse Project/PMango/application/PMango/modules/projects/lib/chartGenerator/WBSTree.gif");
						$filename=PM_footerPdf($pdf, $name[0]['project_name'], 5);
		*/
		?>
		</td>
	</tr>
</table>
<script language="javascript">
	BuildImage('imagePlaceHolder');
</script>
<?php ini_restore('memory_limit');?>