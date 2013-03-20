<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$absence=new TRH_JoursFeries;
	//global $idUserCompt, $idComptEnCours;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {

			case 'edit'	:
				$absence->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $absence,'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				$absence->load($ATMdb, $_REQUEST['id']);
				
				$absence->razCheckbox($ATMdb, $absence);
				$absence->set_values($_REQUEST);
				$absence->save($ATMdb);
				$absence->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Demande enregistrée</div>';
				_fiche($ATMdb, $absence,'view');
				break;
			
			case 'view':
				if(isset($_REQUEST['id'])){
					$idComptEnCours=$_REQUEST['id'];
					$absence->load($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $absence,'view');
				}else{
					
					//récupération compteur en cours
					$sqlReqUser="SELECT rowid, fk_user FROM `llx_rh_absence_emploitemps` where fk_user=".$user->id;
					$ATMdb->Execute($sqlReqUser);
					while($ATMdb->Get_line()) {
								$idComptEnCours=$ATMdb->Get_field('rowid');
						//echo $idComptEnCours;
								$idUserCompt=$ATMdb->Get_field('fk_user');
						//echo $idUserCompt;
					}
					//echo 'allo'.$idComptEnCours.$idUserCompt;
					$absence->load($ATMdb, $idComptEnCours);
					_fiche($ATMdb, $absence,'view');
					
				}
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
	global $db,$user,$idUserCompt, $idComptEnCours;
	llxHeader('','Emploi du temps');

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $_REQUEST['id']?$_REQUEST['id']:$user->id);
	

	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReq="SELECT * FROM `llx_rh_absence_emploitemps` where rowid=".$absence->getId();//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$emploiTemps=new User($db);
				$emploiTemps->lundiam=$ATMdb->Get_field('lundiam');
				$emploiTemps->lundipm=$ATMdb->Get_field('lundipm');
				$emploiTemps->mardiam=$ATMdb->Get_field('mardiam');
				$emploiTemps->mardipm=$ATMdb->Get_field('mardipm');
				$emploiTemps->mercrediam=$ATMdb->Get_field('mercrediam');
				$emploiTemps->mercredipm=$ATMdb->Get_field('mercredipm');
				$emploiTemps->jeudiam=$ATMdb->Get_field('jeudiam');
				$emploiTemps->jeudipm=$ATMdb->Get_field('jeudipm');
				$emploiTemps->vendrediam=$ATMdb->Get_field('vendrediam');
				$emploiTemps->vendredipm=$ATMdb->Get_field('vendredipm');
				$emploiTemps->samediam=$ATMdb->Get_field('samediam');
				$emploiTemps->samedipm=$ATMdb->Get_field('samedipm');
				$emploiTemps->dimancheam=$ATMdb->Get_field('dimancheam');
				$emploiTemps->dimanchepm=$ATMdb->Get_field('dimanchepm');
				$emploiTemps->fk_user=$ATMdb->Get_field('fk_user');
				
				$horaires=new User($db);
				$horaires->lundi_heuredam=$ATMdb->Get_field('lundi_heuredam');
				$horaires->lundi_heurefam=$ATMdb->Get_field('lundi_heurefam');
				$horaires->lundi_heuredpm=$ATMdb->Get_field('lundi_heuredpm');
				$horaires->lundi_heurefpm=$ATMdb->Get_field('lundi_heurefpm');
				
				$horaires->mardi_heuredam=$ATMdb->Get_field('mardi_heuredam');
				$horaires->mardi_heurefam=$ATMdb->Get_field('mardi_heurefam');
				$horaires->mardi_heuredpm=$ATMdb->Get_field('mardi_heuredpm');
				$horaires->mardi_heurefpm=$ATMdb->Get_field('mardi_heurefpm');
				
				$horaires->mercredi_heuredam=$ATMdb->Get_field('mercredi_heuredam');
				$horaires->mercredi_heurefam=$ATMdb->Get_field('mercredi_heurefam');
				$horaires->mercredi_heuredpm=$ATMdb->Get_field('mercredi_heuredpm');
				$horaires->mercredi_heurefpm=$ATMdb->Get_field('mercredi_heurefpm');
				
				$horaires->jeudi_heuredam=$ATMdb->Get_field('jeudi_heuredam');
				$horaires->jeudi_heurefam=$ATMdb->Get_field('jeudi_heurefam');
				$horaires->jeudi_heuredpm=$ATMdb->Get_field('jeudi_heuredpm');
				$horaires->jeudi_heurefpm=$ATMdb->Get_field('jeudi_heurefpm');
				
				$horaires->vendredi_heuredam=$ATMdb->Get_field('vendredi_heuredam');
				$horaires->vendredi_heurefam=$ATMdb->Get_field('vendredi_heurefam');
				$horaires->vendredi_heuredpm=$ATMdb->Get_field('vendredi_heuredpm');
				$horaires->vendredi_heurefpm=$ATMdb->Get_field('vendredi_heurefpm');
				
				$horaires->samedi_heuredam=$ATMdb->Get_field('samedi_heuredam');
				$horaires->samedi_heurefam=$ATMdb->Get_field('samedi_heurefam');
				$horaires->samedi_heuredpm=$ATMdb->Get_field('samedi_heuredpm');
				$horaires->samedi_heurefpm=$ATMdb->Get_field('samedi_heurefpm');
				
				$horaires->dimanche_heuredam=$ATMdb->Get_field('dimanche_heuredam');
				$horaires->dimanche_heurefam=$ATMdb->Get_field('dimanche_heurefam');
				$horaires->dimanche_heuredpm=$ATMdb->Get_field('dimanche_heuredpm');
				$horaires->dimanche_heurefpm=$ATMdb->Get_field('dimanche_heurefpm');
	}
	
	//récupération informations utilisateur dont on modifie le compte
	$sqlReqUser="SELECT * FROM `llx_user` where rowid=".$emploiTemps->fk_user;//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($db);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('name');
	}
	
	 
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/joursferies.tpl.php'
		,array(
			
			
		)
		,array(
			'planning'=>array(
				//checkbox1($pLib,$pName,$pVal,$checked=false,$plus='',$class='',$id='',$order='case_after'){
				'lundiam'=>$form->checkbox1('','lundiam','1',$emploiTemps->lundiam==1?true:false)
				,'lundipm'=>$form->checkbox1('','lundipm','1',$emploiTemps->lundipm==1?true:false)
				,'mardiam'=>$form->checkbox1('','mardiam','1',$emploiTemps->mardiam==1?true:false)
				,'mardipm'=>$form->checkbox1('','mardipm','1',$emploiTemps->mardipm==1?true:false)
				,'mercrediam'=>$form->checkbox1('','mercrediam','1',$emploiTemps->mercrediam==1?true:false)
				,'mercredipm'=>$form->checkbox1('','mercredipm','1',$emploiTemps->mercredipm==1?true:false)
				,'jeudiam'=>$form->checkbox1('','jeudiam','1',$emploiTemps->jeudiam==1?true:false)
				,'jeudipm'=>$form->checkbox1('','jeudipm','1',$emploiTemps->jeudipm==1?true:false)
				,'vendrediam'=>$form->checkbox1('','vendrediam','1',$emploiTemps->vendrediam==1?true:false)
				,'vendredipm'=>$form->checkbox1('','vendredipm','1',$emploiTemps->vendredipm==1?true:false)
				,'samediam'=>$form->checkbox1('','samediam','1',$emploiTemps->samediam==1?true:false)
				,'samedipm'=>$form->checkbox1('','samedipm','1',$emploiTemps->samedipm==1?true:false)
				,'dimancheam'=>$form->checkbox1('','dimancheam','1',$emploiTemps->dimancheam==1?true:false)
				,'dimanchepm'=>$form->checkbox1('','dimanchepm','1',$emploiTemps->dimanchepm==1?true:false)
				,'fk_user'=>$emploiTemps->fk_user
				,'id'=>$absence->getId()
			)
			,'horaires'=>array(
					'lundi_heuredam'=>$form->texte('','lundi_heuredam',$horaires->lundi_heuredam,10,50,'',$class="text", $default='')
					,'lundi_heurefam'=>$form->texte('','lundi_heurefam',$horaires->lundi_heurefam,10,50,'',$class="text", $default='')
					,'lundi_heuredpm'=>$form->texte('','lundi_heuredpm',$horaires->lundi_heuredpm,10,50,'',$class="text", $default='')
					,'lundi_heurefpm'=>$form->texte('','lundi_heurefpm',$horaires->lundi_heurefpm,10,50,'',$class="text", $default='')
					
					,'mardi_heuredam'=>$form->texte('','mardi_heuredam',$horaires->mardi_heuredam,10,50,'',$class="text", $default='')
					,'mardi_heurefam'=>$form->texte('','mardi_heurefam',$horaires->mardi_heurefam,10,50,'',$class="text", $default='')
					,'mardi_heuredpm'=>$form->texte('','mardi_heuredpm',$horaires->mardi_heuredpm,10,50,'',$class="text", $default='')
					,'mardi_heurefpm'=>$form->texte('','mardi_heurefpm',$horaires->mardi_heurefpm,10,50,'',$class="text", $default='')
					
					,'mercredi_heuredam'=>$form->texte('','mercredi_heuredam',$horaires->mercredi_heuredam,10,50,'',$class="text", $default='')
					,'mercredi_heurefam'=>$form->texte('','mercredi_heurefam',$horaires->mercredi_heurefam,10,50,'',$class="text", $default='')
					,'mercredi_heuredpm'=>$form->texte('','mercredi_heuredpm',$horaires->mercredi_heuredpm,10,50,'',$class="text", $default='')
					,'mercredi_heurefpm'=>$form->texte('','mercredi_heurefpm',$horaires->mercredi_heurefpm,10,50,'',$class="text", $default='')
					
					,'jeudi_heuredam'=>$form->texte('','jeudi_heuredam',$horaires->jeudi_heuredam,10,50,'',$class="text", $default='')
					,'jeudi_heurefam'=>$form->texte('','jeudi_heurefam',$horaires->jeudi_heurefam,10,50,'',$class="text", $default='')
					,'jeudi_heuredpm'=>$form->texte('','jeudi_heuredpm',$horaires->jeudi_heuredpm,10,50,'',$class="text", $default='')
					,'jeudi_heurefpm'=>$form->texte('','jeudi_heurefpm',$horaires->jeudi_heurefpm,10,50,'',$class="text", $default='')
					
					,'vendredi_heuredam'=>$form->texte('','vendredi_heuredam',$horaires->vendredi_heuredam,10,50,'',$class="text", $default='')
					,'vendredi_heurefam'=>$form->texte('','vendredi_heurefam',$horaires->vendredi_heurefam,10,50,'',$class="text", $default='')
					,'vendredi_heuredpm'=>$form->texte('','vendredi_heuredpm',$horaires->vendredi_heuredpm,10,50,'',$class="text", $default='')
					,'vendredi_heurefpm'=>$form->texte('','vendredi_heurefpm',$horaires->vendredi_heurefpm,10,50,'',$class="text", $default='')
					
					,'samedi_heuredam'=>$form->texte('','samedi_heuredam',$horaires->samedi_heuredam,10,50,'',$class="text", $default='')
					,'samedi_heurefam'=>$form->texte('','samedi_heurefam',$horaires->samedi_heurefam,10,50,'',$class="text", $default='')
					,'samedi_heuredpm'=>$form->texte('','samedi_heuredpm',$horaires->samedi_heuredpm,10,50,'',$class="text", $default='')
					,'samedi_heurefpm'=>$form->texte('','samedi_heurefpm',$horaires->samedi_heurefpm,10,50,'',$class="text", $default='')
					
					,'dimanche_heuredam'=>$form->texte('','dimanche_heuredam',$horaires->dimanche_heuredam,10,50,'',$class="text", $default='')
					,'dimanche_heurefam'=>$form->texte('','dimanche_heurefam',$horaires->dimanche_heurefam,10,50,'',$class="text", $default='')
					,'dimanche_heuredpm'=>$form->texte('','dimanche_heuredpm',$horaires->dimanche_heuredpm,10,50,'',$class="text", $default='')
					,'dimanche_heurefpm'=>$form->texte('','dimanche_heurefpm',$horaires->dimanche_heurefpm,10,50,'',$class="text", $default='')
					
				
				
			)
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>$userCourant->lastname
				,'firstname'=>$userCourant->firstname
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'emploitemps')  , 'joursferies', 'Absence')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
