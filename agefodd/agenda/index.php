<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Eric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Florian Henry        <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file agefodd/agenda/index.php
 * \ingroup agefodd
 * \brief Home page of calendar events
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die ( "Include of main fails" );

require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

require_once '../lib/agefodd.lib.php';
require_once '../class/html.formagefodd.class.php';

if (! isset ( $conf->global->AGENDA_MAX_EVENTS_DAY_VIEW ))
	$conf->global->AGENDA_MAX_EVENTS_DAY_VIEW = 3;

$filter_commercial = GETPOST ( 'commercial', 'int' );
$filter_customer = GETPOST ( 'fk_soc', 'int' );
$filter_contact = GETPOST ( 'contact', 'int' );
$filter_trainer = GETPOST ( 'trainerid', 'int' );
if ($filter_commercial == - 1) {
	$filter_commercial = 0;
}
if ($filter_customer == - 1) {
	$filter_customer = 0;
}
if ($filter_contact == - 1) {
	$filter_contact = 0;
}
if ($filter_trainer == - 1) {
	$filter_trainer = 0;
}

$type = GETPOST ( 'type' );
$sortfield = GETPOST ( "sortfield", 'alpha' );
$sortorder = GETPOST ( "sortorder", 'alpha' );
$page = GETPOST ( "page", "int" );
if ($page == - 1) {
	$page = 0;
}
$limit = $conf->liste_limit;
$offset = $limit * $page;
if (! $sortorder)
	$sortorder = "ASC";
if (! $sortfield)
	$sortfield = "a.datec";

$canedit = 1;

if ($type == 'trainer') {
	$canedit = 0;
	
	$filter_trainer = $user->id;
	
	if (! $user->rights->agefodd->agendatrainer)
		accessforbidden ();
} else {
	if (! $user->rights->agefodd->agenda)
		accessforbidden ();
}

$action = GETPOST ( 'action', 'alpha' );
$year = GETPOST ( "year", "int" ) ? GETPOST ( "year", "int" ) : date ( "Y" );
$month = GETPOST ( "month", "int" ) ? GETPOST ( "month", "int" ) : date ( "m" );
$week = GETPOST ( "week", "int" ) ? GETPOST ( "week", "int" ) : date ( "W" );
$day = GETPOST ( "day", "int" ) ? GETPOST ( "day", "int" ) : 0;

$maxprint = (isset ( $_GET ["maxprint"] ) ? GETPOST ( "maxprint" ) : $conf->global->AGENDA_MAX_EVENTS_DAY_VIEW);

if (GETPOST ( 'viewcal' )) {
	$action = 'show_month';
	$day = '';
} // View by month
if (GETPOST ( 'viewweek' )) {
	$action = 'show_week';
	$week = ($week ? $week : date ( "W" ));
	$day = ($day ? $day : date ( "d" ));
} // View by week
if (GETPOST ( 'viewday' )) {
	$action = 'show_day';
	$day = ($day ? $day : date ( "d" ));
} // View by day

$langs->load ( "agenda" );
$langs->load ( "other" );
$langs->load ( "commercial" );
$langs->load ( "agefodd@agefodd" );

/*
 * Actions
 */
if (GETPOST ( "viewlist" )) {
	$param = '';
	foreach ( $_POST as $key => $val ) {
		if ($key == 'token')
			continue;
		$param .= '&' . $key . '=' . urlencode ( $val );
	}
	// print $param;
	header ( "Location: " . dol_buildpath('/agefodd/agenda/listactions.php',1).'?' . $param );
	exit ();
}

if ($action == 'delete_action') {
	$event = new ActionComm ( $db );
	$event->fetch ( $actionid );
	$result = $event->delete ();
}

/*
 * View
 */

$help_url = 'EN:Module_Agenda_En|FR:Module_Agenda|ES:M&oacute;dulo_Agenda';
llxHeader ( '', $langs->trans ( "Agenda" ), $help_url );

$form = new Form ( $db );
$formagefodd = new FormAgefodd ( $db );
$companystatic = new Societe ( $db );
$contactstatic = new Contact ( $db );

$now = dol_now ();

