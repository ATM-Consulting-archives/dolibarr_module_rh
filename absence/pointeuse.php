<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./class/pointeuse.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	
	$pointeuse=new TRH_Pointeuse;
	llxHeader('','Pointage');
	switch(__get('action','list') ) {
			case 'new':
				$pointeuse->set_values($_REQUEST);
				_fiche($ATMdb, $absence,'edit');	
				break;	

			case 'imcomming':
				$pointeuse->loadByDate($ATMdb, date('Y-m-d'));
				
				if(date('H')<12)
					$pointeuse->date_deb_am = time();
				else  
					$pointeuse->date_deb_pm = time();
						
				$pointeuse->fk_user = $user->id;		
						
				$pointeuse->set_date('date_jour', date('d/m/Y'));					
				$pointeuse->save($ATMdb);
				
				_liste($ATMdb, $pointeuse);	
				break;	


			case 'imleaving':
				$pointeuse->loadByDate($ATMdb, date('Y-m-d'));

				if(date('H')<12)
					$pointeuse->date_fin_am = time();
				else  
					$pointeuse->date_fin_pm = time();

				$pointeuse->fk_user = $user->id;		
				
				$pointeuse->set_date('date_jour', date('d/m/Y'));					
				$pointeuse->save($ATMdb);
				
				_liste($ATMdb, $pointeuse);	
				break;	


			case 'save':
				$pointeuse->load($ATMdb, $_REQUEST['id']);
				$pointeuse->set_values($_REQUEST);
				$pointeuse->save($ATMdb);
				
			case 'view':
				$pointeuse->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $pointeuse,'view');
				break;

			case 'edit':
				$pointeuse->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $pointeuse,'edit');
				break;

			case 'delete':
				$pointeuse->load($ATMdb, $_REQUEST['id']);
				$pointeuse->delete($ATMdb);
				
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?
				break;

			case 'list' : 
				_liste($ATMdb, $pointeuse);
				break;
		}


	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$pointeuse) {
	global $langs, $conf, $db, $user;	
	
	print dol_get_fiche_head(pointeusePrepareHead()  , '', 'Pointeuse');

	$r = new TSSRenderControler($pointeuse);

	$sql="SELECT rowid as 'Id', date_deb_am,date_fin_am,date_deb_pm,date_fin_pm,date_jour
			FROM ".MAIN_DB_PREFIX."rh_pointeuse WHERE 1 ";
			
	$sql.=" AND fk_user=".$user->id; // TODO mode admin
	
	
	$TOrder = array('date_jour'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;	
	
	?><div style="text-align: right">
		<a class="butAction" href="?action=imcomming">J'arrive</a>
		<a class="butAction" href="?action=imleaving">Je pars</a>
		<a class="butAction" href="?id=<?=$pointeuse->getId()?>&action=new">Nouveau pointage</a><div style="clear:both"></div>
	</div><?
	
	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'Id'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		
		,'hide'=>array()
		,'type'=>array('date_deb_am'=>'hour', 'date_fin_am'=>'hour', 'date_deb_pm'=>'hour', 'date_fin_pm'=>'hour', 'date_jour'=>'date')
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
			'date_deb_am'=>'Arrivée le matin'
			, 'date_fin_am'=>'Départ le matin'
			, 'date_deb_pm'=>'Arrivée l\'après-midi'
			, 'date_fin_pm'=>'Départ l\'après-midi'
			, 'date_jour'=>'Jour'
		)
		,'search'=>array(
			'date_jour'=>array('recherche'=>'calendar')
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

