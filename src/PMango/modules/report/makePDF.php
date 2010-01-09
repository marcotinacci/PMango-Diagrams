<?php
/**
-------------------------------------------------------------------------------------------

 PMango Project

 Title:      pdf report production.

 File:       makePDF.php
 Location:   PMango\modules\report
 Started:    2007.05.08
 Author:     Riccardo Nicolini
 Type:       PHP

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history. 
 - 2007.05.08 Riccardo
   First version, created to product .pdf files.
   
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
global $dPconfig;
define("CELLH",3);
define("SPACE",1);
define("BORD",1);
$orient='L';
$y=40;

switch($dPconfig['currency_symbol']){
	case 'â‚¬': $currency='€';
	break;
	case 'Â£': $currency='£';
	break;
	case 'Â¥': $currency='¥';
	break;
	default: $currency=$dPconfig['currency_symbol'];
}

define("CURRENCY",$currency);

function PM_isNewPage($pdf,$h=0,$orient,$type=0){
	if($h+0.5>($pdf->h-20)-$pdf->GetY()) {
     	if($type==0){
		  	$pdf->SetY($pdf->GetY()-CELLH);
     		$pdf->Cell(0,CELLH,' ','B',0,'C');}
		$pdf->AddPage($orient);
	}	
}

function PM_wordCut($pdf,$string='',$maxlen=0){
	$t_name='';
	$width=0;
	$wordwidth = $pdf->GetStringWidth($string);
	$etc=$pdf->GetStringWidth('...');
	if ($wordwidth > $maxlen)
	{
	    // Word is too long, we cut it
	    for($i=0; $i<strlen($string); $i++)
	    {
	        $wordwidth = $pdf->GetStringWidth(substr($string, $i, 1));
	        if($width + $wordwidth + 4<= $maxlen)
	        {
	            $width += $wordwidth;
	            $output .= substr($string, $i, 1);
	        }
		}
		$output .='...';
	}
	else $output= $string;
	return $output;
}

function PM_getStatus($actual_start_date,$actual_finish_date, $start_date, $finish_date, $tpr, $tview){
	$now = new CDate();	
	$image = '';

	if ((!$actual_finish_date && $now->after( $start_date ))||($now->after($finish_date)&& $tpr < 100)){
				$image='images/icons/!t.png';}
						
	if (($actual_start_date)&&($tview)) {
		if ($now->after( $actual_start_date ) && $tpr < 100) {
	    	$image='images/icons/!r.png';     
	    } 
		if ((!$actual_finish_date && $now->after( $start_date ))||($now->after($finish_date)&& $tpr < 100)){
			$image='images/icons/!t.png';
	    }
	    if ($tpr == 100) {//Done
			$image='images/icons/!v.png';
	    }
	}  
	return $image;
}

function PM_getHeight($task_id, $roles){
	
	if($roles=="P"){
		$sql="SELECT COUNT(DISTINCT users.user_id) FROM users,project_roles,user_tasks
		WHERE user_tasks.user_id=users.user_id AND user_tasks.proles_id=project_roles.proles_id AND user_tasks.task_id=".$task_id;
		$db_names = db_loadResult($sql);
		return $db_names*CELLH;	
		}
	if($roles=="N") return CELLH;
	else{
		$sql="SELECT COUNT(users.user_last_name) FROM users,project_roles,user_tasks
		WHERE user_tasks.user_id=users.user_id AND user_tasks.proles_id=project_roles.proles_id AND user_tasks.task_id=".$task_id;
		$db_roles = db_loadResult($sql);
		return $db_roles*CELLH;
	}	
}

function PM_printRoles($pdf, $roles, $task_id, $w, $y=0){
	
	if($roles=="P"){
		$sql="SELECT DISTINCT users.user_last_name, users.user_first_name FROM users,project_roles,user_tasks
		WHERE user_tasks.user_id=users.user_id AND user_tasks.proles_id=project_roles.proles_id AND user_tasks.task_id=".$task_id;
		$db_names = db_loadList($sql);}
	else{
		$sql="SELECT users.user_last_name, users.user_first_name, project_roles.proles_name FROM users,project_roles,user_tasks
		WHERE user_tasks.user_id=users.user_id AND user_tasks.proles_id=project_roles.proles_id AND user_tasks.task_id=".$task_id;
		$db_roles = db_loadList($sql);
	}
	
	if(count( $db_roles )>0){
	 	if($roles=="A"){
			for ( $i = 0; $i < count( $db_roles ); $i++) {
			 	$string.=PM_wordCut($pdf,$db_roles[$i][0].": ".$db_roles[$i][2],$w[6]-2)."\n";
				}	
			$pdf->MultiCell($w[6],CELLH,$string,'LR','C');
			$pdf->SetXY($w[0]+$w[1]+$w[2]+$w[3]+10+$w[7],$y);
		
			$data=$y+(count( $db_roles )*CELLH);
			return $data;
		}
	
		if($roles=="N"){
			$pdf->Cell($w[3],CELLH,"persons: ". (count( $db_roles )),'LR',0,'C');
			return $y+CELLH; 
		}

	 	if($roles=="R"){
			for ( $i = 0; $i < count( $db_roles ); $i++) {
		 	
		 		$string.=PM_wordCut($pdf,$db_roles[$i][2],$w[3]-2)."\n";	
			}	
			$pdf->MultiCell($w[3],CELLH,$string,'LR','C');
			$pdf->SetXY($w[0]+$w[1]+$w[2]+$w[3]+10+$w[7],$y);
		
			$data=$y+(count( $db_roles )*CELLH);
			return $data;
		}
	}

	if(count( $db_names )>0){
	
		if($roles=="P"){
			for ( $i = 0; $i < count( $db_names ); $i++) {
		 		$string.=PM_wordCut($pdf,$db_names[$i][0]." ".$db_names[$i][1],$w[3]-2)."\n";
			}
			$pdf->MultiCell($w[3],CELLH,$string,'LR','C');
			$pdf->SetXY($w[0]+$w[1]+$w[2]+$w[3]+10+$w[7],$y);

		}

		$data1=$y+(count( $db_names )*CELLH);
		return $data1;	
	}
	
	if(count( $db_names )==0){
		$pdf->Cell($w[3],CELLH,"persons: ". (count( $db_roles )),'LR',0,'C');
		return $y+CELLH; 
	}
			
}

function PM_TempY($p=0){
 
 	global $y;
	if($p!=0) $y=$p;
	else return $y;	
}


function PM_headerPdf($project_name, $page='P', $border=1, $group='', $image_file=''){

	global $brd,$orient;
	$brd=$border;
	$orient=$page;
	
	//Libreria FPDF
	include('lib/fpdf/fpdf.php');
	
	class PM_FPDF extends FPDF
	{
		var $p_name;
		var $g_name;
		var $report_type;
		var $roles;
		var $tview;
		var $r_page;
		var $currency;
		var $w_array;
		
		
		
		function Header()
		{	
		 	global $page;
		    $this->SetFont('Arial','I',8);
		    $this->SetTextColor(0,0,0);
		    $this->Cell(20,3,$this->g_name,0,0,'L');
		    $this->Cell(0,3,$this->p_name,0,1,'R');
		    $this->Ln(3);
		    if($this->report_type==2||$this->report_type==3){
				$this->SetFont('Arial','B',9);
				if($this->tview) $this->Cell($this->w_array[7],4," ",1,0,'C');
				$this->Cell($this->w_array[0],4,"%",1,0,'C');
				$this->Cell($this->w_array[1],4,"WBS",1,0, 'C');
					
				if($this->roles=="A"){
				 	$this->Cell($this->w_array[2]-($this->w_array[6]-$this->w_array[3]),4,"Task Name",1,0,'C');
					$this->Cell($this->w_array[6],4,"People",1,0,'C');}
				else {
					$this->Cell($this->w_array[2],4,"Task Name",1,0,'C');
				 	$this->Cell($this->w_array[3],4,"Peoples",1,0,'C');}
				 		
				if(!$this->tview){	
					$this->Cell($this->w_array[4],4,"Start Date",1,0,'C');
					$this->Cell($this->w_array[4],4,"End Date",1,0,'C');
					$this->Cell($this->w_array[8],4,"Effort",1,0,'C');
					$this->Cell($this->w_array[5],4,"Budget",1,1,'C');}
				else{
				 	$this->Cell($this->w_array[4],4,"First Log",BORD,0,'C');
					$this->Image('modules/report/images/delta.png',$this->GetX()+1,$this->GetY()+0.9,2);
					$this->Cell($this->w_array[9],4,"  T",BORD,0,'C');
					$this->Cell($this->w_array[4],4,"Last Log",BORD,0,'C');
					$this->Image('modules/report/images/delta.png',$this->GetX()+1,$this->GetY()+0.9,2);
					$this->Cell($this->w_array[10],4,"  T",BORD,0,'C');
					$this->Cell($this->w_array[8],4,"Eff.",BORD,0,'C');
					$this->Image('modules/report/images/delta.png',$this->GetX()+1,$this->GetY()+0.9,2);
					$this->Cell($this->w_array[11],4,"  E",BORD,0,'C');
					$this->Cell($this->w_array[5],4,"Cost",BORD,0,'C');
					$this->Image('modules/report/images/delta.png',$this->GetX()+1,$this->GetY()+0.9,2);
					$this->Cell($this->w_array[12],4,"  C",BORD,1,'C');		
				}	
				$this->SetLineWidth(0.3);
				$this->SetY($this->GetY()-4);
				$this->Cell(0,4," ",1,1);
				$this->SetLineWidth(0.05);
			}
			if($this->report_type==4){
				$this->SetFont('Arial','B',9);
				$this->Cell($this->w_array[0],4,"%",'LRTB',0,'C');
				$this->Cell($this->w_array[1],4,"Dates",'LRTB',0,'C');
				$this->Cell($this->w_array[7],4,'WBS','LRTB',0,'C');
				$this->Cell($this->w_array[2],4,"Task Log Name",'LRTB',0,'C');
				$this->Cell($this->w_array[3],4,"Worker",'LRTB',0,'C');
				$this->Cell($this->w_array[4],4,"Role",'LRTB',0,'C');
				$this->Cell($this->w_array[6],4,"Effort",'LRTB',0,'C');
				$this->Cell($this->w_array[5],4,"Cost",'LRTB',0,'C');
				$this->MultiCell(0,4,"Notes",'LRTB','C');
				$this->SetFont('Arial','',8);
			
				$this->SetLineWidth(0.3);
				$this->SetY($this->GetY()-4);
				$this->Cell(0,4," ",1,1);
				$this->SetLineWidth(0.05);
			}
		}
		
		function Logo($title, $logo='')
		{		
		    $this->SetFont('Arial','B',16);
		    //Title
		    $this->Cell(0,10,$title,'LRTB',1,'C');
		    //Logo
			if($logo) $this->Image($logo,11,17,8);
		    //Line break
		    $this->Ln(10);	    
		}
		
		function VMulticell($width,$cell_height,$row_number=1,$content,$border='1',$new_line=1,$align='C'){
	
			$first_border=$border;
			if(eregi("B", $border)) $first_border=str_replace("B", "", $border);
		
			if(eregi("L", $border)) $in_border.='L';
			if(eregi("R", $border)) $in_border.='R';
			if($border=='0') $in_border='0';
			
			$last_border=$border;
			if(eregi("T", $border)) $last_border=str_replace("T", "", $border);
			if($border=='1') {
			 	$first_border='LRT';
				$in_border='LR';
				$last_border='LRB';
			}
		
			if($row_number>1){	
			$this->Cell($width,$cell_height,$content,$first_border,0,$align);
			$this->SetXY($this->GetX()-$width,$this->GetY()+ $cell_height);
		
				for ( $i = 0; $i <($row_number-2); $i++) {
					$this->Cell($width,$cell_height,'',$in_border,0,$align);
				    $this->SetXY($this->GetX()-$width,$this->GetY()+ $cell_height);
			    }
			    $this->Cell($width,$cell_height,'',$last_border,0,$align);
			    $this->SetXY($this->GetX()-$width,$this->GetY()+ $cell_height);
		    
			if($new_line==0){
				$this->SetXY($this->GetX()+$width,$this->GetY()-($row_number*$cell_height));
			}else {$this->SetY($this->GetY()-$cell_height);
					$this->Ln();	
			}
			
			}
			else if($row_number<=1) $this->Cell($width,$cell_height,$content,$border,$new_line,$align);
		}
		
		//Page footer
		function Footer()
		{
		    //Position at 1.5 cm from bottom
		    $this->SetY(-10);
		    //Arial italic 8
		    $this->SetFont('Arial','I',8);
		    $this->SetTextColor(0,0,0);
		    $this->Cell(20,3,date("d/m/Y"),0,0,'L');
		    //Page number
		    $this->Cell(0,3,'Page '.$this->PageNo().'/{nb}',0,0,'R');
		    PM_TempY(10);
		}
	} 
	$pdf=new PM_FPDF($page);
	$pdf->p_name=$project_name;
	$pdf->g_name=$group;
	$pdf->r_page=$page;
	$pdf->currency=CURRENCY;
	$pdf->AliasNbPages();
	$pdf->SetLineWidth(0.05);
	$pdf->AddPage();
	$pdf->Logo($project_name." Report", $image_file);
	$pdf->SetFont('Arial','',8);
	PM_TempY($pdf->GetY()+4);	

	return $pdf;
}

function PM_footerPdf($pdf, $project_name, $p=0){

	switch($p){
	 	case 0: $filename=$project_name.".pdf";
	 	break;
		case 1: $filename=$project_name."- Planned.pdf";
		break;
		case 2: $filename=$project_name."- Actual.pdf";
		break;
		case 3: $filename=$project_name."- Log.pdf";
		break;
		case 4: $filename=$project_name."- Properties.pdf";
		break;
	}
	$pdf->Output("./modules/report/pdf/".$filename,'F');
	return "./modules/report/pdf/".$filename;	
}





function PM_makePropPdf($pdf, $properties, $project_id, $page='P'){
	global $brd, $AppUI;
	$top='LRT';
	$lr='LR';
	$bottom='LRB';
	
	$objPr = new CProject();
	
	$q  = new DBQuery;
	$q->addTable('projects');
	$q->addQuery("group_name,
		CONCAT_WS(' ',user_first_name,user_last_name) user_name,
		projects.*");
	$q->addJoin('groups', 'g', 'group_id = project_group');
	$q->addJoin('users', 'u', 'user_id = project_creator');
	$q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project');
	$q->addWhere('project_id = '.$project_id);
	$q->addGroup('project_id');
	$sql = $q->prepare();
	$q->clear();
	db_loadObject( $sql, $obj );
	
	$df = $AppUI->getPref('SHDATEFORMAT');
	
	// create Date objects from the datetime fields
	$start_date = intval( $obj->project_start_date ) ? new CDate( $obj->project_start_date ) : null;
	$finish_date = intval( $obj->project_finish_date ) ? new CDate( $obj->project_finish_date ) : null;
	
	$task_start_date = $objPr->getStartDateFromTask($project_id);
	$task_start_date['task_start_date'] = intval( $task_start_date['task_start_date'] ) ? new CDate( $task_start_date['task_start_date'] ) : "-";
	$task_finish_date = $objPr->getFinishDateFromTask($project_id);
	$task_finish_date['task_finish_date'] = intval( $task_finish_date['task_finish_date'] ) ? new CDate( $task_finish_date['task_finish_date'] ) : "-";
	
	$actual_start_date = $objPr->getActualStartDate($project_id);
	$actual_start_date['task_log_start_date'] = intval( $actual_start_date['task_log_start_date'] ) ? new CDate( $actual_start_date['task_log_start_date'] ) : "-";
	$actual_finish_date = $objPr->getActualFinishDate($project_id);
	$actual_finish_date['task_log_finish_date'] = intval( $actual_finish_date['task_log_finish_date'] ) ? new CDate( $actual_finish_date['task_log_finish_date'] ) : "-";
	
	$today = intval( $obj->project_today ) ? new CDate( $obj->project_today ) : null;
	
	switch($obj->project_status){
		case 0: $status='Not Defined';
		break;
		case 1:	$status='In Planning';
		break;
		case 2:	$status='In Progress';
		break;
		case 3:	$status='Complete';
		break;
	}
	switch($obj->project_type){
		case 0: $type='Unknown';
		break;
		case 1:	$type='Administrative';
		break;
		case 2:	$type='Operative';
		break;
	}
	switch($obj->project_priority){
		case -1: $priority='low';
		break;
		case 0:	$priority='normal';
		break;
		case 1:	$priority='high';
		break;
	}
	
	
	if($page=='P') $w=array(95,17,18,30);
	else $w=array(138.5,29.25,29.25,40);
	
	PM_isNewPage($pdf,44,$page,1);
	
	$pdf->SetFont('Arial','B',10);
	if(($obj->project_active<1)&&($obj->project_current!='0')) $is_archived=' (Archived)';
	$pdf->Cell(0,4,'Project Properties Report for '.$obj->project_name.$is_archived,0,1,'L');
	$pdf->Cell(0,4,'',0,1,'L');
	
	$pdf->SetFont('Arial','B',9);
	$y2=$pdf->GetY();
	//BASE INFORMATION
	$pdf->Cell($w[0],4,'Base Information',1,1,'C');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell($w[1],4,'Group :','L',0,'L');
	$pdf->Cell($w[3],4,PM_wordCut($pdf,htmlspecialchars( $obj->group_name, ENT_QUOTES),$w[3]),'R',0,'R');
	$pdf->Cell($w[2],4,'Effort :','L',0,'L');
	$pdf->Cell($w[3],4,@$obj->project_effort.' ph','R',1,'R');
	
	if($start_date) $date=$start_date->format( $df );
	else $date='-';
	$pdf->Cell($w[1],4,'Start Date :','L',0,'L');
	$pdf->Cell($w[3],4,$date,'R',0,'R');
	$pdf->Cell($w[2],4,'Target Budget :','L',0,'L');
	$pdf->Cell($w[3],4,@$obj->project_target_budget." ".CURRENCY,'R',1,'R');
	
	if($finish_date) $date=$finish_date->format( $df );
	else $date='-';
	$pdf->Cell($w[1],4,'Finish Date :','LB',0,'L');
	$pdf->Cell($w[3],4,$date,'RB',0,'R');
	$pdf->Cell($w[2],4,'Hard Budget :','LB',0,'L');
	$pdf->Cell($w[3],4,@$obj->project_hard_budget." ".CURRENCY,'RB',1,'R');
	
	//COMPUTED INFORMATION
	$pdf->SetFont('Arial','B',9);
	if(!is_null($today)) $date=" ".$today->format( $df );
	else $date=' -';
	$pdf->Cell($w[0],4,'Computed Information at'.$date,1,1,'C');
	$pdf->SetFont('Arial','',8);
	
	$pdf->Cell($w[1],4,'Start Date from Tasks :','L',0,'L');
	if($task_start_date['task_start_date']!="-") $date=$task_start_date['task_start_date']->format( $df );
	else $date='-';
	$pdf->Cell($w[3],4,$date,'R',0,'R');
	$pdf->Cell($w[2],4,'First Log :','L',0,'L');
	if($actual_start_date['task_log_start_date']!="-") $date=$actual_start_date['task_log_start_date']->format( $df );
	else $date='-';
	$pdf->Cell($w[3],4,$date,'R',1,'R');
	
	$pdf->Cell($w[1],4,'Finish Date from Tasks :','L',0,'L');
	if($task_finish_date['task_finish_date']!="-") $date=$task_finish_date['task_finish_date']->format( $df );
	else $date='-';
	$pdf->Cell($w[3],4,$date,'R',0,'R');
	$pdf->Cell($w[2],4,'Last Log :','L',0,'L');
	if($actual_finish_date['task_log_finish_date']!="-") $date=$actual_finish_date['task_log_finish_date']->format( $df );
	else $date='-';
	$pdf->Cell($w[3],4,$date,'R',1,'R');
	
	$pdf->Cell($w[1],4,'Effort from Tasks :','L',0,'L');
	$pdf->Cell($w[3],4,$objPr->getEffortFromTask($project_id)." ph",'R',0,'R');
	$pdf->Cell($w[2],4,'Actual Effort :','L',0,'L');
	$ae=$objPr->getActualEffort($project_id);
	$pdf->Cell($w[3],4,$ae." ph",'R',1,'R');
	
	$pdf->Cell($w[1],4,'Budget from Tasks :','L',0,'L');
	$pdf->Cell($w[3],4,$objPr->getBudgetFromTask($project_id)." ".CURRENCY,'R',0,'R');
	$pdf->Cell($w[2],4,'Actual Cost :','L',0,'L');
	$ac=$objPr->getActualCost($project_id);
	$pdf->Cell($w[3],4,$ac." ".CURRENCY,'R',1,'R');
	
	$pdf->Cell($w[1],4,'Progress :','L',0,'L');
	$pr=$objPr->getProgress($project_id,@$obj->project_effort);
	$pdf->Cell($w[3],4,$pr."%",'R',0,'R');
	$pdf->Cell($w[2],4,'Effort Performance Index :','L',0,'L');
	$pdf->Cell($w[3],4,$objPr->getEffortPerformanceIndex($project_id,$ae,@$obj->project_effort,$pr),'R',1,'R');
	
	$pdf->Cell($w[1],4,'Time Performance Index :','LB',0,'L');
	$pdf->Cell($w[3],4,$objPr->getTimePerformanceIndex($project_id,null,$start_date,$finish_date,$actual_finish_date['task_log_finish_date'],$pr),'RB',0,'R');
	$pdf->Cell($w[2],4,'Cost Performance Index :','LB',0,'L');
	$pdf->Cell($w[3],4,$objPr->getCostPerformanceIndex($project_id,$ac,$obj->project_target_budget,$pr),'RB',1,'R');
	$pdf->SetXY($w[0]+10,$pdf->GetY()-44);
	
	//DETAILS
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($w[0],4,'Details ',1,1,'C');
	$pdf->SetFont('Arial','',8);
	$pdf->SetX($w[0]+10);
	$pdf->Cell($w[3],4,'Status :','L',0,'L');
	$pdf->Cell($w[1],4,$status,'R',0,'R');
	$pdf->Cell($w[3],4,'Short Name :','L',0,'L');
	$pdf->Cell($w[2],4,PM_wordCut($pdf,htmlspecialchars( @$obj->project_short_name, ENT_QUOTES),$w[2]),'R',1,'R');
	$pdf->SetX($w[0]+10);
	$pdf->Cell($w[3],4,'Type :','L',0,'L');
	$pdf->Cell($w[1],4,$type,'R',0,'R');
	$pdf->Cell($w[3],4,'Active :','L',0,'L');
	if($obj->project_active) $active='Yes';
	else $active='No';
	$pdf->Cell($w[2],4,$active,'R',1,'R');
	$pdf->SetX($w[0]+10);
	$pdf->Cell($w[3],4,'Priority :','L',0,'L');
	$pdf->Cell($w[1],4,$priority,'R',0,'R');
	$pdf->Cell($w[3],4,'Project Creator :','L',0,'L');
	$pdf->Cell($w[2],4,PM_wordCut($pdf,$obj->user_name,$w[2]),'R',1,'R');
	$pdf->SetX($w[0]+10);
	$pdf->Cell(10,4,'URL :','LT',0,'L');
	$pdf->Cell($w[0]-10,4,PM_wordCut(@$pdf,@$obj->project_url,$w[0]-10),'RT',1,'R');
	$pdf->SetX($w[0]+10);
	$y0=$pdf->GetY();
	$pdf->Cell(20,4,'Description :','L',0,'L');
	$pdf->MultiCell($w[0]-20,4,$obj->project_description,'RB','R');
	$y1=$pdf->GetY();
	$pdf->SetX($w[0]+10);
	//ASSIGNED TO PROJECT
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($w[0],4,'Assigned to project',1,1,'C');
	$pdf->SetFont('Arial','',8);
	$pdf->SetX($w[0]+10);
	$q->clear();
	$q->addTable('user_projects','up');
	$q->addQuery('CONCAT_WS(", ",u.user_last_name,u.user_first_name) as nm, u.user_email as um, pr.proles_name as pn');
	$q->addJoin('users','u','u.user_id=up.user_id');
	$q->addJoin('project_roles','pr','pr.proles_id = up.proles_id');
	$q->addWhere('up.proles_id > 0 && up.project_id = '.$project_id);
	$ar_ur = $q->loadList();
	
	if (!is_null($ar_ur) && !empty($ar_ur)){
		foreach ($ar_ur as $ur)
			$proles.=$ur['nm']." (".$ur['pn'].")\n";
	}
	$pdf->MultiCell($w[0],4,$proles,'LRTB','C');
	
	if(count($ar_ur)>4){
		$dif=(count($ar_ur)-4)+(($y1-$y0)/4);
		$pdf->SetY($pdf->GetY()-$dif*4);
		for($i=0;$i<$dif;$i++){
			$space.="\n";
		}
		
		$pdf->MultiCell($w[0],4,$space,'LRB','C');	
	}
	if($properties){
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(0,4,'Properties',1,1,'C');
		$pdf->SetFont('Arial','',8);
	
		$properties=explode("<br>",$properties);
		$pdf->Cell(0,4,strip_tags($properties[0]),$top,1,'L');
		for($i=1;$i<count($properties)-1;$i++){
			$pdf->Cell(0,4,strip_tags($properties[$i]),$lr,1,'L');
		}
		$j=count($properties)-1;
		$pdf->Cell(0,4,strip_tags($properties[$j]),$bottom,1,'L');
	}
	
	$y3=$pdf->GetY();
	$pdf->SetLineWidth(0.3);
	$pdf->SetY($y2);
	$pdf->Cell(0,$y3-$y2," ",1,1);
	$pdf->SetLineWidth(0.05);
} 

function PM_makeLogPdf($pdf, $project_id, $user_id, $hide_inactive, $hide_complete, $start_date, $end_date){

	global $AppUI, $brd, $orient;
	$df = $AppUI->getPref('SHDATEFORMAT');
		
	$q  = new DBQuery;
	$q->addQuery('task_log.*, t.task_id, CONCAT_WS(" ",user_first_name,user_last_name) as user_username, pr.proles_name, pr.proles_hour_cost');
	$q->addTable('task_log');
	$q->addJoin('users', 'u', 'u.user_id = task_log_creator');
	$q->addJoin('project_roles', 'pr', 'pr.proles_id = task_log_proles_id');
	$q->addJoin('tasks', 't', 'task_log_task = t.task_id');
	$q->addWhere("task_project = $project_id ");
	if ($user_id>0) 
		$q->addWhere("task_log_creator=$user_id");
	if ($hide_inactive) 
		$q->addWhere("task_status>=0");
	if ($hide_complete) 
		$q->addWhere("task_log_progress < 100");
	if ($user_id>-2)	
	$q->addOrder('task_log_creation_date');
	else $q->addOrder('task_log_creator');
	$logs = $q->loadList();
	
	$q  = new DBQuery;
	$q->addQuery('projects.project_name');
	$q->addTable('projects');
	$q->addWhere("project_id = $project_id ");
	$name = $q->loadList();
	
	$s = '';
	$hrs = 0;
	$crs = 0;
	$j=0;
	$pdf->report_type=4;
	
	if($orient=='L') $w=array(6,22,65,20,25,10,10,10);
	else $w=array(6,22,40,20,25,10,10,10);
	
	if($hide_complete) $is_complete=', Incomplete tasks only';
	if($hide_inactive) $is_inactive=', Hide Inactive';
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,4,'Task Logs Report for '.$name[0]['project_name'],0,1,'L');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(0,4,'Filters: from '.$start_date->format( $df ).' to '.$end_date->format( $df ).$is_complete.$is_inactive,0,1,'L');
	
	foreach ($logs as $row) {
	$wbs=$pdf->GetStringWidth(CTask::getWBS($row['task_id']))+1;
	if($wbs>$max) $max=$wbs;
	$cost=$pdf->GetStringWidth($row["proles_hour_cost"]*$row["task_log_hours"])+1;
	if($cost>$maxcost) $maxcost=$cost;
	$effort=$pdf->GetStringWidth($row["task_log_hours"])+1;
	if($effort>$maxeffort) $maxeffort=$effort;
	}
	if($max>$w[7]) $w[7]=$max+1;
	if($maxcost>$w[5]) $w[5]=$maxcost+1;
	if($maxeffort>$w[6]) $w[6]=$maxeffort+1;
	
	$pdf->w_array=$w;
	
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($w[0],4,"%",'LRTB',0,'C');
	$pdf->Cell($w[1],4,"Dates",'LRTB',0,'C');
	$pdf->Cell($w[7],4,'WBS','LRTB',0,'C');
	$pdf->Cell($w[2],4,"Task Log Name",'LRTB',0,'C');
	$pdf->Cell($w[3],4,"Worker",'LRTB',0,'C');
	$pdf->Cell($w[4],4,"Role",'LRTB',0,'C');
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($w[6],4,"Effort",'LRTB',0,'C');
	$pdf->Cell($w[5],4,"Cost",'LRTB',0,'C');
	$pdf->SetFont('Arial','B',9);
	$pdf->MultiCell(0,4,"Notes",'LRTB','C');
	$pdf->SetFont('Arial','',8);
	
	$pdf->SetLineWidth(0.3);
	$pdf->SetY($pdf->GetY()-4);
	$pdf->Cell(0,4," ",1,1);
	$pdf->SetLineWidth(0.05);
	$max=0;
	
	foreach ($logs as $row) { 
	 	
	 	$string="";
	 	
		$task_log_date = intval( $row['task_log_creation_date'] ) ? new CDate( $row['task_log_creation_date'] ) : null;
		$task_log_edit_date = intval( $row['task_log_edit_date'] ) ? new CDate( $row['task_log_edit_date'] ) : null;
		$task_log_start_date = intval( $row['task_log_start_date'] ) ? new CDate( $row['task_log_start_date'] ) : null;
		$task_log_finish_date = intval( $row['task_log_finish_date'] ) ? new CDate( $row['task_log_finish_date'] ) : null;
	    
	    if(($start_date->format( FMT_TIMESTAMP_DATE )>$task_log_finish_date->format( FMT_TIMESTAMP_DATE ))||$end_date->format( FMT_TIMESTAMP_DATE )<$task_log_start_date->format( FMT_TIMESTAMP_DATE )){
			
		}else{
	
		$date1 = ($task_log_date ? $task_log_date->format( $df ) : '-');
	    $date2 = ($task_log_edit_date ?  $task_log_edit_date->format( $df ) : '-');
	    $date3 = ($task_log_start_date ?  $task_log_start_date->format( $df ) : '-');
	    $date4 = ($task_log_finish_date ? $task_log_finish_date->format( $df ) : '-');
	    $date = $date1." (C)\n";
	    $h=CELLH;
	    if ($date2 != $date1 && $date2 != '-'){
	    	$date .=$date2." (E)\n";
	    	$h=$h+CELLH;}
	    if (($date3 != $date1 && $date3 != '-') || ($date4 != $date1 && $date4 != '-')) {
	    	$date .=$date3." (S)\n";
	    	$date .=$date4." (F)\n";
	    	$h=$h+(CELLH*2);
	    }
	    
	    if($orient=='L') $m=$pdf->WordWrap($row['task_log_description'],$pdf->w - 23 -($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+3*$w[5]))*CELLH;
	    else $m=$pdf->WordWrap($row['task_log_description'],$pdf->w - 23 -($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+3*$w[5]))*CELLH;
	
	    if($m>$h){
			$k=($m-$h)/CELLH;
	    	for ( $i = 0; $i <$k; $i++) {
		 	$date.=" \n";
			}
			$h=$m;
		} 
	    else{
	      if($m<$h){
				$k=($h-$m)/CELLH;
		    	for ( $i = 0; $i <=$k; $i++) {
			 	$row['task_log_description'].="\n";
				}
			}
	    }
	    
	    PM_isNewPage($pdf,$h+2*(SPACE),$orient);
	    
	    $pdf->Cell($w[0],SPACE," ",'LRT',0,'C');
		$pdf->Cell($w[1],SPACE,'','LRT',0,'R');
		$pdf->Cell($w[7],SPACE,'','LRT',0);
		$pdf->Cell($w[2],SPACE,'','LRT',0);
		$pdf->Cell($w[3],SPACE,'','LRT',0,'C');
		$pdf->Cell($w[4],SPACE,'','LRT',0,'C');
		$pdf->Cell($w[6],SPACE,'','LRT',0,'R');
		$pdf->Cell($w[5],SPACE,'','LRT',0,'C');	
		$pdf->Cell(0,SPACE,'','LRT',1,'R');	
	
	    $pdf->VMulticell($w[0],CELLH,$h/CELLH,$row['task_log_progress'],'LR',0,'R');
	    $pdf->MultiCell($w[1],CELLH,$date,'LR','C');
	    $pdf->SetXY($w[0]+$w[1]+10,$pdf->GetY()-$h);
	    $pdf->VMulticell($w[7],CELLH,$h/CELLH,CTask::getWBS($row['task_id']),'LR',0,'L');
	    $pdf->VMulticell($w[2],CELLH,$h/CELLH,PM_wordCut($pdf,utf8_decode($row["task_log_name"]),$w[2]-1),'LR',0,'L');
		$pdf->VMulticell($w[3],CELLH,$h/CELLH,PM_wordCut($pdf,$row["user_username"],$w[3]),'LR',0,'C');
	    $pdf->VMulticell($w[4],CELLH,$h/CELLH,PM_wordCut($pdf,$row["proles_name"],$w[4]),'LR',0,'C');
	    $pdf->VMulticell($w[6],CELLH,$h/CELLH,$row["task_log_hours"],'LR',0,'R');
	    $pdf->SetFont('Arial','',8);
		$cr = $row["proles_hour_cost"]*$row["task_log_hours"];
		$pdf->VMulticell($w[5],CELLH,$h/CELLH,(float)($cr),'LR',0,'R');
		$pdf->SetFont('Arial','',8);
		$pdf->MultiCell(0,CELLH,$row['task_log_description'],'LR','L');
		
		$pdf->Cell($w[0],SPACE,'','LR',0,'C');
		$pdf->Cell($w[1],SPACE,'','LR',0,'R');
		$pdf->Cell($w[7],SPACE,'','LR',0);
		$pdf->Cell($w[2],SPACE,'','LR',0);
		$pdf->Cell($w[3],SPACE,'','LR',0,'C');
		$pdf->Cell($w[4],SPACE,'','LR',0,'C');
		$pdf->Cell($w[6],SPACE,'','LR',0,'R');
		$pdf->Cell($w[5],SPACE,'','LR',0,'C');	
		$pdf->Cell(0,SPACE,'','LR',1,'R');	
		
		$pdf->SetLineWidth(0.3);
		$pdf->SetY($pdf->GetY()-$h-(2*SPACE));
		$pdf->Cell(0,$h+(2*SPACE)," ",'LR',1);
		$pdf->SetLineWidth(0.05);
		
		if($orient=='P') $pdf->SetFont('Arial','',8);
		$h=0;
				
		$hrs += (float)$row["task_log_hours"];
		$crs += (float)$cr;
	}
	
	if(($logs[$j][task_log_creator]!=$logs[$j+1][task_log_creator])&&($user_id==-2))	{
		$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[7],SPACE," ",'LRT',0,'C');
		$pdf->Cell($w[6],SPACE,'','LRT',0,'R');
		$pdf->Cell($w[5],SPACE,'','LRT',0,'C');	
		$pdf->Cell(0,SPACE,'','LRT',1,'R');	 
	 
		$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[7],CELLH,"Totals for ".$row["user_username"].": ",'LR',0,'R'); 
		$pdf->Cell($w[6],CELLH,$hrs,'LR',0,'R');
		$pdf->SetFont('Arial','',8);
		$pdf->Cell($w[5],CELLH,$crs,'LR',0,'R');
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(0,CELLH," ",'LR',1,'C');
		
		$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[7],SPACE," ",'LR',0,'C');
		$pdf->Cell($w[6],SPACE,'','LR',0,'R');
		$pdf->Cell($w[5],SPACE,'','LR',0,'C');	
		$pdf->Cell(0,SPACE,'','LR',1,'R');	 
		
		$pdf->SetLineWidth(0.3);
		$pdf->SetY($pdf->GetY()-CELLH-(2*SPACE));
		$pdf->Cell(0,CELLH+(2*SPACE)," ",'LR',1);
		$pdf->SetLineWidth(0.05);
	
	$hrs=0;
	$crs=0;}
	$j++;
		
	}
	if($user_id!=-2){
		$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[7],SPACE," ",'LRT',0,'C');
		$pdf->Cell($w[6],SPACE,'','LRT',0,'R');
		$pdf->Cell($w[5],SPACE,'','LRT',0,'C');	
		$pdf->Cell(0,SPACE,'','LRT',1,'R');	 
		
		$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[7],CELLH,"Totals: ",'LR',0,'R'); 
		$pdf->Cell($w[6],CELLH,$hrs,'LR',0,'R');
		$pdf->SetFont('Arial','',8);
		$pdf->Cell($w[5],CELLH,$crs,'LR',0,'R');
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(0,CELLH," ",'LR',1,'C');
		
		$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[7],SPACE," ",'LR',0,'C');
		$pdf->Cell($w[6],SPACE,'','LR',0,'R');
		$pdf->Cell($w[5],SPACE,'','LR',0,'C');	
		$pdf->Cell(0,SPACE,'','LR',1,'R');
		
		$pdf->SetLineWidth(0.3);
		$pdf->SetY($pdf->GetY()-CELLH-(2*SPACE));
		$pdf->Cell(0,CELLH+(2*SPACE)," ",'LR',1);
		$pdf->SetLineWidth(0.05);
	}
	$pdf->report_type=0;
	
	$pdf->SetLineWidth(0.3);
	$pdf->SetY($pdf->GetY()-CELLH);
	$pdf->Cell(0,CELLH," ",'B',1);
	$pdf->SetLineWidth(0.05);
}

function PM_makeTaskPdf($pdf, $project_id, $task_level, $tasks_closed, $tasks_opened, $roles, $tview, $start, $end, $showIncomplete){

	global $AppUI, $brd, $orient;

	$q  = new DBQuery;
	$q->addQuery('task_id, task_name, task_parent, task_project, task_start_date, task_finish_date, task_status, task_milestone');
	$q->addTable('tasks');
	$q->addWhere("task_project = $project_id ");
	$q->addOrder('task_wbs_index');
	$tasks = $q->loadList();
	
	$q  = new DBQuery;
	$q->addQuery('projects.project_name');
	$q->addTable('projects');
	$q->addWhere("project_id = $project_id ");
	$name = $q->loadList();

	$q  = new DBQuery;
	$q->addQuery('projects.project_active, projects.project_current');
	$q->addTable('projects');
	$q->addWhere("project_id = $project_id ");
	$archived = $q->loadList();
	
	$df = $AppUI->getPref('SHDATEFORMAT');
	$user_start=$start->format( FMT_TIMESTAMP_DATE );
	$user_end=$end->format( FMT_TIMESTAMP_DATE );
	$date_ok=true;
	$incomplete=false;
	
	$pdf->report_type=2;
	$pdf->roles=$roles;
	$pdf->tview=$tview;
	
	if($orient=='L') {
		if($tview) $w=array(6,10,147,25,16,15,40,6,8,6,6,8,8);
		else $w=array(6,10,174,25,16,15,40,0,15,0,0,0,0);
		$chars=200;
	}
	else {
		if($tview) $w=array(6,10,63,25,16,12,40,6,8,6,6,8,8);
		else $w=array(6,10,93,25,16,12,40,0,12,0,0,0,0);}
	
	$obj = new CTask();	
	foreach ($tasks as $t){
		$wbs=$pdf->GetStringWidth(CTask::getWBS($row['task_id']))+1;
		if($wbs>$max) $max=$wbs;
		if(!$tview){
			$te = $obj->getEffort($t['task_id']);
			$tc = $obj->getBudget($t['task_id']);
		}else{
		 	$childs = $obj->getChild($t['task_id'],$t['task_project']);
			$p_te = $obj->getEffort($t['task_id'], $childs);
			$p_tc = $obj->getBudget($t['task_id']);
			$tc = $obj->getActualCost($t['task_id'], $childs);
			$te = $obj->getActualEffort($t['task_id'], $childs);
		}
		$cost=$pdf->GetStringWidth($tc)+1;
		if($cost>$maxcost) $maxcost=$cost;
		$effort=$pdf->GetStringWidth($te)+1;
		if($effort>$maxeffort) $maxeffort=$effort;
		if($tc-$p_tc>0) $str='+'.($tc-$p_tc);
		else $str=$tc-$p_tc;
		$d_c=$pdf->GetStringWidth($str)+1;
		if($d_c>$maxd_c) $maxd_c=$d_c;
		if($te-$p_te>0) $str='+'.($te-$p_te);
		else $str=$te-$p_te;
		$d_e=$pdf->GetStringWidth($str)+1;
		if($d_e>$maxd_e) $maxd_e=$d_e; 
	}
	if($max>$w[1]){
	 	$diff+=$max+1-$w[1];
		$w[1]=$max+1;
	} 
	if($maxcost>$w[5]){
	 	$diff+=$maxcost+1-$w[5];
		$w[5]=$maxcost+1;	
	} 
	if($maxeffort>$w[8]){
	 	$diff+=$maxeffort+1-$w[8];
		$w[8]=$maxeffort+1;	
	}
	if($tview){
		if($maxd_c>$w[12]){
		 	$diff+=$maxd_c+1-$w[12];
			$w[12]=$maxd_c+1;	
		} 
		if($maxd_e>$w[11]){
		 	$diff+=$maxd_e+1-$w[11];
			$w[11]=$maxd_e+1;	
		}
	}  
	$w[2]=$w[2]-$diff;
	
	$pdf->w_array=$w;
	
	if($tview) $subtitle='Actual Tasks';
	else $subtitle='Planned Tasks';
	
	if(($archived[0]['project_active']<1)&&($archived[0]['project_current']!='0')) $is_archived=' (Archived)';
	if($showIncomplete) $is_complete=', Incomplete tasks only';
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,4,$subtitle.' Report for '.$name[0]['project_name'].$is_archived,0,1,'L');
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(0,4,'Filters: from '.$start->format( $df ).' to '.$end->format( $df ).$is_complete,0,1,'L');
	
	if($tview){
	 	$pdf->Cell(15,5,'Legenda : ',0,0,'L');
		$pdf->Image('images/icons/!r.png',$pdf->GetX(),$pdf->GetY(),4,4);
		$pdf->SetX($pdf->GetX()+4);
		$pdf->Cell(15,5,'Running, ',0,0,'L');
		$pdf->Image('images/icons/!t.png',$pdf->GetX(),$pdf->GetY(),4,4);
		$pdf->SetX($pdf->GetX()+4);
		$pdf->Cell(10,5,'Late, ',0,0,'L');
		$pdf->Image('images/icons/!v.png',$pdf->GetX(),$pdf->GetY(),4,4);
		$pdf->SetX($pdf->GetX()+4);
		$pdf->Cell(10,5,'Done, ',0,0,'L');
		$pdf->Image('images/icons/!b.png',$pdf->GetX(),$pdf->GetY(),4,4);
		$pdf->SetX($pdf->GetX()+4);
		$pdf->Cell(0,5,'Out of Budget',0,1,'L');
	}
	
	$pdf->SetFont('Arial','B',9);
	if($tview) $pdf->Cell($w[7],4," ",BORD,0,'C');
	$pdf->Cell($w[0],4,"%",BORD,0,'C');
	$pdf->Cell($w[1],4,"WBS",BORD,0, 'C');
		
	if($roles=="A"){
	 	if($orient=='P') $chars=40;
		$pdf->Cell($w[2]-($w[6]-$w[3]),4,"Task Name",BORD,0,'C');
		$pdf->Cell($w[6],4,"People",BORD,0,'C');}
	else {
	 	if($orient=='P') $chars=30;
	 	$pdf->Cell($w[2],4,"Task Name",BORD,0,'C');
	 	$pdf->Cell($w[3],4,"People",BORD,0,'C');}
	 		
	if(!$tview){
		$pdf->Cell($w[4],4,"Start Date",BORD,0,'C');
		$pdf->Cell($w[4],4,"End Date",BORD,0,'C');
		$pdf->Cell($w[8],4,"Eff.",BORD,0,'C');
		$pdf->Cell($w[5],4,"Budget",BORD,1,'C');}
	else{
		$pdf->Cell($w[4],4,"First Log",BORD,0,'C');
		$pdf->Image('modules/report/images/delta.png',$pdf->GetX()+($w[9]/2)-2,$pdf->GetY()+0.9,2);
		$pdf->Cell($w[9],4,"  T",BORD,0,'C');
		$pdf->Cell($w[4],4,"Last Log",BORD,0,'C');
		$pdf->Image('modules/report/images/delta.png',$pdf->GetX()+($w[10]/2)-2,$pdf->GetY()+0.9,2);
		$pdf->Cell($w[10],4,"  T",BORD,0,'C');
		$pdf->Cell($w[8],4,"Eff.",BORD,0,'C');
		$pdf->Image('modules/report/images/delta.png',$pdf->GetX()+($w[11]/2)-2,$pdf->GetY()+0.9,2);
		$pdf->Cell($w[11],4,"  E",BORD,0,'C');
		$pdf->Cell($w[5],4,"Cost",BORD,0,'C');
		$pdf->Image('modules/report/images/delta.png',$pdf->GetX()+($w[12]/2)-2,$pdf->GetY()+0.9,2);
		$pdf->Cell($w[12],4,"  C",BORD,1,'C');
			
	}
	$pdf->SetLineWidth(0.3);
	$pdf->SetY($pdf->GetY()-4);
	$pdf->Cell(0,4," ",1,1);
	$pdf->SetLineWidth(0.05);
	$pdf->SetFont('Arial','',8);	
	
	$array=PM_sortTask( $project_id, $task_level, $tasks_opened, $tasks_closed);
	
	foreach ($array as $t){
		
		$level_space="";
		if($t['task_level']>1){
			for($i=1;$i<=$t['task_level'];$i++){
			$level_space.="  ";
			}
		} 
		
		$task_height=PM_getHeight($t["task_id"],$roles);
		
		if(!$tview){
			$te = $obj->getEffort($t['task_id']);
			$tpr = intval( $obj->getProgress($t['task_id'],$te));
			$tc = $obj->getBudget($t['task_id']);
			
			if ($showIncomplete && ($tpr >= 100)) $incomplete=true;
			
			$start_date = intval( $t["task_start_date"] ) ? new CDate( $t["task_start_date"] ) : null;
			$finish_date = intval( $t["task_finish_date"] ) ? new CDate( $t["task_finish_date"] ) : null;
			if ($user_start>$finish_date->format( FMT_TIMESTAMP_DATE )||$user_end<$start_date->format( FMT_TIMESTAMP_DATE ))
				$date_ok=false;
	
			if($start_date) $start_date=$start_date->format( $df );
			else $start_date='-';
			if($finish_date) $finish_date=$finish_date->format( $df );
			else $finish_date='-';
		}
		else{
			$childs = $obj->getChild($t['task_id'],$t['task_project']); 
		
			$start_date = $obj->getActualStartDate($t['task_id'], $childs);
			$start_date = intval( $start_date['task_log_start_date'] ) ? new CDate( $start_date['task_log_start_date'] ) : null;
			$finish_date = $obj->getActualFinishDate($t['task_id'], $childs);
			$finish_date = intval( $finish_date['task_log_finish_date'] ) ? new CDate( $finish_date['task_log_finish_date'] ) : null;
			if(($start_date)&&($finish_date)){
				if ($user_start>$finish_date->format( FMT_TIMESTAMP_DATE )||$user_end<$start_date->format( FMT_TIMESTAMP_DATE ))
					$date_ok=false;			
			}
			
			$p_te = $obj->getEffort($t['task_id'], $childs);
			$p_tc = $tc = $obj->getBudget($t['task_id']);
			$tc = $obj->getActualCost($t['task_id'], $childs);
			$tpr = intval( $obj->getProgress($t['task_id'],$p_te));
			$te = $obj->getActualEffort($t['task_id'], $childs);
			
			$s = intval( $t["task_start_date"] ) ? new CDate( $t["task_start_date"] ) : null;
			$f = intval( $t["task_finish_date"] ) ? new CDate( $t["task_finish_date"] ) : null;
			$image=PM_getStatus( $start_date, $finish_date, $s, $f, $tpr,$tview);
	
			if ($showIncomplete && ($tpr >= 100)) $incomplete=true;
			
			$delta_e=$te-$p_te;
			if($delta_e==0) $delta_e='';
			if($delta_e>0) $delta_e='+'.$delta_e;
			$delta_c=$tc-$p_tc;
			if($delta_c==0) $delta_c='';
			if($delta_c>0) $delta_c='+'.$delta_c;
			
			$p_start_date = intval( $t["task_start_date"] ) ? new CDate( $t["task_start_date"] ) : null;
			$p_finish_date = intval( $t["task_finish_date"] ) ? new CDate( $t["task_finish_date"] ) : null;
			
			if($start_date){
			$confr1=gregoriantojd($start_date->month,$start_date->day,$start_date->year);
			$confr2=gregoriantojd($p_start_date->month,$p_start_date->day,$p_start_date->year);
			$delta_s=$confr1-$confr2;
			if($delta_s==0) $delta_s='';
			if($delta_s>0) $delta_s='+'.$delta_s;	
			}else $delta_s='';
			
			if($finish_date){
			$confr3=gregoriantojd($finish_date->month,$finish_date->day,$finish_date->year);
			$confr4=gregoriantojd($p_finish_date->month,$p_finish_date->day,$p_finish_date->year);
			$delta_f=$confr3-$confr4;
			if($delta_f==0) $delta_f='';
			if($delta_f>0) $delta_f='+'.$delta_f;	
			}else $delta_f='';
			
			if($start_date) $start_date=$start_date->format( $df );
			else $start_date='-';
			if($finish_date) $finish_date=$finish_date->format( $df );
			else $finish_date='-';
			
			if (($obj->getActualEffort($t['task_id'], $childs) > $obj->getEffort($t['task_id'], $childs))||($obj->getActualCost($t['task_id'], $childs) > $obj->getBudget($t['task_id']))) {
			$image='images/icons/!b.png';	
			}
		}
			if($date_ok && !$incomplete){	
	/*	if (($t["task_parent"] == $t["task_id"] || $t["task_status"] != 0)&&($date_ok)&&(!$incomplete)) {	
			if ((CTask::getTaskLevel($t["task_id"])<$task_level)&&(!in_array($t["task_id"], $tasks_closed)))
					$is_opened = true;
				else
			    	$is_opened = in_array($t["task_id"], $tasks_opened);*/
			    
				PM_isNewPage($pdf,$task_height+2*(SPACE),$orient);	
				
	//			if($t['task_milestone']) $t['task_name']="     ".$t['task_name'];
				if($tview)	$pdf->Cell($w[7],SPACE," ",'LRT',0,'C');
				$pdf->Cell($w[0],SPACE,'','LRT',0,'R');
				$pdf->Cell($w[1],SPACE,'','LRT',0);
			   	if($roles=="A") {
				    $pdf->Cell($w[2]-($w[6]-$w[3]),SPACE,'','LRT',0);
				    $pdf->Cell($w[6],SPACE,'','LRT',0,'C');}
			   	else{
				    $pdf->Cell($w[2],SPACE,'','LRT',0);
					$pdf->Cell($w[3],SPACE,'','LRT',0,'C');}	
				$pdf->Cell($w[4],SPACE,'','LRT',0,'C');
				if($tview) $pdf->Cell($w[9],SPACE,'','LRT',0,'R');
				$pdf->Cell($w[4],SPACE,'','LRT',0,'C');	
				if($tview) $pdf->Cell($w[10],SPACE,'','LRT',0,'R');	
				$pdf->Cell($w[8],SPACE,'','LRT',0,'R');
				if($tview) {
				 	$pdf->Cell($w[11],SPACE,'','LRT',0,'R');	
					$pdf->Cell($w[5],SPACE,'','LRT',0,'R');}
				else $pdf->Cell($w[5],SPACE,'','LRT',1,'R');
				if($tview) $pdf->Cell($w[12],SPACE,'','LRT',1,'R');	
				
				if($tview){
					$pdf->Cell($w[7],$task_height," ",'LR',0,'C');
					$y_image=$pdf->GetY()+($task_height/2-2);
					if($image) $pdf->Image($image,$pdf->GetX()-5,$y_image,4,4);
					$pdf->SetX(10);
					$pdf->Cell($w[7],$task_height," ",'LR',0,'C');
				}
				$pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
				
				$pdf->VMulticell($w[0],CELLH,$task_height/CELLH,$tpr,'LR',0,'R'); 
				$pdf->VMulticell($w[1],CELLH,$task_height/CELLH,CTask::getWBS($t["task_id"]),'LR',0,'L');
				if(!CTask::isLeafSt($t["task_id"])) $pdf->SetFont('Arial','B',8);
				if($t['task_milestone']) $pdf->SetFont('Arial','I',8);	
			   	if($roles=="A") $pdf->VMulticell($w[2]-($w[6]-$w[3]),CELLH,$task_height/CELLH,PM_wordCut($pdf,$level_space.utf8_decode($t['task_name']),$w[2]-($w[6]-$w[3])),'LR',0,'L');
			   	else $pdf->VMulticell($w[2],CELLH,$task_height/CELLH,PM_wordCut($pdf,$level_space.utf8_decode($t['task_name']),$w[2]),'LR',0,'L');
	
	/*			if($t['task_milestone']){
							$pdf->Image('images/icons/milestone.png',$pdf->GetX()-$w[2]+1,$pdf->GetY()+($task_height/2-2),3,3);
						}*/
			   	
			   	$pdf->SetFont('Arial','',8);	
				$height=PM_printRoles($pdf, $roles, $t["task_id"], $w, $pdf->GetY());
				PM_TempY($height);
	
				$pdf->VMulticell($w[4],CELLH,$task_height/CELLH,$start_date,'LR',0,'C'); 
				if($tview) $pdf->VMulticell($w[9],CELLH,$task_height/CELLH,$delta_s,'LR',0,'R');
				$pdf->VMulticell($w[4],CELLH,$task_height/CELLH,$finish_date,'LR',0,'C');
				if($tview) $pdf->VMulticell($w[10],CELLH,$task_height/CELLH,$delta_f,'LR',0,'R');
				$pdf->VMulticell($w[8],CELLH,$task_height/CELLH,$te,'LR',0,'R');
				if($tview) $pdf->VMulticell($w[11],CELLH,$task_height/CELLH,$delta_e,'LR',0,'R');
				$pdf->VMulticell($w[5],CELLH,$task_height/CELLH,$tc,'LR',0,'R');
				if($tview) $pdf->VMulticell($w[12],CELLH,$task_height/CELLH,$delta_c,'LR',0,'R');
				$pdf->SetXY(10, $pdf->GetY()+$task_height);
				
				if($tview)	$pdf->Cell($w[7],SPACE," ",'LR',0,'C');
				$pdf->Cell($w[0],SPACE,'','LR',0,'R');
				$pdf->Cell($w[1],SPACE,'','LR',0);
			   	if($roles=="A") {
				    $pdf->Cell($w[2]-($w[6]-$w[3]),SPACE,'','LR',0);
				    $pdf->Cell($w[6],SPACE,'','LR',0,'C');}
			   	else{
				    $pdf->Cell($w[2],SPACE,'','LR',0);
					$pdf->Cell($w[3],SPACE,'','LR',0,'C');}
				$pdf->Cell($w[4],SPACE,'','LR',0,'C');
				if($tview) $pdf->Cell($w[9],SPACE,'','LR',0,'R');
				$pdf->Cell($w[4],SPACE,'','LR',0,'C');	
				if($tview) $pdf->Cell($w[10],SPACE,'','LR',0,'R');	
				$pdf->Cell($w[8],SPACE,'','LR',0,'R');			
				if($tview) {
				 	$pdf->Cell($w[11],SPACE,'','LR',0,'R');	
					$pdf->Cell($w[5],SPACE,'','LR',0,'R');}
				else $pdf->Cell($w[5],SPACE,'','LR',1,'R');
				if($tview) $pdf->Cell($w[12],SPACE,'','LR',1,'R');		
				
				$pdf->SetLineWidth(0.3);
				$pdf->SetY($pdf->GetY()-$task_height-(2*SPACE));
				$pdf->Cell(0,$task_height+(2*SPACE)," ",'LR',1);
				$pdf->SetLineWidth(0.05);	
		}
	$date_ok=true;
	$incomplete=false;		   	
		
	}
	$pdf->SetLineWidth(0.3);
	$pdf->SetY($pdf->GetY()-4);
	$pdf->Cell(0,4," ",'B',1);
	$pdf->SetLineWidth(0.05);
	
	$pdf->report_type=0;

}

