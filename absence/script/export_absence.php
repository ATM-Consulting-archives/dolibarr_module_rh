<?php
/*
 * Script exportant toutes les lignes de notes de frais étant classées comme "comptabilisées"
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
		
	$ATMdb=new TPDOdb;
	$fichier = fopen("./comptaAbsence.txt", "w+"); 
	$sql="SELECT u.rowid, u.lastname, u.firstname, a.type, a.duree, a.date_debut, a.date_fin, a.ddMoment, a.dfMoment
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a
	WHERE u.rowid=a.fk_user
	AND a.etat='Validee'
	ORDER BY u.rowid";

	$ATMdb->Execute($sql);
	
	while($ATMdb->Get_line()) {
				$rowid=$ATMdb->Get_field('rowid');
				$name=$ATMdb->Get_field('lastname');
				$firstname=$ATMdb->Get_field('firstname');
				$type=$ATMdb->Get_field('type');
				$date_debut=dol_print_date($ATMdb->Get_field('date_debut'),"day");
				$ddMoment=$ATMdb->Get_field('ddMoment');
				$date_fin=dol_print_date($ATMdb->Get_field('date_fin'),"day");
				$dfMoment=$ATMdb->Get_field('dfMoment');
				$duree=$ATMdb->Get_field('duree');
				
				$line = array($rowid, $name, $firstname,  $type, $date_debut, $ddMoment, $date_fin, $dfMoment, $duree);
				$line_implode=implode("\t", $line);
				fputs($fichier, $line_implode);
				fputs($fichier, "\n");
	}
	
	fclose($fichier);
	return 1;