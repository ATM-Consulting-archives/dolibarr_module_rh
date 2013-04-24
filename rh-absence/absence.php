<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
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
				$demandeRecevable=$absence->testDemande($ATMdb);
				
				if($demandeRecevable==1){
					$absence->save($ATMdb);
					$absence->load($ATMdb, $_REQUEST['id']);
					mailConges($absence);
					$mesg = '<div class="ok">Demande enregistrée</div>';
					_fiche($ATMdb, $absence,'view');
				}else{
					if($demandeRecevable==0){
						$mesg = '<div class="error">Demande refusée : La durée de l\'absence dépasse la règle restrictive en vigueur</div>';
						_fiche($ATMdb, $absence,'edit');
					}else if($demandeRecevable==2){
						$absence->avertissement=1;
						$absence->save($ATMdb);
						$absence->load($ATMdb, $_REQUEST['id']);
						mailConges($absence);
						$mesg = '<div class="error">Attention : La durée de l\'absence dépasse la règle en vigueur</div>';
						_fiche($ATMdb, $absence,'view');
					}	
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
				$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` SET etat='Validee', libelleEtat='Acceptée' where fk_user=".$absence->fk_user. " AND rowid=".$absence->getId();
				$ATMdb->Execute($sqlEtat);
				$absence->load($ATMdb, $_REQUEST['id']);
				mailConges($absence);
				$mesg = '<div class="ok">Demande d\'absence acceptée</div>';
				_fiche($ATMdb, $absence,'view');
				break;
				
			case 'envoyerpaie':
				$absence->load($ATMdb, $_REQUEST['id']);
				$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` SET etat='Enregistree', libelleEtat='Enregistrée dans la paie' where fk_user=".$absence->fk_user. " AND rowid=".$absence->getId();
				$ATMdb->Execute($sqlEtat);
				$absence->load($ATMdb, $_REQUEST['id']);
				mailConges($absence);
				$mesg = '<div class="ok">Demande d\'absence enregistrée dans la paie</div>';
				_fiche($ATMdb, $absence,'view');
				break;
				
			case 'refuse':
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->recrediterHeure($ATMdb);
				$absence->load($ATMdb, $_REQUEST['id']);
				$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` SET etat='Refusee', libelleEtat='Refusée' where fk_user=".$absence->fk_user. " AND rowid=".$absence->getId();
				$ATMdb->Execute($sqlEtat);
				$absence->load($ATMdb, $_REQUEST['id']);
				mailConges($absence);
				$mesg = '<div class="error">Demande d\'absence refusée</div>';
				_fiche($ATMdb, $absence,'view');
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
	
	//LISTE D'ABSENCES DU COLLABORATEUR
	$r = new TSSRenderControler($absence);
	$sql="SELECT a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut , a.date_fin, 
			  a.libelle as 'Type absence',a.fk_user,  a.fk_user, CONCAT(u.firstname,' ',u.name) as 'Utilisateur' ,
			   a.libelleEtat as 'Statut demande', a.avertissement
		FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
		WHERE a.fk_user=".$user->id." AND a.entity=".$conf->entity." AND u.rowid=a.fk_user";
		
	
	$TOrder = array('Statut demande'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');		
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'Type absence'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array('Statut demande'=>array(
			'Refusée'=>'<b style="color:#A72947">Refusée</b>',
			'En attente de validation'=>'<b style="color:#5691F9">	En attente de validation</b>' , 
			'Enregistrée dans la paie'=>'<b style="color:#9A69E3">	Acceptée et Enregistrée dans la paie</b>' , 
			'Acceptée'=>'<b style="color:#30B300">Acceptée</b>')
			,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="Ne respecte pas les règles en vigueur"></img>')
		)
		,'hide'=>array('DateCre', 'fk_user', 'ID')
		,'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'liste'=>array(
			'titre'=>'Liste de vos absences'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
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
			
		)
		,'search'=>array(
			'date_debut'=>array('recherche'=>'calendar')
			
		)
		,'orderBy'=>$TOrder
		
	));
	?><br/><a class="butAction" href="?id=<?=$absence->getId()?>&action=new">Nouvelle demande</a><div style="clear:both"></div><br/><?
	$form->end();
	
	
 
 	//LISTE USERS À VALIDER
 	
	$sql=" SELECT DISTINCT u.fk_user FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v, ".MAIN_DB_PREFIX."usergroup_user as u 
			WHERE v.fk_user=".$user->id." 
			AND v.type='Conges'
			AND v.fk_usergroup=u.fk_usergroup
			AND u.fk_user NOT IN (SELECT a.fk_user FROM ".MAIN_DB_PREFIX."rh_absence as a where a.fk_user=1)
			AND v.entity=".$conf->entity;
		
	$ATMdb->Execute($sql);
	$TabUser=array();
	$k=0;
	while($ATMdb->Get_line()) {
				$TabUser[]=$ATMdb->Get_field('fk_user');
				$k++;
	}
	
	if($k==0){
		
	}else{
		//LISTE DES ABSENCES À VALIDER
		$r = new TSSRenderControler($absence);
		$sql="SELECT a.rowid as 'ID', a.date_cre as 'DateCre',a.date_debut, a.date_fin, 
				  a.libelle as 'Type absence',a.fk_user,  CONCAT(u.firstname,' ',u.name) as 'Utilisateur',
				  a.libelleEtat as 'Statut demande', a.avertissement
			FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
			WHERE a.fk_user IN(".implode(',', $TabUser).") AND a.entity=".$conf->entity." AND u.rowid=a.fk_user";
		
		$TOrder = array('Statut demande'=>'DESC');
		if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
		if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
					
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
		$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');		
		//print $page;
		$r->liste($ATMdb, $sql, array(
			'limit'=>array(
				'page'=>$page
				,'nbLine'=>'30'
			)
			,'link'=>array(
				'Type absence'=>'<a href="?id=@ID@&action=view">@val@</a>'
			)
			,'translate'=>array('Statut demande'=>array(
				'Refusée'=>'<b style="color:#A72947">Refusée</b>',
				'En attente de validation'=>'<b style="color:#5691F9">	En attente de validation</b>' , 
				'Enregistrée dans la paie'=>'<b style="color:#9A69E3">	Acceptée et Enregistrée dans la paie</b>' , 
				'Acceptée'=>'<b style="color:#30B300">Acceptée</b>')
				,'avertissement'=>array('1'=>'<img src="./img/warning.png" title="Ne respecte pas les règles en vigueur"></img>')
			)			
			,'hide'=>array('DateCre','fk_user','ID')
			,'type'=>array('date_debut'=>'date','date_fin'=>'date')
			,'liste'=>array(
				'titre'=>'Liste des absences à valider'
				,'image'=>img_picto('','title.png', '', 0)
				,'picto_precedent'=>img_picto('','back.png', '', 0)
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
				
			)
			
			,'orderBy'=>$TOrder
			
		));
	}
	
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$absence, $mode) {
	global $db,$user;
	llxHeader('','Déclaration absence');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $user->id);
	
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1;
	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$user->id." AND anneeNM1=".$anneePrec;//."AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReqUser);
	$congePrec=array();
	while($ATMdb->Get_line()) {
				$congePrec['id']=$ATMdb->Get_field('rowid');
				$congePrec['acquisEx']=$ATMdb->Get_field('acquisExerciceNM1');
				$congePrec['acquisAnc']=$ATMdb->Get_field('acquisAncienneteNM1');
				$congePrec['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeNM1');
				$congePrec['reportConges']=$ATMdb->Get_field('reportCongesNM1');
				$congePrec['congesPris']=$ATMdb->Get_field('congesPrisNM1');
				$congePrec['annee']=$ATMdb->Get_field('anneeNM1');
				$congePrec['fk_user']=$ATMdb->Get_field('fk_user');
	}
	
	$congePrecTotal=$congePrec['acquisEx']+$congePrec['acquisAnc']+$congePrec['acquisHorsPer']+$congePrec['reportConges'];
	$congePrecReste=$congePrecTotal-$congePrec['congesPris'];
	
	//////////////////////////récupération des informations des congés précédents (N-1) de l'utilisateur courant : 
	$sqlReqUser2="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$user->id." AND anneeN=".$anneeCourante;//."AND entity=".$conf->entity;;
	$ATMdb=new Tdb;
	$ATMdb->Execute($sqlReqUser2);
	$congeCourant=array();
	while($ATMdb->Get_line()) {
				$congeCourant['id']=$ATMdb->Get_field('rowid');
				$congeCourant['acquisEx']=$ATMdb->Get_field('acquisExerciceN');
				$congeCourant['acquisAnc']=$ATMdb->Get_field('acquisAncienneteN');
				$congeCourant['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeN');
				$congeCourant['annee']=$ATMdb->Get_field('anneeN');
				$congeCourant['fk_user']=$ATMdb->Get_field('fk_user');
	}
	$congeCourantTotal=$congeCourant['acquisEx']+$congeCourant['acquisAnc']+$congeCourant['acquisHorsPer'];
	
	//////////////////////////////récupération des informations des rtt courants (année N) de l'utilisateur courant : 
	$sqlRtt="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$user->id;
	$ATMdb->Execute($sqlRtt);
	$rttCourant=array();
	while($ATMdb->Get_line()) {
				$rttCourant['id']=$ATMdb->Get_field('rowid');
				$rttCourant['acquis']=$ATMdb->Get_field('rttAcquisMensuel')+$ATMdb->Get_field('rttAcquisAnnuelCumule')+$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				$rttCourant['pris']=$ATMdb->Get_field('rttPris');
				$rttCourant['mensuel']=$ATMdb->Get_field('rttAcquisMensuel');
				$rttCourant['annuelCumule']=$ATMdb->Get_field('rttAcquisAnnuelCumule');
				$rttCourant['annuelNonCumule']=$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				$rttCourant['typeAcquisition']=$ATMdb->Get_field('rttTypeAcquisition');
				$rttCourant['annee']=substr($ATMdb->Get_field('anneertt'),0,4);
				$rttCourant['fk_user']=$ATMdb->Get_field('fk_user');
	}
	$rttCourantReste=$rttCourant['acquis']-$rttCourant['pris'];
	
	//récupération informations utilisateur dont on observe l'absence, ou la crée
	if($absence->fk_user!=0){
		$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$absence->fk_user;//AND entity=".$conf->entity;
	}else{
		$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$user->id;//AND entity=".$conf->entity;
	}
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($db);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('name');
	}
	
	$estValideur=$absence->estValideur($ATMdb,$user->id);
	
	if($absence->fk_user==0){
		$regleId=$user->id;
	}else $regleId=$absence->fk_user;
	
	
	//affichage des règles liées à l'utilisateur 
	$sql="SELECT DISTINCT u.rowid, r.typeAbsence, r.`nbJourCumulable`, r. `restrictif`, r.fk_user, r.fk_usergroup, r.choixApplication
		FROM ".MAIN_DB_PREFIX."user as u,  ".MAIN_DB_PREFIX."usergroup_user as g, ".MAIN_DB_PREFIX."rh_absence_regle as r
		WHERE( r.fk_user=u.rowid AND r.fk_user=".$regleId." AND r.choixApplication Like 'user' AND g.fk_user=u.rowid) 
		OR (r.choixApplication Like 'all' AND u.rowid=".$regleId." and u.rowid=g.fk_user) 
		OR (r.choixApplication Like 'group' AND r.fk_usergroup=g.fk_usergroup AND u.rowid=g.fk_user AND g.fk_user=".$regleId.") 
		ORDER BY r.nbJourCumulable";

		$ATMdb->Execute($sql);
		$TRegle = array();
		$k=0;
		while($ATMdb->Get_line()) {
			$TRegle[$k]['rowid']= $ATMdb->Get_field('rowid');
			$TRegle[$k]['typeAbsence']= $ATMdb->Get_field('typeAbsence');
			$TRegle[$k]['libelle']= saveLibelle($ATMdb->Get_field('typeAbsence'));
			$TRegle[$k]['nbJourCumulable']= $ATMdb->Get_field('nbJourCumulable');
			$TRegle[$k]['restrictif']= $ATMdb->Get_field('restrictif')==1?'Oui':'Non';
			$TRegle[$k]['fk_user']= $ATMdb->Get_field('fk_user');
			$TRegle[$k]['fk_usergroup']= $ATMdb->Get_field('fk_usergroup');
			$TRegle[$k]['choixApplication']= $ATMdb->Get_field('choixApplication');
			$k++;
		}
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/absence.tpl.php'
		,array(
			'TRegle' =>$TRegle
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',$congePrec['acquisEx'],10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',$congePre['acquisAnc'],10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',$congePrec['acquisHorsPer'],10,50,'',$class="text", $default='')
				,'reportConges'=>$form->texte('','reportcongesNM1',$congePrec['reportConges'],10,50,'',$class="text", $default='')
				,'congesPris'=>$form->texte('','congesprisNM1',$congePrec['congesPris'],10,50,'',$class="text", $default='')
				,'anneePrec'=>$form->texte('','anneeNM1',$anneePrec,10,50,'',$class="text", $default='')
				,'total'=>$form->texte('','total',$congePrecTotal,10,50,'',$class="text", $default='')
				,'reste'=>round2Virgule($congePrecReste)
				,'idUser'=>$_REQUEST['id']
			)
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceN',$congeCourant['acquisEx'],10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',$congeCourant['acquisAnc'],10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',$congeCourant['acquisHorsPer'],10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','anneeN',$anneeCourante,10,50,'',$class="text", $default='')
				,'total'=>$form->texte('','total',$congeCourantTotal,10,50,'',$class="text", $default='')
				,'idUser'=>$_REQUEST['id']
			)
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',$rttCourant['acquis'],10,50,'',$class="text", $default='')
				,'rowid'=>$form->texte('','rowid',$rttCourant['id'],10,50,'',$class="text", $default='')
				,'id'=>$form->texte('','fk_user',$_REQUEST['id'],10,50,'',$class="text", $default='')
				,'pris'=>$form->texte('','rttPris',$rttCourant['pris'],10,50,'',$class="text", $default='')
				,'mensuel'=>round2Virgule($rttCourant['mensuel'])
				,'annuelCumule'=>round2Virgule($rttCourant['annuelCumule'])
				,'annuelNonCumule'=>round2Virgule($rttCourant['annuelNonCumule'])
				,'typeAcquisition'=>$form->texte('','typeAcquisition',$rttCourant['typeAcquisition'],10,50,'',$class="text", $default='')
				,'reste'=>$form->texte('','total',$rttCourantReste,10,50,'',$class="text", $default='')
				,'idNum'=>$idRttCourant
			)
			,'absenceCourante'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'id'=>$absence->getId()
				,'commentaire'=>$form->zonetexte('','commentaire',$absence->commentaire, 30,3,'','','-')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->get_date('date_debut'), 10)
				,'ddMoment'=>$form->combo('','ddMoment',$absence->TddMoment,$absence->ddMoment)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->get_date('date_fin'), 10)
				,'dfMoment'=>$form->combo('','dfMoment',$absence->TdfMoment,$absence->dfMoment)
				,'idUser'=>$user->id
				,'comboType'=>$form->combo('','type',$absence->TTypeAbsence,$absence->type)
				,'etat'=>$absence->etat
				,'libelleEtat'=>$form->texte('','etat',$absence->libelleEtat,5,10,'',$class="text", $default='')
				,'duree'=>$form->texte('','duree',round2Virgule($absence->duree),5,10,'',$class="text", $default='')	
				,'dureeHeure'=>$form->texte('','dureeHeure',$absence->dureeHeure,5,10,'',$class="text", $default='')
				,'avertissement'=>$absence->avertissement==1?'<img src="./img/warning.png">  Ne respecte pas les règles en vigueur</img>':'Aucun'
				,'fk_user'=>$absence->fk_user
			)	
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
				,'valideurConges'=>$user->rights->absence->myactions->valideurConges&&$estValideur
				,'enregistrerPaieAbsences'=>$user->rights->absence->myactions->enregistrerPaieAbsences&&$estValideur
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


	
	
