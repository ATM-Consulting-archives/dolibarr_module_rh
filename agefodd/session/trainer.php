<?php
/** Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
* Copyright (C) 2012-2013		Florian Henry	<florian.henry@open-concept.pro>
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
 *	\file       agefodd/session/trainer.php
 *	\ingroup    agefodd
 *	\brief      card of trainer session
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agsession.class.php');
require_once(DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php');
require_once('../class/agefodd_session_formateur.class.php');
require_once('../class/agefodd_session_formateur_calendrier.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');



// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$action=GETPOST('action','alpha');
$id=GETPOST('id','int');
$confirm=GETPOST('confirm','alpha');
$form_update_x=GETPOST('form_update_x','alpha');
$form_add_x=GETPOST('form_add_x','alpha');

$newperiod=GETPOST('newperiod');

/*
 * Actions delete formateur
*/

if ($action == 'confirm_delete_form' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$obsid=GETPOST('opsid','int');

	$agf = new Agefodd_session_formateur($db);
	$result = $agf->remove($obsid);

	if ($result > 0)
	{
		Header ("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
	else
	{
		setEventMessage($agf->error,'errors');
	}
}


if ($action=='edit' && $user->rights->agefodd->creer) {

	if($form_update_x > 0)
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

	if($form_add_x > 0)
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

if ($action=='edit_calendrier' && $user->rights->agefodd->creer)
{


	if($_POST["period_add_x"])
	{
		$error=0;
		$error_message='';

		$agf_cal = new Agefoddsessionformateurcalendrier($db);

		$agf_cal->sessid = GETPOST('sessid','int');
		$agf_cal->fk_agefodd_session_formateur = GETPOST('fk_agefodd_session_formateur','int');
		$agf_cal->trainer_cost = price2num(GETPOST('trainer_cost','alpha'),'MU');
		$agf_cal->date_session = dol_mktime(0,0,0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));

		//From calendar selection
		$heure_tmp_arr = array();

		$heured_tmp = GETPOST('dated','alpha');
		if (!empty($heured_tmp)){
			$heure_tmp_arr = explode(':',$heured_tmp);
			$agf_cal->heured = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
		}

		$heuref_tmp = GETPOST('datef','alpha');
		if (!empty($heuref_tmp)){
			$heure_tmp_arr = explode(':',$heuref_tmp);
			$agf_cal->heuref = dol_mktime($heure_tmp_arr[0],$heure_tmp_arr[1],0,GETPOST('datemonth','int'),GETPOST('dateday','int'),GETPOST('dateyear','int'));
		}

		$result = $agf_cal->create($user);
		if ($result < 0)
		{
			$error++;
			$error_message =  $agf_cal->error;
		}

		if (!$error)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit_calendrier&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($error_message,'errors');
		}
	}

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

		$trainer_cost = price2num(GETPOST('trainer_cost','alpha'),'MU');
		$fk_agefodd_session_formateur = GETPOST('fk_agefodd_session_formateur','int');

		$agf_cal = new Agefoddsessionformateurcalendrier($db);
		$result = $agf_cal->fetch($modperiod);

		if(!empty($modperiod)) 			$agf_cal->id = $modperiod;
		if(!empty($date_session)) 		$agf_cal->date_session = $date_session;
		if(!empty($heured)) 			$agf_cal->heured = $heured;
		if(!empty($heuref)) 			$agf_cal->heuref =  $heuref;
		if(!empty($trainer_cost)) 		$agf_cal->trainer_cost = $trainer_cost;
		if(!empty($fk_agefodd_session_formateur))
			$agf_cal->fk_agefodd_session_formateur = $fk_agefodd_session_formateur;


		$result = $agf_cal->update($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit_calendrier&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($agf_cal->error,'errors');
		}
	}
}

/*
 * Actions delete period
*/

if ($action == 'confirm_delete_period' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$modperiod=GETPOST('modperiod','int');

	$agf = new Agefoddsessionformateurcalendrier($db);
	$result = $agf->remove($modperiod);

	if ($result > 0)
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit_calendrier&id=".$id);
		exit;
	}
	else
	{
		setEventMessage($agf->error,'errors');
	}
}



/*
 * View
*/

llxHeader('',$langs->trans("AgfSessionDetail"));

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);


