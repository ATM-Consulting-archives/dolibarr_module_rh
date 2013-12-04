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
 *	\file       agefodd/session/convention.php
 *	\ingroup    agefodd
 *	\brief      Manage convention template
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../lib/agefodd.lib.php');
require_once('../class/agsession.class.php');
require_once('../class/agefodd_session_calendrier.class.php');
require_once('../class/agefodd_formation_catalogue.class.php');
require_once('../class/agefodd_facture.class.php');
require_once('../class/agefodd_convention.class.php');
require_once('../class/agefodd_contact.class.php');
require_once('../class/agefodd_place.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php');
require_once('../class/agefodd_session_stagiaire.class.php');
require_once('../core/modules/agefodd/modules_agefodd.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();


$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$socid=GETPOST('socid','int');
$sessid=GETPOST('sessid','int');
$arch=GETPOST('arch','int');

$langs->load("companies");

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{

	$agf = new Agefodd_convention($db);
	$result = $agf->remove($id);

	if ($result > 0)
	{
		Header ( 'Location: document.php?id='.$sessid);
		exit;
	}
	else
	{
		setEventMessage($agf->error,'errors');
	}
}


/*
 * Actions archive/active (convention de formation)
*/
if ($action == 'arch_confirm_delete' && $user->rights->agefodd->creer)
{
	if ($_POST["confirm"] == "yes")
	{
		$agf = new Agefodd_convention($db);

		$result = $agf->fetch(0,0,$id);

		$agf->archive = $arch;
		$result = $agf->update($user);

		if ($result > 0)
		{
			Header ( 'Location: '.$_SERVER['PHP_SELF'].'?sessid='.$sessid.'&socid='.$agf->socid);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}

	}
	else
	{
		Header ('Location: '.$_SERVER['PHP_SELF'].'?sessid='.$sessid);
		exit;
	}
}

/*
 * Action generate fiche pédagogique
*/
if ($action == 'builddoc' && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_convention($db);
	
	$result = $agf->fetch(0,0,$id);
	
	// Define output language
	$outputlangs = $langs;
	$newlang=GETPOST('lang_id','alpha');
	if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang=$object->client->default_lang;
	if (! empty($newlang))
	{
		$outputlangs = new Translate("",$conf);
		$outputlangs->setDefaultLang($newlang);
	}
	$model='convention';
	$file = $model.'_'.$agf->sessid.'_'.$agf->socid.'.pdf';
	
	$result = agf_pdf_create($db, $agf->sessid, '', $model, $outputlangs, $file, $agf->socid);

	if ($result > 0)
	{
		Header ( "Location: ".dol_buildpath('/agefodd/session/document.php',1)."?id=".$agf->sessid.'&socid='.$agf->socid);
		exit;
	}
	else
	{
		setEventMessage($agf->error,'errors');
	}
}




/*
 * Action update (convention de formation)
*/
if ($action == 'update' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_convention($db);

		$result = $agf->fetch(0,0,$id);

		$intro1 = GETPOST('intro1','alpha');
		$intro2 = GETPOST('intro2','alpha');
		$art1 = GETPOST('art1','alpha');
		$art2 = GETPOST('art2','alpha');
		$art3 = GETPOST('art3','alpha');
		$art4 = GETPOST('art4','alpha');
		$art5 = GETPOST('art5','alpha');
		$art6 = GETPOST('art6','alpha');
		$art7 = GETPOST('art7','alpha');
		$art8 = GETPOST('art8','alpha');
		$sig = GETPOST('sig','alpha');
		$notes = GETPOST('notes','alpha');


		if (!empty($intro1)) $agf->intro1 = $intro1;
		if (!empty($intro2)) $agf->intro2 = $intro2;
		if (!empty($art1)) $agf->art1 = $art1;
		if (!empty($art2)) $agf->art2 = $art2;
		if (!empty($art3)) $agf->art3 = $art3;
		if (!empty($art4)) $agf->art4 = $art4;
		if (!empty($art5)) $agf->art5 = $art5;
		if (!empty($art6)) $agf->art6 = $art6;
		if (!empty($art7)) $agf->art7 = $art7;
		if (!empty($art8)) $agf->art8 = $art8;
		if (!empty($sig)) $agf->sig = $sig;
		$agf->notes = $notes;
		$agf->socid = $socid;
		$agf->sessid = $sessid;

		$result = $agf->update($user);

		if ($result > 0)
		{
			Header ( 'Location: '.$_SERVER['PHP_SELF'].'?id='.$id);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}

	}
	else
	{
		Header ( 'Location: '.$_SERVER['PHP_SELF'].'?id='.$id);
		exit;
	}
}


