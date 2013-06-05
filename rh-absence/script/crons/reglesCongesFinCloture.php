#!/usr/bin/php
<?php
/*
 * SCRIPT 1 à exécuter
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../../config.php');
	require('../../class/absence.class.php');

	$ATMdb=new Tdb;
	$ATMdb->db->debug=true;

	$o=new TRH_Compteur;
	$o->init_db_by_vars($ATMdb);
	
	
	
	//on récupère la date de fin de cloture des congés
	$k=0;
	$sqlReqCloture="SELECT fk_user, date_congesCloture FROM `".MAIN_DB_PREFIX."rh_compteur`";
	$ATMdb->Execute($sqlReqCloture);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('fk_user')] = $ATMdb->Get_field('date_congesCloture');
	}

	foreach($Tab as $idUser => $dateCloture )
	{
	   	//echo $idUser." ".$dateCloture. "<br/>";
		$date=strtotime($dateCloture);
		$dateMD=date("dm",$date);
	
		////// 1er juin, tous les congés de l'année N sont remis à 0, et sont transférés vers le compteur congés N-1
		$juin=date("dm");
		if($juin==$dateMD){
			//on transfère les jours N-1 non pris vers jours report
			$sqlTransfert="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
				SET reportCongesNM1=CEILING(acquisExerciceNM1)+acquisAncienneteNM1+acquisHorsPeriodeNM1 
				WHERE fk_user =".$idUser;
			$ATMdb->Execute($sqlTransfert);
			
			//on transfère les jours acquis N vers N-1
			$sqlTransfert2="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
			SET acquisExerciceNM1=acquisExerciceN, acquisAncienneteNM1=acquisAncienneteN,
			acquisHorsPeriodeNM1=acquisHorsPeriodeN 
			WHERE fk_user =".$idUser;
			$ATMdb->Execute($sqlTransfert2);
			
			//on remet à 0 les jours année courante
			//L'ancienneté devra normalement être gérée manuellement. 
			$sqlRaz="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
			SET acquisExerciceN=0, acquisHorsPeriodeN=0 WHERE fk_user =".$idUser;
			$ATMdb->Execute($sqlRaz);
			
		}
	}
	
	
	
	//on incrémente les années
	$annee=date("dm");
	if($annee=="0101"){
		//on transfère les jours N-1 non pris vers jours report
		$sqlAnnee="UPDATE ".MAIN_DB_PREFIX."rh_compteur SET anneeN=anneN+1, anneeNM1=anneNM1+1";
		$ATMdb->Execute($sqlAnnee);	
	}
