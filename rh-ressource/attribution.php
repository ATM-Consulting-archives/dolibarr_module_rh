<?php
	require('config.php');
	require('./class/evenement.class.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	$langs->load('ressource@ressource');
	
	//if (!$user->rights->financement->affaire->read)	{ accessforbidden(); }
	$ATMdb=new Tdb;
	$emprunt=new TRH_Evenement;
	$ressource=new TRH_Ressource;
	
	$mesg = '';
	$error=false;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				/*$ATMdb->db->debug=true;
				$emprunt->set_values($_REQUEST);
				//$emprunt->load($ATMdb, 20);
				$mesg = '<div class="ok">Nouvelle attribution créée</div>';*/
				$ressource->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $emprunt,$ressource, 'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$ressource->load($ATMdb, $_REQUEST['id']);
				$emprunt->load($ATMdb, $_REQUEST['idEven']);
				_fiche($ATMdb, $emprunt,$ressource, 'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;				
				if(isset($_REQUEST['newEmprunt'])){
					//on vérifie que la date choisie ne superpose pas avec les autres emprunts.
					if ($ressource->nouvelEmpruntSeChevauche($ATMdb, $_REQUEST ,$_REQUEST['id']) ){
						$mesg = '<div class="error">Les dates choisies se superposent avec d\'autres attributions.</div>';
						$mode = 'edit';
					}
					else {
						$mesg = '<div class="ok">Attribution ajoutée.</div>';
						$emprunt->set_values($_REQUEST);
						$emprunt->save($ATMdb);
					}
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

				
				?>
				<script language="javascript">
					document.location.href="?id=<?$_REQUEST['id']?>&delete_ok=1";					
				</script>
				<?
				//_fiche($ATMdb, $emprunt, $ressource,$mode);
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
		 _liste($ATMdb, $emprunt, $ressource);
	}
	
	
	$ATMdb->close();
	
	llxFooter();


function _liste(&$ATMdb, &$emprunt, &$ressource) {
	global $langs,$conf, $db;
	
	llxHeader('','Liste des attributions');
		
	dol_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'attribution', 'Ressource');
	//getStandartJS();
	
	$r = new TSSRenderControler($emprunt);
	$sql="SELECT DISTINCT e.rowid as 'ID', u.name as 'Nom', 
		DATE(e.date_debut) as 'Date début', DATE(e.date_fin) as 'Date fin', e.commentaire as 'Commentaire', '' as 'Supprimer'
		FROM ".MAIN_DB_PREFIX."rh_evenement as e 
		LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (e.fk_user = u.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
		WHERE e.entity=".$conf->entity."
		AND e.fk_rh_ressource=".$ressource->getId();
	//echo $sql;
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
			,'Supprimer'=>'<a href="?id='.$ressource->getId().'&idEven=@ID@&action=delete"><img="./img/delete.png"  style="cursor:pointer;" ></a>'
			//'<img="./img/delete.png"  style="cursor:pointer;" >'
		)
		,'translate'=>array()
		,'hide'=>array('IDRessource')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Historique des emprunts'
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
	?><a href="?id=<?=$ressource->getId()?>&idEven=<?=$emprunt->getId()?>&action=new">Nouveau</a><?
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$emprunt,&$ressource,  $mode) {
	global $db,$user;
	llxHeader('', 'Attibution');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $ressource->getId());
	echo $form->hidden('action', 'save');
	/*
	$ressource->load_evenement($ATMdb, array('emprunt'));
	$TEmprunts = array();
	foreach($ressource->TEvenement as $k=>$even){
		$TEmprunts[] = array(
					'id'=>$even->getId()
					,'user'=>$even->TUser[$even->fk_user]
					,'date_debut'=>date("d/m/Y",$even->date_debut)
					,'date_fin'=>date("d/m/Y",$even->date_fin)
					,'commentaire'=>$even->motif
		);
	}
*/
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/attribution.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id'=>$ressource->getId()
			)
			,'NEmprunt'=>array(
				'id'=>$emprunt->getId() //$form->hidden('idEven', $emprunt->getId())
				,'idHidden'=>$form->hidden('idEven', $emprunt->getId())
				,'type'=>$form->hidden('type', 'emprunt')
				,'fk_user'=>$form->combo('','fk_user',$emprunt->TUser,$emprunt->fk_user)
				,'fk_rh_ressource'=> $form->hidden('fk_rh_ressource', $ressource->getId())
				,'commentaire'=>$form->texte('','motif',$emprunt->motif, 30,100,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $emprunt->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $emprunt->get_date('date_fin'), 10)
			)
			,'view'=>array(
				'mode'=>$mode
			/*'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'attribution', 'Ressource')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
