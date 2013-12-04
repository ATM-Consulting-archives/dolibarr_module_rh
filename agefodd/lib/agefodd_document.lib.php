<?php
/** 
 * Copyright (C) 2012 	   Florian Henry        <florian.henry@open-concept.pro>
 * Copyright (C) 2012		JF FERRY	<jfefe@aternatik.fr>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       agefodd/lib/agefodd_document.lib.php
 *  \ingroup    agefodd
 *  \brief      Some display function
 */

function show_conv($file, $socid,$nom_courrier)
{
	global $langs, $conf, $db, $id, $form;

	$model = $file;
	$file = $file.'_'.$id.'_'.$socid.'.pdf';

	$agf = new Agefodd_convention($db);
	$result = $agf->fetch($id, $socid);

	$continue=true;
	// Get proposal/order/invoice informations
	$agf_comid= new Agefodd_facture($db);
	$result = $agf_comid->fetch($id,$socid);

	if (!empty($conf->global->MAIN_MODULE_COMMANDE)) {
	if (empty($agf_comid->propalid)) {
		if (empty($agf_comid->comid) && empty($agf_comid->facid) && empty($conf->global->AGF_USE_FAC_WITHOUT_ORDER)) {
			$mess = $form->textwithpicto('',$langs->trans("AgfFactureFacNoBonHelp"),1,'help');
			$continue=false;
		} elseif (empty($agf_comid->comid) && empty($agf_comid->facid) && $conf->global->AGF_USE_FAC_WITHOUT_ORDER) {
			$mess = $form->textwithpicto('',$langs->trans("AgfFactureFacNoBonHelpOpt"),1,'help');
			$continue=false;
		}  elseif (empty($agf_comid->comid) && !empty($agf_comid->facid) && empty($conf->global->AGF_USE_FAC_WITHOUT_ORDER)) {
			$mess = $form->textwithpicto('',$langs->trans("AgfFactureFacNoBonHelp"),1,'help');
			$continue=false;
		}
	}
	} else {
		if (empty($agf_comid->propalid)) {
			$mess = $form->textwithpicto('',$langs->trans("AgfFacturePropalHelp"),1,'help');
			$continue=false;
		}
	}
	

	// If convention contract have already been set (database records exists)
	if ($agf->id && $continue)
	{
		if (is_file($conf->agefodd->dir_output.'/'.$file))
		{
			// Display
			$legende = $langs->trans("AgfDocOpen");
			$mess = '<a href="'.DOL_URL_ROOT.'/document.php?modulepart=agefodd&file='.$file.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';

			// Regenerer
			$legende = $langs->trans("AgfDocRefresh");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=refresh&model='.$model.'&cour='.$nom_courrier.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/refresh.png" border="0" align="absmiddle" hspace="2px" ></a>';

			// Delete
			$legende = $langs->trans("AgfDocDel");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=del&model='.$model.'&cour='.$nom_courrier.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/editdelete.png" border="0" align="absmiddle" hspace="2px" ></a>';
		}
		else
		{
			// Create PDF document
			$legende = $langs->trans("AgfDocCreate");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=create&socid='.$socid.'&model='.$model.'&cour='.$nom_courrier.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a>';
		}

		// Edit Convention
		$legende = $langs->trans("AgfDocEdit");
		$mess.= '<a href="'.dol_buildpath('/agefodd/session/convention.php',1).'?action=edit&id='.$agf->id.'" alt="'.$legende.'" title="'.$legende.'">';
		$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" align="absmiddle" hspace="2px" ></a>';


	}
	elseif ($continue)
	{
		// If not exists you should do it now
		$legende = $langs->trans("AgfDocEdit");
		$mess.= '<a href="'.dol_buildpath('/agefodd/session/convention.php',1).'?action=create&sessid='.$id.'&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
		$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a>';

	}

	return $mess;
}

