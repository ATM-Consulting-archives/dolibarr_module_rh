<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	llxHeader('','Calendrier des absences', '', '', 0,0,
		array(//"/library/wdCalendar/src/jquery.js"   
			"/library/wdCalendar/src/Plugins/Common.js"    
			,"/library/wdCalendar/src/Plugins/datepicker_lang_FR.js" 
			,"/library/wdCalendar/src/Plugins/jquery.datepicker.js" 
			,"/library/wdCalendar/src/Plugins/jquery.alert.js"   
			,"/library/wdCalendar/src/Plugins/jquery.ifrmdailog.js" 
			,"/library/wdCalendar/src/Plugins/wdCalendar_lang_FR.js" 
			,"/library/wdCalendar/src/Plugins/jquery.calendar.js" )
	
	
		,array("/library/wdCalendar/css/dailog.css" 
			,"/library/wdCalendar/css/calendar.css"
			,"/library/wdCalendar/css/dp.css" 
			,"/library/wdCalendar/css/alert.css" 
			,"/library/wdCalendar/css/main.css")
	);
		
	$ATMdb=new Tdb;
	global $user, $conf;
	$absence=new TRH_absence;
	if(isset($_REQUEST['id'])){
		$absence->load($ATMdb, $_REQUEST['id']);
	}else{
		$absence->load($ATMdb, $user->id);
	}
	
	$idGroupe= isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	
	$idCalendar= isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $form->hidden('action', 'afficher');
	echo $form->hidden('id',$absence->getId());
	
	
	$TabUser=array();
	//récupération du tableau utilisateur
	$sqlReq="SELECT u.rowid,u.name, u.firstname FROM ".MAIN_DB_PREFIX."user as u,".MAIN_DB_PREFIX."usergroup_user as g  
	WHERE g.fk_user=u.rowid  AND u.entity=".$conf->entity;
	if($idGroupe!=0){
		$sqlReq.=" AND g.fk_usergroup=".$_REQUEST['groupe'];
	}
	$sqlReq.=" ORDER BY name";
	$ATMdb->Execute($sqlReq);	
	$TabUser[0] = 'Tous';		
	while($ATMdb->Get_line()) {
		$TabUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1').' '.htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
	}
	
	
	$TabGroupe=array();
	$TabGroupe[0] = 'Tous';
	//récupération du tableau groupe
	//LISTE DE GROUPES
	$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity." ORDER BY nom";
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TabGroupe[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
	}
		

	$idUser=$_REQUEST['idUtilisateur']? $_REQUEST['idUtilisateur']:0;
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'absence'=>array(
				'idUser' =>$idUser
				,'idGroupe'=>$idGroupe
				,'TGroupe'=>$form->combo('', 'groupe', $TabGroupe,  $idGroupe)
				//,'TUser'=>$user->rights->absence->myactions->voirToutesAbsences?$form->combo('', 'rowid', $absence->TUser,  $absence->TUser):$form->combo('', 'rowid',$TabUser,  $TabUser)
				,'TUser'=>$form->combo('', 'idUtilisateur', $TabUser,  $idUser)
				//,'droits'=>$droits
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				//,'idAfficher'=>$_REQUEST['rowid']? $_REQUEST['rowid']:0
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->get_date('date_debut'), 10)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->get_date('date_fin'), 10)
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'calendrier', 'Absence')
				,'head3'=>dol_get_fiche_head(absencePrepareHead($absence, 'index')  , 'calendrier', 'Absence')
			)
			
			
		)	
		
	);
	

	llxFooter();