if (empty ( $action ) || $action == 'show_month') {
	$prev = dol_get_prev_month ( $month, $year );
	$prev_year = $prev ['year'];
	$prev_month = $prev ['month'];
	$next = dol_get_next_month ( $month, $year );
	$next_year = $next ['year'];
	$next_month = $next ['month'];
	
	$max_day_in_prev_month = date ( "t", dol_mktime ( 0, 0, 0, $prev_month, 1, $prev_year ) ); // Nb of days in
	                                                                                           // previous month
	$max_day_in_month = date ( "t", dol_mktime ( 0, 0, 0, $month, 1, $year ) ); // Nb of days in next month
	                                                                            // tmpday is a negative or null
	                                                                            // cursor to know how many days
	                                                                            // before the 1 to show on month
	                                                                            // view (if tmpday=0 we start on
	                                                                            // monday)
	$tmpday = - date ( "w", dol_mktime ( 0, 0, 0, $month, 1, $year ) ) + 2;
	$tmpday += ((isset ( $conf->global->MAIN_START_WEEK ) ? $conf->global->MAIN_START_WEEK : 1) - 1);
	if ($tmpday >= 1)
		$tmpday -= 7;
		// Define firstdaytoshow and lastdaytoshow
	$firstdaytoshow = dol_mktime ( 0, 0, 0, $prev_month, $max_day_in_prev_month + $tmpday, $prev_year );
	$next_day = 7 - ($max_day_in_month + 1 - $tmpday) % 7;
	if ($next_day < 6)
		$next_day += 7;
	$lastdaytoshow = dol_mktime ( 0, 0, 0, $next_month, $next_day, $next_year );
}
if ($action == 'show_week') {
	$prev = dol_get_first_day_week ( $day, $month, $year );
	$prev_year = $prev ['prev_year'];
	$prev_month = $prev ['prev_month'];
	$prev_day = $prev ['prev_day'];
	$first_day = $prev ['first_day'];
	
	$week = $prev ['week'];
	
	$day = ( int ) $day;
	$next = dol_get_next_week ( $day, $week, $month, $year );
	$next_year = $next ['year'];
	$next_month = $next ['month'];
	$next_day = $next ['day'];
	
	// Define firstdaytoshow and lastdaytoshow
	$firstdaytoshow = dol_mktime ( 0, 0, 0, $prev_month, $first_day, $prev_year );
	$lastdaytoshow = dol_mktime ( 0, 0, 0, $next_month, $next_day, $next_year );
	
	$max_day_in_month = date ( "t", dol_mktime ( 0, 0, 0, $month, 1, $year ) );
	
	$tmpday = $first_day;
}
if ($action == 'show_day') {
	$prev = dol_get_prev_day ( $day, $month, $year );
	$prev_year = $prev ['year'];
	$prev_month = $prev ['month'];
	$prev_day = $prev ['day'];
	$next = dol_get_next_day ( $day, $month, $year );
	$next_year = $next ['year'];
	$next_month = $next ['month'];
	$next_day = $next ['day'];
	
	// Define firstdaytoshow and lastdaytoshow
	$firstdaytoshow = dol_mktime ( 0, 0, 0, $prev_month, $prev_day, $prev_year );
	$lastdaytoshow = dol_mktime ( 0, 0, 0, $next_month, $next_day, $next_year );
}

$title = $langs->trans ( "DoneAndToDoActions" );

$param = '';
$region = '';
if ($filter_commercial)
	$param .= "&commercial=" . $filter_commercial;
if ($filter_customer)
	$param .= "&fk_soc=" . $filter_customer;
if ($filter_contact)
	$param .= "&contact=" . $filter_contact;
if ($filter_trainer)
	$param .= "&trainerid=" . $filter_trainer;
if ($type)
	$param .= "&type=" . $type;
if ($action == 'show_day' || $action == 'show_week')
	$param .= '&action=' . $action;
$param .= "&maxprint=" . $maxprint;

// Show navigation bar
if (empty ( $action ) || $action == 'show_month') {
	$nav = "<a href=\"?year=" . $prev_year . "&amp;month=" . $prev_month . "&amp;region=" . $region . $param . "\">" . img_previous ( $langs->trans ( "Previous" ) ) . "</a>\n";
	$nav .= " <span id=\"month_name\">" . dol_print_date ( dol_mktime ( 0, 0, 0, $month, 1, $year ), "%b %Y" );
	$nav .= " </span>\n";
	$nav .= "<a href=\"?year=" . $next_year . "&amp;month=" . $next_month . "&amp;region=" . $region . $param . "\">" . img_next ( $langs->trans ( "Next" ) ) . "</a>\n";
	$picto = 'calendar';
}
if ($action == 'show_week') {
	$nav = "<a href=\"?year=" . $prev_year . "&amp;month=" . $prev_month . "&amp;day=" . $prev_day . "&amp;region=" . $region . $param . "\">" . img_previous ( $langs->trans ( "Previous" ) ) . "</a>\n";
	$nav .= " <span id=\"month_name\">" . dol_print_date ( dol_mktime ( 0, 0, 0, $month, 1, $year ), "%Y" ) . ", " . $langs->trans ( "Week" ) . " " . $week;
	$nav .= " </span>\n";
	$nav .= "<a href=\"?year=" . $next_year . "&amp;month=" . $next_month . "&amp;day=" . $next_day . "&amp;region=" . $region . $param . "\">" . img_next ( $langs->trans ( "Next" ) ) . "</a>\n";
	$picto = 'calendarweek';
}
if ($action == 'show_day') {
	$nav = "<a href=\"?year=" . $prev_year . "&amp;month=" . $prev_month . "&amp;day=" . $prev_day . "&amp;region=" . $region . $param . "\">" . img_previous ( $langs->trans ( "Previous" ) ) . "</a>\n";
	$nav .= " <span id=\"month_name\">" . dol_print_date ( dol_mktime ( 0, 0, 0, $month, $day, $year ), "daytextshort" );
	$nav .= " </span>\n";
	$nav .= "<a href=\"?year=" . $next_year . "&amp;month=" . $next_month . "&amp;day=" . $next_day . "&amp;region=" . $region . $param . "\">" . img_next ( $langs->trans ( "Next" ) ) . "</a>\n";
	$picto = 'calendarday';
}

