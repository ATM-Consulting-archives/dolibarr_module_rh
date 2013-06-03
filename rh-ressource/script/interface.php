<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

//Interface qui renvoie les emprunts de ressources d'un utilisateur
$ATMdb=new TPDOdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'emprunt';

_get($ATMdb, $get);

function _get(&$ATMdb, $case) {
	switch ($case) {
		case 'emprunt':
			__out( _emprunt($ATMdb, $_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));
			break;
		case 'orange':
			__out(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			//print_r(_exportOrange($ATMdb, $_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['entity']));
			break;
		default:
			break;
	}
}

function _exportOrange(&$ATMdb, $date_debut, $date_fin, $entity){
	$TabLigne = array();
	
	$sql="SELECT totalIFact, totalEFact, totalFact, natureRefac, montantRefac, name, firstname, COMPTE_TIERS
	FROM ".MAIN_DB_PREFIX."rh_evenement as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
	LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as c ON (c.fk_object = e.fk_user)
	WHERE e.entity=".$entity."
	AND e.type='factTel' 
	AND (e.date_debut<='".$date_fin."' OR e.date_debut>='".$date_debut."')";
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

