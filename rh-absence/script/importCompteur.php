<?php
require('../config.php');
require('../class/absence.class.php');

global $conf;

$ATMdb=new Tdb;

		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();

$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}



//chargement des groupes et des users dans la liste $TGroups;
$TGroups= array();
$sql="SELECT fk_user, fk_usergroup
	FROM ".MAIN_DB_PREFIX."usergroup_user
	WHERE entity IN (0,".$conf->entity.")
	";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');

}



//----------------DEBUT DU TRAITEMENT DES LIGNES D'APPELS----------------------------------------------------------
$nomFichier = "./fichierImports/compteurSalaries.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';



//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		if($numLigne!=0){
			echo 'Traitement de la ligne '.$numLigne.'... <br/>';
		
			if ($numLigne <=3 ){
				$infos = explode(';', $data[0]);
			
				/*if (! array_key_exists ( strtolower($infos[2]) , $TUser )){
					echo 'Erreur : Utilisateur '.strtolower($infos[2]).' inexistant ';
					break;
				}*/
				
				$ResteConges=$data[11]-$data[12];
				echo "Trigramme : ".$data[3].' <br/>';	//colonne D
				echo "  Congés Total Acquis N-1: ".$data[11].' <br/>';//colonne L
				echo "  Congés pris à N-1: ".$data[12].' <br/>';//colonne M
				echo "  Reste Congés N-1: ".$ResteConges.' <br/>';
				echo "  Congés Acquis Exercice N: ".$data[13].' <br/>';  //colonne N
				
				echo "  RTT Acquis TOTAL : ".$data[15].' <br/>';
				
				//RTT Cumulés
				$resteRttCumule=$data[18]-$data[21];
				echo "  RTT Cumulés acquis : ".$data[18].' <br/>';	//colonne S
					echo "  RTT Cumulés pris : ".$resteRttCumule.' <br/>';	//colonne S-V
				echo "  RTT Cumulés A poser : ".$data[21].' <br/>';	//colonne V
			
				
				//RTT Non cumulés
				$resteRttNonCumule=$data[17]-$data[22];
				echo "  RTT Non Cumulés acquis : ".$data[17].' <br/>';	//colonne R
				echo "  RTT Non Cumulés pris : ".$resteRttNonCumule.' <br/>';	//colonne R-U
				echo "  RTT Non Cumulés A poser : ".$data[22].' <br/>';	//colonne U
				
				
	
				
				echo '<br>';
				
			}
			echo 'Fin du traitement. '.($numLigne).' lignes rajoutés à la table.<br><br>';	
		}
		$numLigne++;
	}
}


//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





