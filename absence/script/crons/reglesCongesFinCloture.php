#!/usr/bin/php
<?php
/*
 * SCRIPT 1 à exécuter
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	
	chdir(__DIR__);
	
	require('../../config.php');
	require('../../class/absence.class.php');

	$PDOdb=new TPDOdb;
//	$PDOdb->debug=true;

	$o=new TRH_Compteur;
	$o->init_db_by_vars($PDOdb); // TODO remove or not : on sait jamais, dans la nuit :-/
	
	
	//on récupère la date de fin de cloture des congés
	$sqlReqCloture="SELECT fk_user, date_congesCloture FROM `".MAIN_DB_PREFIX."rh_compteur`";
	$PDOdb->Execute($sqlReqCloture);
	$Tab=array();
	while($PDOdb->Get_line()) {
				$Tab[$PDOdb->Get_field('fk_user')] = $PDOdb->Get_field('date_congesCloture');
	}

	foreach($Tab as $idUser => $dateCloture )
	{
		$u=new User($db);
		$u->fetch($idUser);
		
		if($u->id<=0)continue;

	   	echo $u->getNomUrl(1)." ".$dateCloture. "...";

		$date=strtotime('+1day',strtotime($dateCloture)); // Car on passe à 1h du matin le lendemain
		$dateMD=date('Ymd',$date);
		////// 1er juin, tous les congés de l'année N sont remis à 0, et sont transférés vers le compteur congés N-1
		$juin=date('Ymd');
//var_dump( $juin , $dateMD);
		echo $juin.'?='.$dateMD.'...';	

		if(!strcmp($juin,$dateMD) || isset($_REQUEST['force_for_test'])){
			
			echo 'Oui...';

			$compteur=new TRH_Compteur;
			$compteur->load_by_fkuser($PDOdb, $idUser);
			$compteur->reportCongesNM1 = 0;
			$compteur->congesPrisNM1=$compteur->congesPrisN;
			
			$compteur->acquisExerciceNM1 = ceil($compteur->acquisExerciceN) + $compteur->nombrecongesAcquisAnnuel;
			
			$compteur->acquisAncienneteNM1 = $compteur->acquisAncienneteN;
			$compteur->acquisHorsPeriodeNM1 = $compteur->acquisHorsPeriodeN;
			
			$compteur->acquisExerciceN = 0;
			$compteur->acquisHorsPeriodeN = 0;
			$compteur->congesPrisN = 0;
			$compteur->date_congesCloture = strtotime('+1 year',strtotime($dateCloture));
			
			$compteur->save($PDOdb);
		}
		else {
			echo 'Non...';
		}

		echo '<br />';
	}
	
$PDOdb->close();
