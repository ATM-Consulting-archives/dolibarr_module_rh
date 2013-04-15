<?php
	require('../config.php');
	require('../class/valideur.class.php');

	$f1 = fopen('ListeValideur.csv','r');
	
	fgets($f1);
	
	$ATMdb=new TPDOdb;
	
	$TValideur = array();
	
	while($ligne = fgetcsv($f1)) {
		
		list($nom,$prenom,$code_ana,$valideur, $valideur2) = $ligne;
		$prenom = strtr($prenom,' ','-');
		
		
		list($prenomV, $nomV) = explode(' ', utf8_decode($valideur) );
		
		print "$nom,$prenom,$code_ana,$valideur, $valideur2<br>";
		
		$ATMdb->Execute("SELECT rowid FROM llx_user WHERE name='".addslashes($nom)."' AND firstname='".addslashes($prenom)."' ");
		if($ATMdb->Get_line()) {
			$id_user = $ATMdb->Get_field('rowid');
			print "User $id_user ...<br>";
		}
		else {
			exit("$nom,$prenom ERR");
		}
		$ATMdb->Execute("SELECT rowid FROM llx_user WHERE name='".addslashes($nomV)."' AND firstname='".addslashes($prenomV)."' ");
		if($ATMdb->Get_line()) {
			$id_val1 = $ATMdb->Get_field('rowid');
			print "Valideur1 $id_val1 ...<br>";
			$TValideur[$id_val1][$id_user]=true;
		}
		else {
			exit("$id_val1 ERR");
		}
		$ATMdb->Execute("SELECT rowid FROM llx_user WHERE name='".addslashes($valideur2)."' ");
		if($ATMdb->Get_line()) {
			$id_val2 = $ATMdb->Get_field('rowid');
			print "Valideur2 $id_val2 ...<br>";
			$TValideur[$id_val2][$id_user]=true;
		}
		/*else {
			exit("$id_val2 ERR2");
		}*/
		
		
		
		
	}

	print "Terminé";	
	
	require_once(DOL_DOCUMENT_ROOT."/user/class/user.class.php");	
	require_once(DOL_DOCUMENT_ROOT."/user/class/usergroup.class.php");
	
	foreach ($TValideur as $idvalideur=>$TUser) {
			$valideur=new User($db);
			$valideur->fetch($idvalideur);
			$groupName = "Groupe de validation de ".$valideur->firstname." ".$valideur->nom;
		
			print "Création/Récup du groupe : $groupName<br />";
			
			$group = new UserGroup($db);
			if($group->fetch('',$groupName)) {
				$idGroup=$group->id;
			}
	
			if($idGroup<1) {
				$group->nom =  $groupName;
				$group->entity = 0;
				$idGroup = $group->create();
			}
			
			$valideur->SetInGroup($idGroup, 0);
			
			$v=new TRH_valideur_groupe;
			$v->type='NDFP';
			$v->fk_user=$valideur->id;
			$v->fk_usergroup=$idGroup;
			$v->save($ATMdb);
			
			foreach($TUser as $iduser=>$dummy) {
				
				$fuser = new User($db);
				$fuser->fetch($iduser);
				
				print "Ajout de ".$fuser->firstname." ".$fuser->nom." au groupe <br>"; flush();
				
				$fuser->SetInGroup($idGroup, 0);
			}
		
	}
