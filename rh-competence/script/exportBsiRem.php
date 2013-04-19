<?php

require('../config.php');

//Renvoie les congés maladie du collaborateur de l'année précédente
global $user,$conf;

$ATMdb=new Tdb;
	
$anneeCourante=date('Y');

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
	AND a.anneeRemuneration=".$anneeCourante;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapRem[$user]['anneeRemuneration']=$ATMdb->Get_field('anneeRemuneration');
		$TabRecapRem[$user]['bruteAnnuelle']=$ATMdb->Get_field('bruteAnnuelle');
		$TabRecapRem[$user]['salaireMensuel']=$ATMdb->Get_field('salaireMensuel');
		$TabRecapRem[$user]['primeAnciennete']=$ATMdb->Get_field('primeAnciennete');
		$TabRecapRem[$user]['primeSemestrielle']=$ATMdb->Get_field('primeSemestrielle');
		$TabRecapRem[$user]['primeExceptionnelle']=$ATMdb->Get_field('primeExceptionnelle');
		$TabRecapRem[$user]['prevoyancePartSalariale']=$ATMdb->Get_field('prevoyancePartSalariale');
		$TabRecapRem[$user]['prevoyancePartPatronale']=$ATMdb->Get_field('prevoyancePartPatronale');
		$TabRecapRem[$user]['urssafPartSalariale']=$ATMdb->Get_field('urssafPartSalariale');
		$TabRecapRem[$user]['urssafPartPatronale']=$ATMdb->Get_field('urssafPartSalariale');
		$TabRecapRem[$user]['retraitePartSalariale']=$ATMdb->Get_field('urssafPartSalariale');
		$TabRecapRem[$user]['retraitePartPatronale']=$ATMdb->Get_field('urssafPartSalariale');
	}
}

/*
foreach($TabRecapRem as $tab){
	print_r($tab);
	echo "<br/>";	
}*/

__out($TabRecapRem);

