<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
global $conf;

$ATMdb=new TPDOdb;

		
//on charge quelques listes pour avoir les clés externes.
$TNumero = array();
$sql="SELECT rowid, numero FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TNumero[$ATMdb->Get_field('numero')] = $ATMdb->Get_field('rowid');
	}

$TUser = array();
$sql="SELECT rowid, lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('lastname'))] = $ATMdb->Get_field('rowid');
}


$idCarteSim = getIdType('cartesim');
$nomFichier = "communications_par_ligne.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
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

			$temp->save($ATMdb);
			$cpt++;
				
		}
		
		
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.$cpt.' lignes rajoutés à la table.';
	
}

