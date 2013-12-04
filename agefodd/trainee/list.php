<?php
/** Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
* Copyright (C) 2012-2013       Florian Henry   	<florian.henry@open-concept.pro>
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
 *	\file       agefodd/trainee/list.php
 *	\ingroup    agefodd
 *	\brief      list of trainee
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_stagiaire.class.php');
require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');

$langs->load('agefodd@agefodd');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

llxHeader('',$langs->trans("AgfStagiaireList"));

$sortorder=GETPOST('sortorder','alpha');
$sortfield=GETPOST('sortfield','alpha');
$page=GETPOST('page','alpha');

//Search criteria
$search_name=GETPOST("search_name");
$search_firstname=GETPOST("search_firstname");
$search_civ=GETPOST("search_civ");
$search_soc=GETPOST("search_soc");
$search_tel=GETPOST("search_tel");
$search_mail=GETPOST("search_mail");

// Do we click on purge search criteria ?
if (GETPOST("button_removefilter_x"))
{
	$search_name='';
	$search_firstname='';
	$search_civ='';
	$search_soc='';
	$search_tel='';
	$search_mail='';
}

$filter=array();
if (!empty($search_name)) {
	$filter['s.nom']=$search_name;
}
if (!empty($search_firstname)) {
	$filter['s.prenom']=$search_firstname;
}
if (!empty($search_civ)) {
	$filter['civ.code']=$search_civ;
}
if (!empty($search_soc)) {
	$filter['so.nom']=$search_soc;
}
if (!empty($search_tel)) {
	$filter['s.tel1']=$search_tel;
}
if (!empty($search_mail)) {
	$filter['s.mail']=$search_mail;
}


if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="s.rowid";


if ($page == -1) {
	$page = 0 ;
}

$limit = $conf->liste_limit;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;

$formcompagny = new FormCompany($db);

$agf = new Agefodd_stagiaire($db);
$result = $agf->fetch_all($sortorder, $sortfield, $limit, $offset, $filter);

if ($result>=0)
{

	print_barre_liste($langs->trans("AgfStagiaireList"), $page, $_SERVER['PHP_SELF'],"", $sortfield, $sortorder,'', $result);

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	$arg_url='&page='.$page.'&search_name='.$search_name.'&search_firstname='.$search_firstname.'&search_civ='.$search_civ.'&search_soc='.$search_soc.'&search_tel='.$search_tel.'&search_mail='.$search_mail;
	print_liste_field_titre($langs->trans("Id"),$_SERVER['PHP_SELF'],"s.rowid","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("AgfNomPrenom"),$_SERVER['PHP_SELF'],"s.nom","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("AgfCivilite"),$_SERVER['PHP_SELF'],"civ.code","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Company"),$_SERVER['PHP_SELF'],"so.nom","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Phone"),$_SERVER['PHP_SELF'],"s.tel1","",$arg_url,'',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Mail"),$_SERVER['PHP_SELF'],"s.mail","",$arg_url,'',$sortfield,$sortorder);
	print '<td>&nbsp;</td>';
	print "</tr>\n";

	//Search bar
	$url_form=$_SERVER["PHP_SELF"];
	$addcriteria=false;
	if (!empty($sortorder)){
		$url_form.='?sortorder='.$sortorder;
		$addcriteria=true;
	}
	if (!empty($sortfield)){
		if ($addcriteria){
			$url_form.='&sortfield='.$sortfield;
		}
		else {$url_form.='?sortfield='.$sortfield;
		}
		$addcriteria=true;
	}
	if (!empty($page)){
		if ($addcriteria){
			$url_form.='&page='.$page;
		}
		else {$url_form.='?page='.$page;
		}
	}

	print '<form method="get" action="'.$url_form.'" name="search_form">'."\n";
	print '<tr class="liste_titre">';

	print '<td>&nbsp;</td>';

	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_name" value="'.$search_name.'" size="10">';
	print '<input type="text" class="flat" name="search_firstname" value="'.$search_firstname.'" size="10">';
	print '</td>';

	print '<td class="liste_titre">';
	print $formcompagny->select_civility($search_civ,'search_civ');
	print '</td>';

	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_soc" value="'.$search_soc.'" size="20">';
	print '</td>';

	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_tel" value="'.$search_tel.'" size="10">';
	print '</td>';

	print '<td class="liste_titre">';
	print '<input type="text" class="flat" name="search_mail" value="'.$search_mail.'" size="20">';
	print '</td>';

	print '<td class="liste_titre" align="right"><input class="liste_titre" type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
	print '&nbsp; ';
	print '<input type="image" class="liste_titre" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
	print '</td>';

	print "</tr>\n";
	print '</form>';

	$var=true;
	foreach ($agf->lines as $line)
	{

		// Affichage liste des stagiaires
		$var=!$var;
		print "<tr $bc[$var]>";
		print '<td><a href="card.php?id='.$line->rowid.'">'.img_object($langs->trans("AgfShowDetails"),"user").' '.$line->rowid.'</a></td>';
		print '<td>'.strtoupper($line->nom).' '.ucfirst($line->prenom).'</td>';

		$contact_static= new Contact($db);
		$contact_static->civilite_id = $line->civilite;

		print '<td>'.$contact_static->getCivilityLabel().'</td>';
		print '<td>';
		if ($line->socid)
		{
			print '<a href="'.dol_buildpath('/comm/fiche.php',1).'?socid='.$line->socid.'">';
			print img_object($langs->trans("ShowCompany"),"company").' '.dol_trunc($line->socname,20).'</a>';
		}
		else
		{
			print '&nbsp;';
		}
		print '</td>';
		print '<td>'.dol_print_phone($line->tel1).'</td>';
		print '<td>'.dol_print_email($line->mail, $line->rowid, $line->socid,'AC_EMAIL',25).'</td>';
		print '<td>&nbsp;</td>';
		print "</tr>\n";

	}

	print "</table>";
}
else
{
	setEventMessage($agf->error,'errors');
}

llxFooter();
$db->close();