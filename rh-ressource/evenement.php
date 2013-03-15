<?php
	require('config.php');
	require('./class/evenement.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$evenement=new TRH_Evenement;
	$ressource = new TRH_Ressource;
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$evenement->set_values($_REQUEST);
				_fiche($ATMdb, $evenement,$ressource,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$evenement->load($ATMdb, $_REQUEST['idEven']);
				_fiche($ATMdb, $evenement,$ressource,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$evenement->load($ATMdb, $_REQUEST['idEven']);
				$evenement->set_values($_REQUEST);
				$evenement->save($ATMdb);
					
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $evenement,$ressource,'view');
				break;
			
			case 'view':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$evenement->load($ATMdb, $_REQUEST['idEven']);
				_fiche($ATMdb, $evenement,$ressource,'view');
				break;
			
			case 'deleteEvent':
				//$ATMdb->db->debug=true;
				$evenement->load($ATMdb, $_REQUEST['idEven']);
				$evenement->delete($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">L\'attribution a bien été supprimée.</div>';
				$mode = 'view';
				_liste($ATMdb, $evenement, $ressource,$mode);
				break;
				
				
			
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $evenement,$ressource, 'view');
	}
	else {
		/*
		 * Liste
		 */
		 //$ATMdb->db->debug=true;
		 _liste($ATMdb, $evenement);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _liste(&$ATMdb, &$evenement, &$ressource) {
	global $langs,$conf, $db;	
	llxHeader('','Liste des emprunts');
		
	dol_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'evenement', 'Ressource');
	getStandartJS();
	
	$r = new TSSRenderControler($evenement);
	$sql="SELECT DISTINCT e.rowid as 'ID', u.name as 'Utilisateur', 
		DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin', e.type as 'Type',
		e.motif as 'Motif', e.description as 'Commentaire', e.montant_HT as 'Montant HT', t.taux as 'TVA'
		FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."c_tva as t ON (e.tva = t.rowid)
		WHERE e.entity=".$conf->entity."
		AND e.fk_rh_ressource=".$ressource->getId();
	
	$TOrder = array('Date fin'=>'ASC');
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
			'ID'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&action=view">@val@</a>'
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
	
	?><a href="?id=<?=$ressource->getId()?>&action=new">Nouveau</a><?
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$evenement,&$ressource,  $mode) {
	global $db,$user;
	llxHeader('', 'Evénement');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('idEven',$evenement->getId());

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/evenement.tpl.php'
		,array(
			//'historique'=>$TEvents
		)
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
			)
			,'NEvent'=>array(
				'id'=>$evenement->getId()
				,'user'=>$form->combo('','fk_user',$evenement->TUser,$evenement->fk_user)
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'commentaire'=>$form->texte('','description',$evenement->description, 30,100,'','','-')
				,'motif'=>$form->texte('','motif',$evenement->motif, 30,100,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $evenement->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $evenement->get_date('date_fin'), 10)
				,'type'=>$form->combo('', 'type', $evenement->TType, $evenement->type)
				,'montantHT'=>$form->texte('', 'montant_HT', $evenement->montant_HT, 10,10)
				,'TVA'=>$form->combo('','TVA',$evenement->TTVA,$evenement->TVA)
			)
			,'view'=>array(
				'mode'=>$mode
			/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'evenement', 'Ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
		
}

	
	
