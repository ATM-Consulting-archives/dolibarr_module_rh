<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * et une évenement de type facture
 */
 
//require('../config.php');
//require('../class/evenement.class.php');
//require('../class/ressource.class.php');

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
$idArea = getIdSociete($ATMdb, 'area');
if (!$idArea){echo 'Pas de fournisseur (tiers) du nom de Area !';exit();}
if (empty($nomFichier)){$nomFichier = "./fichierImports/fichier facture area.CSV";}
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

$TRessource = chargeVoiture($ATMdb);
$TEmprunts = chargeEmprunts($ATMdb);
$TAssocies = chargeAssocies($ATMdb);
$TCarteInexistantes = array();
$TCarteNonAttribue = array();
$cpt =0;
//print_r($TRessource);

$idImport = _url_format(basename($nomFichier), false, true);
$ATMdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement WHERE idImport='$idImport'");

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		$infos = explode(';', $data[0]);
		
		$temp = new TRH_Evenement;
		$temp->load_liste($ATMdb);
		if (strpos((string) $infos[10], 'Trajet') !== FALSE ){
			if (! array_key_exists ( $infos[6] , $TRessource )){
				//echo 'Pas de carte  correspondante : '.$infos[6].'<br>';
				$TCarteInexistantes[$infos[6]] = 1;
			}
			else {
				//print_r($infos);
				$temp->fk_rh_ressource = $TRessource[$infos[6]];
				$temp->type = 'trajet';
				
				if ($infos[11]!= '')
					{$temp->set_date('date_debut', $infos[11]);}
				else {$temp->set_date('date_debut', $infos[16]);}
				$temp->set_date('date_fin', $infos[16]);
				$temp->coutEntrepriseHT = strtr($infos[22], ',','.');
				$temp->coutTTC = strtr($infos[24], ',','.');
				$temp->coutEntrepriseTTC = strtr($infos[24], ',','.');
				
				if (!empty($TEmprunts)){
					if ($TAssocies[$temp->fk_rh_ressource] != 0 ){
						$temp->fk_user = getUser($TEmprunts, $TAssocies[$temp->fk_rh_ressource], $temp->date_fin);
					}
					else {
						$temp->fk_user = getUser($TEmprunts, $temp->fk_rh_ressource, $temp->date_debut);
					}
				}
				if ($temp->fk_user==0){
					$TCarteNonAttribue[$infos[6]] = 1;
					//echo 'La carte '.$infos[6].' n\'est pas attribuée sur la période utilisé !<br>';
				}
				$ttva = array_keys($temp->TTVA , floatval(strtr($infos[21], ',','.')));
				$temp->TVA = $ttva[0];
				$temp->numFacture = $infos[4];
				$temp->compteFacture = $infos[13];
				$temp->motif = htmlentities('Trajet de '.strtolower($infos[14]).' à '.strtolower($infos[19]), ENT_COMPAT , 'UTF-8');
				$temp->fk_fournisseur = $idArea;
				$temp->entity = $entity;
				
				if ($infos[15]=='WE'){
					$temp->commentaire = 'Utilisation de la carte durant un WE !';
					//TODO un envoi de mail ici ?
				}
				else {
					$temp->commentaire = '';	
				}
				
				$temp->idImport = $idImport;
				
				$temp->save($ATMdb);
				$cpt ++;
			}
		}
		$numLigne++;
	}
}

foreach ($TCarteInexistantes as $key => $value) {
	$message .= 'Pas de carte  correspondante : '.$key.'<br>';
}
foreach ($TCarteNonAttribue as $key => $value) {
	$message .= 'La carte '.$key.' n\'est pas attribuée sur la période utilisé !<br>';	
}


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
$message .= $cpt." événements trajets rajoutés.<br>";
$message .= 'Fin du traitement. Durée : '.$page_load_time . " sec.<br><br>";
send_mail_resources('Import - Factures AREA',$message);

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
	