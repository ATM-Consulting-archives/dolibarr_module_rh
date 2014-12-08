<?php

	require('../config.php');

	$from = USER_MAIL_SENDER;

    	$name=$user->lastname;
    	$firstname=$user->firstname;

	/*
	 * Mail destinataire
	 */

	$sendto = $user->email;

	$TBS=new TTemplateTBS();
	
		$subject = $langs->trans('NewAbsenceRequestWaitingValidation');
		$tpl = dol_buildpath('/absence/tpl/mail.absence.creationValideur.tpl.php');
	
	$message = $TBS->render($tpl
		,array()
		,array()
	);
	
var_dump($from,$sendto,$subject,$message);

	$mail = new TReponseMail($from,$sendto,$subject,$message);
    	
	$result = $mail->send(true, 'utf-8');
