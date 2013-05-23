<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/contrat.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;

$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);
		
//on charge quelques listes pour avoir les clés externes.
$TTrigramme = array();
$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TTrigramme[strtolower($ATMdb->Get_field('login'))] = $ATMdb->Get_field('rowid');
	}

$TFournisseur = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TFournisseur[strtolower($ATMdb->Get_field('nom'))] = $ATMdb->Get_field('rowid');
	}



$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[strtolower($ATMdb->Get_field('nom'))] = $ATMdb->Get_field('rowid');
	}

$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('taux')] = $ATMdb->Get_field('rowid');
	}


$idVoiture = getIdType('voiture');


echo 'Import initial des voitures.<br><br>';

//----------------------------------------------------------------------------------------------------------------
//PREMIER FICHIER
//----------------------------------------------------------------------------------------------------------------

$nomFichier = 'Etat du Parc PARCOURS.csv';
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$TContrat = array();
$TRessource = array();
$cptContrat = 0;
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			
			//$TRessource = chargeVoiture($ATMdb);
			//$TContrat = chargeContrat($ATMdb, $idVoiture);
			//print_r($TContrat);
			
			$plaque = strtoupper(str_replace('-','',$infos[18])); //immatriculation : on enlève les espaces et on met les lettres en majuscules
			//on regarde si la plaque d'immatriculation est dans la base
			if (empty($plaque)){
				echo 'plaque vide.<br>';
				null;
			}
			else if (!empty($TRessource[$plaque])){
				echo $plaque.' existe déjà<br>';
				//$voiture->load($ATMdb, $TRessource[$plaque]);
			}
			else {
				//clés externes
				$TRessource[$plaque] = new TRH_Ressource;
				$TRessource[$plaque]->fk_rh_ressource_type = $idVoiture;
				$TRessource[$plaque]->load_ressource_type($ATMdb);
				$TRessource[$plaque]->numId = $plaque;
				$TRessource[$plaque]->set_date('date_achat', $infos[5]);
				$TRessource[$plaque]->set_date('date_vente', $infos[14]);
				$TRessource[$plaque]->set_date('date_garantie', '');
				if (empty($TGroups[strtolower($infos[1])])){echo 'Pas de groupe du nom '.$infos[1].'<br>';}
				else {$TRessource[$plaque]->fk_utilisatrice = $TGroups[strtolower($infos[1])];}
				$TRessource[$plaque]->fk_proprietaire = $conf->entity;
				$TRessource[$plaque]->immatriculation = (string)$plaque; //plaque;
				$TRessource[$plaque]->cle = true;
				$TRessource[$plaque]->kit = true; 
				$cpt ++;
				$TRessource[$plaque]->save($ATMdb);
				//echo $plaque.' ajoutee.<BR>';
				
				//si il trouve la personne, il sauvegarde une attribution
				if (!empty($TTrigramme[strtolower($infos[15])])){
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TTrigramme[strtolower($infos[15])]; 
					$emprunt->fk_rh_ressource = $TRessource[$plaque]->getId();
					$emprunt->set_date('date_debut', $infos[5]);
					$emprunt->set_date('date_fin', $infos[14]);
					$emprunt->save($ATMdb);
				}
				else {
					echo 'Trigramme inexistant : '.$infos[15].' : '.$infos[16].'<br>';
				}
			}
			
			$numContrat = $infos[0];
			if (empty($TContrat[$numContrat])){
				$TContrat[$numContrat] = new TRH_Contrat;
				$TContrat[$numContrat]->numContrat = $infos[0];
				$TContrat[$numContrat]->libelle = 'Contrat n°'.$infos[0];
				$TContrat[$numContrat]->set_date('date_debut', $infos[5]);
				$TContrat[$numContrat]->set_date('date_fin', $infos[14]);
				$TContrat[$numContrat]->dureeMois = $infos[2];
				$TContrat[$numContrat]->kilometre = $infos[3]*1000;
				$TContrat[$numContrat]->TVA = $TTVA['19.6'];
				$TContrat[$numContrat]->fk_rh_ressource_type = $idVoiture;
				if (empty($TFournisseur['parcours'])){echo 'Pas de fournisseur du nom de \'parcours\' dans la BD<br>';}
				else {$TContrat[$numContrat]->fk_tier_fournisseur = $TFournisseur['parcours'];}
				$cptContrat++;
				$TContrat[$numContrat]->save($ATMdb);
				
				//association contrat-ressource
				$assoc = new TRH_Contrat_Ressource;
				$assoc->fk_rh_ressource = $TRessource[$plaque]->getId();
				$assoc->fk_rh_contrat = $TContrat[$numContrat]->getId();
				$assoc->commentaire = 'Créé à l\'import initial';
				$assoc->save($ATMdb);
			}
			
			
		}
		
		
		$numLigne++;
		//print_r(explode('\n', $data));
	}
	
}	
echo $cpt.' voiture creees.<br>';
echo $cptContrat.' contrats creees.<br>';
//exit();
//----------------------------------------------------------------------------------------------------------------
//AUTRES FICHIERS POUR COMPLETER LES INFOS
//----------------------------------------------------------------------------------------------------------------

