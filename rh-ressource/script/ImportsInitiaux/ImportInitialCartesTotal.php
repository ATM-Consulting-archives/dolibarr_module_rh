<?php
set_time_limit(0);
ini_set('memory_limit','512M');


require('../../config.php');
require('../../class/evenement.class.php');
require('../../class/ressource.class.php');
require('../../lib/ressource.lib.php');

global $conf;
$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);

echo 'Import initial des cartes.<br><br>';
$idVoiture=  getIdType('voiture');

//on charge quelques listes pour avoir les clés externes.
$TVoiture = array();
$sql="SELECT rowid, numId FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idVoiture." AND entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TVoiture[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}


//pour augmenter les chances de trouver les groupes, on met en minuscule et on enlève les ' et les espaces
$TGroups = array();
$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$nom = str_replace(' ','',$ATMdb->Get_field('nom'));
	$nom = str_replace('\'','',$nom);
	$nom = strtolower($nom);
	//echo $ATMdb->Get_field('nom').'   '.$nom.'<br>';
	$TGroups[$nom] = $ATMdb->Get_field('rowid');
}

//exit();

//----------------------Import des cartes total---------------

$idCarteTotal = getIdType('cartetotal');
$cptCarteTotal = 0;
$cptOkPlaque = 0;
$cptOkGroupe = 0;
$nomFichier = "exportEtatDeParcTotal.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br>';
$TRessource = getIDRessource($ATMdb, $idCarteTotal);

//début du parsing
$numLigne = 0;
if (($handle = fopen("./".$nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=2){
			$infos = explode(';', $data[0]);
			//print_r($infos);
			
			$plaque = strtoupper(str_replace('-','',$infos[7])); // on enlève les - et les espaces dans la plaque.
			$plaque = strtoupper(str_replace(' ','',$plaque));
			$numId = strtoupper($infos[6]);
			if ($numId[0]==7){$numId = substr($numId, 7);} //on enlève la partie "7010010" si elle existe au début du numId
			
			if (empty($numId)){
				null;
			}
			else if (!empty($TRessource[$numId])){
				//echo $numId.' existe déjà<br>';
				null;
			}
			else {
				$carteTotal = new TRH_Ressource;
				//clés externes
				$carteTotal->load_by_numId($ATMdb, $numId);
				$jointurePlaque = true;
				$jointureGroupe = true;
				$carteTotal->fk_rh_ressource_type = $idCarteTotal;
				if (empty($TVoiture[$plaque])){echo 'Plaque non trouvee : '.$plaque.'<br>';$jointurePlaque = false;}
				else {$carteTotal->fk_rh_ressource = $TVoiture[$plaque];}
				$carteTotal->numId = $numId;
				$carteTotal->libelle = 'Carte Total '.$numId;
				$carteTotal->set_date('date_achat', $infos[14]);
				$carteTotal->set_date('date_vente', $infos[16]);
				$carteTotal->set_date('date_garantie', '');
				$carteTotal->fk_proprietaire = $conf->entity;
				
				$gp = str_replace(' ','',$infos[12]);
				$gp = str_replace("'",'',$gp);
				$gp = strtolower($gp);
				if (empty($TGroups[$gp])){echo 'Groupe non trouve : '.$gp.'<br>';$jointureGroupe = false;}
				else {$carteTotal->fk_utilisatrice = $TGroups[$gp];}
				
				//champs propres aux cartes total
				$carteTotal->load_ressource_type($ATMdb);
				
				$carteTotal->totalnumcarte = $numId;
				$carteTotal->totalcomptesupport = $infos[1];
				$carteTotal->totaltypesupport = $infos[3];
				$carteTotal->totalinfostation = $infos[11];
				$carteTotal->totallibeestampe = $infos[12];
				$carteTotal->totaladresseestampe = $infos[13];
				$carteTotal->totaltypecodeconfidentiel = $infos[20];
				$carteTotal->totalcarburant = $infos[21];
				$carteTotal->totalplafondcarburant = $infos[22];
				$carteTotal->totaltypeplafond = $infos[23];
				$carteTotal->totalproduit = $infos[24];
				$carteTotal->totalperiodiciteplafond = $infos[25];
				$carteTotal->totalqtplafond = $infos[26];
				$carteTotal->totaluniteplafond = $infos[27];
				$carteTotal->totaloptionservice = $infos[40];
				$carteTotal->totalplafondservice = $infos[41];
				$carteTotal->totalserviceplafondservice = $infos[43];
				$carteTotal->totalperiodiciteplafondservice = $infos[44];
				$carteTotal->totalqtplafondservice = $infos[45];
				$carteTotal->totaluniteplafondservice = $infos[46];
				
				if ($jointurePlaque){$cptOkPlaque++;}
				if ($jointureGroupe){$cptOkGroupe++;}
				$cptCarteTotal ++;
				
				$carteTotal->save($ATMdb);
				$TRessource[$numId]=$carteTotal->getId();
					
				
			}		
		}
		$numLigne++;
	}
}

echo $cptCarteTotal.' cartes Total importes.<br><br><br>';
echo $cptOkPlaque.' jointures plaque ok.<br>';
echo $cptOkGroupe.' jointures groupe ok.<br>';

//Fin du code PHP : Afficher le temps d'éxecution
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo 'Fin du traitement. Durée : '.$page_load_time . " sec<br><br>";
$ATMdb->close();

	
