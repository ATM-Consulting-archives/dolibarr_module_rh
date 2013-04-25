<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);
		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	}

$idVoiture = getIdTypeVoiture($ATMdb);
$TFichier = array("CPRO GROUPE - PRELVT DU 05.04.13.csv",
"CPRO INFORMATIQUE PREL 05 04 13.csv",
"CPRO - PRELVT DU 05 04 13.csv" 
);

echo 'Import initial des voitures.<br><br>';
$cpt = 0;
foreach ($TFichier as $nomFichier) {
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$temp = new TRH_Ressource;
			$TRessource = chargeVoiture($ATMdb);
			//immatriculation : on enlève les espaces et on met les lettres en majuscules
			$plaque = strtoupper(str_replace('-','',$infos[8]));
			//on regarde si la plaque d'immatriculation est dans la base
			if (array_key_exists($plaque, $TRessource)){
				//echo $plaque.' existe déjà<br>';
				$temp->load($ATMdb, $TRessource[$plaque]);
			}
			else if (empty($plaque)){
				null;
			}
			else {
				//clés externes
				$temp->fk_rh_ressource_type = (int)$idVoiture;
				$temp->load_ressource_type($ATMdb);
				$temp->numId = $plaque;
				$temp->set_date('date_vente', '');
				$temp->set_date('date_garantie', '');
				$temp->immatriculation = (string)$plaque;//plaque;
				$temp->libelle = ucwords(strtolower($infos[40].' '.$infos[41]));
				$temp->marquevoit = (string)$infos[40];
				$temp->modlevoit = (string)$infos[41];
				$temp->bailvoit = 'Location';
				$temp->typevehicule = $infos[9];
				$cpt ++;
				$temp->save($ATMdb);echo $plaque.' : Ajoutee.';
				
				//si il trouve la personne, il sauvegarde une attribution
				$t = explode(' ',$infos[7]);
				if (array_key_exists(strtolower($t[0]), $TUser)){
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TUser[strtolower($t[0])];
					$emprunt->fk_rh_ressource = $temp->getId();
					$emprunt->set_date('date_debut', '01/01/2013');
					$emprunt->set_date('date_fin', '31/12/2013');
					$emprunt->save($ATMdb);
				}
				
			}		
		}
		
		
		$numLigne++;
		//print_r(explode('\n', $data));
	}
	
	
}


	
}

//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $cpt.' voitures importés.<br>';
echo 'Fin du traitement. Durée : '.$page_load_time . " sec";

function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND t.code='voiture' ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$idVoiture = $ATMdb->Get_field('IdType');
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}

function getIdTypeVoiture(&$ATMdb){
	global $conf;
	
	$sql="SELECT rowid as 'IdType' FROM ".MAIN_DB_PREFIX."rh_ressource_type 
	WHERE entity=".$conf->entity."
	 AND code='voiture' ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$idVoiture = $ATMdb->Get_field('IdType');
		}
	return $idVoiture;
}
	