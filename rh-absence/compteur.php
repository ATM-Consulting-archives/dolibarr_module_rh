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
				//$ATMdb->db->debug=true;
				
				$compteur->load($ATMdb, $_REQUEST['id']);
				$compteur->reportRtt=0; // on remet à 0 la checkbox avant de setter la nouvelle valeur
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
					$sqlReqUser="SELECT rowid FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$user->id;
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
		//$ATMdb->db->debug=true;
		_liste($ATMdb, $compteur);
	}

	$ATMdb->close();
	
	llxFooter();
	
	
function _liste(&$ATMdb, &$compteur) {
	global $langs, $conf, $db, $user;	
	llxHeader('','Liste des compteurs de congés des collaborateurs');
	getStandartJS();
	print dol_get_fiche_head(compteurPrepareHead($compteur, 'compteur')  , 'compteur', 'Administration des congés');
	$r = new TSSRenderControler($compteur);
	$sql="SELECT  r.rowid as 'ID', c.firstname, c.name, anneeN as 'annee', 
		r.date_cre as 'DateCre', CAST(r.acquisExerciceN as DECIMAL(16,1)) as 'Congés acquis N', 
		CAST(r.acquisAncienneteN as DECIMAL(16,1)) as 'Congés Ancienneté', 
		CAST(r.acquisExerciceNM1 as DECIMAL(16,1)) as 'Conges Acquis N-1', 
		CAST(r.congesPrisNM1 as DECIMAL(16,1)) as 'Conges Pris N-1',
		CAST(r.rttPris as DECIMAL(16,1))  as 'RttPris'
		FROM ".MAIN_DB_PREFIX."rh_compteur as r, ".MAIN_DB_PREFIX."user as c 
		WHERE r.entity IN (0,".$conf->entity.") AND r.fk_user=c.rowid";
		
	
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
			'name'=>'<a href="?id=@ID@&action=view">@val@</a>'
			,'firstname'=>'<a href="?id=@ID@&action=view">@val@</a>'
		)
		,'translate'=>array()
		,'hide'=>array('DateCre')
		,'type'=>array()
		,'liste'=>array(
			'titre'=>'Liste des compteurs de congés des collaborateurs'
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','previous.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['socid'])
			,'messageNothing'=>"Il n'y a aucun jour acquis à afficher"
			,'order_down'=>img_picto('','1downarrow.png', '', 0)
			,'order_up'=>img_picto('','1uparrow.png', '', 0)
			
		)
		,'title'=>array(
			'name'=>'Nom'
			,'firstname'=>'Prénom'
		)
		,'orderBy'=>$TOrder
		,'eval'=>array(
			'name'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
			,'firstname'=>'htmlentities("@val@", ENT_COMPAT , "ISO8859-1")'
		)
		
	));
	
	
	llxFooter();
}	
	
