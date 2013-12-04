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
 * 	\file		/agefodd/index.php
 * 	\brief		Tableau de bord du module de formation pro. (Agefodd).
* 	\Version	$Id$
*/

$res=@include("../main.inc.php");					// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

dol_include_once('/agefodd/class/agefodd_index.class.php');
dol_include_once('/agefodd/class/agefodd_sessadm.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
dol_include_once('/core/lib/date.lib.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$langs->load('agefodd@agefodd');

llxHeader('',$langs->trans('AgefoddShort'));

print_barre_liste($langs->trans("AgfBilanGlobal"), $page, "index.php","&socid=$socid",$sortfield,$sortorder,'',$num);


print '<table width="auto">';

//colonne gauche
print '<tr><td width=auto>';
print '<table class="noborder" width="400px">';
print '<tr class="liste_titre"><td colspan=4>'.$langs->trans("AgfIndexStatistique").'</td></tr>';

$agf = new Agefodd_index($db);

// Nbre de formation au catalogue actuellement
$resql = $agf->fetch_formation_nb();
print '<tr class="liste"><td>'.$langs->trans("AgfIndexTrainCat").' </td><td align="right">';
print '<a href="'.dol_buildpath('/agefodd/training/list.php',1).'?mainmenu=agefodd">';
print $agf->num.'</a>&nbsp;</td></tr>';


// nbre de stagiaires formés
$resql = $agf->fetch_student_nb();
print '<tr class="liste"><td>'.$langs->trans("AgfIndexTraineeTrained").' </td><td align="right">'.$resql.'&nbsp;</td></tr>';


// nbre de sessions realisées
$resql = $agf->fetch_session_nb();
$nb_total_session = $agf->num;
print '<tr class="liste"><td>'.$langs->trans("AgfIndexSessDo").' </td><td align="right">'.$nb_total_session.'&nbsp;</td></tr>';


// Nbre d'heure/session délivrées
$resql = $agf->fetch_heures_sessions_nb();
print '<tr class="liste"><td>'.$langs->trans("AgfIndexHourSessDo").' </td><td align="right">'.$agf->total.'&nbsp;</td></tr>';
$total_heures = $agf->total;
if ($total_heures == 0 ) $total_heures = 1;



// Nbre d'heures stagiaires délivrées
$resql = $agf->fetch_heures_stagiaires_nb();
print '<tr class="liste"><td>'.$langs->trans("AgfIndexHourTrainneDo").'  </td><td align="right">'.$agf->total.'&nbsp;</td></tr>';

print '<table></table>';
print '&nbsp;';
print '<table class="noborder" width="400px">';

// Les 5 dernieres sessions
print '<tr class="liste_titre"><td colspan=4>'.$langs->trans("AgfIndex5sess").'</td></tr>';
$resql = $agf->fetch_last_formations(5);
$num = count($agf->line);
for ($i=0; $i < $num; $i++)
{
	print '<tr class="liste"><td>';
	print '<a href="'.dol_buildpath('/agefodd/session/card.php',1).'?id='.$agf->line[$i]->id.'">';
	print img_object($langs->trans("AgfShowDetails"),"generic").' '.$agf->line[$i]->id.'</a></td>';
	print '<td colspan=2>'.dol_trunc($agf->line[$i]->intitule, 50).'</td><td align="right">';
	$ilya = (num_between_day($agf->line[$i]->datef, dol_now(),0));
	print $langs->trans("AgfThereIsDay",$ilya);//"il y a ".$ilya." j.";
	print '</td></tr>';
}

print '<table></table>';
print '&nbsp;';
print '<table class="noborder" width="400px">';

// top 5 des formations
print '<tr class="liste_titre"><td colspan=4>'.$langs->trans("AgfIndexTop5").'</td></tr>';
$resql = $agf->fetch_top_formations(5);
$num = count($agf->line);
for ($i=0; $i < $num; $i++)
{
	print '<tr class="liste"><td>';
	print '<a href="'.dol_buildpath('/agefodd/training/card.php',1).'?id='.$agf->line[$i]->idforma.'">';
	print img_object($langs->trans("AgfShowDetails"),"service").' '.$agf->line[$i]->idforma.'</a></td>';
	print '<td colspan=2>'.dol_trunc($agf->line[$i]->intitule, 50).'</td><td align="right">'.$agf->line[$i]->num.' '.sprintf("(%02.1f%%)", (($agf->line[$i]->num * $agf->line[$i]->duree * 100)/$total_heures) ).'</td></tr>';
}
print "</table>";
print '&nbsp;';


//colonne droite
print '</td><td width="auto" valign="top">';

// tableau de bord travail
print '<table class="noborder" width="500px" align="left">';
print '<tr class="liste_titre"><td colspan=3>'.$langs->trans("AgfIndexBoard").' </td>';
print '<td width="50px" align="right">'.$langs->trans("AgfNumber").'</td></tr>';

// sessions en cours
print '<tr class="liste"><td width="10px">'.img_object($langs->trans("AgfShowDetails"),"generic").'</td>';
$resql = $agf->fetch_session(0);
print '<td colspan="2" >'.$langs->trans("AgfRunningSession").'</td><td align="right">';
print '<a href="'.dol_buildpath('/agefodd/session/list.php',1).'">'.$agf->total.'</a>&nbsp;</td></tr>' ;

// tâches en retard
print '<tr class="liste"><td width="10px">&nbsp;</td><td bgcolor="red">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$resql = $agf->fetch_tache_en_retard(0);
$nbre = count($agf->line);
print '<td>'.$langs->trans("AgfAlertLevel0").'</td><td align="right">';
if ($nbre!=0) print '<a href="'.dol_buildpath('/agefodd/session/administrative.php',1).'?id='.$agf->line[0]->sessid.'">'.$nbre.'</a>&nbsp;';
else print '0&nbsp;';
print '</td></tr>' ;

// Taches urgentes (3 jours avant limite)
print '<tr class="liste"><td width="10px">&nbsp;</td><td bgcolor="orange">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$agf->fetch_session_per_dateLimit('asc', 's.datea', '10', '0', 3, 1);
$nbre = count($agf->line);
print '<td>'.$langs->trans("AgfAlertLevel1").'</td><td align="right">';
if ($nbre!=0) print '<a href="'.dol_buildpath('/agefodd/session/administrative.php',1).'?id='.$agf->line[0]->sessid.'">'.$nbre.'</a>&nbsp;';
else print '0&nbsp;';
print '</td></tr>';

// Taches à planifier (8 jours avant limite)
print '<tr class="liste"><td width="10px">&nbsp;</td><td bgcolor="#ffe27d">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$agf->fetch_session_per_dateLimit('asc', 's.datea', '10', '0', 8, 3);
$nbre = count($agf->line);
print '<td >'.$langs->trans("AgfAlertLevel2").'</td><td align="right">';
if ($nbre!=0) print '<a href="'.dol_buildpath('/agefodd/session/administrative.php',1).'?id='.$agf->line[0]->sessid.'">'.$nbre.'</a>&nbsp;';
else print '0&nbsp;';
print '</td></tr>';

// tâches en cours
print '<tr class="liste"><td width="10px">&nbsp;</td><td width="10px">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$resql = $agf->fetch_tache_en_cours();
print '<td>'.$langs->trans("AgfAlertLevel3").'</td><td align="right">';
print '<a href="'.dol_buildpath('/agefodd/session/list.php',1).'">'.$agf->total.'</a>&nbsp;</td></tr>' ;

// sessions à archiver
print '<tr class="liste"><td width="10px" valign="top">'.img_object($langs->trans("AgfShowDetails"),"generic").'</td>';
$num = $agf->fetch_session_to_archive();
print '<td colspan="2" >'.$langs->trans("AgfSessionReadyArchive").'</td><td align="right">';
if ($num != 0)print '<a href="'.dol_buildpath('/agefodd/session/card.php',1).'?id='.$agf->sessid.'">'.$num.'</a>&nbsp;';
else print '0&nbsp;';
print '</td></tr>';


// sessions archivées
print '<tr class="liste"><td width="10px">'.img_object($langs->trans("AgfShowDetails"),"generic").'</td>';
$resql = $agf->fetch_session(1);
if ($resql)
{
	print '<td colspan="2" >'.$langs->trans("AgfMenuSessArchList").'</td><td align="right">';
	print '<a href="'.dol_buildpath('/agefodd/session/list.php',1).'?arch=1">'.$agf->total.'</a>&nbsp;</td></tr>' ;
	//$db->free($resql);
}
else
{
	print '<td colspan="3">&nbsp;</td></tr>';
	//dol_print_error($db);
}

print '</table>';
print '<table></table>';
print '&nbsp;';

if (!empty($conf->global->AGF_MANAGE_CERTIF)) {
	// tableau de bord travail
	
	$time_expiration=GETPOST('certif_time','int');
	if (empty($time_expiration)) {
		$time_expiration=6;
	}
	
	$filter_month_array=array(1,2,3,6,12);
	
	print '<form name="search_certif" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<div style="overflow:auto; height: 200px; overflow-x: hidden;">';
	print '<table class="noborder" width="500px" align="left">';
	print '<tr class="liste_titre"><th>'.$langs->trans("AgfIndexCertif");
	print '<select name="certif_time">';
	foreach($filter_month_array as $i) {
		
		if ($time_expiration==$i) {
			$selected='selected="selected"';
		}else {
			$selected='';
		}
		print '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
	}
	print '</select>'.$langs->trans('Month').'(s)';
	print '<input class="liste_titre" type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/search.png" value="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '" title="' . dol_escape_htmltag ( $langs->trans ( "Search" ) ) . '">';
	print '</th></tr>';

	//List de stagaire concerné
	
	$result = $agf->fetch_certif_expire($time_expiration);
	if ($result && (count($agf->lines)>0)) {

		$style='impair';
		foreach($agf->lines as $line) {
			if ($style=='pair') {
				$style='impair';
			}
			else {$style='pair';
			}
				
			print '<tr class="'.$style.'"><td>';
			print '<a href="'.dol_buildpath('/societe/soc.php',1).'?socid='.$line->customer_id.'">'.$line->customer_name.'</a>';
			print '&nbsp;-&nbsp;<a href="'.dol_buildpath('/agefodd/certificate/list.php',1).'?socid='.$line->customer_id.'&search_training_ref='.$line->fromref.'">'.$line->fromintitule.'</a>';
			print '</td></tr>';
				
		}
	}
	else
	{
		print '<tr class="pair"><td>'.$langs->trans('AgfNoCertif').'</td></tr>';
	}

	print '</table>';
	print '</div>';
	print '</form>';
}

// fin colonne droite
print '</td></tr></table>';

llxFooter();
$db->close();