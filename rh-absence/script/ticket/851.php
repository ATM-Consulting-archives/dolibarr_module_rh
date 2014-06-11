<?php

	require('../../config.php');
	
ini_set('display_errors',1);

	$f1=fopen('851.csv','r') or die('fichier');
	
	$ATMdb=new TPDOdb;
	
	while($row = fgetcsv($f1, 4096, ';', '"')) {
		
		$nom = $row[1];
		$prenom = $row[2];
		
		$cp = $row[4];
		
		$cpreport_reel = strtr($row[5],',','.');
		
		
		$fk_user = _get_fk_user($ATMdb, $nom, $prenom);
	
		$nb_conges = _get_conges($ATMdb, $fk_user);

		
		$sql=" UPDATE ".MAIN_DB_PREFIX."rh_compteur 
				SET congesPrisNM1 = $nb_conges, reportCongesNM1=".$cpreport_reel."
				WHERE fk_user=$fk_user;
		";
	
		print $sql.'<br />';
		
	}
	
function _get_fk_user(&$ATMdb, $nom, $prenom) {

$trans=array(' '=>'%');
$prenom = strtr($prenom, $trans);
$nom = strtr($nom, $trans);

$sql = "SELECT rowid
FROM ".MAIN_DB_PREFIX."user
WHERE name LIKE ".$ATMdb->quote($nom)." AND firstname LIKE ".$ATMdb->quote($prenom)." AND statut=1";
//print $sql;
	$Tab  = $ATMdb->ExecuteAsArray($sql);

	if(count($Tab)!=1) {
//		var_dump($Tab, $nom, $prenom);
		
// exit('Erreur'); 

return -1;
	}
	else {
		return $Tab[0]->rowid;
	}
}
function _get_conges(&$ATMdb, $fk_user) {

	$sql="SELECT SUM(duree) as nb FROM llx_rh_absence WHERE fk_user=$fk_user AND type IN ('conges','cppartiel') AND date_debut>='2014-06-01' AND etat!='Refusee' ";

	$ATMdb->Execute($sql);
	$ATMdb->Get_line();

	return (float)$ATMdb->Get_field('nb');

}
