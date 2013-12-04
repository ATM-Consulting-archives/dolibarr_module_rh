<?php
/** Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2007 Laurent Destailleur  <eldy@users.sourceforge.net>
* Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
* Copyright (C) 2013      Florian Henry		  	<florian.henry@open-concept.pro>
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
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *  \file       /agefodd/training/note.php
 *  \ingroup    agefodd
*  \brief      Note on Agefodd training
*/

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");
dol_include_once('/agefodd/lib/agefodd.lib.php');
dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');


$langs->load("companies");
$langs->load("bills");
$langs->load("orders");

$id = GETPOST('id','int');
$ref=GETPOST('ref','alpha');
$action=GETPOST('action','alpha');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();


$object = new Agefodd($db);
$result = $object->fetch($id, $ref);
if ($result<0)
{
	setEventMessage($object->error,'errors');
}

//Manage note right on this objects
$user->rights->agefodd_formation_catalogue->creer=$user->rights->agefodd->creer;


/*
 * Actions
*/

if ($action == 'setnote_public' && $user->rights->commande->creer)
{
	$object->fetch($id);
	$result=$object->update_note(dol_html_entity_decode(GETPOST('note_public'), ENT_QUOTES),'_public');
	if ($result < 0) setEventMessage($object->error,'errors');
}

else if ($action == 'setnote_private' && $user->rights->commande->creer)
{
	$object->fetch($id);
	$result=$object->update_note(dol_html_entity_decode(GETPOST('note_private'), ENT_QUOTES),'_private');
	if ($result < 0) setEventMessage($object->error,'errors');
}

/*
 * View
*/

$title = $langs->trans("AgfCatalogNote");
llxHeader('',$title);

$form = new Form($db);

if ($id > 0 || ! empty($ref))
{

	$head = training_prepare_head($object);

	dol_fiche_head($head, 'notes', $langs->trans("AgfCatalogNote"), 0, 'label');

	print '<table class="border" width="100%">';

	$object->printFormationInfo();

	print '<br>';

	include DOL_DOCUMENT_ROOT.'/core/tpl/notes.tpl.php';

	print '</div>';
}

llxFooter();
$db->close();