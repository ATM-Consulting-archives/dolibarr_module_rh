<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
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
				
				//on sélectionne tous les users qui sont des cadres de C'PRO ou C'PRO Groupe, 
				//puis ceux qui sont des cadres de C'PRO info
				$sql="SELECT fk_user FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as e, ".MAIN_DB_PREFIX."entity as t
				WHERE e.societeRtt=t.rowid AND t.label LIKE '%info%'";
				$ATMdb->Execute($sql);
				$TUserCproInfo=array();
				While($ATMdb->Get_line()) {
							$TUserCproInfo[]=$ATMdb->Get_field('fk_user');
				}
				
				$sql="SELECT fk_user FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as e, ".MAIN_DB_PREFIX."entity as t
				WHERE e.societeRtt=t.rowid AND t.label LIKE '%groupe%'";
				$ATMdb->Execute($sql);
				$TUserCproGroupe=array();
				While($ATMdb->Get_line()) {
							$TUserCproGroupe[]=$ATMdb->Get_field('fk_user');
				}
				
				// on met à jour les compteurs des cpro info
				$sql="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
					SET rttAcquisAnnuelCumuleInit=".$compteur->rttCumuleInitCadreCpro." 
					WHERE rttMetier LIKE 'cadre'
					 AND fk_user IN(".implode(',', $TUserCproInfo).")";
					
				$ATMdb->Execute($sql);

				//on mets à jour les compteurs des cpro groupe
				$sql="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
					SET rttAcquisAnnuelCumuleInit=".$compteur->rttCumuleInitCadreCpro." 
					WHERE rttMetier LIKE 'cadre'
					 AND fk_user IN(".implode(',', $TUserCproGroupe).")";
				$ATMdb->Execute($sql);
				
				//on récupère la liste des utilisateurs 
				$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
				$ATMdb->Execute($sql);
				$TUser=array();
				While($ATMdb->Get_line()) {
							$TUser[]=$ATMdb->Get_field('rowid');
				}
				
				foreach($TUser as $fk_user){
					//on modifie les infos des compteurs de chaque employé
					$sql="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
					SET date_rttCloture='".date('Y-m-d h:i:s',$compteur->date_rttClotureInit)."', date_congesCloture='".date('Y-m-d h:i:s',$compteur->date_congesClotureInit)."'
					,nombreCongesAcquisMensuel=".$compteur->congesAcquisMensuelInit." WHERE fk_user=".$fk_user;
					$ATMdb->Execute($sql);
				}
				
				
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb, $compteur,'view');
			
				break;
			
			case 'view':
			
					//récupération compteur admin
					$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_admin_compteur";
					$ATMdb->Execute($sql);
					if($ATMdb->Get_line()) {
								$idComptEnCours=$ATMdb->Get_field('rowid');
					}
					$compteur->load($ATMdb, $idComptEnCours);
					_fiche($ATMdb, $compteur,'view');
					
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


	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/adminConges.tpl.php'
		,array(
			
		)
		,array(
			'compteurGlobal'=>array(
				'rowid'=>$compteur->rowid
				,'date_rttClotureInit'=>$form->calendrier('', 'date_rttClotureInit', $compteur->date_rttClotureInit, 12)
				,'date_congesClotureInit'=>$form->calendrier('', 'date_congesClotureInit', $compteur->date_congesClotureInit, 12)
				,'congesAcquisMensuelInit'=>$form->texte('','congesAcquisMensuelInit',round2Virgule($compteur->congesAcquisMensuelInit),10,50,'',$class="text", $default='')
				,'rttCumuleInitCadreCpro'=>$form->texte('','rttCumuleInitCadreCpro',round2Virgule($compteur->rttCumuleInitCadreCpro),10,50,'',$class="text", $default='')	
				,'rttCumuleInitCadreCproInfo'=>$form->texte('','rttCumuleInitCadreCproInfo',round2Virgule($compteur->rttCumuleInitCadreCproInfo),10,50,'',$class="text", $default='')	
				
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


	
	
