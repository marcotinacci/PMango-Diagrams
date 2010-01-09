/**
---------------------------------------------------------------------------

 PMango Project

 Title:      add and edit project

 File:       addedit.js
 Location:   pmango\modules\projects
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       Javascript

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history.
 - 2006.07.30 Lorenzo
   Second version, modified to manage PMango project.
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

function popModel(){
	alert('This function is under construction');
}

function addUser(form) {
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	//gets value of roles assignment of selected resource
	var rolesIndex = form.roles_assignment.options[form.roles_assignment.selectedIndex].value;
	var rolesText = form.roles_assignment.options[form.roles_assignment.selectedIndex].text;
	var users = "x";
	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.assigned.options[au].value + ","
	}
	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources.options[fl].selected && users.indexOf( "," + form.resources.options[fl].value + "=" + rolesIndex + "," ) == -1) {
			t = form.assigned.length;
			opt = new Option( form.resources.options[fl].text+" ["+rolesText+"]", form.resources.options[fl].value+"="+rolesIndex);
			form.members.value += form.resources.options[fl].value+"="+rolesIndex+";";
			form.assigned.options[t] = opt;	
		}
	}
}

function removeUser(form) {//OK
	fl = form.assigned.length -1;
	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			//remove from hperc_assign
			var selValue = form.assigned.options[fl].value;			
			var re = ".*("+selValue+";).*";//c'è user_id
			var hiddenValue = form.members.value;
			if (hiddenValue) {
				var b = hiddenValue.match(re);
				if (b[1]) {
					hiddenValue = hiddenValue.replace(b[1], '');
				}
				form.members.value = hiddenValue;
				form.assigned.options[fl] = null;
				
			}
		}
	}
}

function changeGroup(form) {
	fl = form.resources.length -1;
	// Si scancellano tutti i valori selezionati
	for (fl; fl > -1; fl--) {
		form.resources.options[fl] = null;
	}	
	au = form.assigned.length -1;
	// Si scancellano tutti i valori selezionati
	for (au; au > -1; au--) {
		form.assigned.options[au] = null;
	}
	
	var ng = form.project_group.value;

	s = new String (form.groups.value);//alert(s);
	bg = new String ("-"+ng+"[");
	eg = new String ("]"+ng+"-");
	newUsers = s.slice(s.indexOf(bg)+2+ng.length,s.indexOf(eg));
	var i=0;
	while (newUsers.indexOf(";") != -1) {
		opt = new Option(newUsers.slice(newUsers.indexOf("=")+1,newUsers.indexOf(";")) ,newUsers.slice(0,newUsers.indexOf("=")));
		form.resources.options[i] = opt;
		newUsers=newUsers.slice(newUsers.indexOf(";")+1,newUsers.length);
		i+=1;
	}
	
	if (form.project_group.value > 0) {
		opt2 = new Option(form.creator_name.value, form.project_creator.value+"="+1);
		form.assigned.options[0] = opt2;
		form.members.value = form.project_creator.value+"="+1+";";
	}
}