if (!empty($id))
{
	$agf = new Agsession($db);
	$result = $agf->fetch($id);

	$head = session_prepare_head($agf);

	dol_fiche_head($head, 'trainers', $langs->trans("AgfSessionDetail"), 0, 'group');

	print '<div width=100% align="center" style="margin: 0 0 3px 0;">';
	print $formAgefodd->level_graph(ebi_get_adm_lastFinishLevel($id), ebi_get_level_number($id), $langs->trans("AgfAdmLevel"));
	print '</div>';

	// Print session card
	$agf->printSessionInfo();

	print '&nbsp</div>';
	print_barre_liste($langs->trans("AgfFormateur"),"", "","","","",'',0);


	/*
	 * Confirm delete calendar
	*/
	if ($_POST["period_remove_x"])
	{
		// Param url = id de la periode à supprimer - id session
		$ret=$form->form_confirm($_SERVER['PHP_SELF'].'?modperiod='.$_POST["modperiod"].'&id='.$id,$langs->trans("AgfDeletePeriod"),$langs->trans("AgfConfirmDeletePeriod"),"confirm_delete_period",'','',1);
		if ($ret == 'html') print '<br>';
	}

	if ($action == 'edit')
	{

		/*
		 * Confirm Delete
		*/
		if ($_POST["form_remove_x"]){
			// Param url = id de la ligne formateur dans session - id session
			$ret=$form->form_confirm($_SERVER['PHP_SELF']."?opsid=".$_POST["opsid"].'&id='.$id,$langs->trans("AgfDeleteForm"),$langs->trans("AgfConfirmDeleteForm"),"confirm_delete_form",'','',1);
			if ($ret == 'html') print '<br>';
		}



		print '<div class="tabBar">';
		print '<table class="border" width="100%">';

		// Display edit and update trainer
		$formateurs = new Agefodd_session_formateur($db);
		$nbform = $formateurs->fetch_formateur_per_session($agf->id);
		if ($nbform > 0) {
			for ($i=0; $i < $nbform; $i++)	{
				if ($formateurs->lines[$i]->opsid == $_POST["opsid"] && $_POST["form_remove_x"]) print '<tr bgcolor="#d5baa8">';
				else print '<tr>';
				print '<form name="form_update_'.$i.'" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
				print '<input type="hidden" name="action" value="edit">'."\n";
				print '<input type="hidden" name="sessid" value="'.$formateurs->lines[$i]->sessid.'">'."\n";
				print '<input type="hidden" name="opsid" value="'.$formateurs->lines[$i]->opsid.'">'."\n";

				print '<td width="20px" align="center">'.($i+1).'</td>';

				if ($formateurs->lines[$i]->opsid == $_POST["opsid"] && ! $_POST["form_remove_x"])
				{
					print '<td width="300px" style="border-right: 0px">';
					print $formAgefodd->select_formateur($formateurs->lines[$i]->formid, "formid");
					if ($user->rights->agefodd->modifier)
					{
						print '</td><td><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="form_update" alt="'.$langs->trans("AgfModSave").'" ">';
					}
					print '</td>';
				}
				else
				{
					print '<td width="300px"style="border-right: 0px;">';
					// trainer info
					if (strtolower($formateurs->lines[$i]->lastname) == "undefined")	{
						print $langs->trans("AgfUndefinedStagiaire");
					}
					else {
						print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$formateurs->lines[$i]->socpeopleid.'">';
						print img_object($langs->trans("ShowContact"),"contact").' ';
						print strtoupper($formateurs->lines[$i]->lastname).' '.ucfirst($formateurs->lines[$i]->firstname).'</a>';
					}
					print '</td>';
					print '<td>';


					if ($user->rights->agefodd->modifier)
					{
						print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" name="form_edit" alt="'.$langs->trans("AgfModSave").'">';
					}
					print '&nbsp;';
					if ($user->rights->agefodd->creer)
					{
						print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="form_remove" alt="'.$langs->trans("AgfModSave").'">';
					}
					print '</td>'."\n";
				}
				print '</form>'."\n";
				print '</tr>'."\n";
			}
		}

		// New trainers
		if (isset($_POST["newform"])) {
			print '<tr>';
			print '<form name="form_update_'.($i + 1).'" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="edit">'."\n";
			print '<input type="hidden" name="sessid" value="'.$agf->id.'">'."\n";
			print '<td width="20px" align="center">'.($i+1).'</td>';
			print '<td>';
			print $formAgefodd->select_formateur($formateurs->lines[$i]->formid, "formid", 's.rowid NOT IN (SELECT fk_agefodd_formateur FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur WHERE fk_session='.$id.')',1);
			if ($user->rights->agefodd->modifier) {
				print '</td><td><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="form_add" alt="'.$langs->trans("AgfModSave").'">';
			}
			print '</td>';
			print '</form>';
			print '</tr>'."\n";
		}

		print '</table>';
		if (!isset($_POST["newform"]))	{
			print '</div>';
			print '<table style="border:0;" width="100%">';
			print '<tr><td align="right">';
			print '<form name="newform" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="edit">'."\n";
			print '<input type="hidden" name="newform" value="1">'."\n";
			print '<input type="submit" class="butAction" value="'.$langs->trans("AgfFormateurAdd").'">';
			print '</td></tr>';
			print '</form>';
			print '</table>';
		}
		print '</div>';
	}
	else {
		// Display view mode
		print '&nbsp';

		$formateurs = new Agefodd_session_formateur($db);
		$nbform = $formateurs->fetch_formateur_per_session($agf->id);
		print $langs->trans("AgfFormateur");
		if ($nbform > 0) print ' ('.$nbform.')';

		if ($nbform < 1)
		{
			print '<td style="text-decoration: blink;"><BR><BR>'.$langs->trans("AgfNobody").'</td></tr>';
			print '<table style="border:0;" width="100%">';
			print '<tr><td align="right">';
			print '<form name="newform" action="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'"  method="POST">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="edit">'."\n";
			print '<input type="hidden" name="newform" value="1">'."\n";
			print '<input type="submit" class="butAction" value="'.$langs->trans("AgfFormateurAdd").'">';
			print '</td></tr>';
			print '</form>';
			print '</table>';
		}
		else
		{
		print '<table class="border" width="100%">';

			for ($i=0; $i < $nbform; $i++) {
				print '<tr><td width="20%" valign="top">';
				// Trainers info
				print '<a href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$formateurs->lines[$i]->socpeopleid.'">';
				print img_object($langs->trans("ShowContact"),"contact").' ';
				print strtoupper($formateurs->lines[$i]->lastname).' '.ucfirst($formateurs->lines[$i]->firstname).'</a>';
				//if ($i < ($nbform - 1)) print ',&nbsp;&nbsp;';
				print '</td>';


				if(!empty($conf->global->AGF_DOL_TRAINER_AGENDA))
				{
					/* Time management */
					$calendrier = new Agefoddsessionformateurcalendrier($db);
					$calendrier->fetch_all($formateurs->lines[$i]->opsid);
					$blocNumber = count($calendrier->lines);

					if ($blocNumber < 1 && !(empty($newperiod)))
					{

						print '<span style="color:red;">'.$langs->trans("AgfNoCalendar").'</span>';
					}
					else
					{
						print '<td>';

						print '<table width="100%" class="border">';

						print '<tr class="liste_titre">';
						print '<th class="liste_titre">'.$langs->trans('Date').'</th>';
						print '<th class="liste_titre">'.$langs->trans('Hours').'</th>';
						print '<th class="liste_titre">'.$langs->trans('AgfTrainerCostHour').'</th>';
						print '<th class="liste_titre">'.$langs->trans('Edit').'</th>';
						print '</tr>';

						$old_date = 0;
						$duree = 0;
						for ($j = 0; $j < $blocNumber; $j++)
						{
							if ($calendrier->lines[$i]->id == $_POST["modperiod"] && $_POST["period_remove_x"]) print '<tr bgcolor="#d5baa8">'."\n";
							else print '<tr>'."\n";
							print '<form name="trainer_calendrier_update_'.$j.'" action="'.$_SERVER['PHP_SELF'].'?id='.$id.'"  method="POST">'."\n";
							print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
							print '<input type="hidden" name="action" value="edit_calendrier">'."\n";
							print '<input type="hidden" name="sessid" value="'.$calendrier->lines[$j]->sessid.'">'."\n";
							print '<input type="hidden" name="modperiod" value="'.$calendrier->lines[$j]->id.'">'."\n";

							if ($calendrier->lines[$j]->id == $_POST["modperiod"] && ! $_POST["period_remove_x"])
							{
								print '<td  width="20%">'.$langs->trans("AgfPeriodDate").' ';
								$form->select_date($calendrier->lines[$j]->date_session, 'date','','','','obj_update_'.$j);
								print '</td>';
								print '<td width="40%;" >'.$langs->trans("AgfPeriodTimeB").' ';
								print $formAgefodd->select_time(dol_print_date($calendrier->lines[$j]->heured,'hour'),'dated');
								print ' - '.$langs->trans("AgfPeriodTimeE").' ';
								print $formAgefodd->select_time(dol_print_date($calendrier->lines[$j]->heuref,'hour'),'datef');
								print '</td>';

								// Coût horaire
								print '<td width="20%"> <input type="text" size="10" name="trainer_cost" value="'.price($calendrier->lines[$i]->trainer_cost).'"/>'.$langs->getCurrencySymbol($conf->currency).'</td>';

								if ($user->rights->agefodd->modifier)
								{
									print '</td><td width="30%;"><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="period_update" alt="'.$langs->trans("AgfModSave").'" ">';
								}
							}
							else
							{
								print '<td width="20%">'.dol_print_date($calendrier->lines[$j]->date_session,'daytext').'</td>';
								print '<td  width="40%">'.dol_print_date($calendrier->lines[$j]->heured,'hour').' - '.dol_print_date($calendrier->lines[$j]->heuref,'hour');
								print '</td>';

								// Coût horaire
								print '<td>'.price($calendrier->lines[$j]->trainer_cost,0,$langs).' '.$langs->getCurrencySymbol($conf->currency).'</td>';

								print '<td width="30%;">';
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
							$duree += ($calendrier->lines[$j]->heuref - $calendrier->lines[$j]->heured);

							print '</form>'."\n";
							print '</tr>'."\n";

						}

						// Fiels for new periodes
						if (!empty($newperiod))
						{
							print '<td align="right">';
							print '<form name="newperiod" action="'.$_SERVER['PHP_SELF'].'?action=edit_calendrier&id='.$id.'"  method="POST">'."\n";
							print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
							print '<input type="hidden" name="action" value="edit_calendrier">'."\n";
							print '<input type="hidden" name="newperiod" value="1">'."\n";
							print '<input type="submit" class="butAction" value="'.$langs->trans("AgfPeriodAdd").'">';
							print '</form>';
							print '</td>';
						}
						else
						{
							if($action =="edit_calendrier" && GETPOST('rowf') == $formateurs->lines[$i]->formid)
							{
								print '<form name="period_formateur_update_'.($i + 1).'" action="'.$_SERVER['PHP_SELF'].'?action=edit_calendrier&id='.$id.'"  method="POST">'."\n";
								print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
								print '<input type="hidden" name="action" value="edit_calendrier">'."\n";
								print '<input type="hidden" name="sessid" value="'.$agf->id.'">'."\n";
								print '<input type="hidden" name="fk_agefodd_session_formateur" value="'.$formateurs->lines[$i]->opsid.'">'."\n";
								print '<input type="hidden" name="periodid" value="'.$calendrier->lines[$j]->stagerowid.'">'."\n";
								print '<input type="hidden" id="datetmplday"   name="datetmplday"   value="'.dol_print_date($agf->dated, "%d").'">'."\n";
								print '<input type="hidden" id="datetmplmonth" name="datetmplmonth" value="'.dol_print_date($agf->dated, "%m").'">'."\n";
								print '<input type="hidden" id="datetmplyear"  name="datetmplyear"  value="'.dol_print_date($agf->dated, "%Y").'">'."\n";

								print '<tr>';

								print '<td  width="300px">';
								$form->select_date($agf->dated, 'date','','','','newperiod');
								print '</td>';
								print '<td width="400px">'.$langs->trans("AgfPeriodTimeB").' ';
								print $formAgefodd->select_time('08:00','dated');
								//print '</td>';
								//print '<td width="400px" >';
								print $langs->trans("AgfPeriodTimeE").' ';
								print $formAgefodd->select_time('18:00','datef');
								print '</td>';
								// Coût horaire
								print '<td width="20%"><input type="text" size="10" name="trainer_cost" /></td>';
								if ($user->rights->agefodd->modifier)
								{
									print '<td><input type="image" src="'.dol_buildpath('/agefodd/img/save.png',1).'" border="0" align="absmiddle" name="period_add" alt="'.$langs->trans("AgfModSave").'" "></td>';
								}

								print '</tr>'."\n";

								print '<tr><td colspan="4"><a href="'.$_SERVER['PHP_SELF'].'?id='.$agf->id.'">'.$langs->trans('Cancel').'</a></td></tr>';

								print '</form>';

							}
							else
							{
								print '<tr><td colspan="4"><a href="'.$_SERVER['PHP_SELF'].'?action=edit_calendrier&id='.$agf->id.'&amp;rowf='.$formateurs->lines[$i]->formid.'">'.$langs->trans('Edit').'</a></td></tr>';
							}
						}
						print '</table>';
						print '</td>';
					}
				}
				print "</tr>\n";
			}
		}
		print "</table>";
		print '</div>';
	}
}

/*
 * Action tabs
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && (!empty($agf->id)) && $nbform >= 1)
{
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
	}
}

print '</div>';

llxFooter();
$db->close();