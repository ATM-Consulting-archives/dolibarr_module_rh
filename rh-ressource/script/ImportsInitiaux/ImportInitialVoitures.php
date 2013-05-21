<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;

$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);
		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	}

$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[strtolower($ATMdb->Get_field('nom'))] = $ATMdb->Get_field('rowid');
	}

$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');
	}


$idVoiture = getIdType('voiture');


echo 'Import initial des voitures.<br><br>';

//----------------------------------------------------------------------------------------------------------------
//PREMIER FICHIER
//----------------------------------------------------------------------------------------------------------------

$nomFichier = 'Etat de parc.csv';
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$TContrat = array();
$TRessource = array();

//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$TRessource[$plaque] = new TRH_Ressource;
			//$TRessource = chargeVoiture($ATMdb);
			$TContrat = chargeContrat($ATMdb, $idVoiture);
			print_r($TContrat);
			
			$plaque = strtoupper(str_replace('-','',$infos[17])); //immatriculation : on enlève les espaces et on met les lettres en majuscules
			//on regarde si la plaque d'immatriculation est dans la base
			if (empty($plaque)){
				echo 'plaque vide.<br>';
				null;
			}
			else if (!empty($TRessource[$plaque])){
				echo $plaque.' existe déjà<br>';
				//$voiture->load($ATMdb, $TRessource[$plaque]);
			}
			else {
				//clés externes
				$TRessource[$plaque]->fk_rh_ressource_type = (int)$idVoiture;
				$TRessource[$plaque]->load_ressource_type($ATMdb);
				$TRessource[$plaque]->numId = $plaque;
				$TRessource[$plaque]->set_date('date_achat', $infos[5]);
				$TRessource[$plaque]->set_date('date_vente', $infos[14]);
				$TRessource[$plaque]->set_date('date_garantie', '');
				$TRessource[$plaque]->fk_proprietaire = $TGroups[strtolower($infos[1])];
				$TRessource[$plaque]->immatriculation = (string)$plaque; //plaque;
				$TRessource[$plaque]->cle = true;
				$TRessource[$plaque]->kit = true; 
				$cpt ++;
				//$voiture->save($ATMdb);echo $plaque.' : Ajoutee.';
				
				//si il trouve la personne, il sauvegarde une attribution
				$t = explode(' ',$infos[7]);
				if (!empty($TUser[strtolower($t[0])])){
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TUser[strtolower($t[0])]; //TODO
					$emprunt->fk_rh_ressource = $voiture->getId();
					$emprunt->set_date('date_debut', $infos[5]);
					$emprunt->set_date('date_fin', $infos[14]);
					//$emprunt->save($ATMdb);
				}
			}
			
			if (!empty($TContrat[$infos[0]])){
				$contrat = new TRH_Contrat;
				$contrat->numContrat = $infos[0];
				$contrat->libelle = 'Contrat n°'.$infos[0];
				$contrat->set_date('date_debut', $infos[5]);
				$contrat->set_date('date_fin', $infos[14]);
				$contrat->dureeMois = $infos[2];
				$contrat->kilometre = $infos[3]*1000;
				$contrat->TVA = $TTVA['19.6'];
				$contrat->fk_rh_ressource_type = $idVoiture;
				$contrat->fk_tier_fournisseur = $TGroups[strtolower($infos[1])];
				//print_r($contrat);
				//loyer_TTC, assurance, entretien, fk_tier_utilisateur,entity','type=entier;index,
				//	fk_tier_fournisseur,entity,fk_rh_ressource_type','type=entier;index;				
			}
			
			
		}
		
		
		$numLigne++;
		//print_r(explode('\n', $data));
	}
	
}	

exit();
//----------------------------------------------------------------------------------------------------------------
//AUTRES FICHIERS
//----------------------------------------------------------------------------------------------------------------

$TFichier = array(
	"CPRO GROUPE - PRELVT DU 05.04.13.csv",
	"CPRO INFORMATIQUE PREL 05 04 13.csv",
	"CPRO - PRELVT DU 05 04 13.csv" 
);

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
			
			$voiture = new TRH_Ressource;
			$TRessource = chargeVoiture($ATMdb);
			$TContrat = chargeContrat($ATMdb, $idVoiture);
			//immatriculation : on enlève les espaces et on met les lettres en majuscules
			$plaque = strtoupper(str_replace('-','',$infos[8]));
			//on regarde si la plaque d'immatriculation est dans la base
			if (empty($plaque)){
				null;
			}
			else if (!empty($TRessource[$plaque])){
				//si la voiture existe déjà, on continue à renseigner les champs 
				//$voiture->load($ATMdb, $TRessource[$plaque]);
			
				//clés externes
				$voiture->fk_rh_ressource_type = (int)$idVoiture;
				$voiture->load_ressource_type($ATMdb);
				$voiture->numId = $plaque;
				$voiture->set_date('date_vente', '');
				$voiture->set_date('date_garantie', '');
				//$temp->fk_proprietaire = $TGroups['C\'PRO Groupe'];
				//$temp->immatriculation = (string)$plaque;//plaque;
				$TRessource[$plaque]->libelle = ucwords(strtolower($infos[40].' '.$infos[41]));
				$TRessource[$plaque]->marquevoit = (string)$infos[40];
				$TRessource[$plaque]->modlevoit = (string)$infos[41];
				$TRessource[$plaque]->bailvoit = 'Location';
				$TRessource[$plaque]->typevehicule = $infos[9];
				$cpt ++;
				$TRessource[$plaque]->save($ATMdb);echo $plaque.' : Ajoutee.';
				
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
	}
	
	
}
}

foreach ($TRessource as $key => $value) {
	$value->save($ATMdb);
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

/**
 * Renvoie les contrats liés à une voiture
 */
function chargeContrat(&$ATMdb, $idVoiture){
	global $conf;
	$sql="SELECT rowid, numContrat FROM ".MAIN_DB_PREFIX."rh_contrat
	WHERE entity=".$conf->entity."
	AND fk_rh_ressource_type=".$idVoiture;
	$TListe = array();
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TListe[$ATMdb->Get_field('numContrat')] = 1;//new TRH_Contrat;
		//$TListe[$ATMdb->Get_field('numContrat')]->load($ATMdb, $ATMdb->Get_field('rowid'));
		
	}
	return $TListe;
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
	