/*
 * Action create (training contract)
*/

if ($action == 'create_confirm' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_convention($db);

		$intro1 = GETPOST('intro1','alpha');
		$intro2 = GETPOST('intro2','alpha');
		$art1 = GETPOST('art1','alpha');
		$art2 = GETPOST('art2','alpha');
		$art3 = GETPOST('art3','alpha');
		$art4 = GETPOST('art4','alpha');
		$art5 = GETPOST('art5','alpha');
		$art6 = GETPOST('art6','alpha');
		$art7 = GETPOST('art7','alpha');
		$art8 = GETPOST('art8','alpha');
		$sig = GETPOST('sig','alpha');
		$notes = GETPOST('notes','alpha');


		if (!empty($intro1)) $agf->intro1 = $intro1;
		if (!empty($intro2)) $agf->intro2 = $intro2;
		if (!empty($art1)) $agf->art1 = $art1;
		if (!empty($art2)) $agf->art2 = $art2;
		if (!empty($art3)) $agf->art3 = $art3;
		if (!empty($art4)) $agf->art4 = $art4;
		if (!empty($art5)) $agf->art5 = $art5;
		if (!empty($art6)) $agf->art6 = $art6;
		if (!empty($art7)) $agf->art7 = $art7;
		if (!empty($art8)) $agf->art8 = $art8;
		if (!empty($sig)) $agf->sig = $sig;
		if (!empty($notes)) $agf->notes = $notes;
		$agf->socid = $socid;
		$agf->sessid = $sessid;

		$result = $agf->create($user);

		if ($result > 0)
		{
			Header ( 'Location: '.$_SERVER['PHP_SELF'].'?id='.$result);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}

	}
	else
	{
		Header ( 'Location: '.$_SERVER['PHP_SELF'].'?sessid='.$sessid);
		exit;
	}
}

if ((empty($id)) && (empty($socid)) && (empty($action)))
{
	Header ( 'Location: '.$_SERVER['PHP_SELF'].'?sessid='.$sessid.'&action=create');
	exit;
}


/*
 * View
*/

llxHeader('',$langs->trans("AgfConvention"));

$form = new Form($db);

