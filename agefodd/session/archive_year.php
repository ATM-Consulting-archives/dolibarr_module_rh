<?php
/** Copyright (C) 2009-2010	Erick Bullier			<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin			<regis@dolibarr.fr>
* Copyright (C) 2012-2013   Florian Henry   		<florian.henry@open-concept.pro>
* Copyright (C) 2012       Jean-François FERRY		<jfefe@aternatik.fr>
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
 *	\file       agefodd/session/archive_year.php
 *	\ingroup    agefodd
 *	\brief      multi archive per year
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agsession.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");


// Security check
if (!$user->rights->agefodd->lire) accessforbidden();
if ($user->societe_id) accessforbidden();

$action	= GETPOST("action","alpha");
$year	= GETPOST("year","int");


/*
 * Actions archive
*/

if ($action == 'confirm_archive' && $user->rights->agefodd->creer)
{

	$agf = new Agsession($db);

	$result = $agf->updateArchiveByYear($year,$user);

	if ($result > 0)
	{

		/* Si la mise a jour s'est bien passée, on effectue le nettoyage des templates pdf
		 foreach (glob($conf->agefodd->dir_output."/*_".$id."_*.pdf") as $filename) {
		//echo "$filename effacé <br>";
		if(is_file($filename)) unlink("$filename");
		}
		*/
		setEventMessage($langs->trans('AgfArchiveByYearComplete'),'mesgs');

		Header("Location: ".$_SERVER['PHP_SELF']);
		exit;
	}
	else
	{
		dol_syslog("agefodd:session:archive_year error=".$agf->error, LOG_ERR);
		setEventMessage($agf->error);
	}


}


/*
 * View
*/

llxHeader('',$langs->trans("AgfSessionArchive"));

$agf = new Agsession($db);
$formother=new FormOther($db);

print_fiche_titre($langs->trans("AgfSessionArchive"));

print '<table width="100%" class="border">';

print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
print '<input type="hidden" name="token" value="'. $_SESSION['newtoken'].'" />';
print '<input type="hidden" name="id" value="'.$object->id.'" />';
print '<input type="hidden" name="action" value="search_year" />';
// Year
print '<tr><td align="left" width="30%">'.$langs->trans("AgfSelectYearForArchive").'</td><td align="left">';
print $formother->select_year($year,'year',1,3,-1);
print '</td>';

print '<td colspan="6">';
print '<input type="submit" name="filter_year" value="'.$langs->trans('Search').'" />';
print '</td>';

print '</tr>';

print '</table>';


// Search form submitted
if($action == 'search_year')
{
	if (empty($sortorder)) $sortorder="ASC";
	if (empty($sortfield)) $sortfield="s.dated";
	if (empty($arch)) $arch = 0;

	if ($page == -1) {
		$page = 0 ;
	}

	$filter['YEAR(s.dated)']=$year;

	$agf = new Agsession($db);
	$resql = $agf->fetch_all($sortorder, $sortfield, 0, 0, $arch, $filter);

	print_fiche_titre($langs->trans('AgfSearchResults'));

	if ($resql != -1)
	{
		$num = $resql;
		if($num > 0)
		{
				
			print $langs->trans('AgfNumSessionToArchiveForSelectedYear', $num);
				
			print '<ul>';
			foreach($agf->lines as $session) {
				print '<li>'.$session->ref.' '.$session->intitule.' '.dol_print_date($session->dated,'day').'</li>';
			}
			print '</ul>';
				
			print '<div class="tabsAction">';
			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=confirm_archive&year='.$year.'">'.$langs->trans('AgfArchiveConfirm').'</a>';
			print '</div>';
		}
		else
		{
			print $langs->trans('AgfNoSessionToArchive', $num);
		}
	}


}

print '</div>';

llxFooter();
$db->close();