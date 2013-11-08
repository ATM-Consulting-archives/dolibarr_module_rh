#!/usr/bin/php
<?php
/*
 * Script envoyant un mail au validateur chaque jour si besoin pour le notifier des notes de frais à valider
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('/var/www/dolibarr-rh/htdocs/custom/valideur/config.php');
	
	
	$ATMdb=new TPDOdb;
	$langs->load('mails');
	
	$sql = "SELECT u.rowid, u.lastname,u.firstname,u.email 
	FROM ".MAIN_DB_PREFIX."user u LEFT JOIN ".MAIN_DB_PREFIX."rh_valideur_groupe v ON (v.fk_user=u.rowid)
	WHERE u.email!=''
	AND v.type='NDFP'";
	$ATMdb->Execute($sql);
	$TValideur = array();
	while($ATMdb->Get_line()) {
		
		$TValideur[] = array(
			'id'=>$ATMdb->Get_field('rowid')
			,'name'=>$ATMdb->Get_field('name')
			,'firstname'=>$ATMdb->Get_field('firstname')
			,'email'=>$ATMdb->Get_field('email')
		);
		
	}

	
	foreach($TValideur as $valideur) {
	
		_mail_valideur($ATMdb, $valideur['id'],$valideur['firstname'],$valideur['name'], $valideur['email'] );
		
	}
	
	

	
	return 1;
	
function _mail_valideur(&$ATMdb, $fk_user, $firstname,$name, $sendto) {
		
	$sql = "SELECT COUNT(n.rowid) as 'nbrNdf'
	FROM (((((".MAIN_DB_PREFIX."ndfp as n 
		LEFT JOIN ".MAIN_DB_PREFIX."ndfp_pay_det as p ON (p.fk_ndfp = n.rowid))
		       LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (n.fk_user = u.rowid))
		               LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (s.rowid = n.fk_soc))
		                       LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (n.fk_user=g.fk_user))
		                            LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_valideur_groupe as v ON (g.fk_usergroup=v.fk_usergroup))
	WHERE (v.type='NDFP' 
				AND v.fk_user = ".$fk_user."
                AND n.statut = 4
	            AND ((NOW() >= ADDDATE(n.tms, v.nbjours)) OR (n.total_ttc > v.montant))
	)";
	
	$ATMdb->Execute($sql);
	$ATMdb->Get_line();
	
	$nbrNdf=$ATMdb->Get_field('nbrNdf');
	
	if($nbrNdf>0) {
		/*
		 * S'il y a des ntoe de frais en attente
		 */
		$from = USER_MAIL_SENDER;
			
			$TBS=new TTemplateTBS();
			$subject = "Alerte - Validation de notes de frais en attente (".$nbrNdf.")";
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
			//$mail->emailtoBcc="alexis@atm-consulting.fr";
			
		    (int)$result = $mail->send(true, 'utf-8');		
	}
	
	
	
}
