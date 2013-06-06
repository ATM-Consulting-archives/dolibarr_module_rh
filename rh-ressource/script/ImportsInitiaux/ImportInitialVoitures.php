<?php
require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/contrat.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;

$ATMdb=new TPDOdb;
// relever le point de départ
$timestart=microtime(true);
		
//on charge quelques listes pour avoir les clés externes.
$TTrigramme = array();
$sql="SELECT rowid, login FROM ".MAIN_DB_PREFIX."user WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TTrigramme[strtolower($ATMdb->Get_field('login'))] = $ATMdb->Get_field('rowid');
	}


//listes des entités
$TEntity = array();
$sql="SELECT rowid,label FROM ".MAIN_DB_PREFIX."entity WHERE 1";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TEntity[str_replace("'", "", strtolower(htmlentities($ATMdb->Get_field('label'), ENT_COMPAT , 'ISO8859-1')))] = $ATMdb->Get_field('rowid');
	}


//loueurs
$TFournisseur = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TFournisseur[str_replace("'", "",strtolower($ATMdb->Get_field('nom')))] = $ATMdb->Get_field('rowid');
	}


// société proprietaires
$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$nom = str_replace("'", "",strtolower($ATMdb->Get_field('nom')));
	$TGroups[$nom] = $ATMdb->Get_field('rowid');
	}
print_r($TGroups);
//exit();

$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('taux')] = $ATMdb->Get_field('rowid');
	}


$idVoiture = getIdType('voiture');


echo 'Import initial des voitures.<br><br>';

$TIdContrat = chargeContrat($ATMdb, $idVoiture); //numContrat=>rowid
$TContrat = array(); //ici les contrats
$TIdRessource = getIDRessource($ATMdb, $idVoiture); //là dedans, on charge les numId=>ID
$TRessource = array(); //ici on chargera les ressources




//----------------------------------------------------------------------------------------------------------------
//PREMIER FICHIER
//----------------------------------------------------------------------------------------------------------------

$nomFichier = 'Etat du Parc PARCOURS.csv';
echo 'Traitement du fichier '.$nomFichier.' : <br>';

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
			
			$plaque = strtoupper(str_replace('-','',$infos[18])); //immatriculation : on enlève les espaces et on met les lettres en majuscules
			//on regarde si la plaque d'immatriculation est dans la base
			if (empty($plaque)){
				echo 'Plaque vide.<br>';null;
			}
			else if (!empty($TIdRessource[$plaque])){
				echo $plaque.' existe déjà<br>';
				$TRessource[$plaque] = new TRH_Ressource;
				$TRessource[$plaque]->load($ATMdb, $TIdRessource[$plaque]);
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
				if (empty($TGroups[str_replace("'", "", strtolower($infos[1]))]))
					{echo $plaque.' : pas de groupe du nom '.$infos[1].'. C\'PRO GROUPE mis.<br>';
					$TRessource[$plaque]->fk_utilisatrice = $TGroups['cpro groupe'];
					}
				else {$TRessource[$plaque]->fk_utilisatrice = $TGroups[str_replace("'", "",strtolower($infos[1]))];}
				
				if (empty($TFournisseur['parcours'])){echo $plaque.' : pas de fournisseur du nom de \'Parcours\' dans la BD<br>';}
				else {$TRessource[$plaque]->fk_loueur = $TFournisseur['parcours'];}
				
				$TRessource[$plaque]->immatriculation = (string)$plaque; //plaque;
				$TRessource[$plaque]->cle = true;
				$TRessource[$plaque]->kit = true; 
				$cpt ++;
				$TRessource[$plaque]->save($ATMdb);
				$TIdRessource[$plaque]=$TRessource[$plaque]->getId(); 
				//echo $plaque.' ajoutee.<BR>';
				
				//si il trouve la personne, il sauvegarde une attribution
				if (!empty($TTrigramme[strtolower($infos[15])])){
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TTrigramme[strtolower($infos[15])]; 
					$emprunt->fk_rh_ressource = $TRessource[$plaque]->getId();
					$emprunt->fk_rh_ressource_type = $idVoiture;
					$emprunt->set_date('date_debut', $infos[5]);
					$emprunt->set_date('date_fin', $infos[14]);
					$emprunt->save($ATMdb);
				}
				else {
					echo 'Trigramme inexistant : '.$infos[15].' : '.$infos[16].'<br>';
				}
			}

			$numContrat = $infos[0];
			
			if (empty($TIdContrat[$numContrat])){
				$TContrat[$numContrat] = new TRH_Contrat;
				$TContrat[$numContrat]->numContrat = $infos[0];
				$TContrat[$numContrat]->libelle = 'Contrat n°'.$infos[0];
				$TContrat[$numContrat]->set_date('date_debut', $infos[5]);
				$TContrat[$numContrat]->set_date('date_fin', $infos[14]);
				$TContrat[$numContrat]->dureeMois = $infos[2];
				$TContrat[$numContrat]->kilometre = $infos[3]*1000;
				$TContrat[$numContrat]->TVA = $TTVA['19.6'];
				$TContrat[$numContrat]->fk_rh_ressource_type = $idVoiture;
				if (empty($TFournisseur['parcours'])){echo $plaque.' : pas de fournisseur du nom de \'Parcours\' dans la BD<br>';}
				else {$TContrat[$numContrat]->fk_tier_fournisseur = $TFournisseur['parcours'];}
				$cptContrat++;
				$TContrat[$numContrat]->save($ATMdb);
				$TIdContrat[$numContrat] = $TContrat[$numContrat]->getId();
				//association contrat-ressource
				$assoc = new TRH_Contrat_Ressource;
				$assoc->fk_rh_ressource = $TRessource[$plaque]->getId();
				$assoc->fk_rh_contrat = $TContrat[$numContrat]->getId();
				$assoc->commentaire = 'Créé à l\'import initial';
				$assoc->save($ATMdb);
			}
			else {
				$TContrat[$numContrat] = new TRH_Contrat;
				$TContrat[$numContrat]->load($ATMdb, $TIdContrat[$numContrat]);
			}
		}
		$numLigne++;
	}
}	
echo $cpt.' voiture creees.<br>';
echo $cptContrat.' contrats creees.<br>';
fclose($handle);


