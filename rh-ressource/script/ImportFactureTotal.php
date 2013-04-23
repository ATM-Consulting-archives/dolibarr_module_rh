<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */
 
//require('./config.php');
//require('./class/evenement.class.php');
//require('./class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;

$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}

		
$idVoiture = getIdTypeVoiture($ATMdb);
if (empty($nomFichier)){$nomFichier = "./fichierImports/fichier facture total.csv";}
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
			
			//print_r($infos);
			
			
			$temp = new TRH_Evenement;
			$temp->load_liste($ATMdb);
			$temp->load_liste_type($ATMdb, $temp);
			if (! array_key_exists ( $infos[9] , $TRessource )){
				echo 'Pas de carte correspondante : '.$infos[9].'<br>';
			}
			else {
				$temp->fk_rh_ressource = $TRessource[$infos[9]];
				if (strpos((string) $infos[17], 'age TVA') !== FALSE ){
					$temp->type = 'page';
				}
				else {
					$temp->type = 'pleindessence';
				}
				
				$temp->fk_user = $TUser[strtolower($infos[12])];
				
				$temp->set_date('date_debut', $infos[15]);
				$temp->set_date('date_fin', $infos[15]);
				$temp->coutTTC = strtr($infos[19], ',','.');
				$temp->coutEntrepriseTTC = strtr($infos[19], ',','.');
				$ttva = array_keys($temp->TTVA,floatval(strtr($infos[25], ',','.')));
				$temp->TVA = $ttva[0];
				$temp->coutEntrepriseHT = strtr($infos[20], ',','.');
				$temp->numFacture = $infos[1];
				$temp->compteFacture = $infos[13];
				
				$temp->motif = htmlentities($infos[17], ENT_COMPAT , 'ISO8859-1');
				if ($infos[31]!=''){
					$temp->commentaire = $infos[30].(isset($infos[31]) ? ' kilometrage : '.$infos[31] : '');	
				}
				else {
					$temp->commentaire = $infos[30];	
				}
				
				$temp->save($ATMdb);
			}
			
		}
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.($numLigne-3).' lignes rajoutés à la table.<br><br>';
	
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
	