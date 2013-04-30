<?php

require('config.php');
require('class/groupeformulaire.class.php');
require(DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php');

llxHeader();

global $user;
$ATMdb = new Tdb;
$TSurvey = array();

$sql = "SELECT fg.fk_usergroup AS groupe, fg.fk_survey AS survey, sl.surveyls_title AS title, fg.date_deb AS date_deb, fg.date_fin AS date_fin
		FROM ".MAIN_DB_PREFIX."rh_formulaire_groupe AS fg
			LEFT JOIN lime_surveys_languagesettings AS sl ON sl.surveyls_survey_id = fg.fk_survey
		WHERE fg.date_deb <= NOW() AND fg.date_fin >= NOW()";
$ATMdb->Execute($sql);

while($ATMdb->Get_line()){
	$GroupUser = new UserGroup($db);
	$GroupUser->fetch($ATMdb->Get_field('groupe'));
	$TGroupUser = $GroupUser->listUsersForGroup();
	
	$Tligne = array();
	if(array_key_exists($user->id,$TGroupUser)){
		$Tligne["id_survey"] = $ATMdb->Get_field('survey');
		$Tligne["title_survey"] = $ATMdb->Get_field('title');
		$Tligne["date_debut"] = $ATMdb->Get_field('date_deb');
		$Tligne["date_fin"] = $ATMdb->Get_field('date_fin');
		
		$ATMdb2 = new Tdb;
		$sql = "SELECT tid, token
				FROM lime_tokens_".$ATMdb->Get_field('survey')." 
				WHERE firstname = '".$user->firstname."' AND lastname = '".$user->lastname."' AND email = '".$user->email."'";

		$ATMdb2->Execute($sql);
		if($ATMdb2->Get_line()){
			$Tligne["statut_survey"] = '<span style="color:green">Formulaire répondu ou en cours de réponse</span>';
			$Tligne['un_lien'] = './limesurvey/index.php/'.$Tligne["id_survey"].'?lang=fr&amp;token='.$ATMdb2->Get_field('token');
		}
		else{
			$Tligne['statut_survey'] = '<span style="color:red">En attente de réponse</span>';
			$Tligne['un_lien'] = './limesurvey/index.php/'.$Tligne["id_survey"].'?lang=fr&amp;register_firstname='.$user->firstname.'&amp;register_lastname='.$user->lastname.'&amp;register_email='.htmlentities($user->email);
		}
	}
		
	$TSurvey[] = $Tligne;
}
?>
<h1>Questionnaire</h1>
<?php
$r = new TListviewTBS('liste_ventilation_caisse', ROOT.'custom/formulaire/tpl/html.list.tbs.php');
	
print $r->renderArray($ATMdb, $TSurvey, array(
	'limit'=>array('nbLine'=>1000)
	,'title'=>array(
		'title_survey'=>'Nom Formulaire'
		,'date_debut'=>'Date début accessibilité'
		,'date_fin'=>'Date fin accessibilité'
		,'statut_survey'=>'Etat du formulaire'
		,'link' => 'lien'
	)
	,'type'=>array('date_debut'=>'date','date_fin'=>'date')
	,'hide'=>array(
		'id_survey'
		//,'link'
	)
	,'link'=>array(
		'title_survey'=> '<a target="_blank" href="@un_lien@">@val@</a>'
	)
));
?>
<br>