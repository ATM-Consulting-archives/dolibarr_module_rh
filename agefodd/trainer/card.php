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
 *	\file       agefodd/traineer/card.php
 *	\ingroup    agefodd
 *	\brief      card of traineer
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_formateur.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../lib/agefodd.lib.php');


$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$arch=GETPOST('arch','int');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();



/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_teacher($db);
	$result = $agf->remove($id);

	if ($result > 0)
	{
		Header ( "Location: list.php");
		exit;
	}
	else
	{
		setEventMessage($langs->trans("AgfDeleteFormErr").':'.$agf->error,'errors');
	}
}

/*
 * Actions archive/active
*/
if ($action == 'arch_confirm_delete' && $user->rights->agefodd->creer && $confirm == "yes")
{
	$agf = new Agefodd_teacher($db);

	$result = $agf->fetch($id);

	$agf->archive = $arch;
	$result = $agf->update($user);

	if ($result > 0)
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
	else
	{
		setEventMessage($agf->error,'errors');
	}
}


/*
 * Action create from contact (card trainer : CARREFULL, Dolibarr contact must exists)
*/

if ($action == 'create_confirm_contact' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_teacher($db);

		$agf->spid = GETPOST('spid');
		$agf->type_trainer = $agf->type_trainer_def[1];
		$result = $agf->create($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$result);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}
	else
	{
		Header ( "Location: list.php");
		exit;
	}
}


/*
 * Action create from users (card trainer : CARREFULL, Dolibarr users must exists)
*/

if ($action == 'create_confirm_user' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_teacher($db);

		$agf->fk_user = GETPOST('fk_user','int');
		$agf->type_trainer = $agf->type_trainer_def[0];
		$result = $agf->create($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$result);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}
	else
	{
		Header ( "Location: list.php");
		exit;
	}
}


/*
 * View
*/
$title = ($action == 'create' ? $langs->trans("AgfFormateurAdd") : $langs->trans("AgfTeacher"));
llxHeader('',$title);

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

