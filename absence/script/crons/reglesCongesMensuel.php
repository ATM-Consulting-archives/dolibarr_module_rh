#!/usr/bin/php
<?php
/*
 * SCRIPT 2 à exécuter
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);

	chdir(__DIR__);
	
	require('../../config.php');
	require('../../class/absence.class.php');

	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;

	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
	
	/////chaque mois, les congés année N sont incrémentés de 2,08
	$jour=date("d");
	if($jour=='01' || isset($_REQUEST['forceCompteur'])){
		$k=0;
		$sqlReqUser="SELECT fk_user, nombreCongesAcquisMensuel FROM `".MAIN_DB_PREFIX."rh_compteur`";
		$ATMdb->Execute($sqlReqUser);
		$Tab=array();
		while($ATMdb->Get_line()) {
					$Tab[$ATMdb->Get_field('fk_user')] = $ATMdb->Get_field('nombreCongesAcquisMensuel');
		}

		foreach($Tab as $idUser => $nombreConges )
		{
		    //on incrémente chaque mois les jours de congés
			$sqlIncr="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
				SET acquisExerciceN=acquisExerciceN+".$nombreConges." 
				WHERE fk_user=".$idUser;
			$ATMdb->Execute($sqlIncr);
		}
		
	}
	
$ATMdb->close();
	
