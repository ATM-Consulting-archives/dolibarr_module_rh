<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$absence=new TRH_Absence;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$absence->set_values($_REQUEST);
				_fiche($ATMdb, $absence,'edit');	
				break;	

			case 'save':
				//$ATMdb->db->debug=true;
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->set_values($_REQUEST);
				$existeDeja=$absence->testExisteDeja($ATMdb, $absence);
				if($existeDeja===false){
					
						if($user->rights->absence->myactions->presenceAutoValidate)$absence->etat='Validee';
						else $absence->etat='Avalider';
						
						$absence->niveauValidation=1;
						
						$typeAbs = new TRH_TypeAbsence;
						$typeAbs->load_by_type($ATMdb, $absence->type);
						
						//if ($typeAbs->isPresence) {
							//$absence->duree = $absence->calculDureePresence($ATMdb);
						//} else {
							$absence->duree = $absence->calculDureeAbsenceParAddition($ATMdb);
						//}

						if($absence->save($ATMdb)) {
							if($absence->avertissementInfo) setEventMessage($absence->avertissementInfo, 'warnings');
							
							$absence->load($ATMdb, $_REQUEST['id']);
							if($absence->fk_user==$user->id){	//on vérifie si l'absence a été créée par l'user avant d'envoyer un mail
								mailConges($absence,true);
								mailCongesValideur($ATMdb,$absence,true);
							}
							
							$mesg = $langs->trans('RegisteredPresence');
							setEventMessage($mesg);
							
							_fiche($ATMdb, $absence,'view');	
						}
						else{
							$errors='';
							foreach($absence->errors as $err) $errors.=$err.'<br />';
							
							$mesg = $errors;
							setEventMessage($errors);
							
							_fiche($ATMdb, $absence,'edit');
							
						}
				
				}else{
					$popinExisteDeja = '<div class="error">' . $langs->trans('ImpossibleCreation') . ' : ' . $langs->trans('PresenceAbsenceAlreadyExistDuringThisPeriod', date('d/m/Y', strtotime($existeDeja[0])), date('d/m/Y',  strtotime($existeDeja[1]))) . '</div>';
					
					_fiche($ATMdb, $absence,'edit');
				}
				break;
			
			case 'view':
				$absence->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $absence,'view');
				break;

			case 'delete':
				$absence->load($ATMdb, $_REQUEST['id']);
				//$ATMdb->db->debug=true;
				//avant de supprimer, on récredite les heures d'absences qui avaient été décomptées. (que si l'absence n'a pas été refusée, dans quel cas 
				//les heures seraient déjà recréditées)
				$absence->recrediterHeure($ATMdb);
				$absence->delete($ATMdb);
				
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?php
				break;


			case 'saveComment':
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->commentaireValideur=$_REQUEST['commentValid'];
				$absence->save($ATMdb);
				_fiche($ATMdb, $absence,'view');
				break;
				
			case 'listeValidation' : 
				_listeValidation($ATMdb, $absence);
				break;
				
			case 'listeAdmin' : 
				_listeAdmin($ATMdb, $absence);
				break;
				
			case 'accept':
				$absence->load($ATMdb, $_REQUEST['id']);
				
				$absence->setAcceptee($ATMdb, $user->id, true);
				
				$mesg = $langs->trans('PresenceRequestAccepted');
				setEventMessage($mesg);
				
				_ficheCommentaire($ATMdb, $absence,'edit');
				break;
				
			case 'niveausuperieur':
				$absence->load($ATMdb, $_REQUEST['id']);
				
				$absence->niveauValidation++;
				$absence->save($ATMdb);
				
				mailConges($absence, true);
				
				$mesg = $langs->trans('PresenceRequestSentToSuperior');
				setEventMessage($mesg);
				
				_fiche($ATMdb, $absence,'view');
				break;
				
			case 'refuse':
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->setRefusee($ATMdb,true);
				
				$mesg = $langs->trans('DeniedRequest');
				setEventMessage($mesg);
				
				_ficheCommentaire($ATMdb, $absence,'edit');
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		//$ATMdb->db->debug=true;
		_liste($ATMdb, $absence);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('', $langs->trans('PeriodsPresenceList'));
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', $langs->trans('Absence'));

	//getStandartJS();
	
	$r = new TSSRenderControler($absence);

	//LISTE D'ABSENCES DU COLLABORATEUR
	$sql="SELECT a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut , a.date_fin, 
			a.libelle,a.fk_user,  a.fk_user, u.login, u.firstname, u.lastname,
			a.etat, a.avertissement
			FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
			WHERE a.fk_user=".$user->id." AND u.rowid=a.fk_user AND isPresence=1";
	
	
	$TOrder = array('date_debut'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');		
	//print $page;
	//echo $form->hidden('action', 'listeValidation');
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelle'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array('Statut demande'=>array(
			'Refusée'=>'<b style="color:#A72947">' . $langs->trans('Refused') . '</b>',
			'En attente de validation'=>'<b style="color:#5691F9">' . $langs->trans('WaitingValidation') . '</b>' , 
			'Acceptée'=>'<b style="color:#30B300">' . $langs->trans('Accepted') . '</b>')
			,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="' . $langs->trans('DoNotRespectRules') . '"></img>')
			,'etat'=>$absence->TEtat
		)
		,'hide'=>array('DateCre', 'fk_user', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>$langs->trans('ListOfAbsence')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=> $langs->trans('MessageNothingAbsence')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=> $langs->trans('StartDate')
			,'date_fin'=> $langs->trans('EndDate')
			,'avertissement'=> $langs->trans('Rules')
			,'libelle'=> $langs->trans('AbsenceType')
			,'firstname'=> $langs->trans('FirstName')
			,'lastname'=> $langs->trans('LastName')
			,'login'=> $langs->trans('Login')
			,'etat'=> $langs->trans('RequestStatus')
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			,'date_fin'=>array('recherche'=>'calendar')
			,'libelle'=>true
			,"firstname"=>true
			,"name"=>true
			,"login"=>true
			,'etat'=>$absence->TEtat
		)
		,'eval'=>array(
			'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
			,'etat'=>'_setColorEtat("@val@")'
			
		)
		,'orderBy'=>$TOrder
		
	));
	?><a class="butAction" href="?id=<?=$absence->getId()?>&action=new"><?php echo $langs->trans('NewRequest'); ?></a><div style="clear:both"></div><?php
	$form->end();
	
	
	llxFooter();
}	

