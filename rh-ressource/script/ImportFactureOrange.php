<?php

/*
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
require('../lib/ressource.lib.php');
//*/

ini_set('memory_limit','512M'); //taille mémoire limitée
set_time_limit(0); //durée d'execution illimitée.
global $conf;
$entity = (isset($_REQUEST['entity'])) ? $_REQUEST['entity'] : $conf->entity;
$ATMdb=new TPDOdb;
// relever le point de départ
$timestart=microtime(true);

//$default = 359940; //consideration conso infinie : 99H
$message = '';

//on charge quelques listes pour avoir les clés externes.
$idCarteSim = getIdType('cartesim');
$idOrange = getIdSociete($ATMdb, 'orange');
if (!$idOrange){echo 'Pas de fournisseur (tiers) du nom de Orange !';exit();}
$TNumeroInexistants = array();
$TCompteurs = array();

$TNonAttribuee = array();
$TNumero = array();
$TCoutMinute = array();
$sql="SELECT rowid, numId, coutminuteint, coutminuteext FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idCarteSim." 
	AND entity IN (0,".$conf->entity.")";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TNumero[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	$TCoutMinuteInt[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('coutminuteint');
	$TCoutMinuteExt[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('coutminuteext');
	}


// Pour trouver les utilisateurs, on ne regarde pas la colonne du fichier, mais qui utilise la ressource au moment de la facture
$TAttribution = array();
foreach ($TNumero as $numId => $rowid) {
	$TAttribution[$numId] = ressourceIsEmpruntee($ATMdb, $rowid, date("Y-m-d", time()) );
}

$TUser = array();
$TRowidUser = array();
$sql="SELECT rowid, name, firstname, login FROM ".MAIN_DB_PREFIX."user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	$TRowidUser[] = $ATMdb->Get_field('rowid');
	
}

$TGroups= array();
$sql="SELECT fk_user, fk_usergroup
	FROM ".MAIN_DB_PREFIX."usergroup_user";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');
}

$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');}
$ttva = array_keys ($TTVA, '19.6');
$tva = $ttva[0];






//------------------CHARGEMENT DES REGLES ---------------------
//chargement des limites de conso pour chaque user, selon les règles
$TLimites = array();

$TLimites = load_limites_telephone($ATMdb, $TGroups, $TRowidUser);

/*echo '<br><br><br>';
foreach ($TLimites as $key => $value) {
	echo $key.' ';	
	print_r($value);
	echo '<br>';
}
exit();*/



//----------------TRAITEMENT DU FICHIER DES LIGNES D'APPELS----------------------------------------------------------
if (empty($nomFichier)){$nomFichier = "./fichierImports/detail_appels10.csv";}
$message .= 'Traitement du fichier '.$nomFichier.' : <br><br>';

$idImport = _url_format(basename($nomFichier), false, true);
$ATMdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement WHERE idImport='$idImport'");
$ATMdb->Execute("DELETE FROM ".MAIN_DB_PREFIX."rh_evenement_appel WHERE idImport='$idImport'");

$TDonnees = array();

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle, "", ";")) != false){
		//echo 'Traitement de la ligne '.$numLigne.'... ';
		if ($numLigne >=3){
			
			$infos = explode(';', $data[0]);
			$infos = $data;
			
			$TDonnees[] = $infos;
			
			//var_dump($infos);
			$mois = substr($infos[4],3,7);
			
			$num = $infos[1];
			
			if ($num[0]=='0'){$num = '33'.substr($num, 1);}  	//0607021672=>33607021672
			if ($num[0]=='6'){$num = '33'.$num;}				// 607021672=>33607021672
			if ($num[0]=='+'){$num = substr($num, 1);}  		//+33607021672=>33607021672
			
			
			
			if ( empty($TNumero[$num] )){
				$TNumeroInexistants[$num] = 1;
			}
			
			else{
				$idUser = $TAttribution[$num] ;
				
				if ($idUser!=0){
				//exit();
				
					//si c'est la 1ere fois qu'on passe une ligne avec cet User, on initialise le compteur
					if (empty($TCompteurs[$idUser])){
						$TCompteurs[$idUser] = array(
							'num'=>$num
							,'conso'=>0				//en sec
							,'consoInterne' => 0 	//en sec
							,'consoExterne' => 0 	//en sec
							,'conso3G' => 0
							,'consoSMS' => 0
							,'appels' => str_pad('Date',12).str_pad('Heure',12).str_pad('Numéro',15).str_pad('Type',60).str_pad('Durée',15).'<br>'
						);
					}
					
					//on cherche premierement le type d'appel
					if (strpos(strtolower($infos[11]),'connexion') !== FALSE){
						$TCompteurs[$idUser]['conso3G'] += floatval($infos[10]);
					}
					else if (strpos(strtolower($infos[11]),'appel') !== FALSE){
						//echo 'appels';
						$h = intval(substr($infos[10], 0,2));
						$m = intval(substr($infos[10], 3,2));
						$s = intval(substr($infos[10], 6,2));
						if (strpos(strtolower($infos[11]),'interne') !== FALSE){
							//echo ' internes ';					
							$TCompteurs[$idUser]['consoInterne'] += $s+60*$m+3600*$h;
							$TCompteurs[$idUser]['conso'] += $s+60*$m+3600*$h;
						}
						else{
							//echo ' externes ';
							$TCompteurs[$idUser]['consoExterne'] += $s+60*$m+3600*$h;
							$TCompteurs[$idUser]['conso'] += $s+60*$m+3600*$h;
						}
						$TCompteurs[$idUser]['appels'] .= str_pad($infos[6],12).str_pad($infos[7],12).str_pad($infos[8],15).str_pad(trim($infos[11]),60).str_pad($infos[9],15).'<br>';
					}
					else if (strpos(strtolower($infos[11]),'sms') !== FALSE){
						//echo 'sms';
						$TCompteurs[$idUser]['consoSMS'] += 1;
					}
					else {
						//echo 'PAS TRAITEE : '.$infos[11].'<br>';
					}
				}
				else {
					$TNonAttribuee[$num] = 1;
					
				}
			}
		}
		$numLigne++;
	}
}


