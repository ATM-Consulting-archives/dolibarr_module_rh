<?php

dol_include_once('/jouroff/class/jouroff.class.php');

class TRH_absenceDay {
	
	var $date = '';
	var $isPresence = 0;
	var $label = '';
	var $AM=false;
	var $PM=false;
	var $DAM=false;
	var $DPM=false;
	var $FPM=false;
	var $FAM=false;
	var $colorId = 0;
	var $description='';
	
	function __construct() {
	
	}
	
	function __toString() {
		$r = '';
		if(!empty($this->label)) {
			
			if($this->isPresence)$r.='[Présence] ';
			
			$r.=$this->label;
			
			if($this->DAM) {
				$r.=' : DAM';
			}
			else if($this->FAM) {
				$r.=' : FAM';
			}
			else if($this->DPM) {
				$r.=' : DPM';
			}
			else if($this->FPM) {
				$r.=' : FPM';
			}
			else if($this->PM) {
				$r.=' : PM';
			}
			else if($this->AM) {
				$r.=' : AM';
			}
			
			return $r;				
		}
		else{
			return 'non';	
		}
		
		
		
		
		
	}
	
}

//TRH_CONGE
//classe pour la définition d'une absence 
class TRH_Compteur extends TObjetStd {
	function __construct() { /* declaration */
		global $langs;
		
		//conges N
		parent::set_table(MAIN_DB_PREFIX.'rh_compteur');
		parent::add_champs('acquisExerciceN,acquisRecuperation','type=float;');				
		parent::add_champs('acquisAncienneteN','type=float;');				
		parent::add_champs('acquisHorsPeriodeN','type=float;');											
		parent::add_champs('anneeN','type=entier;');					
		parent::add_champs('dureeN','type=entier;');
		parent::add_champs('date_congesCloture','type=date;');	//date de clôture période rtt
		parent::add_champs('nombreCongesAcquisMensuel,nombrecongesAcquisAnnuel','type=float;');
		
		//conges N-1
		parent::add_champs('acquisExerciceNM1','type=float;');				
		parent::add_champs('acquisAncienneteNM1','type=float;');				
		parent::add_champs('acquisHorsPeriodeNM1','type=float;');				
		parent::add_champs('reportCongesNM1','type=float;');				
		parent::add_champs('congesPrisNM1','type=float;');			
		parent::add_champs('congesPrisN','type=float;');			
		parent::add_champs('congesTotalNM1','type=float;');	
		parent::add_champs('congesResteNM1','type=float;');
		parent::add_champs('anneeNM1','type=entier;');					
		parent::add_champs('dureeNM1','type=entier;');				
		
		//RTT cumulés 
		parent::add_champs('rttCumulePris','type=float;');
		parent::add_champs('rttAcquisAnnuelCumuleInit,rttAcquisAnnuelNonCumuleInit','type=float;');
		parent::add_champs('rttCumuleReportNM1','type=float;');
		parent::add_champs('rttCumuleTotal','type=float;');
		parent::add_champs('rttCumuleAcquis','type=float;');
		
		
		
		//RTT non cumulés 
		parent::add_champs('rttNonCumulePris','type=float;');				
		parent::add_champs('rttAcquisAnnuelNonCumuleInit','type=float;');
		parent::add_champs('rttNonCumuleReportNM1','type=float;');
		parent::add_champs('rttNonCumuleTotal','type=float;');
		parent::add_champs('rttNonCumuleAcquis','type=float;');
		
		
		//RTT mensuels
		parent::add_champs('rttAcquisMensuelInit','type=float;');	
		

		
		parent::add_champs('rttTypeAcquisition','type=chaine;');				//annuel, mensuel...
		parent::add_champs('fk_user','type=entier;index;');			//utilisateur concerné
		parent::add_champs('rttannee','type=entier;');	
		parent::add_champs('rttMetier','type=chaine;');		
		parent::add_champs('date_rttCloture','type=date;');	//date de clôture période rtt
		
		parent::add_champs('reportRtt','type=entier;');	//entier (0 ou 1) pour savoir si l'on reporte les RTT d'une année à l'autre
		

		parent::add_champs('entity','type=entier;');					
	
		
		parent::_init_vars();
		parent::start();
		
		$this->TTypeAcquisition = array('Annuel'=> $langs->trans('TypeAcquisitionYearly'), 'Mensuel'=> $langs->trans('TypeAcquisitionMonthly'));
		$this->TDureeAbsenceUser = array();
		$this->TDureeAllAbsenceUser = array();
		
		
	}
	
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
		TRH_CompteurLog::log($db, $this->getId(), 'compteur', $this->acquisExerciceN, 'sauvegarde compteur ');
	}
	
	function initCompteur(&$PDOdb, $idUser){
		global $conf;
		$this->entity = $conf->entity;
		$annee=date('Y');
		$anneePrec=$annee-1;

		$this->fk_user=$idUser;
		$this->acquisExerciceN=0; 
		$this->acquisAncienneteN=0;
		$this->acquisHorsPeriodeN=0;
		$this->anneeN=$annee;
		$this->acquisExerciceNM1=0;
		$this->acquisAncienneteNM1=0;
		$this->acquisHorsPeriodeNM1=0;
		$this->reportCongesNM1=0;
		$this->congesPrisNM1=0;
		$this->congesPrisN=0;
		$this->anneeNM1=$anneePrec;
		$this->rttTypeAcquisition='Annuel';
		
		$this->rttAcquisMensuelInit=$conf->global->RH_NB_RTT_ANNUEL;
		$this->rttAcquisAnnuelNonCumuleInit=$conf->global->RH_NB_RTTNC_ANNUEL;
		
		
		$this->rttCumuleAcquis=0;
		$this->rttAcquisAnnuelCumuleInit=0;
		$this->rttCumuleReportNM1=0;
		$this->rttCumulePris=0;
		$this->rttCumuleTotal=$this->rttCumuleAcquis+$this->rttCumuleReportNM1-$this->rttCumulePris;
		
		$this->rttNonCumuleAcquis=0;
		$this->rttNonCumuleReportNM1=0;
		
		
		$this->rttNonCumulePris=0;
		$this->rttNonCumuleTotal=$this->rttNonCumuleAcquis+$this->rttNonCumuleReportNM1-$this->rttNonCumulePris;
		
		
		$this->rttMetier='none';
		$this->rttannee=$annee;
		$this->nombreCongesAcquisMensuel=$conf->global->RH_NB_CONGES_MOIS;
		$this->nombrecongesAcquisAnnuel=$conf->global->RH_NB_CONGES_ANNUEL;
		$this->date_rttCloture=strtotime($conf->global->RH_DATE_RTT_CLOTURE); 
		$this->date_congesCloture=strtotime($conf->global->RH_DATE_CONGES_CLOTURE);
		$this->reportRtt=0;
		
		$this->is_archive=0;
	}
	

	//	fonction permettant le chargement du compteur pour un utilisateur si celui-ci existe	
	function load_by_fkuser(&$PDOdb, $fk_user){

		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_compteur 
		WHERE fk_user=".(int)$fk_user;

		$PDOdb->Execute($sql);
		if ($obj = $PDOdb->Get_line()) {
			return $this->load($PDOdb, $obj->rowid);
			
		}
		return false;
	}
	
	
	function add(&$PDOdb, $type, $duree, $motif) {
		
		if($type=="rttcumule"){
			$this->rttCumulePris += $duree;
			$this->rttCumuleTotal -= $duree; 
			
			$this->save($PDOdb);
			
			TRH_CompteurLog::log($PDOdb, $this->getId(), $type, $duree, $motif);
			
		}
		else if($type=='rttnoncumule') {
			$this->rttNonCumulePris += $duree;
			$this->rttNonCumuleTotal -= $duree; 
			
			$this->save($PDOdb);
			
			TRH_CompteurLog::log($PDOdb, $this->getId(), $type, $duree, $motif);
		}
		else if($type=='recup') {
			$this->acquisRecuperation -= $duree;
			$this->save($PDOdb);
			
			TRH_CompteurLog::log($PDOdb, $this->getId(), $type, $duree, $motif);
		}
		else if($type=="conges"||$type=="cppartiel"){	//autre que RTT : décompte les congés
					
			list($congesPrisNM1, $congesPrisN) = $duree;		
					
			$this->congesPrisNM1 += $congesPrisNM1;
			$this->congesPrisN += $congesPrisN; 
			
			$this->save($PDOdb);
			
			TRH_CompteurLog::log($PDOdb, $this->getId(), $type, $congesPrisNM1, $motif . 'NM1');
			TRH_CompteurLog::log($PDOdb, $this->getId(), $type, $congesPrisN, $motif . 'N');
			
		
		}
		
	}
	
}


class TRH_CompteurLog extends TObjetStd {
	function __construct() { /* declaration */
		global $langs;
		
		//conges N
		parent::set_table(MAIN_DB_PREFIX.'rh_compteur_log');
		parent::add_champs('fk_compteur','type=entier;index;');			//utilisateur concerné
		parent::add_champs('nb','type=float;');	
		parent::add_champs('type','type=chaine;index;');		
		parent::add_champs('motif','type=chaine;');		
	
		
		parent::_init_vars();
		parent::start();
		
	}
	
	static function log(&$PDOdb, $fk_compteur,$type, $nb, $motif = '') {
		
		if($nb!=0) {
			$l=new TRH_CompteurLog;
			
			$l->fk_compteur = $fk_compteur;
			$l->type = $type;
			$l->nb=$nb;
			$l->motif = $motif;
			
			$l->save($PDOdb);
			
			
		}
		
	}
	
}



