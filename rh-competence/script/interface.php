<?php

define('INC_FROM_CRON_SCRIPT', true);
require('../config.php');

require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");

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
			$TabRecapFormation[$userId]['libelleFormation']=$ATMdb->Get_field('libelleFormation');
			$TabRecapFormation[$userId]['coutFormation']=round($TabRecapFormation[$user]['coutFormation']+$ATMdb->Get_field('coutFormation'),2);
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
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TabRecapRem['date_debutRemuneration']=dol_print_date($ATMdb->Get_field('date_debutRemuneration'), '%d/%m/%Y');
			$TabRecapRem['date_finRemuneration']=dol_print_date($ATMdb->Get_field('date_finRemuneration'), '%d/%m/%Y');
			$TabRecapRem['bruteAnnuelle']=round($ATMdb->Get_field('bruteAnnuelle'),2);
			$TabRecapRem['salaireMensuel']=round($ATMdb->Get_field('salaireMensuel'),2);
			$TabRecapRem['primeAnciennete']=round($ATMdb->Get_field('primeAnciennete'),2);
			$TabRecapRem['primeSemestrielle']=round($ATMdb->Get_field('primeSemestrielle'),2);
			$TabRecapRem['primeExceptionnelle']=round($ATMdb->Get_field('primeExceptionnelle'),2);
			$TabRecapRem['prevoyancePartSalariale']=round($ATMdb->Get_field('prevoyancePartSalariale'),2);
			$TabRecapRem['prevoyancePartPatronale']=round($ATMdb->Get_field('prevoyancePartPatronale'),2);
			$TabRecapRem['urssafPartSalariale']=round($ATMdb->Get_field('urssafPartSalariale'),2);
			$TabRecapRem['urssafPartPatronale']=round($ATMdb->Get_field('urssafPartPatronale'),2);
			$TabRecapRem['retraitePartSalariale']=round($ATMdb->Get_field('retraitePartSalariale'),2);
			$TabRecapRem['retraitePartPatronale']=round($ATMdb->Get_field('retraitePartPatronale'),2);
		}
		
		return $TabRecapRem;
}

