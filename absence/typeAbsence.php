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
            
            setEventMessage($langs->trans('Saved'));
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
	
	$TAbsenceType = TRH_TypeAbsence::getList($ATMdb);

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
			
			,'colorId'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][colorId]', $absenceTypeDummy->TColorId , $absenceType->colorId)
			
			,'unite'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][unite]', $absenceTypeDummy->TUnite , $absenceType->unite)
			,'decompteNormal'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][decompteNormal]', $absenceTypeDummy->TDecompteNormal , $absenceType->decompteNormal)
            ,'secable'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][insecable]', $absenceTypeDummy->TForAdmin , $absenceType->insecable)
            ,'isPresence'=>$form->hidden( 'TTypeAbsence['.$absenceType->getId().'][isPresence]', 0)
			,'admin'=>$form->combo('', 'TTypeAbsence['.$absenceType->getId().'][admin]', $absenceTypeDummy->TForAdmin , $absenceType->admin)
			
			,'delete'=>$form->checkbox1('', 'TTypeAbsence['.$absenceType->getId().'][delete]', 1)
		);
		
	}

	$TFormAbsenceTypeNew=array(
			'typeAbsence'=>$form->texte('', 'TTypeAbsenceNew[typeAbsence]', '', 15,50)
			,'libelleAbsence'=>$form->texte('', 'TTypeAbsenceNew[libelleAbsence]', '', 30,255)
			,'codeAbsence'=>$form->texte('', 'TTypeAbsenceNew[codeAbsence]', '', 6,10)
			
			,'unite'=>$form->combo('', 'TTypeAbsenceNew[unite]', $absenceTypeDummy->TUnite , null)
			,'decompteNormal'=>$form->combo('', 'TTypeAbsenceNew[decompteNormal]', $absenceTypeDummy->TDecompteNormal , null)
			,'isPresence'=>$form->hidden( 'TTypeAbsenceNew[isPresence]', 0)
			,'admin'=>$form->combo('', 'TTypeAbsenceNew[admin]', $absenceTypeDummy->TForAdmin , null)
			
			,'colorId'=>$form->combo('', 'TTypeAbsenceNew[colorId]', $absenceTypeDummy->TColorId , null)
		);


	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/typeAbsence.tpl.php'
		,array(
			'typeAbsence'=>$TFormAbsenceType
		)
		,array(
			'typeAbsenceNew'=>$TFormAbsenceTypeNew
			,'view'=>array(
				'head'=>dol_get_fiche_head(adminCongesPrepareHead('compteur')  , 'typeabsence', $langs->trans('AbsencesPresencesAdministration'))
			)
			,'translate' => array(
				'Code' => $langs->trans('Code'),
				'Wording' => $langs->trans('Wording'),
				'Unit' => $langs->trans('Unit'),
				'AccountingOfficerCode' => $langs->trans('AccountingOfficerCode'),
				'AbsenceSecable' => $langs->trans('AbsenceSecable'),
				'ColorCode' => $langs->trans('ColorCode'),
				'AskReservedAdmin' => $langs->trans('AskReservedAdmin'),
				'OnlyCountBusinessDay' => $langs->trans('OnlyCountBusinessDay'),
				'New' => $langs->trans('New'),
				'Register' => $langs->trans('Register'),
				'AskDelete' => $langs->trans('AskDelete')
			)
		)	
		
	);
	
	echo $form->end_form();	
	
	llxFooter();