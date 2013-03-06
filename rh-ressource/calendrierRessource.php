<?php
	require('config.php');
	require('./class/ressource.class.php');
	require('./lib/ressource.lib.php');
	
	
	
	llxHeader('','Calendrier des ressources');
	
	$ATMdb=new Tdb;
	$ressource=new TRH_ressource;
	$ressource->load($ATMdb, $_REQUEST['id']);
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/calendrier.tpl.php'
		,array()
		,array(
			'view'=>array(
				'mode'=>$mode
				/*,'userRight'=>((int)$user->rights->financement->affaire->write)*/
				,'head'=>dol_get_fiche_head(ressourcePrepareHead($ressource, 'ressource')  , 'calendrier', 'Ressource')
			)
			
			
		)	
		
	);
	

	llxFooter();

