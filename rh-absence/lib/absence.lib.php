<?php

function absencePrepareHead(&$obj, $type='absence') {
	
	switch ($type) {
		case 'absence':
				return array(
					array(DOL_URL_ROOT_ALT.'/absence/absence.php?id='.$obj->getId()."&action=view", 'Fiche','fiche')
					,array(DOL_URL_ROOT_ALT.'/absence/calendrierAbsence.php?id='.$obj->getId(), 'Calendrier','calendrier')
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





//recrédite les heures au compteur lors de la suppression d'une absence 
function recrediterHeure($absence,&$ATMdb){
	global $user;
	switch($absence->type){
		case "rttcumule" : 
			$sqlRecredit="UPDATE `llx_rh_compteur` SET rttPris=rttPris-".$absence->duree.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule+".$absence->duree."  where fk_user=".$user->id;
			$ATMdb->Execute($sqlRecredit);
		break;
		case "rttnoncumule" : 
			$sqlRecredit="UPDATE `llx_rh_compteur` SET rttPris=rttPris-".$absence->duree.",rttAcquisAnnuelNonCumule=rttAcquisAnnuelNonCumule+".$absence->duree."  where fk_user=".$user->id;
			$ATMdb->Execute($sqlRecredit);
		break;
		default :  //dans les autres cas, on recrédite les congés
			$sqlRecredit="UPDATE `llx_rh_compteur` SET congesPrisNM1=congesPrisNM1-".$absence->duree.",acquisExerciceNM1=acquisExerciceNM1+".$absence->duree."  where fk_user=".$user->id;
			$ATMdb->Execute($sqlRecredit);
		break;
			
	}
}
