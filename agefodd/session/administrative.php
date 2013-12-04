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
 *	\file       agefodd/session/administrative.php
 *	\ingroup    agefodd
 *	\brief      administrative task of session
*/


$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_sessadm.class.php');
require_once('../class/agefodd_session_admlevel.class.php');
require_once('../class/agsession.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$actid=GETPOST('actid','int');

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_sessadm($db);
	$result = $agf->remove($actid);

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


/*
 * Action update
*/
if ($action == 'update' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"] && ! $_POST["delete"])
	{
		$agf = new Agefodd_sessadm($db);

		$result = $agf->fetch($actid);

		$agf->datea = dol_mktime(0,0,0,GETPOST('dateamonth','int'),GETPOST('dateaday','int'),GETPOST('dateayear','int'));
		$agf->dated = dol_mktime(0,0,0,GETPOST('dadmonth','int'),GETPOST('dadday','int'),GETPOST('dadyear','int'));
		$agf->datef = dol_mktime(0,0,0,GETPOST('dafmonth','int'),GETPOST('dafday','int'),GETPOST('dafyear','int'));
		$agf->notes = GETPOST('notes','alpha');
		$result = $agf->update($user);

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
	elseif ($_POST["delete"])
	{
		Header ( 'Location:'. $_SERVER['PHP_SELF'].'?id='.$id.'&action=edit&delete=1&actid='.$actid);
		exit;
	}
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}

if ($action == 'update_archive' && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_sessadm($db);

	$result = $agf->fetch($actid);
	if ($agf->archive==1) {
		$agf->archive=0;
	}
	else  {$agf->archive=1;
	}
	$agf->datef = dol_mktime(0,0,0,dol_print_date(dol_now(),'%m'),dol_print_date(dol_now(),'%d'),dol_print_date(dol_now(),'%Y'));
	$result = $agf->update($user);

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


/*
 * Action create
*/
if ($action == 'create_confirm' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"] )
	{
		$agf = new Agefodd_sessadm($db);

		$parent_level = GETPOST('action_level','int');

		$agf->fk_agefodd_session_admlevel = 0;
		$agf->fk_agefodd_session = $id;
		$agf->delais_alerte=0;
		$agf->archive=0;
		$agf->intitule = GETPOST('intitule','alpha');
		$agf->datea = dol_mktime(0,0,0,GETPOST('dateamonth','int'),GETPOST('dateaday','int'),GETPOST('dateayear','int'));
		$agf->dated = dol_mktime(0,0,0,GETPOST('dadmonth','int'),GETPOST('dadday','int'),GETPOST('dadyear','int'));
		$agf->datef = dol_mktime(0,0,0,GETPOST('dafmonth','int'),GETPOST('dafday','int'),GETPOST('dafyear','int'));
		$agf->notes = GETPOST('notes','alpha');

		//Set good indice and level rank
		if (!empty($parent_level))
		{
			$agf->fk_parent_level = $parent_level;

			$agf_static = new Agefodd_sessadm($db);
			$result_stat = $agf_static->fetch($parent_level);

			if ($result_stat > 0)
			{
				if (!empty($agf_static->id))
				{
					$agf->level_rank = $agf_static->level_rank + 1;
					$agf->indice = ebi_get_next_indice_action($agf_static->id,$id);
				}
				else
				{	//no parent : This case may not occur but we never know
					$agf->indice = (ebi_get_level_number($id) + 1) . '00';
					$agf->level_rank = 0;
				}
			}
			else
			{
				setEventMessage($agf_static->error,'errors');
			}
		}
		else
		{
			//no parent
			$agf->fk_parent_level = 0;
			$agf->indice = (ebi_get_level_number($id) + 1) . '00';
			$agf->level_rank = 0;
		}

		$result = $agf->create($user);

		if ($result < 0)
		{
			setEventMessage($agf->error,'errors');
		}
		else
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
			exit;
		}
	}
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}


/*
 * View
*/

llxHeader('',$langs->trans("AgfSessionDetail"));

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

if ($user->rights->agefodd->creer)
{
	// Display administrative task
	if ($id)
	{
		// View mode
		$agf_session = new Agsession($db);
		$res = $agf_session->fetch($id);

		$head = session_prepare_head($agf_session);
			
		dol_fiche_head($head, 'administrative', $langs->trans("AgfSessionDetail"), 0, 'bill');

		$agf = new Agefodd_sessadm($db);


		// Creation card
		if ($action == 'create')
		{
			print '<form name="create_confirm" action="administrative.php" method="post">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="create_confirm">'."\n";
			print '<input type="hidden" name="id" value="'.$id.'">'."\n";

			print '<table class="border" width="100%">';

			print '<tr><td>'.$langs->trans("AgfSessAdmIntitule").'</td>';
			print '<td><input name="intitule" class="flat" size="50" value=""/></td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfParentLevel").'</td>';
			print '<td>'.$formAgefodd->select_action_session($id).'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfSessAdmDateLimit").'</td><td>';
			$form->select_date('','datea','','','','create_confirm');
			print '</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfSessDateDebut").'</td><td>';
			$form->select_date('', 'dad','','','','create_confirm');
			print '</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfSessDateFin").'</td><td>';
			$form->select_date('', 'daf','','','','create_confirm');
			print '</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
			print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';

			print '</table>';
			print '</div>';

			print '<table style=noborder align="right">';
			print '<tr><td align="center" colspan=2>';
			print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
			print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'"> &nbsp; ';
			print '</td></tr>';

			print '</table>';
			print '</form>';
		}
		// Display edit mode
		elseif ($action == 'edit')
		{
			$result = $agf->fetch($actid);

			/*
			 * Delete confirm
			*/
			if (GETPOST('delete','int') == '1')
			{
				$ret = $form->form_confirm("administrative.php?id=".$id."&actid=".$actid, $langs->trans("AgfDeleteOps"),$langs->trans("AgfConfirmDeleteAction"),"confirm_delete",'','',1);
				if ($ret == 'html') print '<br>';
			}
			print '<form name="update" action="administrative.php" method="post">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="update">'."\n";
			print '<input type="hidden" name="id" value="'.$id.'">'."\n";
			print '<input type="hidden" name="actid" value="'.$agf->id.'">'."\n";

			print '<table class="border" width="100%">';

			print "<tr>";
			print '<td td width="300px">'.$langs->trans("Ref").'</td><td>'.$agf->id.'</td></tr>';

			print '<tr><td>'.$langs->trans("AgfSessAdmIntitule").'</td>';
			print '<td>'.$agf->intitule.'</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfSessAdmDateLimit").'</td><td>';
			$form->select_date($agf->datea,'datea','','','','update');
			print '</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfSessDateDebut").'</td><td>';
			$form->select_date($agf->dated, 'dad','','','','update');
			print '</td></tr>';
			print '<tr><td valign="top">'.$langs->trans("AgfSessDateFin").'</td><td>';
			$form->select_date($agf->datef, 'daf','','','','update');
			print '</td></tr>';

			print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
			print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->notes.'</textarea></td></tr>';

			print '</table>';
			print '</div>';

			print '<table style=noborder align="right">';
			print '<tr><td align="center" colspan=2>';
			print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
			print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'"> &nbsp; ';
			print '<input type="submit" name="delete" class="butActionDelete" value="'.$langs->trans("Delete").'">';
			print '</td></tr>';

			print '</table>';
			print '</form>';

		}
		else
		{
			// Display view mode
			$sess_adm = new Agefodd_sessadm($db);
			$result = $sess_adm->fetch_all($id);

			print '<div width=100% align="center" style="margin: 0 0 3px 0;">';
			print $formAgefodd->level_graph(ebi_get_adm_lastFinishLevel($id), ebi_get_level_number($id), $langs->trans("AgfAdmLevel"));
			print '</div>';

			print '<table width="100%" class="border">';

			if ($result)
			{

				$i=0;
				foreach ($sess_adm->lines as $line)
				{

					if ($line->level_rank == '0' && $i!=0)
					{
						print '<tr  style="border-style:none"><td colspan="6" style="border-style:none">&nbsp;</td></tr>';
					}

					if ($line->level_rank == '0')
					{

						print '<tr align="center" style="border-style:none">';
						print '<td colspan="3" style="border-style:none">&nbsp;</td>';
						print '<td width="150px" style="border-style:none">'.$langs->trans("AgfLimitDate").'</td>';
						print '<td width="150px" style="border-style:none">'.$langs->trans("AgfDateDebut").'</td>';
						print '<td width="150px" style="border-style:none">'.$langs->trans("AgfDateFin").'</td>';
						print '<td style="border-style:none"></td>';
						print '</tr>';
							
					}
					print '<tr style="color:#000000;border:1px;border-style:solid">';

					$bgcolor = '#d5baa8'; //Default color


					//8 day before alert date
					if (dol_now() > dol_time_plus_duree($line->datea,-8,'d')) $bgcolor = '#ffe27d';

					//3 day before alert day
					if (dol_now() > dol_time_plus_duree($line->datea,-3,'d')) $bgcolor = 'orange';

					// if alert date is past then RED
					if (dol_now() > $line->datea) $bgcolor = 'red';

					//if end date is in the past adn task is mark as done , the task is done
					if ((dol_now() > $line->datef) && (!empty($line->archive))) $bgcolor='green';
					//if end date is in the past, the task is done
					if ((dol_now() > $line->datef) && (empty($line->archive))) $bgcolor='red';


					print '<td width="10px" bgcolor="'.$bgcolor.'">&nbsp;</td>';

					print '<td style="border-right-style: none;"><a href="'.dol_buildpath('/agefodd/session/administrative.php',1).'?action=edit&id='.$id.'&actid='.$line->id.'">';
					print str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$line->level_rank).$line->intitule.'</a></td>';

					// Affichage éventuelle des notes
					if (!empty($line->notes))
					{
						print '<td class="adminaction" style="border-left-style: none; width: auto; text-align: right" valign="top">';
						print '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/recent.png" border="0" align="absmiddle" hspace="6px" >';
						print '<span>'.wordwrap(stripslashes($line->notes),50,"<br />",1).'</span></td>';
					}
					else print '<td style="border-left: 0px; width:auto;">&nbsp;</td>';

					// Affichage des différentes dates
					print '<td width="150px" align="center" valign="top">';
					if ($bgcolor == 'red') print '<font style="color:'.$bgcolor.'">';
					print dol_print_date($line->datea,'daytext');
					if ($bgcolor == 'red') print '</font>';
					print '</td>';
					print '<td width="150px" align="center" valign="top">'.dol_print_date($line->dated,'daytext').'</td>';
					print '<td width="150px" align="center" valign="top">'.dol_print_date($line->datef,'daytext').'</td>';

					//Status Line
					if ($line->archive)
					{
						$txtalt=$langs->trans("AgfTerminatedNoPoint");
						$src_state=dol_buildpath('/agefodd/img/undo.png',1);
					}
					else
					{
						$txtalt=$langs->trans("AgfTerminatedPoint");
						$src_state=dol_buildpath('/agefodd/img/ok.png',1);
					}

					print '<td align="center" valign="top"><a href="'.$_SERVER['PHP_SELF'].'?action=update_archive&id='.$id.'&actid='.$line->id.'"><img alt="'.$txtalt.'" src="'.$src_state.'"/></a></td>';

					print '</tr>';

					$i++;
				}
			}


			print '</table>';
			print '&nbsp;';

			print '<table align="center" noborder><tr>';
			print '<td width="10px" bgcolor="green"><td>'.$langs->trans("AgfTerminatedPoint").'&nbsp</td>';
			print '<td width="10px" bgcolor="#ffe27d"><td>'.$langs->trans("AgfXDaysBeforeAlert").'&nbsp;</td>';
			print '<td width="10px" bgcolor="orange"><td>'.$langs->trans("AgfYDaysBeforeAlert").'&nbsp</td>';
			print '<td width="10px" bgcolor="red"><td>'.$langs->trans("AgfAlertDay").'&nbsp</td>';
			print '</tr></table>';

			print '</div>';
		}
	}
}


/*
 * Action tabs
*
*/


print '<div class="tabsAction">';


if ($action != 'create' && $action != 'edit' && $action != 'update')
{
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create&id='.$id.'">'.$langs->trans('Create').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
	}
}

print '</div>';

llxFooter();
$db->close();