<?php

//TRH_CONGE
//classe pour la définition d'une absence 
class TRH_Compteur extends TObjetStd {
	function __construct() { /* declaration */
		
		//conges N
		parent::set_table(MAIN_DB_PREFIX.'rh_compteur');
		parent::add_champs('acquisExerciceN','type=float;');				
		parent::add_champs('acquisAncienneteN','type=float;');				
		parent::add_champs('acquisHorsPeriodeN','type=float;');											
		parent::add_champs('anneeN','type=int;');					
		parent::add_champs('dureeN','type=entier;');
		parent::add_champs('date_congesCloture','type=date;');	//date de clôture période rtt
		parent::add_champs('nombreCongesAcquisMensuel','type=float;');
		
		//conges N-1
		parent::add_champs('acquisExerciceNM1','type=float;');				
		parent::add_champs('acquisAncienneteNM1','type=float;');				
		parent::add_champs('acquisHorsPeriodeNM1','type=float;');				
		parent::add_champs('reportCongesNM1','type=float;');				
		parent::add_champs('congesPrisNM1','type=float;');			
		parent::add_champs('congesTotalNM1','type=float;');	
		parent::add_champs('congesResteNM1','type=float;');
		parent::add_champs('anneeNM1','type=int;');					
		parent::add_champs('dureeNM1','type=entier;');				
		
		//RTT cumulés 
		parent::add_champs('rttCumulePris','type=float;');
		parent::add_champs('rttAcquisAnnuelCumuleInit','type=float;');
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
		parent::add_champs('rttannee','type=int;');	
		parent::add_champs('rttMetier','type=chaine;');		
		parent::add_champs('date_rttCloture','type=date;');	//date de clôture période rtt
		
		parent::add_champs('reportRtt','type=int;');	//entier (0 ou 1) pour savoir si l'on reporte les RTT d'une année à l'autre
		

		parent::add_champs('entity','type=int;');					
	
		
		parent::_init_vars();
		parent::start();
		
		$this->TTypeAcquisition = array('Annuel'=>'Annuel','Mensuel'=>'Mensuel');
		
		
	}
	
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
	
	function initCompteur(&$ATMdb, $idUser){
		global $conf;
		$this->entity = $conf->entity;
		$annee=date('Y');
		$anneePrec=$annee-1;

		$this->fk_user=$idUser;
		$this->acquisExerciceN=0; 
		$this->acquisAncienneteN=0;
		$this->acquisHorsPeriodeN=0;
		$this->anneeN=$annee;
		$this->acquisExerciceNM1=25;
		$this->acquisAncienneteNM1=0;
		$this->acquisHorsPeriodeNM1=0;
		$this->reportCongesNM1=0;
		$this->congesPrisNM1=0;
		$this->anneeNM1=$anneePrec;
		$this->rttTypeAcquisition='Annuel';
		
		$this->rttAcquisMensuelInit=0;
		
		
		$this->rttCumuleAcquis=5;
		$this->rttAcquisAnnuelCumuleInit=5;
		$this->rttCumuleReportNM1=0;
		$this->rttCumulePris=0;
		$this->rttCumuleTotal=$this->rttCumuleAcquis+$this->rttCumuleReportNM1-$this->rttCumulePris;
		
		$this->rttNonCumuleAcquis=7;
		$this->rttNonCumuleReportNM1=0;
		$this->rttAcquisAnnuelNonCumuleInit=7;
		$this->rttNonCumulePris=0;
		$this->rttNonCumuleTotal=$this->rttNonCumuleAcquis+$this->rttNonCumuleReportNM1-$this->rttNonCumulePris;
		
		
		$this->rttMetier='noncadre37cpro';
		$this->rttannee=$annee;
		$this->nombreCongesAcquisMensuel=2.08;
		$this->date_rttCloture=strtotime(DATE_RTT_CLOTURE); // AA Ne devrait pas être en dur mais en config
		$this->date_congesCloture=strtotime(DATE_CONGES_CLOTURE);
		$this->reportRtt=0;
	}
	

	//	fonction permettant le chargement du compteur pour un utilisateur si celui-ci existe	
	function load_by_fkuser(&$ATMdb, $fk_user){

		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_compteur 
		WHERE fk_user=".$fk_user;

		$ATMdb->Execute($sql);
		if ($ATMdb->Get_line()) {
			
			$this->load($ATMdb, $ATMdb->Get_field('rowid'));
			
		}
		return false;
	}
	
}




