<?php

/**
 * Importation de la facture Total
 * On créé un évenement par ligne de ce fichier
 * 
 */
 
/*
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
//*/
global $conf;
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;
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
$idTotal = getIdSociete($ATMdb, 'total');
if (!$idTotal){echo 'Pas de fournisseur (tiers) du nom de Total !';exit();}

$TRessource = getIDRessource($ATMdb, $idVoiture);
//foreach ($TRessource as $key => $value) {echo $key.'=>'.$value.'<br>';}//print_r($TRessource);exit;

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
		$TCarte[$TPlaque[$row->fk_rh_ressource]] = $row->rowid;}
}


//Tableau des TVAs
$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0].' AND active=1';
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('taux')] = $ATMdb->Get_field('rowid');
	}

//trouve l'id du SuperAdmin
$idSuperAdmin = getIdSuperAdmin($ATMdb);

//donne l'user qui utilise la carte
/*$TAttribution = array();
foreach ($TCarte as $numId => $rowid) {
	$idUser = ressourceIsEmpruntee($ATMdb, $rowid, date("Y-m-d", time()) );
	if ($idUser!=0){$TAttribution[$numId] = $idUser;} 
}*/



if (empty($nomFichier)){ exit("Pas de fichier total"); /*$nomFichier = "./fichierImports/Facture TOTAL.csv";*/ }
$message = 'Traitement du fichier '.$nomFichier.' : <br><br>';

//pour avoir un joli nom, on prend la chaine après le dernier caractère /  et on remplace les espaces par des underscores
/*$v =  strrpos ( $nomFichier , '/' );
$idImport = substr($nomFichier, $v+1);*/
$idImport = basename($nomFichier);
$idImport = str_replace(' ', '_', $idImport);

$ATMdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement WHERE idImport='$idImport'");

$TCarteInexistantes = array();
$TCarteNonLie = array();
$TCarteVoitureNonAttribue = array();
$idRessFactice = createRessourceFactice($ATMdb, $idCarteTotal, $idImport, $entity, $idTotal);

//print_r($TRessource);
$cpt = 0;
//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, 0,'\r')) != false){
		//echo 'Traitement de la ligne '.$numLigne.'...';
		if ($numLigne >=2 ){
			$infos = explode(';', $data[0]);
			
			//print_r($infos);
			$plaque = $infos[11];
			$plaque = str_replace('-VU', '', $plaque);
			$plaque = str_replace('-', '', $plaque);
			$plaque = str_replace(' ', '', $plaque);
			
			$typeVehicule = 'VU';
				
			if (empty ($TCarte[$plaque])){
				$TCarteNonLie[$plaque] = 1;
				$idRess = $idRessFactice;
				//echo $plaque.' : carte pas liée à une voiture.<br>';
				null;
			}
			else {
				$idRess = $TCarte[$plaque];
				
				$ressourceLocale = new TRH_Ressource;
				//$ATMdb->debug=true;
				$ressourceLocale->load_by_numId($ATMdb, $plaque);
				$typeVehicule = strtoupper( $ressourceLocale->typevehicule );
				
				print "Ajout de l'évènement sur plaque $plaque ($idRess : $typeVehicule)<br>";
				
			
			}
			//else {
				//print_r($infos);echo '<br>';
				$temp = new TRH_Evenement;
				//$temp->load_liste($ATMdb);
				$temp->fk_rh_ressource_type = $idCarteTotal;
				$temp->fk_rh_ressource = $idRess;
				$t = explode(' ',$infos[30]);
				array_shift($t); 
				$nomPeage = htmlentities(implode(' ', $t), ENT_COMPAT , 'ISO8859-1'); 
				
				if ( !empty($TEvents[strtolower($infos[17])]) ){ //si aucun évenement ne correspond, on le met divers
					$temp->type = $TEvents[strtolower($infos[17])];
				}
				else {
					$temp->type = 'divers';
				}
				
				$temp->motif = $infos[17]; //htmlentities($infos[17], ENT_COMPAT , 'ISO8859-1');
				$temp->commentaire = htmlentities($infos[30], ENT_COMPAT , 'ISO8859-1');
				if (!empty($infos[31])){
					$temp->commentaire .= '<br>Kilometrage saisi: '.intval($infos[31]).'<br>
									'.$infos[18].' Litres d\'essence.';
									
				}
				
				//utilisateur qui utilise la ressource au moment de l'évenement
				if (!empty($TRessource[$plaque])){
					$idUser = ressourceIsEmpruntee($ATMdb, $TRessource[$plaque], date("Y-m-d", dateToInt($infos[15])) );
					if ($idUser!=0){ //si il trouve, on l'affecte à l'utilisateur 
						$temp->fk_user = $idUser;}
					else { //sinon à SuperAdmin.
						$temp->fk_user = $idSuperAdmin;}
				}
				else {$temp->fk_user = $idSuperAdmin;}
				
				$temp->set_date('date_debut', $infos[15]);
				$temp->set_date('date_fin', $infos[15]);
				$temp->coutTTC = strtr($infos[19], ',','.');
				$temp->coutEntrepriseTTC = strtr($infos[19], ',','.');
				
				$taux = number_format(floatval(str_replace(',', '.', $infos[25])),1);
				
				/*
				 * Correction des taux d'import pour traitement retour
				 */ 
				$typeCarburant = (strpos( strtolower($infos[17]), 'gazole')!==false) ? 'gazole' : 'essence';
				
				if($typeVehicule=='VP' && $typeCarburant=='essence')$taux=0;
				else if($typeVehicule=='VP' && $typeCarburant=='gazole')$taux=15.68;
//			print_r($TTVA);
				
				$temp->TVA = $TTVA["$taux"];
//print $temp->TVA;
				$temp->coutEntrepriseHT = round( $temp->coutEntrepriseTTC / (1+ ($taux/100) ),2  )  ;
				$temp->idImport = $idImport;
				$temp->numFacture = $infos[1];
				$temp->date_facture = dateToInt($infos[3]);
				$temp->compteFacture = $infos[13];
				$temp->litreEssence = floatval(strtr($infos[18],',','.'));
				$temp->kilometrage = intval($infos[31]);
				$temp->fk_fournisseur = $idTotal;
				$temp->entity = $entity;
				$temp->save($ATMdb);
				
				$cpt++;
			
		}
		$numLigne++;
		
		//print_r(explode('\n', $data));
	}	
}
if(!empty($TCarteInexistantes)) {
	$message .= 'Erreurs : Pas de carte correspondante : <br>';
	foreach ($TCarteInexistantes as $key => $value) {
		$message .= '     '.$key.'<br>';
	}
	
}

if(!empty($TCarteNonLie)) {
	$message .=  '<br>Erreurs : Carte non lié : <br>';
	foreach ($TCarteNonLie as $key => $value) {
		$message .= '     '.$key.'<br>';
	}
	
}

if(!empty($TCarteVoitureNonAttribue)) {
	$message .=  '<br>Erreurs : Carte lié à une voiture mais non attribué : <br>';
	foreach ($TCarteVoitureNonAttribue as $key => $value) {
		$message .= '     '.$key.'<br>';
	}
	
}
$message .= '<br>Toutes ces cartes ont été liés à la ressource de numid et libellé : \'factice'.$idImport.'\'<br>';



$message .= 'Fin du traitement. '.$cpt.' événements créés.<br><br>';
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


/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
	return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}


