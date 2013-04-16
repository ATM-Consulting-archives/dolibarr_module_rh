<?php

require('../config.php');

//Renvoie les congés du collaborateur de l'année précédente
global $user,$conf;

$ATMdb=new Tdb;
	
$anneeCourante=date('Y');
$anneePrec=$anneeCourante-1;

$TUserID=array();
$sqlReqUser="SELECT rowid, name,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE entity=".$conf->entity;
$ATMdb->Execute($sqlReqUser);

while($ATMdb->Get_line()) {
	$TUserID[]=$ATMdb->Get_field('rowid');
}

foreach($TUserID as $user){
	$sql="SELECT u.name, u.firstname, a.type FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a 
	WHERE u.rowid=a.fk_user 
	AND (a.type='maladiemaintenue' OR a.type='maladienonmaintenue')
	AND a.entity=".$conf->entity;
	//echo $sql;exit;
}




/*
if($result->num_rows > 0){
	__out( array('result'=>1) ); // on enlève l'affichage de la combobox
}else{
	__out( array('result'=>0) ); // on conserve l'affichage de la combobox
}
*/