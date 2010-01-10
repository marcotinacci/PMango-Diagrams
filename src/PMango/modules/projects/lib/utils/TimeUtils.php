<?php
/**
 * la funzione converte dal formato mySQL datetime
 * al formato unix timestamp
 * @param str
 * la data (YYYY-MM-DD HH:MM:SS) da convertire in timestamp
 */
function toTimeStamp($str) {
	/*
	list($date, $time) = explode(' ', $str);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);
	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	return $timestamp;*/
	return strtotime($str);
}

function add_date($givendate,$hour=0,$day=0,$mth=0,$yr=0) {
      $cd = strtotime($givendate);
      $newdate = date('Y-m-d H:i:s', mktime(date('h',$cd)+$hour,
    date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
    date('d',$cd)+$day, date('Y',$cd)+$yr));
      return $newdate;
}
?>