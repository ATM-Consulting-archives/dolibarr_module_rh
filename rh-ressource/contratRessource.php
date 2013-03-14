<?php
	require('config.php');
	require('./class/contrat.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$association = new TRH_Contrat_Ressource;
	$ressource = new TRH_Ressource;
	$contrat = new TRH_Contrat;
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				_fiche($ATMdb, $contrat, $association,$ressource,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $contrat, $association, $ressource,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				
				if(isset($_REQUEST['newAssociation']) ) {
					$association->set_values($_REQUEST);
					$association->save($ATMdb);
				}
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $contrat, $association, $ressource, $mode);
				break;
			
			case 'view':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $contrat, $association, $ressource,'view');
				break;
			
			case 'deleteAssoc':
				//$ATMdb->db->debug=true;
				$association->load($ATMdb, $_REQUEST['idAssoc']);
				$association->delete($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le lien avec le contrat a été supprimée.</div>';
				_fiche($ATMdb, $contrat, $association, $ressource,'view');
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb, $contrat, $association, $ressource,'view');
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
	
function _fiche(&$ATMdb, &$contrat, &$association, &$ressource,  $mode) {
	global $db,$user;
	llxHeader('', 'Contrats');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	
	$ressource->load_contrat($ATMdb);
	$TContrats = array();
	foreach($ressource->TContratAssocies as $assoc){
		$TContrats[] = array(
					'id'=>$assoc->getId()
					,'idContrat'=>$ressource->TContratExaustif[$assoc->fk_rh_contrat]->getId()
					,'libelle'=>$ressource->TContratExaustif[$assoc->fk_rh_contrat]->libelle
					,'date_debut'=>date("d/m/Y",$ressource->TContratExaustif[$assoc->fk_rh_contrat]->date_debut)
					,'date_fin'=>date("d/m/Y",$ressource->TContratExaustif[$assoc->fk_rh_contrat]->date_fin)
					,'bail'=>$ressource->TContratExaustif[$assoc->fk_rh_contrat]->bail
					,'loyer_TTC'=> $ressource->TContratExaustif[$assoc->fk_rh_contrat]->loyer_TTC
					,'TVA'=>$assoc->TTVA[$ressource->TContratExaustif[$assoc->fk_rh_contrat]->TVA]
		);
	}

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/contratRessource.tpl.php'
		,array(
			'associations'=>$TContrats
		)
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
			)
			,'NAssociation'=>array(
				'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'fk_rh_contrat'=>$form->combo('', 'fk_rh_contrat', $ressource->TListeContrat, $association->fk_rh_contrat)
				,'commentaire'=>$form->texte('','motif',$association->commentaire, 30,100,'','','-')
			
			)
			,'view'=>array(
				'mode'=>$mode
			/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'contrats', 'Ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
		
}

	
	
