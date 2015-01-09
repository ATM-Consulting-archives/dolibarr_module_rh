<?php
	
	set_time_limit(0);
	ini_set('memory_limit','512M');
	
	
	require('../../config.php');
	require('../../class/contrat.class.php');
	require('../../class/evenement.class.php');
	require('../../class/ressource.class.php');
	require('../../lib/ressource.lib.php');
	
	$reel = __get('reel','N');

	$ATMdb=new TPDOdb;

	$f1=fopen('ticket1686.csv','r');
	fgets($f1);
	fgets($f1);
	fgets($f1);
	
	while($data = fgetcsv($f1,1024,';','"')) {
		
		$r=new TRH_Ressource;
		$ref = '33'.trim($data[4]);
		if($r->load_by_numId($ATMdb, $ref)!==false) {
			print "$ref...";
			$r->libelle = $data[5];
			$r->set_date('date_achat', $data[6]);
			$r->set_date('date_vente', $data[7]);
			$r->fk_proprietaire = 2;
			$r->fk_loueur = 3;
			$r->fk_entity_utilisatrice = _getIdEntity($data[10]);

			//$r->fk_utilisatrice = _getIdGroupe($ATMdb, $data[11] );
			$r->numerotel = $data[12];
			$r->codepuk = $data[13];
			$r->forfait = $data[14];
//var_dump($r);exit;
			if($reel=='Y')$r->save($ATMdb);

			print "ok<br/>";
		}
		else {
			print $ref." non trouvé <br/>";
			
		}
		
		$ressourceTel = $r->fk_rh_ressource;

		$r=new TRH_Ressource;
		$ref = trim($data[16]);
		if($r->load_by_numId($ATMdb, $ref)!==false) {
			print "$ref...";
			$r->libelle = $data[17];
			$r->set_date('date_achat', $data[18]);
			$r->set_date('date_vente', $data[19]);
			$r->fk_proprietaire = 2;
			$r->fk_loueur = 3;
			$r->fk_entity_utilisatrice = _getIdEntity($data[21]);
//			$r->fk_utilisatrice = _getIdGroupe($ATMdb, $data[22] );
			$r->marquetel = $data[22];
			$r->modeletel = $data[23];
			$r->financement = $data[24];
//var_dump($r);exit;
			if($reel=='Y')$r->save($ATMdb);

			print "ok<br/>";
		}
		else {
			print $ref." non trouvé <br/>";
			
			$r0=new TRH_Ressource;
			$r0->load($ATMdb, $ressourceTel);
			print "Ancienne ressource ".$r0->numId."<br />";
			
			
		}
		
		
	}
function _getIdGroupe(&$ATMdb, $groupe) {
	
	if($groupe=='CPRO TELECOM')$groupe='AGT';
	
	$ATMdb->Execute("SELECT rowid FROM `llx_usergroup`
WHERE `nom` LIKE '".addslashes($groupe)."' LIMIT 1");
	if($ATMdb->Get_line()) {
		return $ATMdb->Get_field('rowid');
	}
	else{
		print('erreur agence '.$groupe.' non trouvée');
		return 0;
	}
	
}	
	
function _getIdEntity($company) {
	$company=strtolower($company);
				
		if(strpos($company,'informatique')!==false) {
			$ldap_entity_login = 3;
		}
		else if(strpos($company,'groupe')!==false) {
			$ldap_entity_login = 1;
		}
		else if(strpos($company,'global')!==false) {
			$ldap_entity_login = 5;
		}
		else if(strpos($company,'agt')!==false || strpos($company,'telecom')!==false) {
			$ldap_entity_login = 4;
		}
		else {
			$ldap_entity_login = 2; 
		}
	
	return $ldap_entity_login;
}
