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
?>