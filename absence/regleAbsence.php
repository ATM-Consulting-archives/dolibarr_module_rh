<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');

	$ATMdb=new TPDOdb;
	$regle=new TRH_RegleAbsence;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$regle->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$regle,'edit');
				
				break;	
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$regle->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,  $regle, 'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$regle->load($ATMdb, $_REQUEST['id']);
				$regle->restrictif=0;
				$regle->set_values($_REQUEST);				
				$regle->save($ATMdb);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb,  $regle,'view');
				break;
			
			case 'view':
				$regle->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,  $regle,'view');
				break;
		
			case 'delete':
				$regle->load($ATMdb, $_REQUEST['id']);
				$regle->delete($ATMdb);
				$mesg = '<div class="ok">La règle a bien été supprimée</div>';
				_liste($ATMdb, $regle);
			
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$regle->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $regle);
	}
	else {
		 _liste($ATMdb, $regle);
	}
	
	
	$ATMdb->close();
	
	
function _liste(&$ATMdb, $regle) {
	global $langs,$conf, $db, $user;	

	llxHeader('','Règles sur les congés');
	print dol_get_fiche_head(reglePrepareHead($regle,'regle')  , 'regle', 'Règles');
		
	$r = new TSSRenderControler($regle);
	$sql="SELECT DISTINCT r.rowid as 'ID', CONCAT(u.firstname,' ',u.lastname) as 'Utilisateur', g.nom as 'Groupe',
		r.typeAbsence, r.nbJourCumulable , r.restrictif as 'Restrictif', '' as 'Supprimer'
		FROM ".MAIN_DB_PREFIX."rh_absence_regle as r
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (r.fk_user = u.rowid)
		LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup as g ON (r.fk_usergroup = g.rowid)
		WHERE r.entity IN (0,".$conf->entity.")";
	
	//echo $sql;
	$TOrder = array('ID'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	
	
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');		
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href=?id=@ID@&action=view&fk_user='.$user->id.'>@val@</a>'
			,'typeAbsence'=>'<a href=?id=@ID@&action=view&fk_user='.$user->id.'>@val@</a>'
			,'Supprimer'=>"<a onclick=\"if (window.confirm('Voulez-vous vraiment supprimer la règle ?')){href='?id=@ID@&fk_user=".$user->id."&action=delete'};\"><img src='./img/delete.png'></a>"
			
		)
		,'translate'=>array(
			'typeAbsence'=>array('rttcumule'=>'RTT Cumulé','rttnoncumule'=>'RTT Non Cumulé', 'conges' => 'Congés', 'maladiemaintenue' => 'Maladie maintenue', 
		'maladienonmaintenue'=>'Maladie non maintenue','maternite'=>'Maternité', 'paternite'=>'Paternité', 
		'chomagepartiel'=>'Chômage Partiel','nonremuneree'=>'Non rémunérée','accidentdetravail'=>'Accident de travail',
		'maladieprofessionnelle'=>'Maladie professionnelle', 'congeparental'=>'Congé parental', 'accidentdetrajet'=>'Accident de trajet',
		'mitempstherapeutique'=>'Mi-temps thérapeutique')
			,'Restrictif'=>array('1'=>'Oui', '0'=>'Non')
		)
		,'hide'=>array()
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des règles sur les demandes d\'absence'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['ID'])
			,'messageNothing'=>"Il n'y a aucune règle à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'title'=>array(
			'typeAbsence'=>'Type d\'absence concerné'
			,'nbJourCumulable'=>'Nombre de jours contigus possible'
		)
		,'eval'=>array('Utilisateur'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
		,'Groupe'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
		)
		,'orderBy'=>$TOrder
		
	));
	
	?><a class="butAction" href="?id=<?=$regle->getId()?>&action=new">Nouvelle règle</a><div style="clear:both"></div></div><?
	$form->end();
	llxFooter();
}	
	
function _fiche(&$ATMdb, $regle, $mode) {
	llxHeader('','Règle sur les Absences', '', '', 0, 0);
	
	global $user,$conf;
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('id', $regle->getId());
	echo $form->hidden('action', 'save');
	
	$regle->load_liste($ATMdb);
	

	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$user->id." AND entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($ATMdb);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('lastname');
	}
	
	
	$TBS=new TTemplateTBS();
	$regle->load_liste($ATMdb);
	print $TBS->render('./tpl/regleAbsence.tpl.php'
		,array()
		,array(
			'newRule'=>array(
				'id'=>$regle->getId()
				,'choixApplication'=>$form->radiodiv('','choixApplication',$regle->TChoixApplication, $regle->choixApplication)
				,'choixApplicationViewMode'=>$regle->TChoixApplication[$regle->choixApplication]
				,'fk_user'=>$form->combo('', 'fk_user',$regle->TUser, $regle->fk_user)
				,'fk_group'=>$form->combo('', 'fk_usergroup',$regle->TGroup, $regle->fk_usergroup)
				,'nbJourCumulable'=>$form->texte('', 'nbJourCumulable', $regle->nbJourCumulable,30 ,255,'','','')
				,'typeAbsence'=>$form->combo('', 'typeAbsence',$regle->TTypeAbsenceAdmin, $regle->typeAbsence)
				,'restrictif'=>$form->checkbox1('','restrictif','1',$regle->restrictif==1?true:false)
				,'titreRegle'=>load_fiche_titre("Règle sur les demandes d'absence",'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
				,'valideurConges'=>$user->rights->absence->myactions->valideurConges&&$estValideur
				,'enregistrerPaieAbsences'=>$user->rights->absence->myactions->enregistrerPaieAbsences&&$estValideur	
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(reglePrepareHead($regle)  , 'regle', 'Règles')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
