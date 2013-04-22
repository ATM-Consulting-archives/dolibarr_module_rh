<?php

require('../config.php');

//Interface qui renvoie les congés maladie (maintenue ou non) et les jours ancienneté acquis du collaborateur souhaité durant la période demandée
global $user,$conf;

$ATMdb=new Tdb;

$get = isset($_REQUEST['get'])?$_REQUEST['get']:'';

_get($get);

function _get($case) {
	switch ($case) {
		case 'formation':
			__out(_formation($_REQUEST['id'], $REQUEST['date_debut'], $REQUEST['date_fin']));	
			break;
		case 'remuneration':
			__out(_remuneration($_REQUEST['id'], $REQUEST['date_debut'], $REQUEST['date_fin']));	
			break;
	}
}


function _formation($userId, $date_debut, $date_fin){
		$TabRecapFormation=array();
		
		$sql="SELECT u.name, u.firstname, a.date_debut, a.date_fin, a.coutFormation, a.libelleFormation
		FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_formation_cv as a 
		WHERE u.rowid=a.fk_user 
		AND a.entity=".$conf->entity."
		AND a.fk_user=".$userId."
		AND (a.date_debut>'".$date_debut."' AND a.date_fin<'".$date_fin."')";
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TabRecapFormation[$userId]['libelleFormation'][]=$ATMdb->Get_field('libelleFormation');
			$TabRecapFormation[$userId]['coutFormation']=$TabRecapFormation[$user]['coutFormation']+$ATMdb->Get_field('coutFormation');
		}
		
		return $TabRecapFormation;
}

function _remuneration($userId, $date_debut, $date_debut){
		$TabRecapFormation=array();
		
		$sql="SELECT *
		FROM ".MAIN_DB_PREFIX."rh_remuneration as a 
		WHERE a.entity=".$conf->entity."
		AND a.fk_user=".$userId."
		AND (a.date_debutRemuneration>'".$date_debut."' AND a.date_finRemuneration<'".$date_debut."')";
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['date_debutRemuneration']=$ATMdb->Get_field('date_debutRemuneration');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['date_finRemuneration']=$ATMdb->Get_field('date_finRemuneration');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['bruteAnnuelle']=$ATMdb->Get_field('bruteAnnuelle');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['salaireMensuel']=$ATMdb->Get_field('salaireMensuel');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['primeAnciennete']=$ATMdb->Get_field('primeAnciennete');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['primeSemestrielle']=$ATMdb->Get_field('primeSemestrielle');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['primeExceptionnelle']=$ATMdb->Get_field('primeExceptionnelle');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['prevoyancePartSalariale']=$ATMdb->Get_field('prevoyancePartSalariale');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['prevoyancePartPatronale']=$ATMdb->Get_field('prevoyancePartPatronale');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['urssafPartSalariale']=$ATMdb->Get_field('urssafPartSalariale');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['urssafPartPatronale']=$ATMdb->Get_field('urssafPartSalariale');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['retraitePartSalariale']=$ATMdb->Get_field('urssafPartSalariale');
			$TabRecapRem[$userId][substr($ATMdb->Get_field('date_debutRemuneration'),0,4)]['retraitePartPatronale']=$ATMdb->Get_field('urssafPartSalariale');
			//print_r($TabRecapRem);
		}
		
		return $TabRecapRem;
}

