#!/usr/bin/php
<?php

/*
 * SCRIPT 4 à exécuter
 * 
 */

define('INC_FROM_CRON_SCRIPT', true);

chdir(__DIR__);
	
require('../../config.php');
require('../../class/absence.class.php');
	
$ATMdb=new TPDOdb;
	
//on récupère toutes les dates dont on doit incrémenter le niveau parce que cela fait plus de 10 jours qu'elles ont été créées, 
//et donc doivent aller au niveau 2 de validation
$sql="SELECT date_cre, rowid FROM ".MAIN_DB_PREFIX."rh_absence WHERE NOW()>=ADDDATE(date_cre, 10) AND etat LIKE 'Avalider'";
$ATMdb->Execute($sql);
$TAbsence=array();
while($ATMdb->Get_line()) {
	$TAbsence[] = $ATMdb->Get_field('rowid');
}

foreach($TAbsence as $idAbs){
	$sql="UPDATE ".MAIN_DB_PREFIX."rh_absence SET niveauValidation=niveauValidation+1 WHERE rowid=".$idAbs;
	echo $sql;exit;
	$ATMdb->Execute($sql);
}

$ATMdb->close();
