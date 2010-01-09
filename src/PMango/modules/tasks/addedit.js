
/**
---------------------------------------------------------------------------

 PMango Project

 Title:      add and edit tasks.

 File:       addedit.php
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       Javascript

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to modify PMango task.
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

var calendarField = '';
var calWin = null;

function setMilestoneEndDate(checked){
    if(checked){
        document.finish_date.value      = document.start_date.value;
        document.task_finish_date.value = document.task_start_date.value;
    } 
}


function popCalendar(field){
	calendarField = field;
	task_cal = document.getElementById('task_' + field.name);
	idate = task_cal.value;
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=251, height=220, scollbars=false' );
}


function popModel(){
	if (document.editFrm.task_id.value > 0)
		window.open( 'index.php?m=public&a=model&dialog=1&callback=setModel&model='+document.editFrm.model.value+'&deliveryDay='+document.editFrm.deliveryDay.value+'&task_id='+document.editFrm.task_id.value, 'modelwin', 'top=250,left=250,width=451, height=180, scollbars=false' );
	else
		alert('After you have created task, you can insert model informations');
}

function setModel(model,deliveryDay) {
	document.editFrm.model.value = model;
	document.editFrm.deliveryDay.value = deliveryDay;
}
/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = document.getElementById('task_' + calendarField.name);
	calendarField.value = fdate;
	fld_date.value = idate;
	// set finish date automatically with start date if start date is after finish date
	e_date = document.getElementById('task_' + 'finish_date');
	e_fdate = document.getElementById('finish_date');
	s_date = document.getElementById('task_' + 'start_date');
	s_fdate = document.getElementById('start_date');
	
	var s = Date.UTC(s_date.value.substring(0,4),(s_date.value.substring(4,6)-1),s_date.value.substring(6,8), s_date.value.substring(8,10), s_date.value.substring(10,12));
	var e = Date.UTC(e_date.value.substring(0,4),(e_date.value.substring(4,6)-1),e_date.value.substring(6,8), e_date.value.substring(8,10), e_date.value.substring(10,12));

	if( s > e) {
		e_date.value = s_date.value;
		e_fdate.value = s_fdate.value;
	}
	
	if (eff_perc) {
		var hours = calcDuration(document.editFrm,1);
		if (document.resourceFrm.ass_users != null) {
			au = document.resourceFrm.ass_users.length -1;
			len = au;
			for (au; au > -1; au--) {
				wl = Math.round((resourceFrm.ass_mh.options[au].value) * 100/ hours);
				
				opt = new Option( wl + " %", wl);
				resourceFrm.ass_perc.options[au] = opt;
			}
			if (len > -1) 
				alert('The percentage of effort can be modified');
		}
	}
}

function changeWorkLoad(form) {
	//alert("Prima "+form.work_load[0].value+ " " +form.work_load[1].value);
	if (form.work_load[1].checked) {
		form.work_load[1].value = 1;
		form.work_load[0].value = 0;
	}
	if (form.work_load[0].checked) {
		form.work_load[0].value = 1;
		form.work_load[1].value = 0;
	}
	if (form.work_load[1].checked) 
		form.wl_desc.value = "%";
	else
		form.wl_desc.value = "ph";
	//alert("Dopo "+form.work_load[0].value+ " " +form.work_load[1].value);
}

function submitIt(form,form2){//alert(calcDuration(form,1));return false;
	if (form.task_name.value.length < 3) {
		alert( task_name_msg );
		form.task_name.focus();
		return false;
	}
	
	if (!form.task_start_date.value) {
		alert( task_start_msg );
		return false;
	}
	
	if (!form.task_finish_date.value) {
		alert( task_end_msg );
		return false;
	}
	
	cdur = calcDuration(form,24)
	if (cdur==0)
		return false;
	
	// Controllo durata delle ore per persona che non sia superiore alle ore giornaliere
	var maxHours= 24 * Math.round(cdur);
	fl = form2.ass_users.length -1;
	for (fl; fl > -1; fl--) {
		if (form2.ass_mh.options[fl].value == 0 || isNaN(form2.ass_mh.options[fl].value)) {
			alert( 'The assigned effort to '+ form2.ass_users.options[fl].text+' as '+ form2.ass_roles.options[fl].text+' is invalid');
			return false;
		}
		if (eff_perc) 
			if (form2.ass_perc.options[fl].value == 0 || isNaN(form2.ass_perc.options[fl].value)) {
				alert( 'The assigned percentage to '+ form2.ass_users.options[fl].text+' as '+ form2.ass_roles.options[fl].text+' is invalid');
				return false;
			}
	}
	
	fl = form2.ass_users.length -1;
	for (fl; fl > -1; fl--) {
		s = new String(form2.ass_users.options[fl].value);
		s = s.substring(0,s.indexOf(","));
		v = parseInt(form2.ass_mh.options[fl].value);
		gl = fl - 1;
		for (gl; gl > -1; gl--) {
			s2 = new String(form2.ass_users.options[gl].value);
			s2 = s2.substring(0,s2.indexOf(","));
			if (parseInt(s2) == parseInt(s)) {
				v = v + parseInt(form2.ass_mh.options[gl].value);
				if (v > maxHours) {
					alert('The task duration is insufficient for the user: '+form2.ass_users.options[gl].text);
					return false;
				}
			}
		}
	}
	
	if ( form.task_start_date.value.length > 0 ) {
			form.task_start_date.value += form.start_hour.value + form.start_minute.value;
	}
	if ( form.task_finish_date.value.length > 0 ) {
		form.task_finish_date.value += form.end_hour.value + form.end_minute.value;
	}
	
	// I dati di resource sono salvati in subform come quelli anche degli altri
	
	// Check the sub forms
	for (var i = 0; i < subForm.length; i++) {
		if (!subForm[i].check())
			return false;
		// Save the subform, this may involve seeding this form
		// with data
		subForm[i].save();
	}

	form.submit();
}

//Check to see if None has been selected.
function checkForTaskDependencyNone(obj){
	var td = obj.length -1;
	for (td; td > -1; td--) {
		if(obj.options[td].value==task_id){
			clearExceptFor(obj, task_id);
			break;
		}
	}
}

//If None has been selected, remove the existing entries.
function clearExceptFor(obj, id){
	var td = obj.length -1;
	for (td; td > -1; td--) {
		if(obj.options[td].value != id){
			obj.options[td]=null;
		}
	}
}

function addTaskDependency(form) {
	var at = form.all_tasks.length -1;
	var td = form.task_dependencies.length -1;
	/*var dav = form.dep_ass.options[form.dep_ass.selectedIndex].value;
	var dat = form.dep_ass.options[form.dep_ass.selectedIndex].text;*/
	
	var tasks = "x";

	//Check to see if None is currently in the dependencies list, and if so, remove it.

	if( td >= 0 && form.task_dependencies.options[0].value==task_id){
		form.task_dependencies.options[0] = null;
		td = form.task_dependencies.length -1;
	}

	//build array of task dependencies
	for (td; td > -1; td--) {
		tasks = tasks + "," + form.task_dependencies.options[td].value + ","
	}

	//Pull selected resources and add them to list
	for (at; at > -1; at--) {
		if (form.all_tasks.options[at].selected && tasks.indexOf( "," + form.all_tasks.options[at].value + "," ) == -1) {
			t = form.task_dependencies.length;
			opt = new Option( form.all_tasks.options[at].text, form.all_tasks.options[at].value);
/*			opt = new Option( form.all_tasks.options[at].text+"["+dat+"]", form.all_tasks.options[at].value + "-" + dav );*/
			form.task_dependencies.options[t] = opt;
		}
	}
	checkForTaskDependencyNone(form.task_dependencies);
}

