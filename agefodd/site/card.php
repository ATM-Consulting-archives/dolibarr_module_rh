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
 *	\file       agefodd/site/card.php
 *	\ingroup    agefodd
 *	\brief      card of location
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once('../class/agefodd_place.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$langs->load('agefodd@agefodd');
$langs->load('companies');

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$arch=GETPOST('arch','int');

$url_return=GETPOST('url_return','alpha');

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_place($db);
	$agf->id=$id;
	$result = $agf->remove($user);

	if ($result > 0)
	{
		Header ( "Location: list.php");
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
		$agf = new Agefodd_place($db);

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
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}

/*
 * Action update (Location)
*/
if ($action == 'update' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"] && ! $_POST["importadress"])
	{
		$agf = new Agefodd_place($db);

		$result = $agf->fetch($id);
		if($result > 0)
		{
			$agf->ref_interne = GETPOST('ref_interne','alpha');
			$agf->adresse = GETPOST('adresse','alpha');
			$agf->cp = GETPOST('zipcode','alpha');
			$agf->ville = GETPOST('town','alpha');
			$agf->fk_pays = GETPOST('country_id','int');
			$agf->tel = GETPOST('phone','alpha');
			$agf->fk_societe = GETPOST('societe','int');
			$agf->notes = GETPOST('notes');
			$agf->acces_site = GETPOST('acces_site');
			$agf->note1 = GETPOST('note1');
			$result = $agf->update($user);

			if ($result > 0)
			{
				Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
				exit;
			}
			else
			{
				setEventMessage($agf->error,'errors');
				$action = 'edit';
			}
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}

	}
	elseif (! $_POST["cancel"] && $_POST["importadress"])	{

		$agf = new Agefodd_place($db);

		$result = $agf->fetch($id);
		$result = $agf->import_customer_adress($user);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$id);
			exit;
		}
		else
		{
			setEventMessage($agf->error,'errors');
		}

	}else {
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}


/*
 * Action create (Location)
*/

