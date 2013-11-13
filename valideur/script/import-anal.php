<?php

	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
	require('../class/analytique_user.class.php');

	$ATMdb=new TPDOdb;

	$TUser=array();

	$f1 = fopen('anal3.csv','r');
	while($ligne = fgetcsv($f1, 4096,';')) {
		//print_r($ligne);
		$login = $ligne[5];
		if($user->fetch('', $login)>0) {
			
			$TUser[$user->id][]=array(
				'code'=>$ligne[3]
				,'pourcentage'=>$ligne[4]
			);
			
		}
		
	}
	
	/*print_r($TUser);
	exit;*/
	foreach($TUser as $fk_user=>$TAnal) {	
		
		$ATMdb->Execute("DELETE FROM llx_rh_analytique_user WHERE fk_user=".$fk_user);
		
		foreach($TAnal as $anal) {
			$a=new TRH_analytique_user;
					
			$a->fk_user =$fk_user;
			$a->code = $anal['code'];
			$a->pourcentage = $anal['pourcentage'];
			$a->save($ATMdb);
		
		}
		
	}
