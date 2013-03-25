<?php

function absencePrepareHead(&$obj, $type='absence') {
	global $user;
	switch ($type) {
		case 'absence':
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/absence.php?id='.$obj->getId()."&action=view", 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/absence/calendrierAbsence.php?idUser='.$obj->fk_user.'&id='.$obj->getId(), 'Calendrier','calendrier')
				);
				break;
		case 'absenceCreation':
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/absence.php?action=new', 'Fiche','fiche')
				);
				break;
		
		
	}
}

function compteurPrepareHead(&$obj, $type='absence') {
	global $user;
	switch ($type) {
		
		case 'compteur':
				if($user->rights->absence->myactions->modifierParamGlobalConges=="1"){
					return array(
					array(DOL_URL_ROOT_ALT.'/absence/compteur.php', 'Compteur','compteur')
					,array(DOL_URL_ROOT_ALT.'/absence/adminCompteur.php?action=view', 'Administration générale congés','adminconges')
					);
					break;
				}else{
					return array(
					array(DOL_URL_ROOT_ALT.'/absence/compteur.php?id='.$obj->getId()."&action=view", 'Compteur','compteur')
					);
				}
				
	}
}

function edtPrepareHead(&$obj, $type='absence') {
	global $user;
	//print '(lol)';
	//print_r($obj);
	switch ($type) {
		
		case 'emploitemps':
				
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/emploitemps.php?id='.$obj->fk_user."&action=view", 'Emploi du temps','emploitemps')
				   ,array(DOL_URL_ROOT_ALT.'/absence/joursferies.php?id='.$obj->fk_user, 'Jours non travaillés','joursferies')
				);
				break;
				
	}
}

//fonction qui permet d'enregistrer le libellé d'une absence suivant son type
function saveLibelle($type){
	switch($type){
		case 'rttcumule':
			return "RTT Cumulé";
		break;
		case 'rttnoncumule':
			return "RTT Non Cumulé";
		break;
		case 'conges':
			return "Congés";
		break;
		case 'maladiemaintenue':
			return "Maladie Maintenue";
		break;
		case 'maladienonmaintenue':
			return "Maladie Non Maintenue";
		break;
		case 'maternite':
			return "Maternité";
		break;
		case 'paternite':
			return "Paternité";
		break;
		case 'chomagepartiel':
			return "Chômage Partiel";
		break;
		case 'nonremuneree':
			return "Non rémunérée";
		break;
		case 'accidentdetravail':
			return "Accident de travail";
		break;
		case 'maladieprofessionnelle':
			return "Maladie Professionnelle";
		break;
		case 'congeparental':
			return "Congé Parental";
		break;
		case 'accidentdetrajet':
			return "Accident de trajet";
		break;
		case 'mitempstherapeutique':
			return "Mi-temps thérapeutique";
		break;
	}
}

//fonction permettant de retourner le libelle de l'état de l'absence (à Valider...)
function saveLibelleEtat($etat){
	switch($etat){
		case 'Avalider':
			return "En attente de validation";
		break;
		case 'Acceptee':
			return "Acceptée";
		break;
		case 'Refusee':
			return "Refusée";
		break;

	}
}





//arrondi variable float à 2 virgules
function round2Virgule($variable){
	if($variable==0){
		return "0";
	}else return round($variable,2);
}