// Must be after the nav definition
$param .= '&year=' . $year . '&month=' . $month . ($day ? '&day=' . $day : '');
// print 'x'.$param;

$head = calendars_prepare_head ( '' );

dol_fiche_head ( $head, 'card', $langs->trans ( 'AgfMenuAgenda' ), 0, $picto );
$formagefodd->agenda_filter ( $form, $year, $month, $day, $filter_commercial, $filter_customer, $filter_contact, $filter_trainer, $canedit );
dol_fiche_end ();

$link = '';

print_fiche_titre ( $title, $link . ' &nbsp; &nbsp; ' . $nav, '' );

// Get event in an array
$eventarray = array ();

$sql = 'SELECT a.id,a.label,';
$sql .= ' a.datep,';
$sql .= ' a.datep2,';
$sql .= ' a.datea,';
$sql .= ' a.datea2,';
$sql .= ' a.percent,';
$sql .= ' a.fk_user_author,a.fk_user_action,a.fk_user_done,';
$sql .= ' a.priority, a.fulldayevent, a.location,';
$sql .= ' a.fk_soc, a.fk_contact,';
$sql .= ' ca.code';
$sql .= " FROM " . MAIN_DB_PREFIX . "actioncomm as a";
$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'c_actioncomm as ca ON a.fk_action = ca.id';
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'user as u ON a.fk_user_author = u.rowid ';
$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_session as agf ON agf.rowid = a.fk_element ';
if (! empty ( $filter_commercial )) {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_session_commercial as salesman ON agf.rowid = salesman.fk_session_agefodd ';
}
if (! empty ( $filter_contact )) {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_session_contact as contact_session ON agf.rowid = contact_session.fk_session_agefodd ';
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_contact as contact ON contact_session.fk_agefodd_contact = contact.rowid ';
}
if (! empty ( $filter_trainer )) {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_session_formateur as trainer_session ON agf.rowid = trainer_session.fk_session ';
	if ($type == 'trainer') {
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_formateur as trainer ON trainer_session.fk_agefodd_formateur = trainer.rowid ';
	}
}

$sql .= ' WHERE a.entity IN (' . getEntity () . ')';
$sql .= ' AND a.elementtype=\'agefodd_agsession\'';
if ($action == 'show_day') {
	$sql .= " AND (";
	$sql .= " (datep BETWEEN '" . $db->idate ( dol_mktime ( 0, 0, 0, $month, $day, $year ) ) . "'";
	$sql .= " AND '" . $db->idate ( dol_mktime ( 23, 59, 59, $month, $day, $year ) ) . "')";
	$sql .= " OR ";
	$sql .= " (datep2 BETWEEN '" . $db->idate ( dol_mktime ( 0, 0, 0, $month, $day, $year ) ) . "'";
	$sql .= " AND '" . $db->idate ( dol_mktime ( 23, 59, 59, $month, $day, $year ) ) . "')";
	$sql .= " OR ";
	$sql .= " (datep < '" . $db->idate ( dol_mktime ( 0, 0, 0, $month, $day, $year ) ) . "'";
	$sql .= " AND datep2 > '" . $db->idate ( dol_mktime ( 23, 59, 59, $month, $day, $year ) ) . "')";
	$sql .= ')';
} else {
	// To limit array
	$sql .= " AND (";
	$sql .= " (datep BETWEEN '" . $db->idate ( dol_mktime ( 0, 0, 0, $month, 1, $year ) - (60 * 60 * 24 * 7) ) . "'"; // Start
	                                                                                                                  // 7 days
	                                                                                                                  // before
	$sql .= " AND '" . $db->idate ( dol_mktime ( 23, 59, 59, $month, 28, $year ) + (60 * 60 * 24 * 10) ) . "')"; // End 7
	                                                                                                             // days after
	                                                                                                             // + 3 to go
	                                                                                                             // from 28 to
	                                                                                                             // 31
	$sql .= " OR ";
	$sql .= " (datep2 BETWEEN '" . $db->idate ( dol_mktime ( 0, 0, 0, $month, 1, $year ) - (60 * 60 * 24 * 7) ) . "'";
	$sql .= " AND '" . $db->idate ( dol_mktime ( 23, 59, 59, $month, 28, $year ) + (60 * 60 * 24 * 10) ) . "')";
	$sql .= " OR ";
	$sql .= " (datep < '" . $db->idate ( dol_mktime ( 0, 0, 0, $month, 1, $year ) - (60 * 60 * 24 * 7) ) . "'";
	$sql .= " AND datep2 > '" . $db->idate ( dol_mktime ( 23, 59, 59, $month, 28, $year ) + (60 * 60 * 24 * 10) ) . "')";
	$sql .= ')';
}

if (! empty ( $filter_commercial )) {
	$sql .= " AND salesman.fk_user_com=" . $filter_commercial;
}
if (! empty ( $filter_customer )) {
	$sql .= " AND agf.fk_soc=" . $filter_customer;
}
if (! empty ( $filter_contact )) {
	
	if ($conf->global->AGF_CONTACT_DOL_SESSION) {
		$sql .= " AND contact.fk_socpeople=" . $filter_contact;
	} else {
		$sql .= " AND contact.rowid=" . $filter_contact;
	}
}
if (! empty ( $filter_trainer )) {
	
	if ($type == 'trainer') {
		$sql .= " AND trainer.fk_user=" . $filter_trainer;
	} else {
		$sql .= " AND trainer_session.fk_agefodd_formateur=" . $filter_trainer;
	}
}

