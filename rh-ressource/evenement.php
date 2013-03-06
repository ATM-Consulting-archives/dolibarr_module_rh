<?php
	require('config.php');
	require('./class/evenement.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$evenement=new TRH_Evenement;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$ATMdb->db->debug=true;
				$evenement->set_values($_REQUEST);
				//$emprunt->load($ATMdb, 20);
				$mesg = '<div class="ok">Nouvel événement créé</div>';
				_fiche($ATMdb, $evenement,'edit');
				
				break;	
			case 'edit'	:
				$ATMdb->db->debug=true;
				$emprunt->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $evenement,'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				$evenement->load($ATMdb, $_REQUEST['id']);
				$evenement->set_values($_REQUEST);
				$evenement->save($ATMdb);
				$evenement->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				
				if(isset($_REQUEST['validerType']) ) {
					$mode = 'edit';	
				}
				
				_fiche($ATMdb, $evenement,$mode);
				break;
			
			case 'view':
				$ATMdb->db->debug=true;
				$evenement->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $evenement,'view');
				break;
			
			case 'delete':
				//$ATMdb->db->debug=true;
				$evenement->load($ATMdb, $_REQUEST['id']);
				$evenement->delete($ATMdb);
				
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?
				
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$evenement->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb, $evenement, 'view');
	}
	else {
		/*
		 * Liste
		 */
		 $ATMdb->db->debug=true;
		 _liste($ATMdb, $evenement);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _liste(&$ATMdb, &$evenement) {
	global $langs,$conf, $db;	
	llxHeader('','Liste des emprunts');
	getStandartJS();
	
	$r = new TSSRenderControler($evenement);
	$sql="SELECT e.rowid as 'ID', r.libelle as 'Ressource', u.name as 'Nom', e.date_cre as 'DateCre', 
		DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin'
		FROM llx_rh_evenement as e, llx_rh_ressource as r, llx_user as u
		WHERE e.entity=".$conf->entity."
		AND u.rowid = e.fk_user
		AND r.rowid = e.fk_rh_ressource
		";
	
	$TOrder = array('DateCre'=>'ASC');
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
			'titre'=>'Liste des evenements'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun emprunt à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$evenement, $mode) {
	global $db,$user;
	llxHeader('', 'emprunt');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $evenement->getId());
	echo $form->hidden('action', 'save');
	

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/evenement.tpl.php'
		,array()
		,array(
			'evenement'=>array(
				'id'=>$evenement->getId()
				,'fk_user'=>$form->combo('','fk_user',$evenement->TUser,$evenement->fk_user)
				,'fk_rh_ressource_type'=> count($evenement->TTypeRessource) ? $form->combo('','fk_rh_ressource_type',$evenement->TTypeRessource,$evenement->fk_rh_ressource_type): "Aucun type"
				,'fk_rh_ressource'=> count($evenement->TRessource) ? $form->combo('','fk_rh_ressource',$evenement->TRessource,$evenement->fk_rh_ressource): "Aucune ressource de ce type"
				,'date_debut'=> $form->calendrier('', 'date_debut', $evenement->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $evenement->get_date('date_fin'), 10)
			)
			,'view'=>array(
				'mode'=>$mode
			/*	,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($evenement, 'ressource')  , 'evenement', 'Ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
