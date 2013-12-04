<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
* Copyright (C) 2012-2013	Florian Henry	<florian.henry@open-concept.pro>
* Copyright (C) 2012		JF FERRY	<jfefe@aternatik.fr>
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
 *	\file       agefodd/session/card.php
 *	\ingroup    agefodd
 *	\brief      card of session
*/


$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agsession.class.php');
require_once('../class/agefodd_sessadm.class.php');
require_once('../class/agefodd_session_admlevel.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../class/agefodd_session_calendrier.class.php');
require_once('../class/agefodd_calendrier.class.php');
require_once('../class/agefodd_session_formateur.class.php');
require_once('../class/agefodd_session_stagiaire.class.php');
require_once('../class/agefodd_facture.class.php');
require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php');
require_once('../class/agefodd_formation_catalogue.class.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();


$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$arch=GETPOST('arch','int');

$agf = new Agsession($db);
$extrafields = new ExtraFields($db);
$extralabels=$extrafields->fetch_name_optionals_label($agf->table_element);

/*
 * Actions delete session
*/

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$agf = new Agsession($db);
	$result = $agf->remove($id);

	if ($result > 0)
	{
		Header ( "Location: list.php");
		exit;
	}
	else
	{
		setEventMessage($langs->trans("AgfDeleteErr").':'.$agf->error,'errors');
	}
}


/*
 * Actions delete period
*/

if ($action == 'confirm_delete_period' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$modperiod=GETPOST('modperiod','int');

	$agf = new Agefodd_sesscalendar($db);
	$result = $agf->remove($modperiod);

	if ($result > 0)
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
		exit;
	}
	else
	{
		setEventMessage($agf->error,'errors');
	}
}

/*
 * Actions archive/active
*/

if ($action == 'arch_confirm_delete' && $user->rights->agefodd->creer)
{
	if ($confirm == "yes")
	{
		$agf = new Agsession($db);

		$result = $agf->fetch($id);
		$agf->archive = $_GET["arch"];
		$result = $agf->updateArchive($user);

		if ($result > 0)
		{
			// If update are OK we delete related files
			foreach (glob($conf->agefodd->dir_output."/*_".$id."_*.pdf") as $filename) {
				if(is_file($filename)) unlink("$filename");
			}

			Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}

	}
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}


/*
 * Action update (fiche session)
*/
if ($action == 'update' && $user->rights->agefodd->creer && ! $_POST["stag_update_x"] && ! $_POST["period_update_x"])
{
	if (! $_POST["cancel"])
	{
		$error=0;

		$agf = new Agsession($db);

		$fk_session_place = GETPOST('place','int');
		if (($fk_session_place==-1) || (empty($fk_session_place)))
		{
			setEventMessage($langs->trans('AgfPlaceMandatory'),'errors');
			$error++;
		}

		$result = $agf->fetch($id);
		
		if ($agf->fk_formation_catalogue != GETPOST('formation','int')) {
			$training_session = new Agefodd ( $db );
			$result = $training_session->fetch ( GETPOST('formation','int') );
			if ($result > 0 ) {
				$agf->nb_subscribe_min = $training_session->nb_subscribe_min;
				$agf->duree_session = $training_session->duree;
				$agf->intitule_custo = $training_session->intitule;
				$agf->fk_product = $training_session->fk_product;
			}
		}else {
			$agf->nb_subscribe_min=GETPOST('nbmintarget','int');
			$agf->fk_product=GETPOST('productid','int');
			$agf->duree_session = GETPOST('duree_session','int');
			$agf->intitule_custo = GETPOST('intitule_custo','alpha');
		}
		
		$agf->fk_formation_catalogue = GETPOST('formation','int');

		$agf->dated = dol_mktime(0,0,0,GETPOST('dadmonth','int'),GETPOST('dadday','int'),GETPOST('dadyear','int'));
		$agf->datef = dol_mktime(0,0,0,GETPOST('dafmonth','int'),GETPOST('dafday','int'),GETPOST('dafyear','int'));
		$agf->fk_session_place = $fk_session_place;
		$agf->type_session = GETPOST('type_session','int');
		$agf->commercialid = GETPOST('commercial','int');
		$agf->contactid = GETPOST('contact','int');
		
		if ($conf->global->AGF_CONTACT_DOL_SESSION)	{
			$agf->sourcecontactid = $agf->contactid;
		}
		$agf->notes = GETPOST('notes','alpha');
		$agf->status = GETPOST('session_status','int');

		$agf->cost_trainer = GETPOST('costtrainer','alpha');
		$agf->cost_site = GETPOST('costsite','alpha');
		$agf->sell_price = GETPOST('sellprice','alpha');

		$agf->date_res_site = dol_mktime(0,0,0,GETPOST('res_sitemonth','int'),GETPOST('res_siteday','int'),GETPOST('res_siteyear','int'));
		$agf->date_res_trainer = dol_mktime(0,0,0,GETPOST('res_trainmonth','int'),GETPOST('res_trainday','int'),GETPOST('res_trainyear','int'));

		if ($agf->date_res_site=='') {
			$isdateressite=0;
		} else {$isdateressite=GETPOST('isdateressite','alpha');
		}
		if ($agf->date_res_trainer=='')	{
			$isdaterestrainer=0;
		} else {$isdaterestrainer=GETPOST('isdaterestrainer','alpha');
		}

		if ($isdateressite==1 && $agf->date_res_site!='') {
			$agf->is_date_res_site = 1;
		}
		else {	$agf->is_date_res_site = 0;	$agf->date_res_site='';
		}

		if ($isdaterestrainer==1 && $agf->date_res_trainer!='') {
			$agf->is_date_res_trainer = 1;
		}
		else {	$agf->is_date_res_trainer = 0; $agf->date_res_trainer='';
		}

		$fk_soc				= GETPOST('fk_soc','int');
		$color				= GETPOST('color','alpha');
		$nb_place			= GETPOST('nb_place','int');
		$nb_stagiaire		= GETPOST('nb_stagiaire','int');
		$force_nb_stagiaire	= GETPOST('force_nb_stagiaire','int');

		if ($force_nb_stagiaire==1 && $agf->force_nb_stagiaire!='') {
			$agf->force_nb_stagiaire = 1;
		}
		else {
			$agf->force_nb_stagiaire = 0;
		}

		$cost_trip = GETPOST('costtrip','alpha');

		if(!empty($fk_soc)) 			$agf->fk_soc =  $fk_soc;
		if(!empty($color))				$agf->color =  $color;
		if(!empty($nb_place)) 			$agf->nb_place = $nb_place;
		if(!empty($nb_stagiaire))		$agf->nb_stagiaire = $nb_stagiaire;
		if(!empty($force_nb_stagiaire))	$agf->force_nb_stagiaire = $force_nb_stagiaire;
		if(!empty($cost_trip)) 			$agf->cost_trip = $cost_trip;

		if ($error==0)
		{
			$extrafields->setOptionalsFromPost($extralabels,$agf);
			
			$result = $agf->update($user);
			if ($result > 0)
			{
				if ($_POST['saveandclose']!='') {
					Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
				}
				else
				{
					setEventMessage($langs->trans('Save'),'mesgs');
					Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
				}
				exit;
			}
			else
			{
				setEventMessage($agf->error,'errors');
			}
		}
		else
		{
			if ($_POST['saveandclose']!='') {
				$action='';
			}
			else
			{
				$action='edit';
			}
		}
	}
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}