//----------------------------------------------------------------------------------------------------------------
//AUTRES FICHIERS POUR COMPLETER LES INFOS DU PREMIER FICHIER
//----------------------------------------------------------------------------------------------------------------

$TFichier = array(
	"CPRO GROUPE"=>"CPRO GROUPE - PRELVT DU 05.04.13.csv",
	"CPRO INFORMATIQUE"=>"CPRO INFORMATIQUE PREL 05 04 13.csv",
	"CPRO"=>"CPRO - PRELVT DU 05 04 13.csv" 
);

$cpt = 0;
$cptContrat = 0;
foreach ($TFichier as $nomEntity=>$nomFichier) {
	//début du parsing
	echo '<br><br>Traitement du fichier '.$nomFichier.' : <br>';
	$numLigne = 0;
	if (($handle = fopen("../fichierImports/".$nomFichier, "r")) !== FALSE) {
		while(($data = fgetcsv($handle, 0,'\r')) != false){
			//echo 'Traitement de la ligne '.$numLigne.'...';
			if ($numLigne >=1){
				$infos = explode(';', $data[0]);
				//print_r($infos);
				
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
		fclose($handle);
		$cpt = 0;
		$cptContrat = 0;
		}
	else {echo 'erreur sur le fichier '.$nomFichier.'<br>';}
}



//----------------------------------------------------------------------------------------------------------------
//SECOND FICHER  : VOITURES NON PARCOURS
//----------------------------------------------------------------------------------------------------------------
$nomFichier = 'Etat Parc autre parcours.csv';
echo '<br><br>Traitement du fichier '.$nomFichier.' : <br>';


$cptContrat = 0;
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=2){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$plaque = strtoupper(str_replace('-','',$infos[8])); //immatriculation : on enlève les espaces et on met les lettres en majuscules
			$plaque = strtoupper(str_replace(' ','',$plaque));
			//on regarde si la plaque d'immatriculation est dans la base
			if (empty($plaque)){
				null;
			}
			else if (!empty($TIdRessource[$plaque])){
				echo $plaque.' existe déjà<br>';
				$TRessource[$plaque] = new TRH_Ressource;
				$TRessource[$plaque]->load($ATMdb, $TIdRessource[$plaque]);
			}
			else {
				//clés externes
				$TRessource[$plaque] = new TRH_Ressource;
				$TRessource[$plaque]->fk_rh_ressource_type = $idVoiture;
				$TRessource[$plaque]->load_ressource_type($ATMdb);
				$TRessource[$plaque]->libelle = $infos[6].' '.$infos[7];
				$TRessource[$plaque]->numId = $plaque;
				$TRessource[$plaque]->immatriculation = (string)$plaque;
				$TRessource[$plaque]->marquevoit = $infos[6];
				$TRessource[$plaque]->modlevoit = $infos[7];
				$TRessource[$plaque]->typevehicule = $infos[10];
				$TRessource[$plaque]->bailvoit = $infos[12];
				$TRessource[$plaque]->modlevoitversioncomm = $infos[9];
				if(stristr($infos[3], 'parking') !== FALSE) {
				 	$TRessource[$plaque]->localisationvehicule = $infos[3];}
				$TRessource[$plaque]->set_date('date_achat', $infos[13]);
				$TRessource[$plaque]->set_date('date_vente', $infos[14]);
				$TRessource[$plaque]->set_date('date_garantie', '');
				
				//groupe utilisateur
				if (empty($TGroups[str_replace("'","",strtolower($infos[1]))])){
				//	echo 'Pas de groupe du nom '.str_replace("'","",strtolower($infos[1])).'<br>';
				}
				else{
					$TRessource[$plaque]->fk_utilisatrice = $TGroups[str_replace("'","",strtolower($infos[1]))];}
				
				//entité propriétaire si c'est une immo.
				//echo $infos[12];
				if (strtolower($infos[12])=='immo'){
					//echo ' ok';
					if (empty($TEntity[str_replace("'","",strtolower($infos[11]))])){
						echo $plaque.' : Pas d\'entité du nom '.$infos[11].'<br>';
					}
					else{
						$TRessource[$plaque]->fk_proprietaire = $TEntity[str_replace("'","",strtolower($infos[11]))];}
				}
				
				
				//société locatrice si c'est une location
				else {
					if (empty($TFournisseur[str_replace("'","",strtolower($infos[11]))])){
						echo $plaque.' : Pas de loueur du nom '.$infos[11].'<br>';
					}
					else{
						$TRessource[$plaque]->fk_proprietaire = $TFournisseur[strtolower($infos[11])];}
				}
				//echo '<br>';
				$TFournisseur[strtolower($infos[11])];
				$TRessource[$plaque]->cle = true;
				$TRessource[$plaque]->kit = true; 
				$cpt ++;
				$TRessource[$plaque]->save($ATMdb);
				$TIdRessource[$plaque]=$TRessource[$plaque]->getId(); 
				//echo $plaque.' ajoutee.<BR>';
				
				//si il trouve la personne, il sauvegarde une attribution
				if (!empty($TTrigramme[strtolower($infos[2])])){
					$emprunt = new TRH_Evenement;
					$emprunt->type = 'emprunt';
					$emprunt->fk_user = $TTrigramme[strtolower($infos[2])]; 
					$emprunt->fk_rh_ressource = $TRessource[$plaque]->getId();
					if (!empty($infos[13])){
						$emprunt->set_date('date_debut', $infos[13]);
						$emprunt->set_date('date_fin', $infos[14]);
						}
					else{
						$emprunt->set_date('date_debut', '01/01/2013');
						$emprunt->set_date('date_fin', '31/12/2013');
					}
					$emprunt->save($ATMdb);
				}
				else {
					if (!empty($infos[2])){
						echo $plaque.' : Trigramme inexistant : '.$infos[2].'<br>';}
					null;
				}
			

				//si le type est Immo, on créé un contrat associé au véhicule
				if (strtolower($infos[12])=='immo'){
					//le num du contrat est une produit des km, de la durée et du loyer.
					$numContrat = $infos[15]*5+$infos[16]+intval(str_replace(',', '.', $infos[17]));
					$TContrat[$numContrat] = new TRH_Contrat;
					$TContrat[$numContrat]->numContrat = $numContrat; 
					$TContrat[$numContrat]->libelle = 'Contrat n°'.$numContrat;
					$TContrat[$numContrat]->set_date('date_debut', $infos[13]);
					$TContrat[$numContrat]->set_date('date_fin', $infos[14]);
					$TContrat[$numContrat]->dureeMois = $infos[15];
					$TContrat[$numContrat]->kilometre = $infos[16];
					$TContrat[$numContrat]->TVA = $TTVA['19.6'];
					$TContrat[$numContrat]->loyer_TTC = str_replace(',', '.', $infos[17]);
					$TContrat[$numContrat]->fk_rh_ressource_type = $idVoiture;
					if (empty($TFournisseur[strtolower($infos[11])])){
					//	echo 'Pas de fournisseur du nom de '.$infos[11].' dans la BD<br>';
					}
					else {$TContrat[$numContrat]->fk_tier_fournisseur = $TFournisseur[strtolower($infos[11])];}
					
					$cptContrat++;
					$TContrat[$numContrat]->save($ATMdb);
					$TIdContrat[$numContrat] = $TContrat[$numContrat]->getId();
					
					//association contrat-ressource
					$assoc = new TRH_Contrat_Ressource;
					$assoc->fk_rh_ressource = $TRessource[$plaque]->getId();
					$assoc->fk_rh_contrat = $TContrat[$numContrat]->getId();
					$assoc->commentaire = 'Créé à l\'import initial';
					$assoc->save($ATMdb);
				}
			}
		}
		
		$numLigne++;
	}
}	
echo $cpt.' voiture creees.<br>';
echo $cptContrat.' contrats creees.<br>';
fclose($handle);


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





/**
 * Renvoie les contrats liés à une voiture
 */
function chargeContrat(&$ATMdb, $idVoiture){
	global $conf;
	$sql="SELECT rowid, numContrat FROM ".MAIN_DB_PREFIX."rh_contrat
	WHERE fk_rh_ressource_type=".$idVoiture;
	$TListe = array();
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TListe[$ATMdb->Get_field('numContrat')] = $ATMdb->Get_field('rowid');//new TRH_Contrat;
		//$TListe[$ATMdb->Get_field('numContrat')]->load($ATMdb, $ATMdb->Get_field('rowid'));
		
	}
	return $TListe;
}
	