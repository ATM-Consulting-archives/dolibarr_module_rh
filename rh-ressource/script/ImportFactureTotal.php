<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * 
 */
 
 
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
//*/
global $conf;

$ATMdb=new TPDOdb;

$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
}

//	Chargement des types d'événements
$TEvents = array();
$sql="SELECT rowid, code, libelle, codecomptable FROM ".MAIN_DB_PREFIX."rh_type_evenement ";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TEvents[strtolower($ATMdb->Get_field('libelle'))] = $ATMdb->Get_field('code');
}

$idVoiture = getIdType('voiture');
$idCarteTotal = getIdType('cartetotal');


$TRessource = getIDRessource($ATMdb, $idVoiture);

//charge une liste rowid de la voiture =>plaque de la voiture
$TPlaque = array();
$sql="SELECT rowid, numId FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE fk_rh_ressource_type=".$idVoiture;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	$TPlaque[$row->rowid] = $row->numId;
}

//donne la carte (rowid) utilisée par une voiture (plaque)  : plaque de la voiture => rowid de la carte total
$TCarte = array();
$sql="SELECT rowid, numId, fk_rh_ressource FROM ".MAIN_DB_PREFIX."rh_ressource 
		WHERE fk_rh_ressource_type = ".$idCarteTotal;
$ATMdb->Execute($sql);
while($row = $ATMdb->Get_line()) {
	if ($row->fk_rh_ressource!=0){
		$TCarte[$TPlaque[$row->fk_rh_ressource]] = $row->rowid;
	}
}
print_r($TCarte);
echo count($TCarte);
echo '<br><br>';

//donne l'user qui utilise la carte
$TAttribution = array();
foreach ($TCarte as $numId => $rowid) {
	$idUser = ressourceIsEmpruntee($ATMdb, $rowid, date("Y-m-d", time()) );
	if ($idUser!=0){$TAttribution[$numId] = $idUser;} 
}

print_r($TAttribution);
echo count($TAttribution);


if (empty($nomFichier)){$nomFichier = "./fichierImports/Facture TOTAL.csv";}
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

$TCarteInexistantes = array();
//print_r($TRessource);
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1 ){
			$infos = explode(';', $data[0]);
			
			//print_r($infos);
			
			 
			$plaque = $infos[11];
			$plaque = str_replace('-VU', '', $plaque);
			$plaque = str_replace('-', '', $plaque);
			$plaque = str_replace(' ', '', $plaque);
			
			if (empty ($TCarte[$plaque])){
				//echo $plaque.' : carte pas lié à une voiture.<br>';
				null;
			}
			else if (empty ($TAttribution[$plaque])){
				//echo $plaque.' : carte liée à une voiture, mais non attribué.<br>';
				null;
			}
			else {
				//print_r($infos);echo '<br>';
				$temp = new TRH_Evenement;
				$temp->load_liste($ATMdb);
				$temp->load_liste_type($ATMdb, $idVoiture);
				$temp->fk_rh_ressource = $TCarte[$plaque];
				$temp->fk_rh_ressource_type = $idCarteTotal;
				$t = explode(' ',$infos[30]);
				array_shift($t); 
				$nomPeage = htmlentities(implode(' ', $t), ENT_COMPAT , 'ISO8859-1'); 
				
				if ( !empty($TEvents[strtolower($infos[17])]) ){
					$temp->type = $TEvents[strtolower($infos[17])];
					
				}
				else {
					$temp->type = 'divers';
				}
				
				$temp->motif = htmlentities($infos[17], ENT_COMPAT , 'ISO8859-1');
				$temp->commentaire = htmlentities($infos[30], ENT_COMPAT , 'ISO8859-1');
				
				$temp->fk_user = $TAttribution[$plaque];
				
				$temp->set_date('date_debut', $infos[15]);
				$temp->set_date('date_fin', $infos[15]);
				$temp->coutTTC = strtr($infos[19], ',','.');
				$temp->coutEntrepriseTTC = strtr($infos[19], ',','.');
				$ttva = array_keys($temp->TTVA,floatval(strtr($infos[25], ',','.')));
				$temp->TVA = $ttva[0];
				$temp->coutEntrepriseHT = strtr($infos[20], ',','.');
				$temp->numFacture = $infos[1];
				$temp->compteFacture = $infos[13];
				$temp->litreEssence = floatval(strtr($infos[18],',','.'));
				$temp->kilometrage = intval($infos[31]);
				
				echo 'lol';
				$temp->save($ATMdb);
				echo 'lol2<br>';
				$cpt++;
			}
			
		}
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}	
}

foreach ($TCarteInexistantes as $key => $value) {
	$message .= 'Erreur : Pas de carte correspondante : '.$key.'<br>';
}
$message .= 'Fin du traitement. '.$cpt.' événements rajoutés créés.<br><br>';
send_mail_resources('Import - Factures TOTAL',$message);
echo $message;
	
function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE (t.code='voiture') ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		//$idVoiture = $ATMdb->Get_field('IdType');
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}


	