<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;

		
//on charge quelques listes pour avoir les clés externes.
$TNumero = array();
$sql="SELECT rowid, numero FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TNumero[$ATMdb->Get_field('numero')] = $ATMdb->Get_field('rowid');
	}

$idCarteSim = getIdTypeCarteSim($ATMdb);
$nomFichier = "communications_par_ligne.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=5){
			$infos = explode(';', $data[0]);
			
			$temp = new TRH_Ressource;
			
			//infos générales d'une ressource
			$temp->set_date('date_achat', date("Y-m-d h:m:s"));
			$temp->set_date('date_vente', date("Y-m-d h:m:s"));
			$temp->set_date('date_garantie', date("Y-m-d h:m:s"));
			$temp->libelle = 'carte Sim n°'.$infos[1];
			$temp->numId = $infos[1];
			
			//clés externes
			$temp->fk_rh_ressource_type = (int)$idCarteSim;
			
			$temp->load_ressource_type($ATMdb);
			
			//infos sur la carte SIM
			$temp->coutminuteinterne = '0.09';
			$temp->coutminuteexterne = '0.09';
			$temp->numerotel = $infos[1];
			$temp->commfixemetrop = $infos[4];
			$temp->commmobileorange = $infos[5];
			$temp->commmobilesfr = $infos[6];
			$temp->commmobilebouygues = $infos[7];
			$temp->commtointernational = $infos[8];
			
			$temp->commfrominternational = $infos[9];
			$temp->comminterne = $infos[10];
			$temp->commvpn = $infos[11];
			$temp->conngprs = $infos[12];
			$temp->conngprsfrominternational = $infos[13];
			
			$temp->conn3g = $infos[14];
			$temp->conn3gfrominternational = $infos[15];
			$temp->sms = $infos[16];
			$temp->smssansfrontiere = $infos[17];
			$temp->servicesms = $infos[18];
			
			$temp->mms = $infos[19];
			$temp->mmssansfrontiere = $infos[20];
			$temp->connexionswifi = $infos[21];
			$temp->connexionswifisurtaxes = $infos[22];
			$temp->wififrominternational = $infos[23];
			
			$temp->autrescommunications = $infos[24];
			$temp->commoptima = $infos[25];
			$temp->depassementfacturation = $infos[26];
			$temp->deductionforfait = $infos[27];
			$temp->totalcomm = $infos[28];
			
			$temp->commsemaine = $infos[29];
			$temp->commweekend = $infos[30];
			$temp->libflotte = $infos[31];
			

			echo ' : Ajoutee.';
			$temp->save($ATMdb);		
		}
		
		echo '<br>';
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.($numLigne-5).' lignes rajoutés à la table.';
	
}


function getIdTypeCarteSim(&$ATMdb){
	global $conf;
	
	$sql="SELECT rowid as 'IdType' FROM ".MAIN_DB_PREFIX."rh_ressource_type 
	WHERE entity=".$conf->entity."
	 AND code='carteSim' ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$idCarteSim = $ATMdb->Get_field('IdType');
		}
	return $idCarteSim;
}
	
	
	