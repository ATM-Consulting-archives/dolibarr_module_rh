<?php
/**
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012-2013 Florian Henry <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file agefodd/cursus/card_trainee.php
 * \ingroup agefodd
 * \brief card of trainee by cursus
 */
error_reporting ( E_ALL );
ini_set ( 'display_errors', true );
ini_set ( 'html_errors', false );

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die ( "Include of main fails" );

require_once ('../class/agefodd_cursus.class.php');
require_once ('../class/agefodd_stagiaire_cursus.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden ();

$langs->load ( 'agefodd@agefodd' );
$langs->load ( 'companies' );

$action = GETPOST ( 'action', 'alpha' );
$confirm = GETPOST ( 'confirm', 'alpha' );
$id = GETPOST ( 'id', 'int' );

$sortorder = GETPOST ( 'sortorder', 'alpha' );
$sortfield = GETPOST ( 'sortfield', 'alpha' );
$page = GETPOST ( 'page', 'int' );

$search_name = GETPOST ( "search_name" );
$search_firstname = GETPOST ( "search_firstname" );
$search_civ = GETPOST ( "search_civ" );
$search_soc = GETPOST ( "search_soc" );

if ($page == - 1) {
	$page = 0;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Do we click on purge search criteria ?
if (GETPOST ( "button_removefilter_x" )) {
	$search_name = '';
	$search_firstname = '';
	$search_civ = '';
	$search_soc = '';
}

$filter = array ();
if (! empty ( $search_name )) {
	$filter ['sta.nom'] = $search_name;
}
if (! empty ( $search_firstname )) {
	$filter ['sta.prenom'] = $search_firstname;
}
if (! empty ( $search_civ )) {
	$filter ['civ.code'] = $search_civ;
}
if (! empty ( $search_soc )) {
	$filter ['so.nom'] = $search_soc;
}

if (empty ( $sortorder ))
	$sortorder = "ASC";
if (empty ( $sortfield ))
	$sortfield = "sta.nom";
	
	/*
 * Associate training to cursus
*/
if ($action == 'addtrainee') {
	$training = new Agefodd_stagiaire_cursus ( $db );
	$training->fk_stagiaire = GETPOST ( 'stagiaire', 'int' );
	$training->fk_cursus = $id;
	$result = $training->create ( $user );
	if ($result < 0) {
		setEventMessage ( $training->error, 'errors' );
	}
}

/*
 * Remove training to cursus
*/
if ($action == 'confirm_delete_trainee' && $confirm == "yes" && $user->rights->agefodd->creer) {
	$training = new Agefodd_stagiaire_cursus ( $db );
	$training->id = GETPOST ( 'lineid', 'int' );
	$result = $training->delete ( $user );
	if ($result < 0) {
		setEventMessage ( $training->error, 'errors' );
	}
}

/*
 * View
*/

$title = $langs->trans ( "AgfCursusParticipants" );
llxHeader ( '', $title );

$form = new Form ( $db );
$formAgefodd = new FormAgefodd ( $db );
$formcompagny = new FormCompany ( $db );

// Card
if (! empty ( $id )) {
	$agf = new Agefodd_cursus ( $db );
	$result = $agf->fetch ( $id );
	
	if ($result > 0) {
		$head = cursus_prepare_head ( $agf );
		
		dol_fiche_head ( $head, 'trainee', $langs->trans ( "AgfCursusParticipants" ), 0, 'calendarweek' );
		
		// Display View mode
		
		/*
		 * Confirm delete trainee
		*/
		if ($action == 'delete_trainee') {
			// Param url = id de la ligne stagiaire dans session - id session
			$ret = $form->form_confirm ( $_SERVER ['PHP_SELF'] . '?id=' . $id . '&lineid=' . GETPOST ( 'lineid', 'int' ), $langs->trans ( "AgfRemoveTraineeCursus" ), $langs->trans ( "AgfConfirmRemoveTraineeCursus" ), "confirm_delete_training", '', '', 1 );
			if ($ret == 'html')
				print '<br>';
		}
		
		print '<table class="border" width="100%">';
		
		print '<tr><td width="20%">' . $langs->trans ( "Id" ) . '</td>';
		print '<td>' . $form->showrefnav ( $agf, 'id', '', 1, 'rowid', 'id' ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfRefInterne" ) . '</td>';
		print '<td>' . $agf->ref_interne . '</td></tr>';
		
		print '<tr><td width="20%">' . $langs->trans ( "AgfIntitule" ) . '</td>';
		print '<td>' . $agf->intitule . '</td></tr>';
		
		print '<tr><td valign="top">' . $langs->trans ( "NotePublic" ) . '</td>';
		print '<td>' . $agf->note_public . '</td></tr>';
		
		print '<tr><td valign="top">' . $langs->trans ( "NotePrivate" ) . '</td>';
		print '<td>' . $agf->note_private . '</td></tr>';
		
		print "</table>";
		
		print '</div>';
	} else {
		setEventMessage ( $agf->error, 'errors' );
	}
	
	/*
 * Manage trainee
*/
	
	$trainee = new Agefodd_stagiaire_cursus ( $db );
	$trainee->fk_cursus = $agf->id;
	$result = $trainee->fetch_stagiaire_per_cursus ( $sortorder, $sortfield, $limit, $offset, $filter );
	if ($result < 0) {
		setEventMessage ( $trainee->error, 'errors' );
	}
	$nbtrainee = count ( $trainee->lines );
	
	print_barre_liste ( $langs->trans ( "AgfMenuActStagiaire" ), $page, $_SERVER ['PHP_SELF'], '&id=' . $id, $sortfield, $sortorder, "", $nbtrainee );
	
	print '<table class="noborder" width="100%">';
	print '<tr>';
	if ($nbtrainee < 1) {
		print '<td style="text-decoration: blink;">' . $langs->trans ( "AgfLimiteNoOne" ) . '</td>';
	} else {
		print '<td>' . $langs->trans ( "AgfMenuActStagiaire" ) . ' (' . $nbtrainee . ')' . '</td>';
	}
	print '</tr>';
	
	if ($nbtrainee > 0) {
		print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
		$arg_url = '&page=' . $page . '&search_name=' . $search_name . '&search_firstname=' . $search_firstname . '&search_civ=' . $search_civ . '&search_soc=' . $search_soc . '&id=' . $agf->id;
		print_liste_field_titre ( $langs->trans ( "AgfNomPrenom" ), $_SERVER ['PHP_SELF'], "sta.nom", "", $arg_url, '', $sortfield, $sortorder );
		print_liste_field_titre ( $langs->trans ( "AgfCivilite" ), $_SERVER ['PHP_SELF'], "civ.code", "", $arg_url, '', $sortfield, $sortorder );
		print_liste_field_titre ( $langs->trans ( "Company" ), $_SERVER ['PHP_SELF'], "so.nom", "", $arg_url, '', $sortfield, $sortorder );
		print_liste_field_titre ( $langs->trans ( "AgfSessionDoneInCursus" ), $_SERVER ['PHP_SELF'], "", "", $arg_url, '', $sortfield, $sortorder );
		print_liste_field_titre ( $langs->trans ( "AgfSessionToDoInCursus" ), $_SERVER ['PHP_SELF'], "", "", $arg_url, '', $sortfield, $sortorder );
		print '<td>&nbsp;</td>';
		print "</tr>\n";
		
		// Search bar
		$url_form = $_SERVER ["PHP_SELF"] . '?id=' . $agf->id;
		$addcriteria = false;
		if (! empty ( $sortorder )) {
			$url_form .= '&sortorder=' . $sortorder;
			$addcriteria = true;
		}
		if (! empty ( $sortfield )) {
			if ($addcriteria) {
				$url_form .= '&sortfield=' . $sortfield;
			} else {
				$url_form .= '&sortfield=' . $sortfield;
			}
			$addcriteria = true;
		}
		if (! empty ( $page )) {
			if ($addcriteria) {
				$url_form .= '&page=' . $page;
			} else {
				$url_form .= '&page=' . $page;
			}
		}
		
		print '<form method="get" action="' . $url_form . '" name="search_form">' . "\n";
		print '<input type="hidden" value="'.$id.'" name="id">';
		print '<tr class="liste_titre">';
		
		print '<td class="liste_titre">';
		print '<input type="text" class="flat" name="search_name" value="' . $search_name . '" size="10">';
		print '<input type="text" class="flat" name="search_firstname" value="' . $search_firstname . '" size="10">';
		print '</td>';
		
		print '<td class="liste_titre">';
		print $formcompagny->select_civility ( $search_civ, 'search_civ' );
		print '</td>';
		
		print '<td class="liste_titre">';
		print '<input type="text" class="flat" name="search_soc" value="' . $search_soc . '" size="20">';
		print '</td>';
		
		print '<td class="liste_titre">';
		print '</td>';
		
		print '<td class="liste_titre">';
		print '</td>';
		
		print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '">';
		print '&nbsp; ';
		print '<input type="image" class="liste_titre" name="button_removefilter" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/searchclear.png" value="' . dol_escape_htmltag ( $langs->trans ( "RemoveFilter" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "RemoveFilter" ) ) . '">';
		print '</td>';
		
		print "</tr>\n";
		print '</form>';
		
		$var = true;
		foreach ( $trainee->lines as $line ) {
			
			// Affichage liste des stagiaires
			$var = ! $var;
			print "<tr $bc[$var]>";
			print '<td><a href="../trainee/card.php?id=' . $line->starowid . '">' . img_object ( $langs->trans ( "AgfShowDetails" ), "user" ) . ' ' . strtoupper ( $line->nom ) . ' ' . ucfirst ( $line->prenom ) . '</a></td>';
			
			$contact_static = new Contact ( $db );
			$contact_static->civilite_id = $line->civilite;
			
			print '<td>' . $contact_static->getCivilityLabel () . '</td>';
			print '<td>';
			if ($line->socid) {
				print '<a href="' . dol_buildpath ( '/comm/fiche.php', 1 ) . '?socid=' . $line->socid . '">';
				print img_object ( $langs->trans ( "ShowCompany" ), "company" ) . ' ' . dol_trunc ( $line->socname, 20 ) . '</a>';
			} else {
				print '&nbsp;';
			}
			print '</td>';
			print '<td><a href="../trainee/cursus_detail.php?cursus_id='.$id.'&id=' . $line->starowid . '">' . $line->nbsessdone . '</a></td>';
			print '<td><a href="../trainee/cursus_detail.php?cursus_id='.$id.'&id=' . $line->starowid . '">' . $line->nbsesstodo . '</a></td>';
			print '<td>&nbsp;</td>';
			print "</tr>\n";
		}
		
		print "</table>";
	}
	
	if ($user->rights->agefodd->modifier) {
		print '<form name="update" action="' . $_SERVER ['PHP_SELF'] . '?id=' . $agf->id . '" method="post">' . "\n";
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
		print '<input type="hidden" name="action" value="addtrainee">' . "\n";
		
		print '<table class="noborder" width="100%">';
		print '<tr>';
		print '<td>' . $langs->trans ( 'AgfStagiaireAdd' );
		print $formAgefodd->select_stagiaire ( '', 'stagiaire', 's.rowid NOT IN (SELECT fk_stagiaire FROM ' . MAIN_DB_PREFIX . 'agefodd_stagiaire_cursus WHERE fk_cursus=' . $id . ')', 1 );
		print '<input type="submit" class="butAction" value="' . $langs->trans ( "Add" ) . '"></td>';
		print '</tr>';
		print "</table>";
		print '</form>';
	}
}

llxFooter ();
$db->close ();