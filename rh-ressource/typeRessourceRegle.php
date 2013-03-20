<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./class/regle.class.php');
	require('./lib/ressource.lib.php');
	
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$ressourceType=new TRH_ressource_type;
	$regle = new TRH_Ressource_Regle;
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				//$ressourceType->save($ATMdb);
				_fiche($ATMdb, $regle, $ressourceType,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				_fiche($ATMdb,  $regle, $ressourceType,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				print_r($_REQUEST);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				$regle->set_values($_REQUEST);				
				$regle->save($ATMdb);
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,  $regle, $ressourceType,$mode);
				break;
			
			case 'view':
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				_fiche($ATMdb,  $regle, $ressourceType,'view');
				break;
		
			case 'delete':
				$regle->load($ATMdb, $_REQUEST['idRegle']);
				$regle->delete($ATMdb);
				//$ATMdb->db->debug=true;
				
				/*
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?*/
				$ressourceType->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le type de ressource est utilisé par une ressource. Il ne peut pas être supprimé.</div>';
				_liste($ATMdb, $ressourceType, 'view');
			
				break;
		}
	}
	elseif(isset($_REQUEST['id']) && isset($_REQUEST['idRegle'])) {
		$ressourceType->load($ATMdb, $_REQUEST['id']);
		$regle->load($ATMdb, $_REQUEST['idRegle']);
		_fiche($ATMdb,  $regle, $ressourceType,'view');
	}
	
	elseif(isset($_REQUEST['id'])) {
		$ressourceType->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $ressourceType);
		
	}
	else {
		/*
		 * Liste
		 */
		 _liste($ATMdb, $ressourceType);
	}
	
	
	$ATMdb->close();
	
	
function _liste(&$ATMdb, &$ressourceType) {
	global $langs,$conf, $db;	
	
	llxHeader('','Règles sur les Ressources');
	?><div class="fiche"><?	
	dol_fiche_head(ressourcePrepareHead($ressourceType, 'type-ressource')  , 'regle', 'Type de ressource');
	
	$r = new TSSRenderControler($ressourceType);
	$sql="SELECT DISTINCT r.rowid as 'ID', u.name as 'Utilisateur', g.nom as 'Groupe', r.duree as 'Période',
		r.montant as 'Montant'
		FROM ".MAIN_DB_PREFIX."rh_ressource_regle as r
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (r.fk_user = u.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."usergroup as g ON (r.fk_group = g.rowid)
		WHERE r.entity=".$conf->entity."
		AND r.fk_rh_ressource_type=".$ressourceType->getId();
	
	//echo $sql;
	$TOrder = array('ID'=>'ASC');
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
			'ID'=>'<a href="?id='.$ressourceType->getId().'&idRegle=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des types de ressources'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun type de ressource à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	?><a class="butAction" href="?id=<?=$ressourceType->getId()?>&action=new">Nouveau</a><div style="clear:both"></div></div><?
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$regle, &$ressourceType, $mode) {
	llxHeader('','Règle sur les Ressources', '', '', 0, 0);
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressourceType->getId());
	echo $form->hidden('idRegle', $regle->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_rh_ressource_type', $ressourceType->getId());
	
	$TBS=new TTemplateTBS();
	
	print $TBS->render('./tpl/ressource.type.regle.tpl.php'
		,array()
		,array(
			'ressourceType'=>array(
				'id'=>$ressourceType->getId()
				,'code'=> $ressourceType->code
				,'libelle'=> $ressourceType->libelle
			)
			,'newRule'=>array(
				'id'=>$regle->getId()
				,'duree'=>$form->combo('', 'duree',$regle->TDuree, $regle->duree)
				,'objet'=>$form->combo('', 'objet',$regle->TObjet, $regle->objet)
				,'fk_user'=>$form->combo('', 'fk_user',$regle->TUser, $regle->fk_user)
				,'fk_group'=>$form->combo('', 'fk_group',$regle->TGroup, $regle->fk_group)
				,'montant'=>$form->texte('', 'montant', $regle->montant, 20,255,'','','-')
			
			)
			,'view'=>array(
				'mode'=>$mode
				,'nbChamps'=>count($ressourceType->TField)
			/*	,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressourceType)  , 'regle', 'Type de ressource')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
