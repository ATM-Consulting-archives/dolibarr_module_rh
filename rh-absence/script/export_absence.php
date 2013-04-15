<?php
/*
 * Script exportant toutes les lignes de notes de frais étant classées comme "comptabilisées"
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
		
	$ATMdb=new Tdb;
	$fichier = fopen("./comptaAbsence.txt", "w+"); 
	$sql="SELECT u.rowid, u.name, u.firstname, a.type, a.date_debut, a.date_fin 
	FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."rh_absence as a
	WHERE u.rowid=a.fk_user
	AND a.etat='Validee'";

	$ATMdb->Execute($sql);
	
	while($ATMdb->Get_line()) {
				$rowid=$ATMdb->Get_field('rowid');
				$name=$ATMdb->Get_field('name');
				$firstname=$ATMdb->Get_field('firstname');
				$type=$ATMdb->Get_field('type');
				$date_debut=dol_print_date($ATMdb->Get_field('date_debut'),"day");
				$date_fin=dol_print_date($ATMdb->Get_field('date_fin'),"day");
				
				$line = array($rowid, $name, $firstname,  $type, $date_debut, $date_fin);
				$line_implode=implode("\t", $line);
				fputs($fichier, $line_implode);
				fputs($fichier, "\n");
	}
	
	fclose($fichier);
	