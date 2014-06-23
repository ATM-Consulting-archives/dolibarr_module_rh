<?php

require('config.php');
require('class/groupeformulaire.class.php');
require(DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php');

llxHeader();

global $user;
$ATMdb = new TPDOdb;
$TSurvey = array();

$sql = "SELECT fg.fk_usergroup AS groupe, fg.fk_survey AS survey, sl.surveyls_title AS title, fg.date_deb AS date_deb, fg.date_fin AS date_fin
		FROM ".MAIN_DB_PREFIX."rh_formulaire_groupe AS fg
			LEFT JOIN ".LIME_DB.".lime_surveys_languagesettings AS sl ON sl.surveyls_survey_id = fg.fk_survey
		WHERE fg.date_deb <= NOW() AND fg.date_fin >= NOW()";
		echo $sql;
$ATMdb->Execute($sql);

while($ATMdb->Get_line()){
	$GroupUser = new UserGroup($db);
	$GroupUser->fetch($ATMdb->Get_field('groupe'));
	$TGroupUser = $GroupUser->listUsersForGroup();
	
	$Tligne = array();
	if(array_key_exists($user->id,$TGroupUser)){
			
		$ATMdb2 = new TPDOdb;
		$sql = "SELECT tid, token
				FROM ".LIME_DB.".lime_tokens_".$ATMdb->Get_field('survey')." 
				WHERE firstname = '".$user->firstname."' AND lastname = '".$user->lastname."' AND email = '".$user->email."'";

		$ATMdb2->Execute($sql);
		if($ATMdb2->Get_line()){
			$statut = '<span style="color:green">Formulaire répondu ou en cours de réponse</span>';
			$Tligne['un_lien'] = './limesurvey/index.php/'.$ATMdb->Get_field('survey').'?lang=fr&amp;token='.$ATMdb2->Get_field('token');
		}
		else{
			$statut = '<span style="color:red">En attente de réponse</span>';
			$Tligne['un_lien'] = './limesurvey/index.php/'.$ATMdb->Get_field('survey').'?lang=fr&amp;register_firstname='.$user->firstname.'&amp;register_lastname='.$user->lastname.'&amp;register_email='.htmlentities($user->email);
		}
		
		$Tligne["id_survey"] = $ATMdb->Get_field('survey');
		$Tligne["title_survey"] = $ATMdb->Get_field('title');
		$Tligne["date_debut"] = $ATMdb->Get_field('date_deb');
		$Tligne["date_fin"] = $ATMdb->Get_field('date_fin');
		$Tligne['statut_survey'] = $statut;
	}
		
	$TSurvey[] = $Tligne;
}

$title = 'Questionnaire';
print_fiche_titre($title, '', 'form32.png@formulaire');
?>
<?php
$r = new TListviewTBS('liste_ventilation_caisse', './tpl/html.list.tbs.php');
	
print $r->renderArray($ATMdb, $TSurvey, array(
	'limit'=>array('nbLine'=>1000)
	,'title'=>array(
		'title_survey'=>'Nom Formulaire'
		,'un_lien' => 'lien'
		,'date_debut'=>'Date début accessibilité'
		,'date_fin'=>'Date fin accessibilité'
		,'statut_survey'=>'Etat du formulaire'
	)
	,'type'=>array('date_debut'=>'date','date_fin'=>'date')
	,'hide'=>array(
		'id_survey'
		,'un_lien'
	)
	,'link'=>array(
		'title_survey'=> '<a target="_blank" href="@un_lien@">@val@</a>'
	)
));
?>
<br>