function _fiche(&$ATMdb, &$compteur, $mode) {
	global $db,$user,$conf;
	llxHeader('');

	$form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
	$form->Set_typeaff($mode);
	
	echo $form->hidden('action', 'save');
	//echo $form->hidden('fk_user', $_REQUEST['id']);
	
	//compteur de l'user courant : 
	$sql="SELECT rowid FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=".$user->id;
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$compteurUserCourant=$ATMdb->Get_field('rowid');
	}
	
	
	//récupération informations utilisateur dont on modifie le compte
	$CompteurActuel=$compteurUserCourant;

	echo $form->hidden('id', $CompteurActuel);
	$sqlReqUser="SELECT fk_user FROM `".MAIN_DB_PREFIX."rh_compteur` where rowid=".$CompteurActuel." AND entity IN (0,".$conf->entity.")";
	$ATMdb->Execute($sqlReqUser);
	while($ATMdb->Get_line()) {
				$userCompteurActuel=$ATMdb->Get_field('fk_user');
	}
	
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."user` where rowid=".$userCompteurActuel." AND entity IN (0,".$conf->entity.")";
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
	$sqlReqUser="SELECT * FROM `".MAIN_DB_PREFIX."rh_compteur` where fk_user=". $userCourant->id." AND entity IN (0,".$conf->entity.")";
	
	$ATMdb->Execute($sqlReqUser);
	$congePrec=array();
	
	while($ATMdb->Get_line()) {
				
				
				$congePrec['id']=$ATMdb->Get_field('rowid');
				$congePrec['acquisEx']=$ATMdb->Get_field('acquisExerciceNM1');
				$congePrec['acquisAnc']=$ATMdb->Get_field('acquisAncienneteNM1');
				$congePrec['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeNM1');
				$congePrec['reportConges']=$ATMdb->Get_field('reportCongesNM1');
				$congePrec['congesPris']=$ATMdb->Get_field('congesPrisNM1');
				$congePrec['annee']=$ATMdb->Get_field('anneeNM1');
				$congePrec['fk_user']=$ATMdb->Get_field('fk_user');
				
				
				$congeCourant['id']=$ATMdb->Get_field('rowid');
				$congeCourant['acquisEx']=$ATMdb->Get_field('acquisExerciceN');
				$congeCourant['acquisAnc']=$ATMdb->Get_field('acquisAncienneteN');
				$congeCourant['acquisHorsPer']=$ATMdb->Get_field('acquisHorsPeriodeN');
				$congeCourant['annee']=$ATMdb->Get_field('anneeN');
				$congeCourant['fk_user']=$ATMdb->Get_field('fk_user');
				$congeCourant['nombreCongesAcquisMensuel']=$ATMdb->Get_field('nombreCongesAcquisMensuel');
				
				
				$rttCourant['id']=$ATMdb->Get_field('rowid');
				$rttCourant['typeAcquisition']=$ATMdb->Get_field('rttTypeAcquisition');
				if($rttCourant['typeAcquisition']=='Annuel'){
					$rttCourant['acquis']=$ATMdb->Get_field('rttAcquisMensuelInit')+$ATMdb->Get_field('rttAcquisAnnuelCumuleInit')+$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				}else if($rttCourant['typeAcquisition']=='Mensuel'){
					$rttCourant['acquis']=$ATMdb->Get_field('rttAcquisMensuelTotal');
				}
				$rttCourant['pris']=$ATMdb->Get_field('rttPris');
				$rttCourant['mensuel']=$ATMdb->Get_field('rttAcquisMensuel');
				$rttCourant['annuelCumule']=$ATMdb->Get_field('rttAcquisAnnuelCumule');
				$rttCourant['annuelNonCumule']=$ATMdb->Get_field('rttAcquisAnnuelNonCumule');
				
				$rttCourant['rttMetier']=$ATMdb->Get_field('rttMetier');
				$rttCourant['annuelCumuleInit']=$ATMdb->Get_field('rttAcquisAnnuelCumuleInit');
				$rttCourant['annuelNonCumuleInit']=$ATMdb->Get_field('rttAcquisAnnuelNonCumuleInit');
				$rttCourant['mensuelInit']=$ATMdb->Get_field('rttAcquisMensuelInit');
				$rttCourant['mensuelTotal']=$ATMdb->Get_field('rttAcquisMensuelTotal');
				$rttCourant['annee']=substr($ATMdb->Get_field('anneertt'),0,4);
				$rttCourant['fk_user']=$ATMdb->Get_field('fk_user');
				
				
				
	}

	$congePrecTotal=$congePrec['acquisEx']+$congePrec['acquisAnc']+	$congePrec['acquisHorsPer']+$congePrec['reportConges'];
	$congePrecReste=$congePrecTotal-$congePrec['congesPris'];
	
	$congeCourantTotal=$congeCourant['acquisEx']+$congeCourant['acquisAnc']	+$congeCourant['acquisHorsPer'];
	
	$rttCourantReste=$rttCourant['acquis']-$rttCourant['pris'];
	

	//récupération des informations globales du compteur
	$sqlReq="SELECT * FROM `".MAIN_DB_PREFIX."rh_admin_compteur`";	
	$ATMdb->Execute($sqlReq);
	while($ATMdb->Get_line()) {
				$compteurGlobal=new User($db);
				$compteurGlobal->rowid=$ATMdb->Get_field('rowid');
				$compteurGlobal->congesAcquisMensuelInit=$ATMdb->Get_field('congesAcquisMensuelInit');
				$compteurGlobal->date_rttClotureInit=$ATMdb->Get_field('date_rttClotureInit');
				$compteurGlobal->date_congesClotureInit=$ATMdb->Get_field('date_congesClotureInit');
				break;
	}
	
	
	
	$TBS=new TTemplateTBS();
	print $TBS->render('./tpl/compteur.tpl.php'
		,array(
		)
		,array(
			'congesPrec'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceNM1',round2Virgule($congePrec['acquisEx']),10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteNM1',round2Virgule($congePrec['acquisAnc']),10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeNM1',round2Virgule($congePrec['acquisHorsPer']),10,50,'',$class="text", $default='')
				,'reportConges'=>$form->texte('','reportCongesNM1',round2Virgule($congePrec['reportConges']),10,50,'',$class="text", $default='')
				,'congesPris'=>$form->texte('','congesPrisNM1',round2Virgule($congePrec['congesPris']),10,50,'',$class="text", $default='')
				,'anneePrec'=>$form->texte('','anneeNM1',round2Virgule($anneePrec),10,50,'',$class="text", $default='')
				,'total'=>round2Virgule($congePrecTotal)
				,'reste'=>round2Virgule($congePrecReste)
				,'idUser'=>$congePrec->fk_user
				,'user'=>$_REQUEST['id']?$_REQUEST['id']:$user->id
			)
			
			,'congesCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquisEx'=>$form->texte('','acquisExerciceN',round2Virgule($congeCourant['acquisEx']),10,50,'',$class="text", $default='')
				,'acquisAnc'=>$form->texte('','acquisAncienneteN',round2Virgule($congeCourant['acquisAnc']),10,50,'',$class="text", $default='')
				,'acquisHorsPer'=>$form->texte('','acquisHorsPeriodeN',round2Virgule($congeCourant['acquisHorsPer']),10,50,'',$class="text", $default='')
				,'anneeCourante'=>$form->texte('','anneeN',round2Virgule($anneeCourante),10,50,'',$class="text", $default='')
				,'total'=>round2Virgule($congeCourantTotal)
				,'idUser'=>$congeCourant->fk_user
				,'date_congesCloture'=>date("d/m/Y",strtotime($compteurGlobal->date_congesClotureInit))
				,'nombreCongesAcquisMensuel'=>$form->texte('','nombreCongesAcquisMensuel',round2Virgule($compteurGlobal->congesAcquisMensuelInit),10,50,'',$class="text", $default='')	
				
				,'titreConges'=>load_fiche_titre("Congés payés",'', 'title.png', 0, '')
				,'titreCongesNM'=>load_fiche_titre("Année N-1",'', '', 0, '')
				,'titreCongesN'=>load_fiche_titre("Année N",'', '', 0, '')
				
			)
			
			,'rttCourant'=>array(
				//texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
				'acquis'=>$form->texte('','rttAcquis',round2Virgule($rttCourant['acquis']),10,50,'',$class="text", $default='')
				,'rowid'=>$form->texte('','rowid',round2Virgule($rttCourant['id']),10,50,'',$class="text", $default='')
				,'pris'=>$form->texte('','rttPris',round2Virgule($rttCourant['pris']),10,50,'',$class="text", $default='')
				,'mensuel'=>$form->texte('','rttAcquisMensuel',round2Virgule($rttCourant['mensuel']),10,50,'',$class="text", $default='')
				,'annuelCumule'=>$form->texte('','rttAcquisAnnuelCumule',round2Virgule($rttCourant['annuelCumule']),10,50,'',$class="text", $default='')
				,'annuelNonCumule'=>$form->texte('','rttAcquisAnnuelNonCumule',round2Virgule($rttCourant['annuelNonCumule']),10,50,'',$class="text", $default='')
				,'date_rttCloture'=>date("d/m/Y",strtotime($compteurGlobal->date_rttClotureInit))
				,'mensuelInit'=>$form->texte('','rttAcquisMensuelInit',round2Virgule($rttCourant['mensuelInit']),10,50,'',$class="text", $default='')
				,'mensuelTotal'=>$form->texte('','rttAcquisMensuelTotal',round2Virgule($rttCourant['mensuelTotal']),10,50,'',$class="text", $default='')
				,'annuelCumuleInit'=>$form->texte('','rttAcquisAnnuelCumuleInit',round2Virgule($rttCourant['annuelCumuleInit']),10,50,'',$class="text", $default='')
				,'annuelNonCumuleInit'=>$form->texte('','rttAcquisAnnuelNonCumuleInit',round2Virgule($rttCourant['annuelNonCumuleInit']),10,50,'',$class="text", $default='')
				,'typeAcquisition'=>$form->combo('','rttTypeAcquisition',$compteur->TTypeAcquisition,$compteur->rttTypeAcquisition)
				,'rttMetier'=>$form->combo('','rttMetier',$compteur->TMetier,$rttCourant['rttMetier'])
				,'rttTypeAcquis'=>$compteur->rttTypeAcquisition
				,'reste'=>$form->texte('','total',round2Virgule($rttCourantReste),10,50,'',$class="text", $default='')
				,'id'=>$compteur->getId()
				,'reportRtt'=>$form->checkbox1('','reportRtt','1',$compteur->reportRtt)
				
				,'titreRtt'=>load_fiche_titre("RTT",'', 'title.png', 0, '')
				,'titreRttCompteur'=>load_fiche_titre("Compteur de RTT",'', '', 0, '')
				,'titreRttMethode'=>load_fiche_titre("Méthode d'acquisition des jours",'', '', 0, '')

			)
			
			,'userCourant'=>array(
				'id'=>$userCourant->id
				,'lastname'=>htmlentities($userCourant->lastname, ENT_COMPAT , 'ISO8859-1')
				,'firstname'=>htmlentities($userCourant->firstname, ENT_COMPAT , 'ISO8859-1')
				,'modifierCompteur'=>$user->rights->absence->myactions->modifierCompteur
			)
			
			,'view'=>array(
				'mode'=>$mode
				,'head'=>dol_get_fiche_head(compteurPrepareHead($compteur, 'compteur')  , 'compteur', 'Absence')
			)
		)	
		
	);
	
	echo $form->end_form();
	// End of page
	
	global $mesg, $error;
	dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
	llxFooter();
}


	
	
