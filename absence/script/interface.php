<?php

if(isset($_REQUEST['inc']) && $_REQUEST['inc']=='main') {
	null;
}
else{
	define('INC_FROM_CRON_SCRIPT', true);	
}


require '../config.php';
dol_include_once('/absence/class/absence.class.php');
dol_include_once('/absence/lib/absence.lib.php');

//Interface qui renvoie les congés maladie (maintenue ou non) et les jours ancienneté acquis du collaborateur souhaité durant la période demandée
$ATMdb=new TPDOdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'';

_get($ATMdb, $get);

function _get(&$ATMdb, $case) {
	switch ($case) {
		case 'jour_anciennete':
			__out(_jourAnciennete($ATMdb, $_REQUEST['fk_user']));	
			break;
		case 'maladie_maintenue':
			__out(_dureeMaladieMaintenue($ATMdb, $_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));	
			break;
		case 'maladie_non_maintenue':
			__out(_dureeMaladieNonMaintenue($ATMdb, $_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));	
			break;
		case 'conges':
			__out(_conges($ATMdb, $_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));	
			break;
		case 'typeAbsence_hour':
			$typeAbsence=new TRH_TypeAbsence;
			$typeAbsence->load_by_type($ATMdb, $_REQUEST['type']);
			__out(array(
				'start'=>$typeAbsence->get_date('date_hourStart','H:i')
				,'end'=>$typeAbsence->get_date('date_hourEnd','H:i')
			));
			break;
			
		case 'planning':
			
			$ATMdb=new TPDOdb;
			$absence=new TRH_Absence;
            
            $absence->date_debut_planning = strtotime('-3month');
            $absence->date_fin_planning = strtotime('+1month');
            
            if(isset($_REQUEST['date_debut_search'])) $absence->set_date('date_debut_planning', $_REQUEST['date_debut_search']); 
			if(isset($_REQUEST['date_fin_search'])) $absence->set_date('date_fin_planning', $_REQUEST['date_fin_search']); 
			
			ob_start();
			getPlanningAbsence($ATMdb, $absence, array((int)GETPOST('groupe'),(int)GETPOST('groupe2'),(int)GETPOST('groupe3')), GETPOST('fk_user'));
			$html = ob_get_clean();
			__out($html);
			
			break;
	}
}

function _jourAnciennete(&$ATMdb, $userId){
	global $user, $conf;
	
	$TabRecapConges=array();
	
	$sql="SELECT a.acquisAncienneteNM1 
	FROM ".MAIN_DB_PREFIX."rh_compteur as a 
	WHERE a.entity IN (0,".$conf->entity.")
	AND a.fk_user=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapConges[$userId]=$ATMdb->Get_field('acquisAncienneteNM1');
	}
	
	return $TabRecapConges;
}

function _dureeMaladieMaintenue(&$ATMdb, $userId, $date_debut, $date_fin){
	global $user, $conf;
	
	$TabRecapMaladie=array();
		
	$sql="SELECT u.lastname, u.firstname, a.type, a.date_debut, a.date_fin, a.duree 
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a 
	WHERE u.rowid=a.fk_user 
	AND a.type LIKE 'maladiemaintenue'
	AND a.entity IN (0,".$conf->entity.")
	AND a.fk_user=".$userId."
	AND (a.date_debut>'".$date_debut."' AND a.date_fin<'".$date_fin."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapMaladie[$userId]['maladiemaintenue']=$TabRecapMaladie[$user]['maladiemaintenue']+$ATMdb->Get_field('duree');
	}
	
	return $TabRecapMaladie;
}

function _dureeMaladieNonMaintenue(&$ATMdb, $userId, $date_debut, $date_fin){
	global $user, $conf;
	
	$TabRecapMaladie=array();
		
	$sql="SELECT u.lastname, u.firstname, a.type, a.date_debut, a.date_fin, a.duree 
	FROM ".MAIN_DB_PREFIX."rh_absence as a
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user = u.rowid)
	WHERE a.type LIKE 'maladienonmaintenue'
	AND a.entity IN (0,".$conf->entity.")
	AND a.fk_user=".$userId."
	AND (a.date_debut>'".$date_debut."' AND a.date_fin<'".$date_fin."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapMaladie[$userId]['maladienonmaintenue']=$TabRecapMaladie[$user]['maladienonmaintenue']+$ATMdb->Get_field('duree');
	}
	
	return $TabRecapMaladie;
}

