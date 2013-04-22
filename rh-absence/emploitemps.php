<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$emploiTemps=new TRH_EmploiTemps;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {

			case 'edit'	:
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $emploiTemps,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				
				$emploiTemps->razCheckbox($ATMdb, $emploiTemps);
				
				$emploiTemps->set_values($_REQUEST);
				
				$emploiTemps->save($ATMdb);
				
				$mesg = '<div class="ok">Demande enregistrée</div>';
				_fiche($ATMdb, $emploiTemps,'view');
				break;
			
			case 'view':
					$emploiTemps->loadByuser($ATMdb, $_REQUEST['fk_user']);
					_fiche($ATMdb, $emploiTemps,'view');
				break;

		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		$emploiTemps->load($ATMdb, $_REQUEST['fk_user']);
		_liste($ATMdb, $emploiTemps);
	}
	
	$ATMdb->close();
	llxFooter();
	
	
function _liste(&$ATMdb, &$emploiTemps) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	getStandartJS();
	print dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', 'Absence');
	
	$r = new TSSRenderControler($emploiTemps);
	$sql="SELECT e.rowid as 'ID', e.date_cre as 'DateCre', e.fk_user as 'Id Utilisateur', CONCAT(u.firstname,' ', u.name) as 'Emploi du temps de l\'utilisateur'
		FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as e, ".MAIN_DB_PREFIX."user as u
		WHERE e.entity=".$conf->entity." AND u.rowid=e.fk_user";

	if($user->rights->absence->myactions->modifierEdt!="1"){
		$sql.=" AND e.fk_user=".$user->id;
	}
	
	$TOrder = array('ID'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id=@ID@&action=view&fk_user='.$user->id.'">@val@</a>'
			,'Emploi du temps de l\'utilisateur'=>'<a href="?id=@ID@&action=view&fk_user='.$user->id.'"<a>Emploi Du Temps de @val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre','ID')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des emplois du temps des collaborateurs'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun emploi du temps à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
		)
		,'orderBy'=>$TOrder
	));
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$emploiTemps, $mode) {
	global $db,$user,$idUserCompt, $idComptEnCours;
	llxHeader('','Emploi du temps');
	$emploiTemps->load($ATMdb, $_REQUEST['id']);
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $_REQUEST['id']);
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $emploiTemps->fk_user);

	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$emploiTemps->fk_user;//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($db);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('name');
	}
	
	$TPlanning=array();
	foreach($emploiTemps->TJour as $jour) {
		foreach(array('am','pm') as $pm) {
			$TPlanning[$jour.$pm]=$form->checkbox1('',$jour.$pm,'1',$emploiTemps->{$jour.$pm}==1?true:false);	
		}
	}
	 
	$THoraire=array();
	foreach($emploiTemps->TJour as $jour) {
		foreach(array('dam','fam','dpm','fpm') as $pm) {
			$THoraire[$jour.'_heure'.$pm]=$form->texte('','date_'.$jour.'_heure'.$pm, date('H:i',$emploiTemps->{'date_'.$jour.'_heure'.$pm}) ,5,5);
		}
	} 
	
	$droitsEdt=0;
	if($user->rights->absence->myactions->modifierEdt&&!$user->rights->absence->myactions->modifierSonEdt){
		if($user->id!=$emploiTemps->fk_user){
			$droitsEdt=$user->rights->absence->myactions->modifierEdt;
		}
	}
	else if($user->rights->absence->myactions->modifierEdt){
		$droitsEdt=$user->rights->absence->myactions->modifierEdt;
	}
	else if($user->rights->absence->myactions->modifierSonEdt&&$user->id==$emploiTemps->fk_user){
		$droitsEdt=$user->rights->absence->myactions->modifierSonEdt;
	}
	else $droitsEdt=0;
	
	//echo "salut".$user->rights->absence->myactions->modifierSonEdt;
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/emploitemps.tpl.php'
		,array(	
		)
		,array(
			'planning'=>$TPlanning
			,'horaires'=>$THoraire
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', 'Absence')
				,'compteur_id'=>$emploiTemps->getId()
			)
			,'droits'=>array(
				'modifierEdt'=>$droitsEdt
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
