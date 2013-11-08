<?php
require('../config.php');
require('../class/evenement.class.php');
require('../class/ressource.class.php');

global $conf;

$ATMdb=new Tdb;
$default = 100000; //consideration conso infinie
		
//on charge quelques listes pour avoir les clés externes.
$TUser = array();
$TCompteurs = array();
$TLimites = array();
$sql="SELECT rowid, name, firstname FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TUser[strtolower($ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('name'))] = $ATMdb->Get_field('rowid');
	$TCompteurs[$ATMdb->Get_field('rowid')] = array(
		'consoInterne' => 0 	//en sec
		,'consoExterne' => 0 	//en sec
		,'conso3G' => 0
		,'consoSMS' => 0
		);
	$TLimites[$ATMdb->Get_field('rowid')] = array(
		'limInterne' => $default	//en sec
		,'limExterne' => $default	//en sec
		,'lim3G' => $default
		,'limSMS' => $default
		);
		
	}

/*
$TNumero = array();
$sql="SELECT rowid, numero FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity;
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TNumero[$ATMdb->Get_field('numero')] = $ATMdb->Get_field('rowid');
	}
//*/

//chargement des groupes et des users dans la liste $TGroups;
$TGroups= array();
$sql="SELECT fk_user, fk_usergroup
	FROM ".MAIN_DB_PREFIX."usergroup_user
	WHERE entity=".$conf->entity."
	";
$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	$TGroups[$ATMdb->Get_field('fk_usergroup')][] = $ATMdb->Get_field('fk_user');

}


//chargement des limites de conso pour chaque user, selon les règles
$sql="SELECT fk_user, fk_usergroup, choixApplication, dureeHInt, dureeMInt, dureeHExt, dureeMExt, limSMS
	FROM ".MAIN_DB_PREFIX."rh_ressource_regle
	WHERE entity=".$conf->entity."
	";

$ATMdb->Execute($sql);
while($ATMdb->Get_line()) {
	if ($ATMdb->Get_field('choixApplication')=='user'){
		modifierLimites($TLimites, $ATMdb->Get_field('fk_user')
			, $ATMdb->Get_field('dureeHInt')
			, $ATMdb->Get_field('dureeMInt')
			, $ATMdb->Get_field('dureeHExt')
			, $ATMdb->Get_field('dureeMExt')
			, $ATMdb->Get_field('limSMS')
			);
		}
	else if ($ATMdb->Get_field('choixApplication')=='group'){
		foreach ($TGroups[$ATMdb->Get_field('fk_usergroup')] as $members) {
			modifierLimites($TLimites, $members
				, $ATMdb->Get_field('dureeHInt')
				, $ATMdb->Get_field('dureeMInt')
				, $ATMdb->Get_field('dureeHExt')
				, $ATMdb->Get_field('dureeMExt')
				, $ATMdb->Get_field('limSMS')
				);
			}
		}
	else if ($ATMdb->Get_field('choixApplication')=='all'){
		foreach ($TUser as $idUser) {
			modifierLimites($TLimites, $idUser
				, $ATMdb->Get_field('dureeHInt')
				, $ATMdb->Get_field('dureeMInt')
				, $ATMdb->Get_field('dureeHExt')
				, $ATMdb->Get_field('dureeMExt')
				, $ATMdb->Get_field('limSMS')
				);
			}
		}
	}


function modifierLimites(&$TLimites, $fk_user, $Hint, $Mint, $Hext, $Mext, $limSMS){
	if ($TLimites[$fk_user]['limInterne'] > (intval($Hint)*3600+intval($Mint)*60) ){
		$TLimites[$fk_user]['limInterne'] = intval($Hint)*3600+intval($Mint)*60;
	}
	if ($TLimites[$fk_user]['limExterne'] > (intval($Hext)*3600+intval($Mext)*60) ){
		$TLimites[$fk_user]['limExterne'] = intval($Hext)*3600+intval($Mext)*60;
	}
	if ($TLimites[$fk_user]['limSMS'] > intval($limSMS) ){
		$TLimites[$fk_user]['limSMS'] = intval($limSMS);
	}
	return;
}