// Sort on date
$sql .= ' ORDER BY datep';
// print $sql;

dol_syslog ( "agefodd/agenda/index.php sql=" . $sql, LOG_DEBUG );
$resql = $db->query ( $sql );
if ($resql) {
	$num = $db->num_rows ( $resql );
	$i = 0;
	while ( $i < $num ) {
		$obj = $db->fetch_object ( $resql );
		
		// Create a new object action
		$event = new ActionComm ( $db );
		$event->id = $obj->id;
		$event->datep = $db->jdate ( $obj->datep ); // datep and datef are GMT date
		$event->datef = $db->jdate ( $obj->datep2 );
		$event->type_code = $obj->code;
		$event->libelle = $obj->label;
		$event->percentage = $obj->percent;
		$event->author->id = $obj->fk_user_author;
		$event->usertodo->id = $obj->fk_user_action;
		$event->userdone->id = $obj->fk_user_done;
		
		$event->priority = $obj->priority;
		$event->fulldayevent = $obj->fulldayevent;
		$event->location = $obj->location;
		
		$event->societe->id = $obj->fk_soc;
		$event->contact->id = $obj->fk_contact;
		
		// Defined date_start_in_calendar and date_end_in_calendar property
		// They are date start and end of action but modified to not be outside calendar view.
		if ($event->percentage <= 0) {
			$event->date_start_in_calendar = $event->datep;
			if ($event->datef != '' && $event->datef >= $event->datep)
				$event->date_end_in_calendar = $event->datef;
			else
				$event->date_end_in_calendar = $event->datep;
		} else {
			$event->date_start_in_calendar = $event->datep;
			if ($event->datef != '' && $event->datef >= $event->datep)
				$event->date_end_in_calendar = $event->datef;
			else
				$event->date_end_in_calendar = $event->datep;
		}
		// Define ponctual property
		if ($event->date_start_in_calendar == $event->date_end_in_calendar) {
			$event->ponctuel = 1;
		}
		
		// Check values
		if ($event->date_end_in_calendar < $firstdaytoshow || $event->date_start_in_calendar > $lastdaytoshow) {
			// This record is out of visible range
		} else {
			if ($event->date_start_in_calendar < $firstdaytoshow)
				$event->date_start_in_calendar = $firstdaytoshow;
			if ($event->date_end_in_calendar > $lastdaytoshow)
				$event->date_end_in_calendar = $lastdaytoshow;
				
				// Add an entry in actionarray for each day
			$daycursor = $event->date_start_in_calendar;
			$annee = date ( 'Y', $daycursor );
			$mois = date ( 'm', $daycursor );
			$jour = date ( 'd', $daycursor );
			
			// Loop on each day covered by action to prepare an index to show on calendar
			$loop = true;
			$j = 0;
			$daykey = dol_mktime ( 0, 0, 0, $mois, $jour, $annee );
			do {
				// if ($event->id==408) print 'daykey='.$daykey.' '.$event->datep.'
				// '.$event->datef.'<br>';
				
				$eventarray [$daykey] [] = $event;
				$j ++;
				
				$daykey += 60 * 60 * 24;
				if ($daykey > $event->date_end_in_calendar)
					$loop = false;
			} while ( $loop );
			
			// print 'Event '.$i.' id='.$event->id.'
			// (start='.dol_print_date($event->datep).'-end='.dol_print_date($event->datef);
			// print '
			// startincalendar='.dol_print_date($event->date_start_in_calendar).'-endincalendar='.dol_print_date($event->date_end_in_calendar).')
			// was added in '.$j.' different index key of array<br>';
		}
		$i ++;
	}
} else {
	dol_print_error ( $db );
}

$maxnbofchar = 30;
$cachethirdparties = array ();
$cachecontacts = array ();

// Define theme_datacolor array
$color_file = DOL_DOCUMENT_ROOT . "/theme/" . $conf->theme . "/graph-color.php";
if (is_readable ( $color_file )) {
	include_once $color_file;
}
if (! is_array ( $theme_datacolor ))
	$theme_datacolor = array (
		array (
		120,130,150 
	),array (
		200,160,180 
	),array (
		190,190,220 
	) 
	);

