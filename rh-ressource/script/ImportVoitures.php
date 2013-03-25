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
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	}


$idVoiture = getIdTypeVoiture($ATMdb);
$nomFichier = "ListeVoitures.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';



//Société utilisatrice;Payeur;Nom de l'utilisateur;Marque;Modèle;Immatriculation;Location Crédit-Bail Immo;TVS;Type;frais restitution
//CPRO INFORMATIQUE;;Restitution à ALD le 18/02/09;Renault;Mégane;8910 XF 26;21-avr.-06;;;

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=2){
			$infos = explode(';', $data[0]);
			print_r($infos);
			
			$temp = new TRH_Ressource;
			
			$TRessource = chargeVoiture($ATMdb);
			print_r($TRessource);
			//immatriculation : on enlève les espaces et on met les lettres en majuscules
			$plaque = strtoupper(str_replace(' ','',$infos[7]));
			//on regarde si la plaque d'immatriculation est dans la base
			if (array_key_exists($plaque, $TRessource)){
				echo 'existe déjà<br>';
				$temp->load($ATMdb, $TRessource[$plaque]);
			}
			else {
				//clés externes
				$temp->fk_rh_ressource_type = (int)$idVoiture;
				
				$temp->load_ressource_type($ATMdb);
				
				$temp->numId = $plaque;
				$temp->plaque = (string)$plaque;//plaque;
				$temp->libelle = $infos[5].' '.$infos[6];
				$temp->marque = (string)$infos[5];
				$temp->modle = (string)$infos[6];
				$temp->bail = (string)$infos[8];
				
				
				$temp->save($ATMdb);echo ' : Ajoutee.';
			}		
		}
		
		echo '<br>';
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.($numLigne-3).' lignes rajoutés à la table.';
	
}

function chargeVoiture(&$ATMdb){
	global $conf;
	echo '<br>coucou<br>';
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND t.code='voiture' ";
	 echo $sql;
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
	