//----------------------------CREATION DES FACTURES-------------------
	
$cptFacture = 0;
//UNE LIGNE PAR UTILISATEUR
foreach ($TUser as $nom => $id) {
	
	//echo $nom.' '.$TCompteurs[$id]['num']; print_r($TCompteurs[$id]);echo '.<br>';
	if ( !empty($TCompteurs[$id] )){
		//Sauvegarde de la facture : une facture par personne et par mois.
		$fact = new TRH_Evenement;
		
		$fact->set_date('date_debut', $infos[6]);
		$fact->set_date('date_fin', $infos[6]);
		$fact->type = 'factTel';
		//clés externes
		$fact->fk_rh_ressource_type = $idCarteSim;
		$fact->fk_rh_ressource = $TNumero[$TCompteurs[$id]['num']];
		$fact->fk_user = $id;
		$fact->motif = 'Facture téléphonique '.$mois;
		$fact->appels = $TCompteurs[$id]['appels'];
		
		$dureeInt = intval($TCompteurs[$id]['consoInterne']/60);
		$dureeExt = intval($TCompteurs[$id]['consoExterne']/60);
		
		$fact->dureeI = $dureeInt;
		$fact->dureeE = $dureeExt;
		$fact->duree = $dureeInt + $dureeExt;
		//cout minute selon la carte sim
		$coutMinuteInt = $TCoutMinuteInt[$TCompteurs[$id]['num']];
		$coutMinuteExt = $TCoutMinuteExt[$TCompteurs[$id]['num']];
		
		//calcul de la durée facturée
		if ($TCompteurs[$id]['consoInterne'] <= $TLimites[$id]['limInterne'])	
			{$dureeFactInt = 0;}
		else
			{$dureeFactInt = intval(($TCompteurs[$id]['consoInterne'] - $TLimites[$id]['limInterne'])/60);}
		
		if ($TCompteurs[$id]['consoExterne'] <= $TLimites[$id]['limExterne'])	
			{$dureeFactExt = 0;}
		else 	
			{$dureeFactExt = intval(($TCompteurs[$id]['consoExterne'] - $TLimites[$id]['limExterne'])/60);}
		
		//calcul des cout sms et 3G
		($TLimites[$id]['smsIllimite']) ? $smsFact = 0 : $smsFact = $TCompteurs[$id]['consoSMS'];
		($TLimites[$id]['dataIllimite']) ? $dataFact = 0 : $dataFact = $TCompteurs[$id]['conso3G'];
		($TLimites[$id]['data15Mo']) ? $dataFact = ($TCompteurs[$id]['conso3G']-15000) : $dataFact = $TCompteurs[$id]['conso3G'];
		($dataFact<0) ? $dataFact = 0 : null;
		
		$fact->totalIFact = $dureeFactInt*$coutMinuteInt;
		$fact->totalEFact = $dureeFactExt*$coutMinuteExt;
		$fact->totalFact = $fact->totalEFact + $fact->totalIFact;
		$fact->natureRefac = $TLimites[$id]['natureRefac'];
		$fact->montantRefac = $TLimites[$id]['montantRefac'];
		
		if (($dureeFactInt*$coutMinuteInt>0) || ($dureeFactExt*$coutMinuteExt>0) || (($dureeFactInt*$coutMinuteInt+$dureeFactExt*$coutMinuteExt)>0) ){
			//echo 'Dépassement : '.$TCompteurs[$id]['num'].'<br>';
			null;
		}
		
		
		$fact->commentaire = "Conso Gén. : ".intToString(intval(($dureeExt+$dureeInt)), false).
								"    Limite : ".intToString(intval($TLimites[$id]['lim'])/60, false). 
								"<br>Conso int. : ".intToString(intval($dureeInt), false).
								"    Limite : ".intToString(intval($TLimites[$id]['limInterne'])/60, false).
								"<br>Conso ext. : ".intToString(intval($dureeExt), false).
								"    Limite : ".intToString(intval($TLimites[$id]['limExterne'])/60, false).
								"<br>Durée Fact: ".intToString($dureeFactInt+$dureeFactExt, false).
								"    Data fact: ".$dataFact."o".
								"    SMS fact: ".$smsFact.
								"<br>Coût int: ".$coutMinuteInt."€/min ; coût ext: ".$coutMinuteExt."€/min";
							
		//echo $temp->commentaire.'<br>';
		$fact->coutTTC = $dureeFactInt*$coutMinuteInt+$dureeFactExt*$coutMinuteExt;
		$fact->coutEntrepriseTTC = 0;//(($TCompteurs[$id]['consoExterne']+$TCompteurs[$id]['consoInterne'])/60)*$coutMinute;  
		$fact->TVA = $tva;
		/*$fact->dureeE = $TCompteurs[$id]['consoExterne'];
		$fact->dureeI = $TCompteurs[$id]['consoInterne'];
		$fact->duree = $TCompteurs[$idUser]['conso'];*/ 
		$fact->entity = $entity;
		$fact->fk_fournisseur = $idOrange;
		
		$fact->idImport = $idImport;
		
		$fact->save($ATMdb);		
		$cptFacture++;
		
		//$num_import = _getNextNumeroImport();
		//_saveFactureIntoTable($ATMdb, $TDonnees, $TCompteurs[$id]['num'], $idImport);
		
	}
	
}

