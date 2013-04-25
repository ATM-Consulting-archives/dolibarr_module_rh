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
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $form->hidden('action', 'afficher');
	echo $form->hidden('id',$absence->getId());
	
	$sql=" SELECT DISTINCT s.name,  s.firstname, u.fk_user FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v, ".MAIN_DB_PREFIX."usergroup_user as u, ".MAIN_DB_PREFIX."user as s
			WHERE v.fk_user=".$user->id." 
			AND v.type='Conges'
			AND u.fk_user=s.rowid
			AND v.fk_usergroup=u.fk_usergroup
			AND u.fk_user NOT IN (SELECT a.fk_user FROM ".MAIN_DB_PREFIX."rh_absence as a where a.fk_user=1)
			AND v.entity=".$conf->entity;
		
	$ATMdb->Execute($sql);
	$TabUser=array();
	$k=0;
	while($ATMdb->Get_line()) {
				$TabUser[$ATMdb->Get_field('fk_user')]=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
				$k++;
	}
	
	if($user->rights->absence->myactions->voirToutesAbsences){
		$combo=$form->combo('', 'rowid', $absence->TUser,  $absence->TUser);
		$droits=1;
	}else if($k>0){
		$combo=$form->combo('', 'rowid',$TabUser,  $TabUser);
		$droits=2;
	}else{
		$droits=0;
		$combo='';
	} 
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'absence'=>array(
				'idUser' =>  $_REQUEST['idUser']? $_REQUEST['idUser']:$user->id
				//,'TUser'=>$user->rights->absence->myactions->voirToutesAbsences?$form->combo('', 'rowid', $absence->TUser,  $absence->TUser):$form->combo('', 'rowid',$TabUser,  $TabUser)
				,'TUser'=>$combo
				,'droits'=>$droits
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				,'idAfficher'=>$_REQUEST['rowid']? $_REQUEST['rowid']:0
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'calendrier', 'Absence')
				,'head3'=>dol_get_fiche_head(absencePrepareHead($absence, 'index')  , 'calendrier', 'Absence')
			)
			
			
		)	
		
	);
	

	llxFooter();

