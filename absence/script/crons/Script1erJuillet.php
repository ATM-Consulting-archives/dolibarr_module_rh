	#!/usr/bin/php
	<?php
	
	define('INC_FROM_CRON_SCRIPT', true);
	
	chdir(__DIR__);
	
	require('../../config.php');
	require('../../class/absence.class.php');

	$ATMdb=new TPDOdb;
	$ATMdb->db->debug=true;


	$sqlMois="SELECT fk_user, rttAcquisMensuelInit 
	FROM `".MAIN_DB_PREFIX."rh_compteur` 
	WHERE rttTypeAcquisition='Mensuel'";
	$ATMdb->Execute($sqlMois);
	$Tab=array();
	while($ATMdb->Get_line()) {
			$Tab[$ATMdb->Get_field('fk_user')]['rttAcquisMensuelInit'] = $ATMdb->Get_field('rttAcquisMensuelInit');
			$Tab[$ATMdb->Get_field('fk_user')]['fk_user'] = $ATMdb->Get_field('fk_user');
	}

	foreach($Tab as $TabMois){
		//on incrémente de 1 par exemple, suivant ce qui est donné dans la base
		$sqlIncr='UPDATE '.MAIN_DB_PREFIX.'rh_compteur 
		SET rttCumuleAcquis=rttCumuleAcquis+'.$TabMois['rttAcquisMensuelInit'].' 
		WHERE rttTypeAcquisition="Mensuel" 
		AND fk_user='.$TabMois['fk_user'];
		$ATMdb->Execute($sqlIncr);
	}

	
	
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