_saveFactureIntoTable($ATMdb, $TDonnees, $idImport);

function _saveFactureIntoTable(&$ATMdb, &$TDonnees, $idImport) {
	//var_dump($TDonnees);exit;
	
	foreach($TDonnees as $TArrayLine){
		
		//if("33".$TArrayLine[1] == $num_ligne){

			$TRH_event_appel = new TRH_Evenement_appel;
			//$TRH_event_appel->num_import = $num_import;
			$TRH_event_appel->idImport = $idImport;
			$TRH_event_appel->compte_facture = $TArrayLine[0];
			$TRH_event_appel->num_gsm = "33".$TArrayLine[1];
			$TRH_event_appel->nom_abonne = $TArrayLine[2];
			$TRH_event_appel->num_facture = $TArrayLine[3];
			$timestamp = strtotime(str_replace('/', '-', $TArrayLine[4]));
			$TRH_event_appel->date_facture = $timestamp;
			$TRH_event_appel->num_abonne = $TArrayLine[5];
			$timestamp = strtotime(str_replace('/', '-', $TArrayLine[6]).' '.$TArrayLine[7]);
			$TRH_event_appel->date_appel = $timestamp;
			$TRH_event_appel->num_appele = $TArrayLine[8];
			$TRH_event_appel->volume_reel = $TArrayLine[9];
			$TRH_event_appel->volume_facture = $TArrayLine[10];
			$TRH_event_appel->type_appel = $TArrayLine[11];
			$TRH_event_appel->montant_euros_ht = price2num($TArrayLine[12]);
			
			$TRH_event_appel->save($ATMdb);
		
		//}
	}
	
}

function _getNextNumeroImport() {
	
	global $db;
	
	$sql = "SELECT MAX(num_import) as num_libre";
	$sql.= " FROM ".MAIN_DB_PREFIX."rh_evenement_appel";
	$resql = $db->query($sql);
	
	while($res = $db->fetch_object($resql)) {
		return $res->num_libre+1;
	}
	
	return 1;
	
}

//----------------------------BILAN DES NUMEROS INEXISTANTS-----------------------

$message .=  '<br>'.count($TNonAttribuee).' téléphones non attribués : <br><br>';
foreach ($TNonAttribuee as $key => $value) {
	$message .=  $key.', ';
}
$message .=  '<br><br>'.count($TNumeroInexistants).' téléphones non trouvés<br><br>';
foreach ($TNumeroInexistants as $num => $rien) {
	$message .= 'Erreur : Numéro '.$num.' inexistant dans la base.<br>';
}


$ATMdb->close();



//----------------------------Fin du code PHP------------------------------------
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
$message .= $cptFacture." factures crees.<br><br>";
$message .= $numLigne." lignes traitées<br><br>";
$message .= "Fin du traitement. ".'Durée : '.$page_load_time . " sec.<br><br>";
echo $message;
//echo $message;
send_mail_resources('Import - Factures Orange',$message);
