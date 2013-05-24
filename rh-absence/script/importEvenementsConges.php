<?php

set_time_limit(0);
ini_set("memory_limit", "512M");

require('../config.php');
require('../class/absence.class.php');
require('../lib/absence.lib.php');


global $conf;

$ATMdb=new Tdb;


		
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
$nomFichier = "./fichierImports/evenements.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';


//début du parsing
$numLigne = 0;
$cpt = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		
		if($numLigne>1){
			$infos = explode(';', $data[0]);
			
			
			if (empty( $TUser[strtolower($infos[4])])){	//si le login n'existe pas, on ne traite pas la ligne
				echo 'Erreur : Utilisateur '.strtolower($infos[3]).' inexistant ';
			}
			else{
					echo 'Traitement de la ligne '.$numLigne.'...';
					echo $infos[4];
					echo '<br/>';
					$absence=new TRH_Absence;
					$absence->load_by_idImport($ATMdb,$TUser[strtolower($infos[4])]);
					
					$absence->fk_user=$TUser[strtolower($infos[4])];
					$absence->idAbsImport=$infos[0];
					$absence->etat='Validee';
					$absence->commentaireValideur=$infos[2];
					
					switch($infos[1]){
						case 40:
							$absence->type='conges';
							$absence->libelle=saveLibelle($absence->type);
							$absence->code=saveCodeTypeAbsence($ATMdb, $absence->type);
							break;
						case 73:
							$absence->type='rttcumule';
							$absence->libelle=saveLibelle($absence->type);
							$absence->code=saveCodeTypeAbsence($ATMdb, $absence->type);
							break;
						case 74:
							$absence->type='rttnoncumule';
							$absence->libelle=saveLibelle($absence->type);
							$absence->code=saveCodeTypeAbsence($ATMdb, $absence->type);
							break;
					}
					
					$absence->libelle=saveLibelle($absence->type);
					$absence->libelleEtat=saveLibelleEtat($absence->type);
					
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
					
			

		//parent::add_champs('date_debut,date_fin','type=date;');	//dates debut fin de congés
		//parent::add_champs('ddMoment, dfMoment','type=chaine;');		//moment (matin ou après midi)

					
					$absence->save($ATMdb);
					$cpt++;
			}
			
			}	
			$numLigne++;	
			
		}
}

echo 'Fin du traitement. '.($cpt).' lignes rajoutées à la table.<br><br>';	

$ATMdb->close();

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------





