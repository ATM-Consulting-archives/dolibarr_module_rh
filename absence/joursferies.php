<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$feries=new TRH_JoursFeries;
	$emploiTemps=new TRH_EmploiTemps;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				$feries->set_values($_REQUEST);
				_fiche($ATMdb, $feries,$emploiTemps, 'edit');
				break;	
			case 'edit'	:
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				$feries->load($ATMdb, $_REQUEST['idJour']);
				_fiche($ATMdb, $feries,$emploiTemps,'edit');
				break;
				
			case 'save':
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				$feries->load($ATMdb, $_REQUEST['idJour']);	
				//print_r($feries);		
				$feries->set_values($_REQUEST);
				
				$existeDeja=$feries->testExisteDeja($ATMdb, $feries);
				
				if(!$existeDeja){
					$mesg = '<div class="error">Jour non travaillé ajouté</div>';
					$feries->save($ATMdb);
					$feries->load($ATMdb, $_REQUEST['idJour']);	
					_fiche($ATMdb, $feries , $emploiTemps,'view');
				}else{
					$mesg = '<div class="error">Création impossible : il existe déjà un jour non travaillé à cette date</div>';
					_fiche($ATMdb, $feries , $emploiTemps,'edit');
				}
				
				
				break;
			
			case 'view':
				$feries->load($ATMdb, $_REQUEST['idJour']);
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $feries,$emploiTemps,'view');
				
				
				break;
			case 'delete':
				//$ATMdb->db->debug=true;
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				$feries->load($ATMdb, $_REQUEST['idJour']);
				$feries->delete($ATMdb, $_REQUEST['idJour']);
				$mesg = '<div class="ok">Le jour a bien été supprimé</div>';
				$mode = 'edit';
				_liste($ATMdb, $feries , $emploiTemps);
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$emploiTemps->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $feries , $emploiTemps);
		
				
	}
	else {
		//$ATMdb->db->debug=true;
		$emploiTemps->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $feries, $emploiTemps);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $feries, $emploiTemps ) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	
	print dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'joursferies', 'Absence');
	//getStandartJS();	
	
	$r = new TSSRenderControler($feries);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			 date_jourOff, moment as 'Période',  commentaire as 'Commentaire', '' as 'Supprimer'
		FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
		WHERE entity IN (0,".$conf->entity.")";
		
	
	$TOrder = array('date_jourOff'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'date_jourOff'=>'<a href="?idJour=@ID@&fk_user='.$user->id.'&action=view">@val@</a>'
			,'Supprimer'=>$user->rights->absence->myactions->ajoutJourOff?"<a onclick=\"if (window.confirm('Voulez-vous vraiment supprimer le jour férié ?')){href='?idJour=@ID@&fk_user=".$user->id."&action=delete'};\"><img src='./img/delete.png'></a>":''
		) 
		,'translate'=>array(
			'Période'=>array('matin'=>'Matin','apresmidi'=>'Après-midi','allday'=>'Toute la journée')
		)
		,'hide'=>array('DateCre')
		,'type'=>array('date_jourOff'=>'date')
		,'liste'=>array(
			'titre'=>'Liste des jours fériés ou non travaillés'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucun jour non travaillé"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)//theme/rh/img/search.png
		,'title'=>array(
			'date_jourOff'=>'Jour non travaillé'
		)
		,'search'=>array(
			'date_jourOff'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));
	if($user->rights->absence->myactions->ajoutJourOff=="1"){
		?>
		<a class="butAction" href="?fk_user=<?=$user->id?>&action=new">Nouveau</a><div style="clear:both"></div>
		<?
	}
	$form->end();
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, $feries, $emploiTemps, $mode) {
	global $db,$user,$idUserCompt, $idComptEnCours;
	llxHeader('','Emploi du temps');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('idJour', $feries->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('id', $user->id);

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/joursferies.tpl.php'
		,array(
			
		)
		,array(
			'joursFeries'=>array(
				'id'=>$feries->getId()
				,'date_jourOff'=>$form->calendrier('', 'date_jourOff', $feries->date_jourOff, 12)
				,'moment'=>$form->combo('','moment',$feries->TMoment,$feries->moment)
				,'commentaire'=>$form->zonetexte('','commentaire',$feries->commentaire, 40,3,'','','-')
				,'titreCreate'=>load_fiche_titre("Nouveau jour férié",'', 'title.png', 0, '')
				,'titreAction'=>$_GET['action']
				,'titreVisu'=>load_fiche_titre("Visualisation du jour férié",'', 'title.png', 0, '')
			)
			,'userCourant'=>array(
				'id'=>$user->id
				,'droitAjoutJour'=>$user->rights->absence->myactions->ajoutJourOff
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'joursferies', 'Absence')
			)
		)	
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