/*
 * Action create
*/
if ($action == 'create' && $user->rights->agefodd->creer)
{
	print_fiche_titre($langs->trans("AgfFormateurAdd"));

	print '<form name="create_contact" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="create_confirm_contact">'."\n";

	print '<div class="warning">'.$langs->trans("AgfFormateurAddContactHelp");
	print '<br>'.$langs->trans("AgfFormateurAddContactHelp1").' <a href="'.DOL_URL_ROOT.'/contact/fiche.php?action=create">'.$langs->trans("AgfFormateurAddContactHelp2").'</a>. '.$langs->trans("AgfFormateurAddContactHelp3").'</div>';

	print '<table class="border" width="100%">'."\n";

	print '<tr><td>'.$langs->trans("AgfContact").'</td>';
	print '<td>';

	$agf_static = new Agefodd_teacher($db);
	$agf_static->fetch_all('ASC','s.lastname, s.firstname','',0);
	$exclude_array = array();
	if (is_array($agf_static->lines) && count($agf_static->lines) > 0)
	{
		foreach($agf_static->lines as $line)
		{
			if (!empty($line->fk_socpeople)) {
				$exclude_array[]=$line->fk_socpeople;
			}
		}
	}
	$form->select_contacts(0,'','spid',1,$exclude_array,'',1,'',1);
	print '</td></tr>';

	print '</table>';


	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';
	print '</table>';
	print '</form>';

	print '<br>';
	print '<br>';
	print '<br>';

	print '<form name="create_user" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="create_confirm_user">'."\n";

	print '<div class="warning">'.$langs->trans("AgfFormateurAddUserHelp");
	print '<br>'.$langs->trans("AgfFormateurAddUserHelp1").' <a href="'.DOL_URL_ROOT.'/user/fiche.php?action=create">'.$langs->trans("AgfFormateurAddUserHelp2").'</a>. '.$langs->trans("AgfFormateurAddUserHelp3").'</div>';

	print '<table class="border" width="100%">'."\n";

	print '<tr><td>'.$langs->trans("AgfUser").'</td>';
	print '<td>';

	$agf_static = new Agefodd_teacher($db);
	$agf_static->fetch_all('ASC','s.lastname, s.firstname','',0);
	$exclude_array = array();
	if (is_array($agf_static->lines) && count($agf_static->lines) > 0)
	{
		foreach($agf_static->lines as $line)
		{
			if ((!empty($line->fk_user)) && (!in_array($line->fk_user,$exclude_array))){
				$exclude_array[]=$line->fk_user;
			}
		}
	}
	$form->select_users('','fk_user',1,$exclude_array);
	print '</td></tr>';

	print '</table>';



	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';
	print '</table>';
	print '</form>';

	print '</div>';
}
else
{
	// Display trainer card
	if ($id)
	{
		$agf = new Agefodd_teacher($db);
		$result = $agf->fetch($id);

		if ($result)
		{
			if ($mesg) print $mesg."<br>";

			// View mode

			$head = trainer_prepare_head($agf);

			dol_fiche_head($head, 'card', $langs->trans("AgfTeacher"), 0, 'user');

			/*
			 * Delete confirm
			*/
			if ($action == 'delete')
			{
				$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("AgfDeleteTeacher"),$langs->trans("AgfConfirmDeleteTeacher"),"confirm_delete",'','',1);
				if ($ret == 'html') print '<br>';
			}

			/*
			 * Confirm archive status change
			*/
			if ($action == 'archive' || $action == 'active')
			{
				if ($action == 'archive') $value=1;
				if ($action == 'active') $value=0;

				$ret=$form->form_confirm($_SERVER['PHP_SELF']."?arch=".$value."&id=".$id,$langs->trans("AgfFormationArchiveChange"),$langs->trans("AgfConfirmArchiveChange"),"arch_confirm_delete",'','',1);
				if ($ret == 'html') print '<br>';
			}

			print '<table class="border" width="100%">';

			print '<tr><td width="20%">'.$langs->trans("Ref").'</td>';
			print '<td>'.$form->showrefnav($agf,'id','',1,'rowid','id').'</td></tr>';

			print '<tr><td>'.$langs->trans("Name").'</td>';
			print '<td>'.ucfirst(strtolower($agf->civilite)).' '.strtoupper($agf->name).' '.ucfirst(strtolower($agf->firstname)).'</td></tr>';


			print "</table>";

			print '</div>';
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}
	}
}


/*
 * Actions tabs
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'nfcontact')
{
	if ($agf->type_trainer==$agf->type_trainer_def[1]) {
		if ($user->rights->societe->contact->creer)
		{
			print '<a class="butAction" href="'.DOL_URL_ROOT.'/contact/fiche.php?id='.$agf->spid.'">'.$langs->trans('AgfModifierFicheContact').'</a>';
		}
		else
		{
			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfModifierFicheContact').'</a>';
		}
	}
	elseif ($agf->type_trainer==$agf->type_trainer_def[0]) {
		if ($user->rights->user->user->creer)
		{
			print '<a class="butAction" href="'.DOL_URL_ROOT.'/user/fiche.php?id='.$agf->fk_user.'">'.$langs->trans('AgfModifierFicheUser').'</a>';
		}
		else
		{
			print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfModifierFicheUser').'</a>';
		}
	}

	if ($user->rights->agefodd->creer)
	{
		print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
	}

	if ($user->rights->agefodd->modifier)
	{
		if ($agf->archive == 0)
		{
			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=archive&id='.$id.'">'.$langs->trans('AgfArchiver').'</a>';
		}
		else
		{
			print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=active&id='.$id.'">'.$langs->trans('AgfActiver').'</a>';
		}
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('AgfArchiver').'/'.$langs->trans('AgfActiver').'</a>';
	}

}

print '</div>';

llxFooter();
$db->close();