<?php

	require '../../config.php';
	dol_include_once('/ressource/class/ressource.class.php');
	dol_include_once('/ressource/class/evenement.class.php');
	
	$nomFichier = "affectation_sim_telephones.csv";
	
	$ATMdb = new TPDOdb;
	
	if (($handle = fopen($nomFichier, "r")) !== false) {
		
		$osef = fgetcsv($handle, "", ";");
		$osef = fgetcsv($handle, "", ";");
		
		while(($data = fgetcsv($handle, "", ";")) != false){
			
			// Si le code compta est différent de "N/A" ou "?"
			if($data[3] !== "N/A" && $data[3] !== "?") {
				
				$id_user = _get_user($data);
				if($id_user !== false) {
					
					$id_tel = _add_tel($data, $ATMdb);
					_add_carte_sim($data, $ATMdb, $id_tel);
					_add_affectation_user_tel($ATMdb, $id_tel, $id_user);
					
				}
				
			}
			
		}
		
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
