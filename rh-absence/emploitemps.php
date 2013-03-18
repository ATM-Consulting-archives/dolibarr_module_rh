<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$absence=new TRH_EmploiTemps;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$absence->set_values($_REQUEST);
				_fiche($ATMdb, $absence,'edit');
				
				break;	
			case 'edit'	:
				$absence->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $absence,'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->set_values($_REQUEST);
				$absence->save($ATMdb);
				$absence->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Demande enregistrée</div>';
				_fiche($ATMdb, $absence,'view');
			
				break;
			
			case 'view':
				$absence->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $absence,'view');
				break;

			case 'delete':
				$absence->load($ATMdb, $_REQUEST['id']);
				//$ATMdb->db->debug=true;
				//avant de supprimer, on récredite les heures d'absences qui avaient été décomptées. 
				recrediterHeure($absence,$ATMdb);
				$absence->delete($ATMdb);
				
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?
				break;
				
			
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		//$ATMdb->db->debug=true;
		_liste($ATMdb, $absence);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	getStandartJS();
	
	$r = new TSSRenderControler($absence);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre',DATE(r.date_debut) as 'Date début', DATE(r.date_fin) as 'Date Fin', 
			  r.libelle as 'Type absence',r.fk_user as 'Utilisateur Courant',  r.libelleEtat as 'Statut demande'
		FROM llx_rh_absence as r
		WHERE r.fk_user=".$user->id." AND r.entity=".$conf->entity;
		
	
	$TOrder = array('Statut demande'=>'DESC');
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
			'titre'=>'Liste de vos absences'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune absence à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$absence, $mode) {
	global $db,$user;
	llxHeader('','Emploi du temps');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $user->id);
	

	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReq="SELECT * FROM `llx_rh_absence_emploitemps` where fk_user=".$user->id;//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$emploiTemps=new User($db);
				$emploiTemps->lundiam=$ATMdb->Get_field('lundiam');
	}
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/emploitemps.tpl.php'
		,array(
			
			
		)
		,array(
			'emploiTemps'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'lundiam'=>$emploiTemps->lundiam
				,'fk_user'=>$_REQUEST['id']
			)
			,'userCourant'=>array(
				'id'=>$user->id
				,'lastname'=>$user->lastname
				,'firstname'=>$user->firstname
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'emploitemps')  , 'fiche', 'Absence')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
