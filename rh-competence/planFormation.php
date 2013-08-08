<?php
	require('config.php');
	require('./class/formation.class.php');
	require('./lib/competence.lib.php');
	$langs->load('competence@competence');
	
	$ATMdb=new Tdb;
	$planFormation = new TRH_formation_plan;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
				_liste($ATMdb);
				break;
				
			case 'new':
				$planFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$planFormation);
				break;	
				
			case 'edit':
				$planFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$planFormation);
				break;
				
			case 'save':
				$planFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$planFormation);
				break;
			
			case 'view':
				$planFormation->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb,$planFormation);
				break;
				
			case 'delete':
				_liste($ATMdb);
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		$planFormation->load($ATMdb, $_REQUEST['id']);
		_fiche($ATMdb,$planFormation);
	}
	else {
		_liste($ATMdb);
	}
	
	$ATMdb->close();
	llxFooter();
	

function _liste(&$ATMdb) {
	global $langs,$conf,$db,$user;
	
	llxHeader('','Liste des Plans de Formations');
	print dol_get_fiche_head(array()  , '', 'Liste des Plans de Formations');
	
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
		,'orderBy'=>$TOrder
	));
	
	llxFooter();
}

function _fiche(&$ATMdb,&$planFormation) {
	global $langs,$conf,$db,$user;
	
	llxHeader('', 'Plan de Formation');
	print dol_get_fiche_head(array()  , '', 'Plan de Formation');
	
	llxFooter();
}