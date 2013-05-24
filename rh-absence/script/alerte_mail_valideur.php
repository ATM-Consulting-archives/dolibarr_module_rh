#!/usr/bin/php
<?php
/*
 * Script envoyant un mail au validateur chaque jour si besoin pour le notifier des notes de frais à valider
 * 
 */
 	define('INC_FROM_CRON_SCRIPT', true);
	require('../config.php');
	
	$ATMdb=new TPDOdb;
	$langs->load('mails');
	
	$sql = "SELECT DISTINCT u.rowid, u.name,u.firstname,u.email 
	FROM ".MAIN_DB_PREFIX."user u LEFT JOIN  llx_rh_valideur_groupe v ON (v.fk_user=u.rowid)
	WHERE u.email!='' 
	AND v.type='Conges'
	";
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
	global $conf;
	//LISTE USERS À VALIDER
	$sql=" SELECT DISTINCT u.fk_user FROM `".MAIN_DB_PREFIX."rh_valideur_groupe` as v, ".MAIN_DB_PREFIX."usergroup_user as u 
			WHERE v.fk_user=".$fk_user." 
			AND v.type='Conges'
			AND v.fk_usergroup=u.fk_usergroup
			AND u.fk_user NOT IN (SELECT a.fk_user FROM ".MAIN_DB_PREFIX."rh_absence as a where a.fk_user=".$fk_user.")
			AND v.entity IN (0,".$conf->entity.")";
		
	$ATMdb->Execute($sql);
	$TabUser=array();
	$k=0;
	while($ATMdb->Get_line()) {
				$TabUser[]=$ATMdb->Get_field('fk_user');
				$k++;
	}
	
	if($k==0){
		
	}else{
		//LISTE DES ABSENCES À VALIDER
		$sql="SELECT COUNT(a.rowid) as 'nbrAbsence', a.rowid as 'ID', a.date_cre as 'DateCre',DATE(a.date_debut) as 'date_debut', DATE(a.date_fin) as 'Date Fin', 
				  a.libelle as 'Type absence',a.fk_user as 'Utilisateur Courant',  CONCAT(u.firstname,' ',u.name) as 'Utilisateur',
				  a.libelleEtat as 'Statut demande', '' as 'Supprimer'
			FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
			WHERE a.fk_user IN(".implode(',', $TabUser).") AND a.entity IN (0,".$conf->entity.") AND u.rowid=a.fk_user";
	
	$ATMdb->Execute($sql);
	$ATMdb->Get_line();
	$nbrAbsence=$ATMdb->Get_field('nbrAbsence');
	}
	
	
	if($nbrAbsence>0) {
		/*
		 * S'il y a des demandes d'absence en attente
		 */
		$from = USER_MAIL_SENDER;
			
			$TBS=new TTemplateTBS();
			$subject = "Alerte - Validation de demandes d'absence en attente (".$nbrNdf.")";
			$message = $TBS->render(DOL_DOCUMENT_ROOT_ALT.'/absence/tpl/mail.validation.attente.tpl.php'
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
			
		    (int)$result = $mail->send(true, 'utf-8');		
	}	
}
