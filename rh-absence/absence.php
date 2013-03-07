<?php
	require('config.php');
	require('./class/absence.class.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$absence=new TRH_Absence;
	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				$absence->set_values($_REQUEST);
				_fiche($ATMdb, $absence,'edit');
				
				break;	
			case 'edit'	:
				$absence->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $absence,'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				$absence->load($ATMdb, $_REQUEST['id']);
				$absence->set_values($_REQUEST);
				$absence->save($ATMdb);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb, $absence,'view');
			
				break;
			
			case 'view':
				$absence->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $absence,'view');
				break;

			case 'delete':
				$absence->load($ATMdb, $_REQUEST['id']);
				//$ATMdb->db->debug=true;
				
				//avant de supprimer, on récredite les heures d'absences qui avaient été décomptées. 
				$sqlAbs="SELECT * FROM `llx_rh_absence` where fk_user=".$user->id. " AND rowid=".$absence->getId();
				$ATMdb->Execute($sqlAbs);
				$Tab=array();
				while($ATMdb->Get_line()) {
							$absenceCourante=new User($db);
							$absenceCourante->id=$ATMdb->Get_field('rowid');
							$absenceCourante->type=$ATMdb->Get_field('type');
							$absenceCourante->dateDebut=$ATMdb->Get_field('date_debut');
							$absenceCourante->dateFin=$ATMdb->Get_field('date_fin');
							$absenceCourante->commentaire=$ATMdb->Get_field('commentaire');
							$absenceCourante->fk_user=$ATMdb->Get_field('fk_user');
							$Tab[]=$absenceCourante;	
				}

				$absence->delete($ATMdb);
				?>
				<script language="javascript">
					document.location.href="?delete_ok=1";					
				</script>
				<?
				
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		$ATMdb->db->debug=true;
		_liste($ATMdb, $absence);
	}
	
	
	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$absence) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos absences');
	getStandartJS();
	
	$r = new TSSRenderControler($absence);
	$sql="SELECT r.rowid as 'ID', r.date_cre as 'DateCre',DATE(r.date_debut) as 'Date début', DATE(r.date_fin) as 'date_fin', 
			  r.type as 'type',r.fk_user as 'Utilisateur Courant',  r.etat as 'etat'
		FROM llx_rh_absence as r
		WHERE r.fk_user=".$user->id;//." AND r.entity=".$conf->entity;
		
	
	$TOrder = array('DateCre'=>'DESC');
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
		,'hide'=>array('DateCre')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste de vos absences'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucune absence à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$absence, $mode) {
	global $db,$user;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $absence->getId());
	echo $form->hidden('action', 'save');
	
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1;
	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReqUser="SELECT * FROM `llx_rh_compteur` where fk_user=".$user->id." AND anneeNM1=".$anneePrec;//."AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$congePrec=new User($db);
				$congePrec->id=$ATMdb->Get_field('rowid');
				$congePrec->acquisEx=$ATMdb->Get_field('acquisExerciceNM1');
				$congePrec->acquisAnc=$ATMdb->Get_field('acquisAncienneteNM1');
				$congePrec->acquisHorsPer=$ATMdb->Get_field('acquisHorsPeriodeNM1');
				$congePrec->reportConges=$ATMdb->Get_field('reportCongesNM1');
				$congePrec->congesPris=$ATMdb->Get_field('congesPrisNM1');
				$congePrec->annee=$ATMdb->Get_field('anneeNM1');
				$congePrec->fk_user=$ATMdb->Get_field('fk_user');
				$Tab[]=$congePrec;	
	}
	
	$congePrecTotal=$congePrec->acquisEx+$congePrec->acquisAnc+$congePrec->acquisHorsPer+$congePrec->reportConges;
	$congePrecReste=$congePrecTotal-$congePrec->congesPris;
	
	//////////////////////////récupération des informations des congés précédents (N-1) de l'utilisateur courant : 
	$sqlReqUser2="SELECT * FROM `llx_rh_compteur` where fk_user=".$user->id." AND anneeN=".$anneeCourante;//."AND entity=".$conf->entity;;
	$ATMdb=new Tdb;
	$ATMdb->Execute($sqlReqUser2);
	$Tab2=array();
	while($ATMdb->Get_line()) {
				$congeCourant=new User($db);
				$congeCourant->id=$ATMdb->Get_field('rowid');
				$congeCourant->acquisEx=$ATMdb->Get_field('acquisExerciceN');
				$congeCourant->acquisAnc=$ATMdb->Get_field('acquisAncienneteN');
				$congeCourant->acquisHorsPer=$ATMdb->Get_field('acquisHorsPeriodeN');
				$congeCourant->annee=$ATMdb->Get_field('anneeN');
				$congeCourant->fk_user=$ATMdb->Get_field('fk_user');
				$Tab2[]=$congeCourant;	
	}
	$congeCourantTotal=$congeCourant->acquisEx+$congeCourant->acquisAnc+$congeCourant->acquisHorsPer;
	
	//////////////////////////////récupération des informations des rtt courants (année N) de l'utilisateur courant : 
	$sqlRtt="SELECT * FROM `llx_rh_compteur` where fk_user=".$user->id;
	$ATMdb->Execute($sqlRtt);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$rttCourant=new User($db);
				$rttCourant->id=$ATMdb->Get_field('rowid');
				$rttCourant->acquis=$ATMdb->Get_field('rttAcquisMensuel')+$ATMdb->Get_field('rttAcquisAnnuelCumule')+$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				$rttCourant->pris=$ATMdb->Get_field('rttPris');
				$rttCourant->mensuel=$ATMdb->Get_field('rttAcquisMensuel');
				$rttCourant->annuelCumule=$ATMdb->Get_field('rttAcquisAnnuelCumule');
				$rttCourant->annuelNonCumule=$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				$rttCourant->typeAcquisition=$ATMdb->Get_field('rttTypeAcquisition');
				$rttCourant->annee=substr($ATMdb->Get_field('anneertt'),0,4);
				$rttCourant->fk_user=$ATMdb->Get_field('fk_user');
				$Tab[]=$rttCourant;	
	}
	$rttCourantReste=$rttCourant->acquis-$rttCourant->pris;
	
	
	/////////////////////////////////////récupération des demandes d'absences du collaborateurs
	$sqlAbsence="SELECT * FROM `llx_rh_absence` where fk_user=".$user->id. " AND rowid=".$absence->getId();
	$ATMdb->Execute($sqlAbsence);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$absenceCourante=new User($db);
				$absenceCourante->id=$ATMdb->Get_field('rowid');
				$absenceCourante->type=$ATMdb->Get_field('type');
				$absenceCourante->dateDebut=$ATMdb->Get_field('date_debut');
				$absenceCourante->dateFin=$ATMdb->Get_field('date_fin');
				$absenceCourante->commentaire=$ATMdb->Get_field('commentaire');
				$absenceCourante->fk_user=$ATMdb->Get_field('fk_user');
				$Tab[]=$absenceCourante;	
	}
	
	
	///////calcul durée du congés 

	
	$dateF=new DateTime($absenceCourante->dateFin);
	$dateD=new DateTime($absenceCourante->dateDebut);
	$dureeAbsenceCourante=date_diff($dateF,$dateD);
	
	///////décompte des congés
	if($absenceCourante->type=="rttcumule"){
		$sqlDecompte="UPDATE `llx_rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante->format('%a').",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule-".$dureeAbsenceCourante->format('%a')."  where fk_user=".$user->id;
		$ATMdb->Execute($sqlDecompte);
		$rttCourant->pris=$rttCourant->pris-$dureeAbsenceCourante->format('%a');
		$rttCourant->annuelCumule=$rttCourant->annuelCumule-$dureeAbsenceCourante->format('%a');
		
	}else if($absenceCourante->type=="rttnoncumule"){
		$sqlDecompte="UPDATE `llx_rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante->format('%a').",rttAcquisAnnuelNonCumule=rttAcquisAnnuelNonCumule-".$dureeAbsenceCourante->format('%a')." where fk_user=".$user->id;
		$ATMdb->Execute($sqlDecompte);
		$rttCourant->pris=$rttCourant->pris-$dureeAbsenceCourante->format('%a');
		$rttCourant->annuelNonCumule=$rttCourant->annuelNonCumule-$dureeAbsenceCourante->format('%a');
	}
	else {	//autre que RTT : décompte les congés
		$sqlDecompte="UPDATE `llx_rh_compteur` SET congesPrisNM1=congesPrisNM1+".$dureeAbsenceCourante->format('%a')." where fk_user=".$user->id;
		$ATMdb->Execute($sqlDecompte);
		$congePrecReste=$congePrecReste-$dureeAbsenceCourante->format('%a');
	}
	
	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/absence.tpl.php'
		,array(
			
			
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',$congePrec->acquisEx,10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',$congePrec->acquisAnc,10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',$congePrec->acquisHorsPer,10,50,'',$class="text", $default='')
				,'reportConges'=>$form->texte('','reportcongesNM1',$congePrec->reportConges,10,50,'',$class="text", $default='')
				,'congesPris'=>$form->texte('','congesprisNM1',$congePrec->congesPris,10,50,'',$class="text", $default='')
				,'anneePrec'=>$form->texte('','anneeNM1',$anneePrec,10,50,'',$class="text", $default='')
				,'total'=>$form->texte('','total',$congePrecTotal,10,50,'',$class="text", $default='')
				,'reste'=>$form->texte('','reste',$congePrecReste,10,50,'',$class="text", $default='')
				,'idUser'=>$congePrec->fk_user
			)
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceN',$congeCourant->acquisEx,10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',$congeCourant->acquisAnc,10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',$congeCourant->acquisHorsPer,10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','anneeN',$anneeCourante,10,50,'',$class="text", $default='')
				,'total'=>$form->texte('','total',$congeCourantTotal,10,50,'',$class="text", $default='')
				,'idUser'=>$congeCourant->fk_user
			)
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',$rttCourant->acquis,10,50,'',$class="text", $default='')
				,'rowid'=>$form->texte('','rowid',$rttCourant->id,10,50,'',$class="text", $default='')
				,'id'=>$form->texte('','fk_user',$rttCourant->fk_user,10,50,'',$class="text", $default='')
				,'pris'=>$form->texte('','rttPris',$rttCourant->pris,10,50,'',$class="text", $default='')
				,'mensuel'=>$form->texte('','rttAcquisMensuel',$rttCourant->mensuel,10,50,'',$class="text", $default='')
				,'annuelCumule'=>$form->texte('','rttAcquisAnnuelCumule',$rttCourant->annuelCumule,10,50,'',$class="text", $default='')
				,'annuelNonCumule'=>$form->texte('','rttAcquisAnnuelNonCumule',$rttCourant->annuelNonCumule,10,50,'',$class="text", $default='')
				,'typeAcquisition'=>$form->texte('','typeAcquisition',$rttCourant->typeAcquisition,10,50,'',$class="text", $default='')
				,'reste'=>$form->texte('','total',$rttCourantReste,10,50,'',$class="text", $default='')
				,'idNum'=>$idRttCourant
				
				
			)
			,'absenceCourante'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'id'=>$absenceCourante->id
				,'commentaire'=>$form->texte('','commentaire',$absenceCourante->commentaire,20,500,'',$class="text", $default='')
				,'date_debut'=> $form->calendrier('', 'date_debut', $absence->get_date('date_debut'), 10)
				,'ddMoment'=>$form->combo('','ddMoment',$absence->TddMoment,$absence->ddMoment)
				,'date_fin'=> $form->calendrier('', 'date_fin', $absence->get_date('date_fin'), 10)
				,'dfMoment'=>$form->combo('','dfMoment',$absence->TdfMoment,$absence->dfMoment)
				,'idUser'=>$form->texte('','fk_user',$user->id,5,10,'',$class="text", $default='')
				,'comboType'=>$form->combo('','type',$absence->TTypeAbsence,$absence->type)
				,'etat'=>$form->hidden('etat',"Avalider")
				,'duree'=>$form->texte('','duree',$dureeAbsenceCourante->format('%a'),5,10,'',$class="text", $default='')
				
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


	
	
