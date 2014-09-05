<?php

set_time_limit(0);
ini_set("memory_limit", "512M");

require('../../config.php');
require('../../class/absence.class.php');
require('../../lib/absence.lib.php');


global $conf, $langs;

$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);
		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();

$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('login'))] = $ATMdb->Get_field('rowid');
}


$TUserEdt = array();
$sql="SELECT rowid, fk_user FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUserEdt[$ATMdb->Get_field('fk_user')] = $ATMdb->Get_field('rowid');
}
//print_r($TUserEdt);


//chargement des groupes et des users dans la liste $TGroups;
$TGroups= array();
$sql="SELECT fk_user, fk_usergroup
	FROM ".MAIN_DB_PREFIX."usergroup_user
	";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');

}



//----------------DEBUT DU TRAITEMENT DES LIGNES D'APPELS----------------------------------------------------------
$nomFichier = "./ImportAbsenceValidee.csv";
echo $langs->trans('FileProcessing', $nomFichier) . ' : <br><br>';


//début du parsing
$numLigne = 0;
$cpt = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		
		if($numLigne>0){
			
			$infos = explode(';', $data[0]);

			$login = strtolower($infos[4]);
			
			//echo $infos[4].$TUserEdt[$TUser[strtolower($infos[4])]]."<br>";
			if(!isset( $TUser[$login] )){	//si le login n'existe pas, on ne traite pas la ligne
				echo $langs->trans('ErrNot', strtolower($infos[4])) . '<br>';
			else {
				echo $TUser[$login].'<br>';
				if ( !isset($TUserEdt[$TUser[$login]] )){	//si le login n'existe pas, on ne traite pas la ligne
					echo $langs->trans('ErrNonExistentUser', $login);
					//null;
				}
				else{
					echo $langs->trans('LineProcessing', $numLigne) . '...';
					echo $infos[4];
					//echo '<br/>';
					$absence=new TRH_Absence;
					$absence->load_by_idImport($ATMdb,$infos[0]);
					
					$absence->fk_user=$TUser[strtolower($infos[4])];
					$absence->idAbsImport=$infos[0];
					echo $absence->idAbsImport;
					$absence->etat='Validee';
					$absence->commentaireValideur=$infos[2];
					
					
					switch($infos[1]){
						case 40:
							$absence->type='conges';
							$absence->libelle=saveLibelle($absence->type);
							$absence->code='0950';//saveCodeTypeAbsence($ATMdb, $absence->type);
							
							break;
						case 73:
							$absence->type='rttcumule';
							$absence->libelle=saveLibelle($absence->type);
							$absence->code='0940';//saveCodeTypeAbsence($ATMdb, $absence->type);

							break;
						case 74:
							$absence->type='rttnoncumule';
							$absence->libelle=saveLibelle($absence->type);
							$absence->code='0930';//saveCodeTypeAbsence($ATMdb, $absence->type);

							break;
					}
					
					$absence->libelle=saveLibelle($absence->type);
					$absence->libelleEtat='Acceptée';
					
					
					//on teste si le début de la demande d'absence et sa fin est le matin ou l'pm
					//début d'absence
					if(substr($infos[5],'-12','2')<=12){
						$absence->ddMoment='matin';
					}
					else{
						$absence->ddMoment='apresmidi';
					}; 
					//fin d'absence
					if(substr($infos[6],'-12','2')<=12){
						$absence->dfMoment='matin';
					}
					else{
						$absence->dfMoment='apresmidi';
					}; 
					
					
					$absence->date_debut=strtotime($infos[7]);
					$absence->date_fin=strtotime($infos[8]);
					
					$absence->duree=0;
					$absence->dureeHeure=0;
					
					//calcul de la durée des absences en jours
					$dureeAbsenceCourante=$absence->calculDureeAbsence($ATMdb, $absence->date_debut, $absence->date_fin, $absence);
					
					$dureeAbsenceCourante=$absence->calculJoursFeries($ATMdb, $dureeAbsenceCourante, $absence->date_debut, $absence->date_fin, $absence);
					//echo "ici".$dureeAbsenceCourante;exit;
					$dureeAbsenceCourante=$absence->calculJoursTravailles($ATMdb, $dureeAbsenceCourante, $absence->date_debut, $absence->date_fin, $absence); 

					$absence->duree=$dureeAbsenceCourante;
 					
					
					//on calcule la durée des absences en heures pour l'export en paie
					$sql="SELECT tempsHebdo FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps 
					WHERE fk_user=".$absence->fk_user;
					$ATMdb->Execute($sql);
					if ($ATMdb->Get_line()) {$tpsHebdo=$ATMdb->Get_field('tempsHebdo');	}
					
					if($tpsHebdo>=35){
						$absence->dureeHeurePaie = $absence->duree*7;
					}
					else $absence->dureeHeurePaie = $absence->dureeHeure;
					
					$absence->niveauValidation=1;
					
					$absence->save($ATMdb);
					$cpt++;
			}
			
			}	
			}
			$numLigne++;	
			
		}
}


//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------


echo $langs->trans('EndOfProcessing') . ' ' . $langs->trans('AddedLines', $cpt) . '<br><br>';	
//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $langs->trans('EndOfProcessing') . ' ' . $langs->trans('ExecutionTime', $page_load_time) . '<br><br>';
$ATMdb->close();