function _listeAdmin(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('', $langs->trans('ListeAllAbsences'));
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', $langs->trans('Absence'));
	//getStandartJS();

	
	$r = new TSSRenderControler($absence);
	
	//droits d'admin : accès à toutes les absences sur la liste

	$sql="SELECT a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut , a.date_fin, 
		 	a.libelle, ROUND(a.duree ,1) as 'duree', a.fk_user,  a.fk_user, u.login, u.firstname, u.lastname,
		  	a.etat, a.avertissement
			FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
			WHERE u.rowid=a.fk_user";
	
	
	$TOrder = array('date_debut'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');		
	echo $form->hidden('action', 'listeAdmin');
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'libelle'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array(
			'avertissement'=>array('1'=>'<img src="./img/warning.png" title="' . $langs->trans('DoNotRespectRules') . '"></img>')
			,'etat'=>$absence->TEtat
		)
		,'hide'=>array('DateCre', 'fk_user', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=> $langs->trans('ListeAllCollabAbsences')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=> $langs->trans('MessageNothingAbsence')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			,'etat'=>$absence->TEtat
			
		)
		,'title'=>array(
			'date_debut'=> $langs->trans('StartDate')
			,'date_fin'=> $langs->trans('EndDate')
			,'avertissement'=> $langs->trans('Rules')
			,'libelle'=>$langs->trans('AbsenceType')
			,'firstname'=> $langs->trans('FirstName')
			,'lastname'=> $langs->trans('LastName')
			,'login'=> $langs->trans('Login')
			,'duree'=>$langs->trans('DurationInDays')
			,'etat'=> $langs->trans('RequestStatus')
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			,'date_fin'=>array('recherche'=>'calendar')
			,'libelle'=>true
			,"firstname"=>true
			,"name"=>true
			,"login"=>true
			,'etat'=>$absence->TEtat
		)
		,'eval'=>array(
			'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
			,'etat'=>'_setColorEtat("@val@")'
		)
		,'orderBy'=>$TOrder
		
	));
	?><a class="butAction" href="?id=<?=$absence->getId()?>&action=new"><?php echo $langs->trans('NewRequest'); ?></a><div style="clear:both"></div><?php
	$form->end();
	
	
	llxFooter();
}	
function _setColorEtat($val) {
	global $langs;
	return strtr($val,array(
				'Refusée'=>'<b style="color:#A72947">' . $langs->trans('Refused') . '</b>',
				'En attente de validation'=>'<b style="color:#5691F9">' . $langs->trans('WaitingValidation') . '</b>' , 
				'Acceptée'=>'<b style="color:#30B300">' . $langs->trans('Accepted') . '</b>'
	));
}
	