/*
 * Action update
* - Calendar update
* - trainer update
*/
if ($action == 'edit' && $user->rights->agefodd->creer)
{
	if($_POST["period_update_x"])
	{

		$modperiod=GETPOST('modperiod','int');
		$date_session = dol_mktime(0,0,0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));

		$heure_tmp_arr = array();

		$heured_tmp = GETPOST('dated','alpha');
		if (!empty($heured_tmp)){
			$heure_tmp_arr = explode(':',$heured_tmp);
			$heured = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
		}

		$heuref_tmp = GETPOST('datef','alpha');
		if (!empty($heuref_tmp)){
			$heure_tmp_arr = explode(':',$heuref_tmp);
			$heuref = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
		}

		$agf = new Agefodd_sesscalendar($db);
		$result = $agf->fetch($modperiod);

		if(!empty($modperiod)) 			$agf->id = $modperiod;
		if(!empty($date_session)) 		$agf->date_session = $date_session;
		if(!empty($heured)) 			$agf->heured = $heured;
		if(!empty($heuref)) 			$agf->heuref =  $heuref;


		$result = $agf->update($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}

	if($_POST["period_add_x"])
	{
		$error=0;
		$error_message='';
	
		//From template
		$idtemplate_array=GETPOST('fromtemplate');
		if (is_array($idtemplate_array)) {
			foreach ($idtemplate_array as $idtemplate) {
				
				$agf = new Agefodd_sesscalendar($db);
				
				$agf->sessid = GETPOST('sessid','int');
				$agf->date_session = dol_mktime(0,0,0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
			
				$tmpl_calendar = new Agefoddcalendrier($db);
				$result=$tmpl_calendar->fetch($idtemplate);
				$tmpldate = dol_mktime(0,0,0,GETPOST('datetmplmonth','int'),GETPOST('datetmplday','int'),GETPOST('datetmplyear','int'));
				if ($tmpl_calendar->day_session!=1) {
					$tmpldate = dol_time_plus_duree($tmpldate, (($tmpl_calendar->day_session)-1), 'd');
				}
					
				$agf->date_session = $tmpldate;
					
				$heure_tmp_arr = explode(':',$tmpl_calendar->heured);
				$agf->heured = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,dol_print_date($agf->date_session, "%m"),dol_print_date($agf->date_session, "%d"),dol_print_date($agf->date_session, "%Y"));
					
				$heure_tmp_arr = explode(':',$tmpl_calendar->heuref);
				$agf->heuref = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,dol_print_date($agf->date_session, "%m"),dol_print_date($agf->date_session, "%d"),dol_print_date($agf->date_session, "%Y"));
				
				$result = $agf->create($user);
				if ($result < 0)
				{
					$error++;
					$error_message .=  $agf->error;
				}
			}
		}else {
			
			$agf = new Agefodd_sesscalendar($db);
			
			$agf->sessid = GETPOST('sessid','int');
			$agf->date_session = dol_mktime(0,0,0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
				
			//From calendar selection
			$heure_tmp_arr = array();

			$heured_tmp = GETPOST('dated','alpha');
			if (!empty($heured_tmp)){
				$heure_tmp_arr = explode(':',$heured_tmp);
				$agf->heured = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
			}

			$heuref_tmp = GETPOST('datef','alpha');
			if (!empty($heuref_tmp)){
				$heure_tmp_arr = explode(':',$heuref_tmp);
				$agf->heuref = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
			}
			
			$result = $agf->create($user);
			if ($result < 0)
			{
				$error++;
				$error_message =  $agf->error;
			}
		}

		

		if (!$error)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($error_message,'errors');
		}
	}

	if($_POST["form_update_x"])
	{
		$agf = new Agefodd_session_formateur($db);

		$agf->opsid = GETPOST('opsid','int');
		$agf->formid = GETPOST('formid','int');

		$result = $agf->update($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}

	if($_POST["form_add_x"])
	{
		$agf = new Agefodd_session_formateur($db);

		$agf->sessid = GETPOST('sessid','int');
		$agf->formid = GETPOST('formid','int');
		$result = $agf->create($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}

}

/*
 * Action create (new training session)
*/

if ($action == 'add_confirm' && $user->rights->agefodd->creer)
{
	$error=0;
	if (! $_POST["cancel"])
	{
		$agf = new Agsession($db);

		$fk_session_place = GETPOST('place','int');
		if (($fk_session_place==-1) || (empty($fk_session_place)))
		{
			$error++;
			setEventMessage($langs->trans('AgfPlaceMandatory'),'errors');
		}
		
		$training_id = GETPOST('formation','int');
		if (($training_id==-1) || (empty($training_id)))
		{
			$error++;
			setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("AgfFormIntitule")), 'errors');
		}

		$agf->fk_formation_catalogue = $training_id;
		$agf->fk_session_place = $fk_session_place;
		$agf->nb_place = GETPOST('nb_place','int');
		$agf->type_session = GETPOST('type_session','int');
		$agf->nb_place = GETPOST('nb_place','int');
		$agf->status = GETPOST('session_status','int');

		$agf->fk_soc = GETPOST('fk_soc','int');
		$agf->dated = dol_mktime(0,0,0,GETPOST('dadmonth','int'),GETPOST('dadday','int'),GETPOST('dadyear','int'));
		$agf->datef = dol_mktime(0,0,0,GETPOST('dafmonth','int'),GETPOST('dafday','int'),GETPOST('dafyear','int'));
		$agf->notes = GETPOST('notes','alpha');
		$agf->commercialid = GETPOST('commercial','int');
		$agf->contactid = GETPOST('contact','int');
		
		$agf->duree_session = GETPOST('duree_session','int');
		$agf->intitule_custo = GETPOST('intitule_custo','alpha');

		if ($error==0)
		{
			
			$extrafields->setOptionalsFromPost($extralabels,$agf);
			
			$result = $agf->create($user);

			if ($result > 0)
			{
				// If session creation are ok
				// We create admnistrative task associated
				$result = $agf->createAdmLevelForSession($user);
				if ($result>0) {
					setEventMessage($agf->error,'errors');
					$error++;
				}
			}
			else
			{
				setEventMessage($agf->error,'errors');
				$error++;
			}
		}
		if ($error==0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$agf->id);
			exit;
		}

		else
		{
			$action='create';
		}
	}
	else
	{
		Header ( "Location: list.php");
		exit;
	}
}

