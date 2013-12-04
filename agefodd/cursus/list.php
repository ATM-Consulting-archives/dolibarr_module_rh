<?php
/** Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
* Copyright (C) 2012-2013       Florian Henry   <florian.henry@open-concept.pro>
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
 *	\file       agefodd/cursus/list.php
 *	\ingroup    agefodd
 *	\brief      list of place
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_cursus.class.php');
require_once('../lib/agefodd.lib.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

llxHeader('',$langs->trans("AgfMenuCursus"));

$sortorder=GETPOST('sortorder','alpha');
$sortfield=GETPOST('sortfield','alpha');
$page=GETPOST('page','int');
$arch=GETPOST('arch','int');

if (empty($sortorder)) $sortorder="ASC";
if (empty($sortfield)) $sortfield="t.ref_interne";
if (empty($arch)) $arch = 0;

if ($page == -1) {
	$page = 0 ;
}

$limit = $conf->liste_limit;
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$agf = new Agefodd_cursus($db);

$result = $agf->fetch_all($sortorder, $sortfield, $limit, $offset, $arch);

$linenum = count($agf->lines);

print_barre_liste($langs->trans("AgfMenuCursus"), $page, $_SERVER['PHP_SELF'],"&arch=".$arch, $sortfield, $sortorder, "", $linenum);

print '<div width="100%" align="right">';
if ($arch == 2)
{
	print '<a href="'.$_SERVER['PHP_SELF'].'?arch=0">'.$langs->trans("AgfCacherCursusArchives").'</a>'."\n";
}
else
{
	print '<a href="'.$_SERVER['PHP_SELF'].'?arch=2">'.$langs->trans("AgfAfficherCursusArchives").'</a>'."\n";

}
print '<a href="'.$_SERVER['PHP_SELF'].'?arch='.$arch.'">'.$txt.'</a>'."\n";
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print_liste_field_titre($langs->trans("Id"),$_SERVER['PHP_SELF'],"t.rowid",'',"&arch=".$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfRefInterne"),$_SERVER['PHP_SELF'],"t.ref_interne",'', "&arch=".$arch,'',$sortfield,$sortorder);
print_liste_field_titre($langs->trans("AgfIntitule"),$_SERVER['PHP_SELF'],"t.intitule",'',"&arch=".$arch,'',$sortfield,$sortorder);
print "</tr>\n";

if ($result>0)
{
	$var=true;
	$i = 0;
	while ($i < $linenum)
	{
		// Affichage liste des sites de formation
		$var=!$var;
		( $agf->lines[$i]->archive == 1 ) ? $style = ' style="color:gray;"' : $style = '';
		print "<tr $bc[$var]>";
		print '<td><span style="background-color:'.$bgcolor.';"><a href="card.php?id='.$agf->lines[$i]->id.'"'.$style.'>'.img_object($langs->trans("AgfMenuCursus"),"document").' '.$agf->lines[$i]->id.'</a></span></td>'."\n";
		print '<td'.$style.'>'.$agf->lines[$i]->ref_interne.'</td>'."\n";
		print '<td '.$style.'>'.$agf->lines[$i]->intitule.'</td>';
		print '</tr>'."\n";

		$i++;
	}
}
else
{
	setEventMessage($agf->error,'errors');
}
print "</table>";
print '<div>';

print '<div class="tabsAction">';
if ($action != 'create' && $action != 'edit')
{
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="card.php?action=create">'.$langs->trans('Create').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Create').'</a>';
	}
}

print '</div>';


llxFooter();
$db->close();