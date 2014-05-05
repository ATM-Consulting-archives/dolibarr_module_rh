<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;
$ATMdb=new TPDOdb;
// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des badges AREA.<br><br>';
$idVoiture=  getIdType('voiture');
//on charge quelques listes pour avoir les clés externes.
$TTrigramme = array();
$sql="SELECT rowid, lastname, firstname,login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0, ".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	 /*strtoupper($ATMdb->Get_field('firstname')).' '.strtoupper($ATMdb->Get_field('lastname'))*/
	$TTrigramme[strtolower($ATMdb->Get_field('login'))] = $ATMdb->Get_field('rowid');
	}

$TVoiture = array();
$sql="SELECT rowid, numId FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idVoiture." AND entity IN (0, ".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TVoiture[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}

$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0, ".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('nom')] = $ATMdb->Get_field('rowid');
}


//-----------------------------------------------------------
//----------------------Import des cartes area---------------
//-----------------------------------------------------------

$idCarteArea = getIdType('badgearea');




$TRessource = getIDRessource($ATMdb, $idCarteArea);

	$nomFichier = "fichier facture area.CSV";
	echo 'Traitement du fichier '.$nomFichier.' : <br>';
	$cptCarteArea = 0;
	$cptNonLie = 0;
	//début du parsing
	$numLigne = 0;
	if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
		while(($data = fgetcsv($handle, 0,'\r')) != false){
			if ($numLigne >=1){
				$infos = explode(';', $data[0]);
				//print_r($infos);
				
				
				$numId = $infos[6];
				if ($numId[0]!= "P"){ //les badges commences par P
					null;
				}
				else if (!empty($TRessource[$numId])){
					null;
				}
				else {
					$temp = new TRH_Ressource;
					
					$temp->fk_rh_ressource_type = $idCarteArea;
					$temp->numId = $numId;
					$temp->libelle = 'Carte Area '.$numId;
					$temp->set_date('date_achat', '01/01/2013');
					$temp->set_date('date_vente', '31/12/2013');
					$temp->set_date('date_garantie', '');
					//$temp->fk_utilisatrice;
					$temp->fk_proprietaire = $conf->entity;
					$temp->entity = $conf->entity;
					//$temp->fk_rh_ressource
					
					$temp->load_ressource_type($ATMdb);
					$temp->numcarte = $numId;	
					$temp->immcarte	= $numId;
					$temp->comptesupport = '';
					
					$cptCarteArea ++;
					$temp->save($ATMdb);
					$TRessource[$numId] = $temp->getId();
					
					$trigramme = explode('-', $infos[7]);
					$trigramme = strtolower($trigramme[0]);
					
					if (!empty($TTrigramme[$trigramme])){
						$emprunt = new TRH_Evenement;
						$emprunt->type = 'emprunt';
						$emprunt->fk_user = $TTrigramme[$trigramme]; 
						$emprunt->fk_rh_ressource = $temp->getId();
						$emprunt->set_date('date_debut', '01/01/2013');
						$emprunt->set_date('date_fin', '31/12/2013');
						$emprunt->save($ATMdb);
					}
					else {
						echo 'Trigramme inexistant : '.$trigramme.'<br>';
						$cptNonLie++;
					}
					
				}		
			}
			$numLigne++;
		}
	}
	
	fclose($handle);
	echo $cptCarteArea.' cartes Area importés.<br>';
	echo $cptNonLie.' cartes non liées.<br><br><br>';



//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$ATMdb->close();

	