function removeTaskDependency(form) {
	td = form.task_dependencies.length -1;

	for (td; td > -1; td--) {
		if (form.task_dependencies.options[td].selected) {
			form.task_dependencies.options[td] = null;
		}
	}
}

function setAMPM( field) {
	ampm_field = document.getElementById(field.name + "_ampm");
	if (ampm_field) {
		if ( field.value > 11 ){
			ampm_field.value = "pm";
		} else {
			ampm_field.value = "am";
		}
	}
}

var hourMSecs = 3600*1000;

/**
* no comment needed
*/
function isInArray(myArray, intValue) {

	for (var i = 0; i < myArray.length; i++) {
		if (myArray[i] == intValue) {
			return true;
		}
	}		
	return false;
}

/**
* @modify_reason calculating duration does not include time information and cal_working_days stored in config.php
*/
function calcDuration(f,typeDur) {

	var int_st_date = new String(f.task_start_date.value + f.start_hour.value + f.start_minute.value);
	var int_en_date = new String(f.task_finish_date.value + f.end_hour.value + f.end_minute.value);
	
	if(int_st_date.substring(0,12) == int_en_date.substring(0,12)) 
		return 0;
		
	var sDate = new Date(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
	var eDate = new Date(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
	
	var s = Date.UTC(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
	var e = Date.UTC(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
	var durn = (e - s) / hourMSecs; //hours absolute diff start and end
	
	//now we should subtract non-working days from durn variable
	var duration = durn  / 24;
	var weekendDays = 0;
		var myDate = new Date(int_st_date.substring(0,4), (int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10));
	for (var i = 0; i < duration; i++) {
		//var myDate = new Date(int_st_date.substring(0,4), (int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10));
		var myDay = myDate.getDate();
		if ( !isInArray(working_days, myDate.getDay()) ) {
			weekendDays++;
		}
		myDate.setDate(myDay + 1);
	}
	//alert('h'+weekendDays);
	//alert(durn);
	//calculating correct durn value
	durn = durn - weekendDays*24;	// total hours minus non-working days (work day hours)

	// check if the last day is a weekendDay
	// if so we subtracted some hours too much before, 
	// we have to fill up the last working day until cal_day_start + daily_working_hours
	if ( !isInArray(working_days, eDate.getDay()) && eDate.getHours() != cal_day_start) {
		durn = durn + Math.max(0, (cal_day_start + daily_working_hours - eDate.getHours()));
	}
	
	//could be 1 or 24 (based on TaskDurationType value)
	var durnType = parseFloat(typeDur);	
	durn /= durnType;
	//alert(durn);
	if (durnType == 1){
		// durn is absolute weekday hours

		// Hours worked on the first day
		var first_day_hours = cal_day_end - sDate.getHours();
		if (first_day_hours > daily_working_hours)
			first_day_hours = daily_working_hours;

		// Hours worked on the last day
		var last_day_hours = eDate.getHours() - cal_day_start;
		if (last_day_hours > daily_working_hours)
			last_day_hours = daily_working_hours;

		// Total partial day hours
		var partial_day_hours = first_day_hours + last_day_hours;

		// Full work days
		var full_work_days = (durn - partial_day_hours) / 24;

		// Total working hours
		durn = Math.floor(full_work_days) * daily_working_hours + partial_day_hours;
		
		// check if the last day is a weekendDay
		// if so we subtracted some hours too much before, 
		// we have to fill up the last working day until cal_day_start + daily_working_hours
		if ( !isInArray(working_days, eDate.getDay()) && eDate.getHours() != cal_day_start) {
			durn = durn + Math.max(0, (cal_day_start + daily_working_hours - eDate.getHours()));
		}

	} else if (durnType == 24 ) {
		//we should talk about working days so task duration equals 41 hrs means 6 (NOT 5) days!!!
		if (durn > Math.round(durn))
			durn++;
		}
	if (durn < 0) durn = 0;
	if ( s > e ) {
		alert( 'Finish date is before start date!');
		return 0;
	}
	else
		return(durn);
}
/**
* Get the end of the previous working day 
*/
function prev_working_day( dateObj ) {
	while ( ! isInArray(working_days, dateObj.getDay()) || dateObj.getHours() < cal_day_start ||
	      (	dateObj.getHours() == cal_day_start && dateObj.getMinutes() == 0 ) ){

		dateObj.setDate(dateObj.getDate()-1);
		dateObj.setHours( cal_day_end );
		dateObj.setMinutes( 0 );
	}

	return dateObj;
}
/**
* Get the start of the next working day 
*/
function next_working_day( dateObj ) {
	while ( ! isInArray(working_days, dateObj.getDay()) || dateObj.getHours() >= cal_day_end ) {
		dateObj.setDate(dateObj.getDate()+1);
		dateObj.setHours( cal_day_start );
		dateObj.setMinutes( 0 );
	}

	return dateObj;
}

function changeRecordType(value){
	// if the record type is changed, then hide everything
	hideAllRows();
	// and how only those fields needed for the current type
	eval("show"+task_types[value]+"();");
}

var subForm = new Array();

function FormDefinition(id, form, check, save) {
	this.id = id;
	this.form = form;
	this.checkHandler = check;
	this.saveHandler = save;
	this.check = fd_check;
	this.save = fd_save;
	this.submit = fd_submit;
	this.seed = fd_seed;
}

function fd_check()
{
	if (this.checkHandler) {
		return this.checkHandler(this.form);
	} else {
		return true;
	}
}

function fd_save()
{
	if (this.saveHandler) {
		var copy_list = this.saveHandler(this.form);
		return copyForm(this.form, document.editFrm, copy_list);
	} else {
		return this.form.submit();
	}
}

function fd_submit()
{
	if (this.saveHandler)
		this.saveHandler(this.form);
	return this.form.submit();
}

function fd_seed()
{
	return copyForm(document.editFrm, this.form);
}

function copyForm(form, to, extras) {
	// Grab all of the elements in the form, and copy them
	// to the main form.  Do not copy hidden fields.
	var h = new HTMLex;
	for (var i = 0; i < form.elements.length; i++) {
		var elem = form.elements[i];
		if (elem.type == 'hidden') {
			// If we have anything in the extras array we check to see if we
			// need to copy it across
			if (!extras)
				continue;
			var found = false;
			for (var j = 0; j < extras.length; j++) {
				if (extras[j] == elem.name) {
				  found = true;
					break;
				}
			}
			if (! found)
				continue;
		}
		// Determine the node type, and determine the current value
		switch (elem.type) {
			case 'textarea':
                to.appendChild(h.addTextArea(elem.name, elem.value));
                break;
            case 'text':
			case 'hidden':
				to.appendChild(h.addHidden(elem.name, elem.value));
				break;
			case 'select-one':
				if (elem.options.length > 0)
					to.appendChild(h.addHidden(elem.name, elem.options[elem.selectedIndex].value));
				break;
			case 'select-multiple':
				var sel = to.appendChild(h.addSelect(elem.name, false, true));
				for (var x = 0; x < elem.options.length; x++) {
					if (elem.options[x].selected) {
						sel.appendChild(h.addOption(elem.options[x].value, '', true));
					}
				}
				break;
			case 'radio':
			case 'checkbox':
				if (elem.checked) {
					to.appendChild(h.addHidden(elem.name, elem.value));
				}
				break;
		}
	}
	return true;
}

function saveDepend(form) {
	var dl = form.task_dependencies.length -1;
        hd = form.hdependencies;
	hd.value = "";
	for (dl; dl > -1; dl--){
		hd.value = "," + hd.value +","+ form.task_dependencies.options[dl].value;
	}
    return new Array('hdependencies');;
}

function checkDetail(form) {
	return true;
}

function saveDetail(form) {
	return null;
}

function checkResource(form) {
	return true;
}

function saveResource(form) {
	var fl = form.ass_users.length -1;
	
	ass_res = form.ass_resources;
	ass_res.value = "|";
	for (fl; fl > -1; fl--){
		if (eff_perc) 
			ass_res.value += form.ass_users.options[fl].value +","+ form.ass_mh.options[fl].value +","+ form.ass_perc.options[fl].value + "|";
		else
			ass_res.value += form.ass_users.options[fl].value +","+ form.ass_mh.options[fl].value +",0|";
	}
	return new Array('ass_resources');
}

function selectPeople(form, block) {
	var fl = form.users.length -1;
	if (block == 1) {
		for (fl; fl > -1; fl--) {
			if (form.users.options[fl].selected) {
				form.roles.options[fl].selected=true;
				if (eff_perc)
					form.dwh.options[fl].selected=true;
				form.meffort.options[fl].selected=true;
			}
			else {
				form.roles.options[fl].selected=false;
				if (eff_perc)
					form.dwh.options[fl].selected=false;
				form.meffort.options[fl].selected=false;
			}
		}
	}
	if (block == 2) {
		for (fl; fl > -1; fl--) {
			if (form.roles.options[fl].selected) {
				form.users.options[fl].selected=true;
				if (eff_perc)
					form.dwh.options[fl].selected=true;
				form.meffort.options[fl].selected=true;
			}
			else {
				form.users.options[fl].selected=false;
				if (eff_perc)
					form.dwh.options[fl].selected=false;
				form.meffort.options[fl].selected=false;
			}
		}
	}
	if (block == 3) {
		for (fl; fl > -1; fl--) {
			if (form.meffort.options[fl].selected) {
				form.users.options[fl].selected=true;
				form.roles.options[fl].selected=true;
				if (eff_perc)
					form.dwh.options[fl].selected=true;
			}
			else {
				form.users.options[fl].selected=false;
				form.roles.options[fl].selected=false;
				if (eff_perc)
					form.dwh.options[fl].selected=false;
			}
		}
	}
	if (eff_perc)
		if (block == 4) {
			for (fl; fl > -1; fl--) {
				if (form.dwh.options[fl].selected) {
					form.users.options[fl].selected=true;
					form.roles.options[fl].selected=true;
					form.meffort.options[fl].selected=true;
				}
				else {
					form.users.options[fl].selected=false;
					form.roles.options[fl].selected=false;
					form.meffort.options[fl].selected=false;
				}
			}
	}
	
	//Pull selected resources and add them to list
	
}

function selectAssigned(form, block) {
	var fl = form.ass_users.length -1;
	if (block == 1) {
		for (fl; fl > -1; fl--) {
			if (form.ass_users.options[fl].selected) {
				form.ass_roles.options[fl].selected=true;
				if (eff_perc)
					form.ass_perc.options[fl].selected=true;
				form.ass_mh.options[fl].selected=true;
			}
			else {
				form.ass_roles.options[fl].selected=false;
				if (eff_perc)
					form.ass_perc.options[fl].selected=false;
				form.ass_mh.options[fl].selected=false;
			}
		}
	}
	if (block == 2) {
		for (fl; fl > -1; fl--) {
			if (form.ass_roles.options[fl].selected) {
				form.ass_users.options[fl].selected=true;
				if (eff_perc)
					form.ass_perc.options[fl].selected=true;
				form.ass_mh.options[fl].selected=true;
			}
			else {
				form.ass_users.options[fl].selected=false;
				if (eff_perc)
					form.ass_perc.options[fl].selected=false;
				form.ass_mh.options[fl].selected=false;
			}
		}
	}
	if (block == 3) {
		for (fl; fl > -1; fl--) {
			if (form.ass_mh.options[fl].selected) {
				form.ass_users.options[fl].selected=true;
				form.ass_roles.options[fl].selected=true;
				if (eff_perc)
					form.ass_perc.options[fl].selected=true;
			}
			else {
				form.ass_users.options[fl].selected=false;
				form.ass_roles.options[fl].selected=false;
				if (eff_perc)
					form.ass_perc.options[fl].selected=false;
			}
		}
	}
	
	if (eff_perc)
		if (block == 4) {
			for (fl; fl > -1; fl--) {
				if (form.ass_perc.options[fl].selected) {
					form.ass_users.options[fl].selected=true;
					form.ass_roles.options[fl].selected=true;
					form.ass_mh.options[fl].selected=true;
				}
				else {
					form.ass_users.options[fl].selected=false;
					form.ass_roles.options[fl].selected=false;
					form.ass_mh.options[fl].selected=false;
				}
			}
	}
	
	//Pull selected resources and add them to list
	
}

function changeResources(form,form2) {
	// task_list => ^[(ui,pi|ef)(ui,pi|ef)]^-tid[(ui,pi|ef)(ui,pi|ef)(ui,pi|ef)(ui,pi|ef)(ui,pi|ef)]tid-tid[(ui,pi|ef)(ui,pi|ef)]...
	// tra ^[ e ]^ ci sono gli utenti del progetto
	// us_hours_list => |ui=dwh|ui=dwh|ui=dwh|ui=dwh|...
	// us_name_list => |ui=un|ui=un|ui=un|ui=un|...
	// proles_list => |pi=pn|pi=pn|pi=pn|...
	// wbs_list => |ti=wbs,wbsi|wbs,wbsi|wbs,wbsi|...
	fl = form2.users.length -1;
	// Si scancellano tutti i valori selezionati
	for (fl; fl > -1; fl--) {
		form2.users.options[fl] = null;
		form2.roles.options[fl] = null;
		form2.meffort.options[fl] = null;
		if (eff_perc)
			form2.dwh.options[fl] = null;
	}	
	var nt = form.task_parent.value;
	
	uhl = new String(form.us_hours_list.value);
	unl = new String(form.us_name_list.value);
	prl = new String(form.proles_list.value);//
	
	// Gestisco le wbs
	wbsl = new String(form.wbs_list.value);//alert(form.wbs_list.value);
	bwbsl = wbsl.indexOf("|"+nt+"=")+2+nt.length;
	form.task_wbs.value = wbsl.slice(bwbsl,wbsl.indexOf(",",bwbsl));
	form.task_wbs_index.value = wbsl.slice(wbsl.indexOf(",",bwbsl)+1,wbsl.indexOf("|",bwbsl));
	if (form.task_wbs.value != "")
		form.wbs.value = form.task_wbs.value+"."+form.task_wbs_index.value;
	else
		form.wbs.value = form.task_wbs_index.value;
	if (form.oldParent.value == nt)
		maxWBSi = currentMaxWBSi;//
	else
		maxWBSi = form.task_wbs_index.value;
			
	tl = new String(form.task_list.value);//alert(nt);alert(tl);alert(form.wbs_list.value);
	bt = new String("-"+nt+"[");
	et = new String("]"+nt+"-");//alert(tl.indexOf(tl.indexOf(bt)+1+nt.length));
	if(tl.indexOf(bt)+1+nt.length == -1 || tl.indexOf(et) == -1) {//alert(wbsl);
		return null;
	}
	newTask = tl.slice(tl.indexOf(bt)+2+nt.length,tl.indexOf(et));
	// ho trovato il task che mi interessa
	
	var i=0;
	
	while ((newTask.indexOf(")") != -1)) {
		user = newTask.slice(newTask.indexOf("(")+1,newTask.indexOf(")"));
		newTask = newTask.slice(newTask.indexOf(")")+1,newTask.length);
		
		ui_v = user.slice(0,user.indexOf(","));
		ri_v = user.slice(user.indexOf(",")+1,user.indexOf("|"));
		ef_v = user.slice(user.indexOf("|")+1,user.length);
		
		bunl = unl.indexOf("|"+ui_v+"=")+2+ui_v.length;
		ui_t = unl.slice(bunl,unl.indexOf("|",bunl));
		
		buhl = uhl.indexOf("|"+ui_v+"=")+2+ui_v.length;
		uh_v = uhl.slice(buhl,uhl.indexOf("|",buhl));
		
		bprl = prl.indexOf("|"+ri_v+"=")+2+ri_v.length;
		ri_t = prl.slice(bprl,prl.indexOf("|",bprl));
		
		opt = new Option(ui_t, ui_v+","+ri_v);
		form2.users.options[i] = opt;
		opt = new Option(ri_t, ui_v+","+ri_v);
		form2.roles.options[i] = opt;
		if (ef_v == "-")
			opt = new Option(ef_v, ef_v);
		else
			opt = new Option(ef_v+" ph", ef_v);
		form2.meffort.options[i] = opt;
		if (eff_perc) {
				opt = new Option(uh_v+" h", uh_v);
				form2.dwh.options[i] = opt;
		}
		i+=1;
	}
	return null;
}

function plusWBS(form) {
	if (form.task_wbs_index.value < maxWBSi) {
		form.task_wbs_index.value++;
		if (form.task_wbs.value != "")
			form.wbs.value = form.task_wbs.value+"."+form.task_wbs_index.value;
		else
			form.wbs.value = form.task_wbs_index.value;
	}
}

function minusWBS(form) {
	if (form.task_wbs_index.value > 1) {
		form.task_wbs_index.value--;
		if (form.task_wbs.value != "")
			form.wbs.value = form.task_wbs.value+"."+form.task_wbs_index.value;
		else
			form.wbs.value = form.task_wbs_index.value;
	}
}

function addUser(form, form2) {
	if (!form2.task_start_date.value) {
		alert( task_start_msg );
		return false;
	}
	if (!form2.task_finish_date.value) {
		alert( task_end_msg );
		return false;
	}
	
	wl = parseFloat(form.wl_value.value);
	if (isNaN(wl) || wl < 0) {
		alert( 'Please, enter a valid number of person hours or percentage');
		return false;
	}
	
	var fl = form.users.length -1;
	var au = form.ass_users.length -1;
	//gets value of percentage assignment of selected resource
	//var perc = form.percentage_assignment.options[form.percentage_assignment.selectedIndex].value;

	var users = "x";

	/*var rolesIndex = form.roles_assignment.options[form.roles_assignment.selectedIndex].value;
	var rolesText = form.roles_assignment.options[form.roles_assignment.selectedIndex].text;*/

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + ";" + form.ass_users.options[au].value + "," + form.ass_roles.options[au].value + ";";
	}// della forma: x;user_id,proles_id;...
	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.users.options[fl].selected && users.indexOf( ";" + form.users.options[fl].value + "," + form.roles.options[fl].value + ";" ) == -1) {
			t = form.ass_users.length;
			if (eff_perc) {
				if (form.work_load[0].checked) {//settata person hours
					wl2 = Math.round((wl * 100)/ calcDuration(form2,1));
					if (isNaN(wl2)) return false;
					opt = new Option( wl + " ph", wl);
					form.ass_mh.options[t] = opt;
					//calcolo la percentuale corrispondente all'impegno in ore-uomo
					opt = new Option( wl2 + " %", wl2);
					form.ass_perc.options[t] = opt;
				} else if (form.work_load[1].checked) {
					wl2 = Math.round(wl * calcDuration(form2,1) / 100);
					if (isNaN(wl2)) return false;
					opt = new Option( wl + " %", wl);
					form.ass_perc.options[t] = opt;
					//calcolo l'impegno in ore uomo corrispondente alla percentuale inserita	
					opt = new Option( wl2 + " ph", wl2);
					form.ass_mh.options[t] = opt;
				}
			} else {
				opt = new Option( wl + " ph", wl);
				form.ass_mh.options[t] = opt;
			}
			
			opt = new Option( form.users.options[fl].text, form.users.options[fl].value);
			form.ass_users.options[t] = opt;
			opt = new Option( form.roles.options[fl].text, form.roles.options[fl].value);
			form.ass_roles.options[t] = opt;
			//LA LETTURA AVVIENE IN SAVERESOURCES 
		}
	}
}

function removeUser(form) {
	var fl = form.ass_users.length -1;
	for (fl; fl > -1; fl--) {
		if (form.ass_users.options[fl].selected) {
			form.ass_users.options[fl] = null;
			form.ass_roles.options[fl] = null;
			form.ass_mh.options[fl] = null;
			if (eff_perc)
				form.ass_perc.options[fl] = null;
		}
	}
}