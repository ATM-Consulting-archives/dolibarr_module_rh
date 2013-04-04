<?php
	require('config.php');
	require('./class/competence.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('competence@competence');
	$langs->load("users");
	
	$ATMdb=new Tdb;
	$lignecv=new TRH_ligne_cv;
	$formation=new TRH_formation_cv;
	$tagCompetence=new TRH_competence_cv;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			
			case 'view':
				$formation->load($ATMdb, $_REQUEST['id']);
				_ficheFormation($ATMdb, $formation, $tagCompetence,'view');
				break;
			case 'edit':
				
				break;
		}
	}
	elseif(($_REQUEST['libelleCompetence'])!="") {
		$recherche=$tagCompetence->miseEnForme($_REQUEST['libelleCompetence']);
		$TCompetence=$tagCompetence->findProfile($ATMdb, $recherche);
		//print_r($TCompetence);
		_liste($ATMdb, $TCompetence, $tagCompetence, $recherche);
	}
	else {
		//$ATMdb->db->debug=true;
		_fiche($ATMdb,$tagCompetence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $TComp, $tagCompetence, $recherche ) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Résultat de la recherche');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'recherche', $langs->trans('Utilisateur'),0, 'user');
	
	////////////AFFICHAGE DES LIGNES DE CV 
	$r = new TSSRenderControler($tagCompetence);
	$sql="SELECT c.fk_user_formation as 'ID' , c.rowid , c.date_cre as 'DateCre', 
			  u.firstname, u.name ,c.libelleCompetence
			 , c.fk_user
		FROM   llx_rh_competence_cv as c, llx_user as u 
		WHERE c.fk_user IN(".implode(',', $TComp).") 
		AND c.libelleCompetence LIKE '".$recherche."'
		AND c.entity=".$conf->entity. " AND c.fk_user=u.rowid";
	
	$TOrder = array('ID'=>'DESC');
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
			'name'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'firstname'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'libelleCompetence'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre', 'fk_user', 'rowid')
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
			,'name'=>'Nom'
			,'firstname'=>'Prénom'
			
		)
		,'search'=>array(
			'libelleExperience'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));

	
	$form->end();
	
	
	llxFooter();
}	

	
function _fiche(&$ATMdb,$tagCompetence, $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Recherche Profil');

	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $tagCompetence->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'recherche';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/rechercheProfil.tpl.php'
		,array(
			
		)
		,array(
			'recherche'=>array(
				'libelle'=>$form->texte('','libelleCompetence','', 30,100,'','','-')
			)
			,'userCourant'=>array(
				'id'=>$user->id
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}



function _ficheFormation(&$ATMdb, $formation, $tagCompetence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');

	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $formation->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'recherche';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $formation->getId());
	echo $form->hidden('action', 'saveformation');
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	$sql="SELECT c.rowid, c.libelleCompetence FROM llx_rh_competence_cv as c, llx_rh_formation_cv as f 
	WHERE c.fk_user_formation=".$formation->getID(). " AND c.fk_user_formation=f.rowid AND c.fk_user=".$fuser->id;

	$k=0;
	$ATMdb->Execute($sql);
	$TTagCompetence=array();
	while($ATMdb->Get_line()) {
			$TTagCompetence[]=array(
				'id'=>$ATMdb->Get_field('rowid')
				,'libelleCompetence'=>$form->texte('','libelleCompetence',$ATMdb->Get_field('libelleCompetence'), 30,100,'','','-')
				
			);
		$k++;
	}
	
	$TNComp=array();
	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/resultat.tpl.php'
		,array(
			'TCompetence'=>$TTagCompetence
		)
		,array(
			'formation'=>array(
				'id'=>$formation->getId()
				,'date_debut'=>$form->calendrier('', 'date_debut', $formation->get_date('date_debut'), 10)
				,'date_fin'=>$form->calendrier('', 'date_fin', $formation->get_date('date_fin'), 10)
				,'libelleFormation'=>$form->texte('','libelleFormation',$formation->libelleFormation, 30,100,'','','-')
				,'commentaireFormation'=>$form->texte('','commentaireFormation',$formation->commentaireFormation, 50,300,'style="width:400px;height:80px;"','','-')
				,'lieuFormation'=>$form->texte('','lieuFormation',$formation->lieuFormation, 30,100,'','','-')
				,'date_formationEcheance'=>$form->calendrier('', 'date_formationEcheance', $formation->get_date('date_formationEcheance'), 10)
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
			)
			,'view'=>array(
				'mode'=>$mode
			)
			,'newCompetence'=>array(
				'hidden'=>$form->hidden('action', 'newCompetence')
				,'id'=>$k
				,'libelleCompetence'=>$form->texte('','TNComp[libelle]','', 30,100,'','','-')
				,'fk_user_formation'=>$form->hidden('TNComp[fk_user_formation]', $formation->getId())
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}