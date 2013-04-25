<?php
/*
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');
//*/


ini_set('memory_limit','512M'); //taille mémoire limitée
set_time_limit(0); //durée d'execution illimitée.
global $conf;
$ATMdb=new Tdb;
// relever le point de départ
$timestart=microtime(true);

$default = 359940; //consideration conso infinie : 99H
$coutMinute = 0.09;		//0.09€/min


//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$TUserInexistants = array();
$TNumeroInexistants = array();
$TCompteurs = array();
$TLimites = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	$TCompteurs[$ATMdb->Get_field('rowid')] = array(
		'conso'=>0				//en sec
		,'consoInterne' => 0 	//en sec
		,'consoExterne' => 0 	//en sec
		,'conso3G' => 0
		,'consoSMS' => 0
		
		);
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


$TNumero = array();
$sql="SELECT rowid, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TNumero[intval($ATMdb->Get_field('numId'))] = $ATMdb->Get_field('rowid');
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
if (empty($nomFichier)){$nomFichier = "./fichierImports/detail_appels.csv";}
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';

//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		//echo 'Traitement de la ligne '.$numLigne.'... ';
		if ($numLigne >=3){
			$infos = explode(';', $data[0]);
			$mois = substr($infos[4],3,7);
			if (! array_key_exists ( strtolower($infos[2]) , $TUser )){
				$TUserInexistants[strtolower($infos[2])] = 1;
			}
			else {
				$id = $TUser[strtolower($infos[2])];
				$TCompteurs[$id]['num'] = $infos[1];
				//on cherche premierement le type d'appel
				
				//echo $infos[11].' :   ';
				if (strpos(strtolower($infos[11]),'connexion') !== FALSE){
					//echo 'connexion '.$infos[10].'<br>';
					$TCompteurs[$id]['conso3G'] += floatval($infos[10]);
				}
				else if (strpos(strtolower($infos[11]),'appel') !== FALSE){
					//echo 'appels';
					if (strpos(strtolower($infos[11]),'interne') !== FALSE){
						//echo ' internes ';
						$h = intval(substr($infos[10], 0,2));
						$m = intval(substr($infos[10], 3,2));
						$s = intval(substr($infos[10], 6,2));
						//echo $h.':'.$m.':'.$s;					
						$TCompteurs[$id]['consoInterne'] += $s+60*$m+3600*$h;
						$TCompteurs[$id]['conso'] += $s+60*$m+3600*$h;
					}
					else{
						//echo ' externes ';
						$h = intval(substr($infos[10], 0,2));
						$m = intval(substr($infos[10], 3,2));
						$s = intval(substr($infos[10], 6,2));
						//echo $h.':'.$m.':'.$s;					
						$TCompteurs[$id]['consoExterne'] += $s+60*$m+3600*$h;
						$TCompteurs[$id]['conso'] += $s+60*$m+3600*$h;
					}
				}
				else if (strpos(strtolower($infos[11]),'sms') !== FALSE){
					//echo 'sms';
					$TCompteurs[$id]['consoSMS'] += 1;
					
				}
				else {
					echo '                      PAS TRAITEE : '.$infos[11];
				}
			}
		}
		
		//echo '<br>';
		$numLigne++;
	}
}





//----------------------------CREATION DES FACTURES-------------------
	
$cptFacture = 0;

//----------------------ECRITURE DANS LE FICHIER D'EXPORT------------------------
//LIGNE D'EN TETE
$export = fopen('./script/exports/exportMoisAvril2013.csv', 'w');
fwrite($export, "Utilisateur;Limite Générale;Conso Générale;Limite Interne;Conso Interne;Durée Interne Facturée;Limite Externe;Conso Externe;Durée Externe Facturée;Durée Totale Facturée;Conso Data;Data Facturés;SMS;SMS facturés;Coût minute;Total Facturé à l'utilisateur\n\r");
//UNE LIGNE PAR UTILISATEUR
foreach ($TUser as $nom => $id) {
	//print_r($TCompteurs[$id]);
	//echo '<br>';
	if ( array_key_exists ( $TCompteurs[$id]['num'] , $TNumero )){
		//Sauvegarde de la facture : une facture par personne et par mois.
		$temp = new TRH_Evenement;
		//infos générales
		$temp->set_date('date_debut', $infos[6]);
		$temp->set_date('date_fin', $infos[6]);
		$temp->type = 'factTel';
		//clés externes
		$temp->fk_rh_ressource = $TNumero[$TCompteurs[$id]['num']];
		$temp->fk_user = $id;
		$temp->motif = 'Facture téléphonique '.$mois;
		
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
		
		
		$temp->commentaire = "Consommation Générale : ".intToString(intval(($dureeExt+$dureeInt))).
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
		$temp->coutTTC = $coutInt+$coutExt;
		$temp->coutEntrepriseTTC = (($TCompteurs[$id]['consoExterne']+$TCompteurs[$id]['consoInterne'])/60)*$coutMinute;  
		$temp->TVA = $tva;
		$temp->save($ATMdb);		
		$cptFacture++;
		
		//ecriture de l'export
		fwrite($export, $nom.';');
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
		fwrite($export, "\n");
		
	}
	else {
		if (isset($TCompteurs[$id]['num']))
			{$TNumeroInexistants[$TCompteurs[$id]['num']] = 1;}
	}
}
fclose($export);



//----------------------------BILAN DES UTILISATEURS ET NUMERO INEXISTANTS-----------------------
foreach ($TUserInexistants as $nom=> $rien) {
	echo 'Erreur : Utilisateur '.$nom.' inexistant dans la base.<br>';
}

foreach ($TNumeroInexistants as $num => $rien) {
	echo 'Erreur : Numéro '.$num.' inexistant dans la base.<br>';
}






//----------------------------Fin du code PHP------------------------------------
$timeend=microtime(true);
$page_load_time = number_format($timeend-$timestart, 3);
echo $cptFacture." factures crees.<br><br>";
echo "Fin du traitement. ".'Durée : '.$page_load_time . " sec.<br><br><br>";


function intToString($val){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	if ($h==0 && $m==0){return '00:00';}
	return $h.':'.$m;
}

