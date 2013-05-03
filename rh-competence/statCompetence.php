<?php
	require('config.php');
	require('./class/competence.class.php');
		require('./lib/competence.lib.php');
	
	$langs->load('competence@competence');
	$langs->load("users");
	
	$ATMdb=new Tdb;
	$lignecv=new TRH_ligne_cv;
	$tagCompetence=new TRH_competence_cv;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			
			case 'view':
				
				break;
			case 'edit':
				
				break;
		}
	}
	else{
		_fiche($ATMdb,$tagCompetence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	/*
function _liste(&$ATMdb,  $tagCompetence, $recherche ) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Résultat de la recherche');
	print dol_get_fiche_head(competencePrepareHead($tagCompetence, '')  , '', 'Statistiques');
	
	////////////AFFICHAGE DES LIGNES DE CV 
	$r = new TSSRenderControler($tagCompetence);
	
	$sql=$tagCompetence->requeteRecherche($ATMdb, $recherche);

	$TOrder = array('Niveau'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'name'=>'<a href=./experience.php?fk_user=@fkuser@>@val@</a>'
			,'firstname'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'libelleCompetence'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre', 'fk_user', 'rowid', 'fkuser')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'RESULTAT DE VOTRE RECHERCHE'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucun résultat au sein de l'entreprise pour votre recherche "
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'libelleCompetence'=>'Libellé Compétence'
			,'name'=>'Utilisateur'
			//,'firstname'=>'Prénom'
			
		)
		,'search'=>array(
			'libelleExperience'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));

	
	$form->end();
	
	
	llxFooter();
}	
*/


function _fiche(&$ATMdb, $tagCompetence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');
	
	print dol_get_fiche_head(competencePrepareHead($tagCompetence, '')  , '', 'Statistiques');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $user->id);
	$fuser->getrights();
	
	$idTagRecherche=isset($_REQUEST['libelle']) ? $_REQUEST['libelle'] : 0;
	$idGroupeRecherche=isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	$idUserRecherche=isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;

	//tableau pour la combobox des tags de compétences
	$sql="SELECT c.rowid, c.libelleCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv as c
	WHERE c.entity=".$conf->entity;
	$ATMdb->Execute($sql);
	$TTagCompetence=array();
	$TTagCompetence[0]='Tous';
	while($ATMdb->Get_line()) {
		$TTagCompetence[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('libelleCompetence');
	}
	
	
	//tableau pour la combobox des groupes
	$TGroupe  = array();
	$TGroupe[0]='Tous';
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
	print $TBS->render('./tpl/statCompetence.tpl.php'
		,array(
			
		)
		,array(
			'competence'=>array(
				'Tlibelle'=>$form->combo('','libelle',$TTagCompetence,$idTagRecherche)
				,'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'TUser'=>$form->combo('','user',$TUser,$idUserRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
				,'droitRecherche'=>$user->rights->curriculumvitae->myactions->rechercheProfil?1:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(competencePrepareHead($tagCompetence, '')  , '', 'Compétences')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

