<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

//Interface qui renvoie les congés maladie (maintenue ou non) et les jours ancienneté acquis du collaborateur souhaité durant la période demandée
global $user,$conf;

$ATMdb=new Tdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'';

_get($get);

function _get($case) {
	switch ($case) {
		case 'jour_anciennete':
			__out(_jourAnciennete($_REQUEST['fk_user']));	
			break;
		case 'maladie_maintenue':
			__out(_dureeMaladieMaintenue($_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));	
			break;
		case 'maladie_non_maintenue':
			__out(_dureeMaladieNonMaintenue($_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));	
			break;
		case 'conges':
			__out(_conges($_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));	
			break;
		case 'recapAbsence':
			__out(_recapAbsence($_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['fk_user'], $_REQUEST['fk_usergroup']));	
			break;
	}
}


function _jourAnciennete($userId){
	
	$TabRecapConges=array();
	
	$sql="SELECT a.acquisAncienneteNM1 
	FROM ".MAIN_DB_PREFIX."rh_compteur as a 
	WHERE a.entity=".$conf->entity."
	AND a.fk_user=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapConges[$userId]=$ATMdb->Get_field('acquisAncienneteNM1');
	}
	
	return $TabRecapConges;
}

function _dureeMaladieMaintenue($userId, $date_debut, $date_fin){
	
	$TabRecapMaladie=array();
		
	$sql="SELECT u.name, u.firstname, a.type, a.date_debut, a.date_fin, a.duree 
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a 
	WHERE u.rowid=a.fk_user 
	AND a.type LIKE 'maladiemaintenue'
	AND a.entity=".$conf->entity."
	AND a.fk_user=".$userId."
	AND (a.date_debut>'".$date_debut."' AND a.date_fin<'".$date_fin."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapMaladie[$userId]['maladiemaintenue']=$TabRecapMaladie[$user]['maladiemaintenue']+$ATMdb->Get_field('duree');
	}
	
	return $TabRecapMaladie;
}

function _dureeMaladieNonMaintenue($userId, $date_debut, $date_fin){
	
	$TabRecapMaladie=array();
		
	$sql="SELECT u.name, u.firstname, a.type, a.date_debut, a.date_fin, a.duree 
	FROM ".MAIN_DB_PREFIX."rh_absence as a
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user = u.rowid)
	WHERE a.type LIKE 'maladienonmaintenue'
	AND a.entity=".$conf->entity."
	AND a.fk_user=".$userId."
	AND (a.date_debut>'".$date_debut."' AND a.date_fin<'".$date_fin."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapMaladie[$userId]['maladienonmaintenue']=$TabRecapMaladie[$user]['maladienonmaintenue']+$ATMdb->Get_field('duree');
	}
	
	return $TabRecapMaladie;
}

function _conges($userId, $date_debut, $date_fin){
	
	$nb_jours_travailles=0;
	
	//**********************************************
	//On récupère l'emploi du temps de l'utilisateur
	//**********************************************
	
	$EmploiDuTemps=array();
	
	$sql="SELECT *
	FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as a
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user = u.rowid)
	WHERE a.entity=".$conf->entity."
	AND a.fk_user=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$EmploiDuTemps['lundiam']=$ATMdb->Get_field('lundiam');
		$EmploiDuTemps['lundipm']=$ATMdb->Get_field('lundipm');
		$EmploiDuTemps['mardiam']=$ATMdb->Get_field('mardiam');
		$EmploiDuTemps['mardipm']=$ATMdb->Get_field('mardipm');
		$EmploiDuTemps['mercrediam']=$ATMdb->Get_field('mercrediam');
		$EmploiDuTemps['mercredipm']=$ATMdb->Get_field('mercredipm');
		$EmploiDuTemps['jeudiam']=$ATMdb->Get_field('jeudiam');
		$EmploiDuTemps['jeudipm']=$ATMdb->Get_field('jeudipm');
		$EmploiDuTemps['vendrediam']=$ATMdb->Get_field('vendrediam');
		$EmploiDuTemps['vendredipm']=$ATMdb->Get_field('vendredipm');
		$EmploiDuTemps['samediam']=$ATMdb->Get_field('samediam');
		$EmploiDuTemps['samedipm']=$ATMdb->Get_field('samedipm');
		$EmploiDuTemps['dimancheam']=$ATMdb->Get_field('dimancheam');
		$EmploiDuTemps['dimanchepm']=$ATMdb->Get_field('dimanchepm');
	}
	
<<<<<<< HEAD
	//On regarde chaque jour
	$dateDeb = strtotime($date_debut);  
	$dateFin = strtotime($date_fin);
	 
	while ($dateDeb <= $dateFin)
	{
		//On regarde si le jour est férié
		$sql="SELECT *
		FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries as a
		WHERE a.entity=".$conf->entity."
		AND a.date_jourOff='".date('Y-m-d 00:00:00',$dateDeb)."'";
		
		$ATMdb->Execute($sql);
		$k=0;
		while($ATMdb->Get_line()) {
			$k++;
		}
		
		//Si le jour n'est pas férié
		if($k=0){
			$timestamp = strtotime(date('Y-m-d', $phpDate));
			$jour = date("w",$timestamp);
			switch ($jour) {
			    case "lundi":
			        if($EmploiDuTemps['lundiam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['lundipm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
				case "mardi":
			        if($EmploiDuTemps['mardiam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['mardipm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
				case "mercredi":
			        if($EmploiDuTemps['mercrediam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['mercredipm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
			    case "jeudi":
			        if($EmploiDuTemps['jeudiam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['jeudipm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
				case "vendredi":
			        if($EmploiDuTemps['vendrediam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['vendredipm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
				case "samedi":
			        if($EmploiDuTemps['samediam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['samedipm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
				case "dimanche":
			        if($EmploiDuTemps['dimancheam']){
			        	$nb_jours_travailles+=0.5;
			        }
					if($EmploiDuTemps['dimanchepm']){
			        	$nb_jours_travailles+=0.5;
			        }
			        break;
			}
		}
		
		$dateDeb = strtotime('+1 day', $dateDeb);
	}
	
	$TabRecapConges['nbJoursTravailles']=$nb_jours_travailles;
	$TabRecapConges['nbJoursNonTravailles']=$nb_jours_non_travailles;
	$TabRecapConges['congesPayes']=$nb_jours_conges;
	$TabRecapConges['eventFamille']=$nb_jours_event_famille;
	$TabRecapConges['congesDivers']=$nb_jours_conges_divers;
	
	return $TabRecapConges;
=======
	return $TabRecapConges;
}


function _recapAbsence($date_debut, $date_fin, $fk_user, $fk_usergroup){
	
	return $date_debut." ".$date_fin." ".$fk_user." ".$fk_usergroup;
>>>>>>> f0a9fa6fe6686c22befadd9895df113f8c5ee101
}
