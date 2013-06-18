<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */
 
/*require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');*/

global $conf;

$ATMdb=new TPDOdb;

// relever le point de départ
$timestart=microtime(true);

$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}
$idVoiture = getIdType('voiture');
$TRessource = chargeVoiture($ATMdb);
$TNonAttribuee = array();
$TNoPlaque = array();
if (empty($nomFichier)){$nomFichier = "./fichierImports/B60465281_Masterplan-CPRO_M_20130430.csv";}
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1 ){
		$infos = explode(';', $data[0]);
		
		$plaque = str_replace('-','',$infos[0]);
		$plaque = str_replace(' ','',$plaque);
		
		$timestamp = mktime(0,0,0,intval(substr($infos[4], 3,2)),intval(substr($infos[4], 0,2)), intval(substr($infos[4], 6,4)));
		$date = date("Y-m-d", $timestamp);
		
		if (empty($TRessource[$plaque])){
			$TNoPlaque[$plaque] = 1 ;
		}
		else {
			$temp = new TRH_Evenement;
			$temp->fk_rh_ressource = $TRessource[$plaque];
			$temp->type = 'facture';
			
			$temp->set_date('date_debut', $date);
			$temp->set_date('date_fin', $date);
			$temp->coutEntrepriseHT = strtr($infos[10], ',','.');
			$temp->coutTTC = strtr($infos[25], ',','.');
			$temp->coutEntrepriseTTC = strtr($infos[25], ',','.');
			$temp->numFacture = $infos[22];
			$temp->motif = $infos[8];
			$temp->commentaire = $infos[7];
			$temp->entity =$entity;
			//$ttva = array_keys($temp->TTVA , floatval(strtr($infos[21], ',','.')));
			//$temp->TVA = $ttva[0];
			//$temp->compteFacture = $infos[13];
			
			
			$idUser = ressourceIsEmpruntee($ATMdb, $TRessource[$plaque], $date);
			//echo $idUser.'<br>';
			if ($idUser == 0) {
				$TNonAttribuee[$date] = $plaque;
			}
			else {
				$temp->fk_user = $idUser;
			}
			$temp->save($ATMdb);
		}
		}
		$numLigne++;
		
	}
	
	
	//Fin du code PHP : Afficher le temps d'éxecution et les résultats.
	if (!empty($TNoPlaque)){
		$message .= 'Voitures non trouvées :<br>';}
	foreach($TNoPlaque as $plaque=>$rien){
		$message .= $plaque.'<br>';}
	
	if (!empty($TNonAttribuee)){
		$message .= 'Voitures non attribué :<br>';}
	foreach($TNonAttribuee as $date=>$plaque){
		$message.= $plaque.' non attribuée le '.$date.'<br>';}
	$message .= '<br>';
	
	$timeend=microtime(true);
	$page_load_time = number_format($timeend-$timestart, 3);
	$message .= 'Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
	send_mail_resources('Import - Factures Euromaster',$message);
	echo $message;
	
	
	
}

function chargeAssocies(&$ATMdb){
	global $conf;
	$sqlReq="SELECT rowid, fk_rh_ressource 
	FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE entity=".$conf->entity;
	$TAssoc = array();
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TAssoc[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('fk_rh_ressource');
	}
	return $TAssoc;
	
	
}

function getUser(&$listeEmprunts , $id, $jour){
	if (empty($listeEmprunts[$id])){return 0;}
	foreach ($listeEmprunts[$id] as $k => $value) {
		if ( ($value['debut'] <= date("Y-m-d",$jour))  
			&& ($value['fin'] >= date("Y-m-d",$jour)) ){
				return $value['fk_user'];
		}
	}
	return 0;
}

function chargeEmprunts(&$ATMdb){
	global $conf;
	$sqlReq="SELECT DISTINCT e.date_debut, e.date_fin , e.fk_user, e.fk_rh_ressource, firstname, name 
	FROM ".MAIN_DB_PREFIX."rh_evenement as e  
	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user=u.rowid) 
	WHERE e.type='emprunt'
	AND e.entity=".$conf->entity."
	ORDER BY date_debut";
	$TUsers = array();
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TUsers[$ATMdb->Get_field('fk_rh_ressource')][] = array(
			'debut'=>$ATMdb->Get_field('date_debut')
			,'fin'=>$ATMdb->Get_field('date_fin')
			,'fk_user'=>$ATMdb->Get_field('fk_user')
			,'user'=>$ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name')
		);
	}
	return $TUsers;
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


