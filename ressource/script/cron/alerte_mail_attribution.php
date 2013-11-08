#!/usr/bin/php
<?php
/*
 * Script envoyant un mail au responsable pour l'informer des attributions terminant très bientôt
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../../config.php');
	
	
	$ATMdb=new TPDOdb;
	$langs->load('mails');
	
	$sql = "SELECT r.numId, r.libelle, u.name, u.firstname, e.date_fin
	FROM ".MAIN_DB_PREFIX."user u
		LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement e ON (e.fk_user=u.rowid)
		LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource r ON (e.fk_rh_ressource=r.rowid)
	WHERE e.type='emprunt'
	AND DATEDIFF(e.date_fin,NOW())<".DAYS_BEFORE_ALERT."
	AND DATEDIFF(e.date_fin,NOW())>=0";
	
	$ATMdb->Execute($sql);
	$TAttribution = array();
	while($ATMdb->Get_line()) {
		$TAttribution[] = array(
			'ressourceID'=>$ATMdb->Get_field('numId')
			,'ressourceName'=>$ATMdb->Get_field('libelle')
			,'userName'=>$ATMdb->Get_field('name')
			,'userFirstname'=>$ATMdb->Get_field('firstname')
			,'dateFinAttribution'=>$ATMdb->Get_field('date_fin')
		);
		
	}
	
	_mail_attribution($ATMdb, $TAttribution);
	
	return 1;
	
function _mail_attribution(&$ATMdb, &$TAttribution) {
	
	$from = USER_MAIL_SENDER;
	$sendto = USER_MAIL_RECEIVER;
	
	if(count($TAttribution)>0){
		$TBS=new TTemplateTBS();
		$subject = "Alerte - Ressources en fin d'attribution";
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/ressource/tpl/mail.attribution.alerte.tpl.php'
			,array(
				'attribution'=>$TAttribution
			)
			,array(
			)
		);
		
		// Send mail
		$mail = new TReponseMail($from,$sendto,$subject,$message);
		
	    (int)$result = $mail->send(true, 'utf-8');
	}
	
}