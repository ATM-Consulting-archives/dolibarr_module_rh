<?php
	require('config.php');
	require('./class/absence.class.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$rtt=new TRH_Rtt;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				_fiche($ATMdb, $rtt,'edit');
				break;	
				
			case 'edit'	:
				_fiche($ATMdb, $rtt,'edit');
				break;
				
			case 'save':
				//$ATMdb->db->debug=true;
				$rtt->load($ATMdb, $_REQUEST['id']);
				$rtt->set_values($_REQUEST);
				$rtt->save($ATMdb);
				$rtt->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				$mode = 'view';
				_fiche($ATMdb, $rtt,'view');
				break;
				
			case 'view':
				_fiche($ATMdb, $rtt,'view');
				break;
				
			case 'delete':
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		_liste($ATMdb, $rtt);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$rtt) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos RTT');
	getStandartJS();
	
	$r = new TSSRenderControler($rtt);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre',r.rttAcquis as 'rttAcquis', r.rttPris as 'rttPris', 
	r.typeAcquisition as 'typeAcquisition', r.rttAcquisMensuel as 'rttAcquisMensuel', 
	r.rttAcquisAnnuelCumule as 'rttAcquisAnnuelCumule', r.rttAcquisAnnuelNonCumule as 'rttAcquisAnnuelNonCumule', 
	r.fk_user as 'Utilisateur Courant'
		FROM llx_rh_rtt as r
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
			'titre'=>'Liste de vos RTT acquis'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun RTT à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$rtt, $mode) {
	global $db,$user;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $rtt->getId());
	echo $form->hidden('action', 'save');
	
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1;
	
	//récupération des informations des rtt courants (année N) de l'utilisateur courant : 
	$sqlRtt="SELECT * FROM `llx_rh_rtt` where fk_user=".$user->id;
	$ATMdb->Execute($sqlRtt);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$rttCourant=new User($db);
				$rttCourant->id=$ATMdb->Get_field('rowid');
				$rttCourant->acquis=$ATMdb->Get_field('rttAcquis');
				$rttCourant->pris=$ATMdb->Get_field('rttPris');
				$rttCourant->mensuel=$ATMdb->Get_field('rttAcquisMensuel');
				$rttCourant->annuelCumule=$ATMdb->Get_field('rttAcquisAnnuelCumule');
				$rttCourant->annuelNonCumule=$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				$rttCourant->typeAcquisition=$ATMdb->Get_field('typeAcquisition');
				$rttCourant->annee=substr($ATMdb->Get_field('annee'),0,4);
				$rttCourant->fk_user=$ATMdb->Get_field('fk_user');
				$Tab[]=$rttCourant;	
	}
	
	$rttCourantReste=$rttCourant->acquis-$rttCourant->pris;
	$idRttCourant=substr($rttCourant->id,0,1);
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/rtt.tpl.php'
		,array(
			
		)
		,array(
			'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',$rttCourant->acquis,10,50,'',$class="text", $default='')
				,'rowid'=>$form->texte('','rowid',$rttCourant->id,10,50,'',$class="text", $default='')
				,'id'=>$form->texte('','fk_user',$rttCourant->id,10,50,'',$class="text", $default='')
				,'pris'=>$form->texte('','rttPris',$rttCourant->pris,10,50,'',$class="text", $default='')
				,'mensuel'=>$form->texte('','rttAcquisMensuel',$rttCourant->mensuel,10,50,'',$class="text", $default='')
				,'annuelCumule'=>$form->texte('','rttAcquisAnnuelCumule',$rttCourant->annuelCumule,10,50,'',$class="text", $default='')
				,'annuelNonCumule'=>$form->texte('','rttAcquisAnnuelNonCumule',$rttCourant->annuelNonCumule,10,50,'',$class="text", $default='')
				,'typeAcquisition'=>$form->texte('','typeAcquisition',$rttCourant->typeAcquisition,10,50,'',$class="text", $default='')
				,'reste'=>$form->texte('','total',$rttCourantReste,10,50,'',$class="text", $default='')
				,'idNum'=>$idRttCourant
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


	
	
