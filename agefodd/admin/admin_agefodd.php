<?php
/** Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
* Copyright (C) 2012-2013   Florian Henry  	<florian.henry@open-concept.pro>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 * 	\file       /agefodd/admin/admin_agefodd.php
 *	\ingroup    agefodd
 *	\brief      agefood module setup page
*/


$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once('../class/agefodd_formation_catalogue.class.php');
require_once('../class/agefodd_session_admlevel.class.php');
require_once('../class/agefodd_calendrier.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/images.lib.php");

$langs->load("admin");
$langs->load('agefodd@agefodd');

if (!$user->admin) accessforbidden();

$action = GETPOST('action','alpha');

if ($action == 'updateMaskType')
{
	$masktype=GETPOST('value');

	if ($masktype)  $res = dolibarr_set_const($db,'AGF_ADDON',$masktype,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
	else
	{
		setEventMessage($langs->trans("Error"),'errors');
	}
}

if ($action == 'updateMask')
{
	$mask=GETPOST('maskagefodd');

	$res = dolibarr_set_const($db,'AGF_UNIVERSAL_MASK',$mask,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
	else
	{
		setEventMessage($langs->trans("Error"),'errors');
	}
}

if ($action == 'updateMaskCertifType')
{
	$masktype=GETPOST('value');

	if ($masktype)  $res = dolibarr_set_const($db,'AGF_CERTIF_ADDON',$masktype,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
	else
	{
		setEventMessage($langs->trans("Error"),'errors');
	}
}

if ($action == 'updateMaskCertif')
{
	$mask=GETPOST('maskagefoddcertif');

	$res = dolibarr_set_const($db,'AGF_CERTIF_UNIVERSAL_MASK',$mask,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
	else
	{
		setEventMessage($langs->trans("Error"),'errors');
	}
}

if ($action == 'setvar')
{
	require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

	$text_color=GETPOST('AGF_TEXT_COLOR','alpha');
	if (!empty($text_color)) {
		$res = dolibarr_set_const($db, 'AGF_TEXT_COLOR', $text_color,'chaine',0,'',$conf->entity);
	} else {
		$res = dolibarr_set_const($db, 'AGF_TEXT_COLOR', '000000','chaine',0,'',$conf->entity);
	}
	if (! $res > 0) $error++;

	$head_color=GETPOST('AGF_HEAD_COLOR','alpha');
	if (!empty($head_color)) {
		$res = dolibarr_set_const($db, 'AGF_HEAD_COLOR', $head_color,'chaine',0,'',$conf->entity);
	} else {
		$res = dolibarr_set_const($db, 'AGF_HEAD_COLOR', 'CB4619','chaine',0,'',$conf->entity);
	}
	if (! $res > 0) $error++;

	$foot_color=GETPOST('AGF_FOOT_COLOR','alpha');
	if (!empty($foot_color)) {
		$res = dolibarr_set_const($db, 'AGF_FOOT_COLOR', $foot_color,'chaine',0,'',$conf->entity);
	} else {
		$res = dolibarr_set_const($db, 'AGF_FOOT_COLOR', 'BEBEBE','chaine',0,'',$conf->entity);
	}
	if (! $res > 0) $error++;

	$use_typestag=GETPOST('AGF_USE_STAGIAIRE_TYPE','int');
	$res = dolibarr_set_const($db, 'AGF_USE_STAGIAIRE_TYPE', $use_typestag,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$def_typestag=GETPOST('AGF_DEFAULT_STAGIAIRE_TYPE','int');
	if (!empty($def_typestag))
	{
		$res = dolibarr_set_const($db, 'AGF_DEFAULT_STAGIAIRE_TYPE', $def_typestag,'chaine',0,'',$conf->entity);
		if (! $res > 0) $error++;
	}

	$pref_val=GETPOST('AGF_ORGANISME_PREF','alpha');
	$res = dolibarr_set_const($db, 'AGF_ORGANISME_PREF', $pref_val,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$def_status=GETPOST('AGF_DEFAULT_SESSION_STATUS','alpha');
	$res = dolibarr_set_const($db, 'AGF_DEFAULT_SESSION_STATUS', $def_status,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$num_org=GETPOST('AGF_ORGANISME_NUM','alpha');
	$res = dolibarr_set_const($db, 'AGF_ORGANISME_NUM', $num_org,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$org_rep=GETPOST('AGF_ORGANISME_REPRESENTANT','alpha');
	$res = dolibarr_set_const($db, 'AGF_ORGANISME_REPRESENTANT', $org_rep,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	if ($_FILES["imagesup"]["tmp_name"])
	{
		if (preg_match('/([^\\/:]+)$/i',$_FILES["imagesup"]["name"],$reg))
		{
			$original_file=$reg[1];

			$isimage=image_format_supported($original_file);
			if ($isimage >= 0)
			{
				dol_syslog("Move file ".$_FILES["imagesup"]["tmp_name"]." to ".$conf->agefodd->dir_output.'/logos/'.$original_file);
				if (! is_dir($conf->agefodd->dir_output.'/images/'))
				{
					dol_mkdir($conf->agefodd->dir_output.'/images/');
				}
				$result=dol_move_uploaded_file($_FILES["imagesup"]["tmp_name"],$conf->agefodd->dir_output.'/images/'.$original_file,1,0,$_FILES['imagesup']['error']);
				if ($result > 0)
				{
					dolibarr_set_const($db, "AGF_INFO_TAMPON",$original_file,'chaine',0,'',$conf->entity);

				}
				else if (preg_match('/^ErrorFileIsInfectedWithAVirus/',$result))
				{
					$langs->load("errors");
					$tmparray=explode(':',$result);
					setEventMessage($langs->trans('ErrorFileIsInfectedWithAVirus',$tmparray[1]),'errors');
					$error++;
				}
				else
				{
					setEventMessage($langs->trans("ErrorFailedToSaveFile"),'errors');
					$error++;
				}
			}
			else
			{
				setEventMessage($langs->trans("ErrorOnlyPngJpgSupported"),'errors');
				$error++;
			}
		}
	}

	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
	else
	{
		setEventMessage($langs->trans("Error")." ".$msg,'errors');
	}
}

if ($action == 'setvarother')
{
	$usedolibarr_agenda=GETPOST('AGF_DOL_AGENDA','alpha');
	if ($usedolibarr_agenda && !$conf->global->MAIN_MODULE_AGENDA) {
		setEventMessage($langs->trans("AgfAgendaModuleNedeed"),'errors');
		$error++;
	}
	else {
		$res = dolibarr_set_const($db, 'AGF_DOL_AGENDA', $usedolibarr_agenda,'chaine',0,'',$conf->entity);
	}
	if (! $res > 0) $error++;

	$use_trainer_agenda=GETPOST('AGF_DOL_TRAINER_AGENDA','alpha');
	if ($use_trainer_agenda && !$conf->global->MAIN_MODULE_AGENDA) {
		setEventMessage($langs->trans("AgfAgendaModuleNedeed"),'errors');
		$error++;
	}
	else {
		$res = dolibarr_set_const($db, 'AGF_DOL_TRAINER_AGENDA', $use_trainer_agenda,'chaine',0,'',$conf->entity);
	}
	if (! $res > 0) $error++;

	$logo_client=GETPOST('AGF_USE_LOGO_CLIENT','alpha');
	$res = dolibarr_set_const($db, 'AGF_USE_LOGO_CLIENT', $logo_client,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$use_dol_contact=GETPOST('AGF_CONTACT_DOL_SESSION','alpha');
	$res = dolibarr_set_const($db, 'AGF_CONTACT_DOL_SESSION', $use_dol_contact,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$use_managecertif=GETPOST('AGF_MANAGE_CERTIF','int');
	$res = dolibarr_set_const($db, 'AGF_MANAGE_CERTIF', $use_managecertif,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$use_manageopca=GETPOST('AGF_MANAGE_OPCA','int');
	$res = dolibarr_set_const($db, 'AGF_MANAGE_OPCA', $use_manageopca,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$use_fac_without_order=GETPOST('AGF_USE_FAC_WITHOUT_ORDER','alpha');
	$res = dolibarr_set_const($db, 'AGF_USE_FAC_WITHOUT_ORDER', $use_fac_without_order,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesearch_training=GETPOST('AGF_TRAINING_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_TRAINING_USE_SEARCH_TO_SELECT', $usesearch_training,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesearch_trainer=GETPOST('AGF_TRAINER_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_TRAINER_USE_SEARCH_TO_SELECT', $usesearch_trainer,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesearch_trainee=GETPOST('AGF_TRAINEE_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_TRAINEE_USE_SEARCH_TO_SELECT', $usesearch_trainee,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesearch_site=GETPOST('AGF_SITE_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_SITE_USE_SEARCH_TO_SELECT', $usesearch_site,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesearch_stagstype=GETPOST('AGF_STAGTYPE_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_STAGTYPE_USE_SEARCH_TO_SELECT', $usesearch_stagstype,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesearch_contact=GETPOST('AGF_CONTACT_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_CONTACT_USE_SEARCH_TO_SELECT', $usesearch_contact,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$use_dol_company_name=GETPOST('MAIN_USE_COMPANY_NAME_OF_CONTACT','alpha');
	$res = dolibarr_set_const($db, 'MAIN_USE_COMPANY_NAME_OF_CONTACT', $use_dol_company_name,'chaine',1,'',$conf->entity);
	if (! $res > 0) $error++;

	$add_OPCA_link_contact=GETPOST('AGF_LINK_OPCA_ADRR_TO_CONTACT','alpha');
	$res = dolibarr_set_const($db, 'AGF_LINK_OPCA_ADRR_TO_CONTACT', $add_OPCA_link_contact,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$useWISIYGtraining=GETPOST('AGF_FCKEDITOR_ENABLE_TRAINING','alpha');
	$res = dolibarr_set_const($db, 'AGF_FCKEDITOR_ENABLE_TRAINING', $useWISIYGtraining,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;

	$usesessiontraineeauto=GETPOST('AGF_SESSION_TRAINEE_STATUS_AUTO','alpha');
	$res = dolibarr_set_const($db, 'AGF_SESSION_TRAINEE_STATUS_AUTO', $usesessiontraineeauto,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;


	if (! $error)
	{
		setEventMessage($langs->trans("SetupSaved"),'mesgs');
	}
	else
	{
		setEventMessage($langs->trans("Error")." ".$msg,'errors');
	}
}

if ($action == 'removeimagesup')
{
	require_once(DOL_DOCUMENT_ROOT."/core/lib/files.lib.php");

	$logofile=$conf->agefodd->dir_output.'/images/'.$conf->global->AGF_INFO_TAMPON;
	dol_delete_file($logofile);
	dolibarr_del_const($db, "AGF_INFO_TAMPON",$conf->entity);

}

if ($action == 'sessionlevel_create')
{
	$agf = new Agefodd_session_admlevel($db);

	$parent_level = GETPOST('parent_level','int');

	if (!empty($parent_level))
	{
		$agf->fk_parent_level = $parent_level;

		$agf_static = new Agefodd_session_admlevel($db);
		$result_stat = $agf_static->fetch($agf->fk_parent_level);

		if ($result_stat > 0)
		{
			if (!empty($agf_static->id))
			{
				$agf->level_rank = $agf_static->level_rank + 1;
				$agf->indice = ebi_get_adm_get_next_indice_action($agf_static->id);
			}
			else
			{	//no parent : This case may not occur but we never know
				$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
				$agf->level_rank = 0;
			}
		}
		else
		{
			setEventMessage($agf_static->error,'errors');
		}
	}
	else
	{
		//no parent
		$agf->fk_parent_level = 0;
		$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
		$agf->level_rank = 0;
	}

	$agf->intitule = GETPOST('intitule','alpha');
	$agf->delais_alerte = GETPOST('delai','int');

	if ($agf->level_rank>3)
	{
		setEventMessage($langs->trans("AgfAdminNoMoreThan3Level"),'errors');
	}
	else
	{
		$result = $agf->create($user);

		if ($result1!=1)
		{
			setEventMessage($agf->error,'errors');
		}
	}


}

if ($action == 'sessionlevel_update')
{
	$agf = new Agefodd_session_admlevel($db);

	$id = GETPOST('id','int');
	$parent_level = GETPOST('parent_level','int');

	$result = $agf->fetch($id);

	if ($result > 0)
	{

		//Up level of action
		if (GETPOST('sesslevel_up_x'))
		{
			$result2 = $agf->shift_indice($user,'less');
			if ($result1!=1)
			{
				setEventMessage($agf->error,'errors');
			}
		}

		//Down level of action
		if (GETPOST('sesslevel_down_x'))
		{
			$result1 = $agf->shift_indice($user,'more');
			if ($result1!=1)
			{
				setEventMessage($agf->error,'errors');
			}
		}

		//Update action
		if (GETPOST('sesslevel_update_x'))
		{
			$agf->intitule = GETPOST('intitule','alpha');
			$agf->delais_alerte = GETPOST('delai','int');

			if (!empty($parent_level))
			{
				if ($parent_level!=$agf->fk_parent_level)
				{
					$agf->fk_parent_level = $parent_level;

					$agf_static = new Agefodd_session_admlevel($db);
					$result_stat = $agf_static->fetch($agf->fk_parent_level);

					if ($result_stat > 0)
					{
						if (!empty($agf_static->id))
						{
							$agf->level_rank = $agf_static->level_rank + 1;
							$agf->indice = ebi_get_adm_get_next_indice_action($agf_static->id);
						}
						else
						{	//no parent : This case may not occur but we never know
							$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
							$agf->level_rank = 0;
						}
					}
					else
					{
						setEventMessage($agf_static->error,'errors');
					}
				}
			}
			else
			{
				//no parent
				$agf->fk_parent_level = 0;
				$agf->level_rank = 0;
			}

			if ($agf->level_rank>3)
			{
				setEventMessage($langs->trans("AgfAdminNoMoreThan3Level"),'errors');
			}
			else
			{
				$result1 = $agf->update($user);
				if ($result1!=1)
				{
					setEventMessage($agf->error,'errors');
				}
			}
		}

		//Delete action
		if (GETPOST('sesslevel_remove_x'))
		{

			$result = $agf->delete($user);
			if ($result!=1)
			{
				setEventMessage($agf->error,'errors');
			}
		}
	}
	else
	{
		setEventMessage('This action do not exists','errors');
	}
}

if ($action=='sessioncalendar_create'){
	$tmpl_calendar = new Agefoddcalendrier($db);
	$tmpl_calendar->day_session=GETPOST('newday','int');
	$tmpl_calendar->heured=GETPOST('periodstart','alpha');
	$tmpl_calendar->heuref=GETPOST('periodend','alpha');

	$result = $tmpl_calendar->create($user);
	if ($result!=1)
	{
		setEventMessage($tmpl_calendar->error,'errors');
	}
}



if ($action=='sessioncalendar_delete') {
	$tmpl_calendar = new Agefoddcalendrier($db);
	$tmpl_calendar->id=GETPOST('id','int');
	$result = $tmpl_calendar->delete($user);
	if ($result!=1)
	{
		setEventMessage($tmpl_calendar->error,'errors');
	}
}

/*
 *  Admin Form
*
*/

llxHeader('',$langs->trans('AgefoddSetupDesc'),'','','','',array('/agefodd/includes/jquery/plugins/colorpicker/js/colorpicker.js'), array('/agefodd/includes/jquery/plugins/colorpicker/css/colorpicker.css'));

$form=new Form($db);
$formAgefodd=new FormAgefodd($db);

dol_htmloutput_mesg($mesg);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("AgefoddSetupDesc"),$linkback,'setup');

// Configuration header
$head = agefodd_admin_prepare_head();
dol_fiche_head($head, 'settings', $langs->trans("Module103000Name"), 0,"agefodd@agefodd");

if ($conf->use_javascript_ajax){
print ' <script type="text/javascript">';
print 'window.fnDisplayOPCAAdrr=function() {$( "#OPCAAdrr" ).show();};'."\n";
print 'window.fnHideOPCAAdrr=function() {$( "#OPCAAdrr" ).hide();};'."\n";
print 'window.fnDisplayCertifAutoAdd=function() {$( "#CertifAutoAdd" ).show();};'."\n";
print 'window.fnHideCertifAutoAdd=function() {$( "#CertifAutoAdd" ).hide();};'."\n";
print ' </script>';
}

// Agefodd numbering module
print_titre($langs->trans("AgfAdminTrainingNumber"));
print '<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="100px">'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60px">'.$langs->trans("Activated").'</td>';
print '<td align="center" width="80px">'.$langs->trans("Infos").'</td>';
print "</tr>\n";

clearstatcache();

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

foreach ($dirmodels as $reldir)
{
	$dir = dol_buildpath("/agefodd/core/modules/agefodd/");

	if (is_dir($dir))
	{
		$handle = opendir($dir);
		if (is_resource($handle))
		{
			$var=true;

			while (($file = readdir($handle))!==false)
			{
				if (preg_match('/^(mod_.*)\.php$/i',$file,$reg))
				{
					$file = $reg[1];
					$classname = substr($file,4);

					require_once($dir.$file.".php");

					$module = new $file;

					// Show modules according to features level
					if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
					if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

					if ($module->isEnabled())
					{
						$var=!$var;
						print '<tr '.$bc[$var].'><td>'.$module->nom."</td><td>\n";
						print $module->info();
						print '</td>';

						// Show example of numbering module
						print '<td nowrap="nowrap">';
						$tmp=$module->getExample();
						if (preg_match('/^Error/',$tmp)) {
							$langs->load("errors"); print '<div class="error">'.$langs->trans($tmp).'</div>';
						}
						elseif ($tmp=='NotConfigured') print $langs->trans($tmp);
						else print $tmp;
						print '</td>'."\n";

						print '<td align="center">';
						if ($conf->global->AGF_ADDON == 'mod_'.$classname)
						{
							print img_picto($langs->trans("Activated"),'switch_on');
						}
						else
						{
							print '<a href="'.$_SERVER["PHP_SELF"].'?action=updateMaskType&amp;value=mod_'.$classname.'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
						}
						print '</td>';

						$agf=new Agefodd($db);
						$agf->initAsSpecimen();

						// Info
						$htmltooltip='';
						$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
						$nextval=$module->getNextValue($mysoc,$agf);
						if ("$nextval" != $langs->trans("AgfNotAvailable"))	// Keep " on nextval
						{
							$htmltooltip.=''.$langs->trans("NextValue").': ';
							if ($nextval)
							{
								$htmltooltip.=$nextval.'<br>';
							}
							else
							{
								$htmltooltip.=$langs->trans($module->error).'<br>';
							}
						}

						print '<td align="center">';
						print $form->textwithpicto('',$htmltooltip,1,0);
						print '</td>';

						print '</tr>';
					}
				}
			}
			closedir($handle);
		}
	}
}

print '</table><br>';

if (!empty($conf->global->AGF_MANAGE_CERTIF)) {

	require_once('../class/agefodd_stagiaire_certif.class.php');

	// Agefodd Certification numbering module
	print_titre($langs->trans("AgfAdminCertifNumber"));
	print '<br>';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td width="100px">'.$langs->trans("Name").'</td>';
	print '<td>'.$langs->trans("Description").'</td>';
	print '<td>'.$langs->trans("Example").'</td>';
	print '<td align="center" width="60px">'.$langs->trans("Activated").'</td>';
	print '<td align="center" width="80px">'.$langs->trans("Infos").'</td>';
	print "</tr>\n";

	clearstatcache();

	$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

	foreach ($dirmodels as $reldir)
	{
		$dir = dol_buildpath("/agefodd/core/modules/agefodd/certificate/");

		if (is_dir($dir))
		{
			$handle = opendir($dir);
			if (is_resource($handle))
			{
				$var=true;

				while (($file = readdir($handle))!==false)
				{
					if (preg_match('/^(mod_.*)\.php$/i',$file,$reg))
					{
						$file = $reg[1];
						$classname = substr($file,4);

						require_once($dir.$file.".php");

						$module = new $file;

						// Show modules according to features level
						if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
						if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

						if ($module->isEnabled())
						{
							$var=!$var;
							print '<tr '.$bc[$var].'><td>'.$module->nom."</td><td>\n";
							print $module->info();
							print '</td>';

							// Show example of numbering module
							print '<td nowrap="nowrap">';
							$tmp=$module->getExample();
							if (preg_match('/^Error/',$tmp)) {
								$langs->load("errors"); print '<div class="error">'.$langs->trans($tmp).'</div>';
							}
							elseif ($tmp=='NotConfigured') print $langs->trans($tmp);
							else print $tmp;
							print '</td>'."\n";

							print '<td align="center">';
							if ($conf->global->AGF_CERTIF_ADDON == 'mod_'.$classname)
							{
								print img_picto($langs->trans("Activated"),'switch_on');
							}
							else
							{
								print '<a href="'.$_SERVER["PHP_SELF"].'?action=updateMaskCertifType&amp;value=mod_'.$classname.'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
							}
							print '</td>';

							$agf=new Agefodd_stagiaire_certif($db);
							$agf->initAsSpecimen();

							// Info
							$htmltooltip='';
							$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
							$nextval=$module->getNextValue($mysoc,$agf);
							if ("$nextval" != $langs->trans("AgfNotAvailable"))	// Keep " on nextval
							{
								$htmltooltip.=''.$langs->trans("NextValue").': ';
								if ($nextval)
								{
									$htmltooltip.=$nextval.'<br>';
								}
								else
								{
									$htmltooltip.=$langs->trans($module->error).'<br>';
								}
							}

							print '<td align="center">';
							print $form->textwithpicto('',$htmltooltip,1,0);
							print '</td>';

							print '</tr>';
						}
					}
				}
				closedir($handle);
			}
		}
	}

	print '</table><br>';
}



// Admin var of module
print_titre($langs->trans("AgfAdmVar"));

print '<table class="noborder" width="100%">';

print '<form method="post" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data" >';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvar">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td width="400px">'.$langs->trans("Valeur").'</td>';
print '<td></td>';
print "</tr>\n";

//Prefecture d\'enregistrement
print '<tr class="pair"><td>'.$langs->trans("AgfPrefNom").'</td>';
print '<td align="left">';
print '<input type="text"   name="AGF_ORGANISME_PREF" value="'.$conf->global->AGF_ORGANISME_PREF.'" size="20" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfPrefNomHelp"),1,'help');
print '</td>';
print '</tr>';

//Numerot d\'enregistrement a la prefecture
print '<tr class="impair"><td>'.$langs->trans("AgfPrefNum").'</td>';
print '<td align="left">';
print '<input type="text"   name="AGF_ORGANISME_NUM" value="'.$conf->global->AGF_ORGANISME_NUM.'" size="20" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfPrefNumHelp"),1,'help');
print '</td>';
print '</tr>';

//Representant de la societé de formation
print '<tr class="pair"><td>'.$langs->trans("AgfRepresant").'</td>';
print '<td align="left">';
print '<input type="text" name="AGF_ORGANISME_REPRESENTANT" value="'.$conf->global->AGF_ORGANISME_REPRESENTANT.'" size="20" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfRepresantHelp"),1,'help');
print '</td>';
print '</tr>';

//PDF Base color
print '<tr class="pair"><td>'.$langs->trans("AgfPDFTextColor").'</td>';
print '<td nowrap="nowrap">';
print $formAgefodd->select_color($conf->global->AGF_TEXT_COLOR, "AGF_TEXT_COLOR");
print '</td>';
print '<td></td>';
print "</tr>";
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfPDFHeadColor").'</td>';
print '<td nowrap="nowrap">';
print $formAgefodd->select_color($conf->global->AGF_HEAD_COLOR, "AGF_HEAD_COLOR");
print '</td>';
print '<td></td>';
print "</tr>";
print '<tr  class="pair">';
print '<td>'.$langs->trans("AgfPDFFootColor").'</td>';
print '<td nowrap="nowrap">';
print $formAgefodd->select_color($conf->global->AGF_FOOT_COLOR, "AGF_FOOT_COLOR");
print '</td>';
print '<td></td>';
print "</tr>";


//Utilisation d'un type de stagaire
print '<tr class="impair"><td>'.$langs->trans("AgfUseStagType").'</td>';
print '<td align="left">';
$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
print $form->selectarray("AGF_USE_STAGIAIRE_TYPE",$arrval,$conf->global->AGF_USE_STAGIAIRE_TYPE);
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseStagTypeHelp"),1,'help');
print '</td>';
print '</tr>';

if (!empty($conf->global->AGF_USE_STAGIAIRE_TYPE))
{
	//Type de stagaire par defaut
	print '<tr class="impair"><td>'.$langs->trans("AgfUseStagTypeDefault").'</td>';
	print '<td align="left">';
	print $formAgefodd->select_type_stagiaire($conf->global->AGF_DEFAULT_STAGIAIRE_TYPE, 'AGF_DEFAULT_STAGIAIRE_TYPE');
	print '</td>';
	print '<td align="center">';
	print '</td>';
	print '</tr>';
}

// Image supplémentaire (tampon / signature)
print '<tr class="pair"><td>'.$langs->trans("AgfImageSupp").' (png,jpg)</td><td>';
print '<table width="100%" class="nocellnopadd"><tr class="nocellnopadd"><td valign="middle" class="nocellnopadd">';
print '<input type="file" class="flat" name="imagesup" size="40">';
print '</td><td valign="middle" align="right">';
if ($conf->global->AGF_INFO_TAMPON)
{
	if (file_exists($conf->agefodd->dir_output.'/images/'.$conf->global->AGF_INFO_TAMPON))
	{
		print ' &nbsp; ';
		print '<img src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=agefodd&amp;file='.urlencode('/images/'.$conf->global->AGF_INFO_TAMPON).'" alt="AGF_INFO_TAMPON" />';
		print '<a href="'.$_SERVER["PHP_SELF"].'?action=removeimagesup">'.img_delete($langs->trans("Delete")).'</a>';
	}
}
else
{
	print '<img height="30" src="'.DOL_URL_ROOT.'/theme/common/nophoto.jpg">';
}
print '</td></tr></table>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfInfoTamponHelp"),1,'help');

print '</td></tr>';

//Default session status
print '<tr class="impair"><td>'.$langs->trans("AgfDefaultSessionStatus").'</td>';
print '<td align="left">';
print $formAgefodd->select_session_status($conf->global->AGF_DEFAULT_SESSION_STATUS,"AGF_DEFAULT_SESSION_STATUS",'t.active=1');
print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';

print '<tr class="impair"><td colspan="3" align="right"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</tr>';

print '</table><br>';
print '</form>';

print '<table class="noborder" width="100%">';

if (!$conf->use_javascript_ajax){
	print '<form method="post" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data" >';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="setvarother">';
}

// Affichage du logo commanditaire
print '<tr class="pair"><td>'.$langs->trans("AgfUseCustomerLogo").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_USE_LOGO_CLIENT');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_USE_LOGO_CLIENT",$arrval,$conf->global->AGF_USE_LOGO_CLIENT);
}
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseCustomerLogoHelp"),1,'help');
print '</td>';
print '</tr>';

// Forcer la liaison d'une facture sans nécessiter de bon de commande
print '<tr class="impair"><td>'.$langs->trans("AgfUseFacWhithoutOrder").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_USE_FAC_WITHOUT_ORDER');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_USE_FAC_WITHOUT_ORDER",$arrval,$conf->global->AGF_USE_FAC_WITHOUT_ORDER);
}
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseFacWhithoutOrderHelp"),1,'help');
print '</td>';
print '</tr>';

//Utilisation du contact agefodd ou dolibarr a la creation de la session
print '<tr class="pair"><td>'.$langs->trans("AgfUseSessionDolContact").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_CONTACT_DOL_SESSION');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_CONTACT_DOL_SESSION",$arrval,$conf->global->AGF_CONTACT_DOL_SESSION);
}
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseSessionDolContactHelp"),1,'help');
print '</td>';
print '</tr>';


// utilisation formulaire Ajax sur choix training
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectTraining").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	if ($conf->use_javascript_ajax){
		print ajax_constantonoff('AGF_TRAINING_USE_SEARCH_TO_SELECT');
	}else {
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_TRAINING_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_TRAINING_USE_SEARCH_TO_SELECT);
	}
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix trainer
print '<tr class="pair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectTrainer").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	if ($conf->use_javascript_ajax){
		print ajax_constantonoff('AGF_TRAINER_USE_SEARCH_TO_SELECT');
	}else {
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_TRAINER_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_TRAINER_USE_SEARCH_TO_SELECT);
	}
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix trainee
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectTrainee").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td  align="left">';
	if ($conf->use_javascript_ajax){
		print ajax_constantonoff('AGF_TRAINEE_USE_SEARCH_TO_SELECT');
	}else {
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_TRAINEE_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_TRAINEE_USE_SEARCH_TO_SELECT);
	}
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix site
print '<tr class="pair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectSite").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	if ($conf->use_javascript_ajax){
		print ajax_constantonoff('AGF_SITE_USE_SEARCH_TO_SELECT');
	}else {
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_SITE_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_SITE_USE_SEARCH_TO_SELECT);
	}
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

if ($conf->global->AGF_USE_STAGIAIRE_TYPE)
{
	// utilisation formulaire Ajax sur choix type de stagiaire
	print '<tr class="impair">';
	print '<td>'.$langs->trans("AgfUseSearchToSelectStagType").'</td>';
	if (! $conf->use_javascript_ajax)
	{
		print '<td nowrap="nowrap" align="right" colspan="2">';
		print $langs->trans("NotAvailableWhenAjaxDisabled");
		print '</td>';
	}
	else
	{
		print '<td align="left">';
		if ($conf->use_javascript_ajax){
			print ajax_constantonoff('AGF_STAGTYPE_USE_SEARCH_TO_SELECT');
		}else {
			$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
			print $form->selectarray("AGF_STAGTYPE_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_STAGTYPE_USE_SEARCH_TO_SELECT);
		}
		print '</td>';
	}
	print '<td>&nbsp;</td>';
	print '</tr>';
}

//Lors de la creation de session -> creation d'un evenement dans l'agenda Dolibarr
print '<tr class="impair"><td>'.$langs->trans("AgfAgendaModuleUse").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_DOL_AGENDA');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_DOL_AGENDA",$arrval,$conf->global->AGF_DOL_AGENDA);
}
print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';

// Active la gestion du temps formateur
print '<tr class="pair"><td>'.$langs->trans("AgfAgendaUseForTrainer").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_DOL_TRAINER_AGENDA');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_DOL_TRAINER_AGENDA",$arrval,$conf->global->AGF_DOL_TRAINER_AGENDA);
}
print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';


// use ajax combo box for contact
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectContact").'</td>';
if (! $conf->use_javascript_ajax || ! $conf->global->CONTACT_USE_SEARCH_TO_SELECT)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabledOrContactComboBox");
	print '</td>';
	print '<td align="center">';
	print '</td>';
}
else
{
	print '<td align="left">';
	if ($conf->use_javascript_ajax){
		print ajax_constantonoff('AGF_CONTACT_USE_SEARCH_TO_SELECT');
	}else {
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_CONTACT_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_CONTACT_USE_SEARCH_TO_SELECT);
	}
	print '</td>';
	print '<td align="center">';
	print '</td>';
}
print '</tr>';

// Update global variable MAIN_USE_COMPANY_NAME_OF_CONTACT
print '<tr class="pair"><td>'.$langs->trans("AgfUseMainNameOfContact").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('MAIN_USE_COMPANY_NAME_OF_CONTACT');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("MAIN_USE_COMPANY_NAME_OF_CONTACT",$arrval,$conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT);
}
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseMainNameOfContactHelp"),1,'help');
print '</td>';
print '</tr>';

// Update global variable AGF_MANAGE_CERTIF
print '<tr class="impair"><td>'.$langs->trans("AgfManageCertification").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	$input_array=array('alert'=>
	array(
	'set'=>array('content'=>$langs->trans('AgfConfirmChangeState'),'title'=>$langs->trans('AgfConfirmChangeState'),'method'=>'fnDisplayCertifAutoAdd','yesButton'=>$langs->trans('Yes'),'noButton'=>$langs->trans('No')),
	'del'=>array('content'=>$langs->trans('AgfConfirmChangeState'),'title'=>$langs->trans('AgfConfirmChangeState'),'method'=>'fnHideCertifAutoAdd','yesButton'=>$langs->trans('Yes'),'noButton'=>$langs->trans('No'))));

	print ajax_constantonoff('AGF_MANAGE_CERTIF',$input_array);
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_MANAGE_CERTIF",$arrval,$conf->global->AGF_MANAGE_CERTIF);
}

print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';


if ($conf->use_javascript_ajax){


	// Update global variable AGF_DEFAULT_CREATE_CERTIF
	print '<tr id ="CertifAutoAdd" class="impair"><td>'.$langs->trans("AgfCertifAutoAdd").'</td>';
	print '<td align="left">';
	print ajax_constantonoff('AGF_DEFAULT_CREATE_CERTIF');
	print '</td>';
	print '<td align="center">';
	print $form->textwithpicto('',$langs->trans("AgfCertifAutoAddHelp"),1,'help');
	print '</td>';
	print '</tr>';

	if (!empty($conf->global->AGF_MANAGE_CERTIF))
	{
		print ' <script type="text/javascript">';
		print '$( "#CertifAutoAdd" ).show()';
		print ' </script>';
	} else {
		print ' <script type="text/javascript">';
		print '$( "#CertifAutoAdd" ).hide()';
		print ' </script>';
	}

}
else
{
	if (!empty($conf->global->AGF_MANAGE_CERTIF))
	{
		// Update global variable AGF_DEFAULT_CREATE_CERTIF
		print '<tr id ="CertifAutoAdd" class="impair"><td>'.$langs->trans("AgfCertifAutoAdd").'</td>';
		print '<td align="left">';
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_DEFAULT_CREATE_CERTIF",$arrval,$conf->global->AGF_DEFAULT_CREATE_CERTIF);
		print '</td>';
		print '<td align="center">';
		print $form->textwithpicto('',$langs->trans("AgfCertifAutoAddHelp"),1,'help');
		print '</td>';
		print '</tr>';
	}
}


// Update global variable AGF_MANAGE_OPCA
print '<tr class="pair"><td>'.$langs->trans("AgfManageOPCA").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	$input_array=array('alert'=>
	array(
		'set'=>array('content'=>$langs->trans('AgfConfirmChangeState'),'title'=>$langs->trans('AgfConfirmChangeState'),'method'=>'fnDisplayOPCAAdrr','yesButton'=>$langs->trans('Yes'),'noButton'=>$langs->trans('No')),
		'del'=>array('content'=>$langs->trans('AgfConfirmChangeState'),'title'=>$langs->trans('AgfConfirmChangeState'),'method'=>'fnHideOPCAAdrr','yesButton'=>$langs->trans('Yes'),'noButton'=>$langs->trans('No'))));
	
	print ajax_constantonoff('AGF_MANAGE_OPCA',$input_array);
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_MANAGE_OPCA",$arrval,$conf->global->AGF_MANAGE_OPCA);
}
print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';

if ($conf->use_javascript_ajax){
	
	
	// Update global variable MAIN_USE_COMPANY_NAME_OF_CONTACT
	print '<tr id ="OPCAAdrr" class="impair"><td>'.$langs->trans("AgfLinkOPCAAddrToContact").'</td>';
	print '<td align="left">';
	print ajax_constantonoff('AGF_LINK_OPCA_ADRR_TO_CONTACT');
	print '</td>';
	print '<td align="center">';
	print $form->textwithpicto('',$langs->trans("AgfLinkOPCAAddrToContactHelp"),1,'help');
	print '</td>';
	print '</tr>';
	
	if (!empty($conf->global->AGF_MANAGE_OPCA))
	{
		print ' <script type="text/javascript">';
		print '$( "#OPCAAdrr" ).show()';
		print ' </script>';
	} else {
		print ' <script type="text/javascript">';
		print '$( "#OPCAAdrr" ).hide()';
		print ' </script>';
	}

}
else 
{
	if (!empty($conf->global->AGF_MANAGE_OPCA))
	{
		// Update global variable AGF_LINK_OPCA_ADRR_TO_CONTACT
		print '<tr id ="OPCAAdrr" class="impair"><td>'.$langs->trans("AgfLinkOPCAAddrToContact").'</td>';
		print '<td align="left">';
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_LINK_OPCA_ADRR_TO_CONTACT",$arrval,$conf->global->AGF_LINK_OPCA_ADRR_TO_CONTACT);
		print '</td>';
		print '<td align="center">';
		print $form->textwithpicto('',$langs->trans("AgfLinkOPCAAddrToContactHelp"),1,'help');
		print '</td>';
		print '</tr>';
	}
}


// Update global variable AGF_FCKEDITOR_ENABLE_TRAINING
print '<tr class="impair"><td>'.$langs->trans("AgfUseWISIWYGTraining").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_FCKEDITOR_ENABLE_TRAINING');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_FCKEDITOR_ENABLE_TRAINING",$arrval,$conf->global->AGF_FCKEDITOR_ENABLE_TRAINING);
}
print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';

// Update global variable AGF_SESSION_TRAINEE_STATUS_AUTO
print '<tr class="pair"><td>'.$langs->trans("AgfUseSubscriptionStatusAuto").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_SESSION_TRAINEE_STATUS_AUTO');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_SESSION_TRAINEE_STATUS_AUTO",$arrval,$conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO);
}
print '</td>';
print '<td align="center">';
$form->textwithpicto('',$langs->trans("AgfUseSubscriptionStatusAutoHelp"),1,'help');
print '</td>';
print '</tr>';

// Update global variable AGF_ADD_TRAINEE_NAME_INTO_DOCPROPODR
print '<tr class="impair"><td>'.$langs->trans("AgfAddTraineeNameIntoDoc").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_ADD_TRAINEE_NAME_INTO_DOCPROPODR');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_ADD_TRAINEE_NAME_INTO_DOCPROPODR",$arrval,$conf->global->AGF_ADD_TRAINEE_NAME_INTO_DOCPROPODR);
}
print '</td>';
print '<td align="center">';
$form->textwithpicto('',$langs->trans("AgfAddTraineeNameIntoDocHelp"),1,'help');
print '</td>';
print '</tr>';

// Update global variable AGF_MANAGE_CURSUS
print '<tr class="pair"><td>'.$langs->trans("AgfManageCursus").'</td>';
print '<td align="left">';
if ($conf->use_javascript_ajax){
	print ajax_constantonoff('AGF_MANAGE_CURSUS');
}else {
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_MANAGE_CURSUS",$arrval,$conf->global->AGF_MANAGE_CURSUS);
}
print '</td>';
print '<td align="center">';
$form->textwithpicto('',$langs->trans("AgfManageCursusHelp"),1,'help');
print '</td>';
print '</tr>';



if (!$conf->use_javascript_ajax){
	print '<tr class="impair"><td colspan="3" align="right"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
	print '</tr>';
}

print '</table><br>';
print '</form>';

//Admin Training level administation

$admlevel = new Agefodd_session_admlevel($db);
$result0 = $admlevel->fetch_all();


print_titre($langs->trans("AgfAdminSessionLevel"));

if ($result0>0)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td width="10px"></td>';
	print '<td>'.$langs->trans("AgfIntitule").'</td>';
	print '<td>'.$langs->trans("AgfParentLevel").'</td>';
	print '<td>'.$langs->trans("AgfDelaiSessionLevel").'</td>';
	print '<td></td>';
	print "</tr>\n";

	$var=true;
	foreach ($admlevel->lines as $line)
	{
		$var=!$var;
		$toplevel='';
		print '<form name="SessionLevel_update_'.$line->rowid.'" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
		print '<input type="hidden" name="id" value="'.$line->rowid.'">'."\n";
		print '<input type="hidden" name="action" value="sessionlevel_update">'."\n";
		print '<tr '.$bc[$var].'>';

		print '<td>';
		if ($line->indice!=ebi_get_adm_indice_per_rank($line->level_rank,$line->fk_parent_level,'MIN'))
		{
			print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/1uparrow.png" border="0" name="sesslevel_up" alt="'.$langs->trans("Save").'">';
		}
		if ($line->indice!=ebi_get_adm_indice_per_rank($line->level_rank,$line->fk_parent_level,'MAX'))
		{
			print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/1downarrow.png" border="0" name="sesslevel_down" alt="'.$langs->trans("Save").'">';
		}
		print '</td>';

		print '<td>'.str_repeat('&nbsp;&nbsp;&nbsp;',$line->level_rank).'<input type="text" name="intitule" value="'.$line->intitule.'" size="30"/></td>';
		print '<td>'.$formAgefodd->select_action_session_adm($line->fk_parent_level,'parent_level',$line->rowid).'</td>';
		print '<td><input type="text" name="delai" value="'.$line->alerte.'"/></td>';
		print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" name="sesslevel_update" alt="'.$langs->trans("Save").'">';
		print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="sesslevel_remove" alt="'.$langs->trans("Delete").'"></td>';
		print '</tr>';
		print '</form>';
	}


}
print '<form name="SessionLevel_create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
print '<input type="hidden" name="action" value="sessionlevel_create">'."\n";
print '<tr>';
print '<td></td>';
print '<td><input type="text" name="intitule" value="" size="30"/></td>';
print '<td>'.$formAgefodd->select_action_session_adm('','parent_level').'</td>';
print '<td><input type="text" name="delai" value=""/></td>';
print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit_add.png" border="0" name="sesslevel_update" alt="'.$langs->trans("Save").'"></td>';
print '</tr>';
print '</form>';
print '</table><br>';

print_titre($langs->trans("AgfAdminCalendarTemplate"));


print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("AgfAdminCalendarTemplate").'</td>';
print "</tr>\n";
print '<tr><td>';
print '<table class="noborder" width="100%">';
print '<tr>';
print '<td>'.$langs->trans("AgfDaySession").'</td>';
print '<td>'.$langs->trans("AgfPeriodTimeB").'</td>';
print '<td>'.$langs->trans("AgfPeriodTimeE").'</td>';
print '<td></td>';
print '</tr>';

$tmpl_calendar = new Agefoddcalendrier($db);
$tmpl_calendar->fetch_all();
foreach($tmpl_calendar->lines as $line) {

	print '<form name="SessionCalendar_'.$line->id.'" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="sessioncalendar_delete">'."\n";
	print '<input type="hidden" name="id" value="'.$line->id.'">'."\n";
	print '<tr>';
	print '<td>'.$line->day_session.'</td>';
	print '<td>'.$line->heured.'</td>';
	print '<td>'.$line->heuref.'</td>';
	print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="sessioncalendar_delete" alt="'.$langs->trans("Save").'"></td>';
	print '</tr>';
	print '</form>';
}

print '<form name="SessionCalendar_new" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
print '<input type="hidden" name="action" value="sessioncalendar_create">'."\n";
print '<tr>';
print '<td><select id="newday" class="flat" name="newday">';
for ($i = 1; $i <= 10; $i++) {
	print '<option value="'.$i.'">'.$i.'</option>';
}
print '</select></td>';
print '<td>'.$formAgefodd->select_time('','periodstart').'</td>';
print '<td>'.$formAgefodd->select_time('','periodend').'</td>';
print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit_add.png" border="0" name="sessioncalendar_create" alt="'.$langs->trans("Save").'"></td>';
print '</tr>';
print '</table>';
print '</td></tr>';
print '</table>';
print '</form>';


llxFooter();
$db->close();
