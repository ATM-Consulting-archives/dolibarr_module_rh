<?php
	require('config.php');
	require('./class/absence.class.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$conge=new TRH_Conge;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
		fiche($ATMdb, $conge,'edit');
				
				break;	
			case 'edit'	:
			_fiche($ATMdb, $conge,'edit');
				break;
				
			case 'save':
				fiche($ATMdb, $conge,'edit');
			
				break;
			
			case 'view':
			_fiche($ATMdb, $conge,'view');
				break;
			
				
				
				
			case 'delete':
				
				
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		_liste($ATMdb, $conge);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$conge) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos congés');
	getStandartJS();
	
	$r = new TSSRenderControler($conge);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre',r.acquisExercice as 'acquisExercice', r.acquisAnciennete as 'acquisAnciennete', r.acquisHorsPeriode as 'acquisHorsPeriode', 
			r.reportConges as 'reportConges', annee as 'annee', r.duree as 'duree', r.fk_user as 'Utilisateur Courant'
		FROM llx_rh_conge as r
		WHERE r.fk_user=".$user->id;
		
	
	$TOrder = array('DateCre'=>'ASC');
	if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
	if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
				
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;			
	//print $page;
	$r->liste($ATMdb, $sql, array(
		'limit'=>array(
			'page'=>$page
			,'nbLine'=>'30'
		)
		,'link'=>array(
			'ID'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre', 'duree')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste de vos congés payés acquis'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun congé à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$conge, $mode) {
	global $db,$user;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $conge->getId());
	echo $form->hidden('action', 'save');
	
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1;
	//récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReqUser="SELECT * FROM `llx_rh_conge` where fk_user=".$user->id." AND annee=".$anneePrec;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$congePrec=new User($db);
				$congePrec->id=$ATMdb->Get_field('rowid');
				$congePrec->acquisEx=$ATMdb->Get_field('acquisExercice');
				$congePrec->acquisAnc=$ATMdb->Get_field('acquisAnciennete');
				$congePrec->acquisHorsPer=$ATMdb->Get_field('acquisHorsPeriode');
				$congePrec->reportConges=$ATMdb->Get_field('reportConges');
				$congePrec->congesPris=$ATMdb->Get_field('congesPris');
				$congePrec->annee=substr($ATMdb->Get_field('annee'),0,4);
				$congePrec->fk_user=$ATMdb->Get_field('fk_user');
				$Tab[]=$congePrec;	
	}
	
	$congePrecTotal=$congePrec->acquisEx+$congePrec->acquisAnc+$congePrec->acquisHorsPer+$congePrec->reportConges;
	$congePrecReste=$congePrecTotal-$congePrec->congesPris;
	
	//récupération des informations des congés précédents (N-1) de l'utilisateur courant : 
	$sqlReqUser2="SELECT * FROM `llx_rh_conge` where fk_user=".$user->id." AND annee=".$anneeCourante;
	$ATMdb=new Tdb;
	$ATMdb->Execute($sqlReqUser2);
	$Tab2=array();
	while($ATMdb->Get_line()) {
				$congeCourant=new User($db);
				$congeCourant->id=$ATMdb->Get_field('rowid');
				$congeCourant->acquisEx=$ATMdb->Get_field('acquisExercice');
				$congeCourant->acquisAnc=$ATMdb->Get_field('acquisAnciennete');
				$congeCourant->acquisHorsPer=$ATMdb->Get_field('acquisHorsPeriode');
				$congeCourant->annee=substr($ATMdb->Get_field('annee'),0,4);
				$congeCourant->fk_user=$ATMdb->Get_field('fk_user');
				$Tab2[]=$congeCourant;	
	}
	
	$congeCourantTotal=$congeCourant->acquisEx+$congeCourant->acquisAnc+$congeCourant->acquisHorsPer;
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/conges.tpl.php'
		,array(
			
			
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisex',$congePrec->acquisEx,10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisanc',$congePrec->acquisAnc,10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquishorsper',$congePrec->acquisHorsPer,10,50,'',$class="text", $default='')
				,'reportConges'=>$form->texte('','reportconges',$congePrec->reportConges,10,50,'',$class="text", $default='')
				,'congesPris'=>$form->texte('','congespris',$congePrec->congesPris,10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','annee',$anneePrec,10,50,'',$class="text", $default='')
				,'anneePrec'=>$form->texte('','annee',$anneePrec,10,50,'',$class="text", $default='')
				,'total'=>$form->texte('','total',$congePrecTotal,10,50,'',$class="text", $default='')
				,'reste'=>$form->texte('','reste',$congePrecReste,10,50,'',$class="text", $default='')
			)
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisex',$congeCourant->acquisEx,10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisanc',$congeCourant->acquisAnc,10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquishorsper',$congeCourant->acquisHorsPer,10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','annee',$anneeCourante,10,50,'',$class="text", $default='')
				,'total'=>$form->texte('','total',$congeCourantTotal,10,50,'',$class="text", $default='')
				,
			)
			,'userCourant'=>array(
				'id'=>$user->id
				,'lastname'=>$user->lastname
				,'firstname'=>$user->firstname
			)
			
			,'view'=>array(
				'mode'=>$mode
			
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
