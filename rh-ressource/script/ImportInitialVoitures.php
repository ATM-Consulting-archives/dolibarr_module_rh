<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;

		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	}


$idVoiture = getIdTypeVoiture($ATMdb);
echo exec('pwd').'<br>';
$TFichier = array("CPRO GROUPE - PRELVT DU 05.04.13.csv",
"CPRO INFORMATIQUE PREL 05 04 13.csv",
"CPRO - PRELVT DU 05 04 13.csv" 
);

foreach ($TFichier as $nomFichier) {
	

echo 'Traitement du fichier '.$nomFichier.' : <br><br>';



//Société utilisatrice;Payeur;Nom de l'utilisateur;Marque;Modèle;Immatriculation;Location Crédit-Bail Immo;TVS;Type;frais restitution
//CPRO INFORMATIQUE;;Restitution à ALD le 18/02/09;Renault;Mégane;8910 XF 26;21-avr.-06;;;

//début du parsing
$numLigne = 0;
if (($handle = fopen("./fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1 && $numLigne<=3){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$temp = new TRH_Ressource;
			$TRessource = chargeVoiture($ATMdb);
			//immatriculation : on enlève les espaces et on met les lettres en majuscules
			$plaque = strtoupper(str_replace('-','',$infos[8]));
			//on regarde si la plaque d'immatriculation est dans la base
			if (array_key_exists($plaque, $TRessource)){
				echo $plaque.' existe déjà<br>';
				$temp->load($ATMdb, $TRessource[$plaque]);
			}
			else {
				//clés externes
				$temp->fk_rh_ressource_type = (int)$idVoiture;
				$temp->load_ressource_type($ATMdb);
				$temp->numId = $plaque;
				$temp->immatriculation = (string)$plaque;//plaque;
				$temp->libelle = $infos[40].' '.$infos[41];
				$temp->marquevoit = (string)$infos[40];
				$temp->modlevoit = (string)$infos[41];
				$temp->bailvoit = 'location';
				$temp->typevehicule = strtolower($infos[9]);
				
				$temp->save($ATMdb);echo $plaque.' : Ajoutee.';
				
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
		
		echo '<br>';
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.($numLigne-3).' lignes rajoutés à la table.';
	
}
}

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
	