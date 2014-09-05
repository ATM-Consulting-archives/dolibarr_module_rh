<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	$emploiTemps=new TRH_EmploiTemps;

	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {

			case 'edit'	:
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $emploiTemps,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$emploiTemps->load($ATMdb, $_REQUEST['id']);
				
				$emploiTemps->razCheckbox($ATMdb, $emploiTemps);
				
				$emploiTemps->set_values($_REQUEST);
				
				$emploiTemps->tempsHebdo=$emploiTemps->calculTempsHebdo($ATMdb, $emploiTemps);
				
				$emploiTemps->save($ATMdb);
				
				$mesg = '<div class="ok">' . $langs->trans('RegistedRequest') . '</div>';
				_fiche($ATMdb, $emploiTemps,'view');
				break;
			case 'archive':
				if(GETPOST('id','int')>0) $emploiTemps->load($ATMdb, GETPOST('id','int'));
				else $emploiTemps->loadByuser($ATMdb, GETPOST('fk_user','int'));
				
				$emploiTempsArchive = clone $emploiTemps;
				
				$ATMdb->Execute("SELECT MAX(date_fin) as date_fin 
					FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps WHERE fk_user=".$emploiTemps->fk_user." AND is_archive=1");
				$row = $ATMdb->Get_line();
				if($row) {
					$emploiTempsArchive->date_debut = strtotime($row->date_fin);
				}
				$emploiTempsArchive->date_fin = time();
				
				$emploiTempsArchive->rowid=0;
				$emploiTempsArchive->is_archive=1;
				
				$emploiTempsArchive->save($ATMdb);
				setEventMessage($langs->trans('ArchivedSchedule'));
				
				_fiche($ATMdb, $emploiTemps,'view');
				
				break;
			case 'deleteArchive':
				
				$emploiTempsArchive=new TRH_EmploiTemps;
				$emploiTempsArchive->load($ATMdb, GETPOST('idArchive','int'));
				$emploiTempsArchive->delete($ATMdb);
				
				setEventMessage($langs->trans('ScheduleArchiveDeleted'));
				
				if(GETPOST('id','int')>0) $emploiTemps->load($ATMdb, GETPOST('id','int'));
				else $emploiTemps->loadByuser($ATMdb, GETPOST('fk_user','int'));
				_fiche($ATMdb, $emploiTemps,'view');
				
				break;	
			
			case 'view':
					if(GETPOST('id','int')>0) $emploiTemps->load($ATMdb, GETPOST('id','int'));
					else $emploiTemps->loadByuser($ATMdb, GETPOST('fk_user','int'));
					_fiche($ATMdb, $emploiTemps,'view');
				break;

		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		if($user->rights->absence->myactions->voirTousEdt){
			$emploiTemps->loadByuser($ATMdb, $_REQUEST['fk_user']);
			_liste($ATMdb, $emploiTemps);
		}else{

			$emploiTemps->loadByuser($ATMdb, $user->id);
			_fiche($ATMdb, $emploiTemps,'view');
		}
		
	}
	
	$ATMdb->close();
	llxFooter();
	
	
function _liste(&$ATMdb, &$emploiTemps) {
	global $langs, $conf, $db, $user;	
	llxHeader('', $langs->trans('ListOfAbsence'));
	getStandartJS();
	print dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', $langs->trans('Absence'));
	
	$r = new TSSRenderControler($emploiTemps);
	$sql="SELECT DISTINCT e.rowid as 'ID', e.date_cre as 'DateCre', 
	 e.fk_user as 'Id Utilisateur', '' as 'Emploi du temps', u.login
	,u.rowid as 'fk_user',u.firstname, u.lastname
		FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps as e INNER JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
		WHERE e.entity IN (0,".$conf->entity.") AND e.is_archive=0 ";

	if($user->rights->absence->myactions->voirTousEdt!="1"){
		$sql.=" AND e.fk_user=".$user->id;
	}
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');	
	$TOrder = array('lastname'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id=@ID@&action=view&fk_user='.$user->id.'">@val@</a>'
			, $langs->trans('Schedule')=>'<a href="?id=@ID@&action=view&fk_user='.$user->id.'"<a>' . $langs->trans('Schedule') . '</a>'
		)
		,'title'=>array(
			'firstname'=> $langs->trans('FirstName')
			,'lastname'=> $langs->trans('LastName')
			,'login'=> $langs->trans('Login')
		)
		,'translate'=>array()
		,'hide'=>array('DateCre','ID', 'Id Utilisateur')
		,'type'=>array()
		,'liste'=>array(
			'titre'=> $langs->trans('CollabScheduleList')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=> $langs->trans('NoScheduleToShow')
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'orderBy'=>$TOrder
		,'search'=>array(
			'firstname'=>true
			,'lastname'=>true
			,'login'=>true
		)
		,'eval'=>array(
				'lastname'=>'_getNomUrl(@fk_user@, "@val@")'
				,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		
	));
	$form->end();
	llxFooter();
}	
function _getNomUrl($fk_user,$nom) {
global $db;
	$user=new User($db);
	
	$user->id = $fk_user;
	$user->lastname=$nom;
	
	return $user->getNomUrl(1);
}
	
function _fiche(&$ATMdb, &$emploiTemps, $mode) {
	global $db, $user,$idUserCompt, $idComptEnCours,$conf, $langs;
	llxHeader('', $langs->trans('Schedule'));
	$emploiTemps->load($ATMdb, $_REQUEST['id']);
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $_REQUEST['id']);
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_user', $emploiTemps->fk_user);


	$userCourant=new User($db);
	$userCourant->fetch($emploiTemps->fk_user);
	
	$TPlanning=array();
	foreach($emploiTemps->TJour as $jour) {
		foreach(array('am','pm') as $pm) {
			$TPlanning[$jour.$pm]=$form->checkbox1('',$jour.$pm,'1',$emploiTemps->{$jour.$pm}==1?true:false);	
		}
	}
	 
	$THoraire=array();
	foreach($emploiTemps->TJour as $jour) {
		foreach(array('dam','fam','dpm','fpm') as $pm) {
			$THoraire[$jour.'_heure'.$pm]=$form->timepicker('','date_'.$jour.'_heure'.$pm, date('H:i',$emploiTemps->{'date_'.$jour.'_heure'.$pm}) ,5,5);
		}
	} 

	$TEntity=array();
	$TEntity=$emploiTemps->load_entities($ATMdb);
	
	$r=new TListviewTBS('listArchive');
	$listeArchive = $r->render($ATMdb, "SELECT
	 	rowid as ID, date_debut,date_fin,tempsHebdo, '' as 'Actions'
	 FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps 
	 WHERE fk_user=".$userCourant->id." AND is_archive=1 ORDER BY date_debut DESC",array(
	 
	 	'type'=>array('date_debut'=>'date', 'date_fin'=>'date')
		,'translate'=>array(
			'date_debut'=>array('30/11/-0001'=>'-')
			,'date_fin'=>array('30/11/-0001'=>'-')
		)		
		,'link'=>array(
			'Actions'=>'
			<a href="?id=@ID@&action=edit">' . $langs->trans('Update') . '</a>
			<a href="?id='.$emploiTemps->getId().'&idArchive=@ID@&action=deleteArchive">' . $langs->trans('Delete') . '</a>'
		)
		,'title'=>array(
			'date_debut'=> $langs->trans('StartDate')
			,'date_fin'=> $langs->trans('EndDate')
			,'tempsHebdo'=> $langs->trans('WeeklyWorkingTimeInHour')
		)
		
	 ));
	
	$TEmploiTemps = $emploiTemps->get_values();
	
	$TEmploiTemps['date_debut'] = $form->calendrier('', 'date_debut', $emploiTemps->get_date('date_debut')  );
	$TEmploiTemps['date_fin'] = $form->calendrier('', 'date_fin',  $emploiTemps->get_date('date_fin'));
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/emploitemps.tpl.php'
		,array(	
		)
		,array(
			'planning'=>$TPlanning
			,'horaires'=>$THoraire
			,'emploiTemps'=>$TEmploiTemps
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'tempsHebdo'=>$emploiTemps->tempsHebdo
				,'societe'=>$emploiTemps->societeRtt
			)
			,'entity'=>array(
				'TEntity'=>$form->combo('','societeRtt',$TEntity,$emploiTemps->societeRtt)
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'emploitemps', $langs->trans('Absence'))
				,'compteur_id'=>$emploiTemps->getId()
				,'titreEdt'=>load_fiche_titre($langs->trans('ScheduleOf', htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1'), htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')),'', 'title.png', 0, '')
				,'listeArchive'=>$listeArchive
			)
			,'droits'=>array(
				'modifierEdt'=>$user->rights->absence->myactions->modifierEdt
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
