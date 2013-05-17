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
$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);

$default = 359940; //consideration conso infinie : 99H
$coutMinute = 0.09;		//0.09€/min
$message = '';

//on charge quelques listes pour avoir les clés externes.
$idCarteSim = getIdType('cartesim');
$TUser = getUsers();
$TUserInexistants = array();
$TNumeroInexistants = array();
$TCompteurs = array();


$TNumero = array();
$sql="SELECT rowid, numId FROM ".MAIN_DB_PREFIX."rh_ressource 
	WHERE fk_rh_ressource_type=".$idCarteSim." 
	AND entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TNumero[$ATMdb->Get_field('numId')] = $ATMdb->Get_field('rowid');
	}


$TLimites = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	$TLimites[$ATMdb->Get_field('rowid')] = array(
		'lim'=>$default
		,'limInterne' => $default	//en sec
		,'limExterne' => $default	//en sec
		,'dataIllimite' => false
		,'dataIphone' => false
		,'mailforfait'=> false
		,'smsIllimite'=> false
		,'data15Mo'=> false
		);	
}



$TGroups= array();
$sql="SELECT fk_user, fk_usergroup
	FROM ".MAIN_DB_PREFIX."usergroup_user
	WHERE entity=".$conf->entity."
	";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');
}

$TTVA = array();
$sqlReq="SELECT rowid, taux FROM ".MAIN_DB_PREFIX."c_tva WHERE fk_pays=".$conf->global->MAIN_INFO_SOCIETE_PAYS[0];
$ATMdb->Execute($sqlReq);
while($ATMdb->Get_line()) {
	$TTVA[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('taux');}
$ttva = array_keys ( $TTVA, '19.6');
$tva = $ttva[0];






//------------------CHARGEMENT DES REGLES ---------------------
//chargement des limites de conso pour chaque user, selon les règles
$sql="SELECT fk_user, fk_usergroup, choixApplication, dureeInt, dureeExt,duree
	, dataIllimite, dataIphone, smsIllimite, mailforfait, data15Mo
	FROM ".MAIN_DB_PREFIX."rh_ressource_regle
	WHERE entity=".$conf->entity."
	";

$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	if ($ATMdb->Get_field('choixApplication')=='user'){
		modifierLimites($TLimites, $ATMdb->Get_field('fk_user')
			, $ATMdb->Get_field('duree')
			, $ATMdb->Get_field('dureeInt')
			, $ATMdb->Get_field('dureeExt')
			, $ATMdb->Get_field('dataIllimite')
			, $ATMdb->Get_field('dataIphone')
			, $ATMdb->Get_field('mailforfait')
			, $ATMdb->Get_field('smsIllimite')
			, $ATMdb->Get_field('data15Mo')
			);
		}
	else if ($ATMdb->Get_field('choixApplication')=='group'){
		if (empty($TGroups[$ATMdb->Get_field('fk_usergroup')]))
			{$message .= 'Groupe n°'.$ATMdb->Get_field('fk_usergroup').' inexistant.<br>';}
		else{
			foreach ($TGroups[$ATMdb->Get_field('fk_usergroup')] as $members) {
				modifierLimites($TLimites, $members
					, $ATMdb->Get_field('duree')
					, $ATMdb->Get_field('dureeInt')
					, $ATMdb->Get_field('dureeExt')
					, $ATMdb->Get_field('dataIllimite')
					, $ATMdb->Get_field('dataIphone')
					, $ATMdb->Get_field('mailforfait')
					, $ATMdb->Get_field('smsIllimite')
					, $ATMdb->Get_field('data15Mo')
					);
				}
			}
		}
	else if ($ATMdb->Get_field('choixApplication')=='all'){
		foreach ($TUser as $idUser) {
			modifierLimites($TLimites, $idUser
				, $ATMdb->Get_field('duree')
				, $ATMdb->Get_field('dureeInt')
				, $ATMdb->Get_field('dureeExt')
				, $ATMdb->Get_field('dataIllimite')
				, $ATMdb->Get_field('dataIphone')
				, $ATMdb->Get_field('mailforfait')
				, $ATMdb->Get_field('smsIllimite')
				, $ATMdb->Get_field('data15Mo')
				);
			}
		}
	}

function modifierLimites(&$TLimites, $fk_user, $gen,  $int, $ext, $dataIll = false, $dataIphone = false, $mail = false, $smsIll = false, $data15Mo= false){
	if (($TLimites[$fk_user]['limInterne'] > $int*60)){
		$TLimites[$fk_user]['limInterne'] = $int*60;
	}
	if (($TLimites[$fk_user]['limExterne'] > $ext*60)) {
		$TLimites[$fk_user]['limExterne'] = $ext*60;
	}
	
	if ($TLimites[$fk_user]['lim'] > ($gen*60)){
		$TLimites[$fk_user]['lim'] = $gen*60;
	}
	
	$TLimites[$fk_user]['dataIllimite'] =$dataIll;
	$TLimites[$fk_user]['dataIphone'] =$dataIphone;
	$TLimites[$fk_user]['mailforfait']=$mail;
	$TLimites[$fk_user]['smsIllimite']=$smsIll;
	$TLimites[$fk_user]['data15Mo']=$data15Mo;
		
	return;
}