// Action clone object
if ($action == 'confirm_clone' && $confirm == 'yes')
{
	if (1==0 &&  ! GETPOST('clone_content'))
	{
		$mesg='<div class="error">'.$langs->trans("NoCloneOptionsSpecified").'</div>';
	}
	else
	{
		$agf = new Agsession($db);
		if ($agf->fetch($id) > 0)
		{
			$result=$agf->createFromClone($id, $hookmanager);
			if ($result > 0)
			{
				if(GETPOST('clone_calendar'))
				{
					// clone calendar information
					$calendrierstat = new Agefodd_sesscalendar($db);
					$calendrier = new Agefodd_sesscalendar($db);
					$calendrier->fetch_all($id);
					$blocNumber = count($calendrier->lines);
					if ($blocNumber > 0)
					{
						$old_date = 0;
						$duree = 0;
						for ($i = 0; $i < $blocNumber; $i++)
						{
							$calendrierstat->sessid = $result;
							$calendrierstat->date_session = $calendrier->lines[$i]->date_session;
							$calendrierstat->heured = $calendrier->lines[$i]->heured;
							$calendrierstat->heuref = $calendrier->lines[$i]->heuref;

							$result1 = $calendrierstat->create($user);
						}
					}
				}
				if(GETPOST('clone_trainee'))
				{
					// Clone trainee information
					$traineestat = new Agefodd_session_stagiaire($db);
					$session_trainee = new Agefodd_session_stagiaire($db);
					$session_trainee->fetch_stagiaire_per_session($id);
					$blocNumber = count($session_trainee->lines);
					if ($blocNumber > 0)
					{
						foreach ($session_trainee->lines as $line)
						{
							$traineestat->fk_session_agefodd=$result;
							$traineestat->fk_stagiaire=$line->id;
							$traineestat->fk_agefodd_stagiaire_type=$line->fk_agefodd_stagiaire_type;
					
							$result1 = $traineestat->create($user);
						}
					}
				}
				
				/*if(GETPOST('clone_trainer') )
				{
					// Clone trainee information
					$traineestat = new Agefodd_session_formateur($db);
					$session_trainee = new Agefodd_session_formateur($db);
					$session_trainee->fetch_formateur_per_session($id);
					$blocNumber = count($session_trainee->lines);
					if ($blocNumber > 0)
					{
						foreach ($session_trainee->lines as $line)
						{
							$traineestat->sessid=$id;
							$traineestat->stagiaire=$line->id;
							$traineestat->stagiaire_type=$line->fk_agefodd_stagiaire_type;
								
							$result1 = $traineestat->create($user);
						}
					}
				}*/
				header("Location: ".$_SERVER['PHP_SELF'].'?id='.$result);
				exit;
			}
			else
			{
				setEventMessage($agf->error,'errors');
				$action='';
			}
		}
	}
}



/*
 * View
*/

llxHeader('',$langs->trans("AgfSessionDetail"),'','','','',array('/agefodd/includes/jquery/plugins/colorpicker/js/colorpicker.js','/agefodd/includes/lib.js'), array('/agefodd/includes/jquery/plugins/colorpicker/css/colorpicker.css','/agefodd/includes/lib.js'));
$form = new Form($db);
$formAgefodd = new FormAgefodd($db);


