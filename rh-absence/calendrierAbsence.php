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
	$absence=new TRH_absence;

	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'absence'=>array(
				'idUser' => $_GET['idUser']
				//,'idUser'=>
			)
			,'view'=>array(
				'mode'=>$mode
				/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(absencePrepareHead($absence, 'absence')  , 'calendrier', 'Absence')
				,'head3'=>dol_get_fiche_head(absencePrepareHead($absence, 'index')  , 'calendrier', 'Absence')
			)
			
			
		)	
		
	);
	

	llxFooter();

