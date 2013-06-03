<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$pointage=new TRH_Pointage;
	
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$pointage->load($ATMdb, $_REQUEST['id']);
				$pointage->set_values($_REQUEST);
				_fiche($ATMdb, $pointage, 'edit');
				break;	
			case 'edit'	:
				$pointage->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $pointage,'edit');
				break;
				
			case 'save':
				$pointage->load($ATMdb, $_REQUEST['id']);
				$pointage->set_values($_REQUEST);
				$mesg = '<div class="ok">Jour non travaillé ajouté</div>';
				$mode = 'view';
				
				$pointage->save($ATMdb);
				$pointage->load($ATMdb, $_REQUEST['id']);
				_liste($ATMdb, $pointage);
				break;
			
			case 'view':
				$pointage->load($ATMdb, $_REQUEST['id']);
				$pointage->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $pointage,'view');
				
				break;
			case 'delete':
				//$ATMdb->db->debug=true;
				$pointage->load($ATMdb, $_REQUEST['id']);
				$pointage->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le jour a bien été supprimé</div>';
				$mode = 'edit';
				_liste($ATMdb, $pointage);
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$pointage->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $pointage);
		
				
	}
	else {
		//$ATMdb->db->debug=true;
		$pointage->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $pointage);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $pointage) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	
	print dol_get_fiche_head(edtPrepareHead($pointage, 'emploitemps')  , 'joursferies', 'Absence');
	//getStandartJS();	
	
	$r = new TSSRenderControler($pointage);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			  date_jourOff, moment as 'Période',  commentaire as 'Commentaire', '' as 'Supprimer'
		FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
		WHERE entity IN (0,".$conf->entity.")";
		
	
	$TOrder = array('ID'=>'DESC');
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
			,'Supprimer'=>$user->rights->absence->myactions->ajoutJourOff?'<a href="?idJour=@ID@&fk_user='.$user->id.'&action=delete"><img src="./img/delete.png"></a>':''
		)
		,'translate'=>array(
			'Période'=>array('matin'=>'Matin','apresmidi'=>'Après-midi','allday'=>'Toute la journée')
		)
		,'hide'=>array('DateCre')
		,'type'=>array('date_jourOff'=>'date')
		,'liste'=>array(
			'titre'=>'Liste des jours non travaillés'
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
		<a class="butAction" href="?id=<?=$user->id?>&action=new">Nouveau</a><div style="clear:both"></div>
		<?
	}
	$form->end();
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, $pointage, $mode) {
	global $db,$user;
	llxHeader('','Pointage collaborateurs');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('action', 'save');
	echo $form->hidden('id', $user->id);

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/pointage.tpl.php'
		,array(
			
		)
		,array(
			'joursFeries'=>array(
				'id'=>$feries->getId()
				,'date_jourOff'=>$form->calendrier('', 'date_jourOff', $feries->date_jourOff, 12)
				,'moment'=>$form->combo('','moment',$feries->TMoment,$feries->moment)
				//,'matin'=>$form->checkbox1('','matin','1',$feries->matin==1?true:false)
				//,'apresmidi'=>$form->checkbox1('','apresmidi','1',$feries->apresmidi==1?true:false)
				,'commentaire'=>$form->texte('','commentaire',$feries->commentaire, 30,100,'','','-')
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


	
	
