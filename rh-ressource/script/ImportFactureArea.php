<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */
 
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;

// relever le point de départ
$timestart=microtime(true);

$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}

		
$idVoiture = getIdTypeVoiture($ATMdb);
$nomFichier = "./fichierImports/fichier facture area.CSV";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

$TRessource = chargeVoiture($ATMdb);

//print_r($TRessource);

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1 ){
			$infos = explode(';', $data[0]);
			
			$temp = new TRH_Evenement;
			$temp->load_liste($ATMdb);
			$temp->load_liste_type($ATMdb, $temp);
			if (strpos((string) $infos[10], 'Trajet') === FALSE ){
				echo 'Ligne sans informations à traiter.<br>';
			}
			else {
				if (! array_key_exists ( $infos[6] , $TRessource )){
					echo 'Pas de carte correspondante : '.$infos[6].'<br>';
				}
				else {
					//print_r($infos);
					$temp->fk_rh_ressource = $TRessource[$infos[6]];
					$temp->type = 'trajet';

					//$temp->fk_user = $TUser[strtolower($infos[12])];
					$temp->set_date('date_debut', $infos[11]);
					$temp->set_date('date_fin', $infos[16]);
					$temp->coutEntrepriseHT = strtr($infos[22], ',','.');
					$temp->coutTTC = strtr($infos[24], ',','.');
					$temp->coutEntrepriseTTC = strtr($infos[24], ',','.');
					
					$ttva = array_keys($temp->TTVA , floatval(strtr($infos[21], ',','.')));
					$temp->TVA = $ttva[0];
					
					$temp->numFacture = $infos[4];
					$temp->compteFacture = $infos[13];
					
					$temp->motif = htmlentities('Trajet de '.strtolower($infos[14]).' à '.strtolower($infos[19]), ENT_COMPAT , 'UTF-8');
					
					if ($infos[15]=='WE'){
						$temp->commentaire = 'Utilisation de la carte durant un WE !';	
					}
					else {
						$temp->commentaire = '';	
					}
					
					
					$temp->save($ATMdb);
					//echo ' : Ajoutee: sur la carte '.$infos[9];
				}
			}
		}
		
		//echo ;
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}

	//Fin du code PHP : Afficher le temps d'éxecution
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	echo 'Fin du traitement. Durée : '.$page_load_time . " sec";
	
}


function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND (t.code='voiture' OR t.code='cartearea') ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
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
	