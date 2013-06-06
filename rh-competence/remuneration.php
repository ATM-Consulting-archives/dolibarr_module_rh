<?php
	require('config.php');
	require('./class/competence.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('competence@competence');
	$langs->load("users");
	
	$ATMdb=new Tdb;
	$remuneration=new TRH_remuneration;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				//$ATMdb->db->debug=true;
				$remuneration->set_values($_REQUEST);
				_fiche($ATMdb, $remuneration, 'edit');
				break;
				
			case 'edit'	:
				//$ATMdb->db->debug=true;
				$remuneration->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $remuneration,'edit');
				break;
				
			case 'save':
				$remuneration->load($ATMdb, $_REQUEST['id']);
				$remuneration->set_values($_REQUEST);
				$mesg = '<div class="ok">La ligne de rémunération a bien été enregistrée</div>';
				$remuneration->save($ATMdb);
				$remuneration->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $remuneration, 'view');
				break;
				
			case 'view':
				$remuneration->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $remuneration, 'view');
				break;
				
			case 'delete':
				//$ATMdb->db->debug=true;
				$remuneration->load($ATMdb, $_REQUEST['id']);
				$remuneration->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">La ligne de rémunération a bien été supprimée</div>';
				_liste($ATMdb, $remuneration);
				break;
				
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$remuneration->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $remuneration);	
	}
	else {
		
		//$ATMdb->db->debug=true;
		$remuneration->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb,$remuneration);
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $remuneration) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos rémunérations');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'remuneration', $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?=$fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?=$fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?=$fuser->firstname ?></td></tr>
	</tbody></table>
	<br/><?
	
	////////////AFFICHAGE DES LIGNES DE REMUNERATION
	$r = new TSSRenderControler($remuneration);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre', DATE_FORMAT(r.date_debutRemuneration, '%d/%m/%Y') as 'Date début', DATE_FORMAT(r.date_finRemuneration, '%d/%m/%Y') as 'Date fin', 
			CONCAT(u.firstname,' ',u.name) as 'Utilisateur' ,
			  CONCAT( ROUND(r.bruteAnnuelle,2),' €') as 'Rémunération brute annuelle',  
			  CONCAT( ROUND(r.salaireMensuel,2),' €') as 'Salaire mensuel', r.fk_user, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_remuneration as r, ".MAIN_DB_PREFIX."user as u
		WHERE r.fk_user=".$_REQUEST['fk_user']." AND r.entity=".$conf->entity." AND u.rowid=r.fk_user";
	
	$TOrder = array('date_debutRemuneration'=>'ASC');
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
			'Rémunération brute annuelle'=>'<a href="?id=@ID@&action=view&fk_user='.$fuser->id.'">@val@</a>'
			,'Date début'=>'<a href="?id=@ID@&action=view&fk_user='.$fuser->id.'">@val@</a>'
			,'Supprimer'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration?'<a href="?id=@ID@&action=delete&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>':''
		)
		,'translate'=>array(
			
		)
		,'hide'=>array('DateCre', 'fk_user')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Visualisation de vos rémunérations'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune rémunération enregistrée"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'title'=>array(
			'date_debutRemuneration'=>'Date début'
		)
		,'search'=>array(
		)
		,'orderBy'=>$TOrder
		
	));
		if($user->rights->curriculumvitae->myactions->ajoutRemuneration==1){
		?>
		<a class="butAction" href="?&action=new&fk_user=<?=$fuser->id?>">Ajouter une rémunération</a><div style="clear:both"></div>
		
		<?
		}
	$form->end();
	
	llxFooter();
}	

	
function _fiche(&$ATMdb, $remuneration,  $mode) {
	global $db,$user,$langs,$conf;
	llxHeader('','Vos Rémunérations');
	
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $remuneration->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'remuneration';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $remuneration->getId());
	echo $form->hidden('fk_user', $_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);
	echo $form->hidden('action', 'save');

	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/remuneration.tpl.php'
		,array(
		)
		,array(
			'remuneration'=>array(
				'id'=>$remuneration->getId()
				,'date_entreeEntreprise'=>$form->calendrier('', 'date_entreeEntreprise', $remuneration->date_entreeEntreprise, 12)
				,'date_debutRemuneration'=>$form->calendrier('', 'date_debutRemuneration', $remuneration->date_debutRemuneration, 12)
				,'date_finRemuneration'=>$form->calendrier('', 'date_finRemuneration', $remuneration->date_finRemuneration, 12)
				,'bruteAnnuelle'=>$form->texte('','bruteAnnuelle',$remuneration->bruteAnnuelle, 30,100,'','','-')
				,'salaireMensuel'=>$form->texte('','salaireMensuel',$remuneration->salaireMensuel, 30,100,'','','-')
				,'primeAnciennete'=>$form->texte('','primeAnciennete',$remuneration->primeAnciennete, 30,100,'','','-')
				,'participation'=>$form->texte('','participation',$remuneration->participation, 30,100,'','','-')
				,'autre'=>$form->texte('','autre',$remuneration->autre, 30,100,'','','-')
				,'prevoyancePartSalariale'=>$form->texte('','prevoyancePartSalariale',$remuneration->prevoyancePartSalariale, 30,100,'','','-')
				,'prevoyancePartPatronale'=>$form->texte('','prevoyancePartPatronale',$remuneration->prevoyancePartPatronale, 30,100,'','','-')
				,'urssafPartSalariale'=>$form->texte('','urssafPartSalariale',$remuneration->urssafPartSalariale, 30,100,'','','-')
				,'urssafPartPatronale'=>$form->texte('','urssafPartPatronale',$remuneration->urssafPartPatronale, 30,100,'','','-')
				,'retraitePartSalariale'=>$form->texte('','retraitePartSalariale',$remuneration->retraitePartSalariale, 30,100,'','','-')
				,'retraitePartPatronale'=>$form->texte('','retraitePartPatronale',$remuneration->retraitePartPatronale, 30,100,'','','-')
				,'mutuellePartSalariale'=>$form->texte('','mutuellePartSalariale',$remuneration->mutuellePartSalariale, 30,100,'','','-')
				,'mutuellePartPatronale'=>$form->texte('','mutuellePartPatronale',$remuneration->mutuellePartPatronale, 30,100,'','','-')
				,'diversPartSalariale'=>$form->texte('','diversPartSalariale',$remuneration->diversPartSalariale, 30,100,'','','-')
				,'diversPartPatronale'=>$form->texte('','diversPartPatronale',$remuneration->diversPartPatronale, 30,100,'','','-')
				,'totalRemPatronale'=>$remuneration->diversPartPatronale+$remuneration->mutuellePartPatronale+$remuneration->retraitePartPatronale+$remuneration->urssafPartPatronale+$remuneration->prevoyancePartPatronale
				,'totalRemSalariale'=>$remuneration->diversPartSalariale+$remuneration->mutuellePartSalariale+$remuneration->retraitePartSalariale+$remuneration->urssafPartSalariale+$remuneration->prevoyancePartSalariale
				,'commentaire'=>$form->texte('','commentaire',$remuneration->commentaire, 30,100,'','','-')
				,'fk_user'=>$remuneration->fk_user
				,'lieuExperience'=>$form->texte('','lieuExperience',$remuneration->lieuExperience, 30,100,'','','-')
			)
			,'userCourant'=>array(
				'id'=>$_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id
				,'ajoutRem'=>$user->rights->curriculumvitae->myactions->ajoutRemuneration
			)
			,'user'=>array(
				'id'=>$fuser->id
				,'lastname'=>$fuser->lastname
				,'firstname'=>$fuser->firstname
			)
			,'view'=>array(
				'mode'=>$mode
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}
