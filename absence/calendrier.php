<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	/*
	 * Inclusion Agenda
	 */
	require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
	require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
	dol_include_once('/core/class/html.formactions.class.php');
	dol_include_once('/core/class/html.form.class.php');
	if (! empty($conf->projet->enabled)) require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
	
	$filter=GETPOST("filter",'',3);
	$filtera = GETPOST("userasked","int",3)?GETPOST("userasked","int",3):GETPOST("filtera","int",3);
	$filtert = GETPOST("usertodo","int",3)?GETPOST("usertodo","int",3):GETPOST("filtert","int",3);
	$filterd = GETPOST("userdone","int",3)?GETPOST("userdone","int",3):GETPOST("filterd","int",3);
	$showbirthday = empty($conf->use_javascript_ajax)?GETPOST("showbirthday","int"):1;
	$socid = GETPOST("socid","int",1);
	if ($user->societe_id) $socid=$user->societe_id;
	
	$result = restrictedArea($user, 'agenda', 0, '', 'myactions');

	$canedit=1;
	if (! $user->rights->agenda->myactions->read) accessforbidden();
	if (! $user->rights->agenda->allactions->read) $canedit=0;
	if (! $user->rights->agenda->allactions->read || $filter =='mine')  // If no permission to see all, we show only affected to me
	{
	    $filtera=$user->id;
	    $filtert=$user->id;
	    $filterd=$user->id;
	}
	
	$action=GETPOST('action','alpha');
	//$year=GETPOST("year");
	$year=GETPOST("year","int")?GETPOST("year","int"):date("Y");
	$month=GETPOST("month","int")?GETPOST("month","int"):date("m");
	$week=GETPOST("week","int")?GETPOST("week","int"):date("W");
	$day=GETPOST("day","int")?GETPOST("day","int"):0;
	$pid=GETPOST("projectid","int",3);
	$status=GETPOST("status");
	$type=GETPOST("type");
	$maxprint=(isset($_GET["maxprint"])?GETPOST("maxprint"):$conf->global->AGENDA_MAX_EVENTS_DAY_VIEW);
	$actioncode=GETPOST("actioncode","alpha",3)?GETPOST("actioncode","alpha",3):(GETPOST("actioncode")=="0"?'':(empty($conf->global->AGENDA_USE_EVENT_TYPE)?'AC_OTH':''));
		