function show_doc($file, $socid, $nom_courrier)
{
	global $langs, $conf, $id, $form, $idform;

	$model = $file;
	if(!empty($nom_courrier)) $file = $file.'-'.$nom_courrier.'_'.$id.'_'.$socid.'.pdf';
	elseif (!empty($socid)) $file = $file.'_'.$id.'_'.$socid.'.pdf';
	elseif ($model=='fiche_pedago') $file=$file.'_'.$idform.'.pdf';
	else $file = $file.'_'.$id.'.pdf';
	
	$model='demo';

	if (is_file($conf->agefodd->dir_output.'/'.$file))
	{
		// afficher
		$legende = $langs->trans("AgfDocOpen");
		$mess = '<a href="'.DOL_URL_ROOT.'/document.php?modulepart=agefodd&file='.$file.'" alt="'.$legende.'" title="'.$legende.'">';
		$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/pdf2.png" border="0" align="absmiddle" hspace="2px" ></a>';

		// Regenerer
		$legende = $langs->trans("AgfDocRefresh");
		$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=refresh&model='.$model.'&cour='.$nom_courrier.'&idform='.$idform.'" alt="'.$legende.'" title="'.$legende.'">';
		$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/refresh.png" border="0" align="absmiddle" hspace="2px" ></a>';

		// Supprimer
		$legende = $langs->trans("AgfDocDel");
		$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=del&model='.$model.'&cour='.$nom_courrier.'&idform='.$idform.'" alt="'.$legende.'" title="'.$legende.'">';
		$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/editdelete.png" border="0" align="absmiddle" hspace="2px" ></a>';

	}
	else
	{
		// Génereration des documents
		if (file_exists(dol_buildpath('/agefodd/core/modules/agefodd/pdf/pdf_'.$model.'.modules.php')))
		{
			$legende = $langs->trans("AgfDocCreate");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=create&socid='.$socid.'&model='.$model.'&cour='.$nom_courrier.'&idform='.$idform.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a>';
		}
		else
		{
			$mess = $form->textwithpicto('',$langs->trans("AgfDocNoTemplate"),1,'warning');
		}
	}
	return $mess;
}

