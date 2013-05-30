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
	global $langs,$conf,$db,$user;
	
	llxHeader('', 'Liste des validations possibles');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'valideur', $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?=$fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?=$fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?=$fuser->firstname ?></td></tr>
	</tbody></table><br/><?
	
	if($user->rights->valideur->myactions->valideur=="1"){
		$valideur=new TRH_valideur_groupe;
		$r = new TSSRenderControler($valideur);
		$sql= "SELECT v.rowid as 'ID', v.type as 'Type', v.nbjours as 'Nombre de jours', CONCAT (CAST(v.montant as DECIMAL(16,2)), ' €') as 'Montant TTC', g.rowid as 'GroupeID', g.nom as 'Groupe',v.validate_himself as 'Se valide lui-même ?', v.level as 'Niveau du valideur', '' as 'Supprimer'";
		$sql.= " FROM ((".MAIN_DB_PREFIX."rh_valideur_groupe as v LEFT JOIN ".MAIN_DB_PREFIX."usergroup as g ON (v.fk_usergroup = g.rowid))
				 		 	LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (v.fk_user = u.rowid))";
		$sql.= " WHERE v.entity=".$conf->entity." AND v.fk_user=".$fuser->id;
		
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
				,'Type'=>'<a href="?id=@ID@&action=edit">@val@</a>'
				,'Groupe'=>'<a href="'.DOL_URL_ROOT.'/user/group/fiche.php?id=@GroupeID@">@val@</a>'
				,'Supprimer'=>'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="img/delete.png" /></a>'
				
			)
			,'translate'=>array('Se valide lui-même ?'=>$valideur->TValidate_himself)
			,'hide'=>array('GroupeID')
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Liste des validations possibles'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucun lien de validation à afficher pour cet utilisateur."
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				
			)
			,'orderBy'=>$TOrder
			
		));
		
		?><a class="butAction" href="?action=new&fk_user=<?=$fuser->id ?>">Nouveau</a>
		<div style="clear:both;"></div>
		<?
		
	}else{
		?>
		<p>Vous ne disposez pas du droit pour vous déclarer valideur d'un groupe.</p>
		<?
	}
	
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
	
	if($user->rights->valideur->myactions->valideur=="1"){
	
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		echo $form->hidden('fk_user', $fuser->id);
		echo $form->hidden('id', $valideur->getId());
		echo $form->hidden('action', 'save');
		
		$valideur->loadListGroup($ATMdb,$fuser->id);
		
		if (!empty($valideur->TGroup) ){
			$TBS=new TTemplateTBS();
			print $TBS->render('./tpl/valideur.tpl.php'
				,array(
				)
				,array(
					'userCourant'=>array(
						'id'=>$user->id
					)
					,'user'=>array(
						'id'=>$fuser->id
						,'lastname'=>$fuser->lastname
						,'firstname'=>$fuser->firstname
					)
					,'valideur'=>array(
						'group'=>$form->combo('','fk_usergroup',$valideur->TGroup,$valideur->fk_usergroup)
						,'type'=> $form->combo('','type',$valideur->TType, $valideur->type)
						,'nbjours'=> $form->texte('', 'nbjours', $valideur->nbjours, 7,10,'','','-')
						,'montant'=> $form->texte('', 'montant', $valideur->montant, 7,10,'','','-')
						,'validate_himself'=>$form->combo('','validate_himself', $valideur->TValidate_himself, $valideur->validate_himself )
						,'level'=>$form->combo('','level', array(1=>'Niveau 1',2=>'Niveau 2',3=>'Niveau 3'), $valideur->level )
					)
					,'view'=>array(
						'mode'=>$mode
					)
					
				)	
				
			);
			echo $form->end_form();
		}
		else {
			?> L'utilisateur n'appartient à aucun groupe. Renseigner un groupe. <?
		}
	
	}else{
		?>
		<p>Vous ne disposez pas du droit pour vous déclarer valideur d'un groupe.</p>
		<?
	}
	
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

dol_fiche_end();
$db->close();