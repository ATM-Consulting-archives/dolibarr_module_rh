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
	$competence=new TRH_competence_cv;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'newlignecv':
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->set_values($_REQUEST);
				_ficheCV($ATMdb, $lignecv, 'edit');
				break;
			case 'newcompetencecv':
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$competence->set_values($_REQUEST);
				_ficheCompetence($ATMdb, $competence, 'edit');
				break;		
				
			case 'save':
				/*$emploiTemps->load($ATMdb, $_REQUEST['id']);
				$feries->load($ATMdb, $_REQUEST['id']);			
				$feries->razCheckbox($ATMdb, $absence);
				$feries->set_values($_REQUEST);
				$mesg = '<div class="ok">Jour non travaillé ajouté</div>';
				$mode = 'view';

				$feries->save($ATMdb);
				$feries->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $feries,$emploiTemps,$mode);*/
				break;
			
			case 'view':
				/*$feries->load($ATMdb, $_REQUEST['id']);
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $feries,$emploiTemps,'view');*/
				
				
				break;
			case 'delete':
				//$ATMdb->db->debug=true;
				/*$emploiTemps->load($ATMdb, $_REQUEST['id']);
				$feries->load($ATMdb, $_REQUEST['id']);
				$feries->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le jour a bien été supprimé</div>';
				$mode = 'edit';
				_liste($ATMdb, $feries , $emploiTemps);*/
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$lignecv->load($ATMdb, $_REQUEST['id']);
		$competence->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $lignecv, $competence);	
	}
	else {
		//$ATMdb->db->debug=true;
		$lignecv->load($ATMdb, $_REQUEST['id']);
		$competence->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $lignecv, $competence);
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $lignecv, $competence ) {
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
			  date_debut, date_fin, experience, fk_user, '' as 'Supprimer'
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
			'experience'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=delete"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de vos expériences professionnelles'
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
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));

		?>
		<a class="butAction" href="?id=<?=$lignecv->getId()?>&action=newlignecv">Ajouter une ligne au CV</a><div style="clear:both"></div>
		<?
	$form->end();
	
	
	////////////AFFICHAGE DES  FORMATIONS
	$r = new TSSRenderControler($competence);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			  date_debut, date_fin, competence, commentaire, fk_user, '' as 'Supprimer'
		FROM   llx_rh_competence_cv
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
			'competence'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=delete"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de vos formations'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune formation suivie"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date Fin'
			,'competence'=>'Compétences'
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));
	?>
		<a class="butAction" href="?id=<?=$competence->getId()?>&action=newcompetencecv">Ajouter une formation</a><div style="clear:both"></div>
	<?
	llxFooter();
}	

	
function _ficheCV(&$ATMdb, $lignecv,  $mode) {
	global $db,$user,$langs;
	llxHeader('','Lignes de CV');
	
	$fuser = new User($db);
	$fuser->fetch($user->id);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'competence';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $lignecv->getId());
	echo $form->hidden('action', 'save');

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/cv.tpl.php'
		,array(
		)
		,array(
			'cv'=>array(
				'id'=>$lignecv->getId()
				,'date_debut'=>$form->calendrier('', 'date_debut', $lignecv->get_date('date_debut'), 10)
				,'date_fin'=>$form->calendrier('', 'date_fin', $lignecv->get_date('date_fin'), 10)
				,'experience'=>$form->texte('','commentaire',$lignecv->exeperience, 30,100,'','','-')
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


	
	
function _ficheCompetence(&$ATMdb, $competence,  $mode) {
	global $db,$user, $langs;
	llxHeader('','Compétences');

	$fuser = new User($db);
	$fuser->fetch($user->id);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'competence';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $competence->getId());
	echo $form->hidden('action', 'save');

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/competence.tpl.php'
		,array(
		)
		,array(
			'competence'=>array(
				'id'=>$competence->getId()
				,'date_debut'=>$form->calendrier('', 'date_debut', $competence->get_date('date_debut'), 10)
				,'date_fin'=>$form->calendrier('', 'date_fin', $competence->get_date('date_fin'), 10)
				,'competence'=>$form->texte('','competence',$competence->competence, 30,100,'','','-')
				,'commentaire'=>$form->texte('','commentaire',$competence->commentaire, 30,100,'','','-')
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

