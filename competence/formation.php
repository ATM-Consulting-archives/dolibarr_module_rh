<?php
	require('config.php');
	require('./class/formation.class.php');
	require('./lib/competence.lib.php');
	$langs->load('competence@competence');
	
	$ATMdb=new TPDOdb;
	$formation = new TRH_formation;
	$planFormation = new TRH_formation_plan;
	
	(!empty($_REQUEST['idPlan'])) ? $planFormation->load($ATMdb, $_REQUEST['idPlan']) : '' ;
	(!empty($_REQUEST['id'])) ? $formation->load($ATMdb, $_REQUEST['id']) : '' ;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$formation->set_values($_REQUEST);
				_fiche($ATMdb,$formation,$planFormation,'new');
				break;	
				
			case 'edit':
				_fiche($ATMdb,$formation,$planFormation,'edit');
				break;
				
			case 'save':
				$formation->set_values($_REQUEST);
				$formation->save($ATMdb);
				_fiche($ATMdb,$formation,$planFormation,'view');
				break;
			
			case 'view':
				_fiche($ATMdb,$formation,$planFormation,'view');
				break;
				
			case 'delete':
				_liste($planFormation);
				break;
		}
	}
	elseif(isset($_REQUEST['id']))
		_fiche($ATMdb,$formation,$planFormation);
	else
		_liste($planFormation);
	
	$ATMdb->close();
	

function _liste(&$ATMdb,$planFormation) {
	header('Location: planFormation.php?id='.$planFormation->getId());
}

function _fiche(&$ATMdb,&$formation,&$planFormation,$mode = 'view') {
	global $langs,$conf,$db,$user;
	
	llxHeader('', 'Formation');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_formation_plan',$planFormation->getId());
		
	//Chargement de la liste des Sessions de Formation associé à la Formation en cours
	$listeSessionFormation = $formation->getListeSessionFormation($ATMdb);
		
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/formationCompetence.tpl.php'
		,array()
		,array(
			'formation'=>array(
				'ID'=>$formation->rowid
				,'libelle'=>$form->texte('','libelle', $formation->libelle,50,255,'','','-')
				,'description'=>$form->zonetexte('','description',$formation->description,50)
				,'budget'=>$form->texte('','budget',$formation->budget,10,255,'','')
				,'budgetConsomme'=>$form->texte('','budget_consomme',$formation->budget_consomme,10,255,'','')
			)
			,'listeSessionFormation'=>array(
				'liste' => $listeSessionFormation	
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->createRessource)
				,'head'=>dol_get_fiche_head(competencePrepareHead($planFormation,'formation'),'fiche','Formation')
				,'onglet'=>dol_get_fiche_head(array(),'','Création de formation')
			)
		)
	);
	
	echo $form->end_form();
	
	llxFooter();
}