//print_r(get_defined_vars());	
	llxHeader('', $langs->trans('AbsencesCalendar'), '', '', 0,0,
		array(//"/library/wdCalendar/src/jquery.js"   
			"/rhlibrary/wdCalendar/src/Plugins/Common.js"    
			,"/rhlibrary/wdCalendar/src/Plugins/datepicker_lang_FR.js" 
			,"/rhlibrary/wdCalendar/src/Plugins/jquery.datepicker.js" 
			,"/rhlibrary/wdCalendar/src/Plugins/jquery.alert.js"   
			,"/rhlibrary/wdCalendar/src/Plugins/jquery.ifrmdailog.js" 
			,"/rhlibrary/wdCalendar/src/Plugins/wdCalendar_lang_FR.js" 
			,"/rhlibrary/wdCalendar/src/Plugins/jquery.calendar.js" )
	
	
		,array("/rhlibrary/wdCalendar/css/dailog.css" 
			,"/rhlibrary/wdCalendar/css/calendar.css"
			,"/rhlibrary/wdCalendar/css/dp.css" 
			,"/rhlibrary/wdCalendar/css/alert.css" 
			,"/rhlibrary/wdCalendar/css/main.css")
	);
		
	$ATMdb=new TPDOdb;
	
	$absence=new TRH_absence;
	if(isset($_REQUEST['id'])){
		$absence->load($ATMdb, $_REQUEST['id']);
	}else{
		$absence->load($ATMdb, $user->id);
	}
	
	$idGroupe= isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	
	$idCalendar= isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
	
	$typeAbsence= isset($_REQUEST['typeAbsence']) ? $_REQUEST['typeAbsence'] : 'Tous';
	
	$formATM=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $formATM->hidden('action', 'afficher');
	echo $formATM->hidden('id',$absence->getId());
	
	$form=new Form($db);
	
	
	$TabGroupe=array();
	$TabGroupe[0] = 'Tous';
	//récupération du tableau groupe
	//LISTE DE GROUPES
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup ORDER BY nom";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TabGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
		
	//on récupère tous les types d'absences existants
	$TTypeAbsence=array();
	$TTypeAbsence['Tous']='Tous';
	$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` ";
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$TTypeAbsence[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
	}
	
	//on récupère le tableau des users suivant le groupe
	$TabUser=array();
	$TabUser[0]='Tous';
	if($idGroupe==0){
		$sql="SELECT rowid,lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$user->id;
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()) {
			$TabUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(html_entity_decode(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))).' '.html_entity_decode(htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1'));
		}
		$sql="SELECT u.rowid,u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u";
	}else{
		$sql="SELECT u.rowid,u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u,
		".MAIN_DB_PREFIX."usergroup_user as g 
		WHERE g.fk_user=u.rowid AND g.fk_usergroup=".$idGroupe;
	}
	$sql.=" ORDER BY lastname";
	
	$ATMdb->Execute($sql);
	
	
	while($ATMdb->Get_line()) {
		$TabUser[$ATMdb->Get_field('rowid')]=ucwords(strtolower(html_entity_decode(htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')))).' '.html_entity_decode(htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1'));
	}


	$idUser=__get('idUtilisateur', $user->id);
	
	$formactions=new FormActions($db);
	
	ob_start();
	$formactions->select_type_actions($actioncode, "actioncode", '', (empty($conf->global->AGENDA_USE_EVENT_TYPE)?1:0));
	$actionCodeInput = ob_get_clean();

	if (!empty($conf->projet->enabled) && $user->rights->projet->lire) {

		ob_start();
		dol_include_once('/core/class/html.formprojet.class.php');
		$formproject=new FormProjets($db);
		$formproject->select_projects($socid?$socid:-1, $pid, 'projectid', 64);
		$select_project = ob_get_clean();
	}
	else{
		$select_project = '';
	}	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrierPerso.tpl.php'
		,array()
		,array(
			'absence'=>array(
				'idUser' =>$idUser
				,'idGroupe'=>$idGroupe
				,'typeAbsence'=>$typeAbsence
				,'TGroupe'=>$formATM->combo('', 'groupe', $TabGroupe,  $idGroupe)
				//,'TUser'=>$user->rights->absence->myactions->voirToutesAbsences?$formATM->combo('', 'rowid', $absence->TUser,  $absence->TUser):$formATM->combo('', 'rowid',$TabUser,  $TabUser)
				,'TUser'=>$formATM->combo('', 'idUtilisateur', $TabUser,  $idUser)
				,'droits'=>$user->rights->absence->myactions->voirToutesAbsences?1:0
				,'btValider'=>$formATM->btsubmit($langs->trans('Submit'), 'valider')
				//,'idAfficher'=>$_REQUEST['rowid']? $_REQUEST['rowid']:0
				,'date_debut'=> $formATM->calendrier('', 'date_debut', $absence->date_debut, 12)
				,'date_fin'=> $formATM->calendrier('', 'date_fin', $absence->date_fin, 12)
			)
			,'agenda'=>array(
				'userasked'=>$form->select_dolusers($filtera,'userasked',1,'',!$canedit)
				,'usertodo'=>$form->select_dolusers($filtert,'usertodo',1,'',!$canedit)
				,'userdone'=>$form->select_dolusers($filterd,'userdone',1,'',!$canedit)
				,'actioncode'=>$actionCodeInput
				,'projectid'=>$select_project
				,'projectEnabled'=>(int)(! empty($conf->projet->enabled) && $user->rights->projet->lire)
				,'newEvent'=>dol_buildpath('/comm/action/fiche.php?mainmenu=agenda&leftmenu=agenda&action=create&idmenu=530',1)
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'calendrier', $langs->trans('Absence'))
				,'head3'=>dol_get_fiche_head(absencePrepareHead($absence, 'index')  , 'calendrier', $langs->trans('Absence'))
				,'titreCalendar'=>load_fiche_titre($langs->trans('MySchedule'),'', 'title.png', 0, '')
				,'agendaEnabled'=>(int)$conf->agenda->enabled
				
				,'projectid'=>$pid
				,'actioncode'=>$actioncode
				,'userdone'=>$filterd
				,'usertodo'=>$filtert
				,'userasked'=>$filtera
				,'filter'=>$filter
				,'status'=>$status
				
			)
			,'translate' => array(
				'Absences' => $langs->trans('Absences'),
				'Group' => $langs->trans('Group'),
				'User' => $langs->trans('User'),
				'Diary' => $langs->trans('Diary'),
				'EventsRegisteredBy' => $langs->trans('EventsRegisteredBy'),
				'Or' => $langs->trans('Or'),
				'EventsAffectedTo' => $langs->trans('EventsAffectedTo'),
				'EventsMadeBy' => $langs->trans('EventsMadeBy'),
				'Type' => $langs->trans('Type'),
				'Project' => $langs->trans('Project'),
				'Loading' => $langs->trans('Loading'),
				'ErrImpossibleLoadData' => $langs->trans('ErrImpossibleLoadData'),
				'NewEvent' => $langs->trans('NewEvent'),
				'ClickToCreateNewEvent' => $langs->trans('ClickToCreateNewEvent'),
				'Today' => $langs->trans('Today'),
				'ClickToBackToToday' => $langs->trans('ClickToBackToToday'),
				'Day' => $langs->trans('Day'),
				'Week' => $langs->trans('Week'),
				'Month' => $langs->trans('Month'),
				'Refresh' => $langs->trans('Refresh'),
				'RefreshView' => $langs->trans('RefreshView'),
				'Previous' => $langs->trans('Previous'),
				'Next' => $langs->trans('Next')
			)
			
		)	
		
	);
	

	llxFooter();

