<?php

	require('../config.php');
	
	
	$ATMdb=new TPDOdb;
	
	$ATMdb->Execute("SELECT DISTINCT u.rowid FROM
llx_user u LEFT OUTER JOIN llx_rh_valideur_groupe uvg ON (uvg.`fk_user`=u.rowid AND `type`='Conges')
WHERE u.rowid IN (SELECT fk_user FROM llx_user) AND uvg.rowid IS NULL");

	$TUser = $ATMdb->Get_All();
	foreach($TUser as $row) {
		
		$fk_user = $row['rowid'];
		
		$fuser=new User($db);
		$fuser->fetch($fk_user); 
		
		$groupname =  'Groupe de validation congés (hiérarchique) de '.$fuser->login;
		
		$group = new UserGroup($db);
		if($group->fetch('',$groupname)) {
			$idGroup=$group->id;
		}
		
		print "Ajout du droit validatio congé de  ".$fuser->login." dans ($idGroup)".$groupname."<br>";
		
		$v=new TRH_valideur_groupe;
		$v->type = 'Conges';
		$v->fk_user = $fk_user;
		$v->entity = $conf->entity;
		$v->level = 1;
		
		$v->fk_usergroup = $idGroup;
		
		/*	parent::add_champs('type','type=chaine;');				//type de valideur
		parent::add_champs('nbjours','type=entier;');			//nbjours avant alerte
		parent::add_champs('montant','type=float;');			//montant avant alerte
		parent::add_champs('fk_user,fk_usergroup,entity,validate_himself,pointeur,level','type=entier;index;');	//utilisateur ou groupe concerné
		*/
	}