function _listeValidation(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('', $langs->trans('ListOfAbsence'));
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', $langs->trans('Absence'));
	//getStandartJS();
 
 
 	//LISTE DES GROUPES À VALIDER
 	$sql=" SELECT DISTINCT fk_usergroup, nbjours, validate_himself, level
 			FROM `".MAIN_DB_PREFIX."rh_valideur_groupe`
			WHERE fk_user=".$user->id." 
			AND type='Conges' AND pointeur !=1 ";
			//AND entity IN (0,".$conf->entity.")";

	$ATMdb->Execute($sql);
	$TabGroupe=array();
	$k=0;
	while($ATMdb->Get_line()) {
				$TabGroupe[$k]['fk_usergroup']=$ATMdb->Get_field('fk_usergroup');
				$TabGroupe[$k]['nbjours']=$ATMdb->Get_field('nbjours');
				$TabGroupe[$k]['validate_himself']=$ATMdb->Get_field('validate_himself');
				$TabGroupe[$k]['level']=$ATMdb->Get_field('level');
				$k++;
	}
	
	//LISTE USERS À VALIDER
	if($k==1){		//on n'a qu'un groupe de validation
		$sql=" SELECT DISTINCT u.fk_user, 
				a.rowid as 'ID', a.date_cre  as 'DateCre',a.date_debut, a.date_fin, 
			  	a.libelle as 'Type absence',a.fk_user,  s.firstname, s.lastname,
			 	a.libelleEtat as 'Statut demande', a.avertissement
				FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v, ".MAIN_DB_PREFIX."usergroup_user as u, 
				".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as s
				WHERE v.fk_user=".$user->id." 
				AND v.fk_usergroup=u.fk_usergroup
				AND u.fk_user=a.fk_user 
				AND u.fk_user=s.rowid
				AND a.etat LIKE 'AValider'
				AND v.fk_usergroup=".$TabGroupe[0]['fk_usergroup'];
				
				if($TabGroupe[0]['level']==1){	//on teste le niveau de validation : si il est de niveau 1, il faut qu'il puisse voir le 2 et 3
					$sql.=" AND ( a.niveauValidation>=1)";
				}else if($TabGroupe[0]['level']==2){
					$sql.=" AND ( a.niveauValidation>=2)";
				}
				else if($TabGroupe[0]['level']==3){
					$sql.=" AND a.niveauValidation>=3";
				}

				
			if($TabGroupe[0]['validate_himself']==0){
				$sql.=" AND u.fk_user NOT IN (SELECT a.fk_user FROM ".MAIN_DB_PREFIX."rh_absence as a where a.fk_user=".$user->id.")";
			}

		
	}else if($k>1){		//on a plusieurs groupes de validation
		$sql=" SELECT DISTINCT u.fk_user, 
				a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut, a.date_fin, 
			  	a.libelle as 'Type absence',a.fk_user,  s.firstname, s.lastname
			 	a.libelleEtat as 'Statut demande', a.avertissement
				FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v, ".MAIN_DB_PREFIX."usergroup_user as u, 
				".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as s
				WHERE v.fk_user=".$user->id." 
				AND v.fk_usergroup=u.fk_usergroup
				AND u.fk_user=a.fk_user 
				AND u.fk_user=s.rowid
				AND a.etat LIKE 'AValider'";
 		
 		$j=0;
		foreach($TabGroupe as $TGroupe){ 	//on affiche les absences des différents groupe de validation
			if($j==0){
				if($TabGroupe[$j]['level']==1){	//on teste le niveau de validation  si il est de niveau 1, il faut qu'il puisse voir le 2 et 3
					$sql.=" AND ( (v.fk_usergroup=".$TabGroupe[$j]['fk_usergroup']."
					AND (a.niveauValidation>=1)
					AND NOW() >= ADDDATE(a.date_cre, ".$TabGroupe[$j]['nbjours'].")
					)";
				}else if($TabGroupe[$j]['level']==2){
					$sql.=" AND ( (v.fk_usergroup=".$TabGroupe[$j]['fk_usergroup']."
					AND (a.niveauValidation>=2)
					AND NOW() >= ADDDATE(a.date_cre, ".$TabGroupe[$j]['nbjours'].")
					)";
				}
				else if($TabGroupe[$j]['level']==3){
					$sql.=" AND ( (v.fk_usergroup=".$TabGroupe[$j]['fk_usergroup']."
					AND a.niveauValidation>=3
					AND NOW() >= ADDDATE(a.date_cre, ".$TabGroupe[$j]['nbjours'].")
					)";
				}
				
			}else{
				if($TabGroupe[$j]['level']==1){	//on teste le niveau de validation
					$sql.=" OR ( v.fk_usergroup=".$TabGroupe[$j]['fk_usergroup']."
						AND (a.niveauValidation>=1) 
						AND NOW() >= ADDDATE(a.date_cre, ".$TabGroupe[$j]['nbjours'].")
						)";
				}
				else if($TabGroupe[$j]['level']==2){
					$sql.=" OR ( v.fk_usergroup=".$TabGroupe[$j]['fk_usergroup']."
						AND (a.niveauValidation>=2) 
						AND NOW() >= ADDDATE(a.date_cre, ".$TabGroupe[$j]['nbjours'].")
						)";
				}
				else if($TabGroupe[$j]['level']==3){
					$sql.=" OR ( v.fk_usergroup=".$TabGroupe[$j]['fk_usergroup']."
						AND a.niveauValidation>=3
						AND NOW() >= ADDDATE(a.date_cre, ".$TabGroupe[$j]['nbjours'].")
						)";
				}	
			}
 			
			$j++;
 		}
 		$sql.=")";
	}
 	else {
		?><div class="error"><?php echo $langs->trans('ErrYouReNotHolidayValidator'); ?></div><?php
	}
 
	
		//LISTE DES ABSENCES À VALIDER
		$r = new TSSRenderControler($absence);
		
		$TOrder = array('DateCre'=>'DESC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
					
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
		$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');	
		echo $form->hidden('action', 'listeValidation');
			
		//print $page;
		$r->liste($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				'Type absence'=>'<a href="?id=@ID@&action=view&validation=ok">@val@</a>'
			)
			,'translate'=>array('Statut demande'=>array(
				'Refusée'=>'<b style="color:#A72947">' . $langs->trans('Refused') . '</b>',
				'En attente de validation'=>'<b style="color:#5691F9">' . $langs->trans('WaitingValidation') . '</b>' , 
				'Acceptée'=>'<b style="color:#30B300">' . $langs->trans('Accepted') . '</b>')
				,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="' . $langs->trans('DoNotRespectRules') . '"></img>')
			)			
			,'hide'=>array('date_cre','fk_user','ID', 'DateCre')
			,'type'=>array('date_debut'=>'date','date_fin'=>'date')
			,'liste'=>array(
				'titre'=> $langs->trans('ListeAbsencesWaitingValidation')
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=> $langs->trans('MessageNothingAbsence')
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
				
			)
			,'title'=>array(
				'date_debut'=> $langs->trans('StartDate')
				,'date_fin'=> $langs->trans('EndDate')
				,'avertissement'=> $langs->trans('Rules')
				,'firstname'=> $langs->trans('FirstName')
				,'lastname'=> $langs->trans('LastName')
				
			)
			,'search'=>array(
				'date_debut'=>array('recherche'=>'calendar')
				,'libelle'=>true
				,"firstname"=>true
				,"name"=>true
			)
			,'eval'=>array(
				'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
				,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
			)
			
			,'orderBy'=>$TOrder
			
		));
	
	
	llxFooter();
}	
function _ficheCommentaire(&$ATMdb, &$absence, $mode) {
	global $db,$user,$conf, $langs;
	llxHeader('', $langs->trans('PresenceRequest'));

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'saveComment');
	
	print dol_get_fiche_head(absencePrepareHead($absence, 'presenceCreation')  , 'fiche', $langs->trans('Presence'));
	
	?> 
	<br><t style='color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica;font-weight: bold;text-decoration: none;text-shadow: 1px 1px 2px #CFCFCF;'>
    <?php echo $langs->trans('AddComment') ?> </t><br/><br/><br/>
	<textarea name="commentValid" rows="3" cols="40"></textarea><br><br>
	<INPUT class="button" TYPE="submit"   id="commentaire" VALUE="<?php echo $langs->trans('Continue'); ?>">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
	<INPUT class="button" TYPE="button" id="newAsk" VALUE="<?php echo $langs->trans('NewRequestOnSameUser'); ?>" onclick="document.location.href='presence.php?action=new&fk_user=<?=$absence->fk_user ?>'">	
	<br><br>

	<?php
	
	echo $form->end_form();
	// End of page
	
	//global $mesg, $error;
	//dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

