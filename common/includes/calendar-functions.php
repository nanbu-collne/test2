<?php
function wc2_get_current_time() {
	//$datetimestr = get_date_from_gmt(gmdate('Y-m-d H:i:s', time()));
	$datetimestr = current_time( 'mysql' );
	$hour = (int)substr($datetimestr, 11, 2);
	$minute = (int)substr($datetimestr, 14, 2);
	$second = (int)substr($datetimestr, 17, 2);
	$month = (int)substr($datetimestr, 5, 2);
	$day = (int)substr($datetimestr, 8, 2);
	$year = (int)substr($datetimestr, 0, 4);
	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	return getdate($timestamp);
}

function wc2_get_today() {
	$dateAry = wc2_get_current_time();
	return array( $dateAry['year'], $dateAry['mon'], $dateAry['mday'] );
}

function wc2_get_today_format() {
	$dateAry = wc2_get_current_time();
	return sprintf( "%04d-%02d-%02d", $dateAry['year'], $dateAry['mon'], $dateAry['mday'] );
}

function wc2_get_today_datetime_format() {
	//$dateAry = wc2_get_current_time();
	//return sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $dateAry['year'], $dateAry['mon'], $dateAry['mday'], $dateAry['hours'], $dateAry['minutes'], $dateAry['seconds'] );
	$datetimestr = current_time( 'mysql' );
	return $datetimestr;
}

function wc2_get_week( $year, $month, $day ) {
	$dateAry = getdate(mktime(0, 0, 0, $month, $day, $year));
	return $dateAry['wday'];
}

function wc2_get_lastday( $year, $month ) {
	list($nextyy, $nextmm) = wc2_get_nextmonth($year, $month);
	$dateAry = getdate(mktime(0, 0, 0, $nextmm, 0, $nextyy));
	return $dateAry['mday'];
}

function wc2_is_today( $year, $month, $day ) {
	list($todayyy, $todaymm, $todaydd) = wc2_get_today();
	if( $year == $todayyy && $month == $todaymm && $day == $todaydd ) {
		return true;
	}
	return false;
}

function wc2_get_nextday( $year, $month, $day ) {
	$dateAry = getdate(mktime(0, 0, 0, $month, $day + 1, $year));
	return array($dateAry['year'], $dateAry['mon'], $dateAry['mday']);
}

function wc2_get_prevday( $year, $month, $day ) {
	$dateAry = getdate(mktime(0, 0, 0, $month, $day - 1, $year));
	return array($dateAry['year'], $dateAry['mon'], $dateAry['mday']);
}

function wc2_get_nextmonth( $year, $month ) {
	$dateAry = getdate(mktime(0, 0, 0, $month + 1, 1, $year));
	return array($dateAry['year'], $dateAry['mon']);
}

function wc2_get_prevmonth( $year, $month ) {
	$dateAry = getdate(mktime(0, 0, 0, $month - 1, 1, $year));
	return array($dateAry['year'], $dateAry['mon']);
}

function wc2_get_aftermonth( $year, $month, $day, $n ) {
	$dateAry = getdate(mktime(0, 0, 0, $month + $n, $day, $year));
	return array($dateAry['year'], $dateAry['mon'], $dateAry['mday']);
}

function wc2_get_beforemonth( $year, $month, $day, $n ) {
	$dateAry = getdate(mktime(0, 0, 0, $month - $n, $day, $year));
	return array($dateAry['year'], $dateAry['mon'], $dateAry['mday']);
}
?>
