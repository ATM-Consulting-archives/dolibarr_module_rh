<?php

require('../config.php');

//Interface qui renvoie les congés maladie (maintenue ou non) et les jours ancienneté acquis du collaborateur souhaité durant la période demandée
$get = isset($_REQUEST['get'])?$_REQUEST['get']:'';

_get($get);

function _get($case) {
	switch ($case) {
		case 'formation':
			__out(_formation($_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));
			break;
		case 'remuneration':
			__out(_remuneration($_REQUEST['fk_user'], $_REQUEST['date_debut'], $_REQUEST['date_fin']));
			break;
	}
}


function _formation($userId, $date_debut, $date_fin){
		global $user,$conf;
		
		$ATMdb=new Tdb;
		
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

function _remuneration($userId, $date_debut, $date_fin){
		global $user,$conf;
		
		$ATMdb=new Tdb;
		
		$TabRecapFormation=array();
		
		$sql="SELECT *
		FROM ".MAIN_DB_PREFIX."rh_remuneration as a 
		WHERE a.entity=".$conf->entity."
		AND a.fk_user=".$userId."
		AND (a.date_debutRemuneration<'".$date_debut."' AND a.date_finRemuneration>'".$date_fin."')";
		print $sql;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TabRecapRem['date_debutRemuneration']=$ATMdb->Get_field('date_debutRemuneration');
			$TabRecapRem['date_finRemuneration']=$ATMdb->Get_field('date_finRemuneration');
			$TabRecapRem['bruteAnnuelle']=$ATMdb->Get_field('bruteAnnuelle');
			$TabRecapRem['salaireMensuel']=$ATMdb->Get_field('salaireMensuel');
			$TabRecapRem['primeAnciennete']=$ATMdb->Get_field('primeAnciennete');
			$TabRecapRem['primeSemestrielle']=$ATMdb->Get_field('primeSemestrielle');
			$TabRecapRem['primeExceptionnelle']=$ATMdb->Get_field('primeExceptionnelle');
			$TabRecapRem['prevoyancePartSalariale']=$ATMdb->Get_field('prevoyancePartSalariale');
			$TabRecapRem['prevoyancePartPatronale']=$ATMdb->Get_field('prevoyancePartPatronale');
			$TabRecapRem['urssafPartSalariale']=$ATMdb->Get_field('urssafPartSalariale');
			$TabRecapRem['urssafPartPatronale']=$ATMdb->Get_field('urssafPartPatronale');
			$TabRecapRem['retraitePartSalariale']=$ATMdb->Get_field('retraitePartSalariale');
			$TabRecapRem['retraitePartPatronale']=$ATMdb->Get_field('retraitePartPatronale');
			//print_r($TabRecapRem);
		}
		
		return $TabRecapRem;
}