function show_fac($file, $socid, $mdle)
{
	global $langs, $conf, $db, $id, $form;

	$agf = new Agefodd_facture($db);
	$result = $agf->fetch($id, $socid);

	// Manage order
	if ($mdle == 'bc')
	{
		if ($agf->comid)
		{
			// Create order
			$legende = $langs->trans("AgfFactureSeeBon",$agf->comref);
			$mess.= '<a href="'.DOL_URL_ROOT.'/commande/fiche.php?mainmenu=commercial&id='.$agf->comid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" align="absmiddle" hspace="2px" ></a>';

			// Go to send mail card
			$legende = $langs->trans("AgfFactureSeeBonMail",$agf->comref);
			$mess.= '<a href="'.DOL_URL_ROOT.'/commande/fiche.php?mainmenu=commercial&id='.$agf->comid.'&action=presend&mode=init" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" ></a>';

			// Unlink order
			$legende = $langs->trans("AgfFactureUnselectBon");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?action=unlink&id='.$id.'&type=bc&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.dol_buildpath('/agefodd/img/unlink.png',1).'" border="0" align="absmiddle" hspace="2px" ></a>';
		}
		else
		{
			$mess = '<table class="nobordernopadding"><tr>';

			// Create Order
			$legende = $langs->trans("AgfFactureGenererBonAuto");
			$mess .= '<td><a href="'.$_SERVER['PHP_SELF'].'?action=createorder&id='.$id.'&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess .= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a></td>';

			// Generer le bon de commande
			$legende = $langs->trans("AgfFactureGenererBon");
			$mess .= '<td><a href="'.DOL_URL_ROOT.'/commande/fiche.php?mainmenu=commercial&action=create&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess .= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a></td>';
				
			
			// Link existing order
			$legende = $langs->trans("AgfFactureSelectBon");
			$mess.= '<td><a href="'.dol_buildpath('/agefodd/session/document.php',1).'?action=link&id='.$id.'&type=bc&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.dol_buildpath('/agefodd/img/link.png',1).'" border="0" align="absmiddle" hspace="2px" ></a></td>';

			$mess.= '<td>'.$form->textwithpicto('',$langs->trans("AgfFactureBonBeforeSelectHelp"),1,'help').'</td>';

			$mess .= '</tr></table>';
		}
	}
	// Manage Invoice
	elseif ($mdle == 'fac')
	{
		if ($agf->facid)
		{
			// See Invoice card
			$legende = $langs->trans("AgfFactureSeeFac").' '.$agf->facnumber;
			$mess = '<a href="'.DOL_URL_ROOT.'/compta/facture.php?mainmenu=accountancy&facid='.$agf->facid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" align="absmiddle" hspace="2px" ></a>';

			// Go to send mail card
			$legende = $langs->trans("AgfFactureSeeFacMail",$agf->facnumber);
			$mess.= '<a href="'.DOL_URL_ROOT.'/compta/facture.php?mainmenu=accountancy&id='.$agf->facid.'&action=presend&mode=init" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" ></a>';

			// Unlink invoice
			$legende = $langs->trans("AgfFactureUnselectFac");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?action=unlink&id='.$id.'&type=fac&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.dol_buildpath('/agefodd/img/unlink.png',1).'" border="0" align="absmiddle" hspace="2px" ></a>';

		}
		else
		{
			
			$mess = '';
			
			//Create invoice from propal if exists
			if (!empty($agf->propalid)) {
				$legende = $langs->trans("AgfFactureAddFacFromPropal");
				$propal_static= new Propal($db);
				$mess.= '<a href="'.DOL_URL_ROOT.'/compta/facture.php?mainmenu=accountancy&action=create&origin='.$propal_static->element.'&originid='.$agf->propalid.'&socid='.$socid.'"  alt="'.$legende.'" title="'.$legende.'">';
				$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a>';
			}
			
			if (!empty($conf->global->AGF_USE_FAC_WITHOUT_ORDER)) {
				

				// Create invoice from order if exists
				if (!empty($agf->comid)) {
					$legende = $langs->trans("AgfFactureAddFacFromOrder");
					$commande_static= new Commande($db);
					$mess.= '<a href="'.DOL_URL_ROOT.'/compta/facture.php?mainmenu=accountancy&action=create&origin='.$commande_static->element.'&originid='.$agf->comid.'&socid='.$socid.'"  alt="'.$legende.'" title="'.$legende.'">';
					$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a>';
				}
				// link existing invoice
				$legende = $langs->trans("AgfFactureSelectFac");
				$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?action=link&id='.$id.'&type=fac&socid='.$socid.'" alt="'.$legende.'" alt="'.$legende.'" title="'.$legende.'">';
				$mess.= '<img src="'.dol_buildpath('/agefodd/img/link.png',1).'" border="0" align="absmiddle" hspace="2px" ></a>';
			}
			elseif (!empty($agf->comid)) {
				$mess = '';
					
				$legende = $langs->trans("AgfFactureAddFacFromOrder");
				$commande_static= new Commande($db);
				$mess.= '<a href="'.DOL_URL_ROOT.'/compta/facture.php?mainmenu=accountancy&action=create&origin='.$commande_static->element.'&originid='.$agf->comid.'&socid='.$socid.'"  alt="'.$legende.'" title="'.$legende.'">';
				$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a>';
				
				//link existing invoice
				$legende = $langs->trans("AgfFactureSelectFac");
				$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?action=link&id='.$id.'&type=fac&socid='.$socid.'" alt="'.$legende.'" alt="'.$legende.'" title="'.$legende.'">';
				$mess.= '<img src="'.dol_buildpath('/agefodd/img/link.png',1).'" border="0" align="absmiddle" hspace="2px" ></a>';
			}else
			{
				$mess = $form->textwithpicto('',$langs->trans("AgfFactureFacNoBonHelp"),1,'help');
			}
		}
	}
	// Manage Invoice
	elseif ($mdle == 'prop')
	{
		if ($agf->propalid)
		{
			// See Proposal card
			$legende = $langs->trans("AgfFactureSeeProp").' '.$agf->propalref;
			$mess = '<a href="'.DOL_URL_ROOT.'/comm/propal.php?id='.$agf->propalid.'&mainmenu=commercial" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" align="absmiddle" hspace="2px" ></a>';
	
			// Go to send mail card
			$legende = $langs->trans("AgfFactureSeePropMail",$agf->propalref);
			$mess.= '<a href="'.DOL_URL_ROOT.'/comm/propal.php?id='.$agf->propalid.'&mainmenu=commercial&action=presend&mode=init" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" ></a>';
	
			// Unlink invoice
			$legende = $langs->trans("AgfFactureUnselectProp");
			$mess.= '<a href="'.$_SERVER['PHP_SELF'].'?action=unlink&id='.$id.'&type=prop&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.dol_buildpath('/agefodd/img/unlink.png',1).'" border="0" align="absmiddle" hspace="2px" ></a>';
	
		}
		else
		{
			
			$mess = '<table class="nobordernopadding"><tr>';

			// Create Order
			$legende = $langs->trans("AgfFactureGenererPropAuto");
			$mess .= '<td><a href="'.$_SERVER['PHP_SELF'].'?action=createproposal&id='.$id.'&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess .= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a></td>';
			
			// Generer le bon de commande
			$legende = $langs->trans("AgfFactureGenererProp");
			$mess .= '<td><a href="'.DOL_URL_ROOT.'/comm/propal.php?action=create&mainmenu=commercial&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess .= '<img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/filenew.png" border="0" align="absmiddle" hspace="2px" ></a></td>';
			

			// Link existing order
			$legende = $langs->trans("AgfFactureSelectProp");
			$mess.= '<td><a href="'.dol_buildpath('/agefodd/session/document.php',1).'?action=link&id='.$id.'&type=prop&socid='.$socid.'" alt="'.$legende.'" title="'.$legende.'">';
			$mess.= '<img src="'.dol_buildpath('/agefodd/img/link.png',1).'" border="0" align="absmiddle" hspace="2px" ></a></td>';

			$mess.= '<td>'.$form->textwithpicto('',$langs->trans("AgfFacturePropBeforeSelectHelp"),1,'help').'</td>';

			$mess .= '</tr></table>';
		}
	}
	else
	{
		$mess = 'error';
	}
	return $mess;
}

