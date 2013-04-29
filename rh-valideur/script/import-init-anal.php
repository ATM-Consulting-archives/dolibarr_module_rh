<?php

	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');

	$f1 = fopen('anal.csv','r');
	while($ligne = fgetcsv($f1)) {
		
		$login = $ligne[4];
		$code_ana = $ligne[2];
		
		$user =new User($db);
		if($user->fetch('', $login)>0) {
			
			$user->array_options["options_code_analytique"] = $code_ana;
			$user->update($user);
		}
		
	}
