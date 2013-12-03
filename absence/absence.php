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
				$absence->niveauValidation=1;
				$existeDeja=$absence->testExisteDeja($ATMdb, $absence);
				if($existeDeja===false){
					$absence->code=saveCodeTypeAbsence($ATMdb, $absence->type);
					$demandeRecevable=$absence->testDemande($ATMdb, $_REQUEST['fk_user'], $absence);
				
					if($demandeRecevable==1){
						$absence->save($ATMdb);
						$absence->load($ATMdb, $_REQUEST['id']);
						if($absence->fk_user==$user->id){	//on vérifie si l'absence a été créée par l'user avant d'envoyer un mail
							mailConges($absence);
							mailCongesValideur($ATMdb,$absence);
						}
						
						$mesg = '<div class="error">Demande enregistrée</div>';
						_fiche($ATMdb, $absence,'view');
					}else{
						if($demandeRecevable==0){
							$mesg = '<div class="error">Demande refusée : La durée de l\'absence dépasse la règle restrictive en vigueur</div>';
							_fiche($ATMdb, $absence,'edit');
						}else if($demandeRecevable==2){
							$absence->avertissement=1;
							$absence->save($ATMdb);
							$absence->load($ATMdb, $_REQUEST['id']);
							if($absence->fk_user==$user->id){	//on vérifie si l'absence a été créée par l'user avant d'envoyer un mail
								mailConges($absence);
								mailCongesValideur($ATMdb,$absence);
							}
							$mesg = '<div class="error">Attention : La durée de l\'absence dépasse la règle en vigueur</div>';
							_fiche($ATMdb, $absence,'view');
						}
						else if($demandeRecevable==3){		// demande rtt non cumulés acollée à un congé, ou rtt ou jour férié
							$mesg = '<div class="error">Demande refusée à cause des règles sur les RTT non cumulés</div>';
							_fiche($ATMdb, $absence,'edit');
						}
					}
				}else{
					$mesg = '<div class="error">Création impossible : il existe déjà une autre demande d\'absence pendant cette période : '.$existeDeja.'</div>';
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
				<?
				break;
				
			case 'accept':
				$absence->load($ATMdb, $_REQUEST['id']);
				$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` 
					SET etat='Validee', libelleEtat='Acceptée', date_validation='".date('Y-m-d')."', fk_user_valideur=".$user->id." 
					WHERE fk_user=".$absence->fk_user. " 
					AND rowid=".$absence->getId();
				$ATMdb->Execute($sqlEtat);
				$absence->load($ATMdb, $_REQUEST['id']);
				mailConges($absence);
				$mesg = '<div class="error">Demande d\'absence acceptée</div>';
				_ficheCommentaire($ATMdb, $absence,'edit');
				break;
				
			case 'niveausuperieur':
				$absence->load($ATMdb, $_REQUEST['id']);
				$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` 
					SET niveauValidation=niveauValidation+1 WHERE rowid=".$absence->getId();
				$ATMdb->Execute($sqlEtat);
				$absence->load($ATMdb, $_REQUEST['id']);
				mailConges($absence);
				$mesg = '<div class="error">Demande d\'absence envoyée au valideur supérieur</div>';
				_fiche($ATMdb, $absence,'view');
				break;
				
			case 'refuse':
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->recrediterHeure($ATMdb);
				$absence->load($ATMdb, $_REQUEST['id']);
				$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` 
					SET etat='Refusee', libelleEtat='Refusée' 
					WHERE fk_user=".$absence->fk_user. " AND rowid=".$absence->getId();
				//print $sqlEtat;
				$ATMdb->Execute($sqlEtat);
				$absence->load($ATMdb, $_REQUEST['id']);
				mailConges($absence);
				$mesg = '<div class="error">Demande d\'absence refusée</div>';
				_ficheCommentaire($ATMdb, $absence,'edit');
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
	llxHeader('','Liste de vos absences');
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', 'Absence');

	//getStandartJS();
	
	$r = new TSSRenderControler($absence);

	//LISTE D'ABSENCES DU COLLABORATEUR
	$sql="SELECT a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut , a.date_fin, 
			a.libelle,a.fk_user,  a.fk_user, u.login, u.firstname, u.lastname,
			a.etat, a.avertissement
			FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
			WHERE a.fk_user=".$user->id." AND u.rowid=a.fk_user";
	
	
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
			'Refusée'=>'<b style="color:#A72947">Refusée</b>',
			'En attente de validation'=>'<b style="color:#5691F9">	En attente de validation</b>' , 
			'Acceptée'=>'<b style="color:#30B300">Acceptée</b>')
			,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="Ne respecte pas les règles en vigueur"></img>')
			,'etat'=>$absence->TEtat
		)
		,'hide'=>array('DateCre', 'fk_user', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de vos absences'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune absence à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date fin'
			,'avertissement'=>'Règle'
			,'libelle'=>'Type d\'absence'
			,'firstname'=>'Prénom'
			,'lastname'=>'Nom'
			,'login'=>'Login'
			,'etat'=>'Statut demande'
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
	?><a class="butAction" href="?id=<?=$absence->getId()?>&action=new">Nouvelle demande</a><div style="clear:both"></div><?
	$form->end();
	
	
	llxFooter();
}	

function _listeAdmin(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de toutes les absences');
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', 'Absence');
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
			'avertissement'=>array('1'=>'<img src="./img/warning.png" title="Ne respecte pas les règles en vigueur"></img>')
			,'etat'=>$absence->TEtat
		)
		,'hide'=>array('DateCre', 'fk_user', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de toutes les absences des collaborateurs'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune absence à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			,'etat'=>$absence->TEtat
			
		)
		,'title'=>array(
			'date_debut'=>'Date début'
			,'date_fin'=>'Date fin'
			,'avertissement'=>'Règle'
			,'libelle'=>'Type d\'absence'
			,'firstname'=>'Prénom'
			,'lastname'=>'Nom'
			,'login'=>'Login'
			,'duree'=>'Durée (en jour)'
			,'etat'=>'Statut demande'
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
	?><a class="butAction" href="?id=<?=$absence->getId()?>&action=new">Nouvelle demande</a><div style="clear:both"></div><?
	$form->end();
	
	
	llxFooter();
}	
function _setColorEtat($val) {
	return strtr($val,array(
				'Refusée'=>'<b style="color:#A72947">Refusée</b>',
				'En attente de validation'=>'<b style="color:#5691F9">	En attente de validation</b>' , 
				'Acceptée'=>'<b style="color:#30B300">Acceptée</b>'
	));
}
	
function _listeValidation(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', 'Absence');
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
		?><div class="error">Vous n'&ecirc;tes pas valideur de cong&eacute;  </div><?
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
				'Refusée'=>'<b style="color:#A72947">Refusée</b>',
				'En attente de validation'=>'<b style="color:#5691F9">	En attente de validation</b>' , 
				'Acceptée'=>'<b style="color:#30B300">Acceptée</b>')
				,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="Ne respecte pas les règles en vigueur"></img>')
			)			
			,'hide'=>array('date_cre','fk_user','ID', 'DateCre')
			,'type'=>array('date_debut'=>'date','date_fin'=>'date')
			,'liste'=>array(
				'titre'=>'Liste des absences à valider'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','previous.png', '', 0)
				,'picto_suivant'=>img_picto('','next.png', '', 0)
				,'noheader'=> (int)isset($_REQUEST['socid'])
				,'messageNothing'=>"Il n'y a aucune absence à afficher"
				,'order_down'=>img_picto('','1downarrow.png', '', 0)
				,'order_up'=>img_picto('','1uparrow.png', '', 0)
				,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
				
			)
			,'title'=>array(
				'date_debut'=>'Date début'
				,'date_fin'=>'Date fin'
				,'avertissement'=>'Règle'
				,'firstname'=>'Prénom'
				,'lastname'=>'Nom'
				
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

function _fiche(&$ATMdb, &$absence, $mode) {
	global $db,$user,$conf;
	llxHeader('','Demande d\'absence');
	//echo $_REQUEST['validation'];
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('userRecapCompteur', isset($_REQUEST['fk_user'])?$_REQUEST['fk_user']:0);
	echo $form->hidden('userAbsenceCree', isset($absence->fk_user)!=0?$absence->fk_user:0);
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1;
	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` 
				WHERE fk_user=".$user->id;
	$ATMdb->Execute($sqlReqUser);
	$congePrec=array();
	$congeCourant=array();
	$rttCourant=array();
	while($ATMdb->Get_line()) {
				$congePrec['id']=$ATMdb->Get_field('rowid');
				$congePrec['acquisEx']=$ATMdb->Get_field('acquisExerciceNM1');
				$congePrec['acquisAnc']=$ATMdb->Get_field('acquisAncienneteNM1');
				$congePrec['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeNM1');
				$congePrec['reportConges']=$ATMdb->Get_field('reportCongesNM1');
				$congePrec['congesPris']=$ATMdb->Get_field('congesPrisNM1');
				$congePrec['annee']=$ATMdb->Get_field('anneeNM1');
				$congePrec['fk_user']=$ATMdb->Get_field('fk_user');
	
				$congeCourant['id']=$ATMdb->Get_field('rowid');
				$congeCourant['acquisEx']=$ATMdb->Get_field('acquisExerciceN');
				$congeCourant['acquisAnc']=$ATMdb->Get_field('acquisAncienneteN');
				$congeCourant['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeN');
				$congeCourant['annee']=$ATMdb->Get_field('anneeN');
				$congeCourant['fk_user']=$ATMdb->Get_field('fk_user');
				
				
				$rttCourant['id']=$ATMdb->Get_field('rowid');
				
				/*$rttCourant['cumuleReste']=round2Virgule($ATMdb->Get_field('rttCumuleTotal'));
				$rttCourant['nonCumuleReste']=round2Virgule($ATMdb->Get_field('rttNonCumuleTotal'));
				*/
				$rttCourant['cumuleReste']=round2Virgule($ATMdb->Get_field('cumuleAcquis')+$ATMdb->Get_field('cumuleReport')-$ATMdb->Get_field('cumulePris'));
				
				$rttCourant['nonCumuleReste']=round2Virgule($ATMdb->Get_field('nonCumuleAcquis')+$ATMdb->Get_field('nonCumuleReport')-$ATMdb->Get_field('nonCumulePris'));
				
				$rttCourant['fk_user']=$ATMdb->Get_field('fk_user');
	
	
	
	}
	
	$congePrecTotal=$congePrec['acquisEx']+$congePrec['acquisAnc']+$congePrec['acquisHorsPer']+$congePrec['reportConges'];
	$congePrecReste=$congePrecTotal-$congePrec['congesPris'];
	

	
	
	
	//récupération informations utilisateur dont on observe l'absence, ou la crée
	if($absence->fk_user!=0){
		$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$absence->fk_user;
	}else{
		$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$user->id;
	}
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($db);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('lastname');
	}
	
	
	//$estValideur=$absence->estValideur($ATMdb,$user->id);
	if(isset($_REQUEST['validation'])){
		if($_REQUEST['validation']=='ok'){
			$estValideur=1;
		}else $estValideur=0;
	}else $estValideur=0;
	
	if($absence->fk_user==0){
		$regleId=$user->id;
	}else $regleId=$absence->fk_user;
	
	//récupération des règles liées à l'utilisateur 
	//$TRegle=array();
	//$TRegle=$absence->recuperationRegleUser($ATMdb, $regleId);

	$comboAbsence=0;
	//création du tableau des utilisateurs liés au groupe du valideur, pour créer une absence, pointage...
	$TUser = array();
	$sql="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$user->id;
	$ATMdb->Execute($sql);
	if($ATMdb->Get_line()){
		$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
	}
	$typeAbsenceCreable=$absence->TTypeAbsenceUser;

	$droitAdmin=0;

	if($user->rights->absence->myactions->creerAbsenceCollaborateur){
		$sql="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user`";
		$droitsCreation=1;
		$comboAbsence=2;
		$typeAbsenceCreable=$absence->TTypeAbsenceAdmin;
		$droitAdmin=1;
//print "admin";
//print_r( $typeAbsenceCreable);
	}else if($user->rights->absence->myactions->creerAbsenceCollaborateurGroupe){
		$sql=" SELECT DISTINCT u.fk_user,s.rowid, s.lastname,  s.firstname 
			FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v INNER JOIN ".MAIN_DB_PREFIX."usergroup_user as u ON (v.fk_usergroup=u.fk_usergroup)
				INNER JOIN ".MAIN_DB_PREFIX."user as s ON (s.rowid=u.fk_user)  
			WHERE v.fk_user=".$user->id." 
			AND v.type='Conges'";
			$comboAbsence=1;
			//echo $sqlReqUser;exit;
		$droitsCreation=1;
		$typeAbsenceCreable=$absence->TTypeAbsenceUser;
	}
	else $droitsCreation=2; //on n'a pas les droits de création
	
	if($droitsCreation==1){
		$sql.=" ORDER BY lastname";
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
		}
	}
	//Tableau affichant les 10 dernières absences du collaborateur
	$TRecap=array();
	$TRecap=$absence->recuperationDerAbsUser($ATMdb, $regleId);
	
	//on regarde si l'utilisateur a le droit de créer une absence non justifiée (POINTEUR)
	
	$sql="SELECT count(*) as 'nb' FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` WHERE fk_user=".$user->id." AND type='Conges' AND pointeur=1";
	$ATMdb->Execute($sql);
	$ATMdb->Get_line();
	
	$pointeurTest=(int)$ATMdb->Get_field('nb');
	
	if(_debug()) {
		print $sql;
	}

	if($pointeurTest>0 && $droitAdmin==0){
		//			print "Utilisateur Pointeur";

		$typeAbsenceCreable=$absence->TTypeAbsencePointeur;
		
		
		if(!$user->rights->absence->myactions->creerAbsenceCollaborateur && !$user->rights->absence->myactions->creerAbsenceCollaborateurGroupe) {
			$sql=" SELECT DISTINCT u.fk_user,s.rowid, s.lastname,  s.firstname 
			FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v INNER JOIN ".MAIN_DB_PREFIX."usergroup_user as u ON (v.fk_usergroup=u.fk_usergroup)
				INNER JOIN ".MAIN_DB_PREFIX."user as s ON (s.rowid=u.fk_user)  
			WHERE v.fk_user=".$user->id." 
			AND v.type='Conges'
			AND v.pointeur=1
			ORDER BY s.lastname
			";
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
			}
		}
		
		$droitsCreation=1;
		
	}
	
	
	
	//on peut supprimer la demande d'absence lorsque temps que la date du jour n'est pas supérieure à datedébut-1
	
	$diff=strtotime('+0day',$absence->date_debut)-time();
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
	print $TBS->render('./tpl/absence.tpl.php'
		,array(
			//'TRegle' =>$TRegle
			'TRecap'=>$TRecap
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',$congePrec['acquisEx'],10,50,'')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',$congePre['acquisAnc'],10,50)
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',$congePrec['acquisHorsPer'],10,50)
				,'reportConges'=>$form->texte('','reportcongesNM1',$congePrec['reportConges'],10,50)
				,'congesPris'=>$form->texte('','congesprisNM1',$congePrec['congesPris'],10,50)
				,'anneePrec'=>$form->texte('','anneeNM1',$anneePrec,10,50)
				,'total'=>$form->texte('','total',$congePrecTotal,10,50)
				,'reste'=>round2Virgule($congePrecReste)
				,'idUser'=>$_REQUEST['id']
			)
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceN',$congeCourant['acquisEx'],10,50)
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',$congeCourant['acquisAnc'],10,50)
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',$congeCourant['acquisHorsPer'],10,50)
				,'anneeCourante'=>$form->texte('','anneeN',$anneeCourante,10,50)
				,'idUser'=>$_REQUEST['id']
			)
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',$rttCourant['acquis'],10,50)
				,'rowid'=>$form->texte('','rowid',$rttCourant['id'],10,50,'')
				//,'id'=>$form->texte('','fk_user',$_REQUEST['id'],10,50,'',$class="text", $default='')
				,'cumuleReste'=>round2Virgule($rttCourant['cumuleReste'])
				,'nonCumuleReste'=>round2Virgule($rttCourant['nonCumuleReste'])
				,'idNum'=>$idRttCourant
			)
			,'absenceCourante'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'id'=>$absence->getId()
				,'commentaire'=>$form->zonetexte('','commentaire',$absence->commentaire, 30,3,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->date_debut,12)
				,'ddMoment'=>$form->combo('','ddMoment',$absence->TddMoment,$absence->ddMoment)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->date_fin, 12)
				,'dfMoment'=>$form->combo('','dfMoment',$absence->TdfMoment,$absence->dfMoment)
				,'idUser'=>$user->id
				,'comboType'=>$form->combo('','type',$typeAbsenceCreable,$absence->type)
				,'etat'=>$absence->etat
				,'libelleEtat'=>$form->texte('','etat',$absence->libelleEtat,5,10,'',$class="text", $default='')
				,'duree'=>$form->texte('','duree',round2Virgule($absence->duree),5,10,'',$class="text", $default='')	
				,'dureeHeure'=>$form->texte('','dureeHeure',$absence->dureeHeure,5,10,'',$class="text", $default='')
				,'dureeHeurePaie'=>$form->texte('','dureeHeurePaie',$absence->dureeHeurePaie,5,10,'',$class="text", $default='')
				,'avertissement'=>$absence->avertissement==1?'<img src="./img/warning.png">  Ne respecte pas les règles en vigueur</img>':'Aucun'
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
				
				,'titreNvDemande'=>load_fiche_titre("Nouvelle demande d'absence",'', 'title.png', 0, '')
				,'titreRecapAbsence'=>load_fiche_titre("Récapitulatif de la demande d'absence",'', 'title.png', 0, '')
				,'titreJourRestant'=>load_fiche_titre("Jours restants à prendre",'', 'title.png', 0, '')
				,'titreDerAbsence'=>load_fiche_titre("Vos dernières absences",'', 'title.png', 0, '')
				,'titreRegle'=>load_fiche_titre("Règles vous concernant",'', 'title.png', 0, '')
				
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
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'fiche', 'Absence')
				,'head2'=>dol_get_fiche_head(absencePrepareHead($absence, 'absenceCreation')  , 'fiche', 'Absence')
				
				
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

function _ficheCommentaire(&$ATMdb, &$absence, $mode) {
	global $db,$user,$conf;
	llxHeader('','Demande d\'absence');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'saveComment');
	
	print dol_get_fiche_head(absencePrepareHead($absence, 'absenceCreation')  , 'fiche', 'Absence');
	
	?> 
	<br><t style='color: #2AA8B9; font-size: 15px;font-family: arial,tahoma,verdana,helvetica;font-weight: bold;text-decoration: none;text-shadow: 1px 1px 2px #CFCFCF;'>
    Vous pouvez ajouter un commentaire pour justifier votre choix </t><br/><br/><br/>
	<textarea name="commentValid" rows="3" cols="40"></textarea><br><br>
	<INPUT class="button" TYPE="submit"   id="commentaire" VALUE="Continuer">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
	<INPUT class="button" TYPE="button" id="newAsk" VALUE="Nouvelle demande sur le même utilisateur" onclick="document.location.href='absence.php?action=new&fk_user=<?=$absence->fk_user ?>'">	
	<br><br>

	<?
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