//----------------TRAITEMENT DU FICHIER DES LIGNES D'APPELS----------------------------------------------------------
if (empty($nomFichier)){$nomFichier = "./fichierImports/detail_appels10.csv";}
$message .= 'Traitement du fichier '.$nomFichier.' : <br><br>';

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		//echo 'Traitement de la ligne '.$numLigne.'... ';
		if ($numLigne >=3){
			$infos = Œ explode(';', $data[0]);
			$mois = substr($infos[4],3,7);
			$num = $infos[1];
			if (empty($TUser[strtolower($infos[2])] )){
				$TUserInexistants[strtolower($infos[2])] = 1;
			}
			else if ( empty($TNumero[$num] )){
				$TNumeroInexistants[$num] = 1;
			}
			else{
				$idUser = $TUser[strtolower($infos[2])];
				
				//si c'est la 1ere fois qu'on passe une ligne avec cet User, on initialise le compteur
				if (empty($TCompteurs[$idUser])){
					$TCompteurs[$idUser] = array(
						'num'=>$num
						,'conso'=>0				//en sec
						,'consoInterne' => 0 	//en sec
						,'consoExterne' => 0 	//en sec
						,'conso3G' => 0
						,'consoSMS' => 0
						
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
				}
				else if (strpos(strtolower($infos[11]),'sms') !== FALSE){
					//echo 'sms';
					$TCompteurs[$idUser]['consoSMS'] += 1;
					
				}
				else {
					//echo 'PAS TRAITEE : '.$infos[11].'<br>';
				}
			}
		}
		$numLigne++;
	}
}





//----------------------------CREATION DES FACTURES-------------------
	
$cptFacture = 0;
//echo count($TCompteurs);
//----------------------ECRITURE DANS LE FICHIER D'EXPORT------------------------
//LIGNE D'EN TETE
//$export = fopen('./script/exports/exportMoisAvril2013.csv', 'w');
//fwrite($export, "Utilisateur;Limite Générale;Conso Générale;Limite Interne;Conso Interne;Durée Interne Facturée;Limite Externe;Conso Externe;Durée Externe Facturée;Durée Totale Facturée;Conso Data;Data Facturés;SMS;SMS facturés;Coût minute;Total Facturé à l'utilisateur\n\r");
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
		$fact->dureeI = intval($TCompteurs[$id]['consoInterne']/60);
		$fact->dureeE = intval($TCompteurs[$id]['consoExterne']/60);
		$fact->duree = $temp->dureeI + $temp->dureeE;
		
		$dureeInt = intval($TCompteurs[$id]['consoInterne']/60);
		$dureeExt = intval($TCompteurs[$id]['consoExterne']/60);
		
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
		
		$fact->commentaire = "Consommation Générale : ".intToString(intval(($dureeExt+$dureeInt))).
								"\nLimite Générale : ".intToString(intval($TLimites[$id]['lim'])/60). 
								"\nConsommation interne : ".intToString(intval($dureeInt)).
								"\nLimite interne : ".intToString(intval($TLimites[$id]['limInterne'])/60).
								"\nConsommation externe : ".intToString(intval($dureeExt)).
								"\nLimite externe : ".intToString(intval($TLimites[$id]['limExterne'])/60).
								"\nDurée Facturée : ".intToString($dureeFactInt+$dureeFactExt).
								"\nDonnées facturés: ".$dataFact."o".
								"\nSMS facturés: ".$smsFact.
								"\nCoût minute: ".$coutMinute."€/min";
							
		//echo $temp->commentaire.'<br>';
		$fact->coutTTC = $coutInt+$coutExt;
		$fact->coutEntrepriseTTC = 0;//(($TCompteurs[$id]['consoExterne']+$TCompteurs[$id]['consoInterne'])/60)*$coutMinute;  
		$fact->TVA = $tva;
		
		
		
		$fact->save($ATMdb);		
		$cptFacture++;
		
		//ecriture de l'export
		/*fwrite($export, $nom.';');
		fwrite($export, intToString(intval($TLimites[$id]['lim']/60)).";");
		fwrite($export, intToString(intval($dureeExt+$dureeInt)).";");
		fwrite($export, intToString(intval($TLimites[$id]['limInterne']/60)).";");
		fwrite($export, intToString(intval($dureeInt)).';');
		fwrite($export, intToString(intval($dureeFactInt)).';');
		fwrite($export, intToString(intval($TLimites[$id]['limExterne']/60)).';');
		fwrite($export, intToString(intval($dureeExt)).';');
		fwrite($export, intToString(intval($dureeFactExt)).';');
		fwrite($export, intToString(intval($dureeFactInt+$dureeFactExt)).';' );
		fwrite($export, $TCompteurs[$id]['conso3G'].';');
		fwrite($export, $dataFact.';');
		fwrite($export, $TCompteurs[$id]['consoSMS'].';');
		fwrite($export, $smsFact.';');
		fwrite($export, $coutMinute.';');
		fwrite($export, $coutMinute*intval($dureeFactInt+$dureeFactExt).';');
		fwrite($export, "\n");*/
		
		}
	
}
//fclose($export);



//----------------------------BILAN DES UTILISATEURS ET NUMERO INEXISTANTS-----------------------
foreach ($TUserInexistants as $nom=> $rien) {
	$message .= 'Erreur : Utilisateur '.$nom.' inexistant dans la base.<br>';
}

foreach ($TNumeroInexistants as $num => $rien) {
	$message .= 'Erreur : Numéro '.$num.' inexistant dans la base.<br>';
}






//----------------------------Fin du code PHP------------------------------------
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
$message .= $cptFacture." factures crees.<br><br>";
$message .= "Fin du traitement. ".'Durée : '.$page_load_time . " sec.<br><br>";

//echo $message;
send_mail_resources('Import - Factures Orange',$message);
