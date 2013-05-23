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
		
		//RTT
		parent::add_champs('fk_user','type=entier;');			//utilisateur concerné
		parent::add_champs('rttPris','type=float;');					
		parent::add_champs('rttTypeAcquisition','type=chaine;');				//heure, jour...
		parent::add_champs('rttAcquisMensuel','type=float;');	
		parent::add_champs('rttAcquisAnnuelCumule','type=float;');
		parent::add_champs('rttAcquisAnnuelNonCumule','type=float;');
		
		parent::add_champs('rttannee','type=int;');	
		parent::add_champs('rttMetier','type=chaine;');		
		parent::add_champs('date_rttCloture','type=date;');	//date de clôture période rtt
		
		parent::add_champs('reportRtt','type=int;');	//entier (0 ou 1) pour savoir si l'on reporte les RTT d'une année à l'autre
		
		//paramètres globaux
		parent::add_champs('rttAcquisMensuelInit','type=float;');	
		parent::add_champs('rttAcquisMensuelTotal','type=float;');	
		parent::add_champs('rttAcquisAnnuelCumuleInit','type=float;');
		parent::add_champs('rttAcquisAnnuelNonCumuleInit','type=float;');
		
		parent::add_champs('entity','type=int;');					
					
		parent::_init_vars();
		parent::start();
		
		$this->TTypeAcquisition = array('Annuel'=>'Annuel','Mensuel'=>'Mensuel');
		$this->TMetier = array('cadre'=>'Cadre',
		'noncadre37cpro'=>'Non Cadre 37h C\'PRO','noncadre37cproinfo'=>'Non Cadre 37h C\'PRO Info',
		'noncadre38cpro'=>'Non Cadre 38h C\'PRO', 'noncadre38cproinfo'=>'Non Cadre 38h C\'PRO Info',
		'noncadre39'=>'Non Cadre 39h', 
		'autre'=>'Autre');
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
		$this->rttPris=0;
		$this->rttTypeAcquisition='Annuel';
		$this->rttAcquisMensuelInit=0;
		$this->rttAcquisMensuelTotal=0;
		$this->rttAcquisAnnuelCumuleInit=5;
		$this->rttAcquisAnnuelNonCumuleInit=7;
		$this->rttAcquisMensuel=0;
		$this->rttAcquisAnnuelCumule=5;
		$this->rttAcquisAnnuelNonCumule=7;
		$this->rttMetier='cadre';
		$this->rttannee=$annee;
		$this->nombreCongesAcquisMensuel=2.08;
		$this->date_rttCloture=strtotime('2013-03-01 00:00:00'); // AA Ne devrait pas être en dur mais en config
		$this->date_congesCloture=strtotime('2013-06-01 00:00:00');
	}
	
}




