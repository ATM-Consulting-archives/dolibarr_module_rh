<?php

	require '../../config.php';
	dol_include_once('/ressource/class/ressource.class.php');
	dol_include_once('/ressource/class/evenement.class.php');
	
	$nomFichier = "affectation_sim_telephones_3.csv";
	
	$ATMdb = new TPDOdb;
	
	if (($handle = fopen($nomFichier, "r")) !== false) {
		
		$osef = fgetcsv($handle, "", ";");

		while(($data = fgetcsv($handle, "", ";")) != false){
			
			$id_user = _get_user($data);
			
			if($id_user > 0) {
				
				$id_tel = _add_tel($data, $ATMdb);
				_add_carte_sim($data, $ATMdb, $id_tel);
				_add_affectation_user_tel($ATMdb, $id_tel, $id_user, $data);
				
			}
			
		}

	}
	
	function _get_user($data) {
		
		global $db;
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.'user';
		$sql.= ' WHERE name = "'.$data[9].'"';
		$sql.= ' AND firstname = "'.$data[10].'"';
		$resql = $db->query($sql);
		
		if($resql) {
			$res = $db->fetch_object($resql);
				$u = new User($db);
				$u->fetch($res->rowid);
				return $u->id;
		}
		else if(isset($_REQUEST['DEBUG'])){
			return 1;
		}
		
		return false;
		
	}

	function _add_carte_sim($data, &$ATMdb, $id_tel) {
		
		$sim = new TRH_Ressource;
		$sim->fk_rh_ressource_type = 5; // SIM
		$sim->load_ressource_type($ATMdb);
		$sim->numId = "33".$data[3];
		$sim->libelle = "Carte SIM 33".$data[3];
		$sim->numerotel = "33".$data[3];
		$sim->codepuk = $data[7];
		$sim->numerosim = $data[5];
		$sim->forfait = $data[18];
		$sim->fk_rh_ressource = $id_tel;
		$sim->save($ATMdb);
		
	}

	function _add_tel($data, &$ATMdb) {
		
		$phone = new TRH_Ressource;
		$phone->fk_rh_ressource_type = 4; // Tél
		$phone->load_ressource_type($ATMdb);
		$phone->libelle = $data[11];
		$phone->imei = $data[6];
		$phone->numerotel = "33".$data[3];
		$phone->save($ATMdb);
		
		return $phone->rowid;
		
	}
	
	function _add_affectation_user_tel(&$ATMdb, $id_tel, $id_user, $data) {
		
		$emprunt = new TRH_Evenement;
		$emprunt->type = "emprunt";
		$emprunt->fk_rh_ressource = $id_tel;
		$emprunt->fk_user = $id_user;
		//echo implode("-", array_reverse(explode("/", $data[14])));exit;
		$emprunt->date_debut = strtotime(implode("/", array_reverse(explode("/", $data[14]))));
		$emprunt->date_fin = strtotime(implode("/", array_reverse(explode("/", $data[15]))));
		
		$emprunt->save($ATMdb);
		
	}
