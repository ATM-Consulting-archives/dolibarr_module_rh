<?php
	require('config.php');
	require('./class/competence.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('competence@competence');
	$langs->load("users");
	
	$ATMdb=new Tdb;
	$lignecv=new TRH_ligne_cv;
	$formation=new TRH_formation_cv;
	$tagCompetence=new TRH_competence_cv;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'newlignecv':
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->set_values($_REQUEST);
				_ficheCV($ATMdb, $lignecv, 'edit');
				break;
			case 'newformationcv':
				//$ATMdb->db->debug=true;
				$formation->load($ATMdb, $_REQUEST['id']);
				$formation->set_values($_REQUEST);
				_ficheFormation($ATMdb, $formation, $tagCompetence, 'edit');
				break;		
				
			case 'editFormation'	:
				//$ATMdb->db->debug=true;
				$formation->load($ATMdb, $_REQUEST['id']);
				_ficheFormation($ATMdb, $formation,$tagCompetence,'edit');
				break;
				
			case 'editCv'	:
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				_ficheCV($ATMdb, $lignecv, 'edit');
				break;
				
			case 'savecv':
				
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->set_values($_REQUEST);
				$mesg = '<div class="ok">Ligne de CV ajoutée</div>';

				$lignecv->save($ATMdb);
				_liste($ATMdb, $lignecv , $formation);
				//_ficheCV($ATMdb, $lignecv,$mode);
				break;
				
			case 'saveformation':
				
				$formation->load($ATMdb, $_REQUEST['id']);
				$formation->set_values($_REQUEST);
				$mesg = '<div class="ok">Nouvelle formation ajoutée</div>';
				$mode = 'view';

				$formation->save($ATMdb);
				_liste($ATMdb, $lignecv , $formation);
				//_ficheCV($ATMdb, $competence,$mode);
				break;
				
			case 'savecompetence':
				$formation->load($ATMdb, $_REQUEST['idForm']);
				$formation->set_values($_REQUEST);
				$formation->save($ATMdb);
				
				$tagCompetence->load($ATMdb, $_REQUEST['addId']);
				$tagCompetence->set_values($_REQUEST);
				print_r($tagCompetence);
				$tagCompetence->save($ATMdb);
				
				$mesg = '<div class="ok">Nouvelle compétence ajoutée</div>';
				$mode = 'view';

				_ficheFormation($ATMdb, $formation , $tagCompetence, 'edit');
				//_ficheCV($ATMdb, $competence,$mode);
				break;
				
			case 'newCompetence':
				if ($_REQUEST['TNComp']['libelle']!=''){
					
					if($_REQUEST['id']!=0){
						
						$formation->load($ATMdb, $_REQUEST['id']);
						$formation->set_values($_REQUEST);
						$formation->save($ATMdb);
						
						$tagCompetence->set_values($_REQUEST);
						$tagCompetence->libelleCompetence=$_REQUEST['TNComp']['libelle'];
						$tagCompetence->fk_user_formation=$_REQUEST['TNComp']['fk_user_formation'];
						$tagCompetence->save($ATMdb);
					}
					else{
						$formation->set_values($_REQUEST);
						$formation->save($ATMdb);
						
						$tagCompetence->set_values($_REQUEST);
						$tagCompetence->libelleCompetence=$_REQUEST['TNComp']['libelle'];
						$tagCompetence->fk_user_formation=$formation->getId();
						$tagCompetence->save($ATMdb);
					}
					
					_ficheFormation($ATMdb, $formation,$tagCompetence,'edit');
				}
				else{
					$formation->load($ATMdb, $_REQUEST['id']);
					$formation->set_values($_REQUEST);
					$mesg = '<div class="ok">Nouvelle formation ajoutée</div>';
					$mode = 'view';
	
					$formation->save($ATMdb);
					_liste($ATMdb, $lignecv , $formation);
				}
				break;
				
			case 'viewCV':
				$lignecv->load($ATMdb, $_REQUEST['id']);
				_ficheCV($ATMdb, $lignecv, 'view');
				break;
				
			case 'viewFormation':
				$formation->load($ATMdb, $_REQUEST['id']);
				_ficheFormation($ATMdb, $formation, $tagCompetence,'view');
				break;
				
			case 'deleteCV':
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">La ligne de CV a bien été supprimée</div>';
				_liste($ATMdb, $lignecv , $formation);
				break;
				
			case 'deleteFormation':
				//$ATMdb->db->debug=true;
				//on supprime tous les tags de compétences associés à cette formation
				$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_competence_cv WHERE fk_user_formation =".$_REQUEST['id'];
				$ATMdb->Execute($sql);
				
				$formation->load($ATMdb, $_REQUEST['id']);
				$formation->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">La ligne de compétence a bien été supprimée</div>';
				_liste($ATMdb, $lignecv , $formation);
				break;
				
			case 'deleteCompetence':
				//$ATMdb->db->debug=true;
				//on supprime la compétence
				$tagCompetence->load($ATMdb, $_REQUEST['idForm']);
				$tagCompetence->delete($ATMdb, $_REQUEST['idForm']);
				$formation->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="error">Le tag de formation a bien été supprimé</div>';
				_ficheFormation($ATMdb, $formation, $tagCompetence,'edit');
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$lignecv->load($ATMdb, $_REQUEST['id']);
		$formation->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $lignecv, $formation);	
	}
	else {
		//$ATMdb->db->debug=true;
		$lignecv->load($ATMdb, $_REQUEST['id']);
		$formation->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $lignecv, $formation);
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $lignecv, $formation ) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos expériences');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']?$_REQUEST['fk_user']:$user->id);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'competence', $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?=$fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?=$fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?=$fuser->firstname ?></td></tr>
	</tbody></table>
	<br/><?
	
	////////////AFFICHAGE DES LIGNES DE CV 
	$r = new TSSRenderControler($lignecv);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			  date_debut, date_fin, libelleExperience, descriptionExperience,lieuExperience, fk_user, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_ligne_cv
		WHERE fk_user=".$_REQUEST['fk_user']." AND entity=".$conf->entity;

	$TOrder = array('date_fin'=>'ASC');
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
			'libelleExperience'=>'<a href="?id=@ID@&action=viewCV&fk_user='.$fuser->id.'">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=deleteCV&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre', 'fk_user')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Visualisation de votre CV'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune expérience professionnelle"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date Fin'
			,'libelleExperience'=>'Libellé Expérience'
			,'descriptionExperience'=>'Description Expérience'
			,'lieuExperience'=>'Lieu'
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));

		?>
		<a class="butAction" href="?id=0&action=newlignecv&fk_user=<?=$fuser->id?>">Ajouter une expérience</a><div style="clear:both"></div>
		<br/>
		<?
	$form->end();
	
	
	////////////AFFICHAGE DES  FORMATIONS
	$r = new TSSRenderControler($formation);
	$sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
			  date_debut, date_fin, libelleFormation,  commentaireFormation,lieuFormation, date_formationEcheance
			  , CONCAT(CAST(coutFormation as DECIMAL(16,2)),' €') as 'Coût total'
			  , CONCAT(CAST(montantOrganisme as DECIMAL(16,2)),' €') as 'Pris en charge par l\'organisme'
			  , CONCAT(CAST(montantEntreprise as DECIMAL(16,2)),' €') as 'Pris pris en charge par l\'entreprise'
			  , fk_user, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_formation_cv
		WHERE fk_user=".$_REQUEST['fk_user']." AND entity=".$conf->entity;

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
			'libelleFormation'=>'<a href="?id=@ID@&action=viewFormation&fk_user='.$fuser->id.'">@val@</a>'
			,'ID'=>'<a href="?id=@ID@&action=viewFormation&fk_user='.$fuser->id.'">@val@</a>'
			,'Supprimer'=>'<a href="?id=@ID@&action=deleteFormation&fk_user='.$fuser->id.'"><img src="./img/delete.png"></a>'
		)
		,'translate'=>array(
		)
		,'hide'=>array('DateCre','fk_user', 'commentaireFormation')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date', 'date_formationEcheance'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de vos formations effectuées'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune formation suivie"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date Fin'
			,'libelleFormation'=>'Libellé Formation'
			,'competenceFormation'=>'Compétences'
			,'commentaireFormation'=>'Commentaires'
			,'lieuFormation'=>'Lieu'
			,'date_formationEcheance'=>'Date d\'échéance'
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));
	?>
		<a class="butAction" href="?id=0&action=newformationcv&fk_user=<?=$fuser->id?>">Ajouter une formation</a><div style="clear:both"></div>
	<?
	llxFooter();
}	

	
function _ficheCV(&$ATMdb, $lignecv,  $mode) {
	global $db,$user,$langs,$conf;
	llxHeader('','Expériences professionnelles');
	
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $formation->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'competence';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?=$fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?=$fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?=$fuser->firstname ?></td></tr>
	</tbody></table>
	<br/><?
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $lignecv->getId());
	echo $form->hidden('fk_user', $_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);
	echo $form->hidden('action', 'savecv');
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/cv.tpl.php'
		,array(
		)
		,array(
			'cv'=>array(
				'id'=>$lignecv->getId()
				,'date_debut'=>$form->calendrier('', 'date_debut', $lignecv->get_date('date_debut'), 10)
				,'date_fin'=>$form->calendrier('', 'date_fin', $lignecv->get_date('date_fin'), 10)
				,'libelleExperience'=>$form->texte('','libelleExperience',$lignecv->libelleExperience, 30,100,'','','-')
				,'descriptionExperience'=>$form->zonetexte('','descriptionExperience',$lignecv->descriptionExperience, 40,3,'','','-')
				//zonetexte($pLib,$pName,$pVal,$pTaille,$pHauteur=5,$plus='',$class='text',$pId='')
				,'lieuExperience'=>$form->texte('','lieuExperience',$lignecv->lieuExperience, 30,100,'','','-')
			)
			,'userCourant'=>array(
				'id'=>$_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id
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


	
	
function _ficheFormation(&$ATMdb, $formation, $tagCompetence,  $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','Formations');

	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $formation->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'competence';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $formation->getId());
	echo $form->hidden('action', 'saveformation');
	echo $form->hidden('fk_user',$_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);

	$sql="SELECT c.rowid, c.libelleCompetence, c.niveauCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."rh_formation_cv as f 
	WHERE c.fk_user_formation=".$formation->getID(). " AND c.fk_user_formation=f.rowid AND c.fk_user=".$fuser->id;
	
	$k=0;
	$ATMdb->Execute($sql);
	$TTagCompetence=array();
	while($ATMdb->Get_line()) {
			$TTagCompetence[]=array(
				'id'=>$ATMdb->Get_field('rowid')
				,'libelleCompetence'=>$ATMdb->Get_field('libelleCompetence')
				,'niveauCompetence'=>$ATMdb->Get_field('niveauCompetence')
			);
		$k++;
	}
	
	$TNComp=array();
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/formation.tpl.php'
		,array(
			'TCompetence'=>$TTagCompetence
		)
		,array(
			'formation'=>array(
				'id'=>$formation->getId()
				,'date_debut'=>$form->calendrier('', 'date_debut', $formation->get_date('date_debut'), 10)
				,'date_fin'=>$form->calendrier('', 'date_fin', $formation->get_date('date_fin'), 10)
				,'libelleFormation'=>$form->texte('','libelleFormation',$formation->libelleFormation, 30,100,'','','-')
				,'coutFormation'=>$form->texte('','coutFormation',$formation->coutFormation, 15,100,'','','-')
				,'montantOrganisme'=>$form->texte('','montantOrganisme',$formation->montantOrganisme, 15,100,'','','-')
				,'montantEntreprise'=>$form->texte('','montantEntreprise',$formation->montantEntreprise, 15,100,'','','-')
				,'commentaireFormation'=>$form->zonetexte('','commentaireFormation',$lignecv->commentaireFormation, 40,3,'','','-')
				,'lieuFormation'=>$form->texte('','lieuFormation',$formation->lieuFormation, 30,100,'','','-')
				,'date_formationEcheance'=>$form->calendrier('', 'date_formationEcheance', $formation->get_date('date_formationEcheance'), 10)
			)
			,'userCourant'=>array(
				'id'=>$fuser->id
				,'nom'=>$fuser->lastname
				,'prenom'=>$fuser->firstname
			)
			,'user'=>array(
				'id'=>$fuser->id
				,'lastname'=>$fuser->lastname
				,'firstname'=>$fuser->firstname
			)
			,'view'=>array(
				'mode'=>$mode
			)
			,'newCompetence'=>array(
				'hidden'=>$form->hidden('action', 'newCompetence')
				,'id'=>$k
				,'libelleCompetence'=>$form->texte('Libellé','TNComp[libelle]','', 30,100,'','','-')
				,'fk_user_formation'=>$form->hidden('TNComp[fk_user_formation]', $formation->getId())
				,'niveauCompetence'=>$form->combo(' Niveau ','niveauCompetence',$tagCompetence->TNiveauCompetence,'')
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