function document_line($intitule, $level=2, $mdle, $socid=0, $nom_courrier='')
{
	print '<tr style="height:14px">'."\n";
	if ($level == 2)
	{
		//print '<td style="border:0px; width:10px">&nbsp;</td>'."\n";
		if ( $mdle == 'bc' || $mdle == 'fac' ||  $mdle == 'prop')
		{
			print '<td style="width="90px;border-left:0px;" align="left">'.show_fac($mdle, $socid, $mdle).'</td>'."\n";
		}
		elseif ( $mdle == 'convention')
		{
			print '<td style="border-left:0px; width:90px" align="left">'.show_conv($mdle, $socid,$nom_courrier).'</td>'."\n";
		}
		else
		{
			print '<td style="border-left:0px; width:90px"  align="left">'.show_doc($mdle, $socid, $nom_courrier).'</td>'."\n";
		}
		print '<td style="border-right:0px;">';
	}
	else print '<td colspan="2" style="border-right:0px;">';
	print $intitule.'</td>'."\n";

	print '</tr>';
}

function document_send_line($intitule, $level=2, $mdle, $socid=0, $nom_courrier='')
{
	global $conf,$langs,$id, $idform;
	$langs->load('mails');
	print '<tr style="height:14px">'."\n";
	if ($level == 2)
	{
		print '<td style="border:0px; width:10px">&nbsp;</td>'."\n";
		print '<td style="border-right:0px;">';
	}
	else print '<td colspan="2" style="border-right:0px;">';
	print $intitule.'</td>'."\n";
	if ( $mdle == 'bc' || $mdle == 'fac')
	{
		print '<td style="border-left:0px;" align="right">'.show_fac($mdle, $socid, $mdle).'</td></tr>'."\n";
	}
	elseif ( $mdle == 'convention')
	{
		print '<td style="border-left:0px; width:200px"  align="right">';

		// Check if file exist
		$filename = 'convention_'.$id.'_'.$socid.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=presend_convention&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');

		print '</td></tr>'."\n";
	}
	else if ($mdle == 'fiche_presence') {

		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		//$filename = 'fiche_presence_'.$id.'_'.$socid.'.pdf';
		$filename = 'fiche_presence_'.$id.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=presend_presence&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";

	}
	else if ($mdle == 'attestation') {
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		$filename = 'attestation_'.$id.'_'.$socid.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=presend_attestation&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
	elseif ( $mdle == 'cloture')
	{
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		$filename = 'courrier-cloture_'.$id.'_'.$socid.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=presend_cloture&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
	elseif ( $mdle == 'accueil')
	{
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		$filename = 'courrier-accueil_'.$id.'_'.$socid.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=presend_accueil&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
	elseif ( $mdle == 'convocation')
	{
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		$filename = 'convocation_'.$id.'_'.$socid.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&socid='.$socid.'&action=presend_convocation&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
	elseif ($mdle == 'conseils')
	{
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		$filename = 'conseils_'.$id.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=presend_conseils&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
	elseif ($mdle == 'fiche_pedago')
	{
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		$filename = 'fiche_pedago_'.$idform.'.pdf';
		$file = $conf->agefodd->dir_output . '/' .$filename;
		if(file_exists($file)) {
			print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&action=presend_pedago&mode=init"><img src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/stcomm0.png" border="0" align="absmiddle" hspace="2px" alt="send" /> '.$langs->trans('SendMail').'</a>';
		}
		else print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
	else
	{
		print '<td style="border-left:0px; width:200px"  align="right">';
		// Check if file exist
		print $langs->trans('AgfDocNotDefined');
		print '</td></tr>'."\n";
	}
}
