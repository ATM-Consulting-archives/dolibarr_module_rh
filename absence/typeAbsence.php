<?php

	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;
	
	llxHeader("Type d'absence");

	$TAbsenceType = TRH_TypeAbsence::getList($ATMdb);

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $compteur->getId());
	echo $form->hidden('action', 'save');
	//echo $form->hidden('fk_user', $_REQUEST['id']);

	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/typeAbsence.tpl.php'
		,array(
			'typeAbsence'=>$TAbsenceType
		)
		,array(
			'view'=>array(
				'head'=>dol_get_fiche_head(adminCongesPrepareHead('compteur')  , 'typeabsence', 'Administration des absences et présences')
			)
			
			
		)	
		
	);
	
	echo $form->end_form();	
	
	llxFooter();