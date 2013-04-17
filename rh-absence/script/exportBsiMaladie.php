<?php

require('../config.php');

//Renvoie les congés maladie du collaborateur de l'année précédente
global $user,$conf;

$ATMdb=new Tdb;
	
$anneeCourante=date('Y');
$anneePrec=$anneeCourante-1;

$debutAnnee='01/06/'.$anneePrec;
$finAnnee='31/05/'.$anneeCourante;

// calcul du timestamp
list($jour, $mois, $annee) = explode('/', $debutAnnee);
$timestampDebutAnnee = mktime (0, 0, 0, $mois, $jour, $annee);
$debutAnnee=date("Y-m-d H:i:s", $timestampDebutAnnee);


list($jour, $mois, $annee) = explode('/', $finAnnee);
$timestampFinAnnee = mktime (0, 0, 0, $mois, $jour, $annee);
$finAnnee=date("Y-m-d H:i:s", $timestampFinAnnee);

$TUserID=array();
$sqlReqUser="SELECT rowid, name,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE entity=".$conf->entity;
$ATMdb->Execute($sqlReqUser);

while($ATMdb->Get_line()) {
	$TUserID[]=$ATMdb->Get_field('rowid');
}

$TabRecap=array();
foreach($TUserID as $user){
	
	$sql="SELECT u.name, u.firstname, a.type, a.date_debut, a.date_fin, a.duree 
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a 
	WHERE u.rowid=a.fk_user 
	AND a.type LIKE 'maladiemaintenue'
	AND a.entity=".$conf->entity."
	AND a.fk_user=".$user."
	AND (a.date_debut>'".$debutAnnee."' AND a.date_fin<'".$finAnnee."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecap['maladiemaintenue'][$user]=$TabRecap[$user]+$ATMdb->Get_field('duree');
	}
}

foreach($TUserID as $user){
	
	$sql="SELECT u.name, u.firstname, a.type, a.date_debut, a.date_fin, a.duree 
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a 
	WHERE u.rowid=a.fk_user 
	AND a.type LIKE 'maladienonmaintenue'
	AND a.entity=".$conf->entity."
	AND a.fk_user=".$user."
	AND (a.date_debut>'".$debutAnnee."' AND a.date_fin<'".$finAnnee."')";
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecap['maladienonmaintenue'][$user]=$TabRecap[$user]+$ATMdb->Get_field('duree');
	}
}
//print_r($TabRecap);
//echo "<br/>";
__out($TabRecap);

