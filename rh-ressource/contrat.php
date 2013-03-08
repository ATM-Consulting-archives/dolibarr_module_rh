<?php
	require('config.php');
	require('./class/contrat.class.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$contrat=new TRH_contrat;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$contrat->set_values($_REQUEST);
				$mesg = '<div class="ok">Nouvelle ressource créée</div>';
				_fiche($ATMdb, $contrat,'edit');
				
				break;	
			case 'edit'	:
				$contrat->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $contrat,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$contrat->load($ATMdb, $_REQUEST['id']);
				$contrat->set_values($_REQUEST);
				$contrat->save($ATMdb);
				$contrat->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				
				_fiche($ATMdb, $contrat,$mode);
				break;
			
			case 'view':
				$contrat->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $contrat,'view');
				break;

			case 'delete':
				//$ATMdb->db->debug=true;
				$contrat->load($ATMdb, $_REQUEST['id']);
				$contrat->delete($ATMdb);
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$contrat->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb, $contrat, 'view');
	}
	else {
		/*
		 * Liste
		 */
		 //$ATMdb->db->debug=true;
		 _liste($ATMdb, $contrat);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _liste(&$ATMdb, &$contrat) {
	global $langs,$conf, $db;
	llxHeader('','Liste des contrats');
	getStandartJS();
	echo date('l jS \of F Y h:i:s A',1362659583);
	$r = new TSSRenderControler($contrat);
	$sql= "SELECT c.rowid as 'ID',  DATE(c.date_debut) as 'Date Début',c.libelle as 'Libellé',
			r.libelle as 'Ressource',c.bail as 'Bail', g.nom as 'Agence',s.nom as 'Fournisseur'
			FROM llx_rh_contrat as c, llx_usergroup as g, llx_societe as s, llx_rh_ressource as r
			WHERE c.entity=".$conf->entity." 
			AND g.rowid = c.fk_tier_utilisateur
			AND s.rowid = c.fk_tier_fournisseur
			AND r.rowid = c.fk_rh_ressource";
	
	
	$TOrder = array('Date Début'=>'ASC');
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
			'Libellé'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des contrats'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun contrat à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$contrat, $mode) {
	global $db,$user;
	llxHeader('', 'Contrat');

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $contrat->getId());
	echo $form->hidden('action', 'save');
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/contrat.tpl.php'
		,array()
		,array(
			'contrat'=>array(
				'id'=>$contrat->getId()
				,'libelle'=>$form->texte('', 'libelle', $contrat->libelle, 50,255,'','','-')
				,'typeContrat'=> $form->combo('','bail',$contrat->TBail, $contrat->bail)
				,'typeRessource'=> $form->combo('','fk_rh_ressource',$contrat->TTypeRessource, $contrat->fk_rh_ressource)
				,'tiersFournisseur'=> $form->combo('','fk_tier_fournisseur',$contrat->TTiers,$contrat->fk_tier_fournisseur)
				,'tiersAgence'=> $form->combo('','fk_tier_utilisateur',$contrat->TAgence,$contrat->fk_tier_utilisateur)
				,'date_debut'=> $form->calendrier('', 'date_debut', $contrat->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $contrat->get_date('date_fin'), 10)
				,'loyer_TTC'=>$form->texte('', 'loyer_TTC', $contrat->loyer_TTC, 10,20,'','','-')
				,'TVA'=>$form->combo('','TVA',$contrat->TTVA,$contrat->TVA)
				,'loyer_HT'=>($contrat->loyer_TTC)*(1-(0.01*$contrat->TTVA[$contrat->TVA]))
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

	
	
