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
$TRessource = chargeVoiture($ATMdb);
$ressource = new TRH_Ressource;
//$ressource->load($ATMdb, $id)
//$ressource->isEmpruntee($ATMdb, date("Y-m-d",time()) );
print_r($TRessource);

$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			
			$temp = new TRH_Evenement;
			//print_r($infos);
			//infos générales
			
			$plaque = strtoupper(str_replace(' ','',$infos[4]));
			//on regarde si la plaque d'immatriculation est dans la base
			echo $plaque;
			if (array_key_exists($plaque, $TRessource)){
				echo ' : existe déjà : ok <br>';
				$temp->type = 'facture';
				$temp->set_date('date_debut', $infos[3]); 	//date de l'événement
				$temp->set_date('date_fin', $infos[2]); 	//date du traitement
				
				$d = explode('/',$infos[2]);
				$dateEven =  date("Y-m-d", mktime (0,0, 0, $d[1], $d[0], $d[2]));
											
				//clés externes
				$temp->fk_rh_ressource = $TRessource[$plaque];
				
				//on cherche qui utilisait la ressource au moment de l'accident.
				$ressource->load($ATMdb, $temp->fk_rh_ressource);
				$idUser = $ressource->isEmpruntee($ATMdb, $dateEven);
				if ($idUser > 0){$temp->fk_user = $idUser;}
				
				$temp->coutHT = $infos[7];
				$temp->motif = $infos[6]; 			//le nom du garage
				$temp->commentaire = $infos[8]; 	//le commentaire
				
				//print_r($temp);
				$temp->save($ATMdb);echo ' : Ajoutee.';		
				
			}
			else {
				echo ' : la ressource n\'existe pas. <br>';
			}
			
		}
		
		echo '<br>';
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}
	
	echo 'Fin du traitement. '.($numLigne-3).' lignes rajoutés à la table.';
	
}



function chargeVoiture(&$ATMdb){
	global $conf;
	echo '<br>coucou<br>';
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND t.code='voiture' ";
	 echo $sql;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$idVoiture = $ATMdb->Get_field('IdType');
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}

