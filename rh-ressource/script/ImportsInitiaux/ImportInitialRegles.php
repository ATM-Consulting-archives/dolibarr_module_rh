<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');
require('../../class/regle.class.php');

global $conf;

$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);


//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user ";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('name').' '.$ATMdb->Get_field('firstname'))] = $ATMdb->Get_field('rowid');
	}

$TRessource = chargeSim($ATMdb);
$t = getIdType($ATMdb);
$idCarteSim = $t[1];
$idTel = $t[0];
$nomFichier = "reglesAppels.csv";
echo 'Import initial des règles d\'appels et des affectations des téléphones.<br><br>';
echo 'Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			
			$numero = $infos[1];
			$nom = $infos[26];
			$prenom = $infos[27];

			if (!empty($nom)){
				if (isset($TUser[strtolower($nom.' '.$prenom)])){
					$temp = new TRH_Ressource_Regle;
					$temp->choixApplication = 'user';
					
					$temp->duree = clockToInt($infos[5]);
					$temp->dureeInt = clockToInt($infos[6]);
					$temp->dureeExt = clockToInt($infos[7]);
					$temp->natureDeduire = $infos[12];
					$temp->montantDeduire = strtolower($infos[13]);
					$temp->dataIllimite = strtolower($infos[14]);
					$temp->dataIphone = strtolower($infos[15]);
					$temp->mailforfait = strtolower($infos[16]);
					$temp->smsIllimite = strtolower($infos[17]);
					$temp->data15Mo = strtolower($infos[18]);
					$temp->carteJumelle = strtolower($infos[19]);
					
					$temp->numeroExclus = '';
							
					$temp->fk_rh_ressource_type = $idTel ;
					$temp->fk_user = intval($TUser[strtolower($nom)]);
					$temp->choixLimite = ($temp->duree==0) ? 'extint' :  'gen' ;		
					
					$temp->save($ATMdb);
					$cpt++;
				}
				else {echo 'Utilisateur inconnu : '.$nom.' '.$prenom.'<br>';}
			}
		}
		
		
		$numLigne++;
		//print_r(explode('\n', $data));
	}
	
	
}


	


//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo 'Fin du traitement. Durée : '.$page_load_time . " sec.<br>".$cpt." règles importés.<br><br>";

function clockToInt($chaine){
	// $chaine est sous format 02:30 hh:mm	
	$t = explode(':',$chaine);
	$h = $t[0];
	$m = $t[1];
	return $h*60+$m;
}

function chargeSim(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE t.code='cartesim' ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}

	