<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$emploiTemps=new TRH_EmploiTemps;
	
	
	
	//global $idUserCompt, $idComptEnCours;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {

			case 'edit'	:
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $emploiTemps,'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				
				$emploiTemps->razCheckbox($ATMdb, $emploiTemps);
				
				$emploiTemps->set_values($_REQUEST);
				
				$emploiTemps->save($ATMdb);
				
				$mesg = '<div class="ok">Demande enregistrée</div>';
				_fiche($ATMdb, $emploiTemps,'view');
				break;
			
			case 'view':
					$emploiTemps->loadByuser($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $emploiTemps,'view');
				break;

		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		//$ATMdb->db->debug=true;
		$sqlReqUser="SELECT rowid FROM `llx_rh_absence_emploitemps` where fk_user=".$user->id;//AND entity=".$conf->entity;
		$ATMdb->Execute($sqlReqUser);
		while($ATMdb->Get_line()) {

				$compteurCourant=$ATMdb->Get_field('rowid');
		}
		$emploiTemps->load($ATMdb, $compteurCourant);
		_fiche($ATMdb, $emploiTemps,'view');
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$emploiTemps) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	getStandartJS();
	
	$r = new TSSRenderControler($emploiTemps);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', fk_user as 'Id Utilisateur'
		FROM llx_rh_absence_emploitemps 
		WHERE entity=".$conf->entity;
		
	
	$TOrder = array('Statut demande'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste de vos absences'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune absence à afficher"
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


	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $_REQUEST['id']);
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $emploiTemps->fk_user);

	$userCourant=new User($db);
	$userCourant->fetch($emploiTemps->fk_user);
	
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
	

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/emploitemps.tpl.php'
		,array(	
		)
		,array(
			'planning'=>$TPlanning
			,'horaires'=>$THoraire
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>$userCourant->lastname
				,'firstname'=>$userCourant->firstname
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', 'Absence')
				,'compteur_id'=>$emploiTemps->getId()
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


	
	
