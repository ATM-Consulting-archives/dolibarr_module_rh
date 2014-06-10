<?php

	require('../../config.php');
	
	$f1=fopen('851.cvs','r')  or die('fichier');
	
	$ATMdb=new TPDOdb;
	
	while($row = fgetcsv($f1, 4096, ';', '"')) {
		
		$nom = $row[1];
		$prenom = $row[2];
		
		$cp = $row[4];
		
		$cpreport_reel = $row[5];
		
		
		$fk_user = _get_fk_user($ATMdb, $nom, $prenom);
		
		$sql=" UPDATE ".MAIN_DB_PREFIX."rh_compteur 
				SET congesPrisNM1 = congesPrisNM1 - (reportCongesNM1 - $cpreport_reel), reportCongesNM1=$cpreport_reel
				WHERE fk_user=$fk_user;
		";
	
		print $sql.'<br />';
		
	}
	
function _get_fk_user(&$ATMdb, $nom, $prenom) {

	$Tab  = $ATMdb->ExecuteAsArray("SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE name=".$ATMdb->quote($nom)." AND fistname=".$ATMdb->quote($prenom)."");

	if(count($Tab)!=1) {
		var_dump($Tab, $nom, $prenom);
		
		exit('Erreur');
	}
	else {
		return $Tab[0]->rowid;
	}
}
