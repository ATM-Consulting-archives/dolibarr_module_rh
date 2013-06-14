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
				
				$emploiTemps->tempsHebdo=$emploiTemps->calculTempsHebdo($ATMdb, $emploiTemps);
				
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
		if($user->rights->absence->myactions->voirTousEdt){
			$emploiTemps->load($ATMdb, $_REQUEST['fk_user']);
			_liste($ATMdb, $emploiTemps);
		}else{
			//on récupère les infos de l'edt de l'utilisateur courant
			$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps
			WHERE entity IN (0,".$conf->entity.") AND fk_user=".$user->id;
			$ATMdb->Execute($sql);
			if($ATMdb->Get_line()) {
						$emploiTemps->load($ATMdb, $ATMdb->Get_field('rowid'));
			}
			_fiche($ATMdb, $emploiTemps,'view');
		}
		
	}
	
	$ATMdb->close();
	llxFooter();
	
	
function _liste(&$ATMdb, &$emploiTemps) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	getStandartJS();
	print dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', 'Absence');
	
	/*$sql=" SELECT DISTINCT u.fk_user FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v, ".MAIN_DB_PREFIX."usergroup_user as u 
			WHERE v.fk_user=".$user->id." 
			AND v.type='Conges'
			AND v.fk_usergroup=u.fk_usergroup
			AND v.entity IN (0,".$conf->entity.")";
		
	$ATMdb->Execute($sql);
	$TabUser=array();
	$k=0;
	while($ATMdb->Get_line()) {
				$TabUser[]=$ATMdb->Get_field('fk_user');
				$k++;
	}*/
	
	$r = new TSSRenderControler($emploiTemps);
	$sql="SELECT DISTINCT e.rowid as 'ID', e.date_cre as 'DateCre', 
	 e.fk_user as 'Id Utilisateur', '' as 'Emploi du temps', u.login,
	u.firstname, u.name, '' as '','' as ''
		FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as e, ".MAIN_DB_PREFIX."user as u
		WHERE e.entity IN (0,".$conf->entity.") AND u.rowid=e.fk_user ";

	if($user->rights->absence->myactions->voirTousEdt!="1"){
		$sql.=" AND e.fk_user=".$user->id;
	}
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');	
	$TOrder = array('name'=>'ASC');
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
			, 'Emploi du temps'=>'<a href="?id=@ID@&action=view&fk_user='.$user->id.'"<a>Emploi du temps</a>'
		)
		,'title'=>array(
			'firstname'=>'Prénom'
			,'name'=>'Nom'
			,'login'=>'Login'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre','ID', 'Id Utilisateur')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des emplois du temps des collaborateurs'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun emploi du temps à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'orderBy'=>$TOrder
		,'search'=>array(
			'firstname'=>true
			,'name'=>true
			,'login'=>true
		)
		,'eval'=>array(
				'name'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
				,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		
	));
	$form->end();
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$emploiTemps, $mode) {
	global $db,$user,$idUserCompt, $idComptEnCours,$conf;
	llxHeader('','Emploi du temps');
	$emploiTemps->load($ATMdb, $_REQUEST['id']);
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $_REQUEST['id']);
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $emploiTemps->fk_user);

	$sql="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$emploiTemps->fk_user;
	$ATMdb->Execute($sql);
	$userCourant=array();
	while($ATMdb->Get_line()) {
				
				$userCourant['firstname']=$ATMdb->Get_field('firstname');
				$userCourant['name']=$ATMdb->Get_field('name');
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

	$TEntity=array();
	$TEntity=$emploiTemps->load_entities($ATMdb);
	//print_r($TEntity);exit;
	
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
				,'tempsHebdo'=>$emploiTemps->tempsHebdo
				,'societe'=>$emploiTemps->societeRtt
			)
			,'entity'=>array(
				'TEntity'=>$form->combo('','societeRtt',$TEntity,$emploiTemps->societeRtt)
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', 'Absence')
				,'compteur_id'=>$emploiTemps->getId()
				,'titreEdt'=>load_fiche_titre("Emploi du temps de ".htmlentities($userCourant['firstname'], ENT_COMPAT , 'ISO8859-1')." ".htmlentities($userCourant['name'], ENT_COMPAT , 'ISO8859-1'),'', 'title.png', 0, '')
			)
			,'droits'=>array(
				'modifierEdt'=>$user->rights->absence->myactions->modifierEdt
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
