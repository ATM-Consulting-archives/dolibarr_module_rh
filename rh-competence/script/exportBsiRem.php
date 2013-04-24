<?php

require('../config.php');

//Renvoie les congés maladie du collaborateur de l'année précédente
global $user,$conf;

$ATMdb=new Tdb;
	
$debutAnnee='2012-01-01 00:00:00';
$finAnnee='2013-01-01 00:00:00';

$TUserID=array();
$sqlReqUser="SELECT rowid, name,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE entity=".$conf->entity;
$ATMdb->Execute($sqlReqUser);

while($ATMdb->Get_line()) {
	$TUserID[]=$ATMdb->Get_field('rowid');
}

$TabRecapFormation=array();
foreach($TUserID as $user){
	
	$sql="SELECT *
	FROM ".MAIN_DB_PREFIX."rh_remuneration as a 
	WHERE a.entity=".$conf->entity."
	AND a.fk_user=".$user."
	AND (a.date_debutRemuneration>'".$debutAnnee."' AND a.date_finRemuneration>'".$finAnnee."')";
	//echo $sql;exit;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapRem[$user]['date_debutRemuneration']=$ATMdb->Get_field('date_debutRemuneration');
		$TabRecapRem[$user]['date_finRemuneration']=$ATMdb->Get_field('date_finRemuneration');
		$TabRecapRem[$user]['bruteAnnuelle']=$ATMdb->Get_field('bruteAnnuelle');
		$TabRecapRem[$user]['salaireMensuel']=$ATMdb->Get_field('salaireMensuel');
		$TabRecapRem[$user]['primeAnciennete']=$ATMdb->Get_field('primeAnciennete');
		$TabRecapRem[$user]['primeSemestrielle']=$ATMdb->Get_field('primeSemestrielle');
		$TabRecapRem[$user]['primeExceptionnelle']=$ATMdb->Get_field('primeExceptionnelle');
		$TabRecapRem[$user]['prevoyancePartSalariale']=$ATMdb->Get_field('prevoyancePartSalariale');
		$TabRecapRem[$user]['prevoyancePartPatronale']=$ATMdb->Get_field('prevoyancePartPatronale');
		$TabRecapRem[$user]['urssafPartSalariale']=$ATMdb->Get_field('urssafPartSalariale');
		$TabRecapRem[$user]['urssafPartPatronale']=$ATMdb->Get_field('urssafPartPatronale');
		$TabRecapRem[$user]['retraitePartSalariale']=$ATMdb->Get_field('retraitePartSalariale');
		$TabRecapRem[$user]['retraitePartPatronale']=$ATMdb->Get_field('retraitePartPatronale');
		print_r($TabRecapRem);
	}
}


/*foreach($TabRecapRem as $tab){
	print_r($tab);
	echo "<br/>";	
}
*/
__out($TabRecapRem);

