<?php

require('config.php');
require('class/groupeformulaire.class.php');
require(DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php');

llxHeader();

global $user;
$ATMdb = new Tdb;
$TSurvey = array();

$sql = "SELECT fg.fk_usergroup AS groupe, fg.fk_survey AS survey, sl.surveyls_title AS title
		FROM ".MAIN_DB_PREFIX."rh_formulaire_groupe AS fg
			LEFT JOIN lime_surveys_languagesettings AS sl ON sl.surveyls_survey_id = fg.fk_survey
		WHERE fg.date_deb <= NOW() AND fg.date_fin >= NOW()";
$ATMdb->Execute($sql);

while($ATMdb->Get_line()){
	$GroupUser = new UserGroup($db);
	$GroupUser->fetch($ATMdb->Get_field('groupe'));
	$TGroupUser = $GroupUser->listUsersForGroup();
	
	if(array_key_exists($user->id,$TGroupUser)){
		$TSurvey[$ATMdb->Get_field('survey')] = $ATMdb->Get_field('title');
	}
}
?>
<h1>Questionnaire</h1>

<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" id="action" value="view" />
	<table>
		<tr height="50px;">
			<td>S&eacute;lectionnez un formulaire : </td>
			<td>
				<select name="survey" id="survey">
					<?php
					$ATMdb = new Tdb;
					$sql = "SELECT s.sid AS id, sl.surveyls_title AS title
							FROM lime_surveys AS s
							LEFT JOIN lime_surveys_languagesettings AS sl ON sl.surveyls_survey_id = s.sid";
					$ATMdb->Execute($sql);
					
					while($ATMdb->Get_line()){
						?>
						<option value="<?=$ATMdb->Get_field('id');?>"><?=$ATMdb->Get_field('title')?></option>
						<?php
					}
					?>
				</select>
			</td>
			<td><input type="submit" value="Valider" /></td>
		</tr>
	</table>
</form>
<!-- <iframe frameborder="0" id="limeSurveyFrame" name="limeSurveyFrame" src="<?=$url ?>" width="100%" height="800" onload="this.height=this.contentWindow.document.body.scrollHeight+50;" >

</iframe> -->

<?php
if(isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'view'){
	$url = './limesurvey/index.php/'.$_POST['survey']."?lang=fr&amp;register_firstname=".$user->firstname."&amp;register_lastname=".$user->lastname."&amp;register_email=".htmlentities($user->email,ENT_QUOTES,"UTF-8");
	?>
	<iframe frameborder="0" id="limeSurveyFrame" name="limeSurveyFrame" src="<?=$url ?>" width="100%" height="800" onload="this.height=this.contentWindow.document.body.scrollHeight+50;" >

	</iframe>
	<?php
}
?>