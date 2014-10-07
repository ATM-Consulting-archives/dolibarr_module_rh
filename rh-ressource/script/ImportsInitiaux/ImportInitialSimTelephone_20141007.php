<?php

	require '../../config.php';
	dol_include_once('/ressource/class/ressource.class.php');
	
	$nomFichier = "affectation_sim_telephones.csv";
	
	$ATMdb = new TPDOdb;
	
	if (($handle = fopen($nomFichier, "r")) !== false) {
		
		$osef = fgetcsv($handle, "", ";");
		$osef = fgetcsv($handle, "", ";");
		
		while(($data = fgetcsv($handle, "", ";")) != false){
			
			$u = _get_user($data);
			//echo $u->lastname." ".$u->firstname."<br />";
			$id_tel = _add_tel($data, $ATMdb);
			_add_carte_sim($data, $ATMdb, $id_tel);
			
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
				return $u;
			}
		}
		
		return false;
		
	}

	function _add_carte_sim($data, &$ATMdb, $id_tel) {
		
		$sim = new TRH_Ressource;
		$sim->fk_rh_ressource_type = 5; // SIM
		$sim->libelle = "Carte SIM ".$data[1];
		$sim->numerotel = $data[1];
		$sim->fk_rh_ressource = $id_tel;
		$sim->save($ATMdb);
		
	}

	function _add_tel($data, &$ATMdb) {
		
		$sim = new TRH_Ressource;
		$sim->fk_rh_ressource_type = 4; // Tél
		$sim->libelle = "Téléphone ".$data[1];
		$sim->numerotel = $data[1];
		$sim->save($ATMdb);
		
		return $sim->rowid;
		
	}
	
	function _add_affectation_user_tel($id_tel) {
		
		$emprunt = new TRH_Evenement;
		
	}
