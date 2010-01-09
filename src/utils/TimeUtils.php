<?php
/**
 * la funzione converte dal formato mySQL datetime
 * al formato unix timestamp
 * @param str
 * la data (YYYY-MM-DD HH:MM:SS) da convertire in timestamp
 */
function toTimeStamp($str) {
	list($date, $time) = explode(' ', $str);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);
	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	return $timestamp;
}
?>