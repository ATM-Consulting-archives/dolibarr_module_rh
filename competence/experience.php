<?php
	require('config.php');
	require('./class/competence.class.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
	
	$langs->load('competence@competence');
	$langs->load("users");
	
	if (!$user->rights->curriculumvitae->myactions->skill) accessforbidden();
	
	$ATMdb=new TPDOdb;
	$lignecv=new TRH_ligne_cv;
	$formation=new TRH_formation_cv;
	$tagCompetence=new TRH_competence_cv;
	$tagCompetenceCV=new TRH_competence_cv;
	$dif=new TRH_dif;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'newlignecv':
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->set_values($_REQUEST);
				_ficheCV($ATMdb, $lignecv, $tagCompetenceCV,'edit');
				break;
			case 'newformationcv':
				//$ATMdb->db->debug=true;
				$formation->load($ATMdb, $_REQUEST['id']);
				$formation->set_values($_REQUEST);
				_ficheFormation($ATMdb, $formation, $tagCompetence, 'edit');
				break;
			case 'newDIF':
				//$ATMdb->db->debug=true;
				$dif->load($ATMdb, $_REQUEST['id']);
				$dif->set_values($_REQUEST);
				_ficheDIF($ATMdb, $dif, 'edit');
				break;
				
			case 'editFormation':
				//$ATMdb->db->debug=true;
				$formation->load($ATMdb, $_REQUEST['id']);
				_ficheFormation($ATMdb, $formation,$tagCompetence,'edit');
				break;
			case 'editCv':
				//$ATMdb->db->debug=true;
				$lignecv->load($ATMdb, $_REQUEST['id']);
				_ficheCV($ATMdb, $lignecv, $tagCompetenceCV,'edit');
				break;
			case 'editDIF':
				//$ATMdb->db->debug=true;
				$dif->load($ATMdb, $_REQUEST['id']);
				_ficheDIF($ATMdb, $dif,'edit');
				break;
				
			case 'savecv':
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->set_values($_REQUEST);
				$mesg = '<div class="ok">Ligne de CV ajoutée</div>';
				$lignecv->save($ATMdb);
				_liste($ATMdb, $lignecv, $formation, $dif);
				//_ficheCV($ATMdb, $lignecv,$mode);
				break;
			case 'saveformation':
				$formation->load($ATMdb, $_REQUEST['id']);
				$formation->set_values($_REQUEST);
				$mesg = '<div class="ok">Nouvelle formation ajoutée</div>';
				$mode = 'view';
				$formation->save($ATMdb);
				_liste($ATMdb, $lignecv, $formation, $dif);
				break;
			case 'savecompetence':
				$formation->load($ATMdb, $_REQUEST['idForm']);
				$formation->set_values($_REQUEST);
				$formation->save($ATMdb);
				
				$tagCompetence->load($ATMdb, $_REQUEST['addId']);
				$tagCompetence->set_values($_REQUEST);
				$tagCompetence->save($ATMdb);
				
				$mesg = '<div class="ok">Nouvelle compétence ajoutée</div>';
				$mode = 'view';

				_ficheFormation($ATMdb, $formation , $tagCompetence, 'edit');
				//_ficheCV($ATMdb, $competence,$mode);
				break;
			case 'saveDIF':
				$dif->load($ATMdb, $_REQUEST['id']);
				$dif->set_values($_REQUEST);
				$mesg = '<div class="ok">Nouvelle fiche de DIF ajoutée</div>';
				$mode = 'view';
				$dif->save($ATMdb);
				_liste($ATMdb, $lignecv, $formation, $dif);
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
					_liste($ATMdb, $lignecv, $formation, $dif);
				}
				break;
				
			case 'newCompetenceCV':
				
				if ($_REQUEST['TNComp']['libelle']!=''){
					if($_REQUEST['id']!=0){						
						$lignecv->load($ATMdb, $_REQUEST['id']);
						$lignecv->set_values($_REQUEST);
						$lignecv->save($ATMdb);
						
						$tagCompetenceCV->set_values($_REQUEST);
						$tagCompetenceCV->libelleCompetence=$_REQUEST['TNComp']['libelle'];
						$tagCompetenceCV->fk_user_lignecv=$_REQUEST['TNComp']['fk_user_lignecv'];
						$tagCompetenceCV->save($ATMdb);
					}
					else{
						$lignecv->set_values($_REQUEST);
						$lignecv->save($ATMdb);
						
						$tagCompetenceCV->set_values($_REQUEST);
						$tagCompetenceCV->libelleCompetence=$_REQUEST['TNComp']['libelle'];
						$tagCompetenceCV->fk_user_lignecv=$lignecv->getId();
						$tagCompetenceCV->save($ATMdb);
					}
					
					_ficheCV($ATMdb, $lignecv,$tagCompetenceCV,'edit');
				}
				else{
					$lignecv->load($ATMdb, $_REQUEST['id']);
					$lignecv->set_values($_REQUEST);
					$mesg = '<div class="ok">Nouvelle expérience ajoutée</div>';
					$mode = 'view';
					$lignecv->save($ATMdb);
					_liste($ATMdb, $lignecv, $formation, $dif);
				}
				break;
				
			case 'viewCV':
				$lignecv->load($ATMdb, $_REQUEST['id']);
				_ficheCV($ATMdb, $lignecv, $tagCompetence,'view');
				break;
			case 'viewFormation':
				$formation->load($ATMdb, $_REQUEST['id']);
				_ficheFormation($ATMdb, $formation, $tagCompetence,'view');
				break;
			case 'viewDIF':
				$dif->load($ATMdb, $_REQUEST['id']);
				_ficheDIF($ATMdb, $dif, 'view');
				break;
				
			case 'deleteCV':
				//$ATMdb->db->debug=true;
				$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_competence_cv WHERE fk_user_lignecv =".$_REQUEST['id'];
				$ATMdb->Execute($sql);
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$lignecv->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">La ligne de CV a bien été supprimée</div>';
				_liste($ATMdb, $lignecv, $formation, $dif);
				break;
			case 'deleteFormation':
				//$ATMdb->db->debug=true;
				//on supprime tous les tags de compétences associés à cette formation
				$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_competence_cv WHERE fk_user_formation =".$_REQUEST['id'];
				$ATMdb->Execute($sql);
				
				$formation->load($ATMdb, $_REQUEST['id']);
				$formation->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">La ligne de compétence a bien été supprimée</div>';
				_liste($ATMdb, $lignecv, $formation, $dif);
				break;
			case 'deleteDIF':
				//$ATMdb->db->debug=true;
				$dif->load($ATMdb, $_REQUEST['id']);
				$dif->delete($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">La ligne de DIF a bien été supprimée</div>';
				_liste($ATMdb, $lignecv, $formation, $dif);
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
				
			case 'deleteCompetenceCV':
				//$ATMdb->db->debug=true;
				//on supprime la compétence
				$tagCompetenceCV->load($ATMdb, $_REQUEST['idForm']);
				$tagCompetenceCV->delete($ATMdb, $_REQUEST['idForm']);
				$lignecv->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="error">Le tag de formation a bien été supprimé</div>';
				_ficheCV($ATMdb, $lignecv, $tagCompetenceCV,'edit');
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$lignecv->load($ATMdb, $_REQUEST['id']);
		$formation->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $lignecv, $formation, $dif);	
	}
	else {
		//$ATMdb->db->debug=true;
		$lignecv->load($ATMdb, $_REQUEST['id']);
		$formation->load($ATMdb, $_REQUEST['id']);
		_liste($ATMdb, $lignecv, $formation, $dif);
	}
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, $lignecv, $formation, $dif) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos expériences');
	
	$fuser = new User($db);
	$fuser->fetch($_REQUEST['fk_user']?$_REQUEST['fk_user']:$user->id);
	$fuser->getrights();

	$head = user_prepare_head($fuser);
	dol_fiche_head($head, 'competence', $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?php echo $fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?php echo $fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?php echo $fuser->firstname ?></td></tr>
	</tbody></table>
	<br/><?php
	
	////////////AFFICHAGE DES LIGNES DE CV 
	$r = new TSSRenderControler($lignecv);
	$sql="SELECT cv.rowid as 'ID', cv.date_cre as 'DateCre', 
			  cv.date_debut, cv.date_fin, cv.libelleExperience, cv.descriptionExperience, GROUP_CONCAT(tag.libelleCompetence) as 'Compétences' ,cv.lieuExperience, cv.fk_user, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_ligne_cv cv LEFT JOIN  ".MAIN_DB_PREFIX."rh_competence_cv tag ON (tag.fk_user_lignecv = cv.rowid) 
		WHERE cv.fk_user=".$_REQUEST['fk_user']." AND cv.entity=".$conf->entity
		." GROUP BY cv.rowid";

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
		,'hide'=>array('DateCre', 'fk_user', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Visualisation de votre CV'
			,'image'=>img_picto('','title.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune expérience professionnelle"
			
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
		<a class="butAction" href="?id=0&action=newlignecv&fk_user=<?php echo $fuser->id?>">Ajouter une expérience</a><div style="clear:both"></div>
		<br/>
		<?php
	$form->end();
	
	
	////////////AFFICHAGE DES  FORMATIONS
	$r = new TSSRenderControler($formation);
	$sql="SELECT cv.rowid as 'ID', cv.date_cre as 'DateCre', 
			  cv.date_debut, date_fin, cv.libelleFormation,  GROUP_CONCAT(tag.libelleCompetence) as 'Compétences', cv.commentaireFormation,cv.lieuFormation
			  , CONCAT(CAST(cv.coutFormation as DECIMAL(16,2)),' €') as 'Coût total'
			  , CONCAT(CAST(cv.montantOrganisme as DECIMAL(16,2)),' €') as 'Pris en charge par l\'organisme'
			  , CONCAT(CAST(cv.montantEntreprise as DECIMAL(16,2)),' €') as 'Pris pris en charge par l\'entreprise'
			  , cv.fk_user, '' as 'Supprimer'
		FROM   ".MAIN_DB_PREFIX."rh_formation_cv cv  LEFT JOIN  ".MAIN_DB_PREFIX."rh_competence_cv tag ON (tag.fk_user_formation = cv.rowid) 
		WHERE cv.fk_user=".$_REQUEST['fk_user']." AND cv.entity=".$conf->entity;

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
		,'hide'=>array('DateCre','fk_user', 'commentaireFormation', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date', 'date_formationEcheance'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de vos formations effectuées'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Aucune formation suivie"
			
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
		<a class="butAction" href="?id=0&action=newformationcv&fk_user=<?php echo $fuser->id?>">Ajouter une formation</a><div style="clear:both"></div>
		<br/>
	<?php
	
		
	llxFooter();
}	

	
function _ficheCV(&$ATMdb, $lignecv, $tagCompetence, $mode) {
	global $db,$user,$langs,$conf;
	llxHeader('','Expériences professionnelles');
	
	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $lignecv->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'competence';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	?><table width="100%" class="border"><tbody>
		<tr><td width="25%" valign="top">Réf.</td><td><?php echo $fuser->id ?></td></tr>
		<tr><td width="25%" valign="top">Nom</td><td><?php echo $fuser->lastname ?></td></tr>
		<tr><td width="25%" valign="top">Prénom</td><td><?php echo $fuser->firstname ?></td></tr>
	</tbody></table>
	<br/><?php
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $lignecv->getId());
	echo $form->hidden('fk_user', $_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);
	echo $form->hidden('action', 'savecv');
	
	
	$sql="SELECT c.rowid, c.libelleCompetence, c.niveauCompetence FROM ".MAIN_DB_PREFIX."rh_competence_cv as c, ".MAIN_DB_PREFIX."rh_ligne_cv as f 
	WHERE c.fk_user_lignecv=".$lignecv->getID(). " AND c.fk_user_lignecv=f.rowid AND c.fk_user=".$fuser->id;
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
	print $TBS->render('./tpl/cv.tpl.php'
		,array(
			'TCompetence'=>$TTagCompetence
		)
		,array(
			'cv'=>array(
				'id'=>$lignecv->getId()
				,'date_debut'=>$form->calendrier('', 'date_debut', $lignecv->date_debut, 12)
				,'date_fin'=>$form->calendrier('', 'date_fin', $lignecv->date_fin, 12)
				,'libelleExperience'=>$form->texte('','libelleExperience',$lignecv->libelleExperience, 50,100,'','','-')
				,'descriptionExperience'=>$form->zonetexte('','descriptionExperience',$lignecv->descriptionExperience, 44,3,'','','-')
				,'lieuExperience'=>$form->texte('','lieuExperience',$lignecv->lieuExperience, 50,100,'','','-')
				,'titre'=>load_fiche_titre("Expérience professionnelle",'', 'title.png', 0, '')
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
			,'newCompetence'=>array(
				'hidden'=>$form->hidden('action', 'newCompetenceCV')
				,'id'=>$k
				,'libelleCompetence'=>$form->texte('Libellé','TNComp[libelle]','', 40,100,'','','-')
				,'fk_user_lignecv'=>$form->hidden('TNComp[fk_user_lignecv]', $lignecv->getId())
				,'niveauCompetence'=>$form->combo(' Niveau ','niveauCompetence',$tagCompetence->TNiveauCompetence,'')
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
				,'date_debut'=>$form->calendrier('', 'date_debut', $formation->date_debut, 12)
				,'date_fin'=>$form->calendrier('', 'date_fin', $formation->date_fin, 12)
				,'libelleFormation'=>$form->texte('','libelleFormation',$formation->libelleFormation, 50,100,'','','')
				,'coutFormation'=>$form->texte('','coutFormation',$formation->coutFormation, 10,50,'','','0')
				,'montantOrganisme'=>$form->texte('','montantOrganisme',$formation->montantOrganisme, 10,50,'','','0')
				,'montantEntreprise'=>$form->texte('','montantEntreprise',$formation->montantEntreprise, 10,50,'','','0')
				,'commentaireFormation'=>$form->zonetexte('','commentaireFormation',$lignecv->commentaireFormation, 45,3,'','','')
				,'lieuFormation'=>$form->texte('','lieuFormation',$formation->lieuFormation, 50,100,'','','')
				,'date_formationEcheance'=>$form->calendrier('', 'date_formationEcheance', $formation->date_formationEcheance, 12)
				,'titre'=>load_fiche_titre("Description de la formation",'', 'title.png', 0, '')
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
				,'libelleCompetence'=>$form->texte('Libellé','TNComp[libelle]','', 40,100,'','','-')
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



function _ficheDIF(&$ATMdb, $dif, $mode) {
	global $db,$user, $langs, $conf;
	llxHeader('','DIF');

	$fuser = new User($db);
	$fuser->fetch(isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : $dif->fk_user);
	$fuser->getrights();
	
	$head = user_prepare_head($fuser);
	$current_head = 'competence';
	dol_fiche_head($head, $current_head, $langs->trans('Utilisateur'),0, 'user');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $dif->getId());
	echo $form->hidden('action', 'saveDIF');
	echo $form->hidden('fk_user',$_REQUEST['fk_user'] ? $_REQUEST['fk_user'] : $user->id);
	echo $form->hidden('entity', $conf->entity);
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/dif.tpl.php'
		,array(
		)
		,array(
			'dif'=>array(
				'id'=>$dif->getId()
				,'annee'=>$form->texte('','annee',$dif->annee, 10,50,'','','-')
				,'nb_heures_acquises'=>$form->texte('','nb_heures_acquises',$dif->nb_heures_acquises, 10,50,'','','-')
				,'nb_heures_prises'=>$form->texte('','nb_heures_prises',$dif->nb_heures_prises, 10,50,'','','-')
				,'nb_heures_restantes'=>$form->texte('','nb_heures_restantes',$dif->nb_heures_restantes, 10,50,'','','-')
				,'titre'=>load_fiche_titre("Fiche de DIF",'', 'title.png', 0, '')
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
				,'userRight'=>((int)$user->rights->curriculumvitae->myactions->gererDif)
			)
		)	
	);
	
	echo $form->end_form();
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

