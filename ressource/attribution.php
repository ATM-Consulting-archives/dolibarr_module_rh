<?php
	require('config.php');
	require('./class/evenement.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new TPDOdb;
	$emprunt=new TRH_Evenement;
	$ressource=new TRH_Ressource;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				/*$ATMdb->db->debug=true;*/
				$ressource->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $emprunt,$ressource, 'new');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$emprunt->load($ATMdb, $_REQUEST['idEven']);
				_fiche($ATMdb, $emprunt,$ressource, 'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;				
				//on vérifie que la date choisie ne superpose pas avec les autres emprunts.
				
				if ($ressource->nouvelEmpruntSeChevauche($ATMdb,  $_REQUEST['id'], $_REQUEST) ){
					$mesg = '<div class="error">Impossible d\'attributer la ressource. Les dates choisies se superposent avec d\'autres attributions.</div>';
				}
				else {
					$mesg = '<div class="ok">Attribution ajoutée.</div>';
					$emprunt->load($ATMdb, $_REQUEST['idEven']);
					$emprunt->set_values($_REQUEST);
					$emprunt->save($ATMdb);
				}
				$ressource->load($ATMdb, $_REQUEST['id']);
				_liste($ATMdb, $emprunt,$ressource);
				break;
			
			case 'view':
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$emprunt->load($ATMdb, $_REQUEST['idEven']);
				_fiche($ATMdb, $emprunt, $ressource, 'view');
				break;
				
			case 'deleteAttribution':
				//$ATMdb->db->debug=true;
				$emprunt->load($ATMdb, $_REQUEST['idEven']);
				$emprunt->delete($ATMdb);
				$ressource->load($ATMdb, $_REQUEST['id']);
				
				?>
				<script language="javascript">
					document.location.href="?id=<?echo $_REQUEST['id'];?>&delete_ok=1";					
				</script>
				<?
				break;
			
		}
	}
	elseif(isset($_REQUEST['id']) && isset($_REQUEST['idEven'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		$emprunt->load($ATMdb, $_REQUEST['idEven']);
		_fiche($ATMdb, $emprunt, $ressource,'view');
	}
	
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $emprunt,$ressource);
	}
	else {
		/*
		 * Liste
		 */
		 //$ATMdb->db->debug=true;
		 $ressource->load($ATMdb, $_REQUEST['id']);
		 _liste($ATMdb, $emprunt, $ressource);
	}
	
	
	$ATMdb->close();
	
	llxFooter();


function _liste(&$ATMdb, &$emprunt, &$ressource) {
	global $langs, $conf, $db, $user;
	
	llxHeader('','Liste des attributions');
	dol_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'attribution', 'Ressource');
	
	printLibelle($ressource);
	
	$r = new TSSRenderControler($emprunt);
	$sql="SELECT DISTINCT e.rowid as 'ID', CONCAT(u.firstname,' ',u.lastname) as 'Utilisateur', 
		DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin', e.commentaire as 'Commentaire'";
	if($user->rights->ressource->ressource->manageAttribution){
		$sql.=",GROUP_CONCAT(CONCAT(' ',code)) as 'Codes analytiques' ,'' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_evenement as e
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as ua ON (e.fk_user = ua.fk_user)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)";
	$sql.=" WHERE e.type='emprunt'
		AND e.fk_rh_ressource=".$ressource->getId();
	if(!$user->rights->ressource->ressource->manageAttribution){
		$sql.=" AND e.fk_user=".$user->id;
		
	}
	$sql.=" GROUP BY ua.fk_user ";
	
	$TOrder = array('Date fin'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&action=view">@val@</a>'
			,'Supprimer'=>"<a style=\"cursor:pointer;\" onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=".$ressource->getId()."&idEven=@ID@&action=deleteAttribution'};\"><img src=\"./img/delete.png\"></a>"
			//,'Supprimer'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&action=deleteAttribution"><img src="./img/delete.png"></a>'
		)
		,'eval'=>array(
			'Utilisateur'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		,'translate'=>array()
		,'hide'=>array('IDRessource')
		,'type'=>array(
			'Date début'=>'date'
			,'Date fin'=>'date'
		)
		,'liste'=>array(
			'titre'=>'Historique des emprunts'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun emprunt à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
		)
		,'orderBy'=>$TOrder
		
	));
	
	if($user->rights->ressource->ressource->manageAttribution){
		?></div><a class="butAction" href="?id=<?=$ressource->getId()?>&action=new">Nouveau</a><?
	}
	
	
	/*
	 * Liste des contrat associé
	 */
	
	$sql="SELECT DISTINCT a.rowid as 'ID',  c.rowid as 'IDContrat' , c.libelle as 'Libellé',
		DATE(c.date_debut) as 'Date début', DATE(c.date_fin) as 'Date fin', a.commentaire as 'Commentaire'";
	
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_contrat_ressource as a
		LEFT JOIN ".MAIN_DB_PREFIX."rh_contrat as c ON (a.fk_rh_contrat = c.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (a.fk_rh_ressource = r.rowid)";
	$sql.=" WHERE 1 
		AND a.fk_rh_ressource=".$ressource->getId();
	
	$TOrder = array('Date début'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$l=new TListviewTBS('listContrat');
	
	print $l->render($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id='.$ressource->getId().'&idAssoc=@ID@">@val@</a>'
			,'Libellé'=>'<a href="contrat.php?id=@IDContrat@">@val@</a>'
			,'Commentaire'=>'<a href="?id='.$ressource->getId().'&idAssoc=@ID@">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre', 'IDContrat')
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
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	
	?><div style="clear:both"></div></div><?
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$emprunt,&$ressource,  $mode) {
	global $db,$user;
	llxHeader('', 'Attibution');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('idEven',$emprunt->getId());
	
	$emprunt->load_liste($ATMdb);
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/attribution.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
				,'entete'=>getLibelle($ressource)
				,'titreNouvelleAttribution'=>load_fiche_titre("Nouvelle attribution",'', 'title.png', 0, '')
				,'titreModificationAttribution'=>load_fiche_titre("Modification d'une attribution",'', 'title.png', 0, '')
			)
			,'NEmprunt'=>array(
				'id'=>$emprunt->getId() //$form->hidden('idEven', $emprunt->getId())
				,'type'=>$form->hidden('type', 'emprunt')
				,'fk_user'=>$form->combo('','fk_user',$emprunt->TUser,$emprunt->fk_user)
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'commentaire'=>$form->zonetexte('','commentaire',$emprunt->commentaire, 80,3)
				,'date_debut'=> $form->calendrier('', 'date_debut', $emprunt->date_debut, 12, 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $emprunt->date_fin, 12, 10)
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->manageAttribution)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'attribution', 'Ressource')
				,'onglet'=>dol_get_fiche_head(array()  , '', 'Attribution')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
