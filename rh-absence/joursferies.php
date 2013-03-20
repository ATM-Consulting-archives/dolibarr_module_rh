<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$feries=new TRH_JoursFeries;
	
	//global $idUserCompt, $idComptEnCours;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$feries->set_values($_REQUEST);
				_fiche($ATMdb, $feries,'edit');
				break;	
			case 'edit'	:
				$feries->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $feries,'edit');
				break;
				
			case 'save':
				$feries->load($ATMdb, $_REQUEST['id']);			
				$feries->razCheckbox($ATMdb, $absence);
				$feries->set_values($_REQUEST);
				$mesg = '<div class="ok">Jour non travaillé ajouté</div>';
				$mode = 'view';

				$feries->save($ATMdb);
				$feries->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $feries,$mode);
				break;
			
			case 'view':
				$feries->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $feries,'view');
				break;
			case 'delete':
				//$ATMdb->db->debug=true;
				$feries->load($ATMdb, $_REQUEST['id']);
				$feries->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le jour a bien été supprimé</div>';
				$mode = 'edit';
				_liste($ATMdb, $feries);
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		_liste($ATMdb, $feries);
	}
	else {
		//$ATMdb->db->debug=true;
		_liste($ATMdb, $feries);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $feries) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	?><div class="fiche"><?	
	dol_get_fiche_head(absencePrepareHead($feries, 'emploitemps')  , 'joursferies', 'Absence');
	//getStandartJS();	
	
	$r = new TSSRenderControler($feries);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			  date_jourOff, moment as 'Période',  commentaire as 'Commentaire', '' as 'Supprimer'
		FROM  llx_rh_absence_jours_feries
		WHERE entity=".$conf->entity;
		
	
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
			'date_jourOff'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=delete"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array(
			'Période'=>array('matin'=>'Matin','apresmidi'=>'Après-midi','allday'=>'Toute la journée')
		)
		,'hide'=>array('DateCre')
		,'type'=>array('date_jourOff'=>'date')
		,'liste'=>array(
			'titre'=>'Liste des jours non travaillés'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
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
	?><a class="butAction" href="?id=<?=$feries->getId()?>&action=new">Nouveau</a><div style="clear:both"></div></div><?
	
	$form->end();
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, $feries, $mode) {
	global $db,$user,$idUserCompt, $idComptEnCours;
	llxHeader('','Emploi du temps');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $feries->getId());
	echo $form->hidden('action', 'save');

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/joursferies.tpl.php'
		,array(
			
		)
		,array(
			'joursFeries'=>array(
				'id'=>$feries->getId()
				,'date_jourOff'=>$form->calendrier('', 'date_jourOff', $feries->get_date('date_jourOff'), 10)
				,'moment'=>$form->combo('','moment',$feries->TMoment,$feries->moment)
				//,'matin'=>$form->checkbox1('','matin','1',$feries->matin==1?true:false)
				//,'apresmidi'=>$form->checkbox1('','apresmidi','1',$feries->apresmidi==1?true:false)
				,'commentaire'=>$form->texte('','commentaire',$feries->commentaire, 30,100,'','','-')
			)

			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($feries, 'emploitemps')  , 'joursferies', 'Absence')
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
