<?php
/** Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
* Copyright (C) 2012       Florian Henry   <florian.henry@open-concept.pro>
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 *	\file       agefodd/training/list.php
 *	\ingroup    agefodd
 *	\brief      list of training
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_formation_catalogue.class.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$langs->load('agefodd@agefodd');


$sortorder=GETPOST('sortorder','alpha');
$sortfield=GETPOST('sortfield','alpha');
$page=GETPOST('page','alpha');
$arch=GETPOST('arch','int');

if (empty($sortorder)) $sortorder="DESC";
if (empty($sortfield)) $sortfield="c.rowid";


if ($page == -1) {
	$page = 0 ;
}

$limit = $conf->liste_limit;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

if (empty($arch)) $arch = 0;

// Search criteria
$search_intitule = GETPOST ( "search_intitule" );
$search_ref = GETPOST ( "search_ref" );
$search_ref_interne = GETPOST ( "search_ref_interne" );
$search_datec = dol_mktime ( 0, 0, 0, GETPOST ( 'search_datecmonth', 'int' ), GETPOST ( 'search_datecday', 'int' ), GETPOST ( 'search_datecyear', 'int' ) );
$search_duree = GETPOST('search_duree');
//$search_dated = dol_mktime ( 0, 0, 0, GETPOST ( 'search_datedmonth', 'int' ), GETPOST ( 'search_datedday', 'int' ), GETPOST ( 'search_datedyear', 'int' ) );



// Do we click on purge search criteria ?
if (GETPOST ( "button_removefilter_x" )) {
	$search_intitule = '';
	$search_ref = '';
	$search_ref_interne = "";
	$search_datec = '';
	$search_duree = "";
	//$search_dated = "";
}


llxHeader('',$langs->trans('AgfMenuCat'));

$agf = new Agefodd($db);
$form = new Form ( $db );

$filter = array ();
if (! empty ( $search_intitule )) {
	$filter ['c.intitule'] = $db->escape($search_intitule);
}
if (! empty ( $search_ref )) {
	$filter ['c.ref'] = $search_ref;
}
if (! empty ( $search_ref_interne )) {
	$filter ['c.ref_interne'] = $search_ref_interne;
}
if (! empty ( $search_datec )) {
	$filter ['c.datec'] = $db->idate ( $search_datec );
}
if (! empty ( $search_duree )) {
	$filter ['c.duree'] = $search_duree;
}


$resql = $agf->fetch_all($sortorder, $sortfield, $limit, $offset, $arch, $filter);

print_barre_liste($langs->trans("AgfMenuCat"), $page, $_SERVER['PHP_SELF'],'&arch='.$arch, $sortfield, $sortorder,'', $resql);

$i = 0;
print '<table class="noborder" width="100%">';
print "<tr class=\"liste_titre\">";
print_liste_field_titre($langs->trans("Id"),$_SERVER['PHP_SELF'],"c.rowid","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfIntitule"),$_SERVER['PHP_SELF'],"c.intitule","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("Ref"),$_SERVER['PHP_SELF'],"c.ref","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfRefInterne"),$_SERVER['PHP_SELF'],"c.ref_interne","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfDateC"),$_SERVER['PHP_SELF'],"c.datec","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfDuree"),$_SERVER['PHP_SELF'],"c.duree","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfDateLastAction"),$_SERVER['PHP_SELF'],"a.dated","",'&arch='.$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfNbreAction"),$_SERVER['PHP_SELF'],'' ,'&arch='.$arch,'',$sortfield,$sortorder);
print "</tr>\n";

print '<form method="get" action="' . $url_form . '" name="search_form">' . "\n";
print '<input type="hidden" name="arch" value="' . $arch . '" >';
print '<tr class="liste_titre">';

print '<td>&nbsp;</td>';

print '<td class="liste_titre">';
print '<input type="text" class="flat" name="search_intitule" value="' . $search_intitule . '" size="20">';
print '</td>';

print '<td class="liste_titre">';
print '<input type="text" class="flat" name="search_ref" value="' . $search_ref . '" size="20">';
print '</td>';

print '<td class="liste_titre">';
print '<input type="text" class="flat" name="search_ref_interne" value="' . $search_ref_interne . '" size="20">';
print '</td>';

print '<td class="liste_titre">';
print $form->select_date ( $search_datec, 'search_datec', 0, 0, 1, 'search_form' );
print '</td>';

print '<td class="liste_titre">';
print '<input type="text" class="flat" name="search_duree" value="' . $search_duree . '" size="5">';
print '</td>';

print '<td class="liste_titre">';
//print $form->select_date ( $search_dated, 'search_dated', 0, 0, 1, 'search_form' );
print '</td>';

print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '">';
print '&nbsp; ';
print '<input type="image" class="liste_titre" name="button_removefilter" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/searchclear.png" value="' . dol_escape_htmltag ( $langs->trans ( "RemoveFilter" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "RemoveFilter" ) ) . '">';
print '</td>';

print "</tr>\n";
print '</form>';

$var=true;
if ($resql>0)
{
	foreach ($agf->lines as $line)
	{

		// Affichage tableau des formations
		$var=!$var;
		print "<tr $bc[$var]>";
		print '<td><a href="card.php?id='.$line->rowid.'">'.img_object($langs->trans("AgfShowDetails"),"service").' '.$line->rowid.'</a></td>';
		print '<td>'.stripslashes($line->intitule).'</td>';
		print '<td>'.$line->ref.'</td>';
		print '<td>'.$line->ref_interne.'</td>';
		print '<td>'.dol_print_date($line->datec,'daytext').'</td>';
		print '<td>'.$line->duree.'</td>';
		print '<td>'.dol_print_date($line->lastsession,'daytext').'</td>';
		print '<td>'.$line->nbsession.'</td>';
		print "</tr>\n";

		$i++;
	}
}
else
{
	setEventMessage($agf->error,'errors');
}

print "</table>";

llxFooter();
$db->close();