/*
 * Affichage de la fiche convention en mode création
*/
if ($action == 'create' && $user->rights->agefodd->creer)
{

	$agf = new Agsession($db);
	$resql = $agf->fetch($sessid);

	//We try to find is a convetion have already been done for this customers
	//If yes we retrieve the old value
	//else we use default
	$agf_last = new Agefodd_convention($db);
	$result = $agf_last->fetch_last_conv_per_socity($socid);
	if ($result > 0)
	{
		$agf_conv = new Agefodd_convention($db);
		$result = $agf_conv->fetch($agf_last->sessid, $socid);
		if($agf_last->sessid) $last_conv = 'ok';
	}

	//intro1
	$statut = getFormeJuridiqueLabel($mysoc->forme_juridique_code);
	$intro1 = $langs->trans('AgfConvIntro1_1').' '.$mysoc->name .', '.$statut.' '.$langs->trans('AgfConvIntro1_2').' ';
	if (!empty($mysoc->capital)) {
		$capital_text=' '.$mysoc->capital.' '.$langs->trans("Currency".$conf->currency);
	} else {
		$capital_text='';
	}
	$intro1.= $capital_text.' '.$langs->trans('AgfConvIntro1_3').' '.$mysoc->town;
	$intro1.= ' ('.$mysoc->zip.') ';
	if (!empty($mysoc->idprof4)) {
		$intro1.= $langs->trans('AgfConvIntro1_4').' '.$mysoc->idprof4;
	}
	if (empty ($conf->global->AGF_ORGANISME_NUM)) {
		$intro1.= ' '.$langs->trans('AgfConvIntro1_5').' '.$conf->global->AGF_ORGANISME_PREF;
	}
	else{
		$intro1.= $langs->trans('AgfConvIntro1_6');
		$intro1.= $conf->global->AGF_ORGANISME_PREF.' '.$langs->trans('AgfConvIntro1_7').' '.$conf->global->AGF_ORGANISME_NUM;
	}
	if (!empty($conf->global->AGF_ORGANISME_REPRESENTANT)) {
		$intro1.= $langs->trans('AgfConvIntro1_8').' '.$conf->global->AGF_ORGANISME_REPRESENTANT.$langs->trans('AgfConvIntro1_9');
	}


	//intro2
	// Get trhidparty info
	$agf_soc = new Societe($db);
	$result = $agf_soc->fetch($socid);

	// if agefodd contact exist
	$agf_contact = new Agefodd_contact($db);
	$resql2 = $agf_contact->fetch($socid,'socid');

	// intro2
	$intro2 = $langs->trans('AgfConvIntro2_1').' '.$agf_soc->name.$langs->trans('AgfConvIntro2_2').' '.$agf_soc->address." ".$agf_soc->zip." ".$agf_soc->town.",";
	$intro2.= ' '.$langs->trans('AgfConvIntro2_3').' '. $agf_soc->idprof2.", ";
	$intro2.= ' '.$langs->trans('AgfConvIntro2_4').' ';
	$intro2.= ucfirst(strtolower($agf_contact->civilite)).' '.$agf_contact->firstname.' '.$agf_contact->lastname;
	$intro2.= ' '.$langs->trans('AgfConvIntro2_5');

	//article 1
	// Mise en page (Cf. fonction "liste_a_puce()" du fichier pdf_convention_modele.php)
	// Si la ligne commence par:
	// '!# ' aucune puce ne sera générée, la ligne commence sur la magre gauche
	// '# ', une puce de premier niveau est mis en place
	// '## ', une puce de second niveau est mis en place
	// '### ', une puce de troisième niveau est mis en place
	$art1 = $langs->trans('AgfConvArt1_1')."\n";
	$art1.= $langs->trans('AgfConvArt1_2').' '.$agf->formintitule.' '.$langs->trans('AgfConvArt1_3')." \n";
	$art1.= $langs->trans('AgfConvArt1_4')."\n";

	$obj_peda = new Agefodd($db);
	$resql = $obj_peda->fetch_objpeda_per_formation($agf->formid);
	foreach($obj_peda->lines as $line)
	{
		$art1.= "##	".$line->intitule."\n";
	}
	$art1.= $langs->trans('AgfConvArt1_5')."\n";
	$art1.= $langs->trans('AgfConvArt1_6')."\n";
	$art1.= $langs->trans('AgfConvArt1_7');

	if ($agf->dated == $agf->datef) $art1.= $langs->trans('AgfConvArt1_8').' '.dol_print_date($agf->datef);
	else $art1.= $langs->trans('AgfConvArt1_9').' '.dol_print_date($agf->dated).' '.$langs->trans('AgfConvArt1_10').' '.dol_print_date($agf->datef);

	$art1.= "\n";

	// Durée de formation
	$art1.= $langs->trans('AgfConvArt1_11').' '.$agf->duree.' '.$langs->trans('AgfConvArt1_12').' '."\n";

	$calendrier = new Agefodd_sesscalendar($db);
	$resql = $calendrier->fetch_all($sessid);
	$blocNumber = count($calendrier->lines);
	$old_date = 0;
	$duree = 0;
	for ($i = 0; $i < $blocNumber; $i++)
	{
		if ($calendrier->lines[$i]->date_session != $old_date)
		{
			if ($i > 0 ) $art1.= "), ";
			$art1.= dol_print_date($calendrier->lines[$i]->date_session,'daytext').' (';
		}
		else $art1.= '/';
		$art1.= dol_print_date($calendrier->lines[$i]->heured,'hour');
		$art1.= ' - ';
		$art1.= dol_print_date($calendrier->lines[$i]->heuref,'hour');
		if ($i == $blocNumber - 1) $art1.=').'."\n";

		$old_date = $calendrier->lines[$i]->date_session;
	}

	$art1.= $langs->trans('AgfConvArt1_13')."\n";

	$stagiaires = new Agefodd_session_stagiaire($db);
	$nbstag = $stagiaires->fetch_stagiaire_per_session($sessid,$socid);
	$art1.= $langs->trans('AgfConvArt1_14').' '.$nbstag.' '.$langs->trans('AgfConvArt1_15');
	if ($nbstag > 1) $art1.= $langs->trans('AgfConvArt1_16');
	$art1.= $langs->trans('AgfConvArt1_17')."\n";
	// Adresse lieu de formation
	$agf_place = new Agefodd_place($db);
	$resql3 = $agf_place->fetch($agf->placeid);
	$adresse = $agf_place->adresse.", ".$agf_place->cp." ".$agf_place->ville;
	$art1.= $langs->trans('AgfConvArt1_18').$agf_place->ref_interne.$langs->trans('AgfConvArt1_19').' '.$adresse.'.';

	// texte 2
	if ($agf_conv->art2) $art2 = $agf_conv->art2;
	else
	{
		$art2 = $langs->trans('AgfConvArt2_1');
	}

	// texte3
	$art3 = $langs->trans('AgfConvArt3_1');
	($nbstag > 1) ? $art3.=$langs->trans('AgfConvArt3_2').' ' : $art3.=' '.$langs->trans('AgfConvArt3_3').' ';

	for ($i= 0; $i < $nbstag; $i++)
	{
		$art3.= $stagiaires->lines[$i]->nom.' '.$stagiaires->lines[$i]->prenom;
		if (!empty($stagiaires->lines[$i]->poste)) {
			$art3.= ' ('.$stagiaires->lines[$i]->poste.')';
		}
		if ($i == $nbstag - 1) $art3.= '.';
		else
		{
			if ($i == $nbstag - 2) $art3.= ' '.$langs->trans('AgfConvArt3_4').' ';
			else  $art3.= ', ';
		}
	}

	// texte 4
	if ($conf->global->FACTURE_TVAOPTION=="franchise") {
		$art4 = $langs->trans('AgfConvArt4_1');
	}
	else {
		$art4 = $langs->trans('AgfConvArt4_3');
	}
	$art4.="\n".$langs->trans('AgfConvArt4_2');

	// texte 5
	if ($agf_conv->art5) $art5 = $agf_conv->art5;
	else
	{
		$art5 = $langs->trans('AgfConvArt5_1');
	}

	//article 6
	if ($agf_conv->art6) {
		$art6 = $agf_conv->art6;
	}
	else {
		$art6 = $langs->trans('AgfConvArt6_1')."\n";
		$art6.= $langs->trans('AgfConvArt6_2')."\n";
		$art6.=	$langs->trans('AgfConvArt6_3')."\n";
		$art6.= $langs->trans('AgfConvArt6_4')."\n";
	}

	//article 7
	if ($agf_conv->art7) $art7 = $agf_conv->art7;
	else
	{
		$art7 = $langs->trans('AgfConvArt7_1');
		$art7 .= $langs->trans('AgfConvArt7_2').' '.$mysoc->town.".";
	}

	// Signature du client
	if ($agf_conv->sig) $sig = $agf_conv->sig;
	else
	{
		$sig = $agf_soc->nom."\n";
		$sig.= $langs->trans('AgfConvArtSig').' ';
		$sig.= ucfirst(strtolower($agf_contact->civilite)).' '.$agf_contact->firstname.' '.$agf_contact->lastname." (*)";
	}

	print_fiche_titre($langs->trans("AgfNewConv"));

	print '<div class="warning">';
	($last_conv == 'ok') ? print $langs->trans("AgfConvLastWarning") : print $langs->trans("AgfConvDefaultWarning");
	print '</div>'."\n";
	print '<form name="create" action="convention.php" method="post">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="create_confirm">'."\n";
	print '<input type="hidden" name="sessid" value="'.$sessid.'">'."\n";
	print '<input type="hidden" name="socid" value="'.$socid.'">'."\n";

	print '<table class="border" width="100%">'."\n";

	print '<tr><td valign="top" width="200px">'.$langs->trans("Company").'</td>';
	print '<td>'.$agf_soc->nom.'</td></tr>';

	print '<tr><td valign="top" width="200px">'.$langs->trans("AgfConventionIntro1").'</td>';
	print '<td><textarea name="intro1" rows="3" cols="0" class="flat" style="width:360px;">'.$intro1.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionIntro2").'</td>';
	print '<td><textarea name="intro2" rows="3" cols="0" class="flat" style="width:360px;">'.$intro2.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt1").'</td>';
	print '<td><textarea name="art1" rows="3" cols="0" class="flat" style="width:360px;">'.$art1.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt2").'</td>';
	print '<td><textarea name="art2" rows="3" cols="0" class="flat" style="width:360px;">'.$art2.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt3").'</td>';
	print '<td><textarea name="art3" rows="3" cols="0" class="flat" style="width:360px;">'.$art3.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt4").'</td>';
	print '<td><textarea name="art4" rows="3" cols="0" class="flat" style="width:360px;">'.$art4.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt5").'</td>';
	print '<td><textarea name="art5" rows="3" cols="0" class="flat" style="width:360px;">'.$art5.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt6").'</td>';
	print '<td><textarea name="art6" rows="3" cols="0" class="flat" style="width:360px;">'.$art6.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionArt7").'</td>';
	print '<td><textarea name="art7" rows="3" cols="0" class="flat" style="width:360px;">'.$art7.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfConventionSig").'</td>';
	print '<td><textarea name="sig" rows="3" cols="0" class="flat" style="width:360px;">'.$sig.'</textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfNote").'<br /><span style=" font-size:smaller; font-style:italic;">'.$langs->trans("AgfConvNotesExplic").'</span></td>';
	print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';
	print '</table>';
	print '</div>';


	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';
	print '</table>';
	print '</form>';

}
else
{
	// Affichage de la fiche convention
	$agf = new Agefodd_convention($db);
	if (!empty($id))	$result = $agf->fetch(0, 0, $id);

	if ($result)
	{
		$agf_session = new Agsession($db);
		$agf_session->fetch($agf->sessid);

		$head = session_prepare_head($agf_session,1);

		$hselected='convention';

		dol_fiche_head($head, $hselected, $langs->trans("AgfConvention"), 0, 'bill');

		// Affichage en mode "édition"
		if ($action == 'edit')
		{
			print '<form name="update" action="convention.php" method="post">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="update">'."\n";
			print '<input type="hidden" name="id" value="'.$id.'">'."\n";
			print '<input type="hidden" name="socid" value="'.$agf->socid.'">'."\n";
			print '<input type="hidden" name="sessid" value="'.$agf->sessid.'">'."\n";

			print '<table class="border" width="100%">'."\n";

			print '<tr><td valign="top" width="200px">'.$langs->trans("Company").'</td>';
			print '<td>'.$agf->socname.'</td></tr>';


			print '<tr><td valign="top" width="200px">'.$langs->trans("AgfConventionIntro1").'</td>';
			print '<td><textarea name="intro1" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->intro1.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionIntro2").'</td>';
			print '<td><textarea name="intro2" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->intro2.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt1").'</td>';
			print '<td><textarea name="art1" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art1.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt2").'</td>';
			print '<td><textarea name="art2" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art2.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt3").'</td>';
			print '<td><textarea name="art3" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art3.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt4").'</td>';
			print '<td><textarea name="art4" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art4.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt5").'</td>';
			print '<td><textarea name="art5" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art5.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt6").'</td>';
			print '<td><textarea name="art6" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art6.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt7").'</td>';
			print '<td><textarea name="art7" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->art7.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionSig").'</td>';
			print '<td><textarea name="sig" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->sig.'</textarea></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfNote").'<br /><span style=" font-size:smaller; font-style:italic;">'.$langs->trans("AgfConvNotesExplic").'</span></td>';
			print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->notes.'</textarea></td></tr>';

			print '</table>';
			print '</div>';
			print '<table style=noborder align="right">';
			print '<tr><td align="center" colspan=2>';
			print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
			print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
			print '</td></tr>';
			print '</table>';
			print '</form>';

			print '</div>'."\n";
		}
		else
		{

			/*
			 * Confirmation de la suppression
			*/
			if ($action == 'delete')
			{
				$ret=$form->form_confirm("convention.php?id=".$id.'&sessid='.$agf->sessid,$langs->trans("AgfDeleteConvention"),$langs->trans("AgfConfirmDeleteConvention"),"confirm_delete",'','',1);
				if ($ret == 'html') print '<br>';
			}
			/*
			 * Confirmation de l'archivage/activation suppression
			*/
			if (isset($_GET["arch"]))
			{
				$ret=$form->form_confirm("convention.php?arch=".$_GET["arch"]."&id=".$id,$langs->trans("AgfFormationArchiveChange"),$langs->trans("AgfConfirmArchiveChange"),"arch_confirm_delete",'','',1);
				if ($ret == 'html') print '<br>';
			}

			//Create a list of customer for each convention
			//$agf_sess= new Agsession($db);
			//$result_sess_soc = $agf_sess->fetch_societe_per_session($sessid);
			//	$result = $agf->fetch($sessid, $agf_sess->line[0]->socid, 0);

			print '<table class="border" width="100%">'."\n";

			print '<tr><td valign="top" width="200px">'.$langs->trans("Company").'</td>';
			print '<td>';
			print $agf->socname;

			/*if ($result_sess_soc >= 1)
			 {
			print '<form name="update" action="convention_fiche.php?id='.$id.'" method="GET">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="id" value="'.$id.'">'."\n";
			print '<input type="hidden" name="sessid" value="'.$sessid.'">'."\n";
			print '<select name="socid">';
			foreach ($agf_sess->line as $line)
			{
			print '<option value="'.$line->socid.'">'.$line->socname.'</option>';
			}
			print '</select>';
			print '<input type="button" value="voir"/>';
			print '</form>';
			}*/
			print '</td></tr>';


			print '<tr><td valign="top" width="200px">'.$langs->trans("AgfConventionIntro1").'</td>';
			print '<td>'.nl2br($agf->intro1).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionIntro2").'</td>';
			print '<td>'.nl2br($agf->intro2).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt1").'</td>';
			print '<td>'.ebi_liste_a_puce($agf->art1, true).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt2").'</td>';
			print '<td>'.nl2br($agf->art2).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt3").'</td>';
			print '<td>'.nl2br($agf->art3).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt4").'</td>';
			print '<td>'.nl2br($agf->art4).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt5").'</td>';
			print '<td>'.nl2br($agf->art5).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt6").'</td>';
			print '<td>'.nl2br($agf->art6).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionArt7").'</td>';
			print '<td>'.nl2br($agf->art7).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfConventionSig").'</td>';
			print '<td>'.nl2br($agf->sig).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfNote").'<br /><span style=" font-size:smaller; font-style:italic;">'.$langs->trans("AgfConvNotesExplic").'</span></td>';
			print '<td valign="top">'.nl2br($agf->notes).'</td></tr>';

			print '</table>';
			print '</div>';

		}

	}
}


/*
 * Action tabs
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'nfcontact')
{
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="convention.php?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
	}
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butActionDelete" href="convention.php?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
	}
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=builddoc&id='.$id.'">'.$langs->trans('AgfDocCreate').' '.$langs->trans('AgfConvention').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfDocCreate').' '.$langs->trans('AgfConvention').'</a>';
	}
}

print '</div>';

llxFooter();
$db->close();