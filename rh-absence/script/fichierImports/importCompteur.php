<?php

set_time_limit(0);
ini_set("memory_limit", "512M");

require('../../config.php');
require('../../class/absence.class.php');


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
$nomFichier = "./compteurSalaries.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle,0,'\r')) != false){
		
		
		if($numLigne>0){
				$data[0]=str_replace(",",'.',$data[0]);
				$infos = explode(';', $data[0]);
			
				
				/*if (empty( $TUser[strtolower($data[3])])){
					echo 'Erreur : Utilisateur '.strtolower($data[3]).' inexistant ';
					break;
				}*/ 
			
				$ResteConges=$infos[11]-$infos[12];
				echo "Trigramme : ".$infos[3].' <br/>';	//colonne D
				echo "Congés Total Acquis N-1: ".$infos[11].' <br/>';//colonne L
				echo "Congés pris à N-1: ".$infos[12].' <br/>';//colonne M
				echo "Reste Congés N-1: ".$ResteConges.' <br/>';
				echo $infos[13].'<br>';
				echo "Congés Acquis Exercice N: ".$infos[13].' <br/>';  //colonne N
				
				$resteRttTotal=$infos[15]-$infos[16];
				echo "RTT Acquis TOTAL : ".$infos[15].' <br/>';
				
				
				//RTT Cumulés
				$prisRttCumule=$infos[18]-$infos[21];
				echo "RTT Cumulés acquis : ".$infos[18].' <br/>';	//colonne S
				echo "RTT Cumulés pris : ".$prisRttCumule.' <br/>';	//colonne S-V
				echo "RTT Cumulés A poser : ".$infos[21].' <br/>';	//colonne V
			
				
				//RTT Non cumulés
				$prisRttNonCumule=$infos[17]-$infos[20];
				echo "RTT Non Cumulés acquis : ".$infos[17].' <br/>';	//colonne R
				echo "RTT Non Cumulés pris : ".$prisRttNonCumule.' <br/>';	//colonne R-U
				echo "RTT Non Cumulés A poser : ".$infos[20].' <br/>';	//colonne U
				
				
				
				echo '<br>';
				
				
				//traitement des lignes et insertion en base
				
				//on récupère le compteur de l'utilisateur si celui-ci existe sinon il sera créé
				$compteur->load_by_fkuser($ATMdb,$TUser[strtolower($infos[3])]);

				$annee=date('Y');
				$anneePrec=$annee-1;
		
				
				//$compteur->acquisAncienneteN=0;
				//$compteur->acquisHorsPeriodeN=0;
				$compteur->anneeN=$annee;
				$compteur->anneeNM1=$anneePrec;
				
				
				//RTT cumulés
				$compteur->rttCumulePris=$prisRttCumule;
				$compteur->rttAcquisAnnuelCumuleInit=$infos[18];
				
				
				//RTT non cumulés
				$compteur->rttNonCumulePris=$prisRttNonCumule;
				$compteur->rttAcquisAnnuelNonCumuleInit=7;
				
				//congés
				$compteur->acquisExerciceNM1=$infos[11];
				$compteur->congesPrisNM1=$infos[12];
				$compteur->acquisExerciceN=$infos[13]; 
				
				
				//$compteur->acquisAncienneteNM1=0;
				//$compteur->acquisHorsPeriodeNM1=0;
				//$compteur->reportCongesNM1=0;
				
				//$compteur->rttTypeAcquisition='Annuel';
				//$compteur->rttAcquisMensuelInit=0;
				
				//$compteur->rttAcquisMensuel=0;
				//$compteur->rttAcquisAnnuelCumule=5;
				//$compteur->rttAcquisAnnuelNonCumule=7;
				//$compteur->rttMetier='cadre';
				$compteur->rttannee=$annee;
				$compteur->nombreCongesAcquisMensuel=2.08;
				$compteur->date_rttCloture=strtotime('2013-03-01 00:00:00'); // AA Ne devrait pas être en dur mais en config
				$compteur->date_congesCloture=strtotime('2013-06-01 00:00:00');
				//$compteur->reportRtt=0;

				exit;
				//$compteur->save($ATMdb);*/
				
			}	
			$numLigne++;	
			echo '<br>';
		}
}

echo 'Fin du traitement. '.($numLigne).' lignes rajoutés à la table.<br><br>';	

$ATMdb->close();

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