if (empty ( $action ) || $action == 'show_month') // View by month
{
	$newparam = $param; // newparam is for birthday links
	$newparam = preg_replace ( '/action=show_month&?/i', '', $newparam );
	$newparam = preg_replace ( '/action=show_week&?/i', '', $newparam );
	$newparam = preg_replace ( '/day=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/month=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/year=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/viewcal=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/type=trainer/i', '', $newparam );
	$newparam .= '&viewcal=1';
	echo '<table width="100%" class="nocellnopadd cal_month">';
	echo ' <tr class="liste_titre">';
	$i = 0;
	while ( $i < 7 ) {
		echo '  <td align="center">' . $langs->trans ( "Day" . (($i + (isset ( $conf->global->MAIN_START_WEEK ) ? $conf->global->MAIN_START_WEEK : 1)) % 7) ) . "</td>\n";
		$i ++;
	}
	echo " </tr>\n";
	
	$todayarray = dol_getdate ( $now, 'fast' );
	$todaytms = dol_mktime ( 0, 0, 0, $todayarray ['mon'], $todayarray ['mday'], $todayarray ['year'] );
	
	// In loops, tmpday contains day nb in current month (can be zero or negative for days of
	// previous month)
	// var_dump($eventarray);
	// print $tmpday;
	for($iter_week = 0; $iter_week < 6; $iter_week ++) {
		echo " <tr>\n";
		for($iter_day = 0; $iter_day < 7; $iter_day ++) {
			/* Show days before the beginning of the current month (previous month)  */
			if ($tmpday <= 0) {
				$style = 'cal_other_month cal_past';
				if ($iter_day == 6)
					$style .= ' cal_other_month_right';
				echo '  <td class="' . $style . ' nowrap" width="14%" valign="top">';
				show_day_events ( $db, $max_day_in_prev_month + $tmpday, $prev_month, $prev_year, $month, $style, $eventarray, $maxprint, $maxnbofchar, $newparam );
				echo "  </td>\n";
			} elseif ($tmpday <= $max_day_in_month) {
				/* Show days of the current month */
				$curtime = dol_mktime ( 0, 0, 0, $month, $tmpday, $year );
				
				$style = 'cal_current_month';
				if ($iter_day == 6)
					$style .= ' cal_current_month_right';
				$today = 0;
				if ($todayarray ['mday'] == $tmpday && $todayarray ['mon'] == $month && $todayarray ['year'] == $year)
					$today = 1;
				if ($today)
					$style = 'cal_today';
				if ($curtime < $todaytms)
					$style .= ' cal_past';
				
				echo '  <td class="' . $style . ' nowrap" width="14%" valign="top">';
				show_day_events ( $db, $tmpday, $month, $year, $month, $style, $eventarray, $maxprint, $maxnbofchar, $newparam );
				echo "  </td>\n";
			} else {
				/* Show days after the current month (next month) */
				$style = 'cal_other_month';
				if ($iter_day == 6)
					$style .= ' cal_other_month_right';
				echo '  <td class="' . $style . ' nowrap" width="14%" valign="top">';
				show_day_events ( $db, $tmpday - $max_day_in_month, $next_month, $next_year, $month, $style, $eventarray, $maxprint, $maxnbofchar, $newparam );
				echo "</td>\n";
			}
			$tmpday ++;
		}
		echo " </tr>\n";
	}
	echo "</table>\n";
} elseif ($action == 'show_week') // View by week
{
	$newparam = $param; // newparam is for birthday links
	$newparam = preg_replace ( '/action=show_month&?/i', '', $newparam );
	$newparam = preg_replace ( '/action=show_week&?/i', '', $newparam );
	$newparam = preg_replace ( '/day=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/month=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/year=[0-9]+&?/i', '', $newparam );
	$newparam = preg_replace ( '/viewweek=[0-9]+&?/i', '', $newparam );
	$newparam .= '&viewweek=1';
	echo '<table width="100%" class="nocellnopadd cal_month">';
	echo ' <tr class="liste_titre">';
	$i = 0;
	while ( $i < 7 ) {
		echo '  <td align="center">' . $langs->trans ( "Day" . (($i + (isset ( $conf->global->MAIN_START_WEEK ) ? $conf->global->MAIN_START_WEEK : 1)) % 7) ) . "</td>\n";
		$i ++;
	}
	echo " </tr>\n";
	
	// In loops, tmpday contains day nb in current month (can be zero or negative for days of
	// previous month)
	// var_dump($eventarray);
	// print $tmpday;
	
	echo " <tr>\n";
	
	for($iter_day = 0; $iter_day < 7; $iter_day ++) {
		if (($tmpday <= $max_day_in_month)) {
			// Show days of the current week
			$curtime = dol_mktime ( 0, 0, 0, $month, $tmpday, $year );
			
			$style = 'cal_current_month';
			if ($iter_day == 6)
				$style .= ' cal_other_month_right';
			$today = 0;
			$todayarray = dol_getdate ( $now, 'fast' );
			if ($todayarray ['mday'] == $tmpday && $todayarray ['mon'] == $month && $todayarray ['year'] == $year)
				$today = 1;
			if ($today)
				$style = 'cal_today';
			
			echo '  <td class="' . $style . ' nowrap" width="14%" valign="top">';
			show_day_events ( $db, $tmpday, $month, $year, $month, $style, $eventarray, 0, $maxnbofchar, $newparam, 1, 300 );
			echo "  </td>\n";
		} else {
			$style = 'cal_current_month';
			if ($iter_day == 6)
				$style .= ' cal_other_month_right';
			echo '  <td class="' . $style . ' nowrap" width="14%" valign="top">';
			show_day_events ( $db, $tmpday - $max_day_in_month, $next_month, $next_year, $month, $style, $eventarray, 0, $maxnbofchar, $newparam, 1, 300 );
			echo "</td>\n";
		}
		$tmpday ++;
	}
	echo " </tr>\n";
	
	echo "</table>\n";
} else // View by day
{
	$newparam = $param; // newparam is for birthday links
	$newparam = preg_replace ( '/action=show_month&?/i', '', $newparam );
	$newparam = preg_replace ( '/action=show_week&?/i', '', $newparam );
	$newparam = preg_replace ( '/viewday=[0-9]+&?/i', '', $newparam );
	$newparam .= '&viewday=1';
	// Code to show just one day
	$style = 'cal_current_month';
	$today = 0;
	$todayarray = dol_getdate ( $now, 'fast' );
	if ($todayarray ['mday'] == $day && $todayarray ['mon'] == $month && $todayarray ['year'] == $year)
		$today = 1;
	if ($today)
		$style = 'cal_today';
	
	$timestamp = dol_mktime ( 12, 0, 0, $month, $day, $year );
	$arraytimestamp = dol_getdate ( $timestamp );
	echo '<table width="100%" class="nocellnopadd">';
	echo ' <tr class="liste_titre">';
	echo '  <td align="center">' . $langs->trans ( "Day" . $arraytimestamp ['wday'] ) . "</td>\n";
	echo " </tr>\n";
	echo " <tr>\n";
	echo '  <td class="' . $style . ' nowrap" width="14%" valign="top">';
	$maxnbofchar = 80;
	show_day_events ( $db, $day, $month, $year, $month, $style, $eventarray, 0, $maxnbofchar, $newparam, 1, 300 );
	echo "</td>\n";
	echo " </tr>\n";
	echo '</table>';
}

