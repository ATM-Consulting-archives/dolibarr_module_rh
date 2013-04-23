<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');

global $conf;
$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des cartes.<br><br>';
$idVoiture=  getIdType($ATMdb, 'voiture');
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	}


//----------------------Import des cartes total---------------
$idCarteTotal = getIdType($ATMdb,'cartetotal');
$cptCarteTotal = 0;
$nomFichier = "fichier facture total.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("./fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$TRessource = getRessource($ATMdb, $idCarteTotal);

			$numId = strtoupper($infos[9]);
			if (array_key_exists($numId, $TRessource)){
				//echo $numId.' existe déjà<br>';
				null;
			}
			else if (empty($numId)){
				null;
			}
			else {
				$temp = new TRH_Ressource;
				//clés externes
				
				$temp->fk_rh_ressource_type = (int)$idCarteTotal;
				$temp->load_ressource_type($ATMdb);
				$temp->numId = $numId;
				$temp->set_date('date_vente', '');
				$temp->set_date('date_garantie', '');
				
				$temp->libelle = 'Carte Total '.$numId;
				$temp->numcarte = $numId;	
				$temp->immcarte	= $numId;
				$temp->codecarte = '';
				$temp->libcarte = "Carte Total ".$numId;
				$temp->comptesupport = '';
				
				$cptCarteTotal ++;
				$temp->save($ATMdb);echo $numId.' : Ajoutee.';		
			}		
		}
		$numLigne++;
	}
}
fclose($handle);
echo $cptCarteTotal.' cartes Total importés.<br>';

//----------------------Import des cartes area---------------
$idCarteArea = getIdType($ATMdb,'cartearea');
$cptCarteArea = 0;
$nomFichier = "fichier facture area.CSV";
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("./fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$TRessource = getRessource($ATMdb, $idCarteArea);
			$numId = $infos[6];
			if (array_key_exists($numId, $TRessource)){
				null;
			}
			else if (empty($numId)){
				null;
			}
			else if (strpos($numId,'TOTAL') !== FALSE){
				null;
			}
			else if (strpos($numId,'*') !== FALSE){
				null;
			}
			else {
				$temp = new TRH_Ressource;
				
				$temp->fk_rh_ressource_type = $idCarteArea;
				$temp->load_ressource_type($ATMdb);
				$temp->numId = $numId;
				$temp->libelle = 'Carte Area '.$numId;
				$temp->set_date('date_vente', '');
				$temp->set_date('date_garantie', '');
				
				$temp->numcarte = $numId;	
				$temp->immcarte	= $numId;
				$temp->comptesupport = '';
				
				$cptCarteArea ++;
				$temp->save($ATMdb);
				//echo $numId.' : Ajoutee.';		
			}		
		}
		$numLigne++;
	}
}

fclose($handle);
	


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $cptCarteArea.' cartes Area importés.<br>';
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";


function getIdType(&$ATMdb, $type){
	global $conf;
	$sql="SELECT rowid as 'IdType' FROM ".MAIN_DB_PREFIX."rh_ressource_type 
	WHERE entity=".$conf->entity."
	 AND code='".$type."' ";
	 //echo $sql.'<br><br>';
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$id = $ATMdb->Get_field('IdType');}
	return $id;
}

function getRessource(&$ATMdb, $idType){
	global $conf;
	$TRessource = array();
	
	$sql="SELECT rowid, numId  FROM ".MAIN_DB_PREFIX."rh_ressource
	WHERE entity=".$conf->entity."
	 AND fk_rh_ressource_type=".$idType;
	// echo $sql.'<br>';
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}
	return $TRessource;
}

	