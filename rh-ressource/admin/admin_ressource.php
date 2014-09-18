<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2013 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/mymodule.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

global $db;

// Libraries
dol_include_once('core/lib/admin.lib.php');
//require_once "../class/myclass.class.php";
// Translations
$langs->load("ressource@ressource");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

$action = $_REQUEST['action'];

/*echo "<pre>";
print_r($_REQUEST);
echo "</pre>";
exit;*/

switch ($action) {
	case 'save':
		
		$TNumerosSpeciaux = $_REQUEST['TNumerosSpeciaux'];
		
		if(_saveNumerosSpeciaux($db, $TNumerosSpeciaux)) {
			
			setEventMessage($langs->trans('NumerosSpeciauxSaved'));
			
		}
		break;
	
	default:
		
		break;
}
 
/*
 * View
 */ 

$TNumerosSpeciaux = unserialize(dolibarr_get_const($db, 'RESSOURCE_ARRAY_NUMEROS_SPECIAUX'));
//print_r($TFraisDePort);
 
$page_name = "NumerosSpeciauxSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Setup page goes here
//echo $langs->trans("FraisDePortSetup");

function _saveNumerosSpeciaux(&$db, $TNumerosSpeciaux) {
	
	$TNums = array();
	
	foreach($TNumerosSpeciaux as $num) {
		$num = _returnCleanedPhoneNumber($num);
		if(!empty($num)){
			$TNums[] = $num;
		}
	}
	
	return dolibarr_set_const($db, 'RESSOURCE_ARRAY_NUMEROS_SPECIAUX', serialize($TNums));
	
}

function _returnCleanedPhoneNumber($num) {
	
	if(empty($num)) return false;
	
	$num = strtr($num, array(
							"."=>""
							,"-"=>""
							,"/"=>""
							,","=>""
						));
	if(!is_numeric($num)) return false;
	if(strlen($num) > 11 || strlen($num) < 9) return false;
	//echo $num;exit;
	if(strlen($num) == "9") { // De la forme 6 ** ** ** **
		$num = str_pad($num, 11, 3, STR_PAD_LEFT);
		return $num;
	}
	if(strlen($num) == "10") { // De la forme 06 ** ** ** **
		$num = str_pad($num, 11, 3, STR_PAD_LEFT);
		$num[1] = 3;
		return $num;
	}
	
	return $num;
	
}

print '<form name="formFraisDePortLevel" method="POST" action="'.dol_buildpath('/ressource/admin/admin_ressource.php', 2).'" />';
print '<table class="noborder" width="100%">';
	
print '<tr class="liste_titre">';
print '<td>'.$langs->trans('SpecialNumbersList').'</td>';
print '</tr>';

print '<input type="hidden" name="action" value="save" />';

if(is_array($TNumerosSpeciaux) && count($TNumerosSpeciaux) > 0) {
	
	foreach($TNumerosSpeciaux as $numero) {
		
		print '<tr>';
		print '<td><input type="text" name="TNumerosSpeciaux[]" value="'.$numero.'" /></td>';
		print '</tr>';
		
	}	
	
}

print '<tr>';

print '<td><input type="text" name="TNumerosSpeciaux[]" /></td>';

print '</tr>';

print '</table>';

print '<div class="tabsAction"><input class="butAction" type="SUBMIT" name="subSaveNumerosSpeciaux" value="'.$langs->trans('SaveNumeros').'" /></div>';

print '</form>';

llxFooter();

$db->close();