<?php

	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new TPDOdb;

	if(isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
		
		if(!empty($_REQUEST['TTypeAbsence'])) {
			foreach($_REQUEST['TTypeAbsence'] as $id=>$TValues) {
				
				$ta=new TRH_TypeAbsence;
				$ta->load($ATMdb, $id);
				$ta->set_values($TValues);
				
				if(isset($TValues['delete'])) {
					$ta->delete($ATMdb);
				}
				else {
					$ta->save($ATMdb);	
				}
			}
		}
		
		$newTA = & $_REQUEST['TTypeAbsenceNew']; 
		
		//print_r($_REQUEST['TTypeAbsenceNew']);
		
		if(!empty($newTA['typeAbsence']) && !empty($newTA['libelleAbsence'])) {
			
			$ta=new TRH_TypeAbsence;
			$ta->set_values($newTA);
			$ta->save($ATMdb);
		}

		
	}


	llxHeader();
	
	$TAbsenceType = TRH_TypeAbsence::getList($ATMdb, true);

	$absenceTypeDummy = new TRH_TypeAbsence;

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff('edit');
	echo $form->hidden('action', 'save');

	$TFormAbsenceType=array();

	foreach($TAbsenceType as $absenceType) {
		
		$TFormAbsenceType[]=array(
			'typeAbsence'=>$form->texte('', 'TTypeAbsence['.$absenceType->getId().'][typeAbsence]', $absenceType->typeAbsence, 15,50)
			,'libelleAbsence'=>$form->texte('', 'TTypeAbsence['.$absenceType->getId().'][libelleAbsence]', $absenceType->libelleAbsence, 30,255)
			,'codeAbsence'=>$form->texte('', 'TTypeAbsence['.$absenceType->getId().'][codeAbsence]', $absenceType->codeAbsence, 6,10)
			
			,'unite'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][unite]', $absenceTypeDummy->TUnite , $absenceType->unite)
			,'decompteNormal'=>$form->hidden( 'TTypeAbsence['.$absenceType->getId().'][decompteNormal]', 'oui')
			,'isPresence'=>$form->hidden( 'TTypeAbsence['.$absenceType->getId().'][isPresence]', 1) 
			
			,'hourStart'=>$form->timepicker('', 'TTypeAbsence['.$absenceType->getId().'][date_hourStart]', $absenceType->get_date('date_hourStart','H:i') , 5,10)
			,'hourEnd'=>$form->timepicker('', 'TTypeAbsence['.$absenceType->getId().'][date_hourEnd]', $absenceType->get_date('date_hourEnd','H:i'), 5,10)
			
			,'colorId'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][colorId]', $absenceTypeDummy->TColorId , $absenceType->colorId)
			
			,'admin'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][admin]', $absenceTypeDummy->TForAdmin , $absenceType->admin)
			
			,'delete'=>$form->checkbox1('', 'TTypeAbsence['.$absenceType->getId().'][delete]', 1)
		);
		
	}

	$TFormAbsenceTypeNew=array(
			'typeAbsence'=>$form->texte('', 'TTypeAbsenceNew[typeAbsence]', '', 15,50)
			,'libelleAbsence'=>$form->texte('', 'TTypeAbsenceNew[libelleAbsence]', '', 30,255)
			,'codeAbsence'=>$form->texte('', 'TTypeAbsenceNew[codeAbsence]', '', 6,10)
			
			,'unite'=>$form->combo('', 'TTypeAbsenceNew[unite]', $absenceTypeDummy->TUnite , null)
			,'hourStart'=>$form->timepicker('', 'TTypeAbsenceNew[date_hourStart]', '8:00', 5,10)
			,'hourEnd'=>$form->timepicker('', 'TTypeAbsenceNew[date_hourEnd]', '18:00', 5,10)
			,'decompteNormal'=>$form->hidden('TTypeAbsenceNew[decompteNormal]', 'oui')
			,'isPresence'=>$form->hidden('TTypeAbsenceNew[isPresence]', 1)
			,'admin'=>$form->combo('', 'TTypeAbsenceNew[admin]', $absenceTypeDummy->TForAdmin , null)
			
			,'colorId'=>$form->combo('', 'TTypeAbsenceNew[colorId]', $absenceTypeDummy->TColorId , null)
			
			
		);


	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/typePresence.tpl.php'
		,array(
			'typeAbsence'=>$TFormAbsenceType
		)
		,array(
			'typeAbsenceNew'=>$TFormAbsenceTypeNew
			,'view'=>array(
				'head'=>dol_get_fiche_head(adminCongesPrepareHead('compteur')  , 'typepresence', $langs->trans('AbsencesPresencesAdministration'))
			)
			,'translate' => array(
				'Code' => $langs->trans('Code'),
				'Wording' => $langs->trans('Wording'),
				'Unit' => $langs->trans('Unit'),
				'StartHour' => $langs->trans('StartHour'),
				'EndHour' => $langs->trans('EndHour'),
				'AccountingOfficerCode' => $langs->trans('AccountingOfficerCode'),
				'ColorCode' => $langs->trans('ColorCode'),
				'AskReservedAdmin' => $langs->trans('AskReservedAdmin'),
				'AskDelete' => $langs->trans('AskDelete'),
				'Register' => $langs->trans('Register')
			)
		)	
		
	);
	
	echo $form->end_form();	
	
	llxFooter();