//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		
		global $user,$conf;
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('code','type=varchar;index;');				//code  congé
		parent::add_champs('type','type=varchar;index;');				//type de congé
		parent::add_champs('libelle','type=varchar;');				//type de congé
		parent::add_champs('date_debut,date_fin, date_validation','type=date;index;');	//dates debut fin de congés
		parent::add_champs('date_hourStart,date_hourEnd','type=date;');	//dates debut fin de congés
		parent::add_champs('ddMoment, dfMoment','type=chaine;');		//moment (matin ou après midi)
		parent::add_champs('duree','type=float;');	
		parent::add_champs('dureeHeure','type=chaine;');	
		parent::add_champs('dureeHeurePaie','type=chaine;');
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('commentaireValideur','type=chaine;');		//commentaire
		parent::add_champs('etat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('avertissement','type=int;');	
		parent::add_champs('libelleEtat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('niveauValidation','type=entier;');	//niveau de validation
		parent::add_champs('idAbsImport','type=entier;index;');	//niveau de validation
		parent::add_champs('fk_user, fk_user_valideur','type=entier;index;');	//utilisateur concerné
		parent::add_champs('entity','type=int;');	
		
		parent::_init_vars();
		parent::start();
		
		$this->TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		$this->Tjoursem = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'); 
		
		
		$ATMdb=new TPDOdb;
		
		
		
		//combo pour le choix de matin ou après midi 
		$this->TddMoment = array('matin'=>'Matin','apresmidi'=>'Après-midi');	//moment de date début
		$this->TdfMoment = array('matin'=>'Matin','apresmidi'=>'Après-midi');	//moment de date fin
		
		//on crée un tableau des utilisateurs pour l'afficher en combo box, et ensuite sélectionner quelles absences afficher
		
		$this->TEtat=array(
			'Validee'=>'Acceptée'
			,'Refusee'=>'Refusée'
			,'Avalider'=>'En attente de validation'
		
		);
		
		$this->date_validation=0;
		
	}

	//renvoie le tableau des utilisateurs
	function recupererTUser(&$ATMdb){
		global $conf;
		$TUser=array();
		$TUser[0] = 'Tous';	
		$sqlReqUser="SELECT rowid, lastname,  firstname FROM `".MAIN_DB_PREFIX."user` 
						ORDER BY lastname";
		$ATMdb->Execute($sqlReqUser);

		while($ATMdb->Get_line()) {
			$TUser[$ATMdb->Get_field('rowid')]=htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1');
		}
		return $TUser;
	}


	//permet la récupération des règles liées à un utilisateur 
	//utile lors de l'affichage à la création d'une demande d'absence
	function recuperationRegleUser(&$ATMdb, $fk_user){
		global $conf;
		
		
		$sql="SELECT DISTINCT r.typeAbsence, r.`nbJourCumulable`, r. `restrictif`, 
		r.fk_user, r.fk_usergroup, r.choixApplication
		FROM ".MAIN_DB_PREFIX."usergroup_user as g, ".MAIN_DB_PREFIX."rh_absence_regle as r
		WHERE r.choixApplication Like 'user' AND r.fk_user=".$fk_user."
		OR (r.choixApplication Like 'all')
		OR (r.choixApplication Like 'group' AND r.fk_usergroup=g.fk_usergroup AND g.fk_user=".$fk_user.") 
		ORDER BY r.nbJourCumulable";

		$ATMdb->Execute($sql);
		$TRegle = array();
		$k=0;
		while($ATMdb->Get_line()) {
			$TRegle[$k]['typeAbsence']= $ATMdb->Get_field('typeAbsence');
			$TRegle[$k]['libelle']= saveLibelle($ATMdb->Get_field('typeAbsence'));
			$TRegle[$k]['nbJourCumulable']= $ATMdb->Get_field('nbJourCumulable');
			$TRegle[$k]['restrictif']= $ATMdb->Get_field('restrictif');
			$TRegle[$k]['fk_user']= $ATMdb->Get_field('fk_user');
			$TRegle[$k]['fk_usergroup']= $ATMdb->Get_field('fk_usergroup');
			$TRegle[$k]['choixApplication']= $ATMdb->Get_field('choixApplication');
			$k++;
		}

		return $TRegle;

	}
	
	
	
	//permet la récupération des règles liées à un utilisateur 
	//utile lors de l'affichage à la création d'une demande d'absence
	function recuperationDerAbsUser(&$ATMdb, $fk_user){
		global $conf;
		$sql="SELECT DATE_FORMAT(date_debut, '%d/%m/%Y') as 'dateD', 
		DATE_FORMAT(date_fin, '%d/%m/%Y')  as 'dateF', libelle, libelleEtat 
		FROM `".MAIN_DB_PREFIX."rh_absence` WHERE fk_user=".$fk_user." 
		GROUP BY date_cre LIMIT 0,10";

		$ATMdb->Execute($sql);
		$TRecap=array();
		$k=0;
		while($ATMdb->Get_line()) {		
			$TRecap[$k]['date_debut']=$ATMdb->Get_field('dateD');
			$TRecap[$k]['date_fin']=$ATMdb->Get_field('dateF');
			$TRecap[$k]['libelle']=$ATMdb->Get_field('libelle');
			$TRecap[$k]['libelleEtat']=$ATMdb->Get_field('libelleEtat');
			$k++;
		}
		return $TRecap;
	}
	

	function testDemande(&$db, $userConcerne, &$absence){
		$ATMdb=new TPDOdb;
		global $conf, $user;
		$this->entity = $conf->entity;
		
		
		
		//on calcule la duree de l'absence, en décomptant jours fériés et jours non travaillés par le collaborateur
		/*$dureeAbsenceCourante=$this->calculDureeAbsence($db, $this->date_debut, $this->date_fin, $absence);
		$dureeJourConge=$this->calculJoursFeries($db, 0, $this->date_debut, $this->date_fin, $absence);
		
		$dureeJourNonTravaille=$this->calculJoursTravailles($db, 0, $this->date_debut, $this->date_fin, $absence); 
		
		$dureeAbsenceCourante += ($dureeJourConge + $dureeJourNonTravaille) ;
		*/
		$dureeAbsenceCourante = $this->calculDureeAbsenceParAddition($db);
		
		//autres paramètes à sauvegarder
		$this->libelle=saveLibelle($this->type);
		$this->duree=$dureeAbsenceCourante;
		$this->etat="Avalider";
		$this->libelleEtat=saveLibelleEtat($this->etat);
		
		//on teste s'il y a des règles qui s'appliquent à cette demande d'absence
		//$this->findRegleUser($db);
		$dureeAbsenceRecevable=$this->dureeAbsenceRecevable($ATMdb);
		
	
		if($dureeAbsenceRecevable==0){
			return 0;
		}
		
		
			//print "$dureeAbsenceCourante = ($dureeJourConge + $dureeJourNonTravaille) ".$this->type ;
		
	
		//on teste si c'est une demande de jours non cumulés, 
		//si les jours N-1 début absence et N+1 fin absence sont travaillés
		//////////////////// FINALEMENT AUCUNE REGLES SUR LES RTT NON CUMULES : LAISSER PLACE AUX EXCEPTIONS
		/*if($this->type=='rttnoncumule'){
			$absenceAutoriseeDebut=$this->isWorkingDayPrevious($ATMdb, $this->date_debut);// AA plus simple 1fct -> isWorkingDay($ATMdb, strtotime( '-1day', $this->date_debut) )
			$absenceAutoriseeFin=$this->isWorkingDayNext($ATMdb, $this->date_fin);// AA plus simple 1fct -> isWorkingDay($ATMdb, strtotime( '+1day', $this->date_fin) )
			if($absenceAutoriseeDebut==0||$absenceAutoriseeFin==0){
				return 3; //etat pour le message d'erreur lié aux rtt non cumulés
			}

		}*/
		
		
		//on récupère la méthode d'acquisition des jours de l'utilisateur en cours : si par mois ou annuel
		$sqlMethode="SELECT rttTypeAcquisition FROM `".MAIN_DB_PREFIX."rh_compteur` 
		WHERE fk_user=".$userConcerne;
		$ATMdb->Execute($sqlMethode);
		while($ATMdb->Get_line()) {
			$methode= $ATMdb->Get_field('rttTypeAcquisition');
		}
		
		///////décompte des congés
		if($this->type=="rttcumule"){//&&$methode=="Annuel"){
			
			$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` 
				SET rttCumulePris=0+rttCumulePris+".$dureeAbsenceCourante.", rttCumuleTotal=rttCumuleTotal-".$dureeAbsenceCourante." 
				WHERE fk_user=".$userConcerne;
			
			$db->Execute($sqlDecompte);
			//$this->rttCumulePris=$this->rttCumulePris+$dureeAbsenceCourante;
			
		}
		else if($this->type=="rttnoncumule"){//&&$methode=="Annuel"){
			$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` 
				SET rttNonCumulePris=rttNonCumulePris+".$dureeAbsenceCourante.", rttNonCumuleTotal=rttNonCumuleTotal-".$dureeAbsenceCourante." 
				WHERE fk_user=".$userConcerne;
			$db->Execute($sqlDecompte);
			//$this->rttNonCumulePris=$this->rttNonCumulePris-$dureeAbsenceCourante;
		}
		else if($this->type=="conges"||$this->type=="cppartiel"){	//autre que RTT : décompte les congés
		
			$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` 
				SET congesPrisNM1=congesPrisNM1+".$dureeAbsenceCourante." 
				WHERE fk_user=".$userConcerne;
		/*	print $sqlDecompte;	
				
				exit("  $dureeAbsenceCourante $userConcerne");*/
			$db->Execute($sqlDecompte);
			$this->congesResteNM1=$this->congesResteNM1-$dureeAbsenceCourante;
		}
		
		return $dureeAbsenceRecevable;
	}
		
		
	function save(&$db) {

		global $conf, $user;
		$this->entity = $conf->entity;
		parent::save($db);
	}

	/*
	 * Faire une fonction qui marche
	 */
	 
	function getJourFerie(&$ATMdb) {
		
		$ATMdb->Execute("SELECT date_jourOff, moment FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries 
		WHERE date_jourOff BETWEEN '".date('Y-m-d 00:00:00', $this->date_debut)."' AND '".date('Y-m-d 23:59:59', $this->date_fin)."'");
		
		$Tab=array();
		while($ATMdb->Get_line()) {
		
			$moment = $ATMdb->Get_field('moment');
			$date_jourOff = date('Y-m-d', strtotime($ATMdb->Get_field('date_jourOff')));
			
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
	function calculDureeAbsenceParAddition(&$ATMdb) {
		global $TJourNonTravailleEntreprise, $langs;
		
		$TJourSemaine = array('dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi');
		$TJourFerie = $this->getJourFerie($ATMdb);	
		
	//	print_r($TJourFerie);
		
		$duree = 0;
		
		$this->dureeHeure=0;
		
		$t_start = $this->date_debut;
		$t_end = $this->date_fin;
		$t_current = $t_start;
		
		$typeAbs = new TRH_TypeAbsence;
		
		$typeAbs->load_by_type($ATMdb, $this->type);
		//print_r($typeAbs);
		$emploiTemps = new TRH_EmploiTemps;
		$emploiTemps->load_by_fkuser($ATMdb, $this->fk_user);
		
		while($t_current<=$t_end) {
			//print date('Y-m-d', $t_current).'<br>';;
			$current_day = $TJourSemaine[(int)date('w', $t_current)];
			if(!in_array($current_day, $TJourNonTravailleEntreprise)) {
				
			/*	
				if(date('Y-m-d', $t_current)=='2013-08-13') {
					print "$t_current==$t_start {$this->ddMoment} {$this->dfMoment}";
				//	print_r( debug_backtrace() );
				}
				*/
				if( ($t_current==$t_start && $this->ddMoment=='matin') || $t_current>$t_start  ) {
					if(!isset($TJourFerie[ date('Y-m-d', $t_current) ]['am'])) {
	
						if($emploiTemps->{$current_day.'am'}==1 ) {
							$duree+=.5;
							$this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"am");
						}		
						else if($typeAbs->decompteNormal=='non' && $emploiTemps->{$current_day.'am'}==0 ) {
							$duree+=.5;
							$this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"am");
						}
						
					}
				}
				
				
				if(($t_current==$t_end && $this->dfMoment=='apresmidi') || $t_current<$t_end  ) {
				
				
					if(!isset($TJourFerie[ date('Y-m-d', $t_current) ]['pm'])) {
	
						if($emploiTemps->{$current_day.'pm'}==1 ) {
							$duree+=.5;
							$this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"pm");
						}		
						else if($typeAbs->decompteNormal=='non' && $emploiTemps->{$current_day.'pm'}==0 ) {
							$duree+=.5;
							$this->dureeHeure += $emploiTemps->getHeurePeriode($current_day,"pm");
						}
						
					}
				
				}
				//print "$current_day<br>";

			}
			
			
			$t_current = strtotime("+1day",$t_current);
		}
		
		if($emploiTemps->tempsHebdo > 35){
			$this->dureeHeurePaie=7*$duree;
		}
		else{
			$this->dureeHeurePaie=$this->dureeHeure;
		} 
		
		return $duree;
	}
	
	//calcul de la durée initiale de l'absence (sans jours fériés, sans les jours travaillés du salariés)
	function calculDureeAbsence(&$ATMdb, $date_debut, $date_fin, &$absence){
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

		return $duree; 
	}
	
	
	//calcul la durée de l'absence après le décompte des jours fériés
	function calculJoursFeries(&$ATMdb, $duree, $date_debut, $date_fin, &$absence){
			
		global $conf, $TJourNonTravailleEntreprise;		
		//on cherche s'il existe un ou plusieurs jours fériés  entre la date de début et de fin d'absence
		$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries`";
		$ATMdb->Execute($sql);
		$Tab = array();
		while($ATMdb->Get_line()) {
			$Tab[date('Y-m-d', strtotime($ATMdb->Get_field('date_jourOff')))]= array(
				'rowid'=>$ATMdb->Get_field('rowid')
				,'moment'=>$ATMdb->Get_field('moment')
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
		$ATMdb->Execute($sql);
		$Tab = array();
		while($ATMdb->Get_line()) {
			$Tab[$ATMdb->Get_field('rowid')]= array(
				'date_jourOff'=>$ATMdb->Get_field('date_jourOff')
				,'moment'=>$ATMdb->Get_field('moment')
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

		
	function calculJoursTravailles(&$ATMdb, $duree, $date_debut, $date_fin, &$absence){
		/*
		 * Cette fonction est ignoble, à retravailler !
		 */
		global $conf, $TJourNonTravailleEntreprise;
		
		//on récupère l'information permettant de savoir si l'on doit décompter les jours normalement ou non
		$sql="SELECT decompteNormal FROM ".MAIN_DB_PREFIX."rh_type_absence WHERE typeAbsence LIKE '".$absence->type."'";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()) {
			$decompteNormal=$ATMdb->Get_field('decompteNormal');
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
		$ATMdb->Execute($sql);
		$TabFerie = array();
		
		while($ATMdb->Get_line()) {
			$TabFerie[$ATMdb->Get_field('rowid')]= array(
				'date_jourOff'=>$ATMdb->Get_field('date_jourOff')
				,'moment'=>$ATMdb->Get_field('moment')
				);
			
		}			
		
		//on cherche le temps total de travail d'un employé par semaine : 
		//cela va permettre de savoir si la durée d'une absence doit être limité à 7h par jour et 35h par semaine ou non
		//si $tempsTravail supérieur à 35h, on limite les durées
		//$tempsTravail=$this->calculTempsTravailHebdo($ATMdb,$this->fk_user);
		
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
		WHERE fk_user=".$absence->fk_user;  
//print $sql;
		$ATMdb->Execute($sql);
		$TTravail = array();
		$TTravailHeure= array();
		while($ATMdb->Get_line()) {
			foreach ($absence->TJour as $jour) {
				foreach(array('am','pm') as $moment) {
					$TTravail[$jour.$moment]=$ATMdb->Get_field($jour.$moment);
					
				}
				foreach(array('dam','fam','dpm','fpm') as $moment) {
					$TTravailHeure["date_".$jour."_heure".$moment]=$ATMdb->Get_field("date_".$jour."_heure".$moment);
				}
			}
			$rowid=$ATMdb->Get_field('rowid');
			$tpsHebdoUser=$ATMdb->Get_field('tempsHebdo');
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
	function recrediterHeure(&$ATMdb){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		if($this->etat!='Refusee'){
			
			switch($this->type){
				case "rttcumule" : 
						$sqlRecredit="UPDATE ".MAIN_DB_PREFIX."rh_compteur 
						SET rttCumulePris=rttCumulePris-".$this->duree.", rttCumuleTotal=rttCumuleTotal+".$this->duree." 
						where fk_user=".$this->fk_user;
						$ATMdb->Execute($sqlRecredit);	
				break;
				case "rttnoncumule" : 
						$sqlRecredit="UPDATE `".MAIN_DB_PREFIX."rh_compteur` 
						SET rttNonCumulePris=rttNonCumulePris-".$this->duree.", rttNonCumuleTotal=rttNonCumuleTotal+".$this->duree."  
						where fk_user=".$this->fk_user;
						$ATMdb->Execute($sqlRecredit);
				break;
				case 'conges':
				case 'cppartiel':
					$sqlRecredit="UPDATE `".MAIN_DB_PREFIX."rh_compteur` 
					SET congesPrisNM1=congesPrisNM1-".$this->duree."  
					where fk_user=".$this->fk_user;
					$ATMdb->Execute($sqlRecredit);
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

	
	function dureeAbsenceRecevable(&$ATMdb){
		$avertissement=0;
		$TRegle=$this->recuperationRegleUser($ATMdb,$this->fk_user);
		if(!empty($TRegle)){
			foreach($TRegle as $TR){
				if($TR['typeAbsence']==$this->type){
					if($this->duree>$TR['nbJourCumulable']){
						if($TR['restrictif']==1){
								 return 0;
						}
						else $avertissement=2;  //"Attention, le nombre de jours dépasse la règle"
					}
				}
			}
		}
		
		if($avertissement==0){
			return 1;
		}
		return $avertissement;
	}
	
	/*function isWorkingDayNext(&$ATMdb, $dateTest){

		$dateNext=strtotime('+1day',$dateTest); // +3600*24; // AA cf mon autre comm, quand l'horloge change d'heure ceci fonctionne mal
		//$jourNext=$this->jourSemaine($dateNext);
		
		//on teste si c'est un jour férié
		$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries`";
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			if($this->php2Date($dateNext)==$ATMdb->Get_field('date_jourOff')&&$ATMdb->Get_field('date_jourOff')!='apresmidi'){
				return 0;
			}
		}
		
		//on teste si le jour suivant est un rtt cumulé ou un congés ce qui est interdit
		$sql="SELECT rowid, date_debut, dfMoment FROM ".MAIN_DB_PREFIX."rh_absence 
		WHERE date_debut LIKE '".$this->php2Date($dateNext)."'
		AND (type LIKE 'rttcumule' OR type LIKE 'conges') 
		AND etat <> 'refusee'"; 
		$ATMdb->Execute($sql);

		while($ATMdb->Get_line()) {
			//echo $this->php2Date($dateNext);
			if($this->php2Date($dateNext)==$ATMdb->Get_field('date_debut')&&$ATMdb->Get_field('date_debut')!='apresmidi'){
				return 0;
			}
		}
		return 1;
	}
	
	function isWorkingDayPrevious(&$ATMdb, $dateTest){

		$datePrec=$dateTest-3600*24;
		//$jourPrec=$this->jourSemaine($datePrec);
		
		//on teste si c'est un jour férié
		$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries` WHERE date_jourOff between '"
		.$jourPrec."' and '". $this->php2Date($this->date_debut)."'"; 
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()) {
			if($this->php2Date($dateTest)==$ATMdb->Get_field('date_jourOff')){
				return 0;
			}
		}
		
		//on teste si le jour précédent est un rtt cumulé ou un congés ce qui est interdit
		$sql="SELECT rowid, date_debut, dfMoment FROM ".MAIN_DB_PREFIX."rh_absence 
		WHERE date_fin LIKE '".$this->php2Date($datePrec)."'
		AND (type LIKE 'rttcumule' OR type LIKE 'conges')
		AND etat <> 'refusee'"; 
		$ATMdb->Execute($sql);

		while($ATMdb->Get_line()) {
			//echo $this->php2Date($datePrec);
			if($this->php2Date($datePrec)==$ATMdb->Get_field('date_fin')&&$ATMdb->Get_field('date_fin')!='matin'){
				return 0;
			}
		}
		return 1;
	}*/


	///////////////FONCTIONS pour le fichier rechercheAbsence\\\\\\\\\\\\\\\\\\\\\\\\\\\
	
	//va permettre la création de la requête pour les recherches d'absence pour les collaborateurs
	function requeteRechercheAbsence(&$ATMdb, $idGroupeRecherche, $idUserRecherche, $horsConges, $date_debut, $date_fin, $typeAbsence){
			
			if($horsConges==1){ //on recherche uniquement une compétence
				$sql=$this->rechercheAucunConges($ATMdb,$idGroupeRecherche, $idUserRecherche,$date_debut, $date_fin, $typeAbsence);
			}
			else if($idGroupeRecherche!=0&&$idUserRecherche==0){ //on recherche les absences d'un groupe
				$sql=$this->rechercheAbsenceGroupe($ATMdb, $idGroupeRecherche, $date_debut, $date_fin, $typeAbsence);
			}
			else{ //if($idUserRecherche!=0){ //on recherche les absences d'un utilisateur
				$sql=$this->rechercheAbsenceUser($ATMdb,$idUserRecherche, $date_debut, $date_fin, $typeAbsence);
			}
			return $sql;
	}
	
	//requete avec groupe de collaborateurs précis
	function rechercheAbsenceGroupe(&$ATMdb, $idGroupeRecherche, $date_debut, $date_fin, $typeAbsence){ 
			global $conf;
			
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
			
			if($typeAbsence!='Tous'){
				$sql.=" AND a.type LIKE '".$typeAbsence."'";
			}
			
			return $sql;
	}
	
	//requete renvoyant les utilisateurs n'ayant pas pris de congés pendant une période
	function rechercheAucunConges(&$ATMdb, $idGroupeRecherche,$idUserRecherche, $date_debut, $date_fin, $typeAbsence){ 
			global $conf;

			if($idUserRecherche!=0){
				
				$sql="SELECT DISTINCT u.login, u.lastname, u.firstname
				FROM ".MAIN_DB_PREFIX."user as u 
				WHERE u.rowid =".$idUserRecherche." AND u.rowid NOT IN (
							SELECT a.fk_user 
							FROM ".MAIN_DB_PREFIX."rh_absence as a
							WHERE a.fk_user=".$idUserRecherche." AND
							(a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)
							";
				if($typeAbsence!='Tous'){
					$sql.=" AND a.type LIKE '".$typeAbsence."' ";
				}
				$sql.=")";
			}
			//	on recherche les utilisateurs n'ayant pas eu d'absences pendant la période désirée
			else if($idGroupeRecherche==0){ 
				$sql="SELECT DISTINCT u.login, u.lastname, u.firstname
				FROM ".MAIN_DB_PREFIX."user as u 
				WHERE u.rowid NOT IN (
							SELECT a.fk_user 
							FROM ".MAIN_DB_PREFIX."rh_absence as a
							WHERE 
							(a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)
							";
				if($typeAbsence!='Tous'){
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
				AND g.fk_user NOT IN (
							SELECT a.fk_user 
							FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."usergroup_user as g
							WHERE g.fk_user=u.rowid 
							AND g.fk_usergroup=".$idGroupeRecherche." 
							AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
							OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
				if($typeAbsence!='Tous'){
					$sql.=" AND a.type LIKE '".$typeAbsence."' ";
				}
				$sql.=")";
			}
			    
			return $sql;
	}

	//requete avec un collaborateur précis
	function rechercheAbsenceUser(&$ATMdb,$idUserRecherche, $date_debut, $date_fin, $typeAbsence){
			global $conf;

			//on recherche les absences d'un utilisateur pendant la période
			$sql="SELECT a.rowid as 'ID',  u.login, u.lastname, u.firstname, 
				DATE_FORMAT(a.date_debut, '%d/%m/%Y') as date_debut, 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as date_fin, a.libelle, a.libelleEtat
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
				WHERE a.fk_user=u.rowid 
				
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin
				)";
			if($idUserRecherche!=0){
				$sql.=" AND a.fk_user=".$idUserRecherche;
			}
			if($typeAbsence!='Tous'){
				$sql.=" AND a.type LIKE '".$typeAbsence."'";
			}
			
			return $sql;
	}
	
	//	fonction permettant le chargement de l'absence pour un utilisateur si celle-ci existe	
	function load_by_idImport(&$ATMdb, $idImport){
		global $conf;
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_absence 
		WHERE idAbsImport=".$idImport;

		$ATMdb->Execute($sql);
		if ($ATMdb->Get_line()) {
			return $this->load($ATMdb, $ATMdb->Get_field('rowid'));
		}
		return false;
	}
	
	
	//fonction qui renvoie 1 si une absence existe déjà pendant la date que l'on veut ajouter, 0 sinon
	function testExisteDeja($ATMdb, $absence){
			
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
		//print date('Y-m-d H:i:s', $date_debut).' '.date('Y-m-d H:i:s', $date_fin);	
		//on récupère toutes les date d'absences du collaborateur
		$sql="SELECT date_debut, date_fin, ddMoment, dfMoment 
		FROM ".MAIN_DB_PREFIX."rh_absence 
		WHERE fk_user=".$absence->fk_user." AND etat IN ('Validee','Avalider')
		";

		$ATMdb->Execute($sql);
		$k=0;
		
		$TAbs=array();
		while($ATMdb->Get_line()) {
			$TAbs[$k]['date_debut']=strtotime($ATMdb->Get_field('date_debut'));
			$TAbs[$k]['date_fin']=strtotime($ATMdb->Get_field('date_fin')) + 86399;
			
			/*$TAbs[$k]['ddMoment']=strtotime($ATMdb->Get_field('ddMoment'));
			$TAbs[$k]['dfMoment']=strtotime($ATMdb->Get_field('dfMoment'));*/
			
			if($ATMdb->Get_field('ddMoment')=='apresmidi') $TAbs[$k]['date_debut'] = strtotime( date('Y-m-d 12:00:00', $TAbs[$k]['date_debut']) );
			if($ATMdb->Get_field('dfMoment')=='matin') $TAbs[$k]['date_fin'] = strtotime( date('Y-m-d 11:59:59', $TAbs[$k]['date_fin']) );
			
			
			$k++;
		}
		//print_r($TAbs);
		if(!empty($TAbs)){
				foreach($TAbs as $dateAbs){
					//on traite le début de l'absence
					//print_r($dateAbs);
					
					/*print date('Y-m-d H:i:s', $date_debut).' - '.date('Y-m-d H:i:s', $date_fin).' - '.date('Y-m-d H:i:s',$dateAbs['date_debut']).' - '
					.date('Y-m-d H:i:s', $dateAbs['date_fin']).' - 	<br>';*/
					if(( $date_debut >= $dateAbs['date_debut'] && $date_debut<=$dateAbs['date_fin'])
						||( $date_fin >= $dateAbs['date_debut'] && $date_fin<=$dateAbs['date_fin']))
					 {
			/*		 	exit( "non" );*/
						return date('Y-m-d H:i',$dateAbs['date_debut'])." au ".date('Y-m-d H:i', $dateAbs['date_fin']);
						
					}
						/*
					if($absence->date_debut<$dateAbs['date_debut'] && $absence->date_fin>$dateAbs['date_fin']) return 1;
					
					//on traite la fin de l'absence	
					if($absence->date_debut>$dateAbs['date_debut'] && $absence->date_fin<$dateAbs['date_fin']) return 1;*/
				}
		 }
			
		//exit();
		
		
		
		return false;
	}
	
	//fonction qui va renvoyer la requête sql de recherche pour le planning
	function requetePlanningAbsence(&$ATMdb, $idGroupeRecherche, $idUserRecherche, $date_debut, $date_fin){
		global $conf;
		
		
		if($idUserRecherche>0){	//on recherche une  personne précis
	
			$sql="SELECT  a.rowid as 'ID', u.rowid as 'idUser', u.login, u.lastname,u.firstname, DATE_FORMAT(a.date_debut, '%d/%m/%Y') as 'date_debut', 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as 'date_fin', a.libelle, a.libelleEtat, a.ddMoment, a.dfMoment
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
				WHERE a.fk_user=u.rowid 
				AND  g.fk_user=u.rowid
				AND u.rowid=".$idUserRecherche."
				AND a.etat!='Refusee'
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
			//	print $sql;
		}

		else if($idGroupeRecherche>0){	//on recherche un groupe précis
			$sql="SELECT  a.rowid as 'ID', u.rowid as 'idUser', u.login, u.lastname,u.firstname, DATE_FORMAT(a.date_debut, '%d/%m/%Y') as 'date_debut', 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as 'date_fin', a.libelle, a.libelleEtat, a.ddMoment, a.dfMoment
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
				WHERE a.fk_user=u.rowid 
				AND  g.fk_user=u.rowid
				AND g.fk_usergroup=".$idGroupeRecherche."
				AND a.etat!='Refusee'
				AND (a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR a.date_fin between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' between a.date_debut AND a.date_fin
				OR '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."' between a.date_debut AND a.date_fin)";
		}
		
		else
		{	//on recherche pour tous les utilisateurs
			$sql="SELECT a.rowid as 'ID',  u.rowid as 'idUser', u.login, u.lastname, u.firstname, 
				DATE_FORMAT(a.date_debut, '%d/%m/%Y') as date_debut, a.ddMoment, a.dfMoment,
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as date_fin, a.libelle, a.libelleEtat
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
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
		$ATMdb->Execute($sql);
		$TabLogin=array();
		while ($ATMdb->Get_line()) {
			$TabAbsence[$ATMdb->Get_field('idUser')][$k]['date_debut']=$ATMdb->Get_field('date_debut');
			$TabAbsence[$ATMdb->Get_field('idUser')][$k]['date_fin']=$ATMdb->Get_field('date_fin');
			$TabAbsence[$ATMdb->Get_field('idUser')][$k]['idUser']=$ATMdb->Get_field('idUser');
			$TabAbsence[$ATMdb->Get_field('idUser')][$k]['type']=$ATMdb->Get_field('libelle');
			$TabAbsence[$ATMdb->Get_field('idUser')][$k]['ddMoment']=$ATMdb->Get_field('ddMoment');
			$TabAbsence[$ATMdb->Get_field('idUser')][$k]['dfMoment']=$ATMdb->Get_field('dfMoment');
			
			
			$k++;
		}
	
	
		
		//on récupère les différents utilisateurs concernés par la recherche
		
		if($idUserRecherche>0) {
			$sql="SELECT u.rowid, u.login, u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u WHERE rowid=".$idUserRecherche;
		}
		else if($idGroupeRecherche!=0){	//on recherche un groupe précis
			$sql="SELECT u.rowid, u.login, u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
			WHERE u.rowid=g.fk_user AND g.fk_usergroup=".$idGroupeRecherche." ORDER BY u.lastname";
		}else{
			$sql="SELECT rowid, login, lastname, firstname FROM ".MAIN_DB_PREFIX."user";
		}
		$ATMdb->Execute($sql);
		while ($ATMdb->Get_line()) {
			$TabLogin[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('firstname')." ".$ATMdb->Get_field('lastname');
		}
		
		$jourFin=strtotime(str_replace("/","-",$date_fin));
		$jourDebut=strtotime(str_replace("/","-",$date_debut));
		
		$TRetour=array();
		//on remplit le tableau de non
		foreach ($TabLogin as $id=>$user) {
			$jourDebut=strtotime(str_replace("/","-",$date_debut));
			//echo "ici".$id." ";
			while($jourFin>=$jourDebut){
					$TRetour[date('d/m/Y',$jourDebut)][$id]="non";
					$jourDebut=strtotime('+1day',$jourDebut);
			}
		}
		
		
		
		
		foreach ($TabLogin as $id=>$user) {
			$jourDebut=strtotime(str_replace("/","-",$date_debut));
			if(!empty($TabAbsence[$id])){
				foreach($TabAbsence as $tabAbs){
					//print_r($tabAbs[$k]);exit;
					foreach($tabAbs as $key=>$value){
						$jourDebut=strtotime(str_replace("/","-",$date_debut));
						//print_r($value);exit;
						if($value['idUser']==$id){
							while($jourFin>=$jourDebut){
								if($TRetour[date('d/m/Y',$jourDebut)][$id]=="non"){
									$moment="";
									if(strtotime(str_replace("/","-",$value['date_debut']))<=$jourDebut&&strtotime(str_replace("/","-",$value['date_fin']))>=$jourDebut){
										if($jourDebut==strtotime(str_replace("/","-",$value['date_debut']))&&$jourDebut==strtotime(str_replace("/","-",$value['date_fin']))){
											if($value['ddMoment']==$value['dfMoment']){
												if($value['ddMoment']=='matin'){
													$moment=" :  AM";
												}else $moment=" :  PM";
											}
										}else if($jourDebut==strtotime(str_replace("/","-",$value['date_debut']))){
											if($value['ddMoment']=='matin'){
												$moment=" : DAM";
											}else $moment=" : DPM";
										}else if($jourDebut==strtotime(str_replace("/","-",$value['date_fin']))){
											if($value['dfMoment']=='matin'){
												$moment=" : FAM";
											}else $moment=" : FPM";
										}
										$TRetour[date('d/m/Y',$jourDebut)][$id]=$value['type'].$moment;
									}else{
										
										$TRetour[date('d/m/Y',$jourDebut)][$id]="non";
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
						$TRetour[date('d/m/Y',$jourDebut)][$id]="non";
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
		parent::add_champs('congesAcquisMensuelInit','type=float;');
		parent::add_champs('rttCumuleInit','type=float;');
		parent::add_champs('date_rttClotureInit','type=date;');
		parent::add_champs('date_congesClotureInit','type=date;');				

		parent::add_champs('entity','type=entier;index;');

					
		parent::_init_vars();
		parent::start();	
		
		
		$this->date_rttClotureInit=strtotime(DATE_RTT_CLOTURE);
		$this->date_congesClotureInit=strtotime(DATE_CONGES_CLOTURE);
		
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
		parent::add_champs('societeRtt','type=chaine;');
		parent::add_champs('entity','type=int;index;');
		
		parent::_init_vars();
		parent::start();	
	}
	
	function loadByuser(&$ATMdb, $id_user) {
		$res = TRequeteCore::get_id_from_what_you_want($ATMdb, $this->get_table(), array('fk_user'=> $id_user));
		if(!empty($res)) {
			return $this->load($ATMdb, $res[0]);
			
		}
		else {
			return false;
		}
		
	}

	function load_entities(&$ATMdb){
		$sql="SELECT label, rowid FROM ".MAIN_DB_PREFIX."entity";
		$ATMdb->Execute($sql);
		$this->TEntity=array();
		while($ATMdb->Get_line()) {
			$this->TEntity[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('label');
		}
		return $this->TEntity;
	}
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		parent::save($db);
	}
	
	function initCompteurHoraire (&$ATMdb, $idUser){
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
		$this->societeRtt='aucune';

	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function razCheckbox(&$ATMdb){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		foreach ($this->TJour as $jour) {
			$this->{$jour."am"}=0;
			 $this->{$jour."pm"}=0;
		}
	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function calculTempsHebdo(&$ATMdb, $edt){
		
		
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
	function load_by_fkuser(&$ATMdb, $fk_user){
		global $conf;
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_absence_emploitemps
		WHERE fk_user=".$fk_user;
		
		$ATMdb->Execute($sql);
		
		if($ATMdb->Get_line()) {
			return $this->load($ATMdb, $ATMdb->Get_field('rowid'));
			
		}
		return false;
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
	
}


//définition de la classe pour l'administration des compteurs
class TRH_JoursFeries extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_absence_jours_feries');
		parent::add_champs('date_jourOff','type=date;');
		parent::add_champs('moment','type=chaine;');
		parent::add_champs('commentaire','type=chaine;');
		parent::add_champs('entity','type=int;');
		
		
		parent::_init_vars();
		parent::start();	
		
		$this->TFerie=array();
		$this->TMoment=array('allday'=>'Toute La journée', 'matin'=>'Matin', 'apresmidi'=>'Après-midi');
		
		$this->moment = 'allday'; 		
	}
	
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		if(!$this->testExisteDeja($db)) {
			parent::save($db);	
		}

	}

	
	//fonction qui renvoie 1 si le jour férié que l'on veut créer existe déjà à la date souhaitée, sinon 0
	function testExisteDeja(&$ATMdb){
		global $conf;
		//on récupère toutes les dates de jours fériés existant
		$sql="SELECT count(*) as 'nb'  FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries
			 WHERE date_jourOff='".$this->get_date('date_jourOff','Y-m-d')."' AND rowid!=".$this->getId();
		$ATMdb->Execute($sql);
		$obj = $ATMdb->Get_line();
			
		//on teste si l'un d'eux est égal à celui que l'on veut créer
		if($obj->nb > 0){
			return 1;	
		}
		
		return 0;
	}
	
	static function estFerie(&$ATMdb, $date) {
		global $conf;
		//on récupère toutes les dates de jours fériés existant
		$sql="SELECT count(*) as 'nb'  FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries
			 WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")
			 AND  date_jourOff=".$ATMdb->quote($date);
			 
		$ATMdb->Execute($sql);
		$obj = $ATMdb->Get_line();
			
		//on teste si l'un d'eux est égal à celui que l'on veut créer
		if($obj->nb > 0){
			return true;	
		}
		
		return false;
		
		
	}
	
	static function syncronizeFromURL(&$ATMdb, $url) {
		
		$iCal = new ICalReader( 'http://www.google.com/calendar/ical/fr.french%23holiday%40group.v.calendar.google.com/public/basic.ics' );
		
		foreach($iCal->cal['VEVENT'] as $event) {
		
			if($event['STATUS']=='CONFIRMED') {
				
				$jf = new TRH_JoursFeries;
				$jf->commentaire = $event['SUMMARY'];
				
				$aaaa = substr($event['DTSTART'], 0,4);
				$mm = substr($event['DTSTART'], 4,2);
				$jj = substr($event['DTSTART'], 6,2);
				
				$jf->set_date('date_jourOff', $jj.'/'.$mm.'/'.$aaaa);
				
				$jf->save($ATMdb);
				
			}


		}
		
	}
	
	
	static function getAll(&$ATMdb, $date_start='', $date_end='') {
		global $conf;	
		
		$Tab=array();
			  //récupération des jours fériés 
		$sql2=" SELECT moment,commentaire,date_jourOff,rowid FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
		 WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")";
		 
		if(!empty($date_start) && !empty($date_end)) $sql2.="AND date_jourOff BETWEEN ".$ATMdb->quote($date_start)." AND ".$ATMdb->quote($date_end);
		 
		
		$ATMdb->Execute($sql2);
   		
	     while ($row = $ATMdb->Get_line()) {
			 $Tab[] =$row;
		  
	     }
		
		return $Tab;
	
}
	
}

//définition de la classe pour la gestion des règles
class TRH_RegleAbsence extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_absence_regle');
		parent::add_champs('typeAbsence','type=chaine;');
		parent::add_champs('choixApplication','type=chaine;');
		parent::add_champs('nbJourCumulable','type=int;');
		parent::add_champs('restrictif','type=entier;');
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('fk_usergroup','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=int;');
		
		
		parent::_init_vars();
		parent::start();	
		
		$this->choixApplication = 'all';
		
		$this->TUser = array();
		$this->TGroup  = array();
		$this->TChoixApplication = array(
			'all'=>'Tous'
			,'group'=>'Groupe'
			,'user'=>'Utilisateur'
		);
	}
	
	function save(&$ATMdb) {
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
		
		parent::save($ATMdb);
	}

	
	
	function load_liste(&$ATMdb){
		global $conf;

		//LISTE DE GROUPES
		$this->TGroup  = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TGroup[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
		}
		
		//LISTE DE USERS
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1').' '.htmlentities($ATMdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1');
		}
	}
}


//définition de la classe pour la gestion des règles
class TRH_TypeAbsence extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_type_absence');
		parent::add_champs('typeAbsence','type=chaine;');
		parent::add_champs('libelleAbsence','type=chaine;');
		parent::add_champs('codeAbsence','type=chaine;index;');
		parent::add_champs('admin','type=int;');
		parent::add_champs('unite','type=chaine;');
		parent::add_champs('entity,isPresence,colorId','type=int;index;');
		
		parent::add_champs('decompteNormal','type=chaine;');
		
		parent::add_champs('date_hourStart,date_hourEnd','type=date;');
		
		parent::_init_vars();
		parent::start();
		
		$this->TIsPresence=array(
			0=>'Absence'
			,1=>'Présence'
		);
		
		$this->TDecompteNormal=array(
			'oui'=>'Oui'
			,'non'=>'Non'
		);
		
		$this->TForAdmin=array(
			0=>'Non'
			,1=>'Oui'
		);
	
		$this->TUnite=array(
			'jour'=>'Jour'
			,'heure'=>'Heure'
		);
		
		$this->TColorId=array(
			0=>'gris'
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
			,15=>'rose'
		);
		
	}
	
	function load_by_type(&$ATMdb, $type) {
		
		return parent::loadBy($ATMdb, $type, 'typeAbsence');
		
	}
	function save(&$ATMdb) {
		global $conf;
		
		$this->entity = $conf->entity;
		
		parent::save($ATMdb);
	}
	static function getList(&$ATMdb, $isPresence=false) {
		global $conf;
		
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_type_absence
		WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.") 
		AND isPresence=".(int)$isPresence."
		ORDER BY typeAbsence";
		
		$Tab = TRequeteCore::_get_id_by_sql($ATMdb, $sql);
		$TAbsenceType=array();
		
		foreach($Tab as $id) {
			
			$a=new TRH_TypeAbsence;
			$a->load($ATMdb, $id);
			
			$TAbsenceType[] = $a;
		}
		
		return $TAbsenceType;
	}
	
	static function getTypeAbsence(&$ATMdb, $type='', $isPresence=false) {
	/* Retourne un tableau code => label */		
		$Tab=array();
		
		if($type=='user') {
			
			$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
				WHERE admin=0 AND isPresence=".(int)$isPresence."
				ORDER BY libelleAbsence
				"
				;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
			}
			
		}
		else if($type=='valideur') {

			$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
					WHERE (admin=0 OR typeAbsence LIKE 'nonjustifiee') AND isPresence=".(int)$isPresence."
					ORDER BY libelleAbsence
					";
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
			}	

		}
		else {

			$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
				WHERE isPresence=".(int)$isPresence."
				ORDER BY libelleAbsence
				"
				;
			$ATMdb->Execute($sql);
			while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
			}

		}
		
		return $Tab;

	}
	
}

		