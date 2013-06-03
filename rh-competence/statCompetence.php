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
	else if(isset($_REQUEST['valider'])){
		_ficheResult($ATMdb,$tagCompetence, 'edit');
	}
	else{
		_fiche($ATMdb,$tagCompetence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	

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
	WHERE c.entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sql);
	$TTagCompetence=array();
	while($ATMdb->Get_line()) {
		$TTagCompetence[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('libelleCompetence');
	}
	
	
	//tableau pour la combobox des groupes
	$TGroupe  = array();
	$TGroupe[0]='Tous';
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
	

	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/statCompetence.tpl.php'
		,array(
			
		)
		,array(
			'competence'=>array(
				'Tlibelle'=>$form->combo('','libelle',$TTagCompetence,$idTagRecherche)
				,'TGroupe'=>$form->combo('','groupe',$TGroupe,$idGroupeRecherche)
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'titreRecherche'=>load_fiche_titre("Statistiques sur les compétences des collaborateurs de l'entreprise",'', 'title.png', 0, '')
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


function _ficheResult(&$ATMdb, $tagCompetence,  $mode) {
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
	
	if($idGroupeRecherche!=0){	//on recherche le nom du groupe
		//echo $idGroupeRecherche;exit;
		$sql="SELECT nom FROM ".MAIN_DB_PREFIX."usergroup
		WHERE rowid =".$idGroupeRecherche." AND entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomGroupeRecherche=$ATMdb->Get_field('nom');
		}
	}else{
		$nomGroupeRecherche='Tous';
	}

	
	if($idTagRecherche!=0){	//on recherche le nom du tag
		$sql="SELECT libelleCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv
		WHERE rowid =".$idTagRecherche." AND entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomTagRecherche=$ATMdb->Get_field('libelleCompetence');
		}
	}else{
		$nomTagRecherche='Tous';
	}

	
	if($idUserRecherche!=0){	//on recherche le nom de l'utilisateur
		$sql="SELECT name,  firstname FROM ".MAIN_DB_PREFIX."user
		WHERE rowid =".$idUserRecherche." AND entity IN (0,".$conf->entity.")";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$nomUserRecherche=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	}else{
		$nomUserRecherche='Tous';
	}
	
	//on va obtenir un tableau permettant d'avoir les stats des compétences suivant la recherche
	$requeteRecherche=$tagCompetence->requeteStatistique($ATMdb, $idGroupeRecherche, $idTagRecherche, $idUserRecherche);

	$taux_resultat_faible=$requeteRecherche['nbUserFaible']*100/$requeteRecherche['nbUser'];
	$taux_resultat_moyen=$requeteRecherche['nbUserMoyen']*100/$requeteRecherche['nbUser'];
	$taux_resultat_bon=$requeteRecherche['nbUserBon']*100/$requeteRecherche['nbUser'];
	$taux_resultat_excellent=$requeteRecherche['nbUserExcellent']*100/$requeteRecherche['nbUser'];
	$taux_resultat_autres=100-$taux_resultat_faible-$taux_resultat_moyen-$taux_resultat_bon-$taux_resultat_excellent;
	
	$nb_resultat_autres=$requeteRecherche['nbUser']-$requeteRecherche['nbUserFaible']-$requeteRecherche['nbUserMoyen']-$requeteRecherche['nbUserBon']-$requeteRecherche['nbUserExcellent'];

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/statCompetenceResult.tpl.php'
		,array(
		)
		,array(
			'demande'=>array(
				'idTagRecherche'=>$idTagRecherche
				,'idGroupeRecherche'=>$idGroupeRecherche
				,'idUserRecherche'=>$idUserRecherche
				,'nomTagRecherche'=>$nomTagRecherche
				,'nomGroupeRecherche'=>$nomGroupeRecherche
				,'nomUserRecherche'=>$nomUserRecherche
			)
			,'resultat'=>array(
				'total'=>$requeteRecherche['nbUser']
				,'faible'=>$taux_resultat_faible
				,'moyen'=>$taux_resultat_moyen
				,'bon'=>$taux_resultat_bon
				,'excellent'=>$taux_resultat_excellent
				,'autres'=>$taux_resultat_autres
				,'nb_faible'=>$requeteRecherche['nbUserFaible']
				,'nb_moyen'=>$requeteRecherche['nbUserMoyen']
				,'nb_bon'=>$requeteRecherche['nbUserBon']
				,'nb_excellent'=>$requeteRecherche['nbUserExcellent']
				,'nb_autres'=>$nb_resultat_autres
				,'titreRecherche'=>load_fiche_titre("Résultat de votre recherche",'', 'title.png', 0, '')
				
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
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