/*
 * Action create
*/
if ($action == 'create' && $user->rights->agefodd->creer)
{

	$fk_soc_crea = GETPOST('fk_soc','int');

	print_fiche_titre($langs->trans("AgfMenuSessNew"));

	print '<form name="add" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add_confirm">';

	print '<table class="border" width="100%">';


	print '<tr><td><span class="fieldrequired">'.$langs->trans("AgfLieu").'</span></td>';
	print '<td><table class="nobordernopadding"><tr><td>';
	print $formAgefodd->select_site_forma(GETPOST('place','int'),'place',1);
	print '</td>';
	print '<td> <a href="'.dol_buildpath('/agefodd/site/card.php',1).'?action=create&url_return='.urlencode($_SERVER['PHP_SELF'].'?action=create').'" title="'.$langs->trans('AgfCreateNewSite').'">'.$langs->trans('AgfCreateNewSite').'</a>';
	print '</td><td>'.$form->textwithpicto('',$langs->trans("AgfCreateNewSiteHelp"),1,'help').'</td></tr></table>';
	print '</td></tr>';

	print '<tr><td><span class="fieldrequired">'.$langs->trans("AgfFormIntitule").'</span></td>';
	print '<td>'.$formAgefodd->select_formation(GETPOST('formation','int'), 'formation','intitule',1).'</td></tr>';

	print '<tr><td>'.$langs->trans("AgfFormTypeSession").'</td>';
	print '<td>'.$formAgefodd->select_type_session('type_session',0).'</td></tr>';

	print '<tr><td><span class="fieldrequired">'.$langs->trans("AgfDateDebut").'</span></td><td>';
	$form->select_date(dol_mktime(0,0,0,GETPOST('dadmonth','int'),GETPOST('dadday','int'),GETPOST('dadyear','int')), 'dad','','','','add');
	print '</td></tr>';

	print '<tr><td><span class="fieldrequired">'.$langs->trans("AgfDateFin").'</span></td><td>';
	$form->select_date(dol_mktime(0,0,0,GETPOST('dafmonth','int'),GETPOST('dafday','int'),GETPOST('dafyear','int')), 'daf','','','','add');
	print '</td></tr>';

	print '<tr><td>'.$langs->trans("Customer").'</td>';
	print '<td>';
	if ($conf->global->AGF_CONTACT_DOL_SESSION)	{
		$events=array();
		$events[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'contact', 'params' => array('add-customer-contact' => 'disabled'));
		print $form->select_company($fk_soc_crea,'fk_soc','',1,1,0,$events);
	} else {
		print $form->select_company($fk_soc_crea,'fk_soc','',1,1);
	}
	print '</td></tr>';

	if ($conf->global->AGF_CONTACT_DOL_SESSION)	{
		print '<tr><td>'.$langs->trans("AgfSessionContact").'</td>';
		print '<td><table class="nobordernopadding"><tr><td>';
		if (!empty($fk_soc_crea)) {
			$formAgefodd->select_contacts_custom($fk_soc_crea,'','contact',1,'','',1,'',1);
		} else {
			$formAgefodd->select_contacts_custom(0,'','contact',1,'',1000,1,'',1);
		}
		print '</td>';
		print '<td>'.$form->textwithpicto('',$langs->trans("AgfAgefoddDolContactHelp"),1,'help').'</td></tr></table>';
		print '</td></tr>';
	}
	else {
		print '<tr><td>'.$langs->trans("AgfSessionContact").'</td>';
		print '<td><table class="nobordernopadding"><tr><td>';
		print $formAgefodd->select_agefodd_contact(GETPOST('contact','int'), 'contact','',1);
		print '</td>';
		print '<td>'.$form->textwithpicto('',$langs->trans("AgfAgefoddContactHelp"),1,'help').'</td></tr></table>';
		print '</td></tr>';
	}

	print '<tr><td>'.$langs->trans("AgfNumberPlaceAvailable").'</td>';
	print '<td>';
	print '<input type="text" class="flat" name="nb_place" size="4" value="'.GETPOST('nb_place','int').'"/>';
	print '</td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
	print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.GETPOST('notes','aplha').'</textarea></td></tr>';
	
	print '<tr><td valign="top">'.$langs->trans("AgfStatusSession").'</td>';
	print '<td>';
	$defstat=GETPOST('AGF_DEFAULT_SESSION_STATUS');
	if (empty($defstat)) $defstat=$conf->global->AGF_DEFAULT_SESSION_STATUS;
	print $formAgefodd->select_session_status($defstat,"session_status",'t.active=1');
	print '</td></tr>';

	if (! empty($extrafields->attribute_label))
	{
		print $agf->showOptionals($extrafields,'edit');
	}
	
	
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
	// Display session card
	if ($id)
	{
		$agf = new Agsession($db);
		$result = $agf->fetch($id);		
		
		if ($result>0)
		{
			if (!(empty($agf->id)))
			{
				$head = session_prepare_head($agf);

				dol_fiche_head($head, 'card', $langs->trans("AgfSessionDetail"), 0, 'calendarday');

				$agf_fact=new Agefodd_facture($db);
				$agf_fact->fetch_by_session($agf->id);
				$other_amount = '('. $langs->trans('AgfProposalAmountSigned').' '.$agf_fact->propal_sign_amount.' '.$langs->trans('Currency'.$conf->currency);
				$other_amount .= '/'. $langs->trans('AgfOrderAmount').' '.$agf_fact->order_amount.' '.$langs->trans('Currency'.$conf->currency);
				$other_amount .= '/'. $langs->trans('AgfInvoiceAmountWaiting').' '.$agf_fact->invoice_ongoing_amount.' '.$langs->trans('Currency'.$conf->currency);
				$other_amount .= '/'. $langs->trans('AgfInvoiceAmountPayed').' '.$agf_fact->invoice_payed_amount.' '.$langs->trans('Currency'.$conf->currency).')';
								
				/*
				 * 
				 * Display edit mode
				 * 
				 */
				if ($action == 'edit')
				{
						
					$newperiod=GETPOST('newperiod','int');
						
					print '<form name="update" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
					print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
					print '<input type="hidden" name="action" value="update">';
					print '<input type="hidden" name="id" value="'.$id.'">';
					print '<input type="hidden" name="action" value="update">';

					print '<table class="border" width="100%">';
					print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
					print '<td>'.$agf->id.'</td></tr>';

					print '<tr><td>'.$langs->trans("AgfFormIntitule").'</td>';
					print '<td>'.$formAgefodd->select_formation($agf->formid, 'formation');
					print '</td></tr>';
					
					print '<tr><td>' . $langs->trans ( "AgfFormIntituleCust" ) . '</td>';
					print '<td><input size="30" type="text" class="flat" id="intitule_custo" name="intitule_custo" value="'.$agf->intitule_custo.'" /></td></tr>';

					print '<tr><td>'.$langs->trans("AgfFormTypeSession").'</td>';
					print '<td>'.$formAgefodd->select_type_session('type_session',$agf->type_session).'</td></tr>';

					print '<tr><td>'.$langs->trans("AgfFormRef").'</td>';
					print '<td>'.$agf->formref.'</td></tr>';

					print '<tr><td>'.$langs->trans("Color").'</td>';
					print '<td><input id="colorpicker" type="text" size="8" name="color" value="'.$agf->color.'" /></td></tr>';

					print '<script type="text/javascript" language="javascript">
						$(document).ready(function() {
						$("#colorpicker").css("backgroundColor", \'#'.$agf->color.'\');
							$("#colorpicker").ColorPicker({
							color: \'#'.$agf->color.'\',
								onShow: function (colpkr) {
								$(colpkr).fadeIn(500);
								return false;
				},
								onHide: function (colpkr) {
								$(colpkr).fadeOut(500);
								return false;
				},
								onChange: function (hsb, hex, rgb) {
								$("#colorpicker").css("backgroundColor", \'#\' + hex);
								$("#colorpicker").val(hex);
				},
								onSubmit: function (hsb, hex, rgb) {
								$("#colorpicker").val(hex);
				}
				});
				})
								.bind(\'keyup\', function(){
								$(this).ColorPickerSetColor(this.value);
				});
								</script>';
					print '<tr><td>'.$langs->trans("AgfSessionCommercial").'</td>';
					print '<td>';
					$form->select_users($agf->commercialid, 'commercial',1, array(1));
					print '</td></tr>';

					print '<tr><td>'.$langs->trans("AgfDuree").'</td>';
					print '<td><input size="4" type="text" class="flat" id="duree_session" name="duree_session" value="'.$agf->duree_session.'" /></td></tr>';
					
					print '<tr><td width="20%">'.$langs->trans("AgfProductServiceLinked").'</td><td>';
					print $form->select_produits($agf->fk_product,'productid','',10000);
					print "</td></tr>";

					print '<tr><td>'.$langs->trans("AgfDateDebut").'</td><td>';
					$form->select_date($agf->dated, 'dad','','','','update');
					print '</td></tr>';

					print '<tr><td>'.$langs->trans("AgfDateFin").'</td><td>';
					$form->select_date($agf->datef, 'daf','','','','update');
					print '</td></tr>';

					print '<tr><td>'.$langs->trans("Customer").'</td>';
					print '<td>';
					if ($conf->global->AGF_CONTACT_DOL_SESSION)	{
						$events=array();
						$events[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'contact', 'params' => array('add-customer-contact' => 'disabled'));
						print $form->select_company($agf->fk_soc,'fk_soc','',1,1,0,$events);
					} else {
						print $form->select_company($agf->fk_soc,'fk_soc','',1,1);
					}

					if ($conf->global->AGF_CONTACT_DOL_SESSION)	{
						print '<tr><td>'.$langs->trans("AgfSessionContact").'</td>';
						print '<td><table class="nobordernopadding"><tr><td>';
						if (!empty($agf->fk_soc)) {
							$form->select_contacts($agf->fk_soc,$agf->sourcecontactid,'contact',1,'','',1,'',1);
						} else {
							$form->select_contacts(0,$agf->sourcecontactid,'contact',1,'','',1,'',1);
						}
						print '</td>';
						print '<td>'.$form->textwithpicto('',$langs->trans("AgfAgefoddDolContactHelp"),1,'help').'</td></tr></table>';
						print '</td></tr>';
					}
					else {
						print '<tr><td>'.$langs->trans("AgfSessionContact").'</td>';
						print '<td><table class="nobordernopadding"><tr><td>';
						print $formAgefodd->select_agefodd_contact($agf->contactid, 'contact','',1);
						print '</td><td>'.$form->textwithpicto('',$langs->trans("AgfAgefoddContactHelp"),1,'help').'</td></tr></table>';
						print '</td></tr>';
					}

					print '<tr><td>'.$langs->trans("AgfLieu").'</td>';
					print '<td>';
					print $formAgefodd->select_site_forma($agf->placeid,'place');
					print '</td></tr>';

					print '<tr><td width="20%">'.$langs->trans("AgfNumberPlaceAvailable").'</td>';
					print '<td><input size="4" type="text" class="flat" name="nb_place" value="'.$agf->nb_place.'" />'.'</td></tr>';

					if ($agf->force_nb_stagiaire==0 || empty($agf->force_nb_stagiaire)) {
						$disabled = 'disabled="disabled"';
						$checked = '';
					}
					else {
						$disabled = '';
						$checked = 'checked="checked"';
					}
					// if not force we must input values
					print '<tr><td width="20%">'.$langs->trans("AgfNbreParticipants").'</td>';
					print '<td><input size="4" type="text" class="flat" id="nb_stagiaire" name="nb_stagiaire" '.$disabled.' value="'.($agf->nb_stagiaire>0?$agf->nb_stagiaire:'0').'" /></td></tr>';

					print '<tr><td width="20%">'.$langs->trans("AgfForceNbreParticipants").'</td>';
					print '<td>';
					print '<input size="4" type="checkbox" '.$checked.' name="force_nb_stagiaire" value="1" onclick="fnForceUpdate(this);" />'.'</td></tr>';

					print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
					if (!empty($agf->note)) $notes = nl2br($agf->note);
					else $notes =  $langs->trans("AgfUndefinedNote");
					print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.stripslashes($agf->notes).'</textarea></td></tr>';

					print '<tr><td>'.$langs->trans("AgfDateResTrainer").'</td><td><table class="nobordernopadding"><tr><td>';
					if ($agf->is_date_res_site==1) {
						$chkrestrainer='checked="checked"';
					}
					print '<input type="checkbox" name="isdaterestrainer" value="1" '.$chkrestrainer.'/></td><td>';
					$form->select_date($agf->date_res_trainer, 'res_train','','',1,'update',1,1);
					print '</td><td>';
					print $form->textwithpicto('', $langs->trans("AgfDateCheckbox"));
					print '</td></tr></table>';
					print '</td></tr>';

					print '<tr><td>'.$langs->trans("AgfDateResSite").'</td><td><table class="nobordernopadding"><tr><td>';
					if ($agf->is_date_res_site==1) {
						$chkressite='checked="checked"';
					}
					print '<input type="checkbox" name="isdateressite" value="1" '.$chkressite.' /></td><td>';
					$form->select_date($agf->date_res_site, 'res_site','','',1,'update',1,1);
					print '</td><td>';
					print $form->textwithpicto('', $langs->trans("AgfDateCheckbox"));
					print '</td></tr></table>';
					
					print '<tr><td width="20%">'.$langs->trans("AgfNbMintarget").'</td><td>';
					print '<input name="nbmintarget" class="flat" size="5" value="'.$agf->nb_subscribe_min.'"></td></tr>';
					
					print '<tr><td valign="top">'.$langs->trans("AgfStatusSession").'</td>';
					print '<td>';
					print $formAgefodd->select_session_status($agf->status,"session_status",'t.active=1');
					print '</td></tr>';
					
					print '</td></tr>';

					if (! empty($extrafields->attribute_label))
					{
						print $agf->showOptionals($extrafields,'edit');
					}
					
					print '</table>';
					print '</div>';

					/*
					 * Cost management
					*/
					print_barre_liste($langs->trans("AgfCost"),"", "","","","",'',0);
					print '<div class="tabBar">';
					print '<table class="border" width="100%">';
					print '<tr><td width="20%">'.$langs->trans("AgfCoutFormateur").'</td>';
					print '<td><input size="6" type="text" class="flat" name="costtrainer" value="'.price($agf->cost_trainer).'" />'.' '.$langs->trans('Currency'.$conf->currency).'</td></tr>';

					print '<tr><td width="20%">'.$langs->trans("AgfCoutSalle").'</td>';
					print '<td><input size="6" type="text" class="flat" name="costsite" value="'.price($agf->cost_site).'" />'.' '.$langs->trans('Currency'.$conf->currency).'</td></tr>';
					print '<tr><td width="20%">'.$langs->trans("AgfCoutDeplacement").'</td>';
					print '<td><input size="6" type="text" class="flat" name="costtrip" value="'.price($agf->cost_trip).'" />'.' '.$langs->trans('Currency'.$conf->currency).'</td></tr>';

					print '<tr><td width="20%">'.$langs->trans("AgfCoutFormation").'</td>';
					print '<td><input size="6" type="text" class="flat" name="sellprice" value="'.price($agf->sell_price).'" />'.' '.$langs->trans('Currency'.$conf->currency).' '.$other_amount.'</td></tr>';
					print '</table></div>';

					print '<table style=noborder align="right">';
					print '<tr><td align="center" colspan=2>';
					print '<input type="submit" class="butAction" name="save" value="'.$langs->trans("Save").'"> &nbsp; ';
					print '<input type="submit" class="butAction" name="saveandclose" value="'.$langs->trans("SaveAndClose").'"> &nbsp; ';
					print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
					print '</td></tr>';
					print '</table>';

					print '</form>';

					/*
					 * Calendar management
					*/
					print_barre_liste($langs->trans("AgfCalendrier"),"", "","","","",'',0);

					/*
					 * Confirm delete calendar
					*/
					if ($_POST["period_remove_x"])
					{
						// Param url = id de la periode à supprimer - id session
						$ret=$form->form_confirm($_SERVER['PHP_SELF'].'?modperiod='.$_POST["modperiod"].'&id='.$id,$langs->trans("AgfDeletePeriod"),$langs->trans("AgfConfirmDeletePeriod"),"confirm_delete_period",'','',1);
						if ($ret == 'html') print '<br>';
					}
					print '<div class="tabBar">';
					print '<table class="border" width="100%">';

					$calendrier = new Agefodd_sesscalendar($db);
					$calendrier->fetch_all($agf->id);
					$blocNumber = count($calendrier->lines);
					if ($blocNumber < 1 && !(empty($newperiod)))
					{
						print '<tr>';
						print '<td  colpsan=1 style="color:red; text-decoration: blink;">'.$langs->trans("AgfNoCalendar").'</td></tr>';
					}
					else
					{
						$old_date = 0;
						$duree = 0;
						for ($i = 0; $i < $blocNumber; $i++)
						{
							if ($calendrier->lines[$i]->id == $_POST["modperiod"] && $_POST["period_remove_x"]) print '<tr bgcolor="#d5baa8">'."\n";
							else print '<tr>'."\n";
							print '<form name="obj_update_'.$i.'" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
							print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
							print '<input type="hidden" name="action" value="edit">'."\n";
							print '<input type="hidden" name="sessid" value="'.$calendrier->lines[$i]->sessid.'">'."\n";
							print '<input type="hidden" name="modperiod" value="'.$calendrier->lines[$i]->id.'">'."\n";

							if ($calendrier->lines[$i]->id == $_POST["modperiod"] && ! $_POST["period_remove_x"])
							{
								print '<td  width="20%">'.$langs->trans("AgfPeriodDate").' ';
								$form->select_date($calendrier->lines[$i]->date_session, 'date','','','','obj_update_'.$i);
								print '</td>';
								print '<td width="150px" nowrap>'.$langs->trans("AgfPeriodTimeB").' ';
								print $formAgefodd->select_time(dol_print_date($calendrier->lines[$i]->heured,'hour'),'dated');
								print ' - '.$langs->trans("AgfPeriodTimeE").' ';
								print $formAgefodd->select_time(dol_print_date($calendrier->lines[$i]->heuref,'hour'),'datef');
								print '</td>';

								if ($user->rights->agefodd->modifier)
								{
									print '</td><td><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="period_update" alt="'.$langs->trans("AgfModSave").'" ">';
								}
							}
							else
							{
								print '<td width="20%">'.dol_print_date($calendrier->lines[$i]->date_session,'daytext').'</td>';
								print '<td  width="150px">'.dol_print_date($calendrier->lines[$i]->heured,'hour').' - '.dol_print_date($calendrier->lines[$i]->heuref,'hour');
								if ($user->rights->agefodd->modifier)
								{
									print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" name="period_edit" alt="'.$langs->trans("AgfModSave").'">';
								}
								print '&nbsp;';
								if ($user->rights->agefodd->creer)
								{
									print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="period_remove" alt="'.$langs->trans("AgfModSave").'">';
								}
							}
							print '</td>' ;

							// We calculated the total session duration time
							$duree += ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured);

							print '</form>'."\n";
							print '</tr>'."\n";
						}
						if (($agf->duree_session * 3600) != $duree)
						{
							print '<tr><td colspan=5 align="center"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/recent.png" border="0" align="absmiddle" hspace="6px" >';
							if (($agf->duree_session * 3600) < $duree) print $langs->trans("AgfCalendarSup");
							if (($agf->duree_session * 3600) > $duree) print $langs->trans("AgfCalendarInf");
							$min = floor($duree/60) ;
							$rmin = sprintf("%02d", $min %60) ;
							$hour = floor($min/60);
							print ' ('.$langs->trans("AgfCalendarDureeProgrammee").': '.$hour.':'.$rmin.', ';
							print $langs->trans("AgfCalendarDureeThéorique").' : '.($agf->duree_session).':00).</td></tr>';
						}
					}

					// Fiels for new periodes
						
					if (!empty($newperiod))
					{
						print "</table></div>";
						print '<table style="border:0;" width="100%">';
						print '<tr><td align="right">';
						print '<form name="newperiod" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
						print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
						print '<input type="hidden" name="action" value="edit">'."\n";
						print '<input type="hidden" name="newperiod" value="1">'."\n";
						print '<input type="submit" class="butAction" value="'.$langs->trans("AgfPeriodAdd").'">';
						print '</td></tr>';
						print '</form>';
					}
					else
					{
						print '<form name="obj_update_'.($i + 1).'" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
						print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
						print '<input type="hidden" name="action" value="edit">'."\n";
						print '<input type="hidden" name="sessid" value="'.$agf->id.'">'."\n";
						print '<input type="hidden" name="periodid" value="'.$stagiaires->lines[$i]->stagerowid.'">'."\n";
						print '<input type="hidden" id="datetmplday"   name="datetmplday"   value="'.dol_print_date($agf->dated, "%d").'">'."\n";
						print '<input type="hidden" id="datetmplmonth" name="datetmplmonth" value="'.dol_print_date($agf->dated, "%m").'">'."\n";
						print '<input type="hidden" id="datetmplyear"  name="datetmplyear"  value="'.dol_print_date($agf->dated, "%Y").'">'."\n";

						//Add new line from template
						$tmpl_calendar = new Agefoddcalendrier($db);
						$result=$tmpl_calendar->fetch_all();
						if ($result) {
							print '<tr>';
							print '<td colspan="3">';
							print $langs->trans('AgfCalendarFromTemplate').':';
							print '<table class="noborder">';
							foreach($tmpl_calendar->lines as $line) {
								
								if ($line->day_session!=1) {
									$tmpldate = dol_time_plus_duree($agf->dated, (($line->day_session)-1), 'd');
								} else {
									$tmpldate= $agf->dated;
								}
								
								if ($tmpldate<=$agf->datef) {
								print '<tr><td>';
								print '<input type="checkbox" name="fromtemplate[]" id="fromtemplate" value="'.$line->id.'">'.dol_print_date($tmpldate,'daytext').' '.$line->heured.' - '.$line->heuref.'</input>';
								print '</td></tr>';
								}
							}
							print '</table>';
							print '</td>';
							if ($user->rights->agefodd->modifier)
							{
								print '<td><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="period_add" alt="'.$langs->trans("AgfModSave").'" "></td>';
							}
							print '</tr>'."\n";
						}
						print '<tr>';

						print '<td  width="300px">'.$langs->trans("AgfPeriodDate").' ';
						$form->select_date($agf->dated, 'date','','','','newperiod');
						print '</td>';
						print '<td width="400px">'.$langs->trans("AgfPeriodTimeB").' ';
						print $formAgefodd->select_time('08:00','dated');
						print '</td>';
						print '<td width="400px">'.$langs->trans("AgfPeriodTimeE").' ';
						print $formAgefodd->select_time('18:00','datef');
						print '</td>';
						if ($user->rights->agefodd->modifier)
						{
							print '<td><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="period_add" alt="'.$langs->trans("AgfModSave").'" "></td>';
						}

						print '</tr>'."\n";
						print '</form>';
					}

					print '</table>';
					print '</div>';


				}
				else
				{
					// Display view mode
					/*
					* Confirm delete
					*/
					if ($action == 'delete')
					{
						$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("AgfDeleteOps"),$langs->trans("AgfConfirmDeleteOps"),"confirm_delete",'','',1);
						if ($ret == 'html') print '<br>';
					}

					/*
					 * confirm archive update status
					*/
					if (isset($_GET["arch"]))
					{
						$ret=$form->form_confirm($_SERVER['PHP_SELF']."?arch=".$_GET["arch"]."&id=".$id,$langs->trans("AgfFormationArchiveChange"),$langs->trans("AgfConfirmArchiveChange"),"arch_confirm_delete",'','',1);
						if ($ret == 'html') print '<br>';
					}

					// Confirm clone
					if ($action == 'clone')
					{
						$formquestion=array(
						'text' => $langs->trans("ConfirmClone"),
						array('type' => 'checkbox', 'name' => 'clone_calendar','label' => $langs->trans("AgfCloneSessionCalendar"),   'value' => 1),
						array('type' => 'checkbox', 'name' => 'clone_trainee','label' => $langs->trans("AgfCloneSessionTrainee"),   'value' => 1)
						);
						$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("CloneSession"),$langs->trans("ConfirmCloneSession"),"confirm_clone",$formquestion,'',1);
						if ($ret == 'html') print '<br>';
					}

					print '<div width=100% align="center" style="margin: 0 0 3px 0;">';
					print $formAgefodd->level_graph(ebi_get_adm_lastFinishLevel($id), ebi_get_level_number($id), $langs->trans("AgfAdmLevel"));
					print '</div>';

					// Print session card
					$agf->printSessionInfo();

					print '&nbsp';

					/*
					 * Manage founding ressources depend type inter-enterprise or extra-enterprise
					*/
					if(!$agf->type_session > 0 && !empty($conf->global->AGF_MANAGE_OPCA))
					{
						print '&nbsp';
						print '<table class="border" width="100%">';
						print '<tr><td>'.$langs->trans("AgfSubrocation").'</td>';
						if ($agf->is_OPCA==1) {
							$isOPCA=' checked="checked" ';
						}else {$isOPCA='';
						}
						print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" '.$isOPCA.'/></td></tr>';

						print '<tr><td width="20%">'.$langs->trans("AgfOPCAName").'</td>';
						print '	<td>';
						print '<a href="'.dol_buildpath('/societe/soc.php',1).'?socid='.$agf->fk_soc_OPCA.'">'.$agf->soc_OPCA_name.'</a>';
						print '</td></tr>';

						print '<tr><td width="20%">'.$langs->trans("AgfOPCAAdress").'</td>';
						print '	<td>';
						print dol_print_address($agf->OPCA_adress,'gmap','thirdparty',0);
						print '</td></tr>';

						print '<tr><td width="20%">'.$langs->trans("AgfOPCAContact").'</td>';
						print '	<td>';
						print '<a href="'.dol_buildpath('/contact/fiche.php',1).'?id='.$agf->fk_socpeople_OPCA.'">'.$agf->contact_name_OPCA.'</a>';
						print '</td></tr>';

						print '<tr><td width="20%">'.$langs->trans("AgfOPCANumClient").'</td>';
						print '<td>';
						print $agf->num_OPCA_soc;
						print '</td></tr>';

						print '<tr><td width="20%">'.$langs->trans("AgfOPCADateDemande").'</td>';
						if ($agf->is_date_ask_OPCA==1) {
							$chckisDtOPCA='checked="checked"';
						}
						print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" name="isdateaskOPCA" value="1" '.$chckisDtOPCA.' />';
						print dol_print_date($agf->date_ask_OPCA,'daytext');
						print '</td></tr>';

						print '<tr><td width="20%">'.$langs->trans("AgfOPCANumFile").'</td>';
						print '<td>';
						print $agf->num_OPCA_file;
						print '</td></tr>';

						print '</table>';
					}

					/*
					 * Cost management
					 */
					$spend_cost = 0;
					$cashed_cost = 0;
						
					print '&nbsp';
					print '<table class="border" width="100%">';
					print '<tr><td width="20%">'.$langs->trans("AgfCoutFormateur").'</td>';
					print '<td>'.price($agf->cost_trainer).' '.$langs->trans('Currency'.$conf->currency).'</td></tr>';
					$spend_cost+=$agf->cost_trainer;

					print '<tr><td width="20%">'.$langs->trans("AgfCoutSalle").'</td>';
					print '<td>'.price($agf->cost_site).' '.$langs->trans('Currency'.$conf->currency).'</td></tr>';
					$spend_cost+=$agf->cost_site;

					print '<tr><td width="20%">'.$langs->trans("AgfCoutDeplacement").'</td>';
					print '<td>'.price($agf->cost_trip).' '.$langs->trans('Currency'.$conf->currency).'</td></tr>';
					$spend_cost+=$agf->cost_trip;

					print '<tr><td width="20%"><strong>'.$langs->trans("AgfCoutTotal").'</strong></td>';
					print '<td><strong>'.price($spend_cost).' '.$langs->trans('Currency'.$conf->currency).'</strong></td></tr>';
					
					print '<tr><td width="20%">'.$langs->trans("AgfCoutFormation").'</td>';
					print '<td>'.price($agf->sell_price).' '.$langs->trans('Currency'.$conf->currency).' '.$other_amount.'</td></tr>';
					$cashed_cost+=$agf->sell_price;
						
					print '<tr><td width="20%"><strong>'.$langs->trans("AgfCoutRevient").'</strong></td>';
					print '<td><strong>'.price($cashed_cost-$spend_cost).' '.$langs->trans('Currency'.$conf->currency).'</strong></td></tr>';

					print '</table>';


					/*
					 * Manage trainers
					*/
					print '&nbsp';
					print '<table class="border" width="100%">';

					$formateurs = new Agefodd_session_formateur($db);
					$nbform = $formateurs->fetch_formateur_per_session($agf->id);
					print '<tr><td width="20%" valign="top">';
					print $langs->trans("AgfFormateur");
					if ($nbform > 0) print ' ('.$nbform.')';
					print '</td>';
					if ($nbform < 1)
					{
						print '<td style="text-decoration: blink;">'.$langs->trans("AgfNobody").'</td></tr>';
					}
					else
					{
						print '<td>';
						for ($i=0; $i < $nbform; $i++)
						{
							// Infos trainers
							print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$formateurs->lines[$i]->socpeopleid.'">';
							print img_object($langs->trans("ShowContact"),"contact").' ';
							print strtoupper($formateurs->lines[$i]->lastname).' '.ucfirst($formateurs->lines[$i]->firstname).'</a>';
							if ($i < ($nbform - 1)) print ',&nbsp;&nbsp;';

						}
						print '</td>';
						print "</tr>\n";
					}
					print "</table>";

					/*
					 * Manage calendars
					*/

					print '&nbsp';
					print '<table class="border" width="100%">';
					print '<tr>';

					$calendrier = new Agefodd_sesscalendar($db);
					$calendrier->fetch_all($agf->id);
					$blocNumber = count($calendrier->lines);
					if ($blocNumber < 1)
					{
						print '<td  width="20%" valign="top" >'.$langs->trans("AgfCalendrier").'</td>';
						print '<td style="color:red; text-decoration: blink;">'.$langs->trans("AgfNoCalendar").'</td></tr>';
					}
					else
					{
						print '<td  width="20%" valign="top" style="border-bottom:0px;">'.$langs->trans("AgfCalendrier").'</td>';
						$old_date = 0;
						$duree = 0;
						for ($i = 0; $i < $blocNumber; $i++)
						{
							if ($calendrier->lines[$i]->date_session != $old_date)
							{
								if ($i > 0 )print '</tr><tr><td width="150px" style="border:0px;">&nbsp;</td>';
								print '<td width="150px">';
								print dol_print_date($calendrier->lines[$i]->date_session,'daytext').'</td><td>';
							}
							else print ', ';
							print dol_print_date($calendrier->lines[$i]->heured,'hour').' - '.dol_print_date($calendrier->lines[$i]->heuref,'hour');
							if ($i == $blocNumber -1 ) print '</td></tr>';

							$old_date = $calendrier->lines[$i]->date_session;

							// We calculate the total duration times
							// reminders: mktime(hours, minutes, secondes, month, day, year);
							$duree += ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured);
						}
						if (($agf->duree * 3600) != $duree)
						{
							print '<tr><td>&nbsp;</td><td colspan=2><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/recent.png" border="0" align="absmiddle" hspace="6px" >';
							if (($agf->duree * 3600) < $duree) print $langs->trans("AgfCalendarSup");
							if (($agf->duree * 3600) > $duree) print $langs->trans("AgfCalendarInf");
							$min = floor($duree/60) ;
							$rmin = sprintf("%02d", $min %60) ;
							$hour = floor($min/60);
							print ' ('.$langs->trans("AgfCalendarDureeProgrammee").': '.$hour.':'.$rmin.', ';
							print $langs->trans("AgfCalendarDureeThéorique").' : '.($agf->duree).':00).</td></tr>';
						}
					}
					print '</tr>';
					print "</table>";

					/*
					 * Manage trainees
					*/

					print '&nbsp';
					print '<table class="border" width="100%">';

					$stagiaires = new Agefodd_session_stagiaire($db);
					$stagiaires->fetch_stagiaire_per_session($agf->id);
					$nbstag = count($stagiaires->lines);
					print '<tr><td  width="20%" valign="top" ';
					if ($nbstag < 1) {
						print '>'.$langs->trans("AgfParticipants").'</td>';
						print '<td style="text-decoration: blink;">'.$langs->trans("AgfNobody").'</td></tr>';
					}
					else
					{
						print ' rowspan='.($nbstag).'>'.$langs->trans("AgfParticipants");
						if ($nbstag > 1) print ' ('.$nbstag.')';
						print '</td>';

						for ($i=0; $i < $nbstag; $i++)	{
							print '<td witdth="20px" align="center">'.($i+1).'</td>';
							print '<td width="400px"style="border-right: 0px;">';
							// Infos trainee
							if (strtolower($stagiaires->lines[$i]->nom) == "undefined")	{
								print $langs->trans("AgfUndefinedStagiaire");
							}
							else {
								$trainee_info = '<a href="'.dol_buildpath('/agefodd/trainee/card.php',1).'?id='.$stagiaires->lines[$i]->id.'">';
								$trainee_info .= img_object($langs->trans("ShowContact"),"contact").' ';
								$trainee_info .= strtoupper($stagiaires->lines[$i]->nom).' '.ucfirst($stagiaires->lines[$i]->prenom).'</a>';
								$contact_static= new Contact($db);
								$contact_static->civilite_id = $stagiaires->lines[$i]->civilite;
								$trainee_info .= ' ('.$contact_static->getCivilityLabel().')';

								if ($agf->type_session == 1 && !empty($conf->global->AGF_MANAGE_OPCA))
								{
									print '<table class="nobordernopadding" width="100%"><tr><td colspan="2">';
									print $trainee_info.' '.$stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session,4);
									print '</td></tr>';

									$agf->getOpcaForTraineeInSession($stagiaires->lines[$i]->socid,$agf->id);
									print '<tr><td width="45%">'.$langs->trans("AgfSubrocation").'</td>';
									if ($agf->is_OPCA==1) {
										$chckisOPCA='checked="checked"';
									}
									print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" '.$chckisOPCA.'" disabled="disabled" readonly="readonly"/></td></tr>';

									print '<tr><td>'.$langs->trans("AgfOPCAName").'</td>';
									print '	<td>';
									print '<a href="'.dol_buildpath('/societe/soc.php',1).'?socid='.$agf->fk_soc_OPCA.'">'.$agf->soc_OPCA_name.'</a>';
									print '</td></tr>';

									print '<tr><td>'.$langs->trans("AgfOPCAContact").'</td>';
									print '	<td>';
									print '<a href="'.dol_buildpath('/contact/fiche.php',1).'?id='.$agf->fk_socpeople_OPCA.'">'.$agf->contact_name_OPCA.'</a>';
									print '</td></tr>';

									print '<tr><td width="20%">'.$langs->trans("AgfOPCANumClient").'</td>';
									print '<td>'.$agf->num_OPCA_soc.'</td></tr>';

									print '<tr><td width="20%">'.$langs->trans("AgfOPCADateDemande").'</td>';
									if ($agf->is_date_ask_OPCA==1) {
										$chckisDtOPCA='checked="checked"';
									}
									print '<td><table class="nobordernopadding"><tr><td>';
									print '<input type="checkbox" class="flat" name="isdateaskOPCA" disabled="disabled" readonly="readonly" value="1" '.$chckisDtOPCA.' /></td>';
									print '<td>';
									print dol_print_date($agf->date_ask_OPCA,'daytext');
									print '</td><td>';
									print '</td></tr></table>';
									print '</td></tr>';

									print '<tr><td width="20%">'.$langs->trans("AgfOPCANumFile").'</td>';
									print '<td>'.$agf->num_OPCA_file.'</td></tr>';

									print '</table>';
								}
								else {
									print $trainee_info.' '.$stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session,4);
								}
							}
							print '</td>';
							print '<td style="border-left: 0px; border-right: 0px;">';
							// Info funding company
							if ($stagiaires->lines[$i]->socid) {
								print '<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$stagiaires->lines[$i]->socid.'">';
								print img_object($langs->trans("ShowCompany"),"company").' '.dol_trunc($stagiaires->lines[$i]->socname,20).'</a>';
							}
							else {
								print '&nbsp;';
							}
							print '</td>';
							print '<td style="border-left: 0px;">';
							// Info funding type
							if ($stagiaires->lines[$i]->type && (!empty($conf->global->AGF_USE_STAGIAIRE_TYPE))) {
								print '<div class=adminaction>';
								print $langs->trans("AgfStagiaireModeFinancement");
								print '-<span>'.stripslashes($stagiaires->lines[$i]->type).'</span></div>';
							}
							else {
								print '&nbsp;';
							}
							print '</td>';
							print "</tr>\n";
						}
					}
					print "</table>";
					print '</div>';
				}
			}
			else
			{
				print $langs->trans('AgfNoSession');
			}
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}
}


/*
 * Action tabs
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && (!empty($agf->id)))
{
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
	}
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="subscribers.php?action=edit&id='.$id.'">'.$langs->trans('AgfModifySubscribersAndSubrogation').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfModifySubscribersAndSubrogation').'</a>';
	}

	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="trainer.php?action=edit&id='.$id.'">'.$langs->trans('AgfModifyTrainer').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfModifyTrainer').'</a>';
	}

	if ($user->rights->agefodd->creer)
	{
		print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
	}
	if ($agf->archive == 0)
	{
		$button = $langs->trans('AgfArchiver');
		$arch = 1;
	}
	else
	{
		$button = $langs->trans('AgfActiver');
		$arch = 0;
	}
	if ($user->rights->agefodd->modifier)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=clone&id='.$id.'">'.$langs->trans('ToClone').'</a>';
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?arch='.$arch.'&id='.$id.'">'.$button.'</a>';

	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$button.'</a>';
	}
}

print '</div>';

llxFooter();
$db->close();