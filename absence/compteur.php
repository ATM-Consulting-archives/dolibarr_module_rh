<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$compteur=new TRH_Compteur;


	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'add':
			case 'new':
				_fiche($ATMdb, $compteur,'edit');
				break;	
			case 'compteurAdmin':
				_listeAdmin($ATMdb, $compteur);
				break;
			case 'edit'	:
				$compteur->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $compteur,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				
				$compteur->load($ATMdb, $_REQUEST['id']);
				$compteur->reportRtt=0; // on remet à 0 la checkbox avant de setter la nouvelle valeur
				$compteur->set_values($_REQUEST);
				$compteur->save($ATMdb);
				$compteur->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">' . $langs->trans('ChangesMade') . '</div>';
				_fiche($ATMdb, $compteur,'view');
			
				break;
			
			case 'view':
			
				if(isset($_REQUEST['id'])){
					$compteur->load($ATMdb, $_REQUEST['id']);
				}
				elseif(GETPOST('fk_user')>0){
					//récupération compteur en cours
					$compteur->load_by_fkuser($ATMdb, GETPOST('fk_user'));
					
				}
				else{
					$compteur->load_by_fkuser($ATMdb, $user->id);
				}

					_fiche($ATMdb, $compteur,'view');

				break;

			case 'log':
				$compteur->load_by_fkuser($ATMdb, GETPOST('fk_user'));
				_log($ATMdb, $compteur);
				
				break;

			case 'delete':
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		//$ATMdb->db->debug=true;
		_liste($ATMdb, $compteur);
	}

	$ATMdb->close();
	
	
	
function _log(&$ATMdb, &$compteur) {
	global $langs, $conf, $db, $user, $listeGlobale;	
	
	llxHeader('', $langs->trans('CounterLog'));
	
	$req = 'SELECT lastname, firstname FROM ' . MAIN_DB_PREFIX . 'user WHERE rowid = ' . $compteur->fk_user;
	$ATMdb->Execute($req);
	$usr = $ATMdb->Get_line();
		
	getStandartJS();
	print dol_get_fiche_head(compteurPrepareHead($compteur, 'compteur', $compteur->fk_user, $usr->lastname, $usr->firstname)  , 'log', $langs->trans('Log'));

	$r = new TSSRenderControler($compteur);
	$sql="SELECT date_cre, type,nb,motif
		FROM ".MAIN_DB_PREFIX."rh_compteur_log 
		WHERE fk_compteur=".$compteur->getId();
		
	
	$TOrder = array('DateCre'=>'DESC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	
	
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		
		,'translate'=>array()
		,'type'=>array('date_cre'=>'date')
		,'title'=>array(
			'date_cre'=> $langs->trans('DateLog')
		)
		
	));
	
		
	llxFooter();
}		
	