//----------------DEBUT DU TRAITEMENT DES LIGNES D'APPELS----------------------------------------------------------
$nomFichier = "listeAppel.csv";
echo 'Traitement du fichier '.$nomFichier.' : <br><br>';



//début du parsing
$numLigne = 0;
if (($handle = fopen($nomFichier, "r")) !== FALSE) {
	while(($data = fgetcsv($handle)) != false){
		echo 'Traitement de la ligne '.$numLigne.'... ';
		if ($numLigne >=3){
			$infos = explode(';', $data[0]);
			
			/*
			 * Sauvegarde de l'événement
			$temp = new TRH_Evenement;
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
			$temp->coutHT = (float)$infos[12];
			echo ' : Ajoutee.';
			$temp->save($ATMdb);		
			//*/
			
			//echo $TUser[];
			//echo array_key_exists ( strtolower($infos[2]) , $TUser );
			if (! array_key_exists ( strtolower($infos[2]) , $TUser )){
				echo 'Erreur : Utilisateur '.strtolower($infos[2]).' inexistant ';
				break;
			}
			
			$id = $TUser[strtolower($infos[2])];
			
			//on cherche premierement le type d'appel
			
			//echo $infos[11].' :   ';
			if (strpos(strtolower($infos[11]),'connexion') !== FALSE){
				echo 'connexion';
				$TCompteurs[$id]['conso3G'] += floatval($infos[10]);
			}
			else if (strpos(strtolower($infos[11]),'appel') !== FALSE){
				echo 'appels';
				if (strpos(strtolower($infos[11]),'interne') !== FALSE){
					echo ' internes ';
					$h = intval(substr($infos[10], 0,2));
					$m = intval(substr($infos[10], 3,2));
					$s = intval(substr($infos[10], 6,2));
					echo $h.':'.$m.':'.$s;					
					$TCompteurs[$id]['consoInterne'] += $s+60*$m+3600*$h;
				}
				else{
					echo ' externes ';
					$h = intval(substr($infos[10], 0,2));
					$m = intval(substr($infos[10], 3,2));
					$s = intval(substr($infos[10], 6,2));
					echo $h.':'.$m.':'.$s;					
					$TCompteurs[$id]['consoExterne'] += $s+60*$m+3600*$h;
				}
			}
			else if (strpos(strtolower($infos[11]),'sms') !== FALSE){
				echo 'sms';
				$TCompteurs[$id]['consoSMS'] += 1;
				
			}
			else {
				echo '                      PAS TRAITEE : '.$infos[11];
			}
		}
		
		echo '<br>';
		$numLigne++;
	}
	echo 'Fin du traitement. '.($numLigne).' lignes rajoutés à la table.<br><br>';	
}

foreach ($TUser as $key => $value) {
	echo $key.' : ';	
	print_r($TLimites[$value]);
	echo '<br>';
}
echo '<br><br>';
foreach ($TUser as $key => $value) {
	echo $key.' : ';	
	print_r($TCompteurs[$value]);
	echo '<br>';
}

//------------FIN DU TRAITEMENT DES LIGNES----------------------------------------------------------


foreach ($TUser as $nom => $id) {
	echo $nom.' : '.'<br>';
	if ($TCompteurs[$id]['consoInterne'] <= $TLimites[$id]['limInterne']){
		echo 'Frais internes : 0<br><dd>';
	}
	else{
		echo 'Frais internes : '.intval(($TCompteurs[$id]['consoInterne'] - $TLimites[$id]['limInterne'])/60)*0.09.'<br>';	
	}
	if ($TCompteurs[$id]['consoInterne'] <= $TLimites[$id]['limInterne']){
		echo 'Frais externes : 0<br>';
	}
	else{
		echo 'Frais externes : '.intval(($TCompteurs[$id]['consoExterne'] - $TLimites[$id]['limExterne'])/60)*0.09.'<br>';	
	}
	if ($TCompteurs[$id]['consoSMS'] <= $TLimites[$id]['limSMS']){
		echo 'Frais SMS : 0<br>';
	}
	else{
		echo 'Frais SMS : '.intval(($TCompteurs[$id]['consoSMS'] - $TLimites[$id]['limSMS'])).'<br>';	
	}
	
	echo '<br><br>';
}





