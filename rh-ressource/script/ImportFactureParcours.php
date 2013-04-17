<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */
 
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../class/contrat.class.php');


global $conf;

$ATMdb=new Tdb;

$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}

$TContrat = array();
$sql="SELECT rowid,  numContrat FROM ".MAIN_DB_PREFIX."rh_contrat WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TContrat[strtolower($ATMdb->Get_field('numContrat'))] = $ATMdb->Get_field('rowid');
}


		
$idVoiture = getIdTypeVoiture($ATMdb);
$idParcours = getIdParcours($ATMdb);
if ($idParcours == 0){echo 'Aucun fournisseur du nom de "Parcours" ';exit;}

if (empty($nomFichier)){$nomFichier = "./fichierImports/CPRO GROUPE - PRELVT DU 05.04.13.csv";}
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

$TRessource = chargeVoiture($ATMdb);

//print_r($TRessource);
$TFacture = array();		

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		$infos = explode(';', $data[0]);
		if ($numLigne >=1 ){
			
			
			//print_r($infos);
			
			
			
			$plaque = str_replace('-','',$infos[8]);
			if (! array_key_exists ( $plaque , $TRessource )){
				if ($plaque != '')
					{echo 'Pas de voiture correspondante : '.$plaque.'<br>';}
			}
			else {
				echo 'Traitement de la Voiture '.$plaque.'<br>';
				$numFacture = $infos[2];
				$numContrat = $infos[4];
				print_r($infos);
				//FACTURE
				if (! array_key_exists ( $numFacture , $TFacture )){
					$TFacture[$numFacture] = new TRH_Evenement;
					$TFacture[$numFacture]->type = 'facture';
					$TFacture[$numFacture]->numFacture = $numFacture;
					$TFacture[$numFacture]->coutTTC= 0;
					$TFacture[$numFacture]->coutEntrepriseTTC = 0;
					$TFacture[$numFacture]->coutEntrepriseHT = 0;
					$TFacture[$numFacture]->fk_rh_ressource = $TRessource[$plaque];
					$TFacture[$numFacture]->motif = 'Facture Parcours';
					$TFacture[$numFacture]->commentaire  = 'concernant '.$infos[0];
					$TFacture[$numFacture]->set_date('date_debut', $infos[10]);
					$TFacture[$numFacture]->set_date('date_fin', $infos[1]);
				}
				
				$TFacture[$numFacture]->coutTTC += intval(strtr($infos[38], ',','.'));
				$TFacture[$numFacture]->coutEntrepriseTTC += floatval(strtr($infos[38], ',','.'));
				//$TFacture[$numFacture]->TVA=19.6;
				echo floatval(strtr($infos[12], ',','.'));
				$TFacture[$numFacture]->coutEntrepriseHT += floatval(strtr($infos[12], ',','.'));
				$t = explode(' ',$infos[7]);
				$TFacture[$numFacture]->fk_user = (array_key_exists(strtolower($t[0]) , $TUser)) ? $TUser[strtolower($t[0])] : 0 ;
				
				//CONTRAT
				if (! array_key_exists ( strtolower($numContrat) , $TContrat )){
					$contrat = new TRH_Contrat;
					$contrat->libelle = 'Contrat n°'.$numContrat;
					$contrat->numContrat = $numContrat;
					$TExtrasFieldValues = array();
					$TExtrasFieldUnites = array();
					for ($i = 12; $i <= 38; $i++) {
		    			$TExtrasFieldValues[] = $infos[$i];
						$TExtrasFieldUnites[] = '€';
					}
					$contrat->extraFieldNom = implode(';', $TExtrasFieldKeys);
					$contrat->extraFieldValeur = implode(';', $TExtrasFieldValues);
					$contrat->extraFieldUnite = implode(';', $TExtrasFieldUnites);
					$contrat->set_date('date_debut', $infos[10]);
					$contrat->set_date('date_fin', $infos[11]);
					$contrat->bail = 'location';
					//$contrat->TVA
					//$contrat->loyer_TTC
					$contrat->fk_tier_fournisseur = $idParcours;
					$contrat->fk_rh_ressource_type = $idVoiture;
					$contrat->save($ATMdb);
					echo 'Id du contrat : '.$contrat->getId().'<br>';
					$assocContrat =  new TRH_Contrat_Ressource;
					echo 'Id ressource : '.$TRessource[$plaque].'<br>';
					$assocContrat->fk_rh_ressource = $TRessource[$plaque];
					$assocContrat->fk_rh_contrat = $contrat->getId();
					$assocContrat->save($ATMdb);
				}
				
				//VEHICULE
				
				
				
				
				
				
				//$temp->save($ATMdb);
			}
			
		}
		else {//ligne d'entete : on charge les noms des champs
			$TExtrasFieldKeys = array(); // 12 à 38
			for ($i = 12; $i <= 38; $i++) {
    			$TExtrasFieldKeys[] = $infos[$i];
			}
		}
		
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}

	// on save les factures
	foreach ($TFacture as $i=>$temp) {
		echo '<br>'.' : ';	
		print_r($temp);
		$temp->save($ATMdb);
		echo '<br>';
	}
	echo 'Fin du traitement. '.($numLigne-3).' lignes rajoutés à la table.';
	
}

function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND (t.code='voiture' OR t.code='carte') ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		//$idVoiture = $ATMdb->Get_field('IdType');
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

function getIdParcours(&$ATMdb){
	global $conf;
	$idParcours = 0;
	$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe 
	WHERE entity=".$conf->entity;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		if (strtolower($ATMdb->Get_field('nom')) == 'parcours'){ 
			$idParcours = $ATMdb->Get_field('rowid');}
		}
	
	return $idParcours;
}
	