//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		
		global $user,$conf;
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('code','type=int;');				//code  congé
		parent::add_champs('type','type=varchar;');				//type de congé
		parent::add_champs('libelle','type=varchar;');				//type de congé
		parent::add_champs('date_debut,date_fin','type=date;');	//dates debut fin de congés
		parent::add_champs('ddMoment, dfMoment','type=chaine;');		//moment (matin ou après midi)
		parent::add_champs('duree','type=float;');	
		parent::add_champs('dureeHeure','type=chaine;');	
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('etat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('avertissement','type=int;');	
		parent::add_champs('libelleEtat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('niveauValidation','type=entier;');	//niveau de validation
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=int;');	
		
		parent::_init_vars();
		parent::start();
		
		$this->TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		$this->Tjoursem = array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'); 
		
		
		$ATMdb=new Tdb;
		
		//combo box pour le type d'absence admin
		$this->TTypeAbsenceAdmin=array();
		$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
		WHERE entity=".$conf->entity;
		$ATMdb->Execute($sql);

		while($ATMdb->Get_line()) {
			$this->TTypeAbsenceAdmin[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
		}
		
		
		//combo box pour le type d'absence utilisateur
		$this->TTypeAbsenceUser=array();
		$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
		WHERE entity=".$conf->entity." AND admin=0";
		$ATMdb->Execute($sql);

		while($ATMdb->Get_line()) {
			$this->TTypeAbsenceUser[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
		}
		
		
		//combo pour le choix de matin ou après midi 
		$this->TddMoment = array('matin'=>'Matin','apresmidi'=>'Après-midi');	//moment de date début
		$this->TdfMoment = array('matin'=>'Matin','apresmidi'=>'Après-midi');	//moment de date fin
		
		//on crée un tableau des utilisateurs pour l'afficher en combo box, et ensuite sélectionner quelles absences afficher
		
		 // AA Ne devrait pas être ici mais dans une fonction à l'afficahge quand on en a besoin !

		$this->TUser=array();
		$sqlReqUser="SELECT rowid, name,  firstname FROM `".MAIN_DB_PREFIX."user` WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReqUser);

		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')]=htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1')." ".htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
		
		
		//on sélectionne les règles relatives à un utilisateurs
		$sql="SELECT DISTINCT u.rowid, r.typeAbsence, r.`nbJourCumulable`, r. `restrictif`, r.fk_user, r.fk_usergroup, r.choixApplication
		FROM ".MAIN_DB_PREFIX."user as u,  ".MAIN_DB_PREFIX."usergroup_user as g, ".MAIN_DB_PREFIX."rh_absence_regle as r
		WHERE( r.fk_user=u.rowid AND r.fk_user=".$user->id." AND r.choixApplication Like 'user' AND g.fk_user=u.rowid) 
		OR (r.choixApplication Like 'all' AND u.rowid=".$user->id." and u.rowid=g.fk_user) 
		OR (r.choixApplication Like 'group' AND r.fk_usergroup=g.fk_usergroup AND u.rowid=g.fk_user AND g.fk_user=".$user->id.") 
		ORDER BY r.nbJourCumulable";

		$ATMdb->Execute($sql);
		$this->TRegle = array();
		$k=0;
		while($ATMdb->Get_line()) {
			$this->TRegle[$k]['rowid']= $ATMdb->Get_field('rowid');
			$this->TRegle[$k]['typeAbsence']= $ATMdb->Get_field('typeAbsence');
			$this->TRegle[$k]['libelle']= saveLibelle($ATMdb->Get_field('typeAbsence'));
			$this->TRegle[$k]['nbJourCumulable']= $ATMdb->Get_field('nbJourCumulable');
			$this->TRegle[$k]['restrictif']= $ATMdb->Get_field('restrictif');
			$this->TRegle[$k]['fk_user']= $ATMdb->Get_field('fk_user');
			$this->TRegle[$k]['fk_usergroup']= $ATMdb->Get_field('fk_usergroup');
			$this->TRegle[$k]['choixApplication']= $ATMdb->Get_field('choixApplication');
			$k++;
		}
		
		
	}


		function testDemande(&$db, $userConcerne, &$absence){
			$ATMdb=new Tdb;
			global $conf, $user;
			$this->entity = $conf->entity;
			
			
			//on calcule la duree de l'absence, en décomptant jours fériés et jours non travaillés par le collaborateur
			$dureeAbsenceCourante=$this->calculDureeAbsence($db, $this->date_debut, $this->date_fin, $absence);
			$dureeAbsenceCourante=$this->calculJoursFeries($db, $dureeAbsenceCourante, $this->date_debut, $this->date_fin, $absence);
			$dureeAbsenceCourante=$this->calculJoursTravailles($db, $dureeAbsenceCourante, $this->date_debut, $this->date_fin, $absence); 
			
			
			//autres paramètes à sauvegarder
			$this->libelle=saveLibelle($this->type);
			$this->duree=$dureeAbsenceCourante;
			$this->etat="Avalider";
			$this->libelleEtat=saveLibelleEtat($this->etat);
			
			//on teste s'il y a des règles qui s'appliquent à cette demande d'absence
			//$this->findRegleUser($db);
			$dureeAbsenceRecevable=$this->dureeAbsenceRecevable();
			
		
			if($dureeAbsenceRecevable==0){
				return 0;
			}
			
			//on teste si c'est une demande de jours non cumulés, 
			//si les jours N-1 début absence et N+1 fin absence sont travaillés
			if($this->type=='rttnoncumule'){
				$absenceAutoriseeDebut=$this->isWorkingDayPrevious($ATMdb, $this->date_debut);// AA plus simple 1fct -> isWorkingDay($ATMdb, strtotime( '-1day', $this->date_debut) )
				$absenceAutoriseeFin=$this->isWorkingDayNext($ATMdb, $this->date_fin);// AA plus simple 1fct -> isWorkingDay($ATMdb, strtotime( '+1day', $this->date_fin) )
				if($absenceAutoriseeDebut==0||$absenceAutoriseeFin==0){
					return 3; //etat pour le message d'erreur lié aux rtt non cumulés
				}
				//on teste finalement si le collaborateur n'a pas déjà pris un jour de rtt non cumulés dans les 2 mois précédents
				$absenceAutorisee1Jour2Mois=$this->rttnoncumuleDuree2mois($ATMdb, $this->date_debut);
				if($absenceAutorisee1Jour2Mois==0){
					return 4; //etat pour le message d'erreur lié aux rtt non cumulés, et indiquant qu'un seul jour peut être pris par 2 mois
				}
			}
			
			
			//on récupère la méthode d'acquisition des jours de l'utilisateur en cours : si par mois ou annuel
			$sqlMethode="SELECT rttTypeAcquisition FROM `".MAIN_DB_PREFIX."rh_compteur` WHERE fk_user=".$userConcerne;
			$ATMdb->Execute($sqlMethode);
			while($ATMdb->Get_line()) {
				$methode= $ATMdb->Get_field('rttTypeAcquisition');
			}
			
			///////décompte des congés
			if($this->type=="rttcumule"&&$methode=="Annuel"){
				$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule-".$dureeAbsenceCourante."  where fk_user=".$userConcerne;
				$db->Execute($sqlDecompte);
				$this->rttPris=$this->rttPris+$dureeAbsenceCourante;
				$this->rttAcquisAnnuelCumule=$this->rttAcquisAnnuelCumule-$dureeAbsenceCourante;
				
			}
			else if($this->type=="rttnoncumule"&&$methode=="Annuel"){
				$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante.",rttAcquisAnnuelNonCumule=rttAcquisAnnuelNonCumule-".$dureeAbsenceCourante." where fk_user=".$userConcerne;
				$db->Execute($sqlDecompte);
				$this->rttPris=$this->rttPris-$dureeAbsenceCourante;
				$this->rttAcquisAnnuelNonCumule=$this->rttAcquisAnnuelNonCumule-$dureeAbsenceCourante;
			}
			else if($this->type=="rttnoncumule"&&$methode=="Mensuel"||$this->type=="rttcumule"&&$methode=="Mensuel"){
				$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante.",rttAcquisMensuel=rttAcquisMensuel-".$dureeAbsenceCourante."  where fk_user=".$userConcerne;
				$db->Execute($sqlDecompte);
				$this->rttPris=$this->rttPris+$dureeAbsenceCourante;
				$this->rttAcquisMensuel=$this->rttAcquisMensuel-$dureeAbsenceCourante;
				
			}
			else if($this->type=="conges"){	//autre que RTT : décompte les congés
				$sqlDecompte="UPDATE `".MAIN_DB_PREFIX."rh_compteur` SET congesPrisNM1=congesPrisNM1+".$dureeAbsenceCourante." where fk_user=".$userConcerne;
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

		
		//calcul de la durée initiale de l'absence (sans jours fériés, sans les jours travaillés du salariés)
		function calculDureeAbsence(&$ATMdb, $date_debut, $date_fin, &$absence){
			$diff=$date_fin-$date_debut;
			$duree=$diff/3600/24;

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

			$dateDebutAbs=$absence->php2Date($date_debut);
			$dateFinAbs=$absence->php2Date($date_fin);
			
			//on cherche s'il existe un ou plusieurs jours fériés  entre la date de début et de fin d'absence
			$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries` WHERE date_jourOff between '"
			.$dateDebutAbs."' and '". $dateFinAbs."'"; 
			//echo $sql;
			$ATMdb->Execute($sql);
			$Tab = array();
			while($ATMdb->Get_line()) {
				$Tab[$ATMdb->Get_field('rowid')]= array(
					'date_jourOff'=>$ATMdb->Get_field('date_jourOff')
					,'moment'=>$ATMdb->Get_field('moment')
					);
			}
			//print "ddmoment =".$this->ddMoment. "   debutAbs".$dateDebutAbs."   fin abs".$dateFinAbs;
			//traitement pour chaque jour férié
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
				else{
					//echo "boucle4";
					if($jour['moment']=='allday'){
						$duree-=1;
					}else{
						$duree-=0.5;
					}
				}
			}
			return $duree;
		}

		
		function calculJoursTravailles(&$ATMdb, $duree, $date_debut, $date_fin, &$absence){
			
			global $conf;
			
						
			//traitement jour de début
			$dateDebutAbs=$absence->php2Date($date_debut);
			$jourDebutSem=$absence->jourSemaine($date_debut);
			
			//traitement jour de fin
			$dateFinAbs=$absence->php2Date($date_fin);
			$jourFinSem=$absence->jourSemaine($date_fin);
			
			//on récupère les jours fériés compris dans la demande d'absence
			$sql="SELECT * FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries` WHERE date_jourOff between '"
			.$dateDebutAbs."' and '". $dateFinAbs."' AND entity=".$conf->entity; 
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
			 
			FROM `".MAIN_DB_PREFIX."rh_absence_emploitemps` 
			WHERE fk_user=".$absence->fk_user." AND entity=".$conf->entity;  

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
				$rowid=$ATMdb->Get_field($rowid);
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
							$duree-=0.5;
						}else{
							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredam"], $TTravailHeure["date_".$jourDebutSem."_heurefam"]));
							$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);	

						}
					}else if($absence->ddMoment=='apresmidi'){		// si la date de debut est lapres midi, il n'y a donc que le cas pm à traiter
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}
						else{
							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredpm"], $TTravailHeure["date_".$jourDebutSem."_heurefpm"]));
							$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
						}
					}else{	//sinon on traite les cas matin et apres midi
						if($TTravail[$jourDebutSem.'am']==0){
							$duree-=0.5;
						}else{
							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredam"], $TTravailHeure["date_".$jourDebutSem."_heurefam"]));
							$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
						}
						
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}else{
							$absence->dureeHeure=$absence->additionnerHeure($absence->dureeHeure,$absence->difheure($TTravailHeure["date_".$jourDebutSem."_heuredpm"], $TTravailHeure["date_".$jourDebutSem."_heurefpm"]));
							$absence->dureeHeure=$absence->horaireMinuteEnCentieme($absence->dureeHeure);
						}
					}
					return $duree;
				}
				else return $duree;
				
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
							$duree-=0.5;
						}
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}
					}else if($absence->ddMoment=='apresmidi'){
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
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
							$duree-=0.5;
						}
					}else if($absence->dfMoment=='apresmidi'){
						if($TTravail[$jourFinSem.'am']==0){
							$duree-=0.5;
						}
						if($TTravail[$jourFinSem.'pm']==0){
							$duree-=0.5;
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
									$duree-=0.5;
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
				$jourEnCours=$jourEnCours+3600*24;
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
						//on récupère la méthode d'acquisition des jours de l'utilisateur en cours : si par mois ou annuel
						$sqlMethode="SELECT rttTypeAcquisition FROM `".MAIN_DB_PREFIX."rh_compteur` WHERE fk_user=".$user->id;
						$ATMdb->Execute($sqlMethode);
						while($ATMdb->Get_line()) {
							$methode= $ATMdb->Get_field('rttTypeAcquisition');
						}
						if($methode=="Annuel"){
							$sqlRecredit="UPDATE ".MAIN_DB_PREFIX."rh_compteur SET rttPris=rttPris-".$this->duree.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule+".$this->duree."  where fk_user=".$user->id;
							$ATMdb->Execute($sqlRecredit);
						}else if ($methode =="Mensuel"){
							$sqlRecredit="UPDATE ".MAIN_DB_PREFIX."rh_compteur SET rttPris=rttPris-".$this->duree.",rttAcquisMensuel=rttAcquisMensuel+".$this->duree."  where fk_user=".$user->id;
							$ATMdb->Execute($sqlRecredit);
						}
						
					break;
					case "rttnoncumule" : 
						$sqlMethode="SELECT rttTypeAcquisition FROM `".MAIN_DB_PREFIX."rh_compteur` WHERE fk_user=".$user->id;
						$ATMdb->Execute($sqlMethode);
						while($ATMdb->Get_line()) {
							$methode= $ATMdb->Get_field('rttTypeAcquisition');
						}
						if($methode=="Annuel"){
							$sqlRecredit="UPDATE `".MAIN_DB_PREFIX."rh_compteur` SET rttPris=rttPris-".$this->duree.",rttAcquisAnnuelNonCumule=rttAcquisAnnuelNonCumule+".$this->duree."  where fk_user=".$user->id;
							$ATMdb->Execute($sqlRecredit);
						}else if ($methode =="Mensuel"){
							$sqlRecredit="UPDATE ".MAIN_DB_PREFIX."rh_compteur SET rttPris=rttPris-".$this->duree.",rttAcquisMensuel=rttAcquisMensuel+".$this->duree."  where fk_user=".$user->id;
							$ATMdb->Execute($sqlRecredit);
						}
					break;
					case 'conges':
						$sqlRecredit="UPDATE `".MAIN_DB_PREFIX."rh_compteur` SET congesPrisNM1=congesPrisNM1-".$this->duree."  where fk_user=".$user->id;
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

		
		function dureeAbsenceRecevable(){
			$avertissement=0;
			foreach($this->TRegle as $TR){
				if($TR['typeAbsence']==$this->type){
					if($this->duree>$TR['nbJourCumulable']){
						if($TR['restrictif']==1){
								 return 0;
						}
						else $avertissement=2;  //"Attention, le nombre de jours dépasse la règle"
					}
				}
			}
			if($avertissement==0){
				return 1;
			}
			return $avertissement;
		}
		
		function isWorkingDayNext(&$ATMdb, $dateTest){

			$dateNext=strtotime('+1day',$dateTest); // +3600*24; // AA cf mon autre comm, quand l'horloge change d'heure ceci fonctionne mal
			//$jourNext=$this->jourSemaine($dateNext);
			
			//on teste si c'est un jour férié
			$sql="SELECT rowid, date_jourOff, moment FROM `".MAIN_DB_PREFIX."rh_absence_jours_feries` WHERE date_jourOff between '"
			.$this->php2Date($this->date_debut)."' and '".$dateNext."'"; 
			
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
		}

		function rttnoncumuleDuree2mois(&$ATMdb, $dateDebut){
			
			//on calcule 2 mois entre la date de début de la demande d'absence, et la prise d'un rtt non cumulé
			$dateLimite=$dateDebut-3600*24*58;
			
			$sql="SELECT SUM(duree) as 'somme' FROM ".MAIN_DB_PREFIX."rh_absence 
			WHERE date_debut between '".$this->php2Date($dateLimite)."' AND '".$this->php2Date($dateDebut)."'
			AND type LIKE 'rttnoncumule' AND etat <> 'refusee'"; 
			$ATMdb->Execute($sql);
			
			while($ATMdb->Get_line()) {
				if($ATMdb->Get_field('somme')>=1){
					return 0;
				}
			}
			return 1;
		}
		
		
		

	///////////////FONCTIONS pour le fichier rechercheAbsence\\\\\\\\\\\\\\\\\\\\\\\\\\\
	
	//va permettre la création de la requête pour les recherches d'absence pour les collaborateurs
	function requeteRechercheAbsence(&$ATMdb, $idGroupeRecherche, $idUserRecherche, $horsConges, $date_debut, $date_fin){
			
			if($horsConges==1){ //on recherche uniquement une compétence
				$sql=$this->rechercheAucunConges($ATMdb,$idGroupeRecherche, $date_debut, $date_fin);
			}
			else if($idGroupeRecherche!=0&&$idUserRecherche==0){ //on recherche les absences d'un groupe
				$sql=$this->rechercheAbsenceGroupe($ATMdb, $idGroupeRecherche, $date_debut, $date_fin);
			}
			else if($idUserRecherche!=0){ //on recherche les absences d'un utilisateur
				$sql=$this->rechercheAbsenceUser($ATMdb,$idUserRecherche, $date_debut, $date_fin);
			}
			return $sql;
	}
	
	//requete avec groupe de collaborateurs précis
	function rechercheAbsenceGroupe(&$ATMdb, $idGroupeRecherche, $date_debut, $date_fin){ 
			global $conf;
			
			//on recherche le nom de la compétence désirée
			$sql="SELECT  u.name,u.firstname, a.date_debut, 
				a.date_fin, a.libelle, a.libelleEtat
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
				WHERE a.fk_user=u.rowid 
				AND  g.fk_user=u.rowid
				AND g.fk_usergroup=".$idGroupeRecherche."
				AND a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				AND a.entity=".$conf->entity;
			
			return $sql;
	}
	
	//requete renvoyant les utilisateurs n'ayant pas pris de congés pendant une période
	function rechercheAucunConges(&$ATMdb, $idGroupeRecherche, $date_debut, $date_fin){ 
			global $conf;

			//on recherche le nom de la compétence désirée
			$sql="SELECT DISTINCT g.fk_user, u.name, u.firstname
			FROM ".MAIN_DB_PREFIX."usergroup_user as g, ".MAIN_DB_PREFIX."user as u
			WHERE g.fk_usergroup =".$idGroupeRecherche."   AND u.rowid=g.fk_user
			AND u.entity=".$conf->entity."
			AND g.fk_user NOT IN (
						SELECT a.fk_user 
						FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as g
						WHERE a.fk_user=u.rowid AND g.fk_user=u.rowid 
						AND g.fk_usergroup=".$idGroupeRecherche." 
						AND a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
						AND a.entity=".$conf->entity.")";

			return $sql;
	}

	//requete avec un collaborateur précis
	function rechercheAbsenceUser(&$ATMdb,$idUserRecherche, $date_debut, $date_fin){
			global $conf;
			
			//on recherche le nom de la compétence désirée
			$sql="SELECT u.name, u.firstname, DATE_FORMAT(a.date_debut, '%d/%m/%Y') as date_debut, 
				DATE_FORMAT(a.date_fin, '%d/%m/%Y') as date_fin, a.libelle, a.libelleEtat
				FROM ".MAIN_DB_PREFIX."rh_absence as a, ".MAIN_DB_PREFIX."user as u
				WHERE a.fk_user=u.rowid 
				AND a.fk_user=".$idUserRecherche."
				AND a.date_debut between '".$this->php2Date(strtotime(str_replace("/","-",$date_debut)))."' AND '".$this->php2Date(strtotime(str_replace("/","-",$date_fin)))."'
				AND a.entity=".$conf->entity;
				echo $sql;
			
			return $sql;
	}
			
}


//définition de la classe pour l'administration des compteurs
class TRH_AdminCompteur extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_admin_compteur');
		parent::add_champs('congesAcquisMensuelInit','type=float;');
		parent::add_champs('date_rttClotureInit','type=date;');
		parent::add_champs('date_congesClotureInit','type=date;');				
					
		parent::_init_vars();
		parent::start();	
	}
	
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
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
					
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('tempsHebdo','type=float;');
		parent::add_champs('entity','type=int;');
		
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
			if($jour!='samedi' && $jour!='dimanche') {
				 $this->{'date_'.$jour."_heuredam"}= strtotime('9:00');
				 $this->{'date_'.$jour."_heurefam"}=strtotime('12:15');
				 $this->{'date_'.$jour."_heuredpm"}=strtotime('14:00');
				 $this->{'date_'.$jour."_heurefpm"}=strtotime('18:00');
			}
				else {
					$this->{'date_'.$jour."_heuredam"}=$this->{'date_'.$jour."_heurefam"}=$this->{'date_'.$jour."_heuredpm"}=$this->{'date_'.$jour."_heurefpm"}= strtotime('0:00');
				}
		}
		$this->tempsHebdo=36.25;

	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function razCheckbox(&$ATMdb, $emploiTemps){
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
		foreach ($edt->TJour as $jour) {
			if($edt->{$jour."am"}=="1"){
				//echo $edt->{"date_".$jour."_heuredam"}.$edt->{"date_".$jour."_heurefam"};exit;
				$tpsHebdo=additionnerHeure($tpsHebdo,difheure(date('h:i',$edt->{"date_".$jour."_heuredam"}), date('h:i',$edt->{"date_".$jour."_heurefam"})));
			}
			if($edt->{$jour."pm"}=="1"){
				$tpsHebdo=additionnerHeure($tpsHebdo,difheure(date('h:i',$edt->{"date_".$jour."_heuredpm"}), date('h:i',$edt->{"date_".$jour."_heurefpm"})));
				
			}
		}
	
		return horaireMinuteEnCentieme($tpsHebdo);
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
	}
	
	
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function razCheckbox(&$ATMdb, $absence){
			$this->matin=0;
			$this->apresmidi=0;
	}
	
}

