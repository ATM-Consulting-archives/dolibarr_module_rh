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
	// On récupère l'id de l'utilisateur concerné par la note de frais
	$sql = "SELECT";
	$sql.= " n.dates as 'dated',";
	$sql.= " n.datee as 'datef',";
	$sql.= " n.fk_user";
	
    $sql.= " FROM ".MAIN_DB_PREFIX."ndfp as n";
    $sql.= " WHERE n.rowid = ".$object->id;
	print "Blurp <3";
	$resql_ndfp=$db->query($sql);
	
	if ($resql_ndfp){
        $num_ndfp = $db->num_rows($resql_ndfp);
        $n = 0;
        if ($num_ndfp){
            while ($n < $num_ndfp){
                $obj_ndfp = $db->fetch_object($resql_ndfp);
				
                if ($obj_ndfp){
					$fk_user_ndfp=$obj_ndfp->fk_user;
				}
                $n++;
            }
        }
    }else{
        $error++;
        dol_print_error($db);
    }
	
	// On récupère maintenant les informations de l'utilisateur
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
		$message = "Votre note de frais a été validée.";
	}else{
		$message = "Votre note de frais a été refusée.";
	}
	
	$subject = $object->ref;
	print "Heyyy <3";
	// Send mail
	$mail = new TReponseMail($from,$sendto,$subject,$message);
	print "Hop <3";
    (int)$result = $mail->send();
	
	print "Mail envoyé !";
	
	return 1;
}