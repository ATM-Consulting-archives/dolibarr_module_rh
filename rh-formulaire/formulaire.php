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
		$sql = "SELECT tid 
				FROM lime_tokens_".$ATMdb->Get_field('survey')." 
				WHERE firstname = ".$user->firstname." AND lastname = ".$user->lastname." AND email = ".$user->email;
		$ATMdb2->Execute($sql);
		if($ATMdb2->Get_line())
			$Tligne["statut_survey"] = "Formulaire répondu ou en cours de réponse";
		else
			$Tligne['statut_survey'] = "En attente de réponse";
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
	)
	,'type'=>array('date_debut'=>'date','date_fin'=>'date')
	,'hide'=>array(
		'id_survey'
	)
	,'link'=>array(
		'title_formulaire'=>'<a href="./limesurvey/index.php/@id_survey@?lang=fr&amp;register_firstname='.$user->firstname.'&amp;register_lastname='.$user->lastname.'&amp;register_email='.htmlentities($user->email,ENT_QUOTES,"UTF-8").'>@val@</a>'
	)
));
?>
<br>
<?php
if(isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'view'){
	$url = './limesurvey/index.php/'.$_POST['survey']."?lang=fr&amp;register_firstname=".$user->firstname."&amp;register_lastname=".$user->lastname."&amp;register_email=".htmlentities($user->email,ENT_QUOTES,"UTF-8");
	?>
	<iframe frameborder="0" id="limeSurveyFrame" name="limeSurveyFrame" src="<?=$url ?>" width="100%" height="800" onload="this.height=this.contentWindow.document.body.scrollHeight+50;" >

	</iframe>
	<?php
}
?>