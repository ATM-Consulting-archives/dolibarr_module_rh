<?php
require('config.php');
require(DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php');
require('class/groupeformulaire.class.php');
require('lib/formulaire.lib.php');
require(DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php');
//require(DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php');

llxHeader();

global $db;
$ATMdb = new TPDOdb;
$form = new Form($db);
$errdatefin = FALSE;
$errdatedeb = FALSE;

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'add'){
	//traitement de l'ajou
	if(isset($_POST['datedeb']) && !empty($_POST['datedeb']) && array_sum(sscanf($_POST['datedeb'], "%d/%d/%d")) != 0)
		$datedeb = $_POST['datedeb'];
	else
		$errdatedeb = TRUE;
	
	if(isset($_POST['datefin']) && !empty($_POST['datefin']) && array_sum(sscanf($_POST['datefin'], "%d/%d/%d")) != 0)
		$datefin = $_POST['datefin'];
	else
		$errdatefin = TRUE;
	
	if($errdatedeb == TRUE || $errdatefin == TRUE) 
		echo dol_htmloutput_mesg('Les dates entrées ne sont pas d\'un type valide.', '', 'error', 0);
	else{
		$TGroupeForm = new TGroupeFormulaire();
		
		$datedeb = explode("/",$datedeb);
		$datedeb =  $datedeb[2]."-".$datedeb[1]."-".$datedeb[0];
		$datefin = explode("/",$datefin);
		$datefin =  $datefin[2]."-".$datefin[1]."-".$datefin[0]." 23:59:59";
		
		$TGroupeForm->fk_usergroup = $_POST['groupe'];
		$TGroupeForm->fk_survey = $_POST['survey'];
		$TGroupeForm->date_deb = strtotime($datedeb);
		$TGroupeForm->date_fin = strtotime($datefin);
		
		$TGroupeForm->save($ATMdb);
		
		send_mail_formulaire($TGroupeForm);
		
		echo dol_htmloutput_mesg('Les droits ont été enregistrés.', '', 'ok', 0);
	}
}
elseif(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']))
{
	$TGroupeForm = new TGroupeFormulaire();
	$TGroupeForm->load($ATMdb,$_GET['id']);
	$TGroupeForm->delete($ATMdb);
}

print dol_get_fiche_head(array()  , '', 'Administration des enquêtes');

$title = 'Gestion des droits';
print_fiche_titre($title, '', 'form32.png@formulaire');

?>
<form action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" id="action" value="add" />
	<table>
		<tr>
			<td width="30%">Groupe : </td>
			<td>
				<select name="groupe" id="groupe">
					<?php
					$ATMdb = new TPDOdb;
					$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."usergroup";
					$ATMdb->Execute($sql);
					
					while($ATMdb->Get_line()){
						$group = new UserGroup($db);
						$group->fetch($ATMdb->Get_field('rowid'));
						?>
						<option value="<?=$group->id;?>"><?=$group->name;?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Formulaire : </td>
			<td>
				<select name="survey" id="survey">
					<?php
					$ATMdb = new TPDOdb;
					$sql = "SELECT s.sid AS id, sl.surveyls_title AS title
							FROM ".LIME_DB.".lime_surveys AS s
							LEFT JOIN ".LIME_DB.".lime_surveys_languagesettings AS sl ON sl.surveyls_survey_id = s.sid";
					$ATMdb->Execute($sql);
					
					while($ATMdb->Get_line()){
						?>
						<option value="<?=$ATMdb->Get_field('id');?>"><?=$ATMdb->Get_field('title')?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Du : </td>
			<td><?php $form->select_date('','datedeb','','','',"add",1,1); ?></td>
		</tr>
		<tr>
			<td>Au : </td>
			<td><?php $form->select_date('','datefin','','','',"add",1,1); ?></td>
		</tr>
		<tr height="20px;">
		</tr>
		<tr>
			<td colspan="4" align="center">
				<input type="submit" class="button" value="Ajouter un droit d'accès" />
			</td>
		</tr>
	</table>
</form>
<br>
<?php
$Tlistedroit = new TGroupeFormulaire;

$r = new TSSRenderControler($Tlistedroit);
$sql = "SELECT fg.rowid AS 'ID', sl.surveyls_title AS title, gr.nom AS groupe, fg.date_deb AS datedeb, fg.date_fin AS datefin, '' as 'Supprimer'
		FROM ".MAIN_DB_PREFIX."rh_formulaire_groupe AS fg
			LEFT JOIN ".LIME_DB.".lime_surveys_languagesettings AS sl ON sl.surveyls_survey_id = fg.fk_survey
			LEFT JOIN ".MAIN_DB_PREFIX."usergroup AS gr ON gr.rowid = fg.fk_usergroup";

$TOrder = array('datedeb'=>'ASC');
if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
			
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');

$r->liste($ATMdb, $sql, array(
	'limit'=>array(
		'page'=>$page
		,'nbLine'=>'30'
	)
	,'type'=>array(
		'datedeb' => 'date',
		'datefin' => 'date'
	)
	,'liste'=>array(
		'titre'=>'Liste des droits'
		,'image'=>img_picto('','form32.png@formulaire', '', 0)
		,'picto_precedent'=>img_picto('','back.png', '', 0)
		,'picto_suivant'=>img_picto('','next.png', '', 0)
		,'noheader'=> (int)isset($_REQUEST['socid'])
		,'messageNothing'=>"Aucun droit configuré."
		,'order_down'=>img_picto('','1downarrow.png', '', 0)
		,'order_up'=>img_picto('','1uparrow.png', '', 0)
		,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
	)
	,'title'=>array(
		'datedeb'=>'Date début'
		,'datefin' => 'Date fin'
		,'title' => 'Enquête'
		,'groupe' => 'Groupe'
	)
	,'search'=>array(
	)
	,'link'=>array(
			'Supprimer'=>"<a onclick=\"if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=@ID@&action=delete'};\" style='cursor:pointer;'><img src=\"./img/delete.png\"></a>"
		)
	,'orderBy'=>$TOrder
	
));
$form->end();
?>
<br>
<?

	print_fiche_titre("Créer une nouvelle enquête", '', 'form32.png@formulaire');
	
?><a href="./limesurvey/admin/" class="butAction" target="_blank">Gestionnaire enquêtes</a>
<div style="clear:both;"></div>