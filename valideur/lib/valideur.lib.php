<?php


/**
 *      Fonction envoyant un mail lors de la validation ou du refus d'une note de frais
 *
 * 		@param      db        	Database
 *      @param      object      Object action is done on
 *      @param      user        Object user
 * 		@param      langs       Object langs
 * 		@param      statut      Expense status
 *      @return     int         <0 if KO, 0 if no action are done, >0 if OK
 */
function send_mail(&$db, $object, $user, $langs, $statut)
{
	global $conf;
	
	// On récupère les informations de l'utilisateur
	
	$sql = "SELECT lastname,firstname,email FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$object->fk_user;
	$resql_user=$db->query($sql);
	
	$obj_user = $db->fetch_object($resql_user);
    $name=$obj_user->name;
    $firstname=$obj_user->firstname;
    $email=$obj_user->email;

	/*
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$name=$ATMdb->Get_field('lastname');
		$firstname=$ATMdb->Get_field('firstname');
		$email=$ATMdb->Get_field('email');
	}*/
	
	$from = USER_MAIL_SENDER;
	$sendto = $email;
	
	$TBS=new TTemplateTBS();
	if($object->statut==1){
		$subject = $object->ref." - Acceptée";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.acceptation.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
				)
			)
		);
	}elseif($object->statut==4){
		$subject = $object->ref." - Soumis à validation";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.soumission.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
				)
			)
		);
	}elseif($object->statut==3){
		$subject = $object->ref." - Refusée";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.refus.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
					,'motif'=>(isset($_REQUEST['cancelComment']) ?$_REQUEST['cancelComment']:'')
				)
			)
		);
	}elseif($object->statut==2){
		$subject = $object->ref." - Remboursée";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.rembourse.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
				)
			)
		);
	}
	
	// Send mail
	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
	if(!empty($conf->global->NDFP_MAIL_COPY_VALIDATION))$mail->emailtoBcc = $conf->global->NDFP_MAIL_COPY_VALIDATION;
	
	dol_syslog("Valideur::sendmail content=$from,$sendto,$subject,$message", LOG_DEBUG);
	
    (int)$result = $mail->send(true, 'utf-8');
	//exit("SENDF MAIL $from,$sendto,$subject,$message");
	return (int)$result;
}