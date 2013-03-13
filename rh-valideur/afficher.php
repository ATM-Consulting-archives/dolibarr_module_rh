<?php

require('config.php');
require('./class/valideur.class.php');

require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

$langs->load('valideur@valideur');
$langs->load("users");

$ATMdb=new TPDOdb;
$valideur=new TRH_valideur_groupe;
$mode = $action;

$mesg = '';
$error=false;


if(isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
		case 'add':
		case 'new':
			$valideur->set_values($_REQUEST);
			//$mesg = '<div class="ok">Nouvelle validation créée</div>';
			_fiche($ATMdb, $valideur,'edit');
			
			break;	
		case 'edit'	:
			$valideur->load($ATMdb, $_REQUEST['id']);
			_fiche($ATMdb, $valideur,'edit');
			break;
			
		case 'save':
			/*$ATMdb->debug=true;
			print_r($_REQUEST);*/
			$valideur->load($ATMdb, $_REQUEST['id']);
			$valideur->set_values($_REQUEST);
			$valideur->save($ATMdb);
			$mesg = '<div class="ok">Validateur créé</div>';
			$mode = 'view';
			
			_liste($ATMdb);
			break;


		case 'delete':
			$valideur->load($ATMdb, $_REQUEST['id']);
			$valideur->delete($ATMdb);
			
			$mesg = '<div class="ok">L\'attribution a bien été supprimée.</div>';
			
			_liste($ATMdb);
			break;
	}
}
elseif(isset($_REQUEST['id'])) {
	_fiche($ATMdb, $valideur, 'view');
}
else {
	/*
	 * Liste
	 */
	 //$ATMdb->db->debug=true;
	 _liste($ATMdb);
}

$ATMdb->close();
llxFooter();

function _liste(&$ATMdb) {
	global $langs,$conf,$db;
	
	llxHeader('', 'Liste des validations possibles');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'valideur', $langs->trans('Utilisateur'),0, 'user');
	
	$valideur=new TRH_valideur_groupe;
	$r = new TSSRenderControler($valideur);
	$sql= "SELECT v.rowid as 'ID', v.type as 'Type', v.nbjours as 'Nbjours', g.nom as 'Group', u.name as 'Utilisateur', '' as 'Supprimer'";
	$sql.= " FROM ((".MAIN_DB_PREFIX."rh_valideur_groupe as v LEFT JOIN ".MAIN_DB_PREFIX."usergroup as g ON (v.fk_usergroup = g.rowid))
			 		 	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (v.fk_user = u.rowid))";
	$sql.= " WHERE v.entity=".$conf->entity." AND v.fk_user=".$fuser->id;
	
	//print $sql;
	
	$TOrder = array('ID'=>'DESC');
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
			'ID'=>'<a href="?id=@ID@&action=edit">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'">Supprimer</a>'
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des validations possibles'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun lien de validation à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	?><a href="?action=new&fk_user=<?=$fuser->id ?>">Nouveau</a><?
	
	llxFooter();
}


function _fiche(&$ATMdb, &$valideur, $mode) {
	global $langs,$db,$user;
	
	llxHeader('', 'Validation');
	//print_r($valideur);
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $valideur->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'valideur';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('fk_user', $fuser->id);
	echo $form->hidden('id', $valideur->getId());
	echo $form->hidden('action', 'save');
	
	
	$TValidations = array();
	/*$sqlReq="SELECT r.rowid, g.nom, r.type, r.nbjours FROM ".MAIN_DB_PREFIX."rh_valideur_groupe r, ".MAIN_DB_PREFIX."usergroup g WHERE g.rowid = r.fk_usergroup";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TValidations[] = array(
			'id'=>$ATMdb->Get_field('rowid')
			,'group'=>$ATMdb->Get_field('nom')
			,'type'=>$ATMdb->Get_field('type')
			,'nbjours'=>$ATMdb->Get_field('nbjours')
		);
	}
	*/
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/valideur.tpl.php'
		,array(
			'validations'=>$TValidations
		)
		,array(
			'userCourant'=>array(
				'id'=>$user->id
			)
			,'valideur'=>array(
				
				'group'=>$form->combo('','fk_usergroup',$valideur->TGroup,$valideur->fk_usergroup)
				,'type'=> $form->combo('','type',$valideur->TType, $valideur->type)
				,'nbjours'=> $form->texte('', 'nbjours', $valideur->nbjours, 7,10,'','','-')
			)
			,'view'=>array(
				'mode'=>$mode
			)
			
		)	
		
	);

	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

dol_fiche_end();
$db->close();