//définition de la classe pour la gestion des règles
class TRH_RegleAbsence extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_absence_regle');
		parent::add_champs('typeAbsence','type=chaine;');
		parent::add_champs('choixApplication','type=chaine;');
		parent::add_champs('nbJourCumulable','type=int;');
		parent::add_champs('restrictif','type=int;');
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('fk_usergroup','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=int;');
		
		
		parent::_init_vars();
		parent::start();	
		
		$this->choixApplication = 'all';
		global $conf;
		$ATMdb=new Tdb;
		//combo box pour le type d'absence admin
		$this->TTypeAbsenceAdmin=array();
		$sql="SELECT typeAbsence, libelleAbsence  FROM `".MAIN_DB_PREFIX."rh_type_absence` 
		WHERE entity=".$conf->entity;
		$ATMdb->Execute($sql);

		while($ATMdb->Get_line()) {
			$this->TTypeAbsenceAdmin[$ATMdb->Get_field('typeAbsence')]=$ATMdb->Get_field('libelleAbsence');
		}
		
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
			case 'all':$this->fk_user = NULL;$this->fk_usergroup=NULL;break;
			case 'user':$this->fk_usergroup = NULL;break;
			case 'group':$this->fk_user = NULL;break;
			default : echo'pbchoixapplication';break;				
		}
		
		parent::save($ATMdb);
	}

	
	
	function load_liste(&$ATMdb){
		global $conf;

		//LISTE DE GROUPES
		$this->TGroup  = array();
		$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TGroup[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
		}
		
		//LISTE DE USERS
		$this->TUser = array();
		$sqlReq="SELECT rowid, firstname, name FROM ".MAIN_DB_PREFIX."user WHERE entity=".$conf->entity;
		$ATMdb->Execute($sqlReq);
		while($ATMdb->Get_line()) {
			$this->TUser[$ATMdb->Get_field('rowid')] = htmlentities($ATMdb->Get_field('firstname'), ENT_COMPAT , 'ISO8859-1').' '.htmlentities($ATMdb->Get_field('name'), ENT_COMPAT , 'ISO8859-1');
		}
	}
}


//définition de la classe pour la gestion des règles
class TRH_TypeAbsence extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_type_absence');
		parent::add_champs('typeAbsence','type=chaine;');
		parent::add_champs('libelleAbsence','type=chaine;');
		parent::add_champs('codeAbsence','type=chaine;');
		parent::add_champs('admin','type=int;');
		parent::add_champs('unite','type=chaine;');
		parent::add_champs('entity','type=int;');
		
		parent::_init_vars();
		parent::start();
	}
}

		