<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;

		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	}

$nomFichier = "Accidents.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

//début du parsing

//GROUPE;Etat;Traite le ;Date;Immat ;km;Nom du garage;Montant H T ;CAUSE;N°Fact;Agence;Nom du conducteur;Marque;LOUEUR;type

$ressource = new TRH_Ressource;
//$ressource->load($ATMdb, $id)
//$ressource->isEmpruntee($ATMdb, date("Y-m-d",time()) );

$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			
			$temp = new TRH_Evenement;
			print_r($infos);
			//infos générales
			$temp->set_date('date_debut', $infos[6]);
			$temp->set_date('date_fin', $infos[6]);
			$temp->type = 'appel';
			
			//clés externes
			$temp->fk_rh_ressource = $TNumero[$infos[1]];
			$temp->fk_user = $TUser[strtolower($infos[2])];
			
			//infos faciles à charger
			$temp->appelHeure= $infos[7];
			$temp->appelNumero = $infos[1];
			$temp->appelDureeReel = $infos[9];
			$temp->appelDureeFacturee = $infos[10];
			$temp->motif = $infos[11];
			
			//le cout pour l'entreprise est celui donnée dans l'import
			$temp->coutEntrepriseHT = (float)$infos[12];
		
			//TODO : le coût va dépendre des règles sur le type et sur l'utilisateur
			$temp->coutHT = (float)$infos[12];
			
			//$temp->save($ATMdb);echo ' : Ajoutee.';		
		}
		
		echo '<br>';
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.($numLigne-3).' lignes rajoutés à la table.';
	
}
	