<?php
/** Copyright (C) 2013       Florian Henry  	<florian.henry@open-concept.pro>
 *
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
 * 	\file       /agefodd/training/training_adm.php
 *	\ingroup    agefodd
 *	\brief      agefood agefodd admin training task by trainig
 */

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once('../class/agefodd_session_admlevel.class.php');
require_once('../class/agefodd_training_admlevel.class.php');
require_once('../class/agefodd_formation_catalogue.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

$langs->load("admin");
$langs->load('agefodd@agefodd');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$action = GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$trainingid=GETPOST('trainingid','int');
$parent_level = GETPOST('parent_level','int');

if (empty($trainingid)) {
	$trainingid=$id;
}

if ($action == 'sessionlevel_create')
{
	$agf = new Agefodd_training_admlevel($db);

	if (!empty($parent_level))
	{
		$agf->fk_parent_level = $parent_level;

		$agf_static = new Agefodd_training_admlevel($db);
		$result_stat = $agf_static->fetch($agf->fk_parent_level);

		if ($result_stat > 0)
		{
			if (!empty($agf_static->id))
			{
				$agf->level_rank = $agf_static->level_rank + 1;
				$agf->indice = ebi_get_adm_training_get_next_indice_action($agf_static->id);
			}
			else
			{	//no parent : This case may not occur but we never know
				$agf->indice = (ebi_get_adm_training_level_number() + 1) . '00';
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
		$agf->indice = (ebi_get_adm_training_level_number() + 1) . '00';
		$agf->level_rank = 0;
	}

	$agf->fk_training = $trainingid;
	$agf->intitule = GETPOST('intitule','alpha');
	$agf->delais_alerte = GETPOST('delai','int');

	if ($agf->level_rank>3)
	{
		setEventMessage($langs->trans("AgfAdminNoMoreThan3Level"),'errors');
	}
	else
	{
		$result = $agf->create($user);

		if ($result1!=1)
		{
			setEventMessage($agf->error,'errors');
		}
	}


}

if ($action == 'sessionlevel_update')
{
	$agf = new Agefodd_training_admlevel($db);

	$result = $agf->fetch($id);

	if ($result > 0)
	{

		//Up level of action
		if (GETPOST('sesslevel_up_x'))
		{
			$result2 = $agf->shift_indice($user,'less');
			if ($result1!=1)
			{
				setEventMessage($agf->error,'errors');
			}
		}

		//Down level of action
		if (GETPOST('sesslevel_down_x'))
		{
			$result1 = $agf->shift_indice($user,'more');
			if ($result1!=1)
			{
				setEventMessage($agf->error,'errors');
			}
		}

		//Update action
		if (GETPOST('sesslevel_update_x'))
		{
			$agf->intitule = GETPOST('intitule','alpha');
			$agf->delais_alerte = GETPOST('delai','int');

			if (!empty($parent_level))
			{
				if ($parent_level!=$agf->fk_parent_level)
				{
					$agf->fk_parent_level = $parent_level;

					$agf_static = new Agefodd_training_admlevel($db);
					$result_stat = $agf_static->fetch($agf->fk_parent_level);

					if ($result_stat > 0)
					{
						if (!empty($agf_static->id))
						{
							$agf->level_rank = $agf_static->level_rank + 1;
							$agf->indice = ebi_get_adm_training_get_next_indice_action($agf_static->id);
						}
						else
						{	//no parent : This case may not occur but we never know
							$agf->indice = (ebi_get_adm_training_level_number() + 1) . '00';
							$agf->level_rank = 0;
						}
					}
					else
					{
						setEventMessage($agf_static->error,'errors');
					}
				}
			}
			else
			{
				//no parent
				$agf->fk_parent_level = 0;
				$agf->level_rank = 0;
			}

			if ($agf->level_rank>3)
			{
				setEventMessage($langs->trans("AgfAdminNoMoreThan3Level"),'errors');
			}
			else
			{
				$result1 = $agf->update($user);
				if ($result1!=1)
				{
					setEventMessage($agf_static->error,'errors');
				}
			}
		}

		//Delete action
		if (GETPOST('sesslevel_remove_x'))
		{

			$result = $agf->delete($user);
			if ($result!=1)
			{
				setEventMessage($agf_static->error,'errors');
			}
		}
	}
	else
	{
		setEventMessage('This action do not exists','errors');
	}
}



/*
 * View
*/
$title =  $langs->trans("AgfCatalogAdminTask");
llxHeader('',$title);

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

$agf = new Agefodd($db);
$result = $agf->fetch($trainingid);

$head = training_prepare_head($agf);

dol_fiche_head($head, 'trainingadmtask', $langs->trans("AgfCatalogDetail"), 0, 'label');

$admlevel = new Agefodd_training_admlevel($db);
$result0 = $admlevel->fetch_all($trainingid);


print_titre($langs->trans("AgfAdminTrainingLevel"));

if ($result0>0)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td width="10px"></td>';
	print '<td>'.$langs->trans("AgfIntitule").'</td>';
	print '<td>'.$langs->trans("AgfParentLevel").'</td>';
	print '<td>'.$langs->trans("AgfDelaiSessionLevel").'</td>';
	print '<td></td>';
	print "</tr>\n";

	$var=true;
	foreach ($admlevel->lines as $line)
	{
		$var=!$var;
		$toplevel='';
		print '<form name="SessionLevel_update_'.$line->rowid.'" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
		print '<input type="hidden" name="id" value="'.$line->rowid.'">'."\n";
		print '<input type="hidden" name="action" value="sessionlevel_update">'."\n";
		print '<input type="hidden" name="trainingid" value="'.$trainingid.'">'."\n";
		print '<tr '.$bc[$var].'>';

		print '<td>';
		if ($line->indice!=ebi_get_adm_training_indice_per_rank($line->level_rank,$line->fk_parent_level,'MIN'))
		{
			print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/1uparrow.png" border="0" name="sesslevel_up" alt="'.$langs->trans("Save").'">';
		}
		if ($line->indice!=ebi_get_adm_training_indice_per_rank($line->level_rank,$line->fk_parent_level,'MAX'))
		{
			print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/1downarrow.png" border="0" name="sesslevel_down" alt="'.$langs->trans("Save").'">';
		}
		print '</td>';

		print '<td>'.str_repeat('&nbsp;&nbsp;&nbsp;',$line->level_rank).'<input type="text" name="intitule" value="'.$line->intitule.'" size="30"/></td>';
		print '<td>'.$formAgefodd->select_action_training_adm($line->fk_parent_level,'parent_level',$line->rowid).'</td>';
		print '<td><input type="text" name="delai" value="'.$line->alerte.'"/></td>';
		print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" name="sesslevel_update" alt="'.$langs->trans("Save").'">';
		print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="sesslevel_remove" alt="'.$langs->trans("Delete").'"></td>';
		print '</tr>';
		print '</form>';
	}
}
print '<form name="SessionLevel_create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
print '<input type="hidden" name="action" value="sessionlevel_create">'."\n";
print '<input type="hidden" name="trainingid" value="'.$trainingid.'">'."\n";
print '<tr>';
print '<td></td>';
print '<td><input type="text" name="intitule" value="" size="30"/></td>';
print '<td>'.$formAgefodd->select_action_training_adm('','parent_level').'</td>';
print '<td><input type="text" name="delai" value=""/></td>';
print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit_add.png" border="0" name="sesslevel_update" alt="'.$langs->trans("Save").'"></td>';
print '</tr>';
print '</form>';
print '</table><br>';

llxFooter();
$db->close();