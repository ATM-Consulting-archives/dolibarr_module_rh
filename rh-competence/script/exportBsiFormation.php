<?php

require('../config.php');

//Renvoie les formations du collaborateur de l'année précédente
global $user,$conf;

$ATMdb=new Tdb;
	
$anneeCourante=date('Y');
$anneePrec=$anneeCourante-1;

$debutAnnee=$anneePrec.'-06-01 00:00:00';
$finAnnee=$anneeCourante.'-05-31 00:00:00';

$TUserID=array();
$sqlReqUser="SELECT rowid, name,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE entity=".$conf->entity;
$ATMdb->Execute($sqlReqUser);

while($ATMdb->Get_line()) {
	$TUserID[]=$ATMdb->Get_field('rowid');
}

$TabRecapFormation=array();
foreach($TUserID as $user){
	
	$sql="SELECT u.name, u.firstname, a.date_debut, a.date_fin, a.coutFormation, a.libelleFormation
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_formation_cv as a 
	WHERE u.rowid=a.fk_user 
	AND a.entity=".$conf->entity."
	AND a.fk_user=".$user."
	AND (a.date_debut>'".$debutAnnee."' AND a.date_fin<'".$finAnnee."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapFormation[$user]['libelleFormation'][]=$ATMdb->Get_field('libelleFormation');
		$TabRecapFormation[$user]['coutFormation']=$TabRecapFormation[$user]['coutFormation']+$ATMdb->Get_field('coutFormation');
	}
}

//print_r($TabRecap);
//echo "<br/>";
__out($TabRecapFormation);

