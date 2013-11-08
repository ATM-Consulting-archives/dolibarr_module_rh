<?php


/**
 *      Fonction envoyant un mail à tous les utilisateurs d'un groupe lors de l'ajout d'un droit à un formulaire
 */
function send_mail_formulaire($object)
{
	global $langs;
	
	$ATMdb = new Tdb;
	
	$langs->load('mails');
	$from = USER_MAIL_SENDER;
	
	$sql = "SELECT s.surveyls_title
			FROM ".LIME_DB.".lime_surveys_languagesettings as s
			WHERE s.surveyls_survey_id = ".$object->fk_survey;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()){
		$titre_form=$ATMdb->Get_field('surveyls_title');
	}
	
	$sql="SELECT u.firstname, u.lastname, u.email
		FROM ".MAIN_DB_PREFIX."user as u
		LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (u.rowid=g.fk_user)
		WHERE g.fk_usergroup = ".$object->fk_usergroup;
	
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()){
		$sendto = $ATMdb->Get_field('email');
	
		$TBS=new TTemplateTBS();
		$subject = "Nouveau questionnaire à remplir - ".$titre_form;
		$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/formulaire/tpl/mail.formulaire.invitation.tpl.php'
			,array()
			,array(
				'formulaire'=>array(
					'nom'=>$ATMdb->Get_field('lastname')
					,'prenom'=>$ATMdb->Get_field('firstname')
					,'dated'=>date('d/m/Y',$object->date_deb)
					,'datef'=>date('d/m/Y',$object->date_fin)
					,'survey'=>$titre_form
				)
			)
		);
		
		// Send mail
		$mail = new TReponseMail($from,$sendto,$subject,$message);
		
	    (int)$result = $mail->send(true, 'utf-8');
		
	}
	
	return 1;
}