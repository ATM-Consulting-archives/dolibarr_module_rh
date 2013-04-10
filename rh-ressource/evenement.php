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
				?>
				<script language="javascript">
					document.location.href="?id=<?echo $_REQUEST['id'];?>&delete_ok=1";					
				</script>
				<?
				/*$ressource->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">L\'attribution a bien été supprimée.</div>';
				_liste($ATMdb, $evenement, $ressource, $_REQUEST['type']);*/
				break;
				
			case 'afficherListe':
				$ressource->load($ATMdb, $_REQUEST['id']);
				_liste($ATMdb, $evenement, $ressource, $_REQUEST['type']);
				break;
			
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$ressource->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $evenement,$ressource);
	}
	else {
		/*
		 * Liste
		 */
		 //$ATMdb->db->debug=true;
		 _liste($ATMdb, $evenement,$ressource);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
function _liste(&$ATMdb, &$evenement, &$ressource, $type = "all") {
	global $conf,$user;	
	llxHeader('','Liste des emprunts');
	?><div class="fiche"><?	
	
	dol_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'evenement', 'Ressource');
	// btsubmit($pLib,$pName,$plus="")
	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	//$form->Set_typeaff($mode);
	echo $form->hidden('action', 'afficherListe');
	echo $form->hidden('id',$ressource->getId());
	$evenement->load_liste_type($ATMdb, $ressource);

	?>
	<table>
		<tr>
			<td> Type d'évenement à afficher : </td>
			<td> <? echo $form->combo('','type', $evenement->TType ,$type) ?> </td>
			<td> <? echo $form->btsubmit('Valider','Valider'); ?>	</td>
		</tr>
	</table>
	<?
	//'onclick=\'document.location.href="?id='.$ressource->getId().'&action=afficherListe "\''
	
	$r = new TSSRenderControler($evenement);
	switch($type){
		case 'all' :
			$jointureChamps ="CONCAT(u.firstname,' ',u.name) as 'Utilisateur', 
				DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin', e.type as 'Type',
				e.motif as 'Motif', e.commentaire as 'Commentaire', CONCAT (CAST(e.coutTTC as DECIMAL(16,2)), ' €') as 'Coût TTC', 
				CONCAT (CAST(e.coutEntrepriseTTC as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise TTC', t.taux as 'TVA' ";
			$jointureType = " AND e.type<>'emprunt' ";
			break;
		 case 'appel' :
			$jointureChamps =" CONCAT(u.firstname,' ',u.name) as 'Utilisateur', 
				DATE(e.date_debut) as 'Date', e.appelHeure as 'Heure', e.appelNumero as 'Numéro appelé', 
				e.appelDureeReel as 'Durée/Volume réel', e.appelDureeFacturee as 'Durée/Volume facturé',
				e.motif as 'Motif', CONCAT (CAST(e.coutHT as DECIMAL(16,2)), ' €') as 'Montant HT' ";
			$jointureType = " AND e.type='appel' ";
			break;
		case 'facture':		
			$jointureChamps ="CONCAT(u.firstname,' ',u.name) as 'Utilisateur', 
				DATE(e.date_debut) as 'Date', DATE(e.date_fin) as 'Traité le',
				e.motif as 'Garage', e.commentaire as 'Commentaire', CONCAT (CAST(e.coutTTC as DECIMAL(16,2)), ' €') as 'Coût TTC', 
				CONCAT (CAST(e.coutEntrepriseTTC as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise TTC', t.taux as 'TVA'";
			$jointureType = " AND e.type='facture' ";
			break;
		default :
			$jointureChamps ="CONCAT(u.firstname,' ',u.name) as 'Utilisateur', 
				DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin',
				e.motif as 'Motif', e.commentaire as 'Commentaire', CONCAT (CAST(e.coutTTC as DECIMAL(16,2)), ' €') as 'Coût', 
				CONCAT (CAST(e.coutEntrepriseTTC as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise TTC', t.taux as 'TVA' ,
				CONCAT (CAST(e.coutEntrepriseHT as DECIMAL(16,2)), ' €') as 'Coût pour l\'entreprise HT'";
			$jointureType = " AND e.type='".$type."'";
		break;
		}	
	
	$sql = "SELECT DISTINCT e.rowid as 'ID', ".$jointureChamps;
	if($user->rights->ressource->ressource->manageEvents){
		$sql.=",'' as 'Supprimer'";
	}
	$sql.=" FROM ".MAIN_DB_PREFIX."rh_evenement as e
			LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
			LEFT JOIN ".MAIN_DB_PREFIX."c_tva as t ON (e.tva = t.rowid)
			WHERE e.entity=".$conf->entity."
			AND e.fk_rh_ressource=".$ressource->getId().$jointureType;
	if(!$user->rights->ressource->ressource->manageEvents){
		$sql.=" AND e.fk_user=".$user->id;
	}
	
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
			'ID'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&action=view">@val@</a>'
			,'Supprimer'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&type='.$type.'&action=deleteEvent"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array('Type'=>$evenement->TType)
		,'hide'=>array()
		,'type'=>array(
			'Date début'=>'date'
			,'Date fin'=>'date'
		)
		,'liste'=>array(
			'titre'=>'Liste des événements de type '.$evenement->TType[$type]
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>'Il n\'y a aucun événement à afficher'
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			//,'id'=>$ressource->getId()
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	if($user->rights->ressource->ressource->manageEvents){
	?><a class="butAction" href="?id=<?=$ressource->getId()?>&action=new">Nouveau</a><?
	}
	?>
	<div style="clear:both"></div></div><?
	$form->end();
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}	

function _fiche(&$ATMdb, &$evenement,&$ressource,  $mode) {
	global $db,$user,$conf;
	llxHeader('', 'Evénement');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('idEven',$evenement->getId());

	$evenement->load_liste($ATMdb);
	$evenement->load_liste_type($ATMdb, $ressource);
	$TBS=new TTemplateTBS();
	$tab = array_splice ( $evenement->TType , 1);
	print $TBS->render('./tpl/evenement.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
			)
			
			,'NEvent'=>array(
				'id'=>$evenement->getId()
				,'user'=>$form->combo('','fk_user',$evenement->TUser,$evenement->fk_user)
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'commentaire'=>$form->texte('','commentaire',$evenement->commentaire, 30,100,'','','-')
				,'motif'=>$form->texte('','motif',$evenement->motif, 30,100,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $evenement->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $evenement->get_date('date_fin'), 10)
				,'type'=>$form->combo('', 'type', $tab, $evenement->type)
				,'coutTTC'=>$form->texte('', 'coutTTC', $evenement->coutTTC, 10,10)
				,'coutEntrepriseTTC'=>$form->texte('', 'coutEntrepriseTTC', $evenement->coutEntrepriseTTC, 10,10)
				,'TVA'=>$form->combo('','TVA',$evenement->TTVA,$evenement->TVA)
				,'coutEntrepriseHT'=>($evenement->coutEntrepriseTTC)*(1-(0.01*$evenement->TTVA[$evenement->TVA]))
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->manageEvents)
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($evenement, 'evenement', $ressource)  , 'fiche', 'Evénement')
			)
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
		
}

	
	
