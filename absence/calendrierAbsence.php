<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
//print_r(get_defined_vars());	
	llxHeader('','Calendrier des absences/présences', '', '', 0,0,
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
	global $user, $conf;
	$absence=new TRH_absence;
	if(isset($_REQUEST['id'])){
		$absence->load($ATMdb, $_REQUEST['id']);
	}else{
		$absence->load($ATMdb, $user->id);
	}
	
	$idGroupe= isset($_REQUEST['groupe']) ? $_REQUEST['groupe'] : 0;
	
	$idCalendar= isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
	
	$typeAbsence= isset($_REQUEST['typeAbsence']) ? $_REQUEST['typeAbsence'] : 'Tous';
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $form->hidden('action', 'afficher');
	echo $form->hidden('id',$absence->getId());
	
	
	
	
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


	$idUser=$_REQUEST['idUtilisateur']? $_REQUEST['idUtilisateur']:0;
	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'absence'=>array(
				'idUser' =>$idUser
				,'idGroupe'=>$idGroupe
				,'typeAbsence'=>$typeAbsence
				,'TGroupe'=>$form->combo('', 'groupe', $TabGroupe,  $idGroupe)
				//,'TUser'=>$user->rights->absence->myactions->voirToutesAbsences?$form->combo('', 'rowid', $absence->TUser,  $absence->TUser):$form->combo('', 'rowid',$TabUser,  $TabUser)
				,'TUser'=>$form->combo('', 'idUtilisateur', $TabUser,  $idUser)
				,'TTypeAbsence'=>$form->combo('', 'typeAbsence', $TTypeAbsence,  $typeAbsence)
				,'droits'=>$user->rights->absence->myactions->voirToutesAbsences?1:0
				,'btValider'=>$form->btsubmit('Valider', 'valider')
				//,'idAfficher'=>$_REQUEST['rowid']? $_REQUEST['rowid']:0
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->date_debut, 12)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->date_fin, 12)
			)
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'calendrier', 'Absence')
				,'head3'=>dol_get_fiche_head(absencePrepareHead($absence, 'index')  , 'calendrier', 'Absence')
				,'titreCalendar'=>load_fiche_titre("Agenda des absences/présences",'', 'title.png', 0, '')
				,'agendaEnabled'=>0
			)
			
			
		)	
		
	);
	

	llxFooter();