$TFichier = array(
	"CPRO GROUPE - PRELVT DU 05.04.13.csv",
	"CPRO INFORMATIQUE PREL 05 04 13.csv",
	"CPRO - PRELVT DU 05 04 13.csv" 
);

$cpt = 0;
$cptContrat = 0;
foreach ($TFichier as $nomFichier) {
echo '<br><br>Traitement du fichier '.$nomFichier.' : <br>';


//début du parsing
$numLigne = 0;
if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=1){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$voiture = new TRH_Ressource;
			//$TRessource = chargeVoiture($ATMdb);
			//$TContrat = chargeContrat($ATMdb, $idVoiture);
			$plaque = strtoupper(str_replace('-','',$infos[8])); //immatriculation : on enlève les espaces et on met les lettres en majuscules
			//si la voiture existe déjà, on continue à renseigner les champs 
			if (!empty($TRessource[$plaque])){
				$TRessource[$plaque]->libelle = ucwords(strtolower($infos[40].' '.$infos[41]));
				$TRessource[$plaque]->marquevoit = (string)$infos[40];
				$TRessource[$plaque]->modlevoit = (string)$infos[41];
				$TRessource[$plaque]->bailvoit = 'Location';
				$TRessource[$plaque]->typevehicule = $infos[9];
				$cpt ++;
				$TRessource[$plaque]->save($ATMdb);
				//echo $plaque.' : completee.<br>';
			}
			
			
			$numContrat = $infos[4];
			//complétage du contrat
			if (!empty($TContrat[$numContrat])){
				//echo 'contrat : '.$numContrat.'<br><br>';
				$cptContrat++;
				$TContrat[$numContrat]->loyer_TTC = strtr($infos[38], ',','.');
				$TContrat[$numContrat]->assurance = strtr($infos[34], ',','.');
				$TContrat[$numContrat]->entretien = strtr($infos[32], ',','.');
			}	
		}
		$numLigne++;
	}
	echo $cpt.' voitures completees.<br>';
	echo $cptContrat.' contrats completees.<br>';
	$cpt = 0;
	$cptContrat = 0;
	}

else {echo 'erreur sur le fichier '.$nomFichier.'<br>';}

}

//sauvegarde finale
foreach ($TRessource as $value) {
	$value->save($ATMdb);
}
foreach ($TContrat as $key => $value) {
	$value->save($ATMdb);
}
$ATMdb->close();

//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo 'Fin du traitement. Duree : '.$page_load_time . " sec";

function chargeVoiture(&$ATMdb){
	global $conf;
	$TRessource = array();
	$sql="SELECT r.rowid as 'ID', t.rowid as 'IdType', r.numId FROM ".MAIN_DB_PREFIX."rh_ressource as r 
	LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t on (r.fk_rh_ressource_type = t.rowid)
	WHERE r.entity=".$conf->entity."
	 AND t.code='voiture' ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$idVoiture = $ATMdb->Get_field('IdType');
		$TRessource[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('ID');
		}
	return $TRessource;
}

/**
 * Renvoie les contrats liés à une voiture
 */
function chargeContrat(&$ATMdb, $idVoiture){
	global $conf;
	$sql="SELECT rowid, numContrat FROM ".MAIN_DB_PREFIX."rh_contrat
	WHERE entity=".$conf->entity."
	AND fk_rh_ressource_type=".$idVoiture;
	$TListe = array();
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TListe[$ATMdb->Get_field('numContrat')] = 1;//new TRH_Contrat;
		//$TListe[$ATMdb->Get_field('numContrat')]->load($ATMdb, $ATMdb->Get_field('rowid'));
		
	}
	return $TListe;
}
	