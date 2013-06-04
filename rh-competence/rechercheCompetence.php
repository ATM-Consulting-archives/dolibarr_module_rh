<?php
	require('config.php');
	require('./class/competence.class.php');
		require('./lib/competence.lib.php');
	
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
		$recherche=$tagCompetence->replaceEspaceEnPourcentage($_REQUEST['libelleCompetence']);
		//print($recherche);print "<br/>";
		$competenceOu=$tagCompetence->separerOu($recherche);
		//print_r($competenceOu);print "<br/>";

		_liste($ATMdb,  $tagCompetence, $competenceOu);
	}
	else {
		//$ATMdb->db->debug=true;
		_fiche($ATMdb,$tagCompetence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb,  $tagCompetence, $recherche ) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Résultat de la recherche');
	print dol_get_fiche_head(competencePrepareHead($tagCompetence, '')  , '', 'Compétences');
	
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

	
function _fiche(&$ATMdb,$tagCompetence, $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Recherche Profil');
	print dol_get_fiche_head(competencePrepareHead($tagCompetence, '')  , '', 'Compétences');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/rechercheCompetence.tpl.php'
		,array(
			
		)
		,array(
			'recherche'=>array(
				'libelle'=>$form->texte('','libelleCompetence','', 30,100,'','','-')
				,'titreRecherche'=>load_fiche_titre("Recherche d'un profil",'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$user->id
				,'droitRecherche'=>$user->rights->curriculumvitae->myactions->rechercheProfil
				
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



function _ficheFormation(&$ATMdb, $formation, $tagCompetence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');
	print dol_get_fiche_head(competencePrepareHead($tagCompetence, '')  , '', 'Compétences');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $formation->getId());
	echo $form->hidden('action', 'saveformation');
	echo $form->hidden('fk_user', $user->id);
	echo $form->hidden('entity', $conf->entity);

	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $formation->fk_user);
	$fuser->getrights();
	
	$sql="SELECT c.rowid, c.libelleCompetence, c.niveauCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."rh_formation_cv as f 
	WHERE c.fk_user_formation=".$formation->getID(). " AND c.fk_user_formation=f.rowid AND c.fk_user=".$formation->fk_user." AND c.entity=".$conf->entity;

	$k=0;
	$ATMdb->Execute($sql);
	$TTagCompetence=array();
	while($ATMdb->Get_line()) {
			$TTagCompetence[]=array(
				'id'=>$ATMdb->Get_field('rowid')
				,'libelleCompetence'=>$form->texte('','libelleCompetence',$ATMdb->Get_field('libelleCompetence'), 30,100,'','','-')
				,'niveauCompetence'=>$form->texte('','niveauCompetence',$ATMdb->Get_field('niveauCompetence'), 10,50,'','','-')
				
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
				,'date_debut'=>$form->calendrier('', 'date_debut', $formation->date_debut, 12)
				,'date_fin'=>$form->calendrier('', 'date_fin', $formation->date_fin, 12)
				,'libelleFormation'=>$form->texte('','libelleFormation',$formation->libelleFormation, 30,100,'','','-')
				,'commentaireFormation'=>$form->texte('','commentaireFormation',$formation->commentaireFormation, 50,300,'style="width:400px;height:80px;"','','-')
				,'lieuFormation'=>$form->texte('','lieuFormation',$formation->lieuFormation, 30,100,'','','-')
				,'date_formationEcheance'=>$form->calendrier('', 'date_formationEcheance', $formation->date_formationEcheance, 12)
				,'titreResultat'=>load_fiche_titre("Description de la formation correspondant à la compétence recherchée",'', 'title.png', 0, '')
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
			,'newCompetence'=>array(
				'hidden'=>$form->hidden('action', 'newCompetence')
				,'id'=>$k
				,'libelleCompetence'=>$form->texte('','TNComp[libelle]','', 30,100,'','','-')
				,'fk_user_formation'=>$form->hidden('TNComp[fk_user_formation]', $formation->getId())
				,'niveauCompetence'=>$form->combo(' Niveau ','niveauCompetence',$tagCompetence->TNiveauCompetence,'')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
	