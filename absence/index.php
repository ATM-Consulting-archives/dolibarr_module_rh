<?php


require('config.php');
require('./lib/absence.lib.php');
require('./class/absence.class.php');

$langs->load('absence@absence');
//require_once(DOL_DOCUMENT_ROOT."/main.inc.php");
//require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
//require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");

llxHeader();



/*
$form = new Form($db);
$formother = new FormOther($db);
*/


$absence = new TRH_Absence;
print dol_get_fiche_head(absencePrepareHead($absence, 'index')  , 'calendrier', $langs->trans('Absence'));




llxfooter();
