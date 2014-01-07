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
					$absence->code=saveCodeTypeAbsence($ATMdb, $absence->type);
				
						$absence->save($ATMdb);
						$mesg = 'Présence enregistrée';
						_fiche($ATMdb, $absence,'view');
				
				}else{
					$mesg = '<div class="error">Création impossible : il existe déjà une autre présence pendant cette période : '.$existeDeja.'</div>';
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
	llxHeader('','Liste de vos périodes présences');
	print dol_get_fiche_head(absencePrepareHead($absence, '')  , '', 'Absence');

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
	llxHeader('','Planification de presence');
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
	$sql="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE rowid=".$user->id;
	$ATMdb->Execute($sql);
	if($ATMdb->Get_line()){
		$TUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
	}

	$typeAbsenceCreable= TRH_TypeAbsence::getTypeAbsence($ATMdb, 'user',true);

	$droitAdmin=0;

	if($user->rights->absence->myactions->creerAbsenceCollaborateur){
		$sql="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user`";
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
			AND v.type='Conges'";
			$comboAbsence=1;
			$droitsCreation=1;
			$typeAbsenceCreable=TRH_TypeAbsence::getTypeAbsence($ATMdb, 'user',true);
	}
	else {
		$droitsCreation=2; //on n'a pas les droits de création
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
	$TRecap=$absence->recuperationDerAbsUser($ATMdb, $regleId);
	
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
				
				,'titreNvDemande'=>load_fiche_titre("Planification de presence",'', 'title.png', 0, '')
				,'titreRecapAbsence'=>load_fiche_titre("Récapitulatif",'', 'title.png', 0, '')
				,'titreJourRestant'=>load_fiche_titre("Jours restants à prendre",'', 'title.png', 0, '')
				,'titreDerAbsence'=>load_fiche_titre("Vos dernières présences/absences",'', 'title.png', 0, '')
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
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'fiche', 'Présence')
				,'head2'=>dol_get_fiche_head(absencePrepareHead($absence, 'absenceCreation')  , 'fiche', 'Présence')
				
				
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

	
	
