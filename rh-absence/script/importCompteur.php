<?php

set_time_limit(0);
ini_set("memory_limit", "512M");

require('../config.php');
require('../class/absence.class.php');


global $conf;

$ATMdb=new Tdb;
$compteur=new TRH_Compteur;

		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();

$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[$ATMdb->Get_field('login')] = $ATMdb->Get_field('rowid');
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
		if($numLigne>1){
			echo 'Traitement de la ligne '.$numLigne.'... <br/>';
		
			if ($numLigne >1 ){
				$infos = explode(';', $data[0]);
			
				/*if (empty( $TUser[strtolower($data[3])])){
					echo 'Erreur : Utilisateur '.strtolower($data[3]).' inexistant ';
					break;
				}*/ 
				
				$ResteConges=$data[11]-$data[12];
				echo "Trigramme : ".$data[3].' <br/>';	//colonne D
				echo "Congés Total Acquis N-1: ".$data[11].' <br/>';//colonne L
				echo "Congés pris à N-1: ".$data[12].' <br/>';//colonne M
				echo "Reste Congés N-1: ".$ResteConges.' <br/>';
				echo "Congés Acquis Exercice N: ".$data[13].' <br/>';  //colonne N
				
				$resteRttTotal=$data[15]-$data[16];
				echo "RTT Acquis TOTAL : ".$data[15].' <br/>';
				
				
				//RTT Cumulés
				$resteRttCumule=$data[18]-$data[21];
				echo "RTT Cumulés acquis : ".$data[18].' <br/>';	//colonne S
				echo "RTT Cumulés pris : ".$resteRttCumule.' <br/>';	//colonne S-V
				echo "RTT Cumulés A poser : ".$data[21].' <br/>';	//colonne V
			
				
				//RTT Non cumulés
				$resteRttNonCumule=$data[17]-$data[20];
				echo "RTT Non Cumulés acquis : ".$data[17].' <br/>';	//colonne R
				echo "RTT Non Cumulés pris : ".$resteRttNonCumule.' <br/>';	//colonne R-U
				echo "RTT Non Cumulés A poser : ".$data[20].' <br/>';	//colonne U
				
				
				
				echo '<br>';
				
				
				//traitement des lignes et insertion en base
				
				//on récupère le compteur de l'utilisateur si celui-ci existe sinon il sera créé
				$compteur->chargementCompteur($ATMdb, $data[3]);
				
				$annee=date('Y');
				$anneePrec=$annee-1;
		
				$compteur->acquisExerciceN=$data[13]; 
				//$compteur->acquisAncienneteN=0;
				//$compteur->acquisHorsPeriodeN=0;
				$compteur->anneeN=$annee;
				$compteur->acquisExerciceNM1=$data[11];
				//$compteur->acquisAncienneteNM1=0;
				//$compteur->acquisHorsPeriodeNM1=0;
				//$compteur->reportCongesNM1=0;
				$compteur->congesPrisNM1=$data[12];
				$compteur->anneeNM1=$anneePrec;
				$compteur->rttPris=0;
				$compteur->rttTypeAcquisition='Annuel';
				$compteur->rttAcquisMensuelInit=0;
				$compteur->rttAcquisMensuelTotal=0;
				$compteur->rttAcquisAnnuelCumuleInit=5;
				$compteur->rttAcquisAnnuelNonCumuleInit=7;
				$compteur->rttAcquisMensuel=0;
				$compteur->rttAcquisAnnuelCumule=5;
				$compteur->rttAcquisAnnuelNonCumule=7;
				$compteur->rttMetier='cadre';
				$compteur->rttannee=$annee;
				$compteur->nombreCongesAcquisMensuel=2.08;
				$compteur->date_rttCloture=strtotime('2013-03-01 00:00:00'); // AA Ne devrait pas être en dur mais en config
				$compteur->date_congesCloture=strtotime('2013-06-01 00:00:00');
				$compteur->reportRtt=0;
				$compteur->entity = $conf->entity;
				
				$compteur->save($ATMdb);
				
				
			}
		}
		$numLigne++;
	}
}
echo 'Fin du traitement. '.($numLigne).' lignes rajoutés à la table.<br><br>';	

$ATMdb->close();

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