function _fiche(&$ATMdb, &$absence, $mode) {
	global $db,$user,$conf, $langs;
	llxHeader('', $langs->trans('PresencePlanning'));
	//echo $_REQUEST['validation'];
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('userRecapCompteur', isset($_REQUEST['fk_user'])?$_REQUEST['fk_user']:0);
	echo $form->hidden('userAbsenceCree', isset($absence->fk_user)!=0?$absence->fk_user:0);

	//récupération informations utilisateur dont on observe l'absence, ou la crée
	$userCourant=new User($db);
	if($absence->fk_user!=0){
		$userCourant->fetch($absence->fk_user);
	}else{
		$userCourant->fetch($user->id);
	}
	
	$comboAbsence=0;
	//création du tableau des utilisateurs liés au groupe du valideur, pour créer une absence, pointage...
	$TUser = array();
	$sql="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$user->id." AND statut=1";
	$ATMdb->Execute($sql);
	if($ATMdb->Get_line()){
		$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
	}

	$typeAbsenceCreable= TRH_TypeAbsence::getTypeAbsence($ATMdb, 'user',true);

	$droitAdmin=0;

	if($user->rights->absence->myactions->creerAbsenceCollaborateur){
		$sql="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE statut=1";
		$droitsCreation=1;
		$comboAbsence=2;
		$typeAbsenceCreable=TRH_TypeAbsence::getTypeAbsence($ATMdb, 'admin',true);
		$droitAdmin=1;
	}
	else if($user->rights->absence->myactions->creerAbsenceCollaborateurGroupe){
		$sql=" SELECT DISTINCT u.fk_user,s.rowid, s.lastname,  s.firstname 
			FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v INNER JOIN ".MAIN_DB_PREFIX."usergroup_user as u ON (v.fk_usergroup=u.fk_usergroup)
				INNER JOIN ".MAIN_DB_PREFIX."user as s ON (s.rowid=u.fk_user)  
			WHERE v.fk_user=".$user->id." 
			AND s.statut=1
			AND v.type='Conges'";
			$comboAbsence=1;
			$droitsCreation=1;
			$typeAbsenceCreable=TRH_TypeAbsence::getTypeAbsence($ATMdb, 'user',true);
	}
	else {
		$droitsCreation=2; //on n'a pas les droits de créationadmin/modules.php?id=7100&action=set&value=modAbsence&mode=common
	}
	
	if($droitsCreation==1){
		$sql.=" ORDER BY lastname";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
		}
	}
	//Tableau affichant les 10 dernières absences du collaborateur
	$TRecap=array();
	$TRecap=$absence->recuperationDerAbsUser($ATMdb, $userCourant->id);
	
	//on regarde si l'utilisateur a le droit de créer une absence non justifiée (POINTEUR)
	
	//on peut supprimer la demande d'absence lorsque temps que la date du jour n'est pas supérieure à datedébut-1
	
	$diff=strtotime('+0day',$absence->date_debut)-time(); // TODO Mais WTF ?!! J'avoue que parfois je suis scié
	$duree=intval($diff/3600/24);

	if($duree>0&&$absence->fk_user==$user->id/* && $absence->etat!='Validee'*/){
		$droitSupprimer=1;
	}
	elseif($user->rights->absence->myactions->creerAbsenceCollaborateur){
		$droitSupprimer=1;
	}
	
	$userValidation=new User($db);
	$userValidation->fetch($absence->fk_user_valideur);
	//print_r($userValidation);
	
	if(isset($_REQUEST['calcul'])) {
		$absence->duree = $absence->calculDureeAbsenceParAddition($ATMdb);
	}
		
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/presence.tpl.php'
		,array(
			//'TRegle' =>$TRegle
			'TRecap'=>$TRecap
		)
		,array(
			'absenceCourante'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'id'=>$absence->getId()
				,'commentaire'=>$form->zonetexte('','commentaire',$absence->commentaire, 30,3,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->date_debut,12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->date_fin, 12)
				
				,'hourStart'=>$form->timepicker('', 'date_hourStart', $absence->date_hourStart,5)
				,'hourEnd'=>$form->timepicker('', 'date_hourEnd', $absence->date_hourEnd,5)
				
				,'idUser'=>$user->id
				,'comboType'=>$form->combo('','type',$typeAbsenceCreable,$absence->type)
				,'etat'=>$absence->etat
				,'libelleEtat'=>$form->texte('','etat',$absence->libelleEtat,5,10,'',$class="text", $default='')
				,'duree'=>$form->texte('','duree',round2Virgule($absence->duree),5,10,'',$class="text", $default='')	
				,'dureeHeure'=>$form->texte('','dureeHeure',$absence->dureeHeure,5,10,'',$class="text", $default='')
				,'dureeHeurePaie'=>$form->texte('','dureeHeurePaie',$absence->dureeHeurePaie,5,10,'',$class="text", $default='')
				,'avertissement'=>$absence->avertissement==1?'<img src="./img/warning.png" />' . $langs->trans('DoNotRespectRules') . ' : '.$absence->avertissementInfo: $langs->trans('None')
				,'fk_user'=>$absence->fk_user
				,'userAbsence'=>$droitsCreation==1?$form->combo('','fk_user',$TUser,$absence->fk_user):''
				,'userAbsenceCourant'=>$droitsCreation==1?'':$form->hidden('fk_user', $user->id)
				,'fk_user_absence'=>$form->hidden('fk_user_absence', $absence->fk_user)
				,'niveauValidation'=>$absence->niveauValidation
				,'commentaireValideur'=>$absence->commentaireValideur
				,'dt_cre'=>$absence->get_dtcre()
				,'time_validation'=>$absence->date_validation
				,'date_validation'=>$absence->get_date('date_validation')
				,'userValidation'=>$userValidation->firstname.' '.$userValidation->lastname
				
				,'titreNvDemande'=>load_fiche_titre($langs->trans('PresencePlanning'),'', 'title.png', 0, '')
				,'titreRecapAbsence'=>load_fiche_titre($langs->trans('Summary'),'', 'title.png', 0, '')
				,'titreJourRestant'=>load_fiche_titre($langs->trans('RemainingDays'),'', 'title.png', 0, '')
				,'titreDerAbsence'=>load_fiche_titre($langs->trans('LastAbsencePresence'),'', 'title.png', 0, '')
				,'titreRegle'=>load_fiche_titre($langs->trans('RulesAboutYou'),'', 'title.png', 0, '')
				
				,'ddMoment'=>$form->combo('','ddMoment',$absence->TddMoment,$absence->ddMoment)
				,'dfMoment'=>$form->combo('','dfMoment',$absence->TdfMoment,$absence->dfMoment)
				
				,'droitSupprimer'=>$droitSupprimer
				
				
				
				
			)	
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
				,'valideurConges'=>$user->rights->absence->myactions->creerAbsenceCollaborateur==1?1:$user->rights->absence->myactions->valideurConges&&$estValideur
				//,'valideurConges'=>$user->rights->absence->myactions->valideurConges
				,'droitCreationAbsenceCollaborateur'=>$droitsCreation==1?'1':'0'
				//,'enregistrerPaieAbsences'=>$user->rights->absence->myactions->enregistrerPaieAbsences&&$estValideur
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'presence')  , 'fiche', $langs->trans('Presence'))
				,'head2'=>dol_get_fiche_head(absencePrepareHead($absence, 'presence')  , 'fiche', $langs->trans('Presence'))
			)
			,'translate' => array(
				'User' => $langs->trans('User'),
				'CurrentUser' => $langs->trans('CurrentUser'),
				'PresenceType' => $langs->trans('PresenceType'),
				'StartDate' => $langs->trans('StartDate'),
				'EndDate' => $langs->trans('EndDate'),
				'Comment' => $langs->trans('Comment'),
				'CreatedThe' => $langs->trans('CreatedThe'),
				'ValidatedThe' => $langs->trans('ValidatedThe'),
				'Register' => $langs->trans('Register'),
				'ConfirmAcceptPresenceRequest' =>addslashes( $langs->trans('ConfirmAcceptPresenceRequest')),
				'ConfirmRefusePresenceRequest' =>addslashes( $langs->transnoentitiesnoconv('ConfirmRefusePresenceRequest')),
				'Accept' => $langs->trans('Accept'),
				'Refuse' => $langs->trans('Refuse'),
				'ConfirmSendToSuperiorAbsenceRequest' =>addslashes( $langs->trans('ConfirmSendToSuperiorAbsenceRequest')),
				'ConfirmDeletePresenceRequest' =>addslashes( $langs->trans('ConfirmDeletePresenceRequest')),
				'Delete' => $langs->trans('Delete'),
				'AbsenceType' => $langs->trans('AbsenceType'),
				'State' => $langs->trans('State'),
				'Warning' => $langs->trans('Warning'),
				'dontSendMail'=>$langs->trans('dontSendMail')
			)
			,'other' => array(
				'dontSendMail' => $user->rights->absence->myactions->CanAvoidSendMail
				,'dontSendMail_CB' => '<input type="checkbox" name="dontSendMail" id="dontSendMail" value="1" />' // J'utilise pas $form->checkbox1('','dontSendMail', 1) parce que j'ai besoin que la ce soit toujours cochable meme en mode view pour les valideurs
			)
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	//global $mesg, $error, $popinExisteDeja, $existeDeja;
	//dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	
	if(!empty($popinExisteDeja) && !empty($existeDeja)) {
		?>
		<script type="text/javascript">
		
		$(document).ready(function() {
		
			$('#user-planning-dialog div.content').before( "<?=addslashes($popinExisteDeja) ?>" );
		
			$('#user-planning-dialog div.content').load('planningUser.php?fk_user=<?=$existeDeja[2] ?>&date_debut=<?=__get('date_debut') ?>&date_fin=<?=__get('date_fin') ?> #plannings');
		
			$('#user-planning-dialog').dialog({
				title: "<?php echo $langs->trans('CreationError'); ?>"	
				,width:700
				,modal:true
			});
			
		});
		
		</script>
		
		<?php
	}

}

	
	
