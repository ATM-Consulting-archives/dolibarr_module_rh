<?php

require('../config.php');

//Renvoie les congés maladie du collaborateur de l'année précédente
global $user,$conf;

$ATMdb=new Tdb;

$TUserID=array();
$sqlReqUser="SELECT rowid, name,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE entity=".$conf->entity;
$ATMdb->Execute($sqlReqUser);

while($ATMdb->Get_line()) {
	$TUserID[]=$ATMdb->Get_field('rowid');
}

$TabRecapConges=array();

foreach($TUserID as $user){
	
	$sql="SELECT a.acquisAncienneteNM1 
	FROM ".MAIN_DB_PREFIX."rh_compteur as a 
	WHERE a.entity=".$conf->entity."
	AND a.fk_user=".$user;

	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TabRecapConges[$user]=$ATMdb->Get_field('acquisAncienneteNM1');
	}
}
//print_r($TabRecap);
//print "<br/>";
__out($TabRecapConges);

