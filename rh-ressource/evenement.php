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
				$evenement->set_values($_REQUEST);
				//$emprunt->load($ATMdb, 20);
				$mesg = '<div class="ok">Nouvel événement créé</div>';
				_fiche($ATMdb, $evenement,$ressource,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $evenement,$ressource,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				
				if(isset($_REQUEST['newEvent']) ) {
					$evenement->set_values($_REQUEST);
					$evenement->save($ATMdb);
				}
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $evenement,$ressource,$mode);
				break;
			
			case 'view':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $evenement,$ressource,'view');
				break;
			
			case 'deleteEvent':
				//$ATMdb->db->debug=true;
				$evenement->load($ATMdb, $_REQUEST['idEvent']);
				$evenement->delete($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">L\'attribution a bien été supprimée.</div>';
				$mode = 'view';
				_fiche($ATMdb, $evenement, $ressource,$mode);
				break;
				
				
			
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb, $evenement,$ressource, 'view');
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
	
function _liste(&$ATMdb, &$evenement) {
	global $langs,$conf, $db;	
	llxHeader('','Liste des emprunts');
	getStandartJS();
	
	$r = new TSSRenderControler($evenement);
	$sql="SELECT e.rowid as 'ID', r.libelle as 'Ressource', u.name as 'Nom', e.date_cre as 'DateCre', 
		DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin'
		FROM ".MAIN_DB_PREFIX."rh_evenement as e, ".MAIN_DB_PREFIX."rh_ressource as r, ".MAIN_DB_PREFIX."user as u
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
	
function _fiche(&$ATMdb, &$evenement,&$ressource,  $mode) {
	global $db,$user;
	llxHeader('', 'Evénement');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	$ressource->load_evenement($ATMdb, array('accident', 'reparation'));
	$TEvents = array();
	foreach($ressource->TEvenement as $k=>$even){
		$TEvents[] = array(
					'id'=>$even->getId()
					,'user'=>$even->TUser[$even->fk_user]
					,'date_debut'=>date("d/m/Y",$even->date_debut)
					,'date_fin'=>date("d/m/Y",$even->date_fin)
					,'commentaire'=>$even->description
					,'motif'=>$even->motif
					,'type'=> $even->TType[$even->type]
					,'montantHT'=>$even->montant_HT
					,'TVA'=>$even->TTVA[$even->TVA]
		);
	}

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/evenement.tpl.php'
		,array(
			'historique'=>$TEvents
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
		/*,array(
			'evenement'=>array(
				'id'=>$evenement->getId()
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'date'=> $form->calendrier('', 'date_debut', $evenement->get_date('date_debut'), 10)
				,'type'=>$form->texte('', 'type', $evenement->type, 20,100)
				,'motif'=>$form->texte('', 'motif', $evenement->motif, 20,100)
				,'montantHT'=>$form->texte('', 'montant_HT', $evenement->montant_HT, 10,10)
				,'TVA'=>$form->combo('','TVA',$evenement->TTVA,$evenement->TVA)
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'evenement', 'Ressource')
			)
			*/
}

	
	
