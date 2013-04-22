<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$compteur=new TRH_AdminCompteur;
	
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				_fiche($ATMdb, $compteur,'edit');
				
				break;	
			case 'edit'	:
				$compteur->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $compteur,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$compteur->load($ATMdb, $_REQUEST['id']);
				$compteur->set_values($_REQUEST);
				$compteur->save($ATMdb);
				$compteur->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb, $compteur,'view');
			
				break;
			
			case 'view':
			
				if(isset($_REQUEST['id'])){
					$compteur->load($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $compteur,'view');
				}else{
					//récupération compteur en cours
					$sqlReqUser="SELECT rowid FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$user->id;
					$ATMdb->Execute($sqlReqUser);
					while($ATMdb->Get_line()) {
								$idComptEnCours=$ATMdb->Get_field('rowid');
					}
					$compteur->load($ATMdb, $idComptEnCours);
					_fiche($ATMdb, $compteur,'view');
					
				}
				break;

			case 'delete':
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		
	}

	$ATMdb->close();
	
	llxFooter();
	

	
function _fiche(&$ATMdb, &$compteur, $mode) {
	global $db,$user;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $compteur->getId());
	echo $form->hidden('action', 'save');
	//echo $form->hidden('fk_user', $_REQUEST['id']);

	
	//////////////////////////récupération des informations des congés précédents (N-1) de l'utilisateur courant : 
	$sqlReq="SELECT * FROM `".MAIN_DB_PREFIX."rh_admin_compteur` where rowid=1";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
				$compteurGlobal=new User($db);
				$compteurGlobal->rowid=$ATMdb->Get_field('rowid');
				$compteurGlobal->congesAcquisMensuelInit=$ATMdb->Get_field('congesAcquisMensuelInit');
				$compteurGlobal->date_rttClotureInit=$ATMdb->Get_field('date_rttClotureInit');
				$compteurGlobal->date_congesClotureInit=$ATMdb->Get_field('date_congesClotureInit');
	}
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/adminConges.tpl.php'
		,array(
			
		)
		,array(
			'compteurGlobal'=>array(
				'rowid'=>$compteurGlobal->rowid
				,'date_rttClotureInit'=>$form->calendrier('', 'date_rttClotureInit', $compteur->get_date('date_rttClotureInit'), 10)
				,'date_congesClotureInit'=>$form->calendrier('', 'date_congesClotureInit', $compteur->get_date('date_congesClotureInit'), 10)
				,'congesAcquisMensuelInit'=>$form->texte('','congesAcquisMensuelInit',round2Virgule($compteurGlobal->congesAcquisMensuelInit),10,50,'',$class="text", $default='')
			)
		
			,'userCourant'=>array(
				'id'=>$user->id
				,'lastname'=>$user->lastname
				,'firstname'=>$user->firstname
				,'modifierParamGlobalConges'=>$user->rights->absence->myactions->modifierParamGlobalConges
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminCongesPrepareHead($compteur, 'compteur')  , 'adminconges', 'Administration des congés')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
