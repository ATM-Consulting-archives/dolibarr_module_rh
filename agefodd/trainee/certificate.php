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
 *	\file       agefodd/trainee/certificate.php
 *	\ingroup    agefodd
 *	\brief      certificate of trainee
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_stagiaire.class.php');
require_once('../class/agefodd_stagiaire_certif.class.php');
require_once('../lib/agefodd.lib.php');
require_once('../class/agsession.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php');


// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$id=GETPOST('id','int');


/*
 * View
*/

llxHeader('',$langs->trans("AgfStagiaireDetail"));


// Affichage de la fiche "stagiaire"
if ($id)
{
	$agf = new Agefodd_stagiaire($db);
	$result = $agf->fetch($id);

	if ($result)
	{
		$agf_certif = new Agefodd_stagiaire_certif($db);
		$result=$agf_certif->fetch_all_by_trainee($id);
		if ($result<0) {
			dol_syslog("agefodd:session:subscribers error=".$agf_certif->error, LOG_ERR);
			$mesg = '<div class="error">'.$agf_certif->error.'</div>';
		}

		$form = new Form($db);

		$head = trainee_prepare_head($agf);

		dol_fiche_head($head, 'certificate', $langs->trans("AgfStagiaireDetailCertificate"), 0, 'user');

		print '<table class="border" width="100%">';

		print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
		print '<td>'.$form->showrefnav($agf,'id	','',1,'rowid','id').'</td></tr>';

		if (!empty($agf->fk_socpeople))
		{
			print '<tr><td>'.$langs->trans("Lastname").'</td>';
			print '<td><a href="'.dol_buildpath('/contact/fiche.php',1).'?id='.$agf->fk_socpeople.'">'.strtoupper($agf->nom).'</a></td></tr>';
		}
		else
		{
			print '<tr><td>'.$langs->trans("Lastname").'</td>';
			print '<td>'.strtoupper($agf->nom).'</td></tr>';
		}

		print '<tr><td>'.$langs->trans("Firstname").'</td>';
		print '<td>'.ucfirst($agf->prenom).'</td></tr>';

		print '<tr><td>'.$langs->trans("AgfCivilite").'</td>';

		$contact_static= new Contact($db);
		$contact_static->civilite_id = $agf->civilite;

		print '<td>'.$contact_static->getCivilityLabel().'</td></tr>';

		print '<tr><td valign="top">'.$langs->trans("Company").'</td><td>';
		if ($agf->socid)
		{
			print '<a href="'.dol_buildpath('/comm/fiche.php',1).'?socid='.$agf->socid.'">';
			print img_object($langs->trans("ShowCompany"),"company").' '.dol_trunc($agf->socname,20).'</a>';
		}
		else
		{
			print '&nbsp;';
		}
		print '</td></tr>';

		print '<tr><td>'.$langs->trans("AgfFonction").'</td>';
		print '<td>'.$agf->fonction.'</td></tr>';

		print '<tr><td>'.$langs->trans("Phone").'</td>';
		print '<td>'.dol_print_phone($agf->tel1).'</td></tr>';

		print '<tr><td>'.$langs->trans("Mobile").'</td>';
		print '<td>'.dol_print_phone($agf->tel2).'</td></tr>';

		print '<tr><td>'.$langs->trans("Mail").'</td>';
		print '<td>'.dol_print_email($agf->mail, $agf->id, $agf->socid,'AC_EMAIL',25).'</td></tr>';

		print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
		if (!empty($agf->note)) $notes = nl2br($agf->note);
		else $notes =  $langs->trans("AgfUndefinedNote");
		print '<td>'.stripslashes($notes).'</td></tr>';

		print "</table>";
		print '</div>';
			
		print_fiche_titre($langs->trans("AgfCertificate"));
			
		if (count($agf_certif->lines)>0) {
			print '<table class="noborder"  width="100%">';
			print '<tr class="liste_titre">';
			print '<th class="liste_titre" width="10%">'.$langs->trans('AgfMenuSess').'</th>';
			print '<th class="liste_titre" width="10%">'.$langs->trans('AgfIntitule').'</th>';
			print '<th class="liste_titre" width="20%">'.$langs->trans('AgfDebutSession').'</th>';
			print '<th class="liste_titre">'.$langs->trans('AgfCertifCode').'</th>';
			print '<th class="liste_titre">'.$langs->trans('AgfCertifLabel').'</th>';
			print '<th class="liste_titre">'.$langs->trans('AgfCertifDateSt').'</th>';
			print '<th class="liste_titre">'.$langs->trans('AgfCertifDateEnd').'</th>';
			print '</tr>';

			$style='impair';
			foreach($agf_certif->lines as $line){
				if ($style=='pair') {
					$style='impair';
				}
				else {$style='pair';
				}

				print '<tr class="'.$style.'">';
				$agf_session = new Agsession($db);
				$agf_session->fetch($line->fk_session_agefodd);
				print '<td><a href="'.dol_buildpath('/agefodd/session/subscribers_certif.php',1).'?id='.$line->fk_session_agefodd.'">'.$line->fk_session_agefodd.'</a></td>';
				print '<td><a href="'.dol_buildpath('/agefodd/session/subscribers_certif.php',1).'?id='.$line->fk_session_agefodd.'">'.$agf_session->formintitule.'</a></td>';
				print '<td>'.dol_print_date($agf_session->dated,'daytext').'</td>';
				print '<td>'.$line->certif_code.'</td>';
				print '<td>'.$line->certif_label.'</td>';
				print '<td>'.dol_print_date($line->certif_dt_start,'daytext').'</td>';
				print '<td>'.dol_print_date($line->certif_dt_end,'daytext').'</td>';
				print '</tr>';
			}
			print '</table>';
		}
		else {
			$langs->trans('AgfNoCertif');
		}
	}
}


llxFooter();
$db->close();