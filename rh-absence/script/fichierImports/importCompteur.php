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

			if (empty( $TUser[strtolower($infos[3])])){
				echo 'Erreur : Utilisateur '.strtolower($infos[3]).' inexistant ';
			}else{
				$compteur=new TRH_Compteur;	
				echo 'Ligne traitée : '.strtolower($infos[3]).'';
				$ResteConges=$infos[11]-$infos[12];
				//echo "Trigramme : ".$infos[3].' <br/>';	//colonne D
				//echo "Congés Total Acquis N-1: ".$infos[11].' <br/>';//colonne L
				//echo "Congés pris à N-1: ".$infos[12].' <br/>';//colonne M
				//echo "Reste Congés N-1: ".$ResteConges.' <br/>';
				//echo $infos[13].'<br>';
				//echo "Congés Acquis Exercice N: ".$infos[13].' <br/>';  //colonne N
				
				$resteRttTotal=$infos[15]-$infos[16];
				//echo "RTT Acquis TOTAL : ".$infos[15].' <br/>';
				
				
				//RTT Cumulés
				$prisRttCumule=$infos[18]-$infos[21];
				//echo "RTT Cumulés acquis : ".$infos[18].' <br/>';	//colonne S
				//echo "RTT Cumulés pris : ".$prisRttCumule.' <br/>';	//colonne S-V
				//echo "RTT Cumulés A poser : ".$infos[21].' <br/>';	//colonne V
			
				
				//RTT Non cumulés
				$prisRttNonCumule=$infos[17]-$infos[20];
				//echo "RTT Non Cumulés acquis : ".$infos[17].' <br/>';	//colonne R
				//echo "RTT Non Cumulés pris : ".$prisRttNonCumule.' <br/>';	//colonne R-U
				//echo "RTT Non Cumulés A poser : ".$infos[20].' <br/>';	//colonne U
				
				
				
				echo '<br>';
				
				
				//traitement des lignes et insertion en base
				
				//on récupère le compteur de l'utilisateur si celui-ci existe sinon il sera créé
				$compteur->load_by_fkuser($ATMdb,$TUser[strtolower($infos[3])]);

				$annee=date('Y');
				$anneePrec=$annee-1;
		
				
				
				
				$compteur->anneeN=$annee;
				$compteur->anneeNM1=$anneePrec;
				
				
				//RTT cumulés
				$compteur->rttCumulePris=$prisRttCumule;
				$compteur->rttCumuleAcquis=$infos[18];
				$compteur->rttCumuleReportNM1=$infos[140];
				$compteur->rttCumuleTotal=$compteur->rttCumuleAcquis+$compteur->rttCumuleReportNM1-$compteur->rttCumulePris;
				$compteur->rttAcquisAnnuelCumuleInit=0; 	//à revoir
				
				
				//RTT non cumulés
				$compteur->rttNonCumulePris=$prisRttNonCumule;
				$compteur->rttNonCumuleAcquis=$infos[17];
				$compteur->rttNonCumuleReportNM1=$infos[141];	//	report
				$compteur->rttNonCumuleTotal=$compteur->rttNonCumuleAcquis+$compteur->rttNonCumuleReportNM1-$compteur->rttNonCumulePris;
				$compteur->rttAcquisAnnuelNonCumuleInit=0;		//à revoir
				
				//congés
				$compteur->acquisExerciceNM1=$infos[11];
				$compteur->congesPrisNM1=$infos[12];
				$compteur->acquisExerciceN=$infos[13]; 	//	report
				
				$compteur->date_rttCloture=strtotime(DATE_RTT_CLOTURE);
				$compteur->date_congesCloture=strtotime(DATE_CONGES_CLOTURE);
				
				
				
				$compteur->acquisAncienneteNM1=$infos[94];
				$compteur->acquisAncienneteN=$infos[120];
				
				$compteur->acquisHorsPeriodeNM1=$infos[95];
				$compteur->acquisHorsPeriodeN=$infos[121];
				
				$compteur->reportCongesNM1=$infos[96];
				
				$compteur->rttAcquisMensuelInit=0;
			
				//$infos[71]//info cadre VRAI ou FAUX
				if($infos[71]=="VRAI"){
					$compteur->rttMetier='cadre';
					$compteur->rttTypeAcquisition='Annuel';
					$compteur->rttAcquisAnnuelCumuleInit=5;			////////////////////revoiiir
					$compteur->rttAcquisAnnuelNonCumuleInit=0;
					
				}
				else{
					//on récupère le temps de travail du salarié pour le calcul du rtt enfonction du métier et entreprise
					$sql="SELECT tempsHebdo, societeRtt
						FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps
						WHERE fk_user=".$TUser[strtolower($infos[3])];
					$ATMdb->Execute($sql);
					if($ATMdb->Get_line()) {
						$tpsHebdoUser=$ATMdb->Get_field('tempsHebdo');
						$idSociete=$ATMdb->Get_field('societeRtt');
					}
					
					//on récupère le nom de la société :
					$sql="SELECT label
						FROM ".MAIN_DB_PREFIX."entity
						WHERE rowid=".$idSociete;
					$ATMdb->Execute($sql);
					if($ATMdb->Get_line()) {
						$societe=$ATMdb->Get_field('label');
					}
					
					switch($societe){
						case "C'PRO":
						case "C'PRO GROUPE":	
							if($tpsHebdoUser==37){
								$compteur->rttMetier='noncadre37cpro';
								$compteur->rttAcquisAnnuelCumuleInit=5;
								$compteur->rttAcquisAnnuelNonCumuleInit=7;
							}elseif($tpsHebdoUser==38){
								$compteur->rttMetier='noncadre38cpro';
								$compteur->rttAcquisAnnuelCumuleInit=3;
								$compteur->rttAcquisAnnuelNonCumuleInit=3;
							}elseif($tpsHebdoUser==39){
								$compteur->rttMetier='noncadre39';
								$compteur->rttAcquisAnnuelCumuleInit=0;
								$compteur->rttAcquisAnnuelNonCumuleInit=0;
							}
							$compteur->rttTypeAcquisition='Annuel';
							break;

						case "C'PRO INFORMATIQUE":
							if($tpsHebdoUser==37){
								$compteur->rttMetier='noncadre37cproinfo';
								$compteur->rttAcquisMensuelInit=1;
								$compteur->rttTypeAcquisition='Mensuel';
							}elseif($tpsHebdoUser==38){
								$compteur->rttMetier='noncadre38cproinfo';
								$compteur->rttAcquisMensuelInit=0.5;
								$compteur->rttTypeAcquisition='Mensuel';
							}elseif($tpsHebdoUser==39){
								$compteur->rttMetier='noncadre39';
								$compteur->rttAcquisMensuelInit=0;
								$compteur->rttTypeAcquisition='Mensuel';
							}else{
								$compteur->rttMetier='Autre';
								$compteur->rttAcquisMensuelInit=0;
								$compteur->rttTypeAcquisition='Annuel';
							}
							break;
							
						case "GLOBAL IMPRESSION":
							$compteur->rttTypeAcquisition='Annuel';
							$compteur->rttMetier='aucunrtt';
							break;
						case "AGT":	//aucun RTT
						$compteur->rttTypeAcquisition='Annuel';
							$compteur->rttMetier='aucunrtt';
							break;
						
					}
				}
				
				$compteur->rttannee=$annee;
				$compteur->nombreCongesAcquisMensuel=2.08;

				$compteur->reportRtt=0;
				
				$compteur->save($ATMdb);
			}
		}
		$numLigne++;	
		echo '<br>';
			
	}
}

echo 'Fin du traitement. '.($numLigne).' lignes rajoutés à la table.<br><br>';	

$ATMdb->close();

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