$db->close ();

llxFooter ();

/**
 * Show event of a particular day
 *
 * @param DoliDB $db handler
 * @param int $day
 * @param int $month
 * @param int $year
 * @param int $monthshown month shown in calendar view
 * @param string $style to use for this day
 * @param array	&$eventarray Array of events
 * @param int $maxprint of actions to show each day on month view (0 means no limit)
 * @param int $maxnbofchar of characters to show for event line
 * @param string $newparam on current URL
 * @param int $showinfo extended information (used by day view)
 * @param int $minheight height for each event. 60px by default.
 * @return void
 */
function show_day_events($db, $day, $month, $year, $monthshown, $style, &$eventarray, $maxprint = 0, $maxnbofchar = 16, $newparam = '', $showinfo = 0, $minheight = 60) {

	global $user, $conf, $langs;	
	global $filter, $filtera, $filtert, $filterd, $status;
	global $theme_datacolor;
	global $cachethirdparties, $cachecontacts, $colorindexused;
	
	print '<div id="dayevent_' . sprintf ( "%04d", $year ) . sprintf ( "%02d", $month ) . sprintf ( "%02d", $day ) . '" class="dayevent">' . "\n";
	$curtime = dol_mktime ( 0, 0, 0, $month, $day, $year );
	print '<table class="nobordernopadding" width="100%">';
	print '<tr><td align="left" class="nowrap">';
	print '<a href="' . $_SERVER ['PHP_SELF'];
	print 'action=show_day&day=' . str_pad ( $day, 2, "0", STR_PAD_LEFT ) . '&month=' . str_pad ( $month, 2, "0", STR_PAD_LEFT ) . '&year=' . $year;
	print $newparam;
	print '">';
	if ($showinfo)
		print dol_print_date ( $curtime, 'daytext' );
	else
		print dol_print_date ( $curtime, '%d' );
	print '</a>';
	print '</td><td align="right" class="nowrap">';
	if ($user->rights->agenda->myactions->create || $user->rights->agenda->allactions->create) {
		$newparam .= '&month=' . str_pad ( $month, 2, "0", STR_PAD_LEFT ) . '&year=' . $year;
		
		// $param='month='.$monthshown.'&year='.$year;
		$hourminsec = '100000';
		print '<a href="' . DOL_URL_ROOT . '/comm/action/fiche.php?action=create&datep=' . sprintf ( "%04d%02d%02d", $year, $month, $day ) . $hourminsec . '&backtopage=' . urlencode ( $_SERVER ["PHP_SELF"] . ($newparam ? '?' . $newparam : '') ) . '">';
		print img_picto ( $langs->trans ( "NewAction" ), 'edit_add.png' );
		print '</a>';
	}
	print '</td></tr>';
	print '<tr height="' . $minheight . '"><td valign="top" colspan="2" class="nowrap" style="padding-bottom: 2px;">';
	
	// $curtime = dol_mktime (0, 0, 0, $month, $day, $year);
	$i = 0;
	$nummytasks = 0;
	$numother = 0;
	$numbirthday = 0;
	$numical = 0;
	$numicals = array ();
	$ymd = sprintf ( "%04d", $year ) . sprintf ( "%02d", $month ) . sprintf ( "%02d", $day );
	
	$nextindextouse = count ( $colorindexused );
	// print $nextindextouse;
	
	foreach ( $eventarray as $daykey => $notused ) {
		$annee = date ( 'Y', $daykey );
		$mois = date ( 'm', $daykey );
		$jour = date ( 'd', $daykey );
		if ($day == $jour && $month == $mois && $year == $annee) {
			foreach ( $eventarray [$daykey] as $index => $event ) {
				if ($i < $maxprint || $maxprint == 0 || ! empty ( $conf->global->MAIN_JS_SWITCH_AGENDA )) {
					$ponct = ($event->date_start_in_calendar == $event->date_end_in_calendar);
					
					// Define $color and $cssclass of event
					$color = - 1;
					$cssclass = '';
					$colorindex = - 1;
					if ((! empty ( $event->author->id ) && $event->author->id == $user->id) || (! empty ( $event->usertodo->id ) && $event->usertodo->id == $user->id) || (! empty ( $event->userdone->id ) && $event->userdone->id == $user->id)) {
						$nummytasks ++;
						$cssclass = 'family_mytasks';
					} else if ($event->type_code == 'ICALEVENT') {
						$numical ++;
						if (! empty ( $event->icalname )) {
							if (! isset ( $numicals [dol_string_nospecial ( $event->icalname )] )) {
								$numicals [dol_string_nospecial ( $event->icalname )] = 0;
							}
							$numicals [dol_string_nospecial ( $event->icalname )] ++;
						}
						$color = $event->icalcolor;
						$cssclass = (! empty ( $event->icalname ) ? 'family_' . dol_string_nospecial ( $event->icalname ) : 'family_other');
					} else {
						$numother ++;
						$cssclass = 'family_other';
					}
					if ($color == - 1) 					// Color was not forced. Set color according to color index.
					{
						// Define color index if not yet defined
						$idusertouse = ($event->usertodo->id ? $event->usertodo->id : 0);
						if (isset ( $colorindexused [$idusertouse] )) {
							$colorindex = $colorindexused [$idusertouse]; // Color already assigned to this
								                                              // user
						} else {
							$colorindex = $nextindextouse;
							$colorindexused [$idusertouse] = $colorindex;
							if (! empty ( $theme_datacolor [$nextindextouse + 1] ))
								$nextindextouse ++; // Prepare
									                    // to use next color
						}
						// print
						// '|'.($color).'='.($idusertouse?$idusertouse:0).'='.$colorindex.'<br>';
						// Define color
						$color = sprintf ( "%02x%02x%02x", $theme_datacolor [$colorindex] [0], $theme_datacolor [$colorindex] [1], $theme_datacolor [$colorindex] [2] );
					}
					$cssclass = $cssclass . ' ' . $cssclass . '_day_' . $ymd;
					
					// Show rect of event
					print '<div id="event_' . $ymd . '_' . $i . '" class="event ' . $cssclass . '">';
					print '<ul class="cal_event"><li class="cal_event">';
					print '<table class="cal_event" style="background: #' . $color . '; -moz-border-radius:4px; background: -webkit-gradient(linear, left top, left bottom, from(#' . $color . '), to(#' . dol_color_minus ( $color, 1 ) . ')); " width="100%"><tr>';
					print '<td class="nowrap cal_event">';
					if ($event->type_code == 'BIRTHDAY') 					// It's a birthday
					{
						print $event->getNomUrl ( 1, $maxnbofchar, 'cal_event', 'birthday', 'contact' );
					}
					if ($event->type_code != 'BIRTHDAY') {
						// Picto
						if (empty ( $event->fulldayevent )) {
							// print $event->getNomUrl(2).' ';
						}
						
						// Date
						if (empty ( $event->fulldayevent )) {
							// print '<strong>';
							$daterange = '';
							
							// Show hours (start ... end)
							$tmpyearstart = date ( 'Y', $event->date_start_in_calendar );
							$tmpmonthstart = date ( 'm', $event->date_start_in_calendar );
							$tmpdaystart = date ( 'd', $event->date_start_in_calendar );
							$tmpyearend = date ( 'Y', $event->date_end_in_calendar );
							$tmpmonthend = date ( 'm', $event->date_end_in_calendar );
							$tmpdayend = date ( 'd', $event->date_end_in_calendar );
							// Hour start
							if ($tmpyearstart == $annee && $tmpmonthstart == $mois && $tmpdaystart == $jour) {
								$daterange .= dol_print_date ( $event->date_start_in_calendar, '%H:%M' );
								if ($event->date_end_in_calendar && $event->date_start_in_calendar != $event->date_end_in_calendar) {
									if ($tmpyearstart == $tmpyearend && $tmpmonthstart == $tmpmonthend && $tmpdaystart == $tmpdayend)
										$daterange .= '-';
									// else
									// print '...';
								}
							}
							if ($event->date_end_in_calendar && $event->date_start_in_calendar != $event->date_end_in_calendar) {
								if ($tmpyearstart != $tmpyearend || $tmpmonthstart != $tmpmonthend || $tmpdaystart != $tmpdayend) {
									$daterange .= '...';
								}
							}
							// Hour end
							if ($event->date_end_in_calendar && $event->date_start_in_calendar != $event->date_end_in_calendar) {
								if ($tmpyearend == $annee && $tmpmonthend == $mois && $tmpdayend == $jour)
									$daterange .= dol_print_date ( $event->date_end_in_calendar, '%H:%M' );
							}
							// print $daterange;
							if ($event->type_code != 'ICALEVENT') {
								$savlabel = $event->libelle;
								$event->libelle = $daterange;
								print $event->getNomUrl ( 0 );
								$event->libelle = $savlabel;
							} else {
								print $daterange;
							}
							// print '</strong> ';
							print "<br>\n";
						} else {
							if ($showinfo) {
								print $langs->trans ( "EventOnFullDay" ) . "<br>\n";
							}
						}
						
						// Show title
						if ($event->type_code == 'ICALEVENT')
							print dol_trunc ( $event->libelle, $maxnbofchar );
						else
							print $event->getNomUrl ( 0, $maxnbofchar, 'cal_event' );
						
						if ($event->type_code == 'ICALEVENT')
							print '<br>(' . dol_trunc ( $event->icalname, $maxnbofchar ) . ')';
							
							// If action related to company / contact
						$linerelatedto = '';
						$length = 16;
						if (! empty ( $event->societe->id ) && ! empty ( $event->contact->id ))
							$length = round ( $length / 2 );
						if (! empty ( $event->societe->id ) && $event->societe->id > 0) {
							if (! isset ( $cachethirdparties [$event->societe->id] ) || ! is_object ( $cachethirdparties [$event->societe->id] )) {
								$thirdparty = new Societe ( $db );
								$thirdparty->fetch ( $event->societe->id );
								$cachethirdparties [$event->societe->id] = $thirdparty;
							} else
								$thirdparty = $cachethirdparties [$event->societe->id];
							$linerelatedto .= $thirdparty->getNomUrl ( 1, '', $length );
						}
						if (! empty ( $event->contact->id ) && $event->contact->id > 0) {
							if (! is_object ( $cachecontacts [$event->contact->id] )) {
								$contact = new Contact ( $db );
								$contact->fetch ( $event->contact->id );
								$cachecontacts [$event->contact->id] = $contact;
							} else
								$contact = $cachecontacts [$event->contact->id];
							if ($linerelatedto)
								$linerelatedto .= ' / ';
							$linerelatedto .= $contact->getNomUrl ( 1, '', $length );
						}
						if ($linerelatedto)
							print '<br>' . $linerelatedto;
					}
					
					// Show location
					if ($showinfo) {
						if ($event->location) {
							print '<br>';
							print $langs->trans ( "Location" ) . ': ' . $event->location;
						}
					}
					
					print '</td>';
					// Status - Percent
					print '<td align="right" class="nowrap">';
					if ($event->type_code != 'BIRTHDAY' && $event->type_code != 'ICALEVENT')
						print $event->getLibStatut ( 3, 1 );
					else
						print '&nbsp;';
					print '</td></tr></table>';
					print '</li></ul>';
					print '</div>';
					$i ++;
				} else {
					print '<a href="' . $_SERVER['PHP_SELF'].'?month=' . $monthshown . '&year=' . $year;
					print ($status ? '&status=' . $status : '') . ($filter ? '&filter=' . $filter : '');
					$newparam = preg_replace ( '/maxprint=[0-9]+&?/i', 'maxprint=0', $newparam );
					print $newparam;
					print '">' . img_picto ( "all", "1downarrow_selected.png" ) . ' ...';
					print ' +' . (count ( $eventarray [$daykey] ) - $maxprint);
					print '</a>';
					break;
					// $ok=false; // To avoid to show twice the link
				}
			}
			
			break;
		}
	}
	if (! $i)
		print '&nbsp;';
	
	if (! empty ( $conf->global->MAIN_JS_SWITCH_AGENDA ) && $i > $maxprint && $maxprint) {
		print '<div id="more_' . $ymd . '">' . img_picto ( "all", "1downarrow_selected.png" ) . ' +' . $langs->trans ( "More" ) . '...</div>';
		// print ' +'.(count($eventarray[$daykey])-$maxprint);
		print '<script type="text/javascript">' . "\n";
		print 'jQuery(document).ready(function () {' . "\n";
		print 'jQuery("#more_' . $ymd . '").click(function() { reinit_day_' . $ymd . '(); });' . "\n";
		
		print 'function reinit_day_' . $ymd . '() {' . "\n";
		print 'var nb=0;' . "\n";
		// TODO Loop on each element of day $ymd and start to toggle once $maxprint has been reached
		print 'jQuery(".family_mytasks_day_' . $ymd . '").toggle();';
		print '}' . "\n";
		
		print '});' . "\n";
		print '</script>' . "\n";
	}
	
	print '</td></tr>';
	print '</table>';
	print '</div>' . "\n";
}

/**
 * Change color with a delta
 *
 * @param string $color
 * @param int $minus
 * @return string color
 */
function dol_color_minus($color, $minus) {

	$newcolor = $color;
	$newcolor [0] = ((hexdec ( $newcolor [0] ) - $minus) < 0) ? 0 : dechex ( (hexdec ( $newcolor [0] ) - $minus) );
	$newcolor [2] = ((hexdec ( $newcolor [2] ) - $minus) < 0) ? 0 : dechex ( (hexdec ( $newcolor [2] ) - $minus) );
	$newcolor [4] = ((hexdec ( $newcolor [4] ) - $minus) < 0) ? 0 : dechex ( (hexdec ( $newcolor [4] ) - $minus) );
	return $newcolor;
}

?>
