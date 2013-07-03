<?php

/**
 * Importation de la facture Total
 * On créé deux évenements par ligne de ce fichier : un loyer et un gestion+entretien
 * 
 */

/*
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
require('../class/contrat.class.php');
//*/

global $conf;
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;

$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

$idVoiture = getIdType('voiture');
$idParcours = getIdSociete($ATMdb, 'parcours');
if (!$idParcours){echo 'Pas de fournisseur (tiers) du nom de Parcours !';exit();}

if ($idParcours == 0){echo 'Aucun fournisseur du nom de "Parcours" ! ';exit;}

$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}

/*$TContrat = array();
$sql="SELECT rowid,  numContrat FROM ".MAIN_DB_PREFIX."rh_contrat WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TContrat[strtolower($ATMdb->Get_field('numContrat'))] = $ATMdb->Get_field('rowid');
}*/


//chargement d'une liste :  plaque => typeVehicule (vu ou vp) 
$TVuVp = array();
$sql="SELECT rowid,  numId, typeVehicule FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE entity=".$conf->entity." 
		AND fk_rh_ressource_type=".$idVoiture;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TVuVp[strtolower($row->numId)] = $row->typeVehicule;
}

//chargement des TVA.
$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('taux')] = $ATMdb->Get_field('rowid');
	}


if (empty($nomFichier)){$nomFichier = "./fichierImports/CPRO - PRELVT DU 05 04 13.csv";}
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

//pour avoir un joli nom, on prend la chaine après le dernier caractère /  et on remplace les espaces par des underscores
$v =  strrpos ( $nomFichier , '/' );
$idImport = substr($nomFichier, $v+1);
$idImport = str_replace(' ', '_', $idImport);
$idRessFactice = createRessourceFactice($ATMdb, $idVoiture, $idImport, $entity, $idParcours);
$idSuperAdmin = getIdSuperAdmin($ATMdb);

$cptFactureLoyer = 0;
$cptFactureGestEntre = 0;
$cptNoAttribution = 0;
$TRessource = chargeVoiture($ATMdb);

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		$infos = explode(';', $data[0]);
		if ($numLigne >=1 ){
			//print_r($infos);
			
			$plaque = str_replace('-','',$infos[8]);
			$plaque = str_replace(' ','',$plaque);
			
			$timestamp = mktime(0,0,0,substr($infos[3], 3,2),substr($infos[3], 0,2), substr($infos[3], 6,4));
			$date = date("Y-m-d", $timestamp);
			
			if (empty($plaque)){
				null;
			}
			
			if (!empty($TRessource[$plaque])){
				$idUser = ressourceIsEmpruntee($ATMdb, $TRessource[$plaque], $date);
				if ($idUser==0){ //si il trouve, on l'affecte à l'utilisateur 
					$idUser = $idSuperAdmin;
					$cptNoAttribution++;
					echo 'Voiture non attribué le '.$date.' : '.$plaque.'<br>';}
				}
			else {
				$idUser = $idSuperAdmin;
				echo 'Pas de voiture correspondante : '.$plaque.'<br>';
				$cptNoVoiture ++;
			}
				//echo $idUser.'<br>';
				
			$numFacture = $infos[2];
			//FACTURE SUR LE LOYER
			$fact = new TRH_Evenement;
			$fact->type = 'factureloyer';
			$fact->numFacture = $numFacture;
			$fact->fk_rh_ressource = $TRessource[$plaque];
			$fact->fk_user = $idUser;
			$fact->fk_rh_ressource_type = $idVoiture;
			$fact->motif = 'Facture mensuelle Parcours : Loyer';
			$fact->commentaire = 'Facture lié au contrat '.$infos[4];
			$fact->set_date('date_debut', $infos[10]);
			$fact->set_date('date_fin', $infos[1]);
			$fact->coutTTC = floatval(strtr($infos[38], ',','.'));
			$fact->coutEntrepriseTTC = floatval(strtr($infos[38], ',','.'));
			$fact->TVA= $TTVA['19.6'];
			$fact->coutEntrepriseHT = floatval(strtr($infos[12], ',','.'));
			$fact->entity =$entity;
			$fact->fk_fournisseur = $idParcours;
			$fact->idImport = $idImport;
			$fact->numFacture = $infos[2];
			$fact->date_facture = dateToInt($infos[3]);
			$fact->save($ATMdb);
			$cptFactureLoyer++;
				
			//FACTURE SUR L'ENTRETIEN ET LA GESTION
			$fact = new TRH_Evenement;
			$fact->type = 'facturegestionetentretien';
			$fact->numFacture = $numFacture;
			$fact->fk_rh_ressource = $TRessource[$plaque];
			$fact->fk_user = $idUser;
			$fact->fk_rh_ressource_type = $idVoiture;
			$fact->motif = 'Facture mensuelle Parcours : Gestion et Entretien';
			$fact->commentaire = 'Facture lié au contrat '.$infos[4].',<br>
									Entretien TTC :'.floatval(strtr($infos[32], ',','.')).'€,<br>
									Gestion TTC :'.floatval(strtr($infos[31], ',','.')).'€';
			$fact->set_date('date_debut', $infos[10]);
			$fact->set_date('date_fin', $infos[1]);
			$fact->coutTTC = floatval(strtr($infos[31], ',','.')+strtr($infos[32], ',','.'));
			$fact->coutEntrepriseTTC = floatval(strtr($infos[31], ',','.')+strtr($infos[32], ',','.'));
			$fact->TVA= $TTVA['19.6'];
			$fact->coutEntrepriseHT = floatval(strtr($infos[13], ',','.')+strtr($infos[14], ',','.'));
			$fact->fk_fournisseur = $idParcours;
			$fact->idImport = $idImport;
			$fact->numFacture = $infos[2];
			$fact->date_facture = dateToInt($infos[3]);
			$fact->entity =$entity;
			$fact->save($ATMdb);
			$cptFactureGestEntre++;
						
		}
	$numLigne++;
}

	//Fin du code PHP : Afficher le temps d'éxecution et le bilan.
	//$message .= $cptContrat.' contrats importés.<br>';
	$message .= $cptNoVoiture.' plaques sans correspondance.<br>';
	$message .= $cptNoAttribution.' voitures non attribués<br>';
	$message .= $cptFactureLoyer.' factures loyer importés.<br>';
	$message .= $cptFactureGestEntre.' factures gestion+entretien importés.<br>';
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message .= '<br>Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	echo $message;
	send_mail_resources('Import - Factures Parcours',$message);
}

function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE (t.code='voiture' OR t.code='carte') ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		//$idVoiture = $ATMdb->Get_field('IdType');
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}


/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}



	