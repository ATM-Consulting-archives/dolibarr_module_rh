<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

//Interface qui renvoie les emprunts de ressources d'un utilisateur
$ATMdb=new TPDOdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'emprunt';

_get($ATMdb, $get);

function _get(&$ATMdb, $case) {
	//on transforme la date du format timestamp en 2013-01-20
	//$timestamp = mktime(0,0,0,substr($date_debut, 3,2),substr($date_debut, 0,2), substr($date_debut, 6,4));
	$date_debut = date("Y-m-d", $_REQUEST['date_debut']);
	//$timestamp = mktime(0,0,0,substr($date_fin, 3,2),substr($date_fin, 0,2), substr($date_fin, 6,4));
	$date_fin = date("Y-m-d", $_REQUEST['date_fin']);
	switch ($case) {
		case 'emprunt':
			__out( _emprunt($ATMdb, $_REQUEST['fk_user'], $date_debut, $date_fin));
			break;
		case 'orange':
			__out(_exportOrange($ATMdb, $date_debut, $date_fin, $_REQUEST['entity']));
			//print_r(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			break;
		case 'voiture':
			__out(_exportVoiture($ATMdb, $date_debut, $date_fin, $_REQUEST['entity']));
			//print_r(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			break;
		
		default:
			break;
	}
}

function _exportVoiture(&$ATMdb, $date_debut, $date_fin, $entity){
	$TLignes = array();
	
	$sql="SELECT coutEntrepriseTTC, coutEntrepriseHT, type, 
				DATE_FORMAT(date_debut, '%d%m%y') as date_debut, 
				DATE_FORMAT(date_debut, '%m') as mois_date_debut, 
				DATE_FORMAT(date_debut, '%Y') as annee_date_debut, 
				typeVehicule, name, firstname, a.code, e.entity, t.codecomptable, 
				ue.COMPTE_TIERS
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t (e.type=t.code)
	LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as a ON (e.fk_user=a.fk_user)
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
		LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON (u.rowid = ue.fk_object)
	WHERE (e.type='factureloyer' OR  e.type='facturegestionetentretien')
	AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')";
	
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$date = $row->date_debut;
		$date_mois = $row->mois_date_debut;
		$date_fin = $row->annee_date_debut;
		//un VU : on prend le HT
		//un VP on prend le TTC
		$total = (strtolower($row->typeVehicule)=='vu') ? $row->coutEntrepriseHT : $row->coutEntrepriseTTC;
		$montant = round($total, 2);
		
		if($entity == $row->entity){
			$sens = 'D';
		}else{
			$sens = 'C';
		}
		
		if($sens='C'){
			$code_compta = '425902';
			$type_compte = 'X';
			$compte_tiers = '';
		}else{
			$code_compta = $codecomptable;
			$type_compte = 'G';
			$compte_tiers = '';
		}
	
		$TLignes[] = array(
			'RES'
			,$date
			,'FF'
			,$code_compta
			,$type_compte
			,$compte_tiers		
			//,'type'=>$TNomsEvenements[$row->type]		
			//,'user'=>htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1')
			//,'compte'=>$TComptes[$row->type]
			//,'codeanalytique'=>$row->code
			//,'typeVehicule'=>$row->typeVehicule
			,'RESSOURCE '.$date_mois.'/'.$date_annee
			,'V'
			,date('dmy')
			,$sens
			,$montant
			,'N'
			,''
			,'EUR'
			);
		
	}
	
	return $TLignes;
	
}


function _exportOrange(&$ATMdb, $date_debut, $date_fin, $entity){
	$TabLigne = array();
	
	$sql="SELECT totalIFact, totalEFact, totalFact, natureRefac, montantRefac, name, firstname, COMPTE_TIERS
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
	LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as c ON (c.fk_object = e.fk_user)
	WHERE e.entity=".$entity."
	AND e.type='factTel' 
	AND (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')";
	//echo $sql.'<br>';
	$ATMdb->Execute($sql);
	while($row = $ATMdb->Get_line()) {
		$total = number_format($row->totalIFact+$row->totalEFact+$row->montantRefac, 2);
		if ($total>0){
			$TabLigne[] = array(
				'user'=>htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1')
				,'comptetiers'=>$row->COMPTE_TIERS
				,'int'=>number_format($row->totalIFact,2)
				,'ext'=>number_format($row->totalEFact,2)
				,'naturerefact'=>$row->natureRefac
				,'montantrefact'=>$row->montantRefac != 0 ? number_format($row->montantRefac, 2) : ''
				,'total'=>$total
				);
		}
	}
	
	
	$ATMdb->close();
	return $TabLigne;
}


function _emprunt(&$ATMdb, $userId, $date_debut, $date_fin){
	global $user, $conf;
	
	$TabEmprunt=array();
	
	$sql="SELECT libelle, numId	
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
	WHERE e.entity=".$conf->entity."
	AND e.fk_user=".$userId."
	AND (date_debut<='".$date_fin."' AND date_fin>='".$date_debut."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabEmprunt[]=array(
			'nom'=>$ATMdb->Get_field('libelle').' - '.$ATMdb->Get_field('numId')
			,'date_debut'=>$ATMdb->Get_field('date_debut')
			,'date_fin'=>$ATMdb->Get_field('date_fin')
		);
	}
	
	$ATMdb->close();
	return $TabEmprunt;
}