//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		global $user,$conf, $langs;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('code','type=varchar;index;');				//code  congé
		parent::add_champs('type','type=varchar;index;');				//type de congé
		parent::add_champs('libelle','type=varchar;');				//type de congé
		parent::add_champs('date_debut,date_fin,date_validation','type=date;index;');	//dates debut fin de congés
		parent::add_champs('date_hourStart,date_hourEnd','type=date;');	//dates debut fin de congés
		parent::add_champs('ddMoment, dfMoment','type=chaine;');		//moment (matin ou après midi)
		parent::add_champs('duree,congesPrisNM1,congesPrisN','type=float;');	
		parent::add_champs('dureeHeure','type=chaine;');	
		parent::add_champs('dureeHeurePaie','type=chaine;');
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('commentaireValideur','type=chaine;');		//commentaire
		parent::add_champs('etat','type=chaine;index;');			//état (à valider, validé...)
		parent::add_champs('avertissement','type=entier;');	
		parent::add_champs('libelleEtat,avertissementInfo','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('niveauValidation','type=entier;');	//niveau de validation
		parent::add_champs('idAbsImport','type=entier;index;');	//niveau de validation
		parent::add_champs('fk_user, fk_user_valideur','type=entier;index;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');	
		
		parent::_init_vars();
		parent::start();
		
		$this->TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		$this->Tjoursem = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'); 
		
		//combo pour le choix de matin ou après midi 
		$this->TddMoment = array('matin'=> $langs->trans('AbsenceMorning'),'apresmidi'=> $langs->trans('AbsenceAfternoon'));	//moment de date début
		$this->TdfMoment = array('matin'=> $langs->trans('AbsenceMorning'),'apresmidi'=> $langs->trans('AbsenceAfternoon'));	//moment de date fin
		
		$this->ddMoment = 'matin';
        $this->dfMoment = 'apresmidi';
		
		//on crée un tableau des utilisateurs pour l'afficher en combo box, et ensuite sélectionner quelles absences afficher
		
		$this->TEtat=array(
			'Validee'=> $langs->trans('Accepted')
			,'Refusee'=> $langs->trans('Refused')
			,'Avalider'=> $langs->trans('WaitingValidation')
		
		);
		
		$this->date_validation=0;
		
		$this->congesPrisNM1 = 0; // lors du comptage d'une absence pour alimenter le compteur
		$this->congesPrisN = 0; // lors du comptage d'une absence pour alimenter le compteur
		
		$this->TTypeAbsenceAdmin=$this->TTypeAbsenceUser=$this->TTypeAbsencePointeur=array(); //cf. loadTypeAbsencePerTypeUser
		
		
	}

	function delete(&$PDOdb)
	{
		TRH_valideur_object::deleteChildren($PDOdb, 'ABS', $this->getId());
		return parent::delete($PDOdb);
	}

	//renvoie le tableau des utilisateurs
	function recupererTUser(&$PDOdb){
		global $conf, $langs;
		$TUser=array();
		$TUser[0] = $langs->trans('AllThis');	
		$sqlReqUser="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user` 
						ORDER BY lastname";
		$PDOdb->Execute($sqlReqUser);

		while($PDOdb->Get_line()) {
			$TUser[$PDOdb->Get_field('rowid')]=htmlentities($PDOdb->Get_field('lastname'), ENT_COMPAT , 'UTF-8')." ".htmlentities($PDOdb->Get_field('firstname'), ENT_COMPAT , 'UTF-8');
		}
		return $TUser;
	}

	function valid(&$PDOdb)
	{
		global $user,$conf,$langs;
		
		$sqlEtat="UPDATE `".MAIN_DB_PREFIX."rh_absence` 
				SET etat='Validee', libelleEtat='" . $langs->trans('Accepted') . "', date_validation='".date('Y-m-d')."', fk_user_valideur=".$user->id." 
				WHERE fk_user=".$this->fk_user. " 
				AND rowid=".$this->getId();
		
		//Valideur fort
		if (TRH_valideur_groupe::isStrong($PDOdb, $user->id, 'Conges', $conf->entity))
		{
			$TRH_valideur_object = TRH_valideur_object::addLink($PDOdb, $conf->entity, $user->id, $this->getId(), 'ABS');
			
			//Validation final
			$PDOdb->Execute($sqlEtat);
		}
		//Valideur faible
		else
		{	
			if (!TRH_valideur_object::alreadyAcceptedByThisUser($PDOdb, $conf->entity, $user->id, $this->getId(), 'ABS'))
			{
				$TRH_valideur_object = TRH_valideur_object::addLink($PDOdb, $conf->entity, $user->id, $this->getId(), 'ABS');
				
				//check si tous le monde a validé
				if (TRH_valideur_object::checkAllAccepted($PDOdb, $user, 'ABS', $this->getId(), $this))
				{
					//Validation final
					$PDOdb->Execute($sqlEtat);
				}
			}
		}
		
	}

	//permet la récupération des règles liées à un utilisateur 
	//utile lors de l'affichage à la création d'une demande d'absence
	function recuperationRegleUser(&$PDOdb, $fk_user){
		global $conf;
		
		
		$sql="SELECT DISTINCT r.rowid,r.typeAbsence, r.`nbJourCumulable`, r. `restrictif`, 
		r.fk_user, r.fk_usergroup, r.choixApplication, r.periode, r.contigue,r.contigueNoJNT
		FROM ".MAIN_DB_PREFIX."rh_absence_regle as r 
			LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (r.fk_usergroup=g.fk_usergroup)
		WHERE r.choixApplication LIKE 'user' AND r.fk_user=".$fk_user."
		OR (r.choixApplication LIKE 'all')
		OR (r.choixApplication LIKE 'group' AND g.fk_user=".$fk_user.") 
		AND r.entity IN (".getEntity().")
		ORDER BY r.nbJourCumulable";

		$PDOdb->Execute($sql);
		$TRegle = array();
		$k=0;
		while($PDOdb->Get_line()) {
			$TRegle[$k]['typeAbsence']= $PDOdb->Get_field('typeAbsence');
			$TRegle[$k]['libelle']= saveLibelle($PDOdb->Get_field('typeAbsence'));
			$TRegle[$k]['nbJourCumulable']= $PDOdb->Get_field('nbJourCumulable');
			$TRegle[$k]['restrictif']= $PDOdb->Get_field('restrictif');
			$TRegle[$k]['fk_user']= $PDOdb->Get_field('fk_user');
			$TRegle[$k]['fk_usergroup']= $PDOdb->Get_field('fk_usergroup');
			$TRegle[$k]['choixApplication']= $PDOdb->Get_field('choixApplication');
			$TRegle[$k]['periode']= $PDOdb->Get_field('periode');
			$TRegle[$k]['id']= $PDOdb->Get_field('rowid');
			$TRegle[$k]['contigue']= $PDOdb->Get_field('contigue');
			$TRegle[$k]['contigueNoJNT']= $PDOdb->Get_field('contigueNoJNT');
			
			
			$k++;
		}

		return $TRegle;

	}
	
	
	
	//permet la récupération des règles liées à un utilisateur 
	//utile lors de l'affichage à la création d'une demande d'absence
	function recuperationDerAbsUser(&$PDOdb, $fk_user){
		global $conf;
		$sql="SELECT DATE_FORMAT(date_debut, '%d/%m/%Y') as 'dateD', 
		DATE_FORMAT(date_fin, '%d/%m/%Y')  as 'dateF', libelle, libelleEtat 
		FROM `".MAIN_DB_PREFIX."rh_absence` WHERE fk_user=".$fk_user." AND entity IN (".getEntity().")
		GROUP BY date_cre LIMIT 0,10";

		$PDOdb->Execute($sql);
		$TRecap=array();
		$k=0;
		while($PDOdb->Get_line()) {		
			$TRecap[$k]['date_debut']=$PDOdb->Get_field('dateD');
			$TRecap[$k]['date_fin']=$PDOdb->Get_field('dateF');
			$TRecap[$k]['libelle']=$PDOdb->Get_field('libelle');
			$TRecap[$k]['libelleEtat']=$PDOdb->Get_field('libelleEtat');
			$k++;
		}
		return $TRecap;
	}
	

	function testDemande(&$PDOdb, $userConcerne, &$absence){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		//on calcule la duree de l'absence, en décomptant jours fériés et jours non travaillés par le collaborateur

		$compteur =new TRH_Compteur;
		$compteur->load_by_fkuser($PDOdb, $userConcerne);
		
		$dureeAbsenceCourante = $this->calculDureeAbsenceParAddition($PDOdb, $compteur->date_congesCloture);
		
		$conges_nm1_restants = $compteur->acquisExerciceNM1+$compteur->acquisAncienneteNM1+$compteur->acquisHorsPeriodeNM1+$compteur->reportCongesNM1-$compteur->congesPrisNM1;
		
		//autres paramètes à sauvegarder
		$this->duree=$dureeAbsenceCourante;
		$this->etat="Avalider";
		$this->libelleEtat=saveLibelleEtat($this->etat);
		
		//on teste s'il y a des règles qui s'appliquent à cette demande d'absence
		//$this->findRegleUser($db);
		$dureeAbsenceRecevable=$this->dureeAbsenceRecevable($PDOdb);
		
	
		if($dureeAbsenceRecevable==0 || ($conf->global->ABSENCE_GREATER_THAN_CONGES_RESTANTS_FORBIDDEN && $dureeAbsenceCourante > $conges_nm1_restants)){
			return 0;
		}
		
		///////décompte des congés
		if($this->type=="rttcumule"){
			$compteur->add($PDOdb, $this->type, $dureeAbsenceCourante, 'Prise de RTT');
			
		}
		else if($this->type=="rttnoncumule"){
			
			$compteur->add($PDOdb, $this->type, $dureeAbsenceCourante, 'Prise de RTT non cumulé');
			
		}
		else if($this->type=="recup"){
			
			$compteur->add($PDOdb, $this->type, $dureeAbsenceCourante, 'Prise de jour de récupération');
			
		}
		else if($this->type=="conges"||$this->type=="cppartiel"){	//autre que RTT : décompte les congés
			$compteur->add($PDOdb, $this->type, array($this->congesPrisNM1,  $this->congesPrisN), 'Prise de congé');
			
			$this->congesResteNM1=$this->congesResteNM1-$dureeAbsenceCourante;
			
		}
		
		return $dureeAbsenceRecevable;
	}
		
	function setRefusee(&$PDOdb) {
		global $langs;
		
		$this->recrediterHeure($PDOdb);
		$this->etat='Refusee';
		$this->libelleEtat = $langs->trans('DeniedRequest');
		$this->save($PDOdb);
		mailConges($this,$isPresence);
	}
	
	function setAcceptee(&$PDOdb, $fk_valideur,$isPresence=false) {
		global $langs,$user,$conf;	
		
		
		$this->etat='Validee';
		$this->libelleEtat = $langs->trans('Accepted');
		$this->date_validation=time();
		$this->fk_user_valideur = $fk_valideur;
		
		
		// Appel des triggers
		dol_include_once('/core/class/interfaces.class.php');
		$interface = new Interfaces($db);
		
		$result = $interface->run_triggers('ABSENCE_BEFOREVALIDATE',$this,$user,$langs,$conf);
		
		if ($result < 0) {
			$error++; $this->errors=$interface->errors;
			return false; 
		}
		else { 
			$this->save($PDOdb);
			mailConges($this, $isPresence);	
			
			return true;
		}
			
		
	}	
		
		
	function save(&$PDOdb, $runTrigger = true) {

		global $conf, $user,$db,$langs;
		$this->entity = $conf->entity;
		
		if(empty($this->code) || empty($this->libelle)) {
			
			$ta = new TRH_TypeAbsence;
			$ta->load_by_type($PDOdb, $this->type);
		
			$this->code=$ta->codeAbsence;
			$this->libelle=$ta->libelleAbsence;
			
		}
		
		// Appel des triggers
		dol_include_once('/core/class/interfaces.class.php');
		$interface = new Interfaces($db);
		
		if($this->getId()>0) {
			$result = $interface->run_triggers('ABSENCE_BEFOREUPDATE',$this,$user,$langs,$conf);
			$f_mode = 'UPDATE';
		}
		else{
			$result = $interface->run_triggers('ABSENCE_BEFORECREATE',$this,$user,$langs,$conf);	
			$f_mode = 'CREATE';
		}
		
		if ($result < 0) {
			$error++; $this->errors=$interface->errors;
			return false; 
		}
		// Fin appel triggers
		else {
			parent::save($PDOdb);

			if($runTrigger) $result = $interface->run_triggers('ABSENCE_'.$f_mode,$this,$user,$langs,$conf);	

			return true;	
		}	
		
		
	}

	/*
	 * Récupère la liste des jours fériés sur la période d'absence
	 */
	 
	function getJourFerie(&$PDOdb) {
		
		$PDOdb->Execute("SELECT date_jourOff, moment FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries 
		WHERE date_jourOff BETWEEN '".date('Y-m-d 00:00:00', $this->date_debut)."' AND '".date('Y-m-d 23:59:59', $this->date_fin)."'");
		
		$Tab=array();
		while($PDOdb->Get_line()) {
		
			$moment = $PDOdb->Get_field('moment');
			$date_jourOff = date('Y-m-d', strtotime($PDOdb->Get_field('date_jourOff')));
			
			if($moment=='matin') {
				$Tab[$date_jourOff]['am']=true;	
			}
			elseif($moment=='apremidi') {
				$Tab[$date_jourOff]['pm']=true;	
			}
			else{
				$Tab[$date_jourOff]['am']=true;	
				$Tab[$date_jourOff]['pm']=true;	
			}
			
		}
		return $Tab;
	}
	
	/**
	 * Fonction qui calcule en interne du la base de la durée de nombre de jour contigue d'une absence (jours avant, pendant, après férié ou non travaillé)
	 * 
	 */
	 private function calculDureeAddContigue(&$PDOdb) {
		
		 $loop=true;$cpt=0;
		 $date = $this->date_debut;
		 while($loop && $cpt<50) {
		 	list($isWorkingDay, $isNotFerie, $isNotAbsence) = $this->isWorkingDayPrevious($PDOdb, $date);
			
			if(!$isWorkingDay && $isNotFerie && $isNotAbsence) {
				$this->dureeContigue++;
			}
			else if(!$isWorkingDay || !$isNotFerie || !$isNotAbsence) {
				$this->dureeContigue++;
				$this->dureeContigueWhitoutJNT++; // compte uniquement les jours d'absence et férié, pas les jour non travaillé
			}
			else{
				$loop = false;
			}

			$date = strtotime('-1day', $date);
			$cpt++;
			
		 }
		 
		 $loop=true;$cpt=0;
		 $date = $this->date_fin;
		 while($loop && $cpt<50) {
		 	list($isWorkingDay, $isNotFerie, $isNotAbsence)=$this->isWorkingDayNext($PDOdb, $date);

			if(!$isWorkingDay && $isNotFerie && $isNotAbsence) {
				$this->dureeContigue++;
			}
			else if(!$isWorkingDay || !$isNotFerie || !$isNotAbsence) {
				$this->dureeContigue++;
				$this->dureeContigueWhitoutJNT++;
			}
			else{
				$loop = false;
			}

			$date = strtotime('+1day', $date);	
			$cpt++;
			
		 }
	
	}
	
	function calculDureeAbsenceParAddition(&$PDOdb, $dateN=0) {
		global $TJourNonTravailleEntreprise, $langs;
		
		$TJourSemaine = array('dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi');
		$TJourFerie = $this->getJourFerie($PDOdb);	
		
		$duree = 0;
		
		$this->dureeHeure=0;
		$this->dureeContigue=0;
		$this->dureeContigueWhitoutJNT = 0;
		
		$t_start = $this->date_debut;
		$t_end = $this->date_fin;
		$t_current = $t_start;
		
		$typeAbs = new TRH_TypeAbsence;
		
		$typeAbs->load_by_type($PDOdb, $this->type);
		
		//print_r($typeAbs);
		$emploiTemps = new TRH_EmploiTemps;
		$emploiTemps->load_by_fkuser($PDOdb, $this->fk_user);
				
		while($t_current<=$t_end) {
			//print date('Y-m-d', $t_current).'<br>';;
			$current_day = $TJourSemaine[(int)date('w', $t_current)];
			if(!@in_array($current_day, $TJourNonTravailleEntreprise)) {
								
				$dureeJour=0;

				if($emploiTemps->estJourTempsPartiel($current_day) && empty($TJourFerie[date('Y-m-d', $t_current)])) {
					$dureeJour += 1;
					$this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"am");
	                $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"pm");
				}
				else {
					
	                if($typeAbs->insecable == 1) {
	                        // absence de type insécable, on compte à la journée complète
	                        
	                        if(!isset($TJourFerie[ date('Y-m-d', $t_current) ]['am']) 
	                            && !isset($TJourFerie[ date('Y-m-d', $t_current) ]['pm']) // ce n'est pas un jour non travaillé complet
	                            && ($emploiTemps->{$current_day.'am'} == 1 || $emploiTemps->{$current_day.'pm'} == 1) // et qu'on travail au moins une demie-journée
	                        ) {
	                            $dureeJour+=1; // je compte la journée entière car insécable
	                            $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"am");
	                            $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"pm");
	                        }
	                        
	                }
	                else {
	                    if( ($t_current==$t_start && $this->ddMoment=='matin') || $t_current>$t_start  ) {
	                        // si l'absence démarre aujorud'hui et qu'elle commence le matin ou bien qu'elle a déjà commencée, je test le matin
	                        
	                        
	                        if(!isset($TJourFerie[ date('Y-m-d', $t_current) ]['am'])) {
	        
	                            if($emploiTemps->{$current_day.'am'} == 1 ) {
	                                $dureeJour+=.5;
	                                $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"am");
	                            }       
	                            else if($typeAbs->decompteNormal=='non' && $emploiTemps->{$current_day.'am'}==0 ) {
	                                $dureeJour+=.5;
	                                $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"am");
	                            }
	                            
	                        }
	                    }
	                    
	                    if(($t_current==$t_end && $this->dfMoment=='apresmidi') || $t_current<$t_end  ) {
	                    // si l'absence se termine aujourd'hui et cet après midi ou bien que l'absence se termine dans le futur, alors je test l'après-midi
	                    
	                        if(!isset($TJourFerie[ date('Y-m-d', $t_current) ]['pm'])) {
	        
	                            if($emploiTemps->{$current_day.'pm'}==1 ) {
	                                $dureeJour+=.5;
	                                $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"pm");
	                            }       
	                            else if($typeAbs->decompteNormal=='non' && $emploiTemps->{$current_day.'pm'}==0 ) {
	                                $dureeJour+=.5;
	                                $this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"pm");
	                            }
	                            
	                        }
	                    
	                    }
	                        
	                }
                
                }
				
				if(!empty($dateN)) {
					// distrib sur conges N ou N+1

					if($t_current<=$dateN) $this->congesPrisNM1+=$dureeJour;
					else $this->congesPrisN+=$dureeJour;
					
				}
				
				$duree+=$dureeJour;
				$this->dureeContigueWhitoutJNT+=$dureeJour;
				
				$this->TDureeAbsenceUser[date('Y', $t_current)][date('m', $t_current)] += $dureeJour;
			
			}
			
			$this->dureeContigue++;
			
			
			$t_current = strtotime('+1day',$t_current);
		}
		
		$this->duree = $duree; // Attention, je rajoute ça ici car semble normal, vérif pas effets de bord
		
		if($emploiTemps->tempsHebdo > 35){
			$this->dureeHeurePaie=7*$duree;
		}
		else{
			$this->dureeHeurePaie=$this->dureeHeure;
		} 
		
		$this->calculDureeAddContigue($PDOdb);
		
		return $duree;
	}
	
	function calculDureePresence(&$PDOdb) {
		$typeAbs = new TRH_TypeAbsence;
		
		$typeAbs->load_by_type($PDOdb, $this->type);
		
		$duree = 0;
		
		$this->dureeHeure=0;
		$this->dureeContigue=0;
		$this->dureeContigueWhitoutJNT = 0;
		
		$dateStart = date('Y-m-d', $this->date_debut);
		$dateEnd = date('Y-m-d', $this->date_fin);
		
		$datetimeStart = new DateTime($dateStart);
		$datetimeEnd = new DateTime($dateEnd);
		$interval = $datetimeStart->diff($datetimeEnd);
		$days = $interval->days;
		
		// Retourne le nombre de semaine
		$weeks = floor($days / 7);
		
		// Retourne le nombre de jour avant la fin de la première semaine
		$remaining = fmod($days, 7);
		
		$firstDayOfWeek = date('N', $dateStart);
		$lastDayOfWeek = date('N', $dateEnd);
		
		if ($firstDayOfWeek <= $lastDayOfWeek) {
			if ($firstDayOfWeek <= 6 && 6 <= $lastDayOfWeek) $remaining--;
			if ($firstDayOfWeek <= 7 && 7 <= $lastDayOfWeek) $remaining--;
		} else {
			if ($firstDayOfWeek == 7) {
				$remaining--;
				
				if ($lastDayOfWeek == 6) {
					$remaining--;
				}
			} else {
				$remaining -= 2;
			}
		}
		
		$workingDays = $weeks * 5;
		if ($remaining > 0) {
			$workingDays += $remaining;
		}
		
		$hourStart = date('H:i', $this->date_hourStart);
		$hourEnd = date('H:i', $this->date_hourEnd);
		
		// Calcul du nombre d'heures de présence
		$hours = difheure($hourStart, $hourEnd);
		$hours = horaireMinuteEnCentieme($hours);
		
		$this->dureeHeure = $hours;
		
		return $workingDays;
	}
	
	//TODO Delete, version dépréciée est buguée
	//calcul de la durée initiale de l'absence (sans jours fériés, sans les jours travaillés du salariés)
	function calculDureeAbsence(&$PDOdb, $date_debut, $date_fin, &$absence){
		$diff=$date_fin-$date_debut;
		$duree=intval($diff/3600/24);
		//echo $duree;exit;
		//prise en compte du matin et après midi
		
		if($absence->ddMoment=="matin"&&$absence->dfMoment=="apresmidi"){
			
			$duree+=1;
		}
		else if($absence->ddMoment==$absence->dfMoment){
			
			$duree+=0.5;
		}
		$this->date_debut = $date_debut;
		$this->date_fin = $date_fin;
		
		return $this->calculDureeAbsenceParAddition($PDOdb);
	}
	
	//TODO Delete, version dépréciée est buguée
	//calcul la durée de l'absence après le décompte des jours fériés
	function calculJoursFeries(&$PDOdb, $duree, $date_debut, $date_fin, &$absence){
			
		global $conf, $TJourNonTravailleEntreprise;		
		//on cherche s'il existe un ou plusieurs jours fériés  entre la date de début et de fin d'absence
		$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries`";
		$PDOdb->Execute($sql);
		$Tab = array();
		while($PDOdb->Get_line()) {
			$Tab[date('Y-m-d', strtotime($PDOdb->Get_field('date_jourOff')))]= array(
				'rowid'=>$PDOdb->Get_field('rowid')
				,'moment'=>$PDOdb->Get_field('moment')
				);
		}
		
		
		/*echo '<pre>';
		print_r($Tab);
		echo '</pre>';*/	
		$t_current = $t_start = $date_debut;
		$t_end = $date_fin;
		while($t_current<=$t_end) {
			
			$date_current = date('Y-m-d', $t_current);
			$jour = $absence->jourSemaine($t_current);
			
			if(in_array($jour, $TJourNonTravailleEntreprise))
				$duree -= 0.5;
		//	print " $date_current ";
			elseif(isset($Tab[$date_current])) {
		//		print "$date_current est férié";
				if($t_current==$t_start && $absence->ddMoment=='apresmidi') {
					if($Tab[$date_current]['moment']=='matin') {
						null;
					}	
					else {
						$duree-=0.5;	
					}
					 
				}	
				else if($t_current==$t_end && $absence->dfMoment=='matin') {
					if($Tab[$date_current]['moment']=='apresmidi') {
						null;
					}	
					else {
						$duree-=0.5;	
					}
				}
				else {
					if($Tab[$date_current]['moment']=='allday') {
						$duree-=1;
					}
					else {
						$duree-=0.5;
					}
					
					
				}
				
			}
			
			
			$t_current = strtotime("+1 day", $t_current);
		}
		
		
		return $duree;
		/*
		$dateDebutAbs=$absence->php2Date($date_debut);
		$dateFinAbs=$absence->php2Date($date_fin);
		
		//on cherche s'il existe un ou plusieurs jours fériés  entre la date de début et de fin d'absence
		$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries`";
		$PDOdb->Execute($sql);
		$Tab = array();
		while($PDOdb->Get_line()) {
			$Tab[$PDOdb->Get_field('rowid')]= array(
				'date_jourOff'=>$PDOdb->Get_field('date_jourOff')
				,'moment'=>$PDOdb->Get_field('moment')
				);
		}
		
		if(!empty($Tab)){
			foreach ($Tab as $key=>$jour) {
			//on teste si le jour est égal à l'une des extrémités de la demande d'absence, sinon il n'y a pas de test spécial à faire
			if($dateDebutAbs==$jour['date_jourOff']&&$dateFinAbs==$jour['date_jourOff']){ //date début absence == jour férié et date fin absence == même jour férié
				//echo "boucle1";
				if($absence->ddMoment==$absence->dfMoment&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->ddMoment==$absence->dfMoment&&$absence->ddMoment=='matin'&&$jour['moment']=='matin'){
					$duree-=0.5;
				}
				else if($absence->ddMoment==$absence->dfMoment&&$absence->ddMoment=='apresmidi'&&$jour['moment']=='apresmidi'){
					$duree-=0.5;
				}
				else if($absence->ddMoment=='matin'&&$absence->dfMoment=='apresmidi'&&$jour['moment']=='apresmidi'){
					$duree-=0.5;
				}
				else if($absence->ddMoment=='matin'&&$absence->dfMoment=='apresmidi'&&$jour['moment']=='matin'){
					$duree-=0.5;
				}
				else if($absence->ddMoment=='matin'&&$absence->dfMoment=='apresmidi'&&$jour['moment']=='allday'){
					$duree-=1;
				}
			}else if($dateDebutAbs==$jour['date_jourOff']){	//si la date début est égale à la date du jour férié
				//echo "boucle2";
				if($absence->ddMoment=='matin'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
					$duree-=1;
				}
				else if($absence->ddMoment=='matin'&&$jour['moment']=='matin'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->ddMoment=='matin'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->ddMoment=='apresmidi'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->ddMoment=='apresmidi'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
			}
			else if($dateFinAbs==$jour['date_jourOff']){	//si la date début est égale à la date du jour férié
			//	echo "boucle3";
				if($absence->dfMoment=='matin'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->dfMoment=='matin'&&$jour['moment']=='matin'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->dfMoment=='matin'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->dfMoment=='apresmidi'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->dfMoment=='apresmidi'&&$jour['moment']=='matin'){ //traite le cas matin et apresmidi
					$duree-=0.5;
				}
				else if($absence->dfMoment=='apresmidi'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
					$duree-=1;
				}
			}
			else if($dateDebutAbs<=$jour['date_jourOff']&&$dateFinAbs>=$jour['date_jourOff']){
				//echo "boucle4";
				if($jour['moment']=='allday'){
					$duree-=1;
				}else{
					$duree-=0.5;
				}
			}
		}
		}
		
		return $duree;*/
	}

		
	function calculJoursTravailles(&$PDOdb, $duree, $date_debut, $date_fin, &$absence){
		/*
		 * Cette fonction est ignoble, à retravailler !
		 */
		global $conf, $TJourNonTravailleEntreprise;
		
		//on récupère l'information permettant de savoir si l'on doit décompter les jours normalement ou non
		$sql="SELECT decompteNormal FROM ".MAIN_DB_PREFIX."rh_type_absence WHERE typeAbsence LIKE '".$absence->type."'";
		$PDOdb->Execute($sql);
		if($PDOdb->Get_line()) {
			$decompteNormal=$PDOdb->Get_field('decompteNormal');
		}
		
		//echo $duree." ".$date_debut." ".$date_fin." <br>";

		//traitement jour de début
		$dateDebutAbs=$absence->php2Date($date_debut);
		$jourDebutSem=$absence->jourSemaine($date_debut);
		
		//traitement jour de fin
		$dateFinAbs=$absence->php2Date($date_fin);
		$jourFinSem=$absence->jourSemaine($date_fin);
		
		
		//on récupère les jours fériés compris dans la demande d'absence
		$sql="SELECT * FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries`";
		//echo $sql;
		$PDOdb->Execute($sql);
		$TabFerie = array();
		
		while($PDOdb->Get_line()) {
			$TabFerie[$PDOdb->Get_field('rowid')]= array(
				'date_jourOff'=>$PDOdb->Get_field('date_jourOff')
				,'moment'=>$PDOdb->Get_field('moment')
				);
			
		}			
		
		//on cherche le temps total de travail d'un employé par semaine : 
		//cela va permettre de savoir si la durée d'une absence doit être limité à 7h par jour et 35h par semaine ou non
		//si $tempsTravail supérieur à 35h, on limite les durées
		//$tempsTravail=$this->calculTempsTravailHebdo($PDOdb,$this->fk_user);
		
		//on cherche les jours travaillés par l'employé
		$sql="SELECT rowid, lundiam, lundipm, 
		mardiam, mardipm, mercrediam, mercredipm, 
		jeudiam, jeudipm, vendrediam, vendredipm,
		samediam, samedipm, dimancheam, dimanchepm
		
		,CONCAT(HOUR(date_lundi_heuredam) ,':' , MINUTE(date_lundi_heuredam)) as	date_lundi_heuredam
		,CONCAT(HOUR(date_lundi_heurefam) ,':' , MINUTE(date_lundi_heurefam)) as	date_lundi_heurefam
		,CONCAT(HOUR(date_lundi_heuredpm) ,':' , MINUTE(date_lundi_heuredpm)) as	date_lundi_heuredpm
		,CONCAT(HOUR(date_lundi_heurefpm) ,':' , MINUTE(date_lundi_heurefpm)) as	date_lundi_heurefpm	
		 	
		,CONCAT(HOUR(date_mardi_heuredam) ,':' , MINUTE(date_mardi_heuredam)) as	date_mardi_heuredam	
		,CONCAT(HOUR(date_mardi_heurefam) ,':' , MINUTE(date_mardi_heurefam)) as	date_mardi_heurefam
		,CONCAT(HOUR(date_mardi_heuredpm) ,':' , MINUTE(date_mardi_heuredpm)) as	date_mardi_heuredpm
		,CONCAT(HOUR(date_mardi_heurefpm) ,':' , MINUTE(date_mardi_heurefpm)) as	date_mardi_heurefpm
		
		,CONCAT(HOUR(date_mercredi_heuredam) ,':' , MINUTE(date_mercredi_heuredam)) as	date_mercredi_heuredam	
		,CONCAT(HOUR(date_mercredi_heurefam) ,':' , MINUTE(date_mercredi_heurefam)) as	date_mercredi_heurefam
		,CONCAT(HOUR(date_mercredi_heuredpm) ,':' , MINUTE(date_mercredi_heuredpm)) as	date_mercredi_heuredpm
		,CONCAT(HOUR(date_mercredi_heurefpm) ,':' , MINUTE(date_mercredi_heurefpm)) as	date_mercredi_heurefpm
		
		,CONCAT(HOUR(date_jeudi_heuredam) ,':' , MINUTE(date_jeudi_heuredam)) as	date_jeudi_heuredam	
		,CONCAT(HOUR(date_jeudi_heurefam) ,':' , MINUTE(date_jeudi_heurefam)) as	date_jeudi_heurefam
		,CONCAT(HOUR(date_jeudi_heuredpm) ,':' , MINUTE(date_jeudi_heuredpm)) as	date_jeudi_heuredpm
		,CONCAT(HOUR(date_jeudi_heurefpm) ,':' , MINUTE(date_jeudi_heurefpm)) as	date_jeudi_heurefpm
		
		,CONCAT(HOUR(date_vendredi_heuredam) ,':' , MINUTE(date_vendredi_heuredam)) as	date_vendredi_heuredam	
		,CONCAT(HOUR(date_vendredi_heurefam) ,':' , MINUTE(date_vendredi_heurefam)) as	date_vendredi_heurefam
		,CONCAT(HOUR(date_vendredi_heuredpm) ,':' , MINUTE(date_vendredi_heuredpm)) as	date_vendredi_heuredpm
		,CONCAT(HOUR(date_vendredi_heurefpm) ,':' , MINUTE(date_vendredi_heurefpm)) as	date_vendredi_heurefpm
		
		,CONCAT(HOUR(date_samedi_heuredam) ,':' , MINUTE(date_samedi_heuredam)) as	date_samedi_heuredam	
		,CONCAT(HOUR(date_samedi_heurefam) ,':' , MINUTE(date_samedi_heurefam)) as	date_samedi_heurefam
		,CONCAT(HOUR(date_samedi_heuredpm) ,':' , MINUTE(date_samedi_heuredpm)) as	date_samedi_heuredpm
		,CONCAT(HOUR(date_samedi_heurefpm) ,':' , MINUTE(date_samedi_heurefpm)) as	date_samedi_heurefpm
		
		,CONCAT(HOUR(date_dimanche_heuredam) ,':' , MINUTE(date_dimanche_heuredam)) as	date_dimanche_heuredam	
		,CONCAT(HOUR(date_dimanche_heurefam) ,':' , MINUTE(date_dimanche_heurefam)) as	date_dimanche_heurefam
		,CONCAT(HOUR(date_dimanche_heuredpm) ,':' , MINUTE(date_dimanche_heuredpm)) as	date_dimanche_heuredpm
		,CONCAT(HOUR(date_dimanche_heurefpm) ,':' , MINUTE(date_dimanche_heurefpm)) as	date_dimanche_heurefpm	
		, tempsHebdo
		FROM `".MAIN_DB_PREFIX."rh_absence_emploitemps` 
		WHERE fk_user=".$absence->fk_user." AND is_archive!=1";  
//print $sql;
		$PDOdb->Execute($sql);
		$TTravail = array();
		$TTravailHeure= array();
		while($PDOdb->Get_line()) {
			foreach ($absence->TJour as $jour) {
				foreach(array('am','pm') as $moment) {
					$TTravail[$jour.$moment]=$PDOdb->Get_field($jour.$moment);
					
				}
				foreach(array('dam','fam','dpm','fpm') as $moment) {
					$TTravailHeure["date_".$jour."_heure".$moment]=$PDOdb->Get_field("date_".$jour."_heure".$moment);
				}
			}
			$rowid=$PDOdb->Get_field('rowid');
			$tpsHebdoUser=$PDOdb->Get_field('tempsHebdo');
		}	
					
		//on traite les jours de début et de fin indépendemment des autres
		if($date_debut==$date_fin){	//si les jours de début et de fin sont les mêmes

			$ferie=0;

			foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite paspour les jours, car déjà traité avant pour les jours 
												//on le traite pour les heures
	 			if(strtotime($jourFerie['date_jourOff'])==$date_debut){
	 				$ferie=1;
	 				
	 				$jourSemaineFerie=$absence->jourSemaine($jourFerie['date_jourOff']);
	 				//on traite le cas des heures
	 				if($jourFerie['moment']=='matin'){
	 					if($TTravail[$jourSemaineFerie.'pm']==1){
	 						if($absence->dfMoment=='apresmidi'){
 								$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredpm"], $TTravailHeure["date_".$jourSemaineFerie."_heurefpm"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
								
	 						}
	 						
	 					}
	 				}
	 				if($jourFerie['moment']=='apresmidi'){
	 					if($TTravail[$jourSemaineFerie.'am']==1){
	 						if($absence->ddMoment=='matin'){
		 						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredam"], $TTravailHeure["date_".$jourSemaineFerie."_heurefam"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
							}
	 					}
	 				}
	 				
	 			}

	 		}
			if(!$ferie){
				
				//echo "boucle1";
				if($absence->dfMoment=='matin'){		// si la date de fin est le matin, il n'y a donc que le cas matin à traiter
					if($TTravail[$jourDebutSem.'am']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
						
					}else{
						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredam"], $TTravailHeure["date_".$jourDebutSem."_heurefam"]));
						$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);	

					}
				}else if($absence->ddMoment=='apresmidi'){		// si la date de debut est lapres midi, il n'y a donc que le cas pm à traiter
					if($TTravail[$jourDebutSem.'pm']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}
					else{
						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredpm"], $TTravailHeure["date_".$jourDebutSem."_heurefpm"]));
						$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
					}
				}else{	//sinon on traite les cas matin et apres midi
					if($TTravail[$jourDebutSem.'am']==0){
						if($decompteNormal=='oui'){
							$duree-=.5;
						}else if(in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=.5;
						}
					}else{
						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredam"], $TTravailHeure["date_".$jourDebutSem."_heurefam"]));
						$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
					}
					
					if($TTravail[$jourDebutSem.'pm']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(!in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}else{
						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredpm"], $TTravailHeure["date_".$jourDebutSem."_heurefpm"]));
						$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
					}
				}
				
				if($tpsHebdoUser>=35){
					$absence->dureeHeurePaie=7*$duree;
				}
				else $absence->dureeHeurePaie=$absence->dureeHeure;
				return $duree;
			}
			
			else {
				if($tpsHebdoUser>=35){
					$absence->dureeHeurePaie=7*$duree;
				}
				else $absence->dureeHeurePaie=$absence->dureeHeure;
				return $duree;
			}
			
		}else{	//les jours de début et de fin sont différents
			//////////////////////////jour de début
			$ferie=0;		
			
			foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas pour les jours, car déjà traité avant
												//on le traite pour les heures
	 			if(strtotime($jourFerie['date_jourOff'])==$date_debut){
	 				$ferie=1;
					$jourSemaineFerie=$absence->jourSemaine($jourFerie['date_jourOff']);
	 				//on traite le cas des heures
	 				if($jourFerie['moment']=='matin'){
	 					if($TTravail[$jourSemaineFerie.'pm']==1){
	 							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredpm"], $TTravailHeure["date_".$jourSemaineFerie."_heurefpm"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
	 					}
	 				}
	 				if($jourFerie['moment']=='apresmidi'){
	 					if($TTravail[$jourSemaineFerie.'am']==1){
	 						if($absence->ddMoment=='matin'){
		 						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredam"], $TTravailHeure["date_".$jourSemaineFerie."_heurefam"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
							}
	 					}
	 				}
	 			}
	 		}
			if(!$ferie){
				if($absence->ddMoment=='matin'){
					if($TTravail[$jourDebutSem.'am']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}	
					}
					if($TTravail[$jourDebutSem.'pm']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}
				}else if($absence->ddMoment=='apresmidi'){
					if($TTravail[$jourDebutSem.'pm']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourDebutSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}
				}
			}
			
			///////////////////////////jour de fin
			$ferie=0;		
			foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
	 			if(strtotime($jourFerie['date_jourOff'])==$date_fin){
	 				$ferie=1;
					$jourSemaineFerie=$absence->jourSemaine($jourFerie['date_jourOff']);
	 				//on traite le cas des heures
	 				if($jourFerie['moment']=='matin'){
	 					if($TTravail[$jourSemaineFerie.'pm']==1){
	 						if($absence->dfMoment=='apresmidi'){
	 							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredpm"], $TTravailHeure["date_".$jourSemaineFerie."_heurefpm"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
	 						}
	 						
	 					}
	 				}
	 				if($jourFerie['moment']=='apresmidi'){
	 					if($TTravail[$jourSemaineFerie.'am']==1){
		 						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredam"], $TTravailHeure["date_".$jourSemaineFerie."_heurefam"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
	 					}
	 				}
	 			}
	 		}
			if(!$ferie){
				if($absence->dfMoment=='matin'){
					if($TTravail[$jourFinSem.'am']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourFinSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}
				}else if($absence->dfMoment=='apresmidi'){
					if($TTravail[$jourFinSem.'am']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourFinSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}
					if($TTravail[$jourFinSem.'pm']==0){
						if($decompteNormal=='oui'){
							$duree-=0.5;
						}else if(in_array($jourFinSem, $TJourNonTravailleEntreprise)){
							$duree-=0.5;
						}
					}
				}
			}
		}
		
		//pour chaque jour, du début de l'absence jusqu'à sa fin, on teste si l'employé travaille
		$jourEnCours=strtotime('+1day',$date_debut);
		$jourFin=$date_fin;
		while($jourEnCours!=$jourFin){
			$ferie=0;
			//echo "boucle1";
			foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas en jours, car déjà traité avant. 
												//on traite les heures
	 			if(strtotime($jourFerie['date_jourOff'])==$jourEnCours){
	 				$ferie=1;
	 				//on traite le cas des heures
	 				$jourSemaineFerie=$absence->jourSemaine($jourFerie['date_jourOff']);
					if($jourFerie['moment']=='matin'){
	 					if($TTravail[$jourSemaineFerie.'pm']==1){
	 							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredpm"], $TTravailHeure["date_".$jourSemaineFerie."_heurefpm"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);	
	 					}
	 				}
	 				if($jourFerie['moment']=='apresmidi'){
	 					if($TTravail[$jourSemaineFerie.'am']==1){
		 						$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourSemaineFerie."_heuredam"], $TTravailHeure["date_".$jourSemaineFerie."_heurefam"]));
								$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
	 					}
	 				}
	 			}
	 		}
			if(!$ferie){
				
				$jourEnCoursSem=$absence->jourSemaine($jourEnCours);
				//echo $jourEnCoursSem;
				foreach ($absence->TJour as $jour) {
					if($jour==$jourEnCoursSem){
						foreach(array('am','pm') as $moment) {
							if($TTravail[$jour.$moment]==0){
								//print "$jour $moment $decompteNormal $duree<br>";
								if($decompteNormal=='oui'){
									$duree-=0.5;
								}else if(in_array($jour, $TJourNonTravailleEntreprise)){
									$duree-=0.5;
								}
								//print "$duree<br>";
							}
						}
					}
				}
			}
			$jourEnCours=strtotime('+1day',$jourEnCours);
			
		}
		
		//////////////////////////////////////////////////////////////TRAITEMENT DES HEURES
		
		//pour chaque jour, du début de l'absence jusqu'à sa fin, on teste si l'employé travaille et on compte les heures
		$jourEnCours=$date_debut;
		$jourFin=$date_fin;
		$dureeHeure=$absence->dureeHeure;
		$cpt=0;
		while($jourEnCours!=$jourFin){
			$ferie=0;
			//echo "boucle1";
			
			foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
	 			if(strtotime($jourFerie['date_jourOff'])==$jourEnCours){
	 				$ferie=1;
	 			}
	 		}
			if(!$ferie){
				$jourEnCoursSem=$absence->jourSemaine($jourEnCours);
				//echo $jourEnCoursSem;
				foreach ($absence->TJour as $jour) {
					if($jour==$jourEnCoursSem){
						foreach(array('am','pm') as $moment) {
							if($TTravail[$jour.$moment]==0){
							}
							else{
								if($cpt==0){   //on traite le premier jour de l'absence
									if($moment=="am"){
										if($absence->ddMoment=="matin"){
											$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
										}else if($absence->ddMoment=="apresmidi"){
										}
									}
									else if($moment=="pm"){
										if($absence->ddMoment=="apresmidi"){
											$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
										}else if($absence->ddMoment=="matin"){
											$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
										}
									}
									
								}else{
									$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
								}
								
							}
						}
					}
					
				}
			}
			$jourEnCours=strtotime('+1day',$jourEnCours);
			$cpt++;
		}
		
		///////////////////////////////////////////////TRAITEMENT DU DERNIER JOUR POUR LES HEURES
		$ferie=0;
		foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
 			if(strtotime($jourFerie['date_jourOff'])==$jourEnCours){
 				$ferie=1;
 			}
 		}
		if(!$ferie){
			$jourEnCoursSem=$absence->jourSemaine($jourEnCours);
			//echo $jourEnCoursSem;
			foreach ($absence->TJour as $jour) {
				if($jour==$jourEnCoursSem){
					foreach(array('am','pm') as $moment) {
						if($TTravail[$jour.$moment]==0){	
						}
						else{
							if($moment=="am"){
								if($absence->dfMoment=="matin"){
									$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
								}else if($absence->dfMoment=="apresmidi"){
									$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
								}
							}
							else if($moment=="pm"){
								if($absence->dfMoment=="apresmidi"){
									$dureeHeure=$absence->additionnerHeure($dureeHeure,$absence->difheure($TTravailHeure["date_".$jour."_heured".$moment], $TTravailHeure["date_".$jour."_heuref".$moment]));
								}else if($absence->dfMoment=="matin"){
								}
							}
						}
					}
				}
				
			}
		}
		
		$absence->dureeHeure=$dureeHeure;
		$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
		if($tpsHebdoUser>=35){
			$absence->dureeHeurePaie=7*$duree;
		}
		else{
			$absence->dureeHeurePaie=$absence->dureeHeure;
		}
		
		
	    return $duree;
	}
		
	//permet d'additionner deux heures ensemble
	function additionnerHeure($dureeTotale, $dureeDiff){
		list($heureT, $minuteT) = explode(':', $dureeTotale);
		//echo "heureT : ".$heureT." minutesT : ".$minuteT;
		list($heureD, $minuteD) = explode(':', $dureeDiff);
		
		$heureT=$heureT+$heureD;
		$minuteT=$minuteT+$minuteD;
		
		while($minuteT>60){
			$minuteT-=60;
			$heureT+=1;
		}
		
		return $heureT.":".$minuteT;
	}
	
	
	
	function horaireMinuteEnCentieme($horaire){
		list($heure, $minute) = explode(':', $horaire);	
		$horaireCentieme=$heure+$minute/60;
		return $horaireCentieme;
	}
	
	//renvoie le jour de la semaine correspondant à la date passée en paramètre
	function jourSemaine($phpDate){
		$timestamp = strtotime(date('Y-m-d', $phpDate));

		// affichage du jour de la semaine
		return $this->Tjoursem[date("w",$timestamp)];
	}
	
	
	//retourne la date au format "Y-m-d H:i:s"
	function php2Date($phpDate){
	    return date("Y-m-d H:i:s", $phpDate);
	}
	
	
	//retourne la date au format "d/m/Y"
	function php2dmy($phpDate){
	    return date("d/m/Y", $phpDate);
	}
	
	
	//recrédite les heures au compteur lors de la suppression d'une absence 
	function recrediterHeure(&$PDOdb){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		if($this->etat!='Refusee'){
			
			$compteur=new TRH_Compteur;
			$compteur->load_by_fkuser($PDOdb, $this->fk_user);
			
			switch($this->type){
				case "rttcumule" : 

						$compteur->add($PDOdb, $this->type, -$this->duree, 'Refus rtt cumulé');						
						
				break;
				case "rttnoncumule" : 
						
						$compteur->add($PDOdb, $this->type, -$this->duree, 'Refus rtt non cumulé');		
						
				break;
				case 'conges':
				case 'cppartiel':
					
					$compteur->add($PDOdb, $this->type, array(-$this->congesPrisNM1, -$this->congesPrisN), 'Refus congé');		
					
				break;
				
				case 'recup':
					$compteur->add($PDOdb, $this->type, -$this->duree, 'Refus jour de récupération');
			
					break;
			}
		}
	}

	
	
	//donne la différence entre 2 heures (respecter l'ordre début et fin)
	function difheure($heuredeb,$heurefin)
	{
		$hd=explode(":",$heuredeb);
		$hf=explode(":",$heurefin);
		$hd[0]=(int)($hd[0]);$hd[1]=(int)($hd[1]);$hd[2]=(int)($hd[2]);
		$hf[0]=(int)($hf[0]);$hf[1]=(int)($hf[1]);$hf[2]=(int)($hf[2]);
		if($hf[2]<$hd[2]){$hf[1]=$hf[1]-1;$hf[2]=$hf[2]+60;}
		if($hf[1]<$hd[1]){$hf[0]=$hf[0]-1;$hf[1]=$hf[1]+60;}
		if($hf[0]<$hd[0]){$hf[0]=$hf[0]+24;}
		return (($hf[0]-$hd[0]).":".($hf[1]-$hd[1]).":".($hf[2]-$hd[2]));
	}
	function testMinEffectifGroupeOk(&$PDOdb, $idGroup, $nb_min) {
		
		if($nb_min>0) {
			$t_current=$this->date_debut;
			
			while($t_current <= $this->date_fin) {
			
				$date =date('Y-m-d', $t_current);
				
				$sql = $this->rechercheAucunConges($PDOdb, $idGroup,0,$date,$date);
				
				$Tab = $PDOdb->ExecuteAsArray($sql);
				$nb_user = count($Tab);
				
				if($nb_user<=$nb_min) return false;
				
				$t_current = strtotime('+1day', $t_current);
			}
			
			
		}
		
		return true;
		
	}
	function mailAlertEffectif($idGroup) {
		global $db;
		$g=new UserGroup($db);
		$g->fetch($idGroup);
		
		$mailto = $g->array_options['options_alert_email'];
		$nb_minimum = $g->array_options['options_number_min'];
		if($this->date_debut == $this->date_debut) {
			$dateInterval = 'le '.dol_print_date($this->date_debut);
		}
		else{
			$dateInterval = 'du '.dol_print_date($this->date_debut).' '.$langs->trans('to').' '.dol_print_date($this->date_fin);	
		}
		
		if( $mailto ) {
			
				$u=new User($db);
				$u->fetch($this->fk_user);
			
				$TBS=new TTemplateTBS;						
				$html = $TBS->render( dol_buildpath('/absence/tpl/mail.absence.alert.minimum.tpl.php')
					,array() 
					,array(
						'mail'=>array(
							'collabName'=>$u->getNomUrl()
							,'DateInterval'=>$dateInterval
							,'groupName'=>$g->name
							,'minimum'=>$nb_minimum
						)
					)
				);
				
				$mailfrom = empty($conf->global->RH_USER_MAIL_SENDER) ? 'alert@dynamicrh.atm-consulting.fr' : $conf->global->RH_USER_MAIL_SENDER;
				
				$rep=new TReponseMail( $mailfrom, $mailto,"Alerte défaut de personnel groupe ".$g->name, $html);
				$rep->send();
				
			
		}
		
		
		
	}
	function testEffectifGroupe(&$PDOdb) {
		global $db;
		
		$user =new User($db);
		$user->fetch($this->fk_user);
		
		if($user->id>0) {
			
			$g=new UserGroup($db);
			$TGroup = $g->listGroupsForUser($user->id);
			
			foreach($TGroup as $group) {
				
				$nb_min = (int)$group->array_options['options_number_min'];
				
				if(!$this->testMinEffectifGroupeOk($PDOdb, $group->id, $nb_min)) {
					
					$this->mailAlertEffectif($group->id);
					
					return false;
				}
				
			}		
		
			return true;
		
		}		
		
		return false;
	}

	
	function dureeAbsenceRecevable(&$PDOdb){
		global $langs;
		
		$dureeAbsenceRecevable=0;
		$TRegle=$this->recuperationRegleUser($PDOdb,$this->fk_user);
		//var_dump($TRegle);
		$this->loadDureeAllAbsenceUser($PDOdb, $this->type);
		$dureeAbsenceRecevable = $this->nbJoursTotalRegle($this->TDureeAllAbsenceUser, $TRegle);
		
		if(!$this->testEffectifGroupe($PDOdb)) {
			$dureeAbsenceRecevable = 2;
			$this->error = $langs->trans('ErrInsuffisanteNumberOfPerson');
		}
	
		return $dureeAbsenceRecevable;
	}
	
	function loadTypeAbsencePerTypeUser(&$PDOdb) {
		
		//combo box pour le type d'absence admin
		$this->TTypeAbsenceAdmin=$this->TTypeAbsenceUser=$this->TTypeAbsencePointeur=array();
		$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence`";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$this->TTypeAbsenceAdmin[$PDOdb->Get_field('typeAbsence')]=$PDOdb->Get_field('libelleAbsence');
		}
		
		
		//combo box pour le type d'absence utilisateur
		$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
				WHERE admin=0";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$this->TTypeAbsenceUser[$PDOdb->Get_field('typeAbsence')]=$PDOdb->Get_field('libelleAbsence');
		}
		
		//combo box pour le type d'absence pointeur
		$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
				WHERE admin=0 OR typeAbsence LIKE 'nonjustifiee'";
		$PDOdb->Execute($sql);
		while($PDOdb->Get_line()) {
			$this->TTypeAbsencePointeur[$PDOdb->Get_field('typeAbsence')]=$PDOdb->Get_field('libelleAbsence');
		}
	}
	
	/**
	 * Charge l'attribut TDureeAllAbsenceUser de l'objet absence associant à chaque mois de chaque année une durée total de congés pris ou demandés
	 * @param object $objet : objet absence
	 */
	function loadDureeAllAbsenceUser(&$PDOdb, $typeAbsence='Tous') {
		$this->TDureeAllAbsenceUser=array();
		
		// On récupère toutes les absences contenues dans le ou les mois sur le(s)quel(s) se trouve la plage de congés
		$sql = $this->rechercheAbsenceUser($PDOdb,$this->fk_user, date("Y-m-01 H:i:s", $this->date_debut), date("Y-m-".date("t", date("m", $this->date_fin))." H:i:s", $this->date_fin), $typeAbsence);

		$Tab = $PDOdb->ExecuteAsArray($sql);
		foreach($Tab as $row) {
			
			$abs = new TRH_Absence;
			$abs->load($PDOdb, $row->ID);
			$abs->calculDureeAbsenceParAddition($PDOdb);
			
			if(!empty($abs->TDureeAbsenceUser)) {
				foreach($abs->TDureeAbsenceUser as $annee => $tabMonth) {
					foreach($tabMonth as $month => $duree) {
						@$this->TDureeAllAbsenceUser[$annee][$month] += $duree;
					}
				}
				
			}
			
		}
		
	}
	
	/**
	 * Vérifie si le nombre de jours total de congés de l'utilisateur est inférieure au nombre total de jours autorisé par les règles en vigueur
	 * @param array $TDureeAllAbsenceUser Tableau de l'objet absence qui associe à chaque mois de chaque année une durée total de congés pris ou demandés
	 * @param int $avertissement 0=refusé restrictif, 1=accepté, 2=refusé non restrictif
	 */
	function nbJoursTotalRegle($TDureeAllAbsenceUser, $TRegles) {

		//var_dump($tabReglesHomeOffice);
		// On récupère la règle qui concerne le nombre de jours à ne pas dépasser
		
		$pas_avertissement=1;
		
		if(is_array($TRegles) && count($TRegles) > 0) {
			foreach($TRegles as $TLineRegle) {
				
				if($TLineRegle['typeAbsence']==$this->type) {
					
					if($TLineRegle['contigue']==1 && $TLineRegle['contigueNoJNT']==1) {
						$dureeTest = $this->dureeContigueWhitoutJNT;
					}
					else if($TLineRegle['contigue']==1) {
						$dureeTest = $this->dureeContigue;
					}
					else{
						$dureeTest = $this->duree;
					}
					$nbJoursAutorises = $TLineRegle['nbJourCumulable'];
					
						// var_dump($TLineRegle['nbJourCumulable'], $dureeTest, $this->duree, $this->dureeContigue,$this->dureeContigueWhitoutJNT);
					
					//echo $this->duree;exit;
					if($TLineRegle['periode']==='ONE' && $dureeTest>$TLineRegle['nbJourCumulable']){
						if($TLineRegle['restrictif']==1){
								 return 0;
						}
						else {
							$pas_avertissement=2;  //"Attention, le nombre de jours dépasse la règle"
							if(!empty($this->avertissementInfo))$this->avertissementInfo.=', ';
							$this->avertissementInfo = 'Règle '.$TLineRegle['id'];
						}
						
					} elseif($TLineRegle['periode'] === "YEAR") {
	
						foreach($TDureeAllAbsenceUser as $annee => $tabMonth) {
							
							// On calcule le nombre de jour total par an
							$dureeTotale = 0;
							foreach($tabMonth as $duree) {
								$dureeTotale += $duree;
							}
							// Si le nombre de jours total par an est supérieur au nb autorisé, on retourn false
							if($dureeTotale+$dureeTest > $nbJoursAutorises) {
								if($TLineRegle['restrictif']==1){
										 return 0;
								}
								else {
									$pas_avertissement=2;
									if(!empty($this->avertissementInfo))$this->avertissementInfo.=', ';
									$this->avertissementInfo = 'Règle '.$TLineRegle['id'];
								 } //"Attention, le nombre de jours dépasse la règle"
							}
						}
						
					} else if($TLineRegle['periode'] === "MONTH") {
						
						
						foreach($TDureeAllAbsenceUser as $annee => $tabMonth) {
							foreach($tabMonth as $duree) {
								if($duree+$dureeTest > $nbJoursAutorises) {
									if($TLineRegle['restrictif']==1){
											 return 0;
									}
									else{
										$pas_avertissement=2;  //"Attention, le nombre de jours dépasse la règle"
										if(!empty($this->avertissementInfo))$this->avertissementInfo.=', ';
										$this->avertissementInfo = 'Règle '.$TLineRegle['id'];
									} 
								}
							}
						}
						
					}
					
				}
					
			}
		}

		return $pas_avertissement;

	}
	
		/**
	 * Retourne un tableau contenant les règles sur le Home office qui concernent l'utilisateur courant
	 * @return array $tabRegles tableau de règles sur le home Office par lesquelles l'utilisateur courant est concerné
	 */
	function _getReglesHomeOffice() {
			
		global $db, $user;
		
		$tabRegles = array();
		
		$user_group = new UserGroup($db);
		$TGroups_of_user = $user_group->listGroupsForUser($user->id);
		if(count($TGroups_of_user) > 0) $TGroups_of_user = array_keys($TGroups_of_user);
		
		$sql = "SELECT rowid, nbJourCumulable, restrictif, periode";
		$sql.= " FROM ".MAIN_DB_PREFIX.'rh_absence_regle';
		$sql.= " WHERE (fk_user = ".$user->id;
		if(count($TGroups_of_user) > 0) $sql.= " OR fk_usergroup IN (".implode(",", $TGroups_of_user).")";
		$sql.= ")";
		$sql.= ' AND typeAbsence = "HomeOffice"';
		
		$resql = $db->query($sql);
		if($resql->num_rows > 0) {
			while($res = $db->fetch_object($resql)) {
				$tabRegles[] = $res;
			}
		}
		//echo $sql;exit;
		return $tabRegles;
		
	}
	
	function isWorkingDay(&$PDOdb, $date) {
		$isNotFerie=$isNotAbsence=$isWorkingDay=false;
		
		
		$isNotFerie = !(TRH_JoursFeries::estFerie($PDOdb, $date));
		if($isNotFerie) $isNotAbsence = $this->isNotAbsenceDay($PDOdb, $date);
		
		if($isNotFerie && $isNotAbsence) $isWorkingDay= (TRH_EmploiTemps::estTravaille($PDOdb, $this->fk_user, $date)!='NON');
		
		return array($isWorkingDay, $isNotFerie, $isNotAbsence);
	
	}
	
	function isWorkingDayNext(&$PDOdb, $dateTest){ // regarde x/x emploi du temps

		$date=strtotime('+1day',$dateTest); 
		
		return $this->isWorkingDay($PDOdb, date('Y-m-d', $date));
				
	}
	
	function isWorkingDayPrevious(&$PDOdb, $dateTest){

		$date=strtotime('-1day',$dateTest); 
		return $this->isWorkingDay($PDOdb, date('Y-m-d', $date));
	}

	function isNotAbsenceDay(&$PDOdb, $date) {
		
		$sql = $this->rechercheAbsenceUser($PDOdb, $this->fk_user, $date, $date);
		$Tab = $PDOdb->ExecuteAsArray($sql);
		
		return (count($Tab) == 0);
		
	}

	///////////////FONCTIONS pour le fichier rechercheAbsence\\\\\\\\\\\\\\\\\\\\\\\\\\\
	
	//va permettre la création de la requête pour les recherches d'absence pour les collaborateurs
	function requeteRechercheAbsence(&$PDOdb, $idGroupeRecherche, $idUserRecherche, $horsConges, $date_debut, $date_fin, $typeAbsence){
			
			if($horsConges==1){ //on recherche uniquement une compétence
				$sql=$this->rechercheAucunConges($PDOdb,$idGroupeRecherche, $idUserRecherche,$date_debut, $date_fin, $typeAbsence);
			}
			else if($idGroupeRecherche!=0&&$idUserRecherche==0){ //on recherche les absences d'un groupe
				$sql=$this->rechercheAbsenceGroupe($PDOdb, $idGroupeRecherche, $date_debut, $date_fin, $typeAbsence);
			}
			else{ //if($idUserRecherche!=0){ //on recherche les absences d'un utilisateur
				$sql=$this->rechercheAbsenceUser($PDOdb,$idUserRecherche, $date_debut, $date_fin, $typeAbsence);
			}
			return $sql;
	}
	
	//requete avec groupe de collaborateurs précis
	function rechercheAbsenceGroupe(&$PDOdb, $idGroupeRecherche, $date_debut, $date_fin, $typeAbsence){ 
			global $conf, $langs;
			
			//on recherche les absences d'un groupe pendant la période
			$sql="SELECT  a.rowid as 'ID', u.login, u.lastname,u.firstname, DATE_FORMAT(a.date_debut, '%d/%m/%Y') as 'date_debut', 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as 'date_fin', a.libelle, a.libelleEtat
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
				WHERE a.fk_user=u.rowid 
				AND  g.fk_user=u.rowid
				AND g.fk_usergroup=".$idGroupeRecherche."
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
			
			if($typeAbsence!= 'Tous'){
				$sql.=" AND a.type LIKE '".$typeAbsence."'";
			}
			
			return $sql;
	}
	
	//requete renvoyant les utilisateurs n'ayant pas pris de congés pendant une période
	function rechercheAucunConges(&$PDOdb, $idGroupeRecherche,$idUserRecherche, $date_debut, $date_fin, $typeAbsence='Tous'){ 
			global $conf, $langs;

			if($idUserRecherche!=0){
				
				$sql="SELECT DISTINCT u.login, u.lastname, u.firstname
				FROM ".MAIN_DB_PREFIX."user as u 
				WHERE u.rowid =".$idUserRecherche." AND u.rowid NOT IN (
							SELECT a.fk_user 
							FROM ".MAIN_DB_PREFIX."rh_absence as a,".MAIN_DB_PREFIX."rh_type_absence as t 
							WHERE a.fk_user=".$idUserRecherche." AND a.type=t.typeAbsence AND t.isPresence!=1
							(a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)
							";
				if($typeAbsence!= 'Tous'){
					$sql.=" AND a.type LIKE '".$typeAbsence."' ";
				}
				$sql.=")";
			}
			//	on recherche les utilisateurs n'ayant pas eu d'absences pendant la période désirée
			else if($idGroupeRecherche==0){ 
				$sql="SELECT DISTINCT u.login, u.lastname, u.firstname
				FROM ".MAIN_DB_PREFIX."user as u 
				WHERE  u.rowid NOT IN (
							SELECT a.fk_user 
							FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."rh_type_absence as t
							WHERE a.type=t.typeAbsence AND t.isPresence!=1 AND
							(a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)
							";
				if($typeAbsence!= 'Tous'){
					$sql.=" AND a.type LIKE '".$typeAbsence."' ";
				}
				$sql.=")";
			}
			else
			{	//	on recherche les utilisateurs d'un groupe n'ayant pas eu d'absences pendant la période désirée

				$sql="SELECT DISTINCT g.fk_user,  u.login, u.lastname, u.firstname
				FROM ".MAIN_DB_PREFIX."usergroup_user as g, ".MAIN_DB_PREFIX."user as u
				
				WHERE u.rowid=g.fk_user";
				if($idGroupeRecherche!=0){
						$sql.=" AND g.fk_usergroup=".$idGroupeRecherche;
				}
				$sql.="
				AND  g.fk_user NOT IN (
							SELECT a.fk_user 
							FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."usergroup_user as g,".MAIN_DB_PREFIX."rh_type_absence as t 
							WHERE g.fk_user=u.rowid AND a.type=t.typeAbsence AND t.isPresence!=1 
							AND g.fk_usergroup=".$idGroupeRecherche." 
							AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
				if($typeAbsence!= 'Tous'){
					$sql.=" AND a.type LIKE '".$typeAbsence."' ";
				}
				$sql.=")";
			}
			    
			return $sql;
	}

	//requete avec un collaborateur précis
	function rechercheAbsenceUser(&$PDOdb,$idUserRecherche, $date_debut, $date_fin, $typeAbsence='Tous'){
			global $conf, $langs;

			//on recherche les absences d'un utilisateur pendant la période
			$sql="SELECT a.rowid as 'ID',  u.login, u.lastname, u.firstname,a.type,a.date_hourStart,a.date_hourEnd,
				DATE_FORMAT(a.date_debut, '%d/%m/%Y') as date_debut, 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as date_fin, a.libelle, a.libelleEtat
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
				WHERE a.fk_user=u.rowid 
				AND a.etat!='Refusee'
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin
				)";
			if($idUserRecherche!=0){
				$sql.=" AND a.fk_user=".$idUserRecherche;
			}
			if($typeAbsence!= 'Tous'){
				$sql.=" AND a.type LIKE '".$typeAbsence."'";
			}
			
			return $sql;
	}
	
	//	fonction permettant le chargement de l'absence pour un utilisateur si celle-ci existe	
	function load_by_idImport(&$PDOdb, $idImport){
		global $conf;
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_absence 
		WHERE idAbsImport=".$idImport;

		$PDOdb->Execute($sql);
		if ($PDOdb->Get_line()) {
			return $this->load($PDOdb, $PDOdb->Get_field('rowid'));
		}
		return false;
	}
	
	
	//fonction qui renvoie 1 si une absence existe déjà pendant la date que l'on veut ajouter, 0 sinon
	function testExisteDeja($PDOdb, $absence){
			
		if($absence->ddMoment=='apresmidi')	{
			$date_debut = strtotime( date('Y-m-d 12:00:00', $absence->date_debut) );
		}
		else {
			$date_debut = strtotime( date('Y-m-d 00:00:00', $absence->date_debut) );
		}
		
		if($absence->dfMoment=='matin')	{
			$date_fin = strtotime( date('Y-m-d 11:59:59', $absence->date_fin) );
		}
		else {
			$date_fin = strtotime( date('Y-m-d 23:59:59', $absence->date_fin) );
		}			
		
		
		$sql="SELECT date_debut, date_fin, ddMoment, dfMoment 
		FROM ".MAIN_DB_PREFIX."rh_absence 
		WHERE fk_user=".$absence->fk_user." AND etat IN ('Validee','Avalider')
		
		AND date_debut<='".date('Y-m-d 23:59:59', $absence->date_fin)."' 
		AND date_fin>='".date('Y-m-d 00:00:00', $absence->date_debut)."' 
		";

		$PDOdb->Execute($sql);
		$k=0;
		
		$TAbs=array();
		while($PDOdb->Get_line()) {
			$TAbs[$k]['date_debut']=strtotime($PDOdb->Get_field('date_debut'));
			$TAbs[$k]['date_fin']=strtotime($PDOdb->Get_field('date_fin')) + 86399;
			
			/*$TAbs[$k]['ddMoment']=strtotime($PDOdb->Get_field('ddMoment'));
			$TAbs[$k]['dfMoment']=strtotime($PDOdb->Get_field('dfMoment'));*/
			
			if($PDOdb->Get_field('ddMoment')=='apresmidi') $TAbs[$k]['date_debut'] = strtotime( date('Y-m-d 12:00:00', $TAbs[$k]['date_debut']) );
			if($PDOdb->Get_field('dfMoment')=='matin') $TAbs[$k]['date_fin'] = strtotime( date('Y-m-d 11:59:59', $TAbs[$k]['date_fin']) );
			
			
			$k++;
		}
		//print_r($TAbs);
		if(!empty($TAbs)){
				foreach($TAbs as $dateAbs){
					//on traite le début de l'absence
					//print_r($dateAbs);
					
				/*	print date('Y-m-d H:i:s', $date_debut).' <= '.date('Y-m-d H:i:s', $dateAbs['date_fin']).' && '.date('Y-m-d H:i:s', $date_fin).' >= '.date('Y-m-d H:i:s',$dateAbs['date_debut'])
					.' - 	<br>';
				*/
					if( $date_debut <= $dateAbs['date_fin'] && $date_fin>=$dateAbs['date_debut'])
					 {
					 	
						return array(date('Y-m-d H:i',$dateAbs['date_debut']),date('Y-m-d H:i', $dateAbs['date_fin']),$absence->fk_user);
						
					}
						/*
					if($absence->date_debut<$dateAbs['date_debut'] && $absence->date_fin>$dateAbs['date_fin']) return 1;
					
					//on traite la fin de l'absence	
					if($absence->date_debut>$dateAbs['date_debut'] && $absence->date_fin<$dateAbs['date_fin']) return 1;*/
				}
		 }
			
		
		
		return false;
	}
	
	static function getPlanning(&$PDOdb, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin){
			
		dol_include_once('/absence/class/pointeuse.class.php');

			$abs = new TRH_Absence;
			
			$t_current = strtotime($date_debut);
			$t_end = strtotime($date_fin);
			
			while($t_current <= $t_end) {
				
				$TPlanning = $abs->requetePlanningAbsence($PDOdb, $idGroupeRecherche, $idUserRecherche, date('d/m/Y', $t_current), date('d/m/Y', $t_current));
				
				list($dt, $TAbsence) = each($TPlanning);
				
				foreach($TAbsence as $fk_user => $ouinon) {	
					$date = date('Y-m-d', $t_current);
					
					$presence = strpos($ouinon, '[Présence]') !== false; //TODO refondre un peu ça pour éviter cette grosse merde de strpos
					
					$estUnJourTravaille = TRH_EmploiTemps::estTravaille($PDOdb, $fk_user, $date);
					$estFerie = TRH_JoursFeries::estFerie($PDOdb, $date);
					
					@$Tab[$fk_user][$date]['presence_jour_entier'] = (int)($estUnJourTravaille=='OUI' && $ouinon=='non' && !$estFerie) ;
					@$Tab[$fk_user][$date]['presence'] = (int)(($estUnJourTravaille!='NON' && $ouinon=='non' && !$estFerie) || $presence) ;
					
					if($Tab[$fk_user][$date]['presence_jour_entier']==1)@$Tab[$fk_user][$date]['nb_jour_presence'] = 1;
					else if($Tab[$fk_user][$date]['presence_jour_entier']==0 && $Tab[$fk_user][$date]['presence']==1)@$Tab[$fk_user][$date]['nb_jour_presence'] = 0.5;
					else @$Tab[$fk_user][$date]['nb_jour_presence'] = 0;
					
					@$Tab[$fk_user][$date]['absence'] = (int)($ouinon!='non' && !$estFerie) ;
					
					if($Tab[$fk_user][$date]['absence']==1 && $estUnJourTravaille=='OUI')@$Tab[$fk_user][$date]['nb_jour_absence'] = 1;
					else if($Tab[$fk_user][$date]['absence']==1 && $estUnJourTravaille!='NON')@$Tab[$fk_user][$date]['nb_jour_absence'] = 0.5;
					else $Tab[$fk_user][$date]['nb_jour_absence'] = 0;
					 
					$TTime = TRH_EmploiTemps::getWorkingTimeForDayUser($PDOdb, $fk_user,$date);
					$t_am = $TTime['am'];
					$t_pm = $TTime['pm'];
					
					$Tab[$fk_user][$date]['t_am'] = $t_am;
					$Tab[$fk_user][$date]['t_pm'] = $t_pm;
										
					if ($Tab[$fk_user][$date]['nb_jour_presence'] == 1 || ($Tab[$fk_user][$date]['nb_jour_absence'] == 1 && $presence)) {
						$Tab[$fk_user][$date]['nb_heure_presence'] = $t_am + $t_pm;
					}
					else if ($Tab[$fk_user][$date]['nb_jour_presence']==0.5 && $estUnJourTravaille=='AM')
						$Tab[$fk_user][$date]['nb_heure_presence'] = $t_am; 
					else if ($Tab[$fk_user][$date]['nb_jour_presence']==0.5 && $estUnJourTravaille=='PM')
						$Tab[$fk_user][$date]['nb_heure_presence'] = $t_pm;
					else
						$Tab[$fk_user][$date]['nb_heure_presence'] = 0;
				
					if($Tab[$fk_user][$date]['nb_jour_absence']==1)$Tab[$fk_user][$date]['nb_heure_absence'] = $t_am + $t_pm;
					else if($Tab[$fk_user][$date]['nb_jour_absence']==0.5 && $estUnJourTravaille=='AM')$Tab[$fk_user][$date]['nb_heure_absence'] = $t_am; 
					else if($Tab[$fk_user][$date]['nb_jour_absence']==0.5 && $estUnJourTravaille=='PM')$Tab[$fk_user][$date]['nb_heure_absence'] = $t_pm; 
					else $Tab[$fk_user][$date]['nb_heure_absence'] = 0;
				
					@$Tab[$fk_user][$date]['ferie'] = (int)$estFerie ;
					
					$Tab[$fk_user][$date]['nb_jour_ferie'] = ($Tab[$fk_user][$date]['ferie'] && $estUnJourTravaille!='NON') ? 1:0;
					
					$Tab[$fk_user][$date]['estUnJourTravaille'] = $estUnJourTravaille;
					$Tab[$fk_user][$date]['typeAbsence'] = $ouinon;
					
					$timePresencePresume = $Tab[$fk_user][$date]['nb_heure_presence'] * 3600;
					
					$Tab[$fk_user][$date]['nb_heure_presence_reelle'] = TRH_Pointeuse::tempsTravailReelDuJour(
							$PDOdb
							, $fk_user
							, $date
							, $timePresencePresume
						); // en heure
					
					$Tab[$fk_user][$date]['nb_heure_suplementaire'] = $Tab[$fk_user][$date]['nb_heure_presence_reelle'] - $Tab[$fk_user][$date]['nb_heure_presence']; 
					
				}
				
				$t_current = strtotime('+1day', $t_current);
			}
			
			return $Tab;	
	}
	
	//fonction qui va renvoyer la requête sql de recherche pour le planning
	function requetePlanningAbsence(&$PDOdb, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin){
			// TODO cette fonction est une horreur, à recoder
			
		global $conf;
		
		if(!is_array($idGroupeRecherche)) { 
			$idGroupeRecherche = array($idGroupeRecherche);
		}
		
		

		if(array_sum($idGroupeRecherche)>0){	//on recherche un groupe précis
		
				$sql="SELECT  a.rowid as 'ID', u.rowid as 'idUser', u.login, u.lastname,u.firstname, DATE_FORMAT(a.date_debut, '%d/%m/%Y') as 'date_debut', 
					DATE_FORMAT(a.date_fin, '%d/%m/%Y') as 'date_fin', a.libelle, a.libelleEtat, a.ddMoment, a.dfMoment,ta.isPresence,ta.colorId, a.commentaire
					FROM ".MAIN_DB_PREFIX."rh_absence as a LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user=u.rowid)
					LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_type_absence as ta ON (a.type=ta.typeAbsence)
					LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (g.fk_user=u.rowid)
					WHERE 1 ";
					
					$sql.= " AND g.fk_usergroup IN (".implode(',',$idGroupeRecherche).")";
				
				$sql.=" AND a.etat!='Refusee'
					AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
					OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
					OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
					OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
				
			
		}
		
		else if($idUserRecherche>0){	//on recherche une  personne précis
	
			$sql="SELECT  a.rowid as 'ID', u.rowid as 'idUser', u.login, u.lastname,u.firstname, DATE_FORMAT(a.date_debut, '%d/%m/%Y') as 'date_debut', 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as 'date_fin', a.libelle, a.libelleEtat, a.ddMoment, a.dfMoment,ta.isPresence,ta.colorId, a.commentaire
				FROM ".MAIN_DB_PREFIX."rh_absence as a LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user=u.rowid)
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_type_absence as ta ON (a.type=ta.typeAbsence)
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (g.fk_user=u.rowid)
				WHERE a.fk_user=".$idUserRecherche."
				AND a.etat!='Refusee'
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
				
		}
		else
		{	//on recherche pour tous les utilisateurs
			$sql="SELECT a.rowid as 'ID',  u.rowid as 'idUser', u.login, u.lastname, u.firstname, 
				DATE_FORMAT(a.date_debut, '%d/%m/%Y') as date_debut, a.ddMoment, a.dfMoment, a.commentaire,
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as date_fin, a.libelle, a.libelleEtat,ta.isPresence,ta.colorId
				FROM ".MAIN_DB_PREFIX."rh_absence as a LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (a.fk_user=u.rowid)
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_type_absence as ta ON (a.type=ta.typeAbsence)
				LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (g.fk_user=u.rowid)
				WHERE a.fk_user=u.rowid 
				AND a.etat!='Refusee'
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin
				)";
		}
	
		// on traite la recherche pour le planning
		$k=0;
		$PDOdb->Execute($sql);
		$TabLogin=array();
		while ($PDOdb->Get_line()) {
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['date_debut']=$PDOdb->Get_field('date_debut');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['date_fin']=$PDOdb->Get_field('date_fin');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['idUser']=$PDOdb->Get_field('idUser');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['type']=$PDOdb->Get_field('libelle');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['ddMoment']=$PDOdb->Get_field('ddMoment');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['dfMoment']=$PDOdb->Get_field('dfMoment');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['isPresence']=$PDOdb->Get_field('isPresence');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['colorId']=$PDOdb->Get_field('colorId');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['commentaire']=$PDOdb->Get_field('commentaire');
			$TabAbsence[$PDOdb->Get_field('idUser')][$k]['idAbsence']=$PDOdb->Get_field('ID');
			
			
			$k++;
		}
		
		//on récupère les différents utilisateurs concernés par la recherche
		
		if($idUserRecherche>0) {
			$sql="SELECT u.rowid, u.login, u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u WHERE rowid=".$idUserRecherche;
		}
		else if(array_sum($idGroupeRecherche)>0 ) {	//on recherche un groupe précis
			$sql="SELECT u.rowid, u.login, u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
			WHERE u.rowid=g.fk_user AND g.fk_usergroup IN (".implode(',',$idGroupeRecherche).") AND u.statut=1 ORDER BY u.lastname";
		}else{
			$sql="SELECT rowid, login, lastname, firstname FROM ".MAIN_DB_PREFIX."user WHERE statut=1";
		}
		$PDOdb->Execute($sql);
		while ($PDOdb->Get_line()) {
			$TabLogin[$PDOdb->Get_field('rowid')]=$PDOdb->Get_field('firstname')." ".$PDOdb->Get_field('lastname');
		}
		
		if($conf->global->RH_PLANNING_SEARCH_MODE == 'INTERSECTION') {
		// élimination des users non présent dans tous les groupes. AA peu opti mais je n'ai guère le choix si je veux pas refondre toutes la requête	
			
			foreach($TabLogin as $idUser=>$row) {
				
				$TLGroup = array();
				
				$sql="SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE fk_user=".$idUser;
				
				$PDOdb->Execute($sql);
				while($obj = $PDOdb->Get_line()) {
					$TLGroup[] = $obj->fk_usergroup;
				}
				
				foreach($idGroupeRecherche as $idGroup) {
					
					if($idGroup>0) {
						
						if(!in_array($idGroup, $TLGroup)) {
							unset($TabLogin[$idUser]);
						}else{
							break;
						}
						
					}
					
				}
				
				
			}
						
		}
		
		
		
		$jourFin=strtotime(str_replace("/","-",$date_fin));
		$jourDebut=strtotime(str_replace("/","-",$date_debut));
		
		$TRetour=array();
		//on remplit le tableau de non
		foreach ($TabLogin as $id=>$user) {
			$jourDebut=strtotime(str_replace("/","-",$date_debut));
			//echo "ici".$id." ";
			while($jourFin>=$jourDebut){
					$TRetour[date('d/m/Y',$jourDebut)][$id]=new TRH_absenceDay;
					$jourDebut=strtotime('+1day',$jourDebut);
			}
		}
		
		
		
		
		foreach ($TabLogin as $id=>$user) {
			$jourDebut=strtotime(str_replace("/","-",$date_debut)); //TODO so moche
			if(!empty($TabAbsence[$id])){
				foreach($TabAbsence as $tabAbs){
					//print_r($tabAbs[$k]);exit;
					foreach($tabAbs as $key=>$value){
						$jourDebut=strtotime(str_replace("/","-",$date_debut));
						//print_r($value);exit;
						if($value['idUser']==$id){
							while($jourFin>=$jourDebut){
								if($TRetour[date('d/m/Y',$jourDebut)][$id]=='non'){
										
									$moment=new TRH_absenceDay;
									if(strtotime(str_replace("/","-",$value['date_debut']))<=$jourDebut&&strtotime(str_replace("/","-",$value['date_fin']))>=$jourDebut){
										if($jourDebut==strtotime(str_replace("/","-",$value['date_debut']))&&$jourDebut==strtotime(str_replace("/","-",$value['date_fin']))){
											if($value['ddMoment']==$value['dfMoment']){
												if($value['ddMoment']=='matin'){
													$moment->AM = true;
												}else $moment->PM = true;
											}
										}else if($jourDebut==strtotime(str_replace("/","-",$value['date_debut']))){
											if($value['ddMoment']=='matin'){
												$moment->DAM=$moment->AM=true;
											}else $moment->DPM=$moment->PM=true;
										}else if($jourDebut==strtotime(str_replace("/","-",$value['date_fin']))){
											if($value['dfMoment']=='matin'){
												$moment->FAM=$moment->AM=true;
											}else $moment->FPM=$moment->PM=true;
										}
										
										if($value['isPresence']>0) $moment->isPresence = 1;
										$moment->label = $value['type'];
										$moment->description = $value['commentaire'];
										$moment->colorId = $value['colorId'];
										$moment->date = date('Y-m-d', $jourDebut);
										 
										$moment->idAbsence =  $value['idAbsence'];
										 
										$TRetour[date('d/m/Y',$jourDebut)][$id]=$moment;
										 
									}else{
										
										$TRetour[date('d/m/Y',$jourDebut)][$id]=new TRH_absenceDay;
									}
								}
								//$typeTemp=$value['type'];
								$jourDebut=strtotime('+1day',$jourDebut);
							}
						}
						
					}
					

				}
			}else{
				//echo "ici".$id." ";
				while($jourFin>=$jourDebut){
						$TRetour[date('d/m/Y',$jourDebut)][$id]=new TRH_absenceDay;
						$jourDebut=strtotime('+1day',$jourDebut);
				}
			}
			
		}
		//print_r($TRetour);
		return $TRetour;
	}		
}


//définition de la classe pour l'administration des compteurs
class TRH_AdminCompteur extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_admin_compteur');
		parent::add_champs('congesAcquisMensuelInit,congesAcquisAnnuelInit','type=float;');
		parent::add_champs('rttCumuleInit,rttNonCumuleInit','type=float;');
		parent::add_champs('date_rttClotureInit','type=date;');
		parent::add_champs('date_congesClotureInit','type=date;');				

		parent::add_champs('entity','type=entier;index;');

					
		parent::_init_vars();
		parent::start();	
		
		
		$this->date_rttClotureInit=strtotime(date('01-01-Y',strtotime('1year')));
		$this->date_congesClotureInit=strtotime(date('Y-05-31'));
		
	}
	
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
	function loadCompteur(&$db) {
	global $conf;
		
		$sql="SELECT rowid FROM ".$this->get_table()." 
		WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")";
		$db->Execute($sql);
		
		$db->Get_line();
		
		$this->load($db, $db->Get_field('rowid'));
		
	}
	
}

//définition de la classe pour l'emploi du temps des salariés
class TRH_EmploiTemps extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_absence_emploitemps');
		
		//demi-journées de travail
		$this->TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		foreach ($this->TJour as $jour) {
			parent::add_champs($jour.'am','type=entier;');
			parent::add_champs($jour.'pm','type=entier;');
			parent::add_champs($jour.'_is_tempspartiel','type=entier;');
		}
		
		//horaires de travail
		foreach ($this->TJour as $jour) {
			parent::add_champs('date_'.$jour.'_heuredam','type=date;');
			parent::add_champs('date_'.$jour.'_heurefam','type=date;');		
			parent::add_champs('date_'.$jour.'_heuredpm','type=date;');
			parent::add_champs('date_'.$jour.'_heurefpm','type=date;');
		}
					
		parent::add_champs('fk_user','type=entier;index;');	//utilisateur concerné
		parent::add_champs('tempsHebdo','type=float;');
		
		parent::add_champs('entity,is_archive','type=entier;index;');
		
		parent::add_champs('date_debut,date_fin', array('type'=>'date'));
		
		parent::_init_vars();
		parent::start();	

		$this->is_archive=0;
	}
	
	function loadByuser(&$PDOdb, $id_user) {
		return $this->load_by_fkuser($PDOdb, $id_user); // TODO remove double
		
		
	}

	function load_entities(&$PDOdb){
		$sql="SELECT label, rowid FROM ".MAIN_DB_PREFIX."entity";
		$PDOdb->Execute($sql);
		$this->TEntity=array();
		while($PDOdb->Get_line()) {
			$this->TEntity[$PDOdb->Get_field('rowid')]=$PDOdb->Get_field('label');
		}
		return $this->TEntity;
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		parent::save($db);
	}
	
	function initCompteurHoraire (&$PDOdb, $idUser){
		global $conf;
		$this->entity = $conf->entity;
	
		$this->fk_user=$idUser;
		$this->lundiam=1;
		$this->lundipm=1;
		$this->mardiam=1;
		$this->mardipm=1;
		$this->mercrediam=1;
		$this->mercredipm=1;
		$this->jeudiam=1;
		$this->jeudipm=1;
		$this->vendrediam=1;
		$this->vendredipm=1;
		$this->samediam=0;
		$this->samedipm=0;
		$this->dimancheam=0;
		$this->dimancheam=0;
		
		foreach ($this->TJour as $jour) {
			if($jour!='samedi' && $jour!='dimanche'&&$jour!='vendredi') {
				 $this->{'date_'.$jour."_heuredam"}= strtotime('8:15');
				 $this->{'date_'.$jour."_heurefam"}=strtotime('12:00');
				 $this->{'date_'.$jour."_heuredpm"}=strtotime('14:00');
				 $this->{'date_'.$jour."_heurefpm"}=strtotime('17:45');
			}
			elseif($jour=='vendredi'){
				 $this->{'date_'.$jour."_heuredam"}= strtotime('8:15');
				 $this->{'date_'.$jour."_heurefam"}=strtotime('12:00');
				 $this->{'date_'.$jour."_heuredpm"}=strtotime('14:00');
				 $this->{'date_'.$jour."_heurefpm"}=strtotime('17:15');
			}
			else{
				$this->{'date_'.$jour."_heuredam"}=$this->{'date_'.$jour."_heurefam"}=$this->{'date_'.$jour."_heuredpm"}=$this->{'date_'.$jour."_heurefpm"}= strtotime('0:00');
			}
		}
		$this->tempsHebdo=37;
	
	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function razCheckbox(&$PDOdb){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		foreach ($this->TJour as $jour) {
			$this->{$jour."am"}=0;
			 $this->{$jour."pm"}=0;
			 $this->{$jour."_is_tempspartiel"}=0;
		}
	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function calculTempsHebdo(&$PDOdb, $edt){
		
		
		$tpsHebdo='0:0';
		
		$tps = 0;
		
		foreach ($edt->TJour as $jour) {
			if($edt->{$jour."am"}=="1"){
				//echo $edt->{"date_".$jour."_heuredam"}.$edt->{"date_".$jour."_heurefam"};exit;
				//$tpsHebdo=additionnerHeure($tpsHebdo,difheure(date('h:i',$edt->{"date_".$jour."_heuredam"}), date('h:i',$edt->{"date_".$jour."_heurefam"})));
			
				$tps += $edt->{"date_".$jour."_heurefam"} - $edt->{"date_".$jour."_heuredam"};	
			}
			
			if($edt->{$jour."pm"}=="1"){
				//$tpsHebdo=additionnerHeure($tpsHebdo,difheure(date('h:i',$edt->{"date_".$jour."_heuredpm"}), date('h:i',$edt->{"date_".$jour."_heurefpm"})));

				$tps += $edt->{"date_".$jour."_heurefpm"} - $edt->{"date_".$jour."_heuredpm"};	
				
			}
		}
	
		$nbHeure = $tps / 60 / 60;
		//print "$nbHeure";
	    return $nbHeure;
	
		//return horaireMinuteEnCentieme($tpsHebdo);
	}
	

	//fonction permettant le chargement de l'emploi du temps d'un user si celui-ci existe	
	function load_by_fkuser(&$PDOdb, $fk_user, $date=''){
		
		
		if(!empty($date)) {
			$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps
			WHERE fk_user=".(int)$fk_user." AND is_archive=1 
			AND date_debut<='$date 23:59:59'  AND date_fin>='$date 00:00:00'";
			
			$PDOdb->Execute($sql);
			if($row = $PDOdb->Get_line()) {
				
				$id = $row->rowid;
				
			}
			
		}
		
		if(empty($date) || empty($id)) {
			$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps
			WHERE fk_user=".(int)$fk_user." AND is_archive!=1";
			
			$PDOdb->Execute($sql);
		
			if($PDOdb->Get_line()) {
				$id = $PDOdb->Get_field('rowid');	
			}
			
		}
				
		if(!empty($id)) return $this->load($PDOdb, $id);
		else return false;
		
	}
	
	function getHeures($date) {
				
		$iJour = (int)date('N', strtotime($date)) - 1 ; 	
		
		$jour = $this->TJour[$iJour];
	//	exit($date.' '.$iJour.' '.$jour);
		return array(
			$this->{"date_".$jour."_heuredam"}
			,$this->{"date_".$jour."_heurefam"}
			,$this->{"date_".$jour."_heuredpm"}
			,$this->{"date_".$jour."_heurefpm"}
		);
		
	}
	
	function getHeurePeriode($current_day,$periode){
		return ($this->{"date_".$current_day."_heuref".$periode} - $this->{"date_".$current_day."_heured".$periode}) / 3600;
	}	
	
	static function estTravaille(&$PDOdb, $id_user, $date) {
		global $db,$TCacheUserDateEntree;
		
		if(!isset($TRHCacheUserDateEntree))$TRHCacheUserDateEntree=array();
		if(empty($TRHCacheUserDateEntree[$id_user])) {
			$u =new User($db);
			$u->fetch($id_user);
			
			$TRHCacheUserDateEntree[$id_user] = $u->array_options['options_DDA'];
		}
///		var_dump($TRHCacheUserDateEntree[$id_user], $date);exit;
		if(!empty($TRHCacheUserDateEntree[$id_user]) && strtotime($TRHCacheUserDateEntree[$id_user]) > strtotime($date) ) return 'NON';
		
		$e=new TRH_EmploiTemps;
		$e->load_by_fkuser($PDOdb, $id_user, $date);
		
		$iJour = (int)date('N', strtotime($date)) - 1 ; 	
		
		$jour = $e->TJour[$iJour];
		
		if($e->{$jour.'am'} && $e->{$jour.'pm'})return 'OUI';
		else if($e->{$jour.'am'}) return 'AM';
		else if($e->{$jour.'pm'}) return 'PM';
		else return 'NON';
		
	}
	
	static function getWorkingTimeForDayUser($PDOdb, $fk_user, $date) {
				
			
			$emploiTemps = new TRH_EmploiTemps;
			$emploiTemps->load_by_fkuser($PDOdb, $fk_user,$date);

			$iJour = (int)date('N', strtotime($date)) - 1 ; 	
		
			$jour = $emploiTemps->TJour[$iJour];	

			$ret =  array(
				'am' => $emploiTemps->getHeurePeriode($jour, 'am')
				,'pm' => $emploiTemps->getHeurePeriode($jour, 'pm')
			
			);
			
			return $ret;
			
	}
	
	function estJourTempsPartiel($jour) {
		
		return $this->{$jour.'_is_tempspartiel'};
		
	}
	
}


//définition de la classe pour la gestion des règles
class TRH_RegleAbsence extends TObjetStd {	
	static $TPeriode =array(
		'ONE'=>'Pour chaque plage'
		,'MONTH'=>'Mois'
		,'YEAR'=>"Année"
	);
	
	function __construct() {
		global $langs;
		 
		parent::set_table(MAIN_DB_PREFIX.'rh_absence_regle');
		parent::add_champs('typeAbsence','type=chaine;');
		parent::add_champs('choixApplication,periode','type=chaine;index;');
		parent::add_champs('nbJourCumulable','type=float;index;');
		parent::add_champs('restrictif,contigue,contigueNoJNT','type=entier;');
		parent::add_champs('fk_user','type=entier;index;');	//utilisateur concerné
		parent::add_champs('fk_usergroup','type=entier;index;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;index;');
		
		
		parent::_init_vars();
		parent::start();	
		
		$this->choixApplication = 'all';
		
		$this->TUser = array();
		$this->TGroup  = array();
		$this->TChoixApplication = array(
			'all'=> $langs->trans('AllThis')
			,'group'=> $langs->trans('ApplicationChoiceGroup')
			,'user'=> $langs->trans('ApplicationChoiceUser')
		);
		
		$this->periode ='ONE';
		
	}
	
	function save(&$PDOdb) {
		global $conf;
		$this->entity = $conf->entity;
		
		switch ($this->choixApplication){
			case 'all':
				$this->fk_user = NULL;
				$this->fk_usergroup=NULL;
				break;
			case 'user':
				$this->fk_usergroup = NULL;
				break;
			case 'group':
				$this->fk_user = NULL;
				break;
			default : 
				echo'pbchoixapplication';
				break;				
		}
		
		parent::save($PDOdb);
	}

	
	
	function load_liste(&$PDOdb){
		global $conf;

		//LISTE DE GROUPES
		$this->TGroup  = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TGroup[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('nom'), ENT_COMPAT , 'UTF-8');
		}
		
		//LISTE DE USERS
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user";
		$PDOdb->Execute($sqlReq);
		while($PDOdb->Get_line()) {
			$this->TUser[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('firstname'), ENT_COMPAT , 'UTF-8').' '.htmlentities($PDOdb->Get_field('lastname'), ENT_COMPAT , 'UTF-8');
		}
	}
}


//définition de la classe pour la gestion des règles
class TRH_TypeAbsence extends TObjetStd {
	function __construct() {
		global $langs;
		
		parent::set_table(MAIN_DB_PREFIX.'rh_type_absence');
		parent::add_champs('typeAbsence','type=chaine;index;');
		parent::add_champs('libelleAbsence','type=chaine;index;');
		parent::add_champs('codeAbsence','type=chaine;index;');
		parent::add_champs('admin,insecable','type=entier;index;');
		parent::add_champs('unite','type=chaine;');
		parent::add_champs('entity,isPresence,colorId','type=entier;index;');
		
		parent::add_champs('decompteNormal','type=chaine;');
		
		parent::add_champs('date_hourStart,date_hourEnd','type=date;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TIsPresence=array(
			0=> $langs->trans('Absence')
			,1=> $langs->trans('Presence')
		);
		
		$this->TDecompteNormal=array(
			'oui'=> $langs->trans('Yes')
			,'non'=> $langs->trans('No')
		);
		
		$this->TForAdmin=array(
			0=> $langs->trans('No')
			,1=> $langs->trans('Yes')
		);
	
		$this->TUnite=array(
			'jour'=> $langs->trans('Day')
			,'heure'=> $langs->trans('Hour')
		);
		
		$this->TColorId=array(
			0=>$langs->trans('Default')
			,1=>'rouge'
			,2=>'rose'
			,3=>'violet'
			,4=>'pourpre'
			,5=>'bleu nuit'
			,6=>'bleu'
			,7=>'cyan'
			,8=>'vert'
			,9=>'vert sombre'
			,10=>'vert clair'
			,11=>'vert jaune'
			,12=>'jaune'
			,13=>'orange'
			,14=>'orange sanguin'
			,15=>'rose sombre'
		);
		
	}
	
	function load_by_type(&$PDOdb, $type) {
		
		return parent::loadBy($PDOdb, $type, 'typeAbsence');
		
	}
    static function getUnsecable(&$PDOdb) {
        
        return TRequeteCore::get_id_from_what_you_want($PDOdb, MAIN_DB_PREFIX."rh_type_absence", 'insecable=1', 'typeAbsence');
        
        
    }
    
	function save(&$PDOdb) {
		global $conf;
		
		$this->entity = $conf->entity;
		
		parent::save($PDOdb);
	}
	 
	static function getColor($i,$theme=0) {
		
		$color = "666666d96666e67399b373b38c66d9668cb3668cd959bfb365ad894cb0528cbf40bfbf4de0c240f2a640e6804dbe9494";
	
		$hex = substr($color, $theme*30 +  $i*6, 6);
		
		if(empty($hex)) return '';
		
		return '#'.$hex;
		
	}
	
	static function getList(&$PDOdb, $isPresence=false) {
		global $conf;
		
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_type_absence
		WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.") 
		AND isPresence=".(int)$isPresence."
		ORDER BY typeAbsence";
		
		$Tab = TRequeteCore::_get_id_by_sql($PDOdb, $sql);
		$TAbsenceType=array();
		
		foreach($Tab as $id) {
			
			$a=new TRH_TypeAbsence;
			$a->load($PDOdb, $id);
			
			$TAbsenceType[] = $a;
		}
		
		return $TAbsenceType;
	}
	
	static function getTypeAbsence(&$PDOdb, $type='', $isPresence=false) {
	/* Retourne un tableau code => label */		
		$Tab=array();
		
		if($type=='user') {
			
			$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
				WHERE admin=0 AND isPresence=".(int)$isPresence."
				ORDER BY libelleAbsence
				"
				;
			$PDOdb->Execute($sql);
			while($PDOdb->Get_line()) {
				$Tab[$PDOdb->Get_field('typeAbsence')]=$PDOdb->Get_field('libelleAbsence');
			}
			
		}
		else if($type=='valideur') {

			$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
					WHERE (admin=0 OR typeAbsence LIKE 'nonjustifiee') AND isPresence=".(int)$isPresence."
					ORDER BY libelleAbsence
					";
			$PDOdb->Execute($sql);
			while($PDOdb->Get_line()) {
				$Tab[$PDOdb->Get_field('typeAbsence')]=$PDOdb->Get_field('libelleAbsence');
			}	

		}
		else {

			$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
				WHERE isPresence=".(int)$isPresence."
				ORDER BY libelleAbsence
				"
				;
			$PDOdb->Execute($sql);
			while($PDOdb->Get_line()) {
				$Tab[$PDOdb->Get_field('typeAbsence')]=$PDOdb->Get_field('libelleAbsence');
			}

		}
		
		return $Tab;

	}
	
}

		
