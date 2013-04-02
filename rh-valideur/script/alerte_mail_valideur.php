<?php
/*
 * Script envoyant un mail au validateur chaque jour si besoin pour le notifier des notes de frais à valider
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
	
	global $db;
	
	$langs->load('mails');
	
	$sql = "SELECT";
	$sql.= " u.name,";
	$sql.= " u.firstname,";
	$sql.= " u.email";
	
    $sql.= " FROM ".MAIN_DB_PREFIX."user as u";
    $sql.= " WHERE u.rowid = ".$_REQUEST['fk_user'];
	
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
	
	/*
	* Récupération des Ids sur lesquels le valideur a les droits
	*/
	$resUsers=$db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE fk_user_delegation=".$_REQUEST['fk_user']);
	$TUser=array($_REQUEST['fk_user']);        
	while ($obj = $db->fetch_object($resUsers)){
	     $TUser[] = $obj->rowid;
	}
	       
	$sql = "SELECT COUNT(n.rowid) as 'nbrNdf'
	FROM (((((".MAIN_DB_PREFIX."ndfp as n 
	LEFT JOIN ".MAIN_DB_PREFIX."ndfp_pay_det as p ON (p.fk_ndfp = n.rowid))
	       LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (n.fk_user = u.rowid))
	               LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (s.rowid = n.fk_soc))
	                       LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (n.fk_user=g.fk_user))
	                            LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_valideur_groupe as v ON (g.fk_usergroup=v.fk_usergroup))
	WHERE (v.type='NDFP' 
				AND v.fk_user = ".$_REQUEST['fk_user']."
                AND n.statut = 4
	            AND ((NOW() >= ADDDATE(n.tms, v.nbjours)) OR (n.total_ttc > v.montant))
	)";
	
	$resql_ndf=$db->query($sql);
	$obj_ndf = $db->fetch_object($resql_ndf);
	$nbrNdf=$obj_ndf->nbrNdf;
	
	$from = USER_MAIL_SENDER;
	$sendto = $email;
	
	$TBS=new TTemplateTBS();
	$subject = "Alerte - Validation de notes de frais en attente";
	$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/valideur/tpl/mail.validation.attente.tpl.php'
		,array()
		,array(
			'validation'=>array(
				'nom'=>$name
				,'prenom'=>$firstname
				,'nbr'=>$nbrNdf
			)
		)
	);
	
	// Send mail
	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
    (int)$result = $mail->send();
	
	return 1;