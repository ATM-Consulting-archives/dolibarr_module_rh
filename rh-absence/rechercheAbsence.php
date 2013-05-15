<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$absence=new TRH_Absence;

	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			
			case 'view':
				
				break;
			case 'edit':
				
				break;
		}
	}
	else if(isset($_REQUEST['valider'])){
		_ficheResult($ATMdb,$absence, 'edit');
	}
	else{
		_fiche($ATMdb,$absence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	

function _fiche(&$ATMdb, $absence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');
	
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	
	$idTagRecherche=isset($_REQUEST['libelle']) ? $_REQUEST['libelle'] : 0;
	$idGroupeRecherche=isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	$idUserRecherche=isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;

	
	//tableau pour la combobox des groupes
	$TGroupe  = array();
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
	
	//tableau pour la combobox des utilisateurs
	$TUser=array();
	$TUser[0]='Tous';
	$sqlReqUser="SELECT u.rowid, u.name,  u.firstname FROM `".MAIN_DB_PREFIX."user` as u, ".MAIN_DB_PREFIX."usergroup_user as g
	 WHERE u.entity=".$conf->entity;
	if($idGroupeRecherche!=0){
		$sqlReqUser.=" AND g.fk_user=u.rowid AND g.fk_usergroup=".$idGroupeRecherche;
	}
	$ATMdb->Execute($sqlReqUser);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')]=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
	}
		
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/rechercheAbsence.tpl.php'
		,array(
			
		)
		,array(
			'recherche'=>array(
				'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'TUser'=>$form->combo('','user',$TUser,$idUserRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->get_date('date_fin'), 10)
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->curriculumvitae->myactions->rechercheProfil?1:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


function _ficheResult(&$ATMdb, $tagCompetence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');
	
	print dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	
	$idGroupeRecherche=isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	$idUserRecherche=isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;
	
	if($idGroupeRecherche!=0){	//on recherche le nom du groupe
		//echo $idGroupeRecherche;exit;
		$sql="SELECT nom FROM ".MAIN_DB_PREFIX."usergroup
		WHERE rowid =".$idGroupeRecherche." AND entity=".$conf->entity;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomGroupeRecherche=$ATMdb->Get_field('nom');
		}
	}else{
		$nomGroupeRecherche='Tous';
	}

	
	if($idUserRecherche!=0){	//on recherche le nom de l'utilisateur
		$sql="SELECT name,  firstname FROM ".MAIN_DB_PREFIX."user
		WHERE rowid =".$idUserRecherche." AND entity=".$conf->entity;
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomUserRecherche=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	}else{
		$nomUserRecherche='Tous';
	}
	
	//on va obtenir un tableau des absences des collaborateurs pour la recherche
	$requeteRecherche=$tagCompetence->requeteStatistique($ATMdb, $idGroupeRecherche, $idTagRecherche, $idUserRecherche);


	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/statCompetenceResult.tpl.php'
		,array(
		)
		,array(
			'demande'=>array(
				'idGroupeRecherche'=>$idGroupeRecherche
				,'idUserRecherche'=>$idUserRecherche
				,'nomGroupeRecherche'=>$nomGroupeRecherche
				,'nomUserRecherche'=>$nomUserRecherche
			)
			,'resultat'=>array(
				
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminRecherchePrepareHead($absence, '')  , '', 'Recherche')
			)
		)	
	);

	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

