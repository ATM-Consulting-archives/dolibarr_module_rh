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
function send_mail($db, $object, $user, $langs, $statut)
{
	// On récupère les informations de l'utilisateur
	$sql = "SELECT";
	$sql.= " u.name,";
	$sql.= " u.firstname,";
	$sql.= " u.email";
	
    $sql.= " FROM ".MAIN_DB_PREFIX."user as u";
    $sql.= " WHERE u.rowid = ".$object->fk_user;
	
	$resql_user=$db->query($sql);
	
	if ($resql_user){
        $num_user = $db->num_rows($resql_user);
        $m = 0;
        if ($num_user){
            while ($m < $num_user){
                $obj_user = $db->fetch_object($resql_user);

                if ($obj_user){
					$name=$obj_user->name;
					$firstname=$obj_user->firstname;
					$email=$obj_user->email;
				}
                $m++;
            }
        }
    }else{
        $error++;
        dol_print_error($db);
    }
	
	$langs->load('mails');
	
	$from = USER_MAIL_SENDER;
	$sendto = $email;
	
	$TBS=new TTemplateTBS();
	if($statut==1){
		$subject = $object->ref." - Acceptée";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/valideur/tpl/mail.validation.acceptation.tpl.php'
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
	}elseif($statut==2){
		$subject = $object->ref." - Soumis à validation";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/valideur/tpl/mail.validation.soumission.tpl.php'
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
	}elseif($statut==3){
		$subject = $object->ref." - Refusée";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/valideur/tpl/mail.validation.refus.tpl.php'
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
	}elseif($statut==4){
		$subject = $object->ref." - Remboursée";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/valideur/tpl/mail.validation.rembourse.tpl.php'
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
	
    (int)$result = $mail->send();
	
	return 1;
}