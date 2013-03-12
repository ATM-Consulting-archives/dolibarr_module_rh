<?php


/**
 *      Fonction envoyant un mail lors de la validation ou du refus d'une note de frais
 *
 *      @param      object      Object action is done on
 *      @param      user        Object user
 *      @return     int         <0 if KO, 0 if no action are done, >0 if OK
 */
function send_mail_validate($db, $object, $user, $langs, $is_validate)
{
	// On récupère les informations de l'utilisateur
	/*$sql = "SELECT";
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
	
	$langs->load('mails');*/
	
	$from = 'arnaud.pothier@pi.esisar.grenoble-inp.fr';
	$sendto = 'arnaud.pothier@pi.esisar.grenoble-inp.fr';
	
	if($is_validate){
		$message = file_get_contents('../valideur/tpl/mail.validation.acceptation.tpl.php', FILE_USE_INCLUDE_PATH);
	}else{
		$message = file_get_contents('../valideur/tpl/mail.validation.refus.tpl.php', FILE_USE_INCLUDE_PATH);
	}
	
	$subject = $object->ref;
	
	// Send mail
	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
    (int)$result = $mail->send();
	
	return 1;
}