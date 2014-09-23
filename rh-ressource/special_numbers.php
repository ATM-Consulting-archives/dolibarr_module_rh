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
/*$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}
if (! $res) {
    $res = @include("../../../../main.inc.php"); // From "custom" directory
}
if (! $res) {
    $res = @include("../../../../../main.inc.php"); // From "custom" directory
}
*/
global $db,$user;

// Libraries
require('config.php');
dol_include_once('/core/lib/admin.lib.php');
dol_include_once('/ressource/class/numeros_speciaux.class.php');
//require_once "../class/myclass.class.php";
// Translations
$langs->load("ressource@ressource");

$ATMdb = new TPDOdb;

// Access control
if (! $user->admin) {
    accessforbidden();
}

if(!$user->rights->ressource->ressource->accessSpecialNumbers)
	accessforbidden();

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
		
		if(_saveNumerosSpeciaux($ATMdb, $TNumerosSpeciaux)) {
			
			setEventMessage($langs->trans('NumerosSpeciauxSaved'));
			
		}
		break;
	
	case 'delete':
	
		TRH_Numero_special::deleteNumber($db, $_REQUEST['number']);
	
	default:
		
		break;
}
 
/*
 * View
 */ 
$TNumerosSpeciaux = TRH_Numero_special::getAllNumbers($db);
//print_r($TFraisDePort);
 
$page_name = "NumerosSpeciauxSetup";
llxHeader('', $langs->trans($page_name));

print_fiche_titre($langs->trans($page_name), $linkback);

// Setup page goes here
//echo $langs->trans("FraisDePortSetup");

function _saveNumerosSpeciaux(&$ATMdb, $TNumerosSpeciaux) {
		
	global $db;
	
	$TNums = array();
	
	foreach($TNumerosSpeciaux as $num) {
		$num = _returnCleanedPhoneNumber($num);
		if(!empty($num) && !TRH_Numero_special::existeNumber($db, $num)){
			$number = new TRH_Numero_special;
			$number->numero = $num;
			$number->save($ATMdb);
		}
	}
	
	return 1;
	
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

print '<form name="formNumerosSpeciaux" method="POST" action="'.dol_buildpath('/ressource/special_numbers.php', 2).'" />';
print '<table class="noborder" width="100%">';
	
print '<tr class="liste_titre">';
print '<td>'.$langs->trans('SpecialNumbersList').'</td>';
print '</tr>';

print '<input type="hidden" name="action" value="save" />';

if(is_array($TNumerosSpeciaux) && count($TNumerosSpeciaux) > 0) {
	
	foreach($TNumerosSpeciaux as $numero) {
		
		print '<tr>';
		print '<td><input type="text" name="TNumerosSpeciaux[]" value="'.$numero.'" /></td>';
		print '<td><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?number='.$numero.'&action=delete" />Supprimer numéro</a></td>';
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