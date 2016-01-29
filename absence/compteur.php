<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$PDOdb=new TPDOdb;
	$compteur=new TRH_Compteur;


	if(isset($_REQUEST['action'])) {
		
		switch($_REQUEST['action']) {
			
			case 'add':
			case 'new':
				_fiche($PDOdb, $compteur,'edit');
				break;	
			case 'compteurAdmin':
				_listeAdmin($PDOdb, $compteur);
				break;
			case 'edit'	:
				$compteur->load($PDOdb, $_REQUEST['id']);
				_fiche($PDOdb, $compteur,'edit');
				break;
				
			case 'save':
				//$PDOdb->db->debug=true;
				
				$compteur->load($PDOdb, $_REQUEST['id']);
				$compteur->reportRtt=0; // on remet à 0 la checkbox avant de setter la nouvelle valeur
				//var_dump($_REQUEST);
				$compteur->set_values($_REQUEST);
				$compteur->save($PDOdb);
				$compteur->load($PDOdb, $_REQUEST['id']);
              //  var_dump($compteur);
				$mesg = '<div class="ok">' . $langs->trans('ChangesMade') . '</div>';
				_fiche($PDOdb, $compteur,'view');
			
				break;
			
			case 'view':
			
				if(isset($_REQUEST['id'])){
					$compteur->load($PDOdb, $_REQUEST['id']);
				}
				elseif(GETPOST('fk_user')>0){
					//récupération compteur en cours
					$compteur->load_by_fkuser($PDOdb, GETPOST('fk_user'));
					
				}
				else{
					$compteur->load_by_fkuser($PDOdb, $user->id);
				}

					_fiche($PDOdb, $compteur,'view');

				break;

			case 'log':
				$compteur->load_by_fkuser($PDOdb, GETPOST('fk_user'));
				_log($PDOdb, $compteur);
				
				break;

			case 'delete':
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		//$PDOdb->db->debug=true;
		_liste($PDOdb, $compteur);
	}

	$PDOdb->close();
	
	
	
function _log(&$PDOdb, &$compteur) {
	global $langs, $conf, $db, $user, $listeGlobale;	
	
	llxHeader('', $langs->trans('CounterLog'));
	
	$req = 'SELECT lastname, firstname FROM ' . MAIN_DB_PREFIX . 'user WHERE rowid = ' . $compteur->fk_user;
	$PDOdb->Execute($req);
	$usr = $PDOdb->Get_line();
		
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
	$r->liste($PDOdb, $sql, array(
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
	
function _liste(&$PDOdb, &$compteur) {
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
	$r->liste($PDOdb, $sql, array(
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
			'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "UTF-8")))'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "UTF-8")'
		)
		
	));
	
		$form->end();
	llxFooter();
}	
	
	
	
function _listeAdmin(&$PDOdb, &$compteur) {
	global $langs, $conf, $db, $user,$listeGlobale;	
	$listeGlobale='admin';
	llxHeader('', $langs->trans('HolidaysCollabCounterList'));
	getStandartJS();
	print dol_get_fiche_head(adminCompteurPrepareHead($compteur, 'compteur')  , 'compteur', $langs->trans('HolidaysAdministration'));
	$r = new TSSRenderControler($compteur);
	
	
	$fk_group = GETPOST('fk_group');
	
	$sql="SELECT  DISTINCT r.rowid as 'ID', login, firstname, lastname, ";
	if($conf->multicompany->enabled) $sql.= " IF(c.entity=0,'Toutes',e.label) as 'Entité', ";
	$sql .=	" '' as 'Compteur',
		r.date_cre as 'DateCre', CAST(r.acquisExerciceN as DECIMAL(16,1)) as 'Congés acquis N', 
		CAST(r.acquisAncienneteN as DECIMAL(16,1)) as 'Congés Ancienneté', 
		CAST(r.acquisExerciceNM1 as DECIMAL(16,1)) as 'Conges Acquis N-1', 
		CAST(r.congesPrisNM1 as DECIMAL(16,1)) as 'Conges Pris N-1'
		FROM ".MAIN_DB_PREFIX."rh_compteur as r 
				INNER JOIN ".MAIN_DB_PREFIX."user as c ON (r.fk_user=c.rowid)
				LEFT JOIN  ".MAIN_DB_PREFIX."usergroup_user as gu ON (r.fk_user=gu.fk_user) ";
	if($conf->multicompany->enabled) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."entity as e ON (e.rowid = c.entity) ";
	$sql .=	" WHERE 1 ";
	$sql .= " AND c.statut=1";
	
	if($fk_group>0) $sql.=" AND gu.fk_usergroup=".$fk_group;
	
	$TOrder = array('lastname'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
	
			
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
	echo $form->hidden('action', 'compteurAdmin');		
	
	$formDoli = new Form($db);
	
	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>$conf->liste_limit
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
			,'picto_search'=>img_picto('','search.png', '', 0)
			,'head_search'=>$formDoli->select_dolgroups($fk_group, 'fk_group',1)
			
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
			'lastname'=>'ucwords(strtolower(htmlentities("@val@", ENT_COMPAT , "UTF-8")))'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "UTF-8")'
		)
		,'orderBy'=>$TOrder

		
	));
	
	$form->end();
	llxFooter();
}	
	
	
function _fiche(&$PDOdb, &$compteur, $mode) {
	global $db,$user,$conf,$TTypeMetier, $langs;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('action', 'save');
	echo $form->hidden('id', $compteur->getId());

	$userCourant=new User($db);
	$userCourant->fetch($compteur->fk_user);
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1; 
	
	$congePrecTotal=$compteur->acquisExerciceNM1 +$compteur->acquisAncienneteNM1+$compteur->acquisHorsPeriodeNM1+$compteur->reportCongesNM1;
	$congePrecReste=$congePrecTotal-$compteur->congesPrisNM1;
	
	$congeCourantTotal=$compteur->acquisExerciceN+$compteur->acquisAncienneteN	+$compteur->acquisHorsPeriodeN;
	
	$rttCourantReste=$compteur->rttCumuleAcquis -$compteur->rttCumulePris;
	
    $TTypeAbsence = TRH_TypeAbsence::getTypeAbsence($PDOdb, 'admin');
    
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/compteur.tpl.php'
		,array(
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',round2Virgule($compteur->acquisExerciceNM1),10,50,'')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',round2Virgule($compteur->acquisAncienneteNM1),10,50,'')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',round2Virgule($compteur->acquisHorsPeriodeNM1),10,50,'')
				,'reportConges'=>$form->texte('','reportCongesNM1',round2Virgule($compteur->reportCongesNM1),10,50,'')
				,'congesPris'=>$form->texte('','congesPrisNM1',round2Virgule($compteur->congesPrisNM1),10,50)
				,'anneePrec'=>$form->texte('','anneeNM1',round2Virgule($compteur->anneeNM1),10,50,'')
				,'total'=>round2Virgule($congePrecTotal)
				,'reste'=>round2Virgule($congePrecReste)
				
				,'dates'=>date('d/m', strtotime('+1day',$compteur->date_congesCloture) ).' au '.date('d/m', $compteur->date_congesCloture )
				,'dateFin'=>date('d/m', $compteur->date_congesCloture )
			)
			
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='')
				'acquisEx'=>$form->texte('','acquisExerciceN',round2Virgule($compteur->acquisExerciceN),10,50,'')
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',round2Virgule($compteur->acquisAncienneteN),10,50,'')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',round2Virgule($compteur->acquisHorsPeriodeN),10,50,'')
				,'anneeCourante'=>$form->texte('','anneeN',round2Virgule($compteur->anneeN),10,50,'')
				,'congesPris'=>$form->texte('','congesPrisN',round2Virgule($compteur->congesPrisN),10,50)
				,'total'=>round2Virgule($congeCourantTotal)
				,'idUser'=>$compteur->fk_user
				,'date_congesCloture'=>date("d/m/Y",$compteur->date_congesCloture)
				,'nombreCongesAcquisMensuel'=>$form->texte('','nombreCongesAcquisMensuel',round2Virgule($compteur->nombreCongesAcquisMensuel),10,50,'')	
				,'nombreCongesAcquisAnnuel'=>$form->texte('','nombreCongesAcquisAnnuel',round2Virgule($compteur->nombrecongesAcquisAnnuel),10,50,'')	
				
				,'titreConges'=>load_fiche_titre($langs->trans('HolidaysPaid'),'', 'title.png', 0, '')

				,'acquisRecuperation'=>$form->texte('','acquisRecuperation',round2Virgule($compteur->acquisRecuperation),10,50)
				
			)
			
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='')
				'acquis'=>$form->texte('','rttAcquis',round2Virgule($compteur->rttAcquis),10,50,'')
				,'rowid'=>$form->texte('','rowid',$compteur->getId(),10,50,'')
				,'mensuel'=>$form->texte('','rttAcquisMensuel',round2Virgule($compteur->rttAcquisMensuel),10,50,'')
				,'annuelCumule'=>$form->texte('','rttAcquisAnnuelCumule',round2Virgule($compteur->rttAcquisAnnuelCumule),10,50,'')
				,'annuelNonCumule'=>$form->texte('','rttAcquisAnnuelNonCumule',round2Virgule($compteur->rttAcquisAnnuelNonCumule),10,50,'')
				,'date_rttCloture'=>date("d/m/Y", $compteur->date_rttCloture)
				,'mensuelInit'=>$form->texte('','rttAcquisMensuelInit',round2Virgule($compteur->rttAcquisMensuelInit),10,50,'')
				,'mensuelTotal'=>$form->texte('','rttAcquisMensuelTotal',round2Virgule($compteur->rttAcquisMensuelTotal),10,50,'')
				,'annuelCumuleInit'=>$form->texte('','rttAcquisAnnuelCumuleInit',round2Virgule($compteur->rttAcquisAnnuelCumuleInit),10,50,'')
				,'annuelNonCumuleInit'=>$form->texte('','rttAcquisAnnuelNonCumuleInit',round2Virgule($compteur->rttAcquisAnnuelNonCumuleInit),10,50,'')
				,'typeAcquisition'=>$form->combo('','rttTypeAcquisition',$compteur->TTypeAcquisition,$compteur->rttTypeAcquisition)
				,'rttMetier'=>$userCourant->job  /*$form->combo('','rttMetier',$TTypeMetier,$compteur->rttMetier)*/
				,'rttTypeAcquis'=>$compteur->rttTypeAcquisition
				,'reste'=>$form->texte('','total',round2Virgule($rttCourantReste),10,50,'')
				,'id'=>$compteur->getId()
				,'reportRtt'=>$form->checkbox1('','reportRtt','1',$compteur->reportRtt)
				

				
				,'cumuleAcquisInit'=>$form->texte('','rttAcquisAnnuelCumuleInit',round2Virgule($compteur->rttAcquisAnnuelCumuleInit),10,50,'')
				,'cumuleAcquis'=>$form->texte('','rttCumuleAcquis',round2Virgule($compteur->rttCumuleAcquis),10,50,'')
				,'cumulePris'=>$form->texte('','rttCumulePris',round2Virgule($compteur->rttCumulePris),10,50,'')
				,'cumuleReport'=>$form->texte('','rttCumuleReportNM1',round2Virgule($compteur->rttCumuleReportNM1),10,50,'')
				,'cumuleTotal'=>$form->texte('','rttCumuleTotal',round2Virgule($compteur->rttCumuleTotal),10,50,'')

				
				,'nonCumuleAcquisInit'=>$form->texte('','rttAcquisAnnuelNonCumuleInit',round2Virgule($compteur->rttAcquisAnnuelNonCumuleInit),10,50,'')
				,'nonCumuleAcquis'=>$form->texte('','rttNonCumuleAcquis',round2Virgule($compteur->rttNonCumuleAcquis),10,50,'')
				,'nonCumulePris'=>$form->texte('','rttNonCumulePris',round2Virgule($compteur->rttNonCumulePris),10,50,'')
				,'nonCumuleReport'=>$form->texte('','rttNonCumuleReportNM1',round2Virgule($compteur->rttNonCumuleReportNM1),10,50,'')
				,'nonCumuleTotal'=>$form->texte('','rttNonCumuleTotal',round2Virgule($compteur->rttNonCumuleTotal),10,50,'')

				
				,'titreRtt'=>load_fiche_titre($langs->trans('DayOff'),'', 'title.png', 0, '')

			)
			
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
				,'link'=>$userCourant->getNomUrl(1)
				,'modifierCompteur'=>$user->rights->absence->myactions->modifierCompteur
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(compteurPrepareHead($compteur, 'compteur',$userCourant->id, htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1'), htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1'))  , 'compteur', $langs->trans('Absence'))
			)
            
			,'translate' => array(
				'Year' 							=> $langs->transnoentities('Year'),
				'CurrentUser' 					=> $langs->transnoentities('CurrentUser'),
				'AcquiredOnExercise' 			=> $langs->transnoentities('AcquiredOnExercise'),
				'AcquiredSeniority' 			=> $langs->transnoentities('AcquiredSeniority'),
				'AcquiredOutOfPeriod' 			=> $langs->transnoentities('AcquiredOutOfPeriod'),
				'OpenPostponement' 				=> $langs->transnoentities('OpenPostponement'),
				'TotalHolidays' 				=> $langs->transnoentities('TotalHolidays'),
				'HolidaysTaken' 				=> $langs->transnoentities('HolidaysTaken'),
				'RemainingBefore' 				=> $langs->transnoentities('RemainingBefore'),
				'AcquiredExercise' 				=> $langs->transnoentities('AcquiredExercise'),
				'HolidaysTaken' 				=> $langs->transnoentities('HolidaysTaken'),
				'NbDaysAcquiredByMonth' 		=> $langs->transnoentities('NbDaysAcquiredByMonth'),
				'LastClosingHoliday' 			=> $langs->transnoentities('LastClosingHoliday'),
				'CounterCumulatedDayOff' 		=> $langs->transnoentities('Counter').' '.$TTypeAbsence['rttcumule'],
				'CumulatedDayOffAcquired' 		=> $langs->transnoentities('CumulatedDayOffAcquired'),
				'CumulatedDayOffTaken' 			=> $langs->transnoentities('CumulatedDayOffTaken'),
				'PostponedCumulatedDayOff' 		=> $langs->transnoentities('PostponedCumulatedDayOff'),
				'CumulatedDayOffToTake' 		=> $langs->transnoentities('CumulatedDayOffToTake'),
				'CounterNonCumulatedDayOff' 	=> $langs->transnoentities('Counter').' '.$TTypeAbsence['rttnoncumule'],
				'NonCumulatedDayOffAcquired' 	=> $langs->transnoentities('NonCumulatedDayOffAcquired'),
				'NonCumulatedDayOffTaken' 		=> $langs->transnoentities('NonCumulatedDayOffTaken'),
				'PostponedNonCumulatedDayOff' 	=> $langs->transnoentities('PostponedNonCumulatedDayOff'),
				'AcquisitionMethodOfDays' 		=> $langs->transnoentities('AcquisitionMethodOfDays'),
				'CollabJob' 					=> $langs->transnoentities('CollabJob'),
				'AcquisitionType' 				=> $langs->transnoentities('AcquisitionType'),
				'AcquiredDaysOffPerMonth' 		=> $langs->transnoentities('AcquiredDaysOffPerMonth'),
				'NbDaysAcquiredByYear' 		=> $langs->transnoentities('NbDaysAcquiredByYear'),
				'YearlyCumulatedDaysOff' 		=> $langs->transnoentities('YearlyCumulatedDaysOff'),
				'YearlyNonCumulatedDaysOff' 	=> $langs->transnoentities('YearlyNonCumulatedDaysOff'),
				'DaysOffPostponement' 			=> $langs->transnoentities('DaysOffPostponement'),
				'LastClosingDayOff' 			=> $langs->transnoentities('LastClosingDayOff'),
				'Register' 						=> $langs->transnoentities('Register'),
				'Cancel' 						=> $langs->transnoentities('Cancel'),
				'Modify' 						=> $langs->transnoentities('Modify'),
				'Total'							=> $langs->transnoentities('Total'),
				'NonCumulatedDaysOffToTake'		=> $langs->transnoentities('NonCumulatedDaysOffToTake'),
				'acquisRecuperation'=>$langs->transnoentities('acquisRecuperation'),
			)
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}

