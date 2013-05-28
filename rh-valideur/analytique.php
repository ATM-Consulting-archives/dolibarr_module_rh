<?php

require('config.php');
require('./class/analytique.class.php');
require('./class/analytique_user.class.php');

require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

$langs->load('valideur@valideur');
$langs->load("users");

$ATMdb=new TPDOdb;
$analytique=new TRH_analytique;
$analytique_user=new TRH_analytique_user;
$mode = $action;

$mesg = '';
$error=false;

if(isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
		case 'add':
		case 'new':
			$analytique->set_values($_REQUEST);
			_fiche($ATMdb, $analytique,'edit');
			
			break;
		case 'add_code':
		case 'new_code':
			$analytique_user->set_values($_REQUEST);
			_fiche_code($ATMdb, $analytique_user,'edit');
			
			break;
		
		case 'edit'	:
			$analytique->load($ATMdb, $_REQUEST['id']);
			_fiche($ATMdb, $analytique,'edit');
			break;
		case 'edit_code'	:
			$analytique_user->load($ATMdb, $_REQUEST['id']);
			_fiche_code($ATMdb, $analytique_user,'edit');
			break;
		
		case 'save':
			$analytique->load($ATMdb, $_REQUEST['id']);
			$analytique->set_values($_REQUEST);
			$analytique->save($ATMdb);
			$mesg = '<div class="ok">Code analytique créé</div>';
			$mode = 'view';
			
			_liste($ATMdb);
			break;
		case 'save_code':
			$analytique_user->load($ATMdb, $_REQUEST['id']);
			$analytique_user->set_values($_REQUEST);
			$analytique_user->save($ATMdb);
			$mesg = '<div class="ok">Code analytique affecté</div>';
			$mode = 'view';
			
			_liste($ATMdb);
			break;

		case 'delete':
			$analytique->load($ATMdb, $_REQUEST['id']);
			$analytique->delete($ATMdb);
			
			$mesg = '<div class="ok">Le code analytique a bien été supprimé.</div>';
			
			_liste($ATMdb);
			break;
		case 'delete_code':
			$analytique_user->load($ATMdb, $_REQUEST['id']);
			$analytique_user->delete($ATMdb);
			
			$mesg = '<div class="ok">L\'affectation a bien été supprimée.</div>';
			
			_liste($ATMdb);
			break;
	}
}
elseif(isset($_REQUEST['id'])) {
	_fiche($ATMdb, $analytique, 'view');
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
	
	llxHeader('', 'Liste des codes analytiques');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'analytique', $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody><tr><td width="25%" valign="top">Réf.</td><td>
			<?=$fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?=$fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?=$fuser->firstname ?></td></tr>
	</tbody></table><br/>
	
	<?
	
	if($user->rights->valideur->myactions->analytique=="1"){
		$analytique_user=new TRH_analytique_user;
		$r = new TSSRenderControler($analytique_user);
		$sql= "SELECT u.rowid as 'ID', a.code as 'Code analytique', u.pourcentage as 'Pourcentage', '' as 'Supprimer'
			FROM ".MAIN_DB_PREFIX."rh_analytique_user as u
				LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique as a ON (a.rowid = u.fk_code)
			WHERE u.fk_user = ".$fuser->id." AND u.entity IN (0, ".$conf->entity.")";
		
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
				'ID'=>'<a href="?fk_user='.$fuser->id.'&id=@ID@&action=edit_code">@val@</a>'
				,'Pourcentage'=>'@Pourcentage@%'
				,'Supprimer'=>"<a onclick=\"if (confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete_code&fk_user=".$fuser->id."'};\" style='cursor:pointer;'><img src=\"./img/delete.png\"></a>"
			)
			,'hide'=>array()
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Liste des codes analytiques de l\'utilisateur'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucune affectation à afficher."
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				
			)
			,'orderBy'=>$TOrder
			
		));
		
		?><a class="butAction" href="?action=new_code&fk_user=<?=$fuser->id ?>">Affecter un code</a>
		<?
		
		$analytique=new TRH_analytique;
		$r = new TSSRenderControler($analytique);
		$sql= "SELECT a.rowid as 'ID', a.code as 'Code analytique', '' as 'Supprimer'
			FROM ".MAIN_DB_PREFIX."rh_analytique as a
			WHERE a.entity IN (0, ".$conf->entity.")";
		
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
				'ID'=>'<a href="?fk_user='.$fuser->id.'&id=@ID@&action=edit">@val@</a>'
				,'Supprimer'=>"<a onclick=\"if (confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete&fk_user=".$fuser->id."'};\" style='cursor:pointer;'><img src=\"./img/delete.png\"></a>"
			)
			,'hide'=>array()
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Liste de tous les codes analytiques'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucun code analytique à afficher."
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
		<p>Vous ne disposez pas du droit pour déclarer des codes analytiques.</p>
		<?
	}
	
	llxFooter();
}


function _fiche(&$ATMdb, &$analytique, $mode) {
	global $langs,$db,$user;
	
	llxHeader('', 'Analytique');
	//print_r($analytique);
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $analytique->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'analytique';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	if($user->rights->valideur->myactions->analytique=="1"){
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		echo $form->hidden('fk_user', $fuser->id);
		echo $form->hidden('id', $analytique->getId());
		echo $form->hidden('action', 'save');
		
		$TBS=new TTemplateTBS();
		print $TBS->render('./tpl/analytique.tpl.php'
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
				,'analytique'=>array(
					'id'=>$analytique->getId()
					,'code'=>$form->texte('', 'code', $analytique->code, 7,10,'','','-')
				)
				,'view'=>array(
					'mode'=>$mode
				)
				
			)	
			
		);
		echo $form->end_form();
	
	}else{
		?>
		<p>Vous ne disposez pas du droit pour ajouter des codes analytiques.</p>
		<?
	}
	
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

function _fiche_code(&$ATMdb, &$analytique_user, $mode) {
	global $langs,$db,$user;
	
	llxHeader('', 'Analytique');
	//print_r($analytique);
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $analytique_user->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'analytique';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$analytique_user->loadListAnalytique($ATMdb);
	
	if($user->rights->valideur->myactions->analytique=="1"){
		$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
		$form->Set_typeaff($mode);
		echo $form->hidden('fk_user', $fuser->id);
		echo $form->hidden('id', $analytique_user->getId());
		echo $form->hidden('action', 'save_code');
		
		$TBS=new TTemplateTBS();
		print $TBS->render('./tpl/analytique_user.tpl.php'
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
				,'analytique'=>array(
					'id'=>$analytique_user->getId()
					,'code'=>$form->combo('','fk_code',$analytique_user->TAnalytique,$analytique->fk_code)
					,'pourcentage'=>$form->texte('', 'pourcentage', $analytique_user->pourcentage, 3,3,'','','-')
				)
				,'view'=>array(
					'mode'=>$mode
				)
				
			)	
			
		);
		echo $form->end_form();
	
	}else{
		?>
		<p>Vous ne disposez pas du droit pour ajouter des codes analytiques.</p>
		<?
	}
	
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

dol_fiche_end();
$db->close();