/**
---------------------------------------------------------------------------

 PMango Project

 Title:      task view.

 File:       view.js
 Location:   pmango\modules\tasks
 Started:    2005.09.30
 Author:     Lorenzo Ballini
 Type:       Javascript

 This file is part of the PMango project
 Further information at: http://penelope.di.unipi.it

 Version history. 
 - 2006.07.30 Lorenzo
   Second version, modified to view PMango task.
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

function popEmailContacts() {
	updateEmailContacts();
	var email_others = document.getElementById('email_others');
	window.open(
	  './index.php?m=public&a=contact_selector&dialog=1&call_back=setEmailContacts&selected_contacts_id='
		+ email_others.value, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setEmailContacts(contact_id_string) {
	if (! contact_id_string)
		contact_id_string = "";
	var email_others = document.getElementById('email_others');
	email_others.value = contact_id_string;
}

function updateEmailContacts() {
	var email_others = document.getElementById('email_others');
	var task_emails = document.getElementById('email_task_list');
	var proj_emails = document.getElementById('email_project_list');
	var do_task_emails = document.getElementById('email_task_contacts');
	var do_proj_emails = document.getElementById('email_project_contacts');

	// Build array out of list of contact ids.
	var email_list = email_others.value.split(',');
	if (do_task_emails.checked) {
		var telist = task_emails.value.split(',');
		var full_list = email_list.concat(telist);
		email_list = full_list;
		do_task_emails.checked = false;
	}

	if (do_proj_emails.checked) {
		var prlist = proj_emails.value.split(',');
		var full_proj = email_list.concat(prlist);
		email_list = full_proj;
		do_proj_emails.checked = false;
	}

	// Now do a reduction
	email_list.sort();
	var output_array = new Array();
	var last_elem = -1;
	for (var i = 0; i < email_list.length; i++) {
		if (email_list[i] == last_elem) {
			continue;
		}
		last_elem = email_list[i];
		output_array.push(email_list[i]);
	}
	email_others.value = output_array.join();
}

function emailNumericCompare(a, b) {
	return a - b;
}