function PM_sortTask( $project_id, $task_level, $tasks_opened, $tasks_closed){
 	
	$q  = new DBQuery;
	$q->addQuery('task_id, task_name, task_parent, task_project, task_start_date, task_finish_date, task_status, task_milestone');
	$q->addTable('tasks');
	$q->addWhere("task_project = $project_id ");
	$q->addOrder('task_wbs_index');
	$tasks = $q->loadList();
	
	$a_task=array();
 	$obj = new CTask();
 	
	foreach($tasks as $t) {
		if (($t["task_parent"] == $t["task_id"] || $t["task_status"] != 0)) {	
			if ((CTask::getTaskLevel($t["task_id"])<$task_level)&&(!in_array($t["task_id"], $tasks_closed)))
				$is_opened = true;
			else
		    		$is_opened = in_array($t["task_id"], $tasks_opened);
		    
			$t['task_level']=1;		
		    $a_task[]=$t;
		    
		
			if($is_opened){
				$a_task=PM_sortChildTask( $tasks, $t['task_id'] , $task_level, $tasks_opened, $tasks_closed, $a_task, 1);}
		}
	}

return $a_task;
}

function PM_sortChildTask($tasks, $parent, $task_level, $tasks_opened, $tasks_closed, $a_task, $level){
 	
 	$obj = new CTask();
 	
	for($i=0;$i<count($tasks);$i++) {
	 	

		if (($tasks[$i]["task_parent"] == $parent && $tasks[$i]["task_parent"] != $tasks[$i]["task_id"])) {
			if ((CTask::getTaskLevel($tasks[$i]["task_id"])<$task_level)&&(!in_array($tasks[$i]["task_id"], $tasks_closed)))
				$is_opened = true;
			else
		    		$is_opened = in_array($tasks[$i]["task_id"], $tasks_opened);
		   		
		    $tasks[$i]['task_level']=$level+1;
			$a_task[]=$tasks[$i];
			
			if($is_opened){
				$a_task=PM_sortChildTask( $tasks, $tasks[$i]['task_id'] , $task_level, $tasks_opened, $tasks_closed, $a_task,$level+1);}
		}
	}
return $a_task;
}
?>
