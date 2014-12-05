<?php

	ini_set('memory_limit', '-1');
	ini_set('display_errors', true);
	set_time_limit(0);

	require('../../config.php');

	dol_include_once('/absence/class/absence.class.php');

	$f1 = fopen('compteurGenin.csv','r');
	
	$ATMdb=new TPDOdb;
	
	while($row = fgetcsv($f1,4096,';','"')) {
		
		$login='';
		$var = explode(' ',$row[2]);
		foreach($var as $v) $login.=substr($v,0 ,1);

		$login = strtr(strtolower($login.$row[1]) , array(' '=>'')  );
		
		if(empty($login)) continue;

		$u=new User($db);
		$u->fetch('', $login);

		if($u->id>0) {
			print "Mise à jour compteur {$u->login}...";
			$compteur=new TRH_Compteur;	
			$compteur->load_by_fkuser($ATMdb, $u->id);
				
			$compteur->initCompteur($ATMdb, $u->id);
			
			$compteur->acquisExerciceN=$row[16]; 
			$compteur->acquisAncienneteN=0;
			$compteur->acquisHorsPeriodeN=0;
			$compteur->anneeN=$annee;
			$compteur->acquisExerciceNM1=$row[17];
			$compteur->acquisAncienneteNM1=0;
			$compteur->acquisHorsPeriodeNM1=0;
			$compteur->reportCongesNM1=0;
			$compteur->congesPrisNM1=$row[18];
			$compteur->congesPrisN=0;
			$compteur->anneeNM1=$anneePrec;
			$compteur->rttTypeAcquisition='Annuel';
			
			$compteur->rttAcquisMensuelInit=0;
			
			
			$compteur->rttCumuleAcquis=0;
			$compteur->rttAcquisAnnuelCumuleInit=0;
			$compteur->rttCumuleReportNM1=0;
			$compteur->rttCumulePris=0;
			$compteur->rttCumuleTotal=$compteur->rttCumuleAcquis+$compteur->rttCumuleReportNM1-$compteur->rttCumulePris;
			
			$compteur->rttNonCumuleAcquis=0;
			$compteur->rttNonCumuleReportNM1=0;
			$compteur->rttAcquisAnnuelNonCumuleInit=0;
			$compteur->rttNonCumulePris=0;
			$compteur->rttNonCumuleTotal=$compteur->rttNonCumuleAcquis+$compteur->rttNonCumuleReportNM1-$compteur->rttNonCumulePris;
			
			
			$compteur->rttMetier='none';
			$compteur->rttannee=$annee;
			$compteur->nombreCongesAcquisMensuel=2.08;
			$compteur->date_rttCloture=strtotime($conf->global->RH_DATE_RTT_CLOTURE); 
			$compteur->date_congesCloture=strtotime($conf->global->RH_DATE_CONGES_CLOTURE);
			$compteur->reportRtt=0;
			
			$compteur->is_archive=0;
//			var_dump($compteur);exit;
			$compteur->save($ATMdb);
			print "ok<br />";
		}
		
	}
