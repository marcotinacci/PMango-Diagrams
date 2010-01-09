<?php 
/**
 * this class model the concept of enumeration of a fixed list of item, in 
 * particular with this abstraction we capture the data array entry to use both in
 * Task and DataArrayBuilder class. 
 * 
 * This class have no constructor because she haven't a state and haven't no 
 * meaning to be instantiated. It's only responsibility is to provide a fixed
 * static variables that captures the data array entry for indexing the associative
 * array needed by the Task e DataArrayBuilder to avoid duplication of the key in
 * both the classes.
 * 
 * Every static item have a name and a associated string with the same value as the
 * name of the item. In this way we can refer only to the static item, without 
 * worrying about the underlying value (that can be changed whenever you want, 
 * without touch the client code)
 */
class DataArrayKeyEnumeration {
	private function __construct() { }
	
	public static $wbsIdentifier = "wbsIdentifier";
	public static $name = "name";
	public static $plan_effort = "plan_effort";
	public static $assigned_to_task = "assigned_to_task";
	public static $plan_duration = "plan_duration";
	public static $plan_cost = "plan_cost";
	public static $planned_start_date = "planned_start_date";
	public static $planned_finish_date = "planned_finish_date";
	public static $act_duration = "act_duration";
	public static $act_effort = "act_effort";
	public static $act_cost = "act_cost";
	public static $actual_start_date = "actual_start_date";
	public static $actual_finish_date = "actual_finish_date";
	public static $level = "level";
	public static $percentage = "percentage";
}
?>