<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;
$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des cartes.<br><br>';
$idVoiture=  getIdType('voiture');
//on charge quelques listes pour avoir les clés externes.
$TTrigramme = array();
$sql="SELECT rowid, name, firstname,login FROM ".MAIN_DB_PREFIX."user ";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	 /*strtoupper($ATMdb->Get_field('firstname')).' '.strtoupper($ATMdb->Get_field('name'))*/
	$TTrigramme[strtolower($ATMdb->Get_field('login'))] = $ATMdb->Get_field('rowid');
	}

$TVoiture = array();
$sql="SELECT rowid, numId FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idVoiture;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TVoiture[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}

$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup ";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('nom')] = $ATMdb->Get_field('rowid');
}

//----------------------Import des cartes total---------------

$idCarteTotal = getIdType('cartetotal');
$cptCarteTotal = 0;
$nomFichier = "exportEtatDeParcTotal.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=2){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$TRessource = getIDRessource($ATMdb, $idCarteTotal);
			$plaque = strtoupper(str_replace('-','',$infos[7]));
			$plaque = strtoupper(str_replace(' ','',$plaque));
			$numId = strtoupper($infos[6]);
			if ($numId[0]==7){$numId = substr($numId, 7);} //on enlève la partie "7010010" si elle existe au début du numId
			
			if (!empty($TRessource[$numId])){
				//echo $numId.' existe déjà<br>';
				null;
			}
			else if (empty($numId)){
				null;
			}
			else {
				$carteTotal = new TRH_Ressource;
				//clés externes
				
				$carteTotal->fk_rh_ressource_type = $idCarteTotal;
				if (empty($TVoiture[$plaque])){echo 'Plaque non trouvé : '.$plaque.'<br>';}
				else {$carteTotal->fk_rh_ressource = $TVoiture[$plaque];}
				$carteTotal->numId = $numId;
				$carteTotal->libelle = 'Carte Total '.$numId;
				$carteTotal->set_date('date_achat', $infos[14]);
				$carteTotal->set_date('date_vente', $infos[16]);
				$carteTotal->set_date('date_garantie', '');
				$carteTotal->fk_proprietaire = $conf->entity;
				$carteTotal->fk_utilisatrice = $TGroups[$infos[12]];
				
				//champs propres aux cartes total
				$carteTotal->load_ressource_type($ATMdb);
				
				$carteTotal->totalnumcarte = $numId;
				$carteTotal->totalcomptesupport = $infos[1];
				$carteTotal->totaltypesupport = $infos[3];
				$carteTotal->totalinfostation = $infos[11];
				$carteTotal->totallibeestampe = $infos[12];
				$carteTotal->totaladresseestampe = $infos[13];
				$carteTotal->totaltypecodeconfidentiel = $infos[20];
				$carteTotal->totalcarburant = $infos[21];
				$carteTotal->totalplafondcarburant = $infos[22];
				$carteTotal->totaltypeplafond = $infos[23];
				$carteTotal->totalproduit = $infos[24];
				$carteTotal->totalperiodiciteplafond = $infos[25];
				$carteTotal->totalqtplafond = $infos[26];
				$carteTotal->totaluniteplafond = $infos[27];
				$carteTotal->totaloptionservice = $infos[40];
				$carteTotal->totalplafondservice = $infos[41];
				$carteTotal->totalserviceplafondservice = $infos[43];
				$carteTotal->totalperiodiciteplafondservice = $infos[44];
				$carteTotal->totalqtplafondservice = $infos[45];
				$carteTotal->totaluniteplafondservice = $infos[46];
				
				$cptCarteTotal ++;
				$carteTotal->save($ATMdb);		
				
				//si il trouve la personne, il sauvegarde une attribution
				/*$t1 = explode('-', $infos[8]);
				$t2 = explode(' ', $t1[0]);
				$trigramme = strtolower($t2[0]);
				if (!empty($TTrigramme[$trigramme])){
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TTrigramme[strtolower($trigramme)]; 
					$emprunt->fk_rh_ressource = $carteTotal->getId();
					$emprunt->set_date('date_debut', $infos[14]);
					$emprunt->set_date('date_fin', $infos[16]);
					//echo '_____________________Trigramme TROUVE : '.$trigramme.'<br>';
					//$emprunt->save($ATMdb);
				}else {
					echo 'Trigramme inexistant : '.$trigramme.'<br>';}*/
				
				
			}		
		}
		$numLigne++;
	}
}

echo $cptCarteTotal.' cartes Total importés.<br><br><br>';

//exit();


//----------------------Import des cartes area---------------


$idCarteArea = getIdType('cartearea');
$cptCarteArea = 0;


$TFichier = array(
	"fichier facture area.CSV"
	,"Facture AREA.CSV"
);


foreach ($TFichier as $nomFichier) {
echo '<br><br>Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$TRessource = getIDRessource($ATMdb, $idCarteArea);
			$numId = $infos[6];
			if (!empty($TRessource[$numId])){
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

}
echo $cptCarteArea.' cartes Area importés.<br>';
 
	

//----------------------Import des téléphones---------------


$idTel = getIdType('telephone');
$idSim = getIdType('carteSim');
$TUserInexistants = array();
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtoupper($ATMdb->Get_field('name').' '.$ATMdb->Get_field('firstname'))] = $ATMdb->Get_field('rowid');
	}
$TAgence = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
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
			$TNumero = getIDRessource($ATMdb, $idSim);
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
$ATMdb->close();

function getIDRessource(&$ATMdb, $idType){
	global $conf;
	$TRessource = array();
	
	$sql="SELECT rowid, numId  FROM ".MAIN_DB_PREFIX."rh_ressource
	
	 AND fk_rh_ressource_type=".$idType;
	// echo $sql.'<br>';
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}
	return $TRessource;
}

	