function _liste(&$ATMdb, &$compteur) {
	global $langs, $conf, $db, $user, $listeGlobale;	
	$listeGlobale='normale';
	llxHeader('', $langs->trans('HolidaysCollabCounterList'));
	getStandartJS();
	print dol_get_fiche_head(compteurPrepareHead($compteur, 'compteur',$user->id)  , 'compteur', $langs->trans('HolidaysAdministration'));
	$r = new TSSRenderControler($compteur);
	$sql="SELECT  r.rowid as 'ID', c.login, c.firstname, c.lastname, anneeN as 'annee', 
		r.date_cre as 'DateCre', CAST(r.acquisExerciceN as DECIMAL(16,1)) as 'Congés acquis N', 
		CAST(r.acquisAncienneteN as DECIMAL(16,1)) as 'Congés Ancienneté', 
		CAST(r.acquisExerciceNM1 as DECIMAL(16,1)) as 'Conges Acquis N-1', 
		CAST(r.congesPrisNM1 as DECIMAL(16,1)) as 'Conges Pris N-1',
		CAST(r.rttPris as DECIMAL(16,1))  as 'RttPris'
		FROM ".MAIN_DB_PREFIX."rh_compteur as r INNER JOIN ".MAIN_DB_PREFIX."user as c ON ( r.fk_user=c.rowid ) 
		WHERE r.entity IN (0,".$conf->entity.")";
		
	
	$TOrder = array('DateCre'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'lastname'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'firstname'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre')
		,'type'=>array()
		,'liste'=>array(
			'titre'=> $langs->trans('HolidaysCollabCounterList')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=> $langs->trans('NoAcquiredDaysToShow')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'title'=>array(
			'lastname'=> $langs->trans('LastName')
			,'firstname'=> $langs->trans('FirstName')
		)
		,'orderBy'=>$TOrder
		,'eval'=>array(
			'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		
	));
	
		$form->end();
	llxFooter();
}	
	
	
	
function _listeAdmin(&$ATMdb, &$compteur) {
	global $langs, $conf, $db, $user,$listeGlobale;	
	$listeGlobale='admin';
	llxHeader('', $langs->trans('HolidaysCollabCounterList'));
	getStandartJS();
	print dol_get_fiche_head(adminCompteurPrepareHead($compteur, 'compteur')  , 'compteur', $langs->trans('HolidaysAdministration'));
	$r = new TSSRenderControler($compteur);
	$sql="SELECT  r.rowid as 'ID', login, firstname, lastname, '' as 'Compteur',
		r.date_cre as 'DateCre', CAST(r.acquisExerciceN as DECIMAL(16,1)) as 'Congés acquis N', 
		CAST(r.acquisAncienneteN as DECIMAL(16,1)) as 'Congés Ancienneté', 
		CAST(r.acquisExerciceNM1 as DECIMAL(16,1)) as 'Conges Acquis N-1', 
		CAST(r.congesPrisNM1 as DECIMAL(16,1)) as 'Conges Pris N-1'
		FROM ".MAIN_DB_PREFIX."rh_compteur as r INNER JOIN ".MAIN_DB_PREFIX."user as c ON (r.fk_user=c.rowid) 
		WHERE 1 ";
	
	
	$TOrder = array('lastname'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	
			
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	echo $form->hidden('action', 'compteurAdmin');		
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'Compteur'=>'<a href="?id=@ID@&action=view">'. $langs->trans('Counter') . '</a>'
			,'ID'=>'<a href="?id=@ID@&action=view">@val@</a>'
			
		)
		,'translate'=>array()
		,'hide'=>array('DateCre','ID')
		,'type'=>array()
		,'liste'=>array(
			'titre'=> $langs->trans('HolidaysCollabCounterList')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=> $langs->trans('NoAcquiredDaysToShow')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
			
		)
		,'title'=>array(
			'firstname'=> $langs->trans('FirstName')
			,'lastname'=> $langs->trans('LastName')
			,'login'=> $langs->trans('Login')
		)
		,'search'=>array(
			'firstname'=>true
			,'lastname'=>true
			,'login'=>true
		)
		,'eval'=>array(
			'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "ISO8859-1")))'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		,'orderBy'=>$TOrder

		
	));
	
	$form->end();
	llxFooter();
}	
	
	
function _fiche(&$ATMdb, &$compteur, $mode) {
	global $db,$user,$conf,$TTypeMetier, $langs;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('action', 'save');
	//echo $form->hidden('fk_user', $_REQUEST['id']);
	
	//compteur de l'user courant : 
	$sql="SELECT rowid FROM `".MAIN_DB_PREFIX."rh_compteur` WHERE fk_user=".$compteur->fk_user;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$compteurUserCourant=$ATMdb->Get_field('rowid');
	}
	
	
	//récupération informations utilisateur dont on modifie le compte
	$CompteurActuel=$compteurUserCourant;

	echo $form->hidden('id', $CompteurActuel);
	$sqlReqUser="SELECT fk_user FROM `".MAIN_DB_PREFIX."rh_compteur` where rowid=".$CompteurActuel;
	$ATMdb->Execute($sqlReqUser);
	while($ATMdb->Get_line()) {
				$userCompteurActuel=$ATMdb->Get_field('fk_user');
	}
	
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$userCompteurActuel;

	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($db);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('lastname');
	}
	
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1; 
	// TODO fucking object !
	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=". $userCourant->id;
	
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
				$congePrec['date_congesCloture']=strtotime($ATMdb->Get_field('date_congesCloture'));
				
				
				$congeCourant['id']=$ATMdb->Get_field('rowid');
				$congeCourant['acquisEx']=$ATMdb->Get_field('acquisExerciceN');
				$congeCourant['acquisAnc']=$ATMdb->Get_field('acquisAncienneteN');
				$congeCourant['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeN');
				$congeCourant['congesPris']=$ATMdb->Get_field('congesPrisN');
				$congeCourant['annee']=$ATMdb->Get_field('anneeN');
				$congeCourant['fk_user']=$ATMdb->Get_field('fk_user');
				$congeCourant['nombreCongesAcquisMensuel']=$ATMdb->Get_field('nombreCongesAcquisMensuel');
				$congeCourant['acquisRecuperation']=$ATMdb->Get_field('acquisRecuperation');
				
				
				$rttCourant['id']=$ATMdb->Get_field('rowid');
				$rttCourant['typeAcquisition']=$ATMdb->Get_field('rttTypeAcquisition');
				/*if($rttCourant['typeAcquisition']=='Annuel'){
					$rttCourant['acquis']=$ATMdb->Get_field('rttAcquisMensuelInit')+$ATMdb->Get_field('rttAcquisAnnuelCumuleInit')+$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				}else if($rttCourant['typeAcquisition']=='Mensuel'){
					$rttCourant['acquis']=$ATMdb->Get_field('rttAcquisMensuelTotal');
				}*/
				
				
				// RTT cumulés
				
				
				$rttCourant['cumuleAcquisInit']=$ATMdb->Get_field('rttAcquisAnnuelCumuleInit');
				$rttCourant['cumuleAcquis']=$ATMdb->Get_field('rttCumuleAcquis');
				$rttCourant['cumulePris']=$ATMdb->Get_field('rttCumulePris');
				$rttCourant['cumuleReport']=$ATMdb->Get_field('rttCumuleReportNM1');
				$rttCourant['cumuleTotal']=$rttCourant['cumuleAcquis']+$rttCourant['cumuleReport']-$rttCourant['cumulePris'];
				
				//RTT non cumulés
				$rttCourant['nonCumuleAcquisInit']=$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				$rttCourant['nonCumuleAcquis']=$ATMdb->Get_field('rttNonCumuleAcquis');
				$rttCourant['nonCumulePris']=$ATMdb->Get_field('rttNonCumulePris');
				$rttCourant['nonCumuleReport']=$ATMdb->Get_field('rttNonCumuleReportNM1');
				$rttCourant['nonCumuleTotal']=$rttCourant['nonCumuleAcquis']+$rttCourant['nonCumuleReport']-$rttCourant['nonCumulePris'];
				

				
				$rttCourant['rttMetier']=$ATMdb->Get_field('rttMetier');
				$rttCourant['annuelCumuleInit']=$ATMdb->Get_field('rttAcquisAnnuelCumuleInit');
				$rttCourant['annuelNonCumuleInit']=$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				$rttCourant['mensuelInit']=$ATMdb->Get_field('rttAcquisMensuelInit');
				$rttCourant['annee']=substr($ATMdb->Get_field('anneertt'),0,4);
				$rttCourant['fk_user']=$ATMdb->Get_field('fk_user');
				
				$rttCourant['dateConges']=$ATMdb->Get_field('date_congesCloture');
				$rttCourant['dateRtt']=$ATMdb->Get_field('date_rttCloture');
				$rttCourant['nombreCongesAcquisMensuel']=$ATMdb->Get_field('nombreCongesAcquisMensuel');
				
				
	}


	$congePrecTotal=$congePrec['acquisEx']+$congePrec['acquisAnc']+	$congePrec['acquisHorsPer']+$congePrec['reportConges'];
	$congePrecReste=$congePrecTotal-$congePrec['congesPris'];
	
	$congeCourantTotal=$congeCourant['acquisEx']+$congeCourant['acquisAnc']	+$congeCourant['acquisHorsPer'];
	
	$rttCourantReste=$rttCourant['acquis']-$rttCourant['pris'];
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/compteur.tpl.php'
		,array(
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',round2Virgule($congePrec['acquisEx']),10,50,'')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',round2Virgule($congePrec['acquisAnc']),10,50,'')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',round2Virgule($congePrec['acquisHorsPer']),10,50,'')
				,'reportConges'=>$form->texte('','reportCongesNM1',round2Virgule($congePrec['reportConges']),10,50,'')
				,'congesPris'=>$form->texte('','congesPrisNM1',round2Virgule($congePrec['congesPris']),10,50)
				,'anneePrec'=>$form->texte('','anneeNM1',round2Virgule($anneePrec),10,50,'')
				,'total'=>round2Virgule($congePrecTotal)
				,'reste'=>round2Virgule($congePrecReste)
				,'idUser'=>$congePrec->fk_user
				,'user'=>$_REQUEST['id']?$_REQUEST['id']:$user->id
				,'dates'=>date('d/m', strtotime('+1day' ,$congePrec['date_congesCloture']) ).' au '.date('d/m', $congePrec['date_congesCloture'] )
				,'dateFin'=>date('d/m', $congePrec['date_congesCloture'] )
			)
			
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceN',round2Virgule($congeCourant['acquisEx']),10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',round2Virgule($congeCourant['acquisAnc']),10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',round2Virgule($congeCourant['acquisHorsPer']),10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','anneeN',round2Virgule($anneeCourante),10,50,'',$class="text", $default='')
				,'congesPris'=>$form->texte('','congesPrisN',round2Virgule($congeCourant['congesPris']),10,50)
				,'total'=>round2Virgule($congeCourantTotal)
				,'idUser'=>$congeCourant->fk_user
				,'date_congesCloture'=>date("d/m/Y",strtotime($rttCourant['dateConges']))
				,'nombreCongesAcquisMensuel'=>$form->texte('','nombreCongesAcquisMensuel',round2Virgule($rttCourant['nombreCongesAcquisMensuel']),10,50,'',$class="text", $default='')	
				
				,'titreConges'=>load_fiche_titre($langs->trans('HolidaysPaid'),'', 'title.png', 0, '')

				,'acquisRecuperation'=>$form->texte('','acquisRecuperation',round2Virgule($congeCourant['acquisRecuperation']),10,50)
				
			)
			
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',round2Virgule($rttCourant['acquis']),10,50,'',$class="text", $default='')
				,'rowid'=>$form->texte('','rowid',round2Virgule($rttCourant['id']),10,50,'',$class="text", $default='')
				,'mensuel'=>$form->texte('','rttAcquisMensuel',round2Virgule($rttCourant['mensuel']),10,50,'',$class="text", $default='')
				,'annuelCumule'=>$form->texte('','rttAcquisAnnuelCumule',round2Virgule($rttCourant['annuelCumule']),10,50,'',$class="text", $default='')
				,'annuelNonCumule'=>$form->texte('','rttAcquisAnnuelNonCumule',round2Virgule($rttCourant['annuelNonCumule']),10,50,'',$class="text", $default='')
				,'date_rttCloture'=>date("d/m/Y",strtotime($rttCourant['dateRtt']))
				,'mensuelInit'=>$form->texte('','rttAcquisMensuelInit',round2Virgule($rttCourant['mensuelInit']),10,50,'',$class="text", $default='')
				,'mensuelTotal'=>$form->texte('','rttAcquisMensuelTotal',round2Virgule($rttCourant['mensuelTotal']),10,50,'',$class="text", $default='')
				,'annuelCumuleInit'=>$form->texte('','rttAcquisAnnuelCumuleInit',round2Virgule($rttCourant['annuelCumuleInit']),10,50,'',$class="text", $default='')
				,'annuelNonCumuleInit'=>$form->texte('','rttAcquisAnnuelNonCumuleInit',round2Virgule($rttCourant['annuelNonCumuleInit']),10,50,'',$class="text", $default='')
				,'typeAcquisition'=>$form->combo('','rttTypeAcquisition',$compteur->TTypeAcquisition,$compteur->rttTypeAcquisition)
				,'rttMetier'=>$form->combo('','rttMetier',$TTypeMetier,$rttCourant['rttMetier'])
				,'rttTypeAcquis'=>$compteur->rttTypeAcquisition
				,'reste'=>$form->texte('','total',round2Virgule($rttCourantReste),10,50,'',$class="text", $default='')
				,'id'=>$compteur->getId()
				,'reportRtt'=>$form->checkbox1('','reportRtt','1',$compteur->reportRtt)
				

				
				,'cumuleAcquisInit'=>$form->texte('','rttAcquisAnnuelCumuleInit',round2Virgule($rttCourant['cumuleAcquisInit']),10,50,'',$class="text", $default='')
				,'cumuleAcquis'=>$form->texte('','rttCumuleAcquis',round2Virgule($rttCourant['cumuleAcquis']),10,50,'',$class="text", $default='')
				,'cumulePris'=>$form->texte('','rttCumulePris',round2Virgule($rttCourant['cumulePris']),10,50,'',$class="text", $default='')
				,'cumuleReport'=>$form->texte('','rttCumuleReportNM1',round2Virgule($rttCourant['cumuleReport']),10,50,'',$class="text", $default='')
				,'cumuleTotal'=>$form->texte('','rttCumuleTotal',round2Virgule($rttCourant['cumuleTotal']),10,50,'',$class="text", $default='')

				
				,'nonCumuleAcquisInit'=>$form->texte('','rttAcquisAnnuelNonCumuleInit',round2Virgule($rttCourant['nonCumuleAcquisInit']),10,50,'',$class="text", $default='')
				,'nonCumuleAcquis'=>$form->texte('','rttNonCumuleAcquis',round2Virgule($rttCourant['nonCumuleAcquis']),10,50,'',$class="text", $default='')
				,'nonCumulePris'=>$form->texte('','rttNonCumulePris',round2Virgule($rttCourant['nonCumulePris']),10,50,'',$class="text", $default='')
				,'nonCumuleReport'=>$form->texte('','rttNonCumuleReportNM1',round2Virgule($rttCourant['nonCumuleReport']),10,50,'',$class="text", $default='')
				,'nonCumuleTotal'=>$form->texte('','rttNonCumuleTotal',round2Virgule($rttCourant['nonCumuleTotal']),10,50,'',$class="text", $default='')

				
				,'titreRtt'=>load_fiche_titre($langs->trans('DayOff'),'', 'title.png', 0, '')

			)
			
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
				,'modifierCompteur'=>$user->rights->absence->myactions->modifierCompteur
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(compteurPrepareHead($compteur, 'compteur',$userCourant->id, htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1'), htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1'))  , 'compteur', $langs->trans('Absence'))
			)
			,'translate' => array(
				'Year' 							=> $langs->trans('Year'),
				'CurrentUser' 					=> $langs->trans('CurrentUser'),
				'AcquiredOnExercise' 			=> $langs->trans('AcquiredOnExercise'),
				'AcquiredSeniority' 			=> $langs->trans('AcquiredSeniority'),
				'AcquiredOutOfPeriod' 			=> $langs->trans('AcquiredOutOfPeriod'),
				'OpenPostponement' 				=> $langs->trans('OpenPostponement'),
				'TotalHolidays' 				=> $langs->trans('TotalHolidays'),
				'HolidaysTaken' 				=> $langs->trans('HolidaysTaken'),
				'RemainingBefore' 				=> $langs->trans('RemainingBefore'),
				'AcquiredExercise' 				=> $langs->trans('AcquiredExercise'),
				'HolidaysTaken' 				=> $langs->trans('HolidaysTaken'),
				'NbDaysAcquiredByMonth' 		=> $langs->trans('NbDaysAcquiredByMonth'),
				'LastClosingHoliday' 			=> $langs->trans('LastClosingHoliday'),
				'CounterCumulatedDayOff' 		=> $langs->trans('CounterCumulatedDayOff'),
				'CumulatedDayOffAcquired' 		=> $langs->trans('CumulatedDayOffAcquired'),
				'CumulatedDayOffTaken' 			=> $langs->trans('CumulatedDayOffTaken'),
				'PostponedCumulatedDayOff' 		=> $langs->trans('PostponedCumulatedDayOff'),
				'CumulatedDayOffToTake' 		=> $langs->trans('CumulatedDayOffToTake'),
				'CounterNonCumulatedDayOff' 	=> $langs->trans('CounterNonCumulatedDayOff'),
				'NonCumulatedDayOffAcquired' 	=> $langs->trans('NonCumulatedDayOffAcquired'),
				'NonCumulatedDayOffTaken' 		=> $langs->trans('NonCumulatedDayOffTaken'),
				'PostponedNonCumulatedDayOff' 	=> $langs->trans('PostponedNonCumulatedDayOff'),
				'AcquisitionMethodOfDays' 		=> $langs->trans('AcquisitionMethodOfDays'),
				'CollabJob' 					=> $langs->trans('CollabJob'),
				'AcquisitionType' 				=> $langs->trans('AcquisitionType'),
				'AcquiredDaysOffPerMonth' 		=> $langs->trans('AcquiredDaysOffPerMonth'),
				'YearlyCumulatedDaysOff' 		=> $langs->trans('YearlyCumulatedDaysOff'),
				'YearlyNonCumulatedDaysOff' 	=> $langs->trans('YearlyNonCumulatedDaysOff'),
				'DaysOffPostponement' 			=> $langs->trans('DaysOffPostponement'),
				'LastClosingDayOff' 			=> $langs->trans('LastClosingDayOff'),
				'Register' 						=> $langs->trans('Register'),
				'Cancel' 						=> $langs->trans('Cancel'),
				'Modify' 						=> $langs->trans('Modify'),
				'Total'							=> $langs->trans('Total'),
				'NonCumulatedDaysOffToTake'		=> $langs->trans('NonCumulatedDaysOffToTake'),
				'acquisRecuperation'=>$langs->trans('acquisRecuperation'),
			)
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

