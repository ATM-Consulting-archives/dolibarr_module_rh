<?php

	require('../config.php');
	require_once(DOL_DOCUMENT_ROOT."/user/class/user.class.php");
	require_once(DOL_DOCUMENT_ROOT."/user/class/usergroup.class.php");
	dol_include_once("/valideur/class/valideur.class.php");
	
	global $langs;
	
	$ATMdb=new TPDOdb;
	
	$ATMdb->Execute("SELECT DISTINCT u.rowid FROM
llx_user u LEFT OUTER JOIN llx_rh_valideur_groupe uvg ON (uvg.`fk_user`=u.rowid AND `type`='Conges')
WHERE u.rowid IN (SELECT fk_user FROM llx_user) AND uvg.rowid IS NULL");

	$TUser = $ATMdb->Get_All();
	foreach($TUser as $row) {
		
		$fk_user = $row->rowid;
		
		$fuser=new User($db);
		$fuser->fetch($fk_user); 
		
		$groupname =  $langs->trans('ValidationGroupOf', $fuser->login);
		
		$group = new UserGroup($db);
		if($group->fetch('',$groupname)) {
			$idGroup=(int)$group->id;
		}
		else {
			exit($langs->trans('UnableToLoadTheGroup'));
		}
		
		print $langs->trans('AddValidationRightOfSmbdInGroup', $fuser->login, $idGroup, $groupname) . '<br>';
		
		$v=new TRH_valideur_groupe;
		$v->type = 'Conges';
		$v->fk_user = $fk_user;
		$v->entity = $conf->entity;
		$v->level = 1;
		
		$v->fk_usergroup = $idGroup;
		$v->save($ATMdb);		
		/*	parent::add_champs('type','type=chaine;');				//type de valideur
		parent::add_champs('nbjours','type=entier;');			//nbjours avant alerte
		parent::add_champs('montant','type=float;');			//montant avant alerte
		parent::add_champs('fk_user,fk_usergroup,entity,validate_himself,pointeur,level','type=entier;index;');	//utilisateur ou groupe concerné
		*/
	}
