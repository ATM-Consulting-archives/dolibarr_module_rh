<?php

	require('../../config.php');

	require('../../class/absence.class.php');
	
	$f1=fopen('CPRO.csv','r');
	fgets($f1);
	
	$ATMdb = new TPDOdb;
	
	while (($row = fgetcsv($f1, 0, ";")) !== FALSE) {
        
		$login = $row[0];
		print "test de $login...";

		$user=new User($db);
		$user->fetch('', $login);
		
		$c=new TRH_Compteur;
		$c->load_by_fkuser($ATMdb, $user->id);
		
		$rtt=(double)strtr($row[3],',','.');
		$congeRestant=(double)strtr($row[4],',','.');
		$congeAcquis=(double)strtr($row[5],',','.');
		
		//print "(RTT $rtt, Conge restant $congeRestant, Conge Acquis $congeAcquis)";
		//print "{$c->acquisExerciceNM1} + {$c->acquisAncienneteN}+{$c->acquisHorsPeriodeNM1} + {$c->reportCongesNM1} - {$c->congesPrisNM1}";
		
		$congePrecTotal=$c->acquisExerciceNM1 + $c->acquisAncienneteN+$c->acquisHorsPeriodeNM1 + $c->reportCongesNM1;
		$congePrecReste=$congePrecTotal-$c->congesPrisNM1;
		
		$rttC = $c->rttCumuleTotal + $c->rttNonCumuleTotal;
		
		if($congePrecReste!=$congeRestant) {
			print '<span style="color:red;">CP restant '.$congePrecReste.' au lieu de '.$congeRestant.'</span>';
		}
		if($rtt!=$rttC) {
			print ' <span style="color:orange;">RTT restant '.$rttC.' au lieu de '.$rtt.'</span>';
		}
		
		
		print "<br />";
    }
	
	fclose($f1);
