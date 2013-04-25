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
	
	$fiche = isset($_REQUEST['fiche']) ? $_REQUEST['fiche'] : false;
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;
	$fk_user = isset($_REQUEST['fk_user']) ? $_REQUEST['fk_user'] : 0;
	$typeEven = isset($_REQUEST['typeEven']) ? $_REQUEST['typeEven'] : null ;
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form2','GET');
	echo $form->hidden('action', 'afficher');
	//echo $form->hidden('id',$ressource->getId());
	//echo 'Type : '.$type.' id : '.$id.' user : '.$fk_user.' even : '.$typeEven.'<br>';
	$url = ($id>0 ? 'id='.$id : '').($type>0 ? '&type='.$type : '' ).($fk_user>0 ? '&fk_user='.$fk_user : '' ).($typeEven ? '&typeEven='.$typeEven : '' );
	
	//LISTE DE USERS
	$TUser = array('');
	$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
		$TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	
	$TRessource = array('');
	$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE entity=".$conf->entity." 
	AND fk_rh_ressource_type = ".$type;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$TRessource[$ATMdb->Get_field('rowid')] = $ATMdb->Get_field('libelle').' '.$ATMdb->Get_field('numId');
			}

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'ressource'=>array(
				'id' => $ressource->getId()
				,'fiche'=> $fiche
				,'type'=>$form->combo('', 'type', $ressource->TType, $type)
				,'typeURL'=>$type
				,'idRessource'=>$form->combo('', 'id', $TRessource, $id)
				,'fk_user'=>$form->combo('', 'fk_user', $TUser, $fk_user)
				,'typeEven'=>$form->combo('', 'typeEven', array('all'=>'','accident'=>'Accident','reparation'=>'Réparation','facture'=>'Facture'), $typeEven)
				,'URL'=>$url
				,'btValider'=>$form->btsubmit('Valider', 'valider')
			)
			,'view'=>array(
				'mode'=>$mode
				/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'calendrier', 'Ressource')
			)
			
			
		)	
		
	);
	
	$form->end();

	llxFooter();

