<?php
	require('config.php');
	require('./class/contrat.class.php');
	require('./lib/ressource.lib.php');
	
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
				_fiche($ATMdb, $contrat,'new');
				
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
				if ($_REQUEST['libelle']!=''){
					
					$mesg = '<div class="ok">Modifications effectuées</div>';
					$mode = 'view';
				}
				else {
					$mesg = '<div class="error">Veuillez renseigner un libellé.</div>';
					$mode = 'edit';
				}

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
	global $langs,$conf,$db,$user;
	llxHeader('','Liste des contrats');
	print dol_get_fiche_head(array()  , '', 'Liste contrats');
	getStandartJS();
	
	$r = new TSSRenderControler($contrat);
	
	$sql= "SELECT c.rowid as 'ID', c.libelle , c.numContrat,   DATE(c.date_debut) as 'Date début', 
			DATE(c.date_fin) as 'Date fin',
			t.libelle as 'Type Ressource' , s.nom as 'Fournisseur'";
	if($user->rights->ressource->contrat->createContract){
		$sql.=", '' as Supprimer";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_contrat as c";
	$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (c.fk_tier_fournisseur = s.rowid)";
	$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource_type as t ON (c.fk_rh_ressource_type = t.rowid)";
	if(!$user->rights->ressource->contrat->viewContract){
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."rh_contrat_ressource as cr ON cr.fk_rh_contrat = c.rowid";
		$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON e.fk_rh_ressource=cr.fk_rh_ressource";
	}
	$sql.=" WHERE 1 ";
	if(!$user->rights->ressource->contrat->viewContract){
		$sql.=" AND e.type ='emprunt'";
		$sql.=" AND e.fk_user=".$user->id;
	}
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','GET');
	
	$TOrder = array('Date début'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelle'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\"  onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete'};\"><img src=\"./img/delete.png\"></a>"
		)
		,'translate'=>array()
		,'hide'=>array()
		,'type'=>array(
			'Date début'=>'date'
			,'Date fin'=>'date'
			)
		,'liste'=>array(
			'titre'=>'Liste des contrats'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun contrat à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'libelle'=>'Libellé'
			,'numContrat'=>'Numéro du contrat'
		)
		,'search'=>array(
			'numContrat'=>true
			,'libelle'=>array('recherche'=>true,'table'=>'c')
		)
		,'orderBy'=>$TOrder
		
	));
	
	$form->end();
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$contrat, $mode) {
	global $db,$user, $conf;
	llxHeader('', 'Contrat');

	$html=new Form($db);
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $contrat->getId());
	echo $form->hidden('action', 'save');
	$contrat->load_liste($ATMdb);
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/contrat.tpl.php'
		,array()
		,array(
			'contrat'=>array(
				'id'=>$contrat->getId()
				,'titreModification'=>load_fiche_titre("Modification d'un contrat",'', 'title.png', 0, '')
				,'titreNouveau'=>load_fiche_titre("Création d'un contrat",'', 'title.png', 0, '')
				,'libelle'=>$form->texte('', 'libelle', $contrat->libelle, 50,255,'','','-')
				,'numContrat'=>$form->texte('', 'numContrat', $contrat->numContrat, 50,255,'','','-')
				//,'typeContrat'=> $form->combo('','bail',$contrat->TBail, $contrat->bail)
				,'typeRessource'=> $form->combo('','fk_rh_ressource_type',$contrat->TTypeRessource, $contrat->fk_rh_ressource_type)
				//,'tiersFournisseur'=> ($mode=='edit') ? $html->select_company('','fk_tier_fournisseur','',0, 0,1) : $contrat->fk_tier_fournisseur
				,'tiersFournisseur'=> $form->combo('','fk_tier_fournisseur',$contrat->TFournisseur,$contrat->fk_tier_fournisseur)
				,'tiersAgence'=> $form->combo('','fk_tier_utilisateur',$contrat->TAgence,$contrat->fk_tier_utilisateur)
				,'date_debut'=> $form->calendrier('', 'date_debut', $contrat->date_debut,12, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $contrat->date_fin,12, 12)
				,'entretien'=>$form->texte('', 'entretien', $contrat->entretien, 10,20,'','','0')
				,'assurance'=>$form->texte('', 'assurance', $contrat->assurance, 10,20,'','','0')
				,'kilometre'=>$form->texte('', 'kilometre', $contrat->kilometre, 8,8,'','','')
				,'dureemois'=>$form->texte('', 'dureeMois', $contrat->dureeMois, 8,8,'','','')
				,'loyer_TTC'=>$form->texte('', 'loyer_TTC', $contrat->loyer_TTC, 10,20,'','','0')
				,'TVA'=>$form->combo('','TVA',$contrat->TTVA,$contrat->TVA)
				,'loyer_HT'=>$form->texte('', 'loyer_HT', number_format(($contrat->loyer_TTC)*(1-($contrat->TTVA[$contrat->TVA]/100)),2), 10,20,'disabled','','')
				
				
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRightViewContrat'=>(int)$user->rights->ressource->contrat->viewPrixContrat
				,'userRight'=>((int)$user->rights->ressource->contrat->createContract)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($contrat, 'contrat')  , 'fiche', 'Contrat')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Création contrat')
			)
			
			
		)	
		
	);
	
	
	if ($mode == 'view' ){
		print "<br/>";
		//liste des ressources associées
		$r = new TSSRenderControler($contrat);
		$sql= "SELECT r.rowid as ID, r.libelle as 'Libellé' , r.numId as 'Numéro Id'
				FROM ".MAIN_DB_PREFIX."rh_ressource as r, ".MAIN_DB_PREFIX."rh_contrat_ressource as l";
		if(!$user->rights->ressource->ressource->viewRessource){
			$sql.=" LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON e.fk_rh_ressource=l.fk_rh_ressource";
		}
		$sql.=" WHERE r.entity IN (0,".$conf->entity.")
				AND l.fk_rh_contrat =".$contrat->getId()."
				AND l.fk_rh_ressource = r.rowid	";
		if(!$user->rights->ressource->ressource->viewRessource){
			$sql.=" AND e.type ='emprunt'
				AND e.fk_user=".$user->id;
		}
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
				'Libellé'=>'<a href="ressource.php?id=@ID@&action=view">@val@</a>'
			)
			,'translate'=>array()
			,'hide'=>array()
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Liste des ressources associées'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucune ressource associée"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				
			)
			,'orderBy'=>$TOrder
			
		));
		
		print "<br/>";
		//liste des adresses liés au contrat
		$r = new TSSRenderControler($contrat);
		$sql= "SELECT s.rowid as ID , s.name as 'Nom', CONCAT(s.address, ' ', s.cp, ' ',s.ville) as 'Adresse',
				s.phone as 'Tél pro.', s.phone_mobile as 'Tél portable', s.fax as 'Fax', s.email as 'EMail'
				FROM ".MAIN_DB_PREFIX."socpeople as s
				LEFT JOIN	".MAIN_DB_PREFIX."rh_contrat as c ON (s.fk_soc = c.fk_tier_fournisseur)
				WHERE s.entity IN (0,".$conf->entity.")
				AND c.rowid =".$contrat->getId();
		$TOrder = array('ID'=>'ASC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
					
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		$r->liste($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				'Nom'=>'<a href="../../contact/fiche.php?id=@ID@">@val@</a>'
			)
			,'translate'=>array()
			,'hide'=>array()
			,'type'=>array()
			,'liste'=>array(
				'titre'=>'Liste des agences à contacter en cas de problème'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucune agence liée"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				
			)
			,'orderBy'=>$TOrder
			
		));
	}
	
	
	echo $form->end_form();
	// End of page
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
