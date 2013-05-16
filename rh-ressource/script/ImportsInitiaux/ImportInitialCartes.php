<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');

global $conf;
$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des cartes.<br><br>';
$idVoiture=  getIdType($ATMdb, 'voiture');
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname,login FROM ".MAIN_DB_PREFIX."user ";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[$ATMdb->Get_field('login') /*strtoupper($ATMdb->Get_field('firstname')).' '.strtoupper($ATMdb->Get_field('name'))*/] = $ATMdb->Get_field('rowid');
	}


//----------------------Import des cartes total---------------

$idCarteTotal = getIdType($ATMdb,'cartetotal');
$cptCarteTotal = 0;
$nomFichier = "fichier facture total.csv";
$nomFichier = "Facture TOTAL.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=2){
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
$nomFichier = "Facture AREA.CSV";
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
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
echo $cptCarteArea.' cartes Area importés.<br>';
 
	

//----------------------Import des téléphones---------------


$idTel = getIdType($ATMdb,'telephone');
$idSim = getIdType($ATMdb,'carteSim');
$TUserInexistants = array();
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtoupper($ATMdb->Get_field('name').' '.$ATMdb->Get_field('firstname'))] = $ATMdb->Get_field('rowid');
	}
$TAgence = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TAgence[strtolower($ATMdb->Get_field('nom'))] = $ATMdb->Get_field('rowid');
	}
$nomFichier = "reglesAppels.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$cptTel = 0;

//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			//echo '<br>';
			$nom = $infos[26];
			$prenom = $infos[27];
			$numIdSim = '0'.$infos[1];
			$TNumero = getRessource($ATMdb, $idSim);
			$numId = $infos[6];
			
			if (empty($nom)){
				null;
			}
			else if (!array_key_exists(strtoupper($nom.' '.$prenom), $TUser)){
				$TUserInexistants[$nom.' '.$prenom] = 1;
				null; //si l'user n'existe pas
			}
			else if (array_key_exists($numIdSim, $TNumero)){
				//echo $numIdSim." déjà créé.<br>";
				null;//si le numéro existe
			}
			else {
				$tab = explode('(', $infos[23]);
				$modle = strtolower($tab[0]);
				if ( (strpos($modle,'samsung')!== FALSE) || (strpos($modle,'galaxy')!== FALSE) || (strpos($modle,'s2')!== FALSE) ){
					$marque = 'Samsung';
				}
				else if (strpos($modle,'htc')  !== FALSE){
					$marque = 'HTC';
				}
				else if (strpos($modle,'iphone')  !== FALSE){
					$marque = 'Apple';
				}
				else {
					$marque = '';
				}
				$modle = ucwords($modle);
				//echo 'Marque : '.$marque.'    modèle : '.$modle.'<br>';
			
			
				$tel = new TRH_Ressource;
				$tel->fk_rh_ressource_type = $idTel;
				$tel->load_ressource_type($ATMdb);
				$tel->numId = 'Téléphone n°'.$cptTel;
				$tel->libelle = empty($marque) ? 'Téléphone' : $marque.' '.$modle;
				$tel->fk_proprietaire = $TAgence[strtolower($infos[3])];
				$tel->set_date('date_vente', '');
				$tel->set_date('date_garantie', '');
				$tel->marquetel = $marque;	
				$tel->modletel	= $modle;
				$tel->save($ATMdb);
				
				$sim = new TRH_Ressource;
				$sim->fk_rh_ressource_type = $idSim;
				$sim->fk_rh_ressource = $tel->getId(); //association de la carte Sim au Téléphone.
				$sim->load_ressource_type($ATMdb);
				$sim->numId = $numIdSim;
				$sim->libelle = 'Carte Sim '.$numIdSim;
				$tel->fk_proprietaire = $TAgence[strtolower($infos[3])];
				$sim->set_date('date_vente', '');
				$sim->set_date('date_garantie', '');
				$sim->numerotel = $numIdSim;
				$sim->coutminuteinterne = 0.09;
				$sim->coutminuteexterne = 0.09;
				$sim->save($ATMdb);
				
				$emprunt = new TRH_Evenement;
				$emprunt->type = 'emprunt';
				$emprunt->fk_user = $TUser[strtoupper($nom.' '.$prenom)];
				$emprunt->fk_rh_ressource = $tel->getId();
				$emprunt->set_date('date_debut', '01/01/2013');
				$emprunt->set_date('date_fin', '31/12/2013');
				$emprunt->save($ATMdb);
				
				$cptTel ++;
			}		
		}
		$numLigne++;
	}
}
echo 'Liste des utilisateurs non trouvés dans la base : <br>'; 
foreach ($TUserInexistants as $nom => $value) {
	echo $nom.', ';
}
echo '<br>';
fclose($handle);
echo $cptTel.' Telephone créés importés.<br>';
 

 


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
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

	