if ($action == 'create_confirm' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_place($db);

		$agf->ref_interne = GETPOST('ref_interne','alpha');
		$agf->fk_societe = GETPOST('societe','int');
		$agf->notes = GETPOST('notes','alpha');
		$agf->acces_site = GETPOST('acces_site','alpha');
		$agf->note1 = GETPOST('note1','alpha');
		$result = $agf->create($user);

		if ($result > 0)
		{
			if($url_return)
				Header ( "Location: ".$url_return);
			else
				Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$result);
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

$title = ($action == 'create' ? $langs->trans("AgfCreatePlace") : $langs->trans("AgfSessPlace"));
llxHeader('',$title);

$form = new Form($db);

/*
 * Action create
*/
if ($action == 'create' && $user->rights->agefodd->creer)
{
	$formcompany = new FormCompany($db);
	print_fiche_titre($langs->trans("AgfCreatePlace"));

	print '<form name="create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="create_confirm">'."\n";

	print '<input type="hidden" name="url_return" value="'.$url_return.'">'."\n";

	print '<table class="border" width="100%">'."\n";

	print '<tr><td width="20%"><span class="fieldrequired">'.$langs->trans("AgfSessPlaceCode").'</span></td>';
	print '<td><input name="ref_interne" class="flat" size="50" value=""></td></tr>';

	print '<tr><td><span class="fieldrequired">'.$langs->trans("Company").'</span></td>';
	print '<td>'.$form->select_company('','societe','((s.client IN (1,2)) OR (s.fournisseur=1))',1,1,0).'</td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
	print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfAccesSite").'</td>';
	print '<td><textarea name="acces_site" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfPlaceNote1").'</td>';
	print '<td><textarea name="note1" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';
	print '</table>';
	print '</div>';


	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" name="importadress" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';
	print '</table>';
	print '</form>';

}
else
{
	// Card
	if ($id)
	{
		$agf = new Agefodd_place($db);
		$result = $agf->fetch($id);

		if ($result>0)
		{
			$head = site_prepare_head($agf);

			dol_fiche_head($head, 'card', $langs->trans("AgfSessPlace"), 0, 'address');

			// Card in edit mode
			if ($action == 'edit')
			{
				$formcompany = new FormCompany($db);

				print '<form name="update" action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
				print '<input type="hidden" name="action" value="update">'."\n";
				print '<input type="hidden" name="id" value="'.$id.'">'."\n";

				print '<table class="border" width="100%">'."\n";
				print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
				print '<td>'.$agf->id.'</td></tr>';

				print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
				print '<td><input name="ref_interne" class="flat" size="50" value="'.$agf->ref_interne.'"></td></tr>';

				print '<tr><td>'.$langs->trans("Company").'</td>';
				print '<td>'.$form->select_company($agf->socid,'societe','((s.client IN (1,2)) OR (s.fournisseur=1))',0,1).'</td></tr>';

				print '<tr><td>'.$langs->trans("Address").'</td>';
				print '<td><input name="adresse" class="flat" size="50" value="'.$agf->adresse.'"></td></tr>';


				print '<tr><td>'.$langs->trans('Zip').'</td><td>';
				print $formcompany->select_ziptown($agf->cp,'zipcode',array('town','selectcountry_id'),6).'</tr>';
				print '<tr></td><td>'.$langs->trans('Town').'</td><td>';
				print $formcompany->select_ziptown($agf->ville,'town',array('zipcode','selectcountry_id')).'</tr>';

				print '<tr><td>'.$langs->trans("Country").'</td>';
				print '<td>'.$form->select_country($agf->country,'country_id').'</td></tr>';

				print '<tr><td>'.$langs->trans("Phone").'</td>';
				print '<td><input name="phone" class="flat" size="50" value="'.$agf->tel.'"></td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
				print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->notes.'</textarea></td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfAccesSite").'</td>';
				print '<td><textarea name="acces_site" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->acces_site.'</textarea></td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfPlaceNote1").'</td>';
				print '<td><textarea name="note1" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->note1.'</textarea></td></tr>';

				print '</table>';
				print '</div>';
				print '<table style=noborder align="right">';
				print '<tr><td align="center" colspan=2>';
				print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
				print '<input type="submit" name="importadress" class="butAction" value="'.$langs->trans("AgfImportCustomerAdress").'"> &nbsp; ';
				print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
				print '</td></tr>';
				print '</table>';
				print '</form>';

				print '</div>'."\n";
			}
			else
			{
				// Display View mode

				/*
				 * Confirm delete
				*/
				if ($action == 'delete')
				{
					$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("AgfDeletePlace"),$langs->trans("AgfConfirmDeletePlace"),"confirm_delete",'','',1);
					if ($ret == 'html') print '<br>';
				}
				/*
				 * Confirm archive
				*/
				if ($action=='archive' || $action=='active')
				{
					if ($action == 'archive') $value=1;
					if ($action == 'active') $value=0;

					$ret=$form->form_confirm($_SERVER['PHP_SELF']."?arch=".$value."&id=".$id,$langs->trans("AgfFormationArchiveChange"),$langs->trans("AgfConfirmArchiveChange"),"arch_confirm_delete",'','',1);
					if ($ret == 'html') print '<br>';
				}

				print '<table class="border" width="100%">';

				print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
				print '<td>'.$form->showrefnav($agf,'id	','',1,'rowid','id').'</td></tr>';

				print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
				print '<td>'.$agf->ref_interne.'</td></tr>';

				print '<tr><td valign="top">'.$langs->trans("Company").'</td><td>';
				if ($agf->socid)
				{
					print '<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$agf->socid.'">';
					print img_object($langs->trans("ShowCompany"),"company").' '.dol_trunc($agf->socname,20).'</a>';
				}
				else
				{
					print '&nbsp;';
				}
				print '</tr>';

				print '<tr><td rowspan=3 valign="top">'.$langs->trans("Address").'</td>';
				print '<td>'.$agf->adresse.'</td></tr>';

				print '<tr>';
				print '<td>'.$agf->cp.' - '.$agf->ville.'</td></tr>';

				print '<tr>';
				print '<td>';
				$img=picto_from_langcode($agf->country_code);
				if ($agf->isInEEC()) print $form->textwithpicto(($img?$img.' ':'').$agf->country,$langs->trans("CountryIsInEEC"),1,0);
				else print ($img?$img.' ':'').$agf->country;
				print '</td></tr>';

				print '</td></tr>';

				print '<tr><td>'.$langs->trans("Phone").'</td>';
				print '<td>'.dol_print_phone($agf->tel).'</td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfNotes").'</td>';
				print '<td>'.nl2br($agf->notes).'</td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfAccesSite").'</td>';
				print '<td>'.nl2br($agf->acces_site).'</td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfPlaceNote1").'</td>';
				print '<td>'.nl2br($agf->note1).'</td></tr>';

				print "</table>";

				print '</div>';
			}

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