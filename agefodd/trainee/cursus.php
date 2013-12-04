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
 * \file agefodd/trainee/cursus.php
 * \ingroup agefodd
 * \brief session of trainee
 */

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die ( "Include of main fails" );

require_once ('../class/agefodd_stagiaire.class.php');
require_once ('../class/agefodd_stagiaire_cursus.class.php');
require_once ('../lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php');
require_once ('../class/agsession.class.php');
require_once ('../class/html.formagefodd.class.php');

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden ();

$id = GETPOST ( 'id', 'int' );
$action = GETPOST ( 'action', 'alpha' );

$sortorder = GETPOST ( 'sortorder', 'alpha' );
$sortfield = GETPOST ( 'sortfield', 'alpha' );
$page = GETPOST ( 'page', 'int' );

if (empty ( $sortorder ))
	$sortorder = "ASC";
if (empty ( $sortfield ))
	$sortfield = "c.ref_interne";
if (empty ( $arch ))
	$arch = 0;

if ($page == - 1) {
	$page = 0;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

/*
 * Associate training to cursus
*/
if ($action == 'addcursus') {
	$training = new Agefodd_stagiaire_cursus ( $db );
	$training->fk_stagiaire = $id;
	$training->fk_cursus = GETPOST ( 'cursus_id', 'int' );
	$result = $training->create ( $user );
	if ($result < 0) {
		setEventMessage ( $training->error, 'errors' );
	}
}

/*
 * View
*/

llxHeader ( '', $langs->trans ( "AgfStagiaireCursus" ) );

// Affichage de la fiche "stagiaire"
if ($id) {
	$agf = new Agefodd_stagiaire ( $db );
	$result = $agf->fetch ( $id );
	
	if ($result > 0) {
		
		$form = new Form ( $db );
		$formAgefodd = new FormAgefodd ( $db );
		
		$head = trainee_prepare_head ( $agf );
		
		dol_fiche_head ( $head, 'cursus', $langs->trans ( "AgfStagiaireDetail" ), 0, 'user' );
		
		print '<table class="border" width="100%">';
		
		print '<tr><td width="20%">' . $langs->trans ( "Ref" ) . '</td>';
		print '<td>' . $form->showrefnav ( $agf, 'id	', '', 1, 'rowid', 'id' ) . '</td></tr>';
		
		if (! empty ( $agf->fk_socpeople )) {
			print '<tr><td>' . $langs->trans ( "Lastname" ) . '</td>';
			print '<td><a href="' . dol_buildpath ( '/contact/fiche.php', 1 ) . '?id=' . $agf->fk_socpeople . '">' . strtoupper ( $agf->nom ) . '</a></td></tr>';
		} else {
			print '<tr><td>' . $langs->trans ( "Lastname" ) . '</td>';
			print '<td>' . strtoupper ( $agf->nom ) . '</td></tr>';
		}
		
		print '<tr><td>' . $langs->trans ( "Firstname" ) . '</td>';
		print '<td>' . ucfirst ( $agf->prenom ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfCivilite" ) . '</td>';
		
		$contact_static = new Contact ( $db );
		$contact_static->civilite_id = $agf->civilite;
		
		print '<td>' . $contact_static->getCivilityLabel () . '</td></tr>';
		
		print '<tr><td valign="top">' . $langs->trans ( "Company" ) . '</td><td>';
		if ($agf->socid) {
			print '<a href="' . dol_buildpath ( '/comm/fiche.php', 1 ) . '?socid=' . $agf->socid . '">';
			print img_object ( $langs->trans ( "ShowCompany" ), "company" ) . ' ' . dol_trunc ( $agf->socname, 20 ) . '</a>';
		} else {
			print '&nbsp;';
		}
		print '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfFonction" ) . '</td>';
		print '<td>' . $agf->fonction . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "Phone" ) . '</td>';
		print '<td>' . dol_print_phone ( $agf->tel1 ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "Mobile" ) . '</td>';
		print '<td>' . dol_print_phone ( $agf->tel2 ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "Mail" ) . '</td>';
		print '<td>' . dol_print_email ( $agf->mail, $agf->id, $agf->socid, 'AC_EMAIL', 25 ) . '</td></tr>';
		
		print '<tr><td valign="top">' . $langs->trans ( "AgfNote" ) . '</td>';
		if (! empty ( $agf->note ))
			$notes = nl2br ( $agf->note );
		else
			$notes = $langs->trans ( "AgfUndefinedNote" );
		print '<td>' . stripslashes ( $notes ) . '</td></tr>';
		
		print "</table>";
		print '</div>';
		
		$agf_cursus = new Agefodd_stagiaire_cursus ( $db );
		$agf_cursus->fk_stagiaire = $id;
		$result = $agf_cursus->fetch_cursus_per_trainee ( $sortorder, $sortfield, $limit, $offset );
		
		if ($result < 0) {
			setEventMessage ( $agf_cursus->error, 'errors' );
		}
		
		print_barre_liste ( $langs->trans ( "AgfMenuCursus" ), $page, $_SERVER ['PHP_SELF'], "&arch=" . $arch, $sortfield, $sortorder, "", count ( $agf_cursus->lines ) );
		
		if (count ( $agf_cursus->lines ) > 0) {
			print '<table class="noborder"  width="100%">';
			print '<tr class="liste_titre">';
			print_liste_field_titre ( $langs->trans ( "Id" ), $_SERVER ['PHP_SELF'], "c.rowid", '', '&id=' . $id, '', $sortfield, $sortorder );
			print_liste_field_titre ( $langs->trans ( "AgfRefInterne" ), $_SERVER ['PHP_SELF'], "c.ref_interne", '', '&id=' . $id, '', $sortfield, $sortorder );
			print_liste_field_titre ( $langs->trans ( "AgfIntitule" ), $_SERVER ['PHP_SELF'], "c.intitule", '', '&id=' . $id, '', $sortfield, $sortorder );
			print '<td></td>';
			print "</tr>\n";
			print '</tr>';
			
			$style = 'pair';
			foreach ( $agf_cursus->lines as $line ) {
				if ($style == 'pair') {
					$style = 'impair';
				} else {
					$style = 'pair';
				}
				
				if ($line->archive == 1) {
					$styletext = ' style="color:gray;"';
				} else {
					$styletext = '';
				}
				
				print '<tr class="' . $style . '">';
				
				print '<td ' . $styletext . '><a ' . $styletext . ' href="' . dol_buildpath ( '/agefodd/cursus/card.php', 1 ) . '?id=' . $line->id . '">' . $line->id . '</a></td>';
				print '<td ' . $styletext . '><a ' . $styletext . ' href="' . dol_buildpath ( '/agefodd/cursus/card.php', 1 ) . '?id=' . $line->id . '">' . $line->ref_interne . '</a></td>';
				print '<td ' . $styletext . '>' . $line->intitule . '</td>';
				print '<td><a href="cursus_detail.php?cursus_id='.$line->id.'&id='.$id.'"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" alt="'.$langs->trans("Modify").'"></a></td>';
				print '</tr>';
			}
			print '</table>';
		} else {
			print $langs->trans ( 'AgfNoCursus' );
		}
		
		if ($user->rights->agefodd->modifier) {
			print '<form name="update" action="' . $_SERVER ['PHP_SELF'] . '?id=' . $agf->id . '" method="post">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="addcursus">' . "\n";
			
			print '<table class="noborder" width="100%">';
			print '<tr>';
			print '<td>' . $langs->trans ( 'AgfCursusAdd' ) . ' ';
			print $formAgefodd->select_cursus ( '', 'cursus_id', 'c.ref_interne', 1, 0, array (), array (
				' AND c.rowid NOT IN (SELECT fk_cursus FROM ' . MAIN_DB_PREFIX . 'agefodd_stagiaire_cursus WHERE fk_stagiaire=' . $id . ')' 
			) );
			print '<input type="submit" class="butAction" value="' . $langs->trans ( "Add" ) . '"></td>';
			print '</tr>';
			print "</table>";
			print '</form>';
		}
	} else {
		setEventMessage ( $agf->error, 'errors' );
	}
}

llxFooter ();
$db->close ();