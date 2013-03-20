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
				$feries->set_values($_REQUEST);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				if(isset($_REQUEST['TFerie'])){
				
					foreach($_REQUEST['TFerie'] as $k=>$jour) {
						$feries->TFerie[$k]->set_values($jour);					
					}
				}
				if(isset($_REQUEST['newJour'])) {
					$mode = 'edit';
				}

				$feries->save($ATMdb);
				$feries->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $feries,$mode);
				break;
			
			case 'view':
				$feries->load($ATMdb, $_REQUEST['id']);
				
				_fiche($ATMdb, $feries,'view');
				break;
			case 'deleteJour':
				//$ATMdb->db->debug=true;
				$feries->delJour($ATMdb, $_REQUEST['idJour']);
				$feries->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Le champs a bien été supprimé</div>';
				$mode = 'edit';
				_fiche($ATMdb, $feries,$mode);
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
	getStandartJS();
	
	$r = new TSSRenderControler($feries);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', 
			  r.date_jourOff as 'Jour non travaillé',r.matin as 'Matinée',  r.apresmidi as 'Après-midi'
		FROM  llx_rh_absence_jours_feries as r
		WHERE r.entity=".$conf->entity;
		
	
	$TOrder = array('ID'=>'DESC');
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
			'titre'=>'Liste des jours non travaillés'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucun jour non travaillé"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	?><a class="butAction" href="?id=<?=$feries->getId()?>&action=new">Nouveau</a><div style="clear:both"></div></div><?
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, $feries, $mode) {
	global $db,$user,$idUserCompt, $idComptEnCours;
	llxHeader('','Emploi du temps');

	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $feries->getId());
	echo $form->hidden('action', 'save');
	//echo $form->hidden('fk_user', $_REQUEST['id']?$_REQUEST['id']:$user->id);
	

	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReq="SELECT * FROM `llx_rh_absence_jours_feries`//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	$Tab=array();
	while($ATMdb->Get_line()) {
		
	}
	
	//Champs
	$TFeries=array();
	foreach($feries->TFerie as $k=>$jour){
		$TFeries[$k]=array(
				'id'=>$jour->getId()
				,'date_jourOff'=>$form->calendrier('', 'TFeries['.$k.'][date_jourOff]', $jour->get_date('date_jourOff'), 10)
				,'matin'=>$form->checkbox1('','TFeries['.$k.'][matin]','1',$jour->matin==1?true:false)
				,'apresmidi'=>$form->checkbox1('','TFeries['.$k.'][apresmidi]','1',$jour->apresmidi==1?true:false)
				,'numero'=>$k
			);
	}
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/joursferies.tpl.php'
		,array(
			
		)
		,array(
			'joursFeries'=>array(
				'id'=>$feries->getId()
				,'date_jourOff'=>$form->calendrier('', 'date_jourOff', $feries->get_date('date_jourOff'), 10)
				,'matin'=>$form->checkbox1('','matin','1',$feries->matin==1?true:false)
				,'apresmidi'=>$form->checkbox1('','apresmidi','1',$feries->apresmidi==1?true:false)
				,'commentaire'=>$form->texte('','commentaire',$feries->commentaire, 30,100,'','','-')
			)
			
			/*,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>$userCourant->lastname
				,'firstname'=>$userCourant->firstname
			)*/
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


	
	
