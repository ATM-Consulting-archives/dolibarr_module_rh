<?php

define('INC_FROM_CRON_SCRIPT', true);

require('../config.php');

$login = GETPOST('login');
$password = GETPOST('password');

$u=new User($db);
$u->fetch('', $login);

if(md5($u->pass) != $password) {
	exit('authentification fault');
	
}

// the iCal date format. Note the Z on the end indicates a UTC timestamp.
define('DATE_ICAL', 'Ymd\THis\Z');
 
// max line length is 75 chars. New line is \\n
 
$output = "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
PRODID:RH-PLANNING-".."\n";
 
// loop over events
foreach ($query_appointments->result() as $appointment):
 $output .=
"BEGIN:VEVENT
SUMMARY:$appointment->firstname $appointment->surname
UID:$appointment->id
STATUS:" . strtoupper($appointment->status) . "
DTSTART:" . date(DATE_ICAL, strtotime($appointment->starts)) . "
DTEND:" . date(DATE_ICAL, strtotime($appointment->ends)) . "
LAST-MODIFIED:" . date(DATE_ICAL, strtotime($appointment->last_update)) . "
LOCATION:$appointment->location_name $appointment->name
END:VEVENT\n";
endforeach;
 
// close calendar
$output .= "END:VCALENDAR";
 
echo $output;
 
