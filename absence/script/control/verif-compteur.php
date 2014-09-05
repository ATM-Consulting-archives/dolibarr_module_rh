<?php

	require('../../config.php');

	require('../../class/absence.class.php');
	
	$f1=fopen('CPRO.csv','r');
	fgets($f1);
	
	$ATMdb = new TPDOdb;
	
	while (($row = fgetcsv($f1, 0, ";")) !== FALSE) {
        
		$login = $row[0];
		
		$user=new User($db);
		$user->fetch('', $login);
		
		print "test de $login ({$user->id})...";
		
		$c=new TRH_Compteur;
		$c->load_by_fkuser($ATMdb, $user->id);
		
		$rtt = (double)strtr($row[3],',','.');
		$congeRestant = (double)strtr($row[4],',','.');
		$congeAcquis = (double)strtr($row[5],',','.');
		
		//print "(RTT $rtt, Conge restant $congeRestant, Conge Acquis $congeAcquis)";
		//print "{$c->acquisExerciceNM1} + {$c->acquisAncienneteN}+{$c->acquisHorsPeriodeNM1} + {$c->reportCongesNM1} - {$c->congesPrisNM1}";
		
		$congePrecTotal = $c->acquisExerciceNM1 + $c->acquisAncienneteN+$c->acquisHorsPeriodeNM1 + $c->reportCongesNM1;
		$congePrecReste = $congePrecTotal - $c->congesPrisNM1;
		
		$rttC = $c->rttCumuleTotal;
		
		if($congePrecReste != $congeRestant) {
			
			
			$ATMdb->Execute("SELECT rowid FROM llx_rh_absence WHERE fk_user=".$user->id." AND type='conges' AND date_fin>'2013-08-20' ");
			$TAbs = $ATMdb->Get_All();
			$dureePlus = 0;
			foreach($TAbs as $abs) {
				
				$absence = new TRH_Absence;
				$absence->load($ATMdb, $abs->rowid);
						
				if($absence->date_debut < strtotime('2013-08-21') ) $absence->date_debut = 	strtotime('2013-08-21');
				//print $absence->get_date('date_debut');
				$dureePlus += $absence->calculDureeAbsenceParAddition($ATMdb);	
				//print " $dureePlus ";		
			}
			
		
			
			if($congePrecReste+$dureePlus!=$congeRestant) {
				print '<span style="color:red;'.($dureePlus==0?'font-weight:bold;':'').'">'. $langs->trans('RemainingHolidays') . ' ' . ($congePrecReste+$dureePlus). ' ' . $langs->trans('InsteadOf') . ' ' .$congeRestant.'</span>';	
			}
			
		}
		
		if($rtt!=$rttC) {
			
			$ATMdb->Execute("SELECT rowid FROM llx_rh_absence WHERE fk_user=".$user->id." AND type LIKE 'rttcumule' AND date_fin>'2013-08-20' ");
			$TAbs = $ATMdb->Get_All();
			$dureePlus=0;
			foreach($TAbs as $abs) {
				
				$absence = new TRH_Absence;
				$absence->load($ATMdb, $abs->rowid);
						
				if($absence->date_debut < strtotime('2013-08-21') ) $absence->date_debut = 	strtotime('2013-08-21');
				//print $absence->get_date('date_debut');
				$dureePlus += $absence->calculDureeAbsenceParAddition($ATMdb);	
				//print " $dureePlus ";		
			}
			
			
			if($rttC+$dureePlus!=$rtt) {
				print ' <span style="color:orange;">' . $langs->trans('RemainingDayOff') . ' ' .($rttC+$dureePlus). ' ' . $langs->trans('InsteadOf') . ' ' .$rtt.'</span>';	
			}
			
			
		}
		
		
		print "<br />";
    }
	
	fclose($f1);
