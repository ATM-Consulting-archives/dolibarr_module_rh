<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$compteur=new TRH_AdminCompteur;
	
	
		switch(__get('action','view')) {
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
				
				
				$sql="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
					SET rttAcquisAnnuelCumuleInit=".$compteur->rttCumuleInit." 
					WHERE rttMetier LIKE 'cadre' ";
				
				if(!empty($conf->multicompany->enabled) && !empty($conf->multicompany->transverse_mode)) {
					null;
				}
				elseif(!empty($conf->multicompany->enabled)) {
					$sql.=" AND entity=".$conf->entity;
				}	
					 
					
				$ATMdb->Execute($sql);

				$sql="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
					SET date_rttCloture='".date('Y-m-d h:i:s',$compteur->date_rttClotureInit)."', date_congesCloture='".date('Y-m-d h:i:s',$compteur->date_congesClotureInit)."'
					,nombreCongesAcquisMensuel=".$compteur->congesAcquisMensuelInit;	
					
				if(!empty($conf->multicompany->enabled) && !empty($conf->multicompany->transverse_mode)) {
					null;
				}
				elseif(!empty($conf->multicompany->enabled)) {
					$sql.=" AND entity=".$conf->entity;
				}		
				
				$ATMdb->Execute($sql);
				
				
				
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb, $compteur,'view');
			
				break;
			
			case 'view':
			
					$compteur->loadCompteur($ATMdb);
					_fiche($ATMdb, $compteur,'view');
					
				break;

			case 'delete':
				
				break;
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

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/adminConges.tpl.php'
		,array(
			
		)
		,array(
			'compteurGlobal'=>array(
				'rowid'=>$compteur->rowid
				,'date_rttClotureInit'=>$form->calendrier('', 'date_rttClotureInit', $compteur->date_rttClotureInit, 12)
				,'date_congesClotureInit'=>$form->calendrier('', 'date_congesClotureInit', $compteur->date_congesClotureInit, 12)
				,'congesAcquisMensuelInit'=>$form->texte('','congesAcquisMensuelInit',round2Virgule($compteur->congesAcquisMensuelInit),10,50)
				,'rttCumuleInitCadreCpro'=>$form->texte('','rttCumuleInitCadreCpro',round2Virgule($compteur->rttCumuleInit),10,50)	
				/*,'rttCumuleInitCadreCproInfo'=>$form->texte('','rttCumuleInitCadreCproInfo',round2Virgule($compteur->rttCumuleInitCadreCproInfo),10,50)*/	
				
				,'titreConges'=>load_fiche_titre("Congés payés",'', 'title.png', 0, '')
				,'titreRtt'=>load_fiche_titre("RTT",'', 'title.png', 0, '')	
			)
			
			,'userCourant'=>array(
				'id'=>$user->id
				,'lastname'=>$user->lastname
				,'firstname'=>$user->firstname
				,'modifierParamGlobalConges'=>$user->rights->absence->myactions->modifierParamGlobalConges
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(adminCongesPrepareHead( 'compteur')  , 'adminconges', 'Administration des congés')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
