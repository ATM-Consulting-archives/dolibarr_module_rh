<?php

	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
	require('../class/analytique_user.class.php');

	$ATMdb=new TPDOdb;

	$f1 = fopen('anal2.csv','r');
	while($ligne = fgetcsv($f1)) {
		
		$login = $ligne[8];
		$code_ana1 = $ligne[0];
		$code_ana_per1 = $ligne[1];
		
		$code_ana2 = $ligne[2];
		$code_ana_per2 = $ligne[3];
		
		$code_ana3 = $ligne[4];
		$code_ana_per3 = $ligne[5];
		
		$code_ana4 = $ligne[6];
		$code_ana_per4 = $ligne[7];
		
		
		
		$user =new User($db);
		if($user->fetch('', $login)>0) {
			
				if(!empty($code_ana1)) {
					
					$a=new TRH_analytique_user;
					
					$a->fk_user = $user->id;
					$a->code = $code_ana1;
					$a->pourcentage = $code_ana_per1;
					$a->save($ATMdb);
					
				}	

				if(!empty($code_ana2)) {
					
					$a=new TRH_analytique_user;
					
					$a->fk_user = $user->id;
					$a->code = $code_ana2;
					$a->pourcentage = $code_ana_per2;
					$a->save($ATMdb);
					
				}	

				if(!empty($code_ana3)) {
					
					$a=new TRH_analytique_user;
					
					$a->fk_user = $user->id;
					$a->code = $code_ana3;
					$a->pourcentage = $code_ana_per3;
					$a->save($ATMdb);
					
				}	

				if(!empty($code_ana4)) {
					
					$a=new TRH_analytique_user;
					
					$a->fk_user = $user->id;
					$a->code = $code_ana4;
					$a->pourcentage = $code_ana_per4;
					
					$a->save($ATMdb);
				}	

		}
		
	}