function _conges(&$ATMdb, $userId, $date_debut, $date_fin){
	global $user, $conf;
			
	//**********************************************
	//On récupère le nombre de jours entre les 2 dates
	//**********************************************
	
	$dateDeb = strtotime($date_debut);
	$dateFin = strtotime($date_fin);
	
	$nb_jours = ($dateFin-$dateDeb)/(3600*24);
	$nb_jours_travailles = 0;
	$nb_jours_conges = 0;
	$nb_jours_event_famille = 0;
	$nb_jours_conges_divers = 0;
	
	//**********************************************
	//On récupère l'emploi du temps de l'utilisateur
	//**********************************************
	
	$EmploiDuTemps=array();
	
	$sql="SELECT *
	FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as a
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user = u.rowid)
	WHERE a.entity IN (0,".$conf->entity.")
	AND a.fk_user=".$userId." AND a.is_archive!=1";
	
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
	
	//Traitement sur chaque jour
	while ($dateDeb <= $dateFin)
	{
		$timestamp = strtotime(date('Y-m-d', $dateDeb));
		setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
		$jour = strftime("%A",$timestamp);
		
		//on regarde tout d'abord quand l'utilisateur doit travailler
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
		
		//on regarde ensuite si la personne a déposé un jour d'absence ce jour-ci, si oui, on le décompte
		$sql="SELECT *
		FROM ".MAIN_DB_PREFIX."rh_absence as a
			LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user=u.rowid)
		WHERE a.entity IN (0,".$conf->entity.")
		AND u.rowid=".$userId."
		AND a.etat='Validee'
		AND (a.date_debut<='".date('Y-m-d 00:00:00',$dateDeb)."' AND a.date_fin>='".date('Y-m-d 00:00:00',$dateDeb)."')";
		
		$k=0;
		$m=0;
		$n=0;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$date_absence_debut=$ATMdb->Get_field('date_debut');
			$date_absence_fin=$ATMdb->Get_field('date_fin');
			$periode_date_debut=$ATMdb->Get_field('ddMoment');
			$periode_date_fin=$ATMdb->Get_field('dfMoment');
			if((date('Y-m-d 00:00:00',$dateDeb)==$date_absence_debut)&&(date('Y-m-d 00:00:00',$dateDeb)==$date_absence_fin)){
				if(($periode_date_debut=='matin')&&($periode_date_fin=='apresmidi')){
					$nb_jours_travailles-=1;
					$n++;
				}elseif($periode_date_fin=='matin'){
					$nb_jours_travailles-=0.5;
					$k++;
				}else{
					$nb_jours_travailles-=0.5;
					$m++;
				}
			}elseif(date('Y-m-d 00:00:00',$dateDeb)==$date_absence_debut){
				if(($periode_date_debut=='matin')){
					$nb_jours_travailles-=1;
					$n++;
				}else{
					$nb_jours_travailles-=0.5;
					$m++;
				}
			}elseif(date('Y-m-d 00:00:00',$dateDeb)==$date_absence_fin){
				if(($periode_date_fin=='apresmidi')){
					$nb_jours_travailles-=1;
					$n++;
				}else{
					$nb_jours_travailles-=0.5;
					$k++;
				}
			}else{
				$nb_jours_travailles-=1;
				$n++;
			}
		}
		
		//on regarde si le jour est férié, si oui, on le décompte
		$sql="SELECT *
		FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries as a
		WHERE a.entity IN (0,".$conf->entity.")
		AND a.date_jourOff='".date('Y-m-d 00:00:00',$dateDeb)."'";
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$typeAbsence=$ATMdb->Get_field('typeAbsence');
			$periode=$ATMdb->Get_field('moment');
			if($periode=='matin'){
				//si l'utilisateur n'a pas déposé d'absence le matin de ce jour-ci
				if($k==0){
					$nb_jours_travailles-=0.5;
					switch ($typeAbsence) {
		    			case "conges":
							$nb_jours_conges+=0.5;
							break;
						case "paternite":
						case "mariage":
						case "deuil":
						case "naissanceadoption":
						case "enfantmalade":
						case "maternite":
						case "congeparental":
							$nb_jours_event_famille+=0.5;
							break;
						default:
							$nb_jours_conges_divers+=0.5;
							break;
					}
				}
			}elseif($periode=='apresmidi'){
				//si l'utilisateur n'a pas déposé d'absence l'après-midi de ce jour-ci
				if($m==0){
					$nb_jours_travailles-=0.5;
					switch ($typeAbsence) {
		    			case "conges":
							$nb_jours_conges+=0.5;
							break;
						case "paternite":
						case "mariage":
						case "deuil":
						case "naissanceadoption":
						case "enfantmalade":
						case "maternite":
						case "congeparental":
							$nb_jours_event_famille+=0.5;
							break;
						default:
							$nb_jours_conges_divers+=0.5;
							break;
					}
				}
			}elseif($periode=='allday'){
				//si l'utilisateur n'a pas déposé d'absence ce jour-ci
				if($n==0){
					$nb_jours_travailles-=1;
					switch ($typeAbsence) {
		    			case "conges":
							$nb_jours_conges+=1;
							break;
						case "mariage":
						case "deuil":
						case "naissanceadoption":
						case "enfantmalade":
						case "demenagement":
							$nb_jours_event_famille+=1;
							break;
						default:
							$nb_jours_conges_divers+=1;
							break;
					}
				}
			}
		}
		
		//on incrémente la date
		$dateDeb = strtotime('+1 day', $dateDeb);
	}

	$nb_jours_non_travailles=$nb_jours-$nb_jours_travailles;
	
	$TabRecapConges['nbJoursTravailles']=$nb_jours_travailles;
	$TabRecapConges['nbJoursNonTravailles']=$nb_jours_non_travailles;
	$TabRecapConges['congesPayes']=$nb_jours_conges;
	$TabRecapConges['eventFamille']=$nb_jours_event_famille;
	$TabRecapConges['congesDivers']=$nb_jours_conges_divers;
	
	return $TabRecapConges;
}