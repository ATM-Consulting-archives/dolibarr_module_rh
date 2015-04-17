<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

dol_include_once('/valideur/lib/valideur.lib.php');

//Interface qui renvoie toutes les lignes de notes de frais étant classées comme "comptabilisées"
$ATMdb=new TPDOdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'ndf';

_get($ATMdb, $get);

function _get(&$ATMdb, $case) {
	switch ($case) {
		case 'ndf':
			__out(_ndf($ATMdb,$_REQUEST['date_debut'], $_REQUEST['date_fin'], $_REQUEST['type'], $_REQUEST['entity']));
			break;
		case 'situation_perso':
			__out( _situation_perso($ATMdb,$_REQUEST['fk_user']));	
			break;
		case 'situation_pro':
			__out( _situation_pro($ATMdb,$_REQUEST['fk_user']));	
			break;
	}
}

function _ndf(&$ATMdb, $date_debut, $date_fin, $type, $entity){
    
	$TabNdf = extract_ndf($ATMdb, $date_debut, $date_fin, $type, $entity, isset( $_REQUEST['withLogin'] ) );
	
	return $TabNdf;
}

function _situation_perso(&$ATMdb, $userId){
	global $user, $conf;
		
	$TabRecapSituationPerso=array();
	
	$sql="SELECT e.DDN as 'ddn', e.SIT_FAM as 'situation_famille', e.NB_ENF_CHARGE as 'nb_enfants'
	FROM ".MAIN_DB_PREFIX."user_extrafields as e
	WHERE e.fk_object=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$ddn=$ATMdb->Get_field('ddn');
		$TabRecapSituationPerso['ddn']=substr($ddn,8,2)."/".substr($ddn,5,2)."/".substr($ddn,0,4);
		$TabRecapSituationPerso['situation_famille']=$ATMdb->Get_field('situation_famille');
		$TabRecapSituationPerso['nb_enfants']=$ATMdb->Get_field('nb_enfants');
	}
	
	return $TabRecapSituationPerso;
	
}

function _situation_pro(&$ATMdb, $userId){
	global $user, $conf;
	
	$TabRecapSituationPro=array();
	
	$sql="SELECT u.job as 'fonction'
	FROM ".MAIN_DB_PREFIX."user as u
	WHERE u.rowid=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['fonction']=$ATMdb->Get_field('fonction');
	}
	
	$sql="SELECT r.date_entreeEntreprise
	FROM ".MAIN_DB_PREFIX."rh_remuneration as r
	WHERE r.fk_user=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$date_anciennete=$ATMdb->Get_field('date_entreeEntreprise');
		$TabRecapSituationPro['date_anciennete']=substr($date_anciennete,8,2)."/".substr($date_anciennete,5,2)."/".substr($date_anciennete,0,4);
	}
	
	$sql="SELECT e.HORAIRE as 'horaire', e.STATUT as 'statut', e.NIVEAU as 'niveau', e.CONTRAT as 'contrat'
	FROM ".MAIN_DB_PREFIX."user_extrafields as e
	WHERE e.fk_object=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['horaire']=$ATMdb->Get_field('horaire');
		$TabRecapSituationPro['statut']=$ATMdb->Get_field('statut');
		$TabRecapSituationPro['niveau']=$ATMdb->Get_field('niveau');
		$TabRecapSituationPro['contrat']=$ATMdb->Get_field('contrat');
	}
	
	$sql="SELECT e.label
	FROM ".MAIN_DB_PREFIX."entity as e
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.entity=e.rowid)
	WHERE u.rowid=".$userId;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapSituationPro['affectation']=$ATMdb->Get_field('label');
	}
	
	return $TabRecapSituationPro;
	
}
