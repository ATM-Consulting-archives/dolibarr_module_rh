<?php
	require('config.php');
	require('./class/formation.class.php');
	require('./lib/competence.lib.php');
	
	$langs->load('competence@competence');
	
	$ATMdb=new TPDOdb;
	$planFormation = new TRH_formation_plan;
	
	$action = __get('action','list');
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'new':
				$planFormation->set_values($_REQUEST);
				_fiche($ATMdb,$planFormation,'new');
				break;	
				
			case 'edit':
				 $planFormation->load($ATMdb, __get('id',0,'int'));

				_fiche($ATMdb,$planFormation,'edit');
				break;
				
			case 'save':
				$planFormation->load($ATMdb, __get('id',0,'int'));
				
				$planFormation->set_values($_REQUEST);
				$planFormation->save($ATMdb);
				_fiche($ATMdb,$planFormation,'view');
				break;
			
			case 'view':
				$planFormation->load($ATMdb, __get('id',0,'int'));
				
				_fiche($ATMdb,$planFormation,'view');
				break;
				
			case 'delete':
				$planFormation->load($ATMdb, __get('id',0,'int'));
				
				$planFormation->delete($ATMdb);
				_liste($ATMdb,$planFormation,'view');
				break;
			case 'list':
				_liste($ATMdb,$planFormation,'view');
				break;
		}
	}
	
	$ATMdb->close();
	

function _liste(&$ATMdb,$planFormation) {
	global $langs,$conf,$db,$user;
	
	llxHeader('','Plan de Formation');
	print dol_get_fiche_head(array()  , '', 'Plan de Formation');
	
	$r = new TSSRenderControler($planFormation);
	
	$sql = "SELECT fp.rowid AS 'IdPlan', fp.libelle AS 'LibellePlan', fp.description AS 'DescriptionPlan', fp.budget AS 'BudgetPlan'
		    FROM ".MAIN_DB_PREFIX."rh_formation_plan AS fp
		    ORDER BY fp.rowid ASC";
	
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');	
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'50'
		)
		,'hide'=>array('IdPlan')
		,'type'=>array('LibellePlan'=>'string')
		,'link'=>array(
			'LibellePlan'=>'<a href="?id=@IdPlan@&action=view">@val@</a>'
		)
		,'liste'=>array(
			'titre'=>'Liste des PLans de Formations'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y aucun plan à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			,'picto_search'=>'<img src="../../theme/rh/img/search.png">'
		)
		,'title'=>array(
			'LibellePlan'=>'Libellé'
			,'DescriptionPlan'=>'Description'
			,'BudgetPlan'=> 'Budget générale'
		)
		,'search'=>array(
			'LibellePlan'=>true
			,'BudgetPlan'=>true
		)
	));
	
	llxFooter();
}

function _fiche(&$ATMdb,&$planFormation,$mode = 'view') {
	global $langs,$conf,$db,$user;
	
	llxHeader('', 'Plan de Formation');
	
	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	if($mode=="edit")
		echo $form->hidden('id',$planFormation->getId());
	echo $form->hidden('action', 'save');
		
	//Chargement de la liste des Formation associé au plan
	$listeFormation = $planFormation->getListeFormation($ATMdb);
		
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/planFormation.tpl.php'
		,array()
		,array(
			'planFormation'=>array(
				'ID'=>$planFormation->rowid
				,'libelle'=>$form->texte('','libelle', $planFormation->libelle,50,255,'','','-')
				,'description'=>$form->zonetexte('','description',$planFormation->description,50)
				,'date_debut'=>$form->calendrier('','date_debut', $planFormation->date_debut,12, 12)
				,'date_fin'=>$form->calendrier('','date_fin', $planFormation->date_fin,12, 12)
				,'budget'=>$form->texte('','budget',$planFormation->budget,10,255,'','')
				,'budget_opca'=>$planFormation->budget_opca
				,'budget_final'=>$planFormation->budget_final
			)
			,'listeFormation'=>array(
				'liste' => $listeFormation	
			)
			,'view'=>array(
				'mode'=>$mode
				,'userRight'=>((int)$user->rights->ressource->ressource->createRessource)
				,'head'=>dol_get_fiche_head(competencePrepareHead($planFormation,'planFormation'),'fiche','Plan de Formation')
				,'onglet'=>dol_get_fiche_head(array(),'','Plan de formation')
			)
		)
	);
	
	echo $form->end_form();
	
	llxFooter();
}