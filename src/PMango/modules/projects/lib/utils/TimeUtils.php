<?php
/**
 * la funzione converte aggiunge alla data un certo numero di ore, giorni, 
 * mesi e anni
 * @param givendate
 * la data (YYYY-MM-DD HH:MM:SS) da modificare
 * @param hour
 * numero di ore da aggiungere
 * @param day
 * numero di giorni da aggiungere
 * @param mth
 * numero di mesi da aggiungere
 * @param yr
 * numero di anni da aggiungere
 */
function add_date($givendate,$hour=0,$day=0,$mth=0,$yr=0) {
	$cd = strtotime($givendate);
	$newdate = date('Y-m-d H:i:s', mktime(date('H',$cd)+$hour,
	date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
	date('d',$cd)+$day, date('Y',$cd)+$yr));
	return $newdate;
}

function mangoToGanttDate($date){
	return substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2).' 00:00:00';
}


/*This method expect a date in full timestamp format YYYY-MM-DD HH:mm:ss and return only the
* date in the format YYYY-MM-DD */
function getDateOnly($fullDate)
{
	$pieces = explode(" ",$fullDate);
	return $pieces[0];
}

/** 
 * compares two timestamps and returns array with differencies 
 * (year, month, day, hour, minute, second)
 */

function diff_date($d1, $d2){
  //check higher timestamp and switch if neccessary
  if ($d1 < $d2){
    $temp = $d2;
    $d2 = $d1;
    $d1 = $temp;
  }
  else {
    $temp = $d1; //temp can be used for day count if required
  }
  $d1 = date_parse(date("Y-m-d H:i:s",$d1));
  $d2 = date_parse(date("Y-m-d H:i:s",$d2));
  //seconds
  if ($d1['second'] >= $d2['second']){
    $diff['second'] = $d1['second'] - $d2['second'];
  }
  else {
    $d1['minute']--;
    $diff['second'] = 60-$d2['second']+$d1['second'];
  }
  //minutes
  if ($d1['minute'] >= $d2['minute']){
    $diff['minute'] = $d1['minute'] - $d2['minute'];
  }
  else {
    $d1['hour']--;
    $diff['minute'] = 60-$d2['minute']+$d1['minute'];
  }
  //hours
  if ($d1['hour'] >= $d2['hour']){
    $diff['hour'] = $d1['hour'] - $d2['hour'];
  }
  else {
    $d1['day']--;
    $diff['hour'] = 24-$d2['hour']+$d1['hour'];
  }
  //days
  if ($d1['day'] >= $d2['day']){
    $diff['day'] = $d1['day'] - $d2['day'];
  }
  else {
    $d1['month']--;
    $diff['day'] = date("t",$temp)-$d2['day']+$d1['day'];
  }
  //months
  if ($d1['month'] >= $d2['month']){
    $diff['month'] = $d1['month'] - $d2['month'];
  }
  else {
    $d1['year']--;
    $diff['month'] = 12-$d2['month']+$d1['month'];
  }
  //years
  $diff['year'] = $d1['year'] - $d2['year'];
  return $diff;
  
}


?>