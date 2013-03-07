<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	//llxHeader($head = '', $title='', $help_url='', $target='', $disablejs=0, $disablehead=0, $arrayofjs='', $arrayofcss='', $morequerystring='') 
	//"../wdCalendar/css/dailog.css" ,"../wdCalendar/css/calendar.css", "../wdCalendar/css/dp.css" ,"../wdCalendar/css/alert.css" ,"../wdCalendar/css/main.css" 
    // 

	
	llxHeader('','Calendrier des ressources', '', '', 0,0,
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
	$ressource=new TRH_ressource;
	$ressource->load($ATMdb, $_REQUEST['id']);
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id' => $ressource->getId()
			)
			,'view'=>array(
				'mode'=>$mode
				/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'calendrier', 'Ressource')
			)
			
			
		)	
		
	);
	

	llxFooter();

