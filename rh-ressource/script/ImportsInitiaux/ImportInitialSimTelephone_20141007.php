<?php

	require '../../config.php';
	dol_include_once('/ressource/class/ressource.class.php');
	dol_include_once('/ressource/class/evenement.class.php');
	
	$nomFichier = "affectation_sim_telephones_2.csv";
	
	$ATMdb = new TPDOdb;
	
	if (($handle = fopen($nomFichier, "r")) !== false) {
		
		$osef = fgetcsv($handle, "", ";");
		$osef = fgetcsv($handle, "", ";");
		$osef = fgetcsv($handle, "", ";");
		
		while(($data = fgetcsv($handle, "", ";")) != false){
			
			// Si le code compta est différent de "N/A" ou "?"
			$TDonnees[] = $data;
			
		}
		
	}
	
	$data = _get_formatted_array($TDonnees);
	var_dump($data);exit;
	function _get_formatted_array($data) {
		
		$TResult = array();
		
		foreach($data as $line) {
			
			if(!empty($line[5])) $TResult["33".$line[3]]['num_sim'] = $line[5];
			if(!empty($line[6])) $TResult["33".$line[3]]['imei'] = $line[6];
			if(!empty($line[7])) $TResult["33".$line[3]]['puk'] = $line[7];
			if(!empty($line[9])) $TResult["33".$line[3]]['TUser'] = explode(" ", $line[9]);
			if(!empty($line[20])) $TResult["33".$line[3]]['lib_phone'] = $line[20];
			
		}
		
		return $TResult;
		
	}
	
	function _get_user($data) {
		
		global $db;
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.'user';
		$sql.= ' WHERE email = "'.$data[2].'"';
		$resql = $db->query($sql);
		
		if($resql) {
			while ($res = $db->fetch_object($resql)) {
				$u = new User($db);
				$u->fetch($res->rowid);
				return $u->id;
			}
		}
		
		return false;
		
	}

	function _add_carte_sim($data, &$ATMdb, $id_tel) {
		
		$sim = new TRH_Ressource;
		$sim->fk_rh_ressource_type = 5; // SIM
		$sim->libelle = "Carte SIM 33".$data[1];
		$sim->numerotel = "33".$data[1];
		$sim->fk_rh_ressource = "33".$id_tel;
		$sim->save($ATMdb);
		
	}

	function _add_tel($data, &$ATMdb) {
		
		$sim = new TRH_Ressource;
		$sim->fk_rh_ressource_type = 4; // Tél
		$sim->libelle = "Téléphone 33".$data[1];
		$sim->numerotel = "33".$data[1];
		$sim->save($ATMdb);
		
		return $sim->rowid;
		
	}
	
	function _add_affectation_user_tel(&$ATMdb, $id_tel, $id_user) {
		
		$emprunt = new TRH_Evenement;
		$emprunt->type = "emprunt";
		$emprunt->fk_rh_ressource = $id_tel;
		$emprunt->fk_user = $id_user;
		
		$emprunt->save($ATMdb);
		
	}


	// Ajout à la main de ceux qui n'ont pas d'email
	
	//ALONSO FRANCOIS
	// Tél
	$sim = new TRH_Ressource;
	$sim->fk_rh_ressource_type = 4; // Tél
	$sim->libelle = "Téléphone 33".$data[1];
	$sim->numerotel = "33".$data[1];
	$sim->save($ATMdb);

	// SIM
	$sim = new TRH_Ressource;
	$sim->fk_rh_ressource_type = 5; // SIM
	$sim->libelle = "Carte SIM 33".$data[1];
	$sim->numerotel = "33".$data[1];
	$sim->fk_rh_ressource = "33".$id_tel;
	$sim->save($ATMdb);

	// Emprunt
	$emprunt = new TRH_Evenement;
	$emprunt->type = "emprunt";
	$emprunt->fk_rh_ressource = $id_tel;
	$emprunt->fk_user = 28511;
	$emprunt->save($ATMdb);
	
	
	
	
	//CLEMENT BRUN
	// Tél
	$sim = new TRH_Ressource;
	$sim->fk_rh_ressource_type = 4; // Tél
	$sim->libelle = "Téléphone 33".$data[1];
	$sim->numerotel = "33".$data[1];
	$sim->save($ATMdb);

	// SIM
	$sim = new TRH_Ressource;
	$sim->fk_rh_ressource_type = 5; // SIM
	$sim->libelle = "Carte SIM 33".$data[1];
	$sim->numerotel = "33".$data[1];
	$sim->fk_rh_ressource = "33".$id_tel;
	$sim->save($ATMdb);

	// Emprunt
	$emprunt = new TRH_Evenement;
	$emprunt->type = "emprunt";
	$emprunt->fk_rh_ressource = $id_tel;
	$emprunt->fk_user = $id_user;
	$emprunt->save($ATMdb);
	
	
	
	
	//BESSON QUENTIN
	// Tél
	$sim = new TRH_Ressource;
	$sim->fk_rh_ressource_type = 4; // Tél
	$sim->libelle = "Téléphone 33".$data[1];
	$sim->numerotel = "33".$data[1];
	$sim->save($ATMdb);

	// SIM
	$sim = new TRH_Ressource;
	$sim->fk_rh_ressource_type = 5; // SIM
	$sim->libelle = "Carte SIM 33".$data[1];
	$sim->numerotel = "33".$data[1];
	$sim->fk_rh_ressource = "33".$id_tel;
	$sim->save($ATMdb);

	// Emprunt
	$emprunt = new TRH_Evenement;
	$emprunt->type = "emprunt";
	$emprunt->fk_rh_ressource = $id_tel;
	$emprunt->fk_user = $id_user;
	$emprunt->save($ATMdb);