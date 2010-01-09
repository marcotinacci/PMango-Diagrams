---------------------------------------------------------------------------

 PMango Project

 Title:      Distribution main Readme

 File:       ReadMe.txt
 Location:   pmango/
 Started:    2006.07.18
 Author:     Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 Type:       ReadMe

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2008.02.16 Marco
   V 2.2.1: fixed missed fonts for FPDF library.
 - 2007.10.20 Marco
   V 2.2.0: implemented PDF reports and perfomance tuning of data base.
 - 2007.06.16 Lorenzo
   Modified to guarantee compatibility with php > 5.2.0 and its new 
   session type configuration. There are also introduced files to 
   develop cpm functionality.	
 - 2006.12.31 Giovanni
   Updated web link to Sourceforge. Name changed to PMango.
   Aligned project description to the official one.
   Added release notes and this change log after Lorenzo work.
 - 2006.08.24 Giovanni
   Minor changes to English.
 - 2006.08.03 Lorenzo
   Second version.
 - 2006.07.18 Giovanni & Lorenzo
   First version. 


---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006-2008  Giovanni A. Cignoni, Lorenzo Ballini,
                          Marco Bonacchi, Riccardo Nicolini

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is 
 released under GNU GPL, further information at: http://www.dotproject.net
 Other libraries used by PMango are redistributed under their own licenses.
 See ReadMe in the root folder for details. 

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

 Brief description of the PMango Project

 PMango is a web based L/WAMP application for project planning and control.

 The PMango project was set up to support students carrying out projects 
 in software engineering courses. This particular context defined the 
 foundations of the application, which aims to provide features to:

 - check and validate the well-formedness of a project plan;
 - evaluate the effort distribution against well know reference models;
 - log activities and control actual effort during project development;
 - compare plans at different stages in project life and analyze 
   project performance.

 The original PMango philosophy of "training and supervision" fits well 
 for usage in organizations at their early stages in using project
 management and workflow methods and technologies. 
 PMango is designed to be used for:

 - introducing project management techniques in small organizations;
 - remote consulting and project supervision;
 - educational support and professional training.

 The first PMango release was an additional module for dotProject
 (http://www.dotproject.net). The current release is an independent 
 application that reuses part of the dotProject code. 
 An installation of PMango is running at the Dept. of Computer Science 
 of the University of Pisa, it supports teaching activities and is 
 available for demo. 
 Sources, distributed under GNU GPL, are kept on Sourceforge.

 For more info see the PMango Web Page: http://pmango.sourceforge.net
 or contact Giovanni A. Cignoni (giovanni@di.unipi.it).


--------------------------------------------------------------------------- 

 Installation

 Unpack the distribution archive then open pmango/install/index.php with 
 your web browser and follow the prompts.
 Details of the installation procedure are in the Technical Manual
 that is in the pmango/Docs folder.

 To use the application refer to the User Manual in the pmango/Docs folder.


--------------------------------------------------------------------------- 

 Release notes

 Version: 2.2.1, 2008.02.16

 - fixed missing fonts for FPDF library;

 Version: 2.2.0, 2007.10.20

 - implemented printable PDF project reports;
 - perfomance tuning of data base.

 Version: 2.1.2, 2006.12.31

 - name changed in PMango, new logo;
 - fixed additional slash in modules/public/color_selector.php;
 - fixed bug when setting capabilities for user.

 
--------------------------------------------------------------------------- 

 Libraries:

 The following libraries are used by Mango and redistributed under their 
 own licenses: 
 - Adodb, mango\lib\adodb\license.txt, 
   http://adodb.sourceforge.net;
 - calendar, mango\GNU-GPL.txt, 
   http://students.infoiasi.ro/~mishoo/site/calendar.epl;
 - jpgraph, mango\lib\jpgraph\qpl.txt, 
   http://www.aditus.nu/jpgraph;
 - pear, mango\lib\pear\license.txt, 
   http://www.php.net;
 - phpgacl, mango\lib\phpgacl\copying.txt, 
   http://phpgacl.sourceforge.net.

--------------------------------------------------------------------------- 


