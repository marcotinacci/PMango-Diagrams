<?php
/**---------------------------------------------------------------------------

 PMango Project

 Title:      custom fields parser class

 File:       customfieldsparser.class.php
 Location:   pmango/classes
 Started:    2005.09.30
 Author:     dotProject team
 Type:       class

 This file is part of the PMango project
 Further information at: http://pmango.sourceforge.net

 Version history.
 - 2006.07.18 Lorenzo
   First version, unmodified from dotProject 2.0.1.

---------------------------------------------------------------------------

 PMango - A web application for project planning and control.

 Copyright (C) 2006 Giovanni A. Cignoni, Lorenzo Ballini, Marco Bonacchi
 All rights reserved.

 PMango reuses part of the code of dotProject 2.0.1: dotProject code is
 released under GNU GPL, further information at: http://www.dotproject.net
 Copyright (c) 2003-2005 The dotProject Development Team

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
 Foundation Inc., 51 Franklin St, 5th Floor, Boston, MA 02110-1301 USA

---------------------------------------------------------------------------*/

	class CustomFieldsParser{
		var $fields_array        = array();
		var $custom_record_type;
		var $previous_data       = array();
		var $row_id              = 0;
		var $custom_record_types = array();
		
		var $table_name    = "tasks";
		var $field_name    = "task_custom";
		var $id_field_name = "task_id";
		
		/**
		* @return CustomFieldsParser
		* @param char Field type: TaskCustomFields, CompanyCustomFields
		* @desc Constructor
		*/
		function CustomFieldsParser($custom_record_type, $row_id = 0){
			$this->custom_record_type = $custom_record_type;
			
			$this->_fetchFields();
			$this->_fetchCustomRecordTypes();
			
			switch($this->custom_record_type){
				case "TaskCustomFields":
					$this->table_name    = "tasks";
					$this->field_name    = "task_custom";
					$this->id_field_name = "task_id";
					break;
				case "CompanyCustomFields":
					$this->table_name    = "groups";
					$this->field_name    = "group_custom";
					$this->id_field_name = "group_id";
					break;
				default:
					$AppUI->setMsg("Invalid custom field record type: $custom_record_type");
					break;
			}
			
			$this->row_id = $row_id;
			if($this->row_id != 0){
				$this->_fetchPreviousData();
			}
		}
		
		function _fetchFields(){
			$this->fields_array = dPgetSysVal($this->custom_record_type);
		}
		
		function _fetchCustomRecordTypes(){
			switch($this->custom_record_type){
				case "TaskCustomFields":
					$field_types = "TaskType";
					break;
				case "CompanyCustomFields":
					$field_types = "CompanyType";
					break;
			}
			$this->custom_record_types = dPgetSysVal($field_types);
		}
		
		function _fetchPreviousData(){
			$q = new DBQuery;
			$q->addTable($this->table_name);
			$q->addQuery($this->field_name);
			$q->addWhere("{$this->id_field_name} = {$this->row_id}");
			$previous_data = $q->loadResult();
			
			if($previous_data != ""){
				$previous_data = unserialize($previous_data);
				$previous_data = !is_array($previous_data) ? array() : $previous_data;
			} else {
				$previous_data = array();
			}
			$this->previous_data = $previous_data;
		}
		
		function _getLabelHTML($field_config){
			if($field_config["type"] == "label"){
				$separador = "";
				$colspan   = "colspan='2'";
				$field_config["name"] = "<b>".$field_config["name"]."</b>";
			} else {
				$separador = ":";
				$colspan   = "";
			}
				
			return "<td $colspan>".$field_config["name"]." $separador</td>";
		}
		
		function parseEditField($key){
			$field_config = unserialize($this->fields_array[$key]);
			$parsed   = "<tr id='custom_tr_$key'>";
				
			$parsed .= $this->_getLabelHTML($field_config);
			switch($field_config["type"]){
				case "text":
					$parsed .= "<td align='left'><input type='text' name='custom_$key' class='text' " . $field_config["options"] . " value='" . ( isset($this->previous_data[$key]) ? $this->previous_data[$key] : "") . "' /></td>";
					break;
				case "select":
					$parsed .= "<td align='left'>". arraySelect(explode(",",$field_config["selects"]), "custom_$key", 'size="1" class="text" ' . $field_config["options"] ,( isset($this->previous_data[$key]) ? $this->previous_data[$key] : "")) . "</td>";
					break;
				case "textarea":
					$parsed .=  "<td align='left'><textarea name='custom_$key' class='textarea'" . $field_config["options"] . ">" . ( isset($this->previous_data[$key]) ? $this->previous_data[$key] : "") . "</textarea></td>";
					break;
				case "checkbox":
					$options_array = explode(",",$field_config["selects"]);
					$parsed .= "<td align='left'>";
					foreach ( $options_array as $option ) {
						$checked = "";
						if ( isset($this->previous_data[$key]) && array_key_exists( $option, array_flip($this->previous_data[$key]) ) ) {
							$checked = "checked";
						} 
						$parsed .=  "<input type='checkbox' value='$option' name='custom_" . $key ."[]' class='text' style='border:0' $checked " . $field_config["options"] . "  />$option<br />";
						$checked = "";
					}
					$parsed .= "</td>";
					break;
			}
			$parsed .= "</tr>";
			return $parsed;
		}
		
		function parseViewField($key){
			$field_config = unserialize($this->fields_array[$key]);
			$parsed  = "<tr id='custom_tr_$key'>";
			$parsed .= $this->_getLabelHTML($field_config);
			switch ( $field_config["type"]){
				case "text":
					$parsed .= "<td class='hilite'>" . dPformSafe(( isset($this->previous_data[$key]) ? $this->previous_data[$key] : "")) . "</td>";
					break;
				case "select":
					$optionarray = explode(",",$field_config["selects"]);
					$parsed .= "<td class='hilite' width='300'>". dPformSafe(( isset($this->previous_data[$key]) ? $optionarray[$this->previous_data[$key]] : "")) . "</td>";
					break;
				case "textarea":
					$parsed .=  "<td valign='top' class='hilite'>" . dPformSafe(( isset($this->previous_data[$key]) ? $this->previous_data[$key] : "")) . "</td>";
					break;
				case "checkbox":
					$optionarray = explode(",",$field_config["selects"]);
                    $parsed .= "<td align='left'>";
                    foreach ( $optionarray as $option ) {
                    		$checked = "";
                            if ( isset($this->previous_data[$key]) && array_key_exists( $option, array_flip($this->previous_data[$key]) ) ) {
                                    $checked = "checked";
                            }
                            $parsed .=  "<input type='checkbox' value='$option' name='custom_" . $key ."[]' class='text' locked style='border:0' $checked " . $field_config["options"] . " disabled />$option<br />";
                    }
                    $parsed .= "</td>";
                    break;
			}
			$parsed .= "</tr>";
			return $parsed;
		}
		
		function parseTableForm($edit = false, $record_type = null){
			$parsed = "<table>";
			
			$visible_keys = array();
			if(!is_null($record_type)){
				$visible_keys = $this->_getVisibleKeysForType($record_type);
			}
			
			foreach($this->fields_array as $key => $field){
				$field_config = unserialize($field);
				if($edit){
					$fnc_name = "parseEditField";
				} else {
					$fnc_name = "parseViewField";
				}

				if(in_array($key, $visible_keys)){
					$parsed .= $this->$fnc_name($key);
				} else if (is_null($record_type)){
					$parsed .= $this->$fnc_name($key);
				}
			}
			$parsed .= "</table>";
			return $parsed;
		}
		
		function _getVisibleKeysForType($record_type){
			if(!isset($this->visible_keys)){
				$this->visible_keys = array();
			}
			
			if(isset($this->visible_keys[$record_type])){
				return $this->visible_keys[$record_type];
			} else {
				$this->visible_keys[$record_type] = array();
			}
			
			foreach($this->fields_array as $key => $field){
				$field_config = unserialize($field);
				if($field_config["record_type"] == $record_type || $field_config["record_type"] == ""){
					$this->visible_keys[$record_type][] = $key;
				}
			}
			return $this->visible_keys[$record_type];
		}
		
		function _parseShowFunction($key){
			$parsed = "";
			$record_type = $this->custom_record_types[$key];
			
	    	$record_type = str_replace(" ",  "_", $record_type);
	    	$parsed .= "function show$record_type(){\n";
	    	
	    	foreach($this->_getVisibleKeysForType($record_type) as $visible_key){
	    		$parsed .= "document.getElementById('custom_tr_$visible_key').style.display='';\n";
	    	}
	    	$parsed .= "}\n";

	    	return $parsed;
		}
		
		function parseShowFunctions(){
			$parsed = "";
			
			foreach($this->custom_record_types as $key => $record_type){
				$parsed .= $this->_parseShowFunction($key);
			}
			return $parsed;
		}
		
		function showHideAllRowsFunction(){
			$parsed = "function hideAllRows(){\n";
			foreach($this->fields_array as $key => $field_config){
				$field_config = unserialize($field_config);
				if($field_config["record_type"] != ""){
					$parsed .= "document.getElementById('custom_tr_$key').style.display='none';\n";
				}
			}
			$parsed .= "}\n";
			return $parsed;
		}
		
	}
?>
