<?php
	require('config.php');
	require('./class/absence.class.php');
	require('./lib/absence.lib.php');
	
	$langs->load('absence@absence');
	
	$ATMdb=new Tdb;
	$compteur=new TRH_Compteur;
	



	
	if(isset($_REQUEST['action'])) {
		switch($_REQUEST['action']) {
			case 'add':
			case 'new':
				_fiche($ATMdb, $compteur,'edit');
				
				break;	
			case 'edit'	:
				$compteur->load($ATMdb, $_REQUEST['id']);
				_fiche($ATMdb, $compteur,'edit');
				break;
				
			case 'save':
				$ATMdb->db->debug=true;
				//echo "salut".$compteur->rttCloture;
				//$compteur->rttCloture=date("Y-m-d h:i:s", $compteur->rttCloture);
				//echo "salut".$compteur->rttCloture;
				//print_r($compteur);
				/*$compteur->rttCloture=date($compteur->rttCloture);
				echo "salut".$compteur->rttCloture;*/
				
				$compteur->load($ATMdb, $_REQUEST['id']);
				$compteur->set_values($_REQUEST);
				$compteur->save($ATMdb);
				$compteur->load($ATMdb, $_REQUEST['id']);
				$mesg = '<div class="ok">Modifications effectuées</div>';
				_fiche($ATMdb, $compteur,'view');
			
				break;
			
			case 'view':
			
				if(isset($_REQUEST['id'])){
					$compteur->load($ATMdb, $_REQUEST['id']);
					_fiche($ATMdb, $compteur,'view');
				}else{
					//récupération compteur en cours
					$sqlReqUser="SELECT rowid FROM `llx_rh_compteur` where fk_user=".$user->id;
					$ATMdb->Execute($sqlReqUser);
					while($ATMdb->Get_line()) {
								$idComptEnCours=$ATMdb->Get_field('rowid');
					}
					$compteur->load($ATMdb, $idComptEnCours);
					_fiche($ATMdb, $compteur,'view');
					
				}
				break;

			case 'delete':
				
				break;
		}
	}
	elseif(isset($_REQUEST['id'])) {
		
	}
	else {
		$ATMdb->db->debug=true;
		_liste($ATMdb, $compteur);
	}

	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$compteur) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste de vos jours acquis');
	getStandartJS();
	
	$r = new TSSRenderControler($compteur);
	$sql="SELECT  r.rowid as 'ID', firstname as 'Prenom', name as 'Nom', anneeN as 'annee', r.date_cre as 'DateCre',r.acquisExerciceN as 'Congés acquis N', 
	r.acquisAncienneteN as 'Congés Ancienneté', r.acquisExerciceNM1 as 'Conges Acquis N-1', r.congesPrisNM1 as 'Conges Pris N-1',
			   r.rttPris as 'RttPris'
		FROM llx_rh_compteur as r, llx_user as c 
		WHERE r.entity=".$conf->entity." AND r.fk_user=c.rowid";
		
	
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
			'Nom'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'Prenom'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste de vos jours acquis'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun jour acquis à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'orderBy'=>$TOrder
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$compteur, $mode) {
	global $db,$user;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	echo $form->hidden('id', $compteur->getId());
	echo $form->hidden('action', 'save');
	//echo $form->hidden('fk_user', $_REQUEST['id']);
	
	
	//récupération informations utilisateur dont on modifie le compte
	$CompteurActuel=$_GET['id']?$_GET['id']:$compteur->getId();
	$sqlReqUser="SELECT fk_user FROM `llx_rh_compteur` where rowid=".$CompteurActuel;//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCompteurActuel=$ATMdb->Get_field('fk_user');
	}
	
	$sqlReqUser="SELECT * FROM `llx_user` where rowid=".$userCompteurActuel;//AND entity=".$conf->entity;
	$ATMdb->Execute($sqlReqUser);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$userCourant=new User($db);
				$userCourant->firstname=$ATMdb->Get_field('firstname');
				$userCourant->id=$ATMdb->Get_field('rowid');
				$userCourant->lastname=$ATMdb->Get_field('name');
	}
	
	
	$anneeCourante=date('Y');
	$anneePrec=$anneeCourante-1;
	//////////////////////récupération des informations des congés courants (N) de l'utilisateur courant : 
	$sqlReqUser="SELECT * FROM `llx_rh_compteur` where fk_user=". $userCourant->id." AND anneeNM1=".$anneePrec;//."AND entity=".$conf->entity;
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
	$sqlReqUser2="SELECT * FROM `llx_rh_compteur` where fk_user=". $userCourant->id." AND anneeN=".$anneeCourante;//."AND entity=".$conf->entity;
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
				$congeCourant->nombreCongesAcquisMensuel=$ATMdb->Get_field('nombreCongesAcquisMensuel');
				$Tab2[]=$congeCourant;	
	}
	
	$congeCourantTotal=$congeCourant->acquisEx+$congeCourant->acquisAnc+$congeCourant->acquisHorsPer;
	
	//////////////////////////////récupération des informations des rtt courants (année N) de l'utilisateur courant : 
	$sqlRtt="SELECT * FROM `llx_rh_compteur` where fk_user=".$userCourant->id;
	$ATMdb->Execute($sqlRtt);
	$Tab=array();
	while($ATMdb->Get_line()) {
				$rttCourant=new User($db);
				$rttCourant->id=$ATMdb->Get_field('rowid');
				$rttCourant->acquis=$ATMdb->Get_field('rttAcquisMensuelInit')+$ATMdb->Get_field('rttAcquisAnnuelCumuleInit')+$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				$rttCourant->pris=$ATMdb->Get_field('rttPris');
				$rttCourant->mensuel=$ATMdb->Get_field('rttAcquisMensuel');
				$rttCourant->annuelCumule=$ATMdb->Get_field('rttAcquisAnnuelCumule');
				$rttCourant->annuelNonCumule=$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				$rttCourant->typeAcquisition=$ATMdb->Get_field('rttTypeAcquisition');
				$rttCourant->annuelCumuleInit=$ATMdb->Get_field('rttAcquisAnnuelCumuleInit');
				$rttCourant->annuelNonCumuleInit=$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				$rttCourant->mensuelInit=$ATMdb->Get_field('rttAcquisMensuelInit');
				$rttCourant->annee=substr($ATMdb->Get_field('anneertt'),0,4);
				$rttCourant->fk_user=$ATMdb->Get_field('fk_user');
				
				$Tab[]=$rttCourant;	
	}
	
	$rttCourantReste=$rttCourant->acquis-$rttCourant->pris;
	

	//récupération des informations globales du compteur
	$sqlReq="SELECT * FROM `llx_rh_admin_compteur` where rowid=1";	
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
				$compteurGlobal=new User($db);
				$compteurGlobal->rowid=$ATMdb->Get_field('rowid');
				$compteurGlobal->congesAcquisMensuelInit=$ATMdb->Get_field('congesAcquisMensuelInit');
				$compteurGlobal->date_rttClotureInit=$ATMdb->Get_field('date_rttClotureInit');
				$compteurGlobal->date_congesClotureInit=$ATMdb->Get_field('date_congesClotureInit');
	}
	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/compteur.tpl.php'
		,array(
			
			
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',round2Virgule($congePrec->acquisEx),10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',round2Virgule($congePrec->acquisAnc),10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',round2Virgule($congePrec->acquisHorsPer),10,50,'',$class="text", $default='')
				,'reportConges'=>$form->texte('','reportCongesNM1',round2Virgule($congePrec->reportConges),10,50,'',$class="text", $default='')
				,'congesPris'=>$form->texte('','congesPrisNM1',round2Virgule($congePrec->congesPris),10,50,'',$class="text", $default='')
				,'anneePrec'=>$form->texte('','anneeNM1',round2Virgule($anneePrec),10,50,'',$class="text", $default='')
				,'total'=>round2Virgule($congePrecTotal)
				,'reste'=>round2Virgule($congePrecReste)
				,'idUser'=>$congePrec->fk_user
				,'user'=>$_REQUEST['id']?$_REQUEST['id']:$user->id
			)
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceN',round2Virgule($congeCourant->acquisEx),10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',round2Virgule($congeCourant->acquisAnc),10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',round2Virgule($congeCourant->acquisHorsPer),10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','anneeN',round2Virgule($anneeCourante),10,50,'',$class="text", $default='')
				,'total'=>round2Virgule($congeCourantTotal)
				,'idUser'=>$congeCourant->fk_user
				//,'date_congesCloture'=>$form->calendrier('', 'date_congesCloture', $compteurGlobal->date_congesClotureInit, 10)
				,'date_congesCloture'=>$compteurGlobal->date_congesClotureInit
				,'nombreCongesAcquisMensuel'=>$form->texte('','nombreCongesAcquisMensuel',round2Virgule($compteurGlobal->congesAcquisMensuelInit),10,50,'',$class="text", $default='')
				
				
				
			)
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',round2Virgule($rttCourant->acquis),10,50,'',$class="text", $default='')
				,'rowid'=>$form->texte('','rowid',round2Virgule($rttCourant->id),10,50,'',$class="text", $default='')
				,'pris'=>$form->texte('','rttPris',round2Virgule($rttCourant->pris),10,50,'',$class="text", $default='')
				,'mensuel'=>$form->texte('','rttAcquisMensuel',round2Virgule($rttCourant->mensuel),10,50,'',$class="text", $default='')
				,'annuelCumule'=>$form->texte('','rttAcquisAnnuelCumule',round2Virgule($rttCourant->annuelCumule),10,50,'',$class="text", $default='')
				,'annuelNonCumule'=>$form->texte('','rttAcquisAnnuelNonCumule',round2Virgule($rttCourant->annuelNonCumule),10,50,'',$class="text", $default='')
				,'date_rttCloture'=>$compteurGlobal->date_rttClotureInit
				,'mensuelInit'=>$form->texte('','rttAcquisMensuel',round2Virgule($rttCourant->mensuelInit),10,50,'',$class="text", $default='')
				,'annuelCumuleInit'=>$form->texte('','rttAcquisAnnuelCumule',round2Virgule($rttCourant->annuelCumuleInit),10,50,'',$class="text", $default='')
				,'annuelNonCumuleInit'=>$form->texte('','rttAcquisAnnuelNonCumule',round2Virgule($rttCourant->annuelNonCumuleInit),10,50,'',$class="text", $default='')
				//,'typeAcquisition'=>$form->texte('','typeAcquisition',$rttCourant->typeAcquisition,10,50,'',$class="text", $default='')
				,'typeAcquisition'=>$form->combo('','rttTypeAcquisition',$compteur->TTypeAcquisition,$compteur->rttTypeAcquisition)
				,'rttTypeAcquis'=>$compteur->rttTypeAcquisition
				,'reste'=>$form->texte('','total',round2Virgule($rttCourantReste),10,50,'',$class="text", $default='')
				,'id'=>$compteur->getId()
				
				

				
			)
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>$userCourant->lastname
				,'firstname'=>$userCourant->firstname
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(absencePrepareHead($compteur, 'compteur')  , 'compteur', 'Absence')
			
			
			)
			
			
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
