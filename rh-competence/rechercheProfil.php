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
			case 'add':
			case 'newlignecv':
				//$ATMdb->db->debug=true;
			
				break;
			
				
			case 'view':
				
				break;
			case 'edit':
				
				break;
				
			
		}
	}
	elseif(($_REQUEST['libelleCompetence'])!="") {
		$TCompetence=$tagCompetence->findProfile($ATMdb, $_REQUEST['libelleCompetence']);
		print_r($TCompetence);
		
	}
	else {
		//$ATMdb->db->debug=true;
		echo "rien à chercher";
		_fiche($ATMdb,$tagCompetence, 'edit');
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $lignecv, $formation ) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos expériences');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'competence', $langs->trans('Utilisateur'),0, 'user');
	
	////////////AFFICHAGE DES LIGNES DE CV 
	$r = new TSSRenderControler($lignecv);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			  date_debut, date_fin, libelleExperience, descriptionExperience,lieuExperience, fk_user, '' as 'Supprimer'
		FROM   llx_rh_ligne_cv
		WHERE fk_user=".$user->id." AND entity=".$conf->entity;

	$TOrder = array('ID'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelleExperience'=>'<a href="?id=@ID@&action=viewCV">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=deleteCV&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre', 'fk_user')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'VISUALISATION DE VOTRE CV'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune expérience professionnelle"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date Fin'
			,'libelleExperience'=>'Libellé Expérience'
			,'descriptionExperience'=>'Description Expérience'
			,'lieuExperience'=>'Lieu'
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));

		?>
		<a class="butAction" href="?id=<?=$lignecv->getId()?>&action=newlignecv&fk_user=<?=$fuser->id?>">Ajouter une expérience</a><div style="clear:both"></div>
		<br/><br/><br/><br/><br/>
		<?
	$form->end();
	
	
	llxFooter();
}	

	
function _fiche(&$ATMdb,$tagCompetence, $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Recherche Profil');

	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $formation->fk_user);
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

