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
		
		//paramètres globaux
		parent::add_champs('rttAcquisMensuelInit','type=float;');	
		parent::add_champs('rttAcquisAnnuelCumuleInit','type=float;');
		parent::add_champs('rttAcquisAnnuelNonCumuleInit','type=float;');
		
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
		$this->acquisExerciceN='6';
		$this->acquisAncienneteN='1';
		$this->acquisHorsPeriodeN='0';
		$this->anneeN=$annee;
		$this->acquisExerciceNM1='25';
		$this->acquisAncienneteNM1='1';
		$this->acquisHorsPeriodeNM1='0';
		$this->reportCongesNM1='0';
		$this->congesPrisNM1='4';
		$this->anneeNM1=$anneePrec;
		$this->rttPris='0';
		$this->rttTypeAcquisition='Annuel';
		$this->rttAcquisMensuelInit='0';
		$this->rttAcquisAnnuelCumuleInit='5';
		$this->rttAcquisAnnuelNonCumuleInit='7';
		$this->rttAcquisMensuel='0';
		$this->rttAcquisAnnuelCumule='5';
		$this->rttAcquisAnnuelNonCumule='7';
		$this->rttannee=$annee;
		$this->nombreCongesAcquisMensuel='2.08';
		$this->date_rttCloture=strtotime('2013-03-01 00:00:00');
		$this->date_congesCloture=strtotime('2013-06-01 00:00:00');
	}
}




//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('code','type=int;');				//code  congé
		parent::add_champs('type','type=varchar;');				//type de congé
		parent::add_champs('libelle','type=varchar;');				//type de congé
		parent::add_champs('date_debut,date_fin','type=date;');	//dates debut fin de congés
		parent::add_champs('ddMoment, dfMoment','type=chaine;');		//moment (matin ou après midi)
		parent::add_champs('duree','type=float;');				
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('etat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('libelleEtat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		
		parent::add_champs('entity','type=int;');	
		
		parent::_init_vars();
		parent::start();
		
		$this->TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		$this->Tjoursem = array("dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi");
		
		
		//combo box pour le type d'absence
		$this->TTypeAbsence = array('rttcumule'=>'RTT Cumulé','rttnoncumule'=>'RTT Non Cumulé', 'conges' => 'Congés', 'maladiemaintenue' => 'Maladie maintenue', 
		'maladienonmaintenue'=>'Maladie non maintenue','maternite'=>'Maternité', 'paternite'=>'Paternité', 
		'chomagepartiel'=>'Chômage Partiel','nonremuneree'=>'Non rémunérée','accidentdetravail'=>'Accident de travail',
		'maladieprofessionnelle'=>'Maladie professionnelle', 'congeparental'=>'Congé parental', 'accidentdetrajet'=>'Accident de trajet',
		'mitempstherapeutique'=>'Mi-temps thérapeutique');
		
		//combo pour le choix de matin ou après midi 
		$this->TddMoment = array('matin'=>'Matin','apresmidi'=>'Après-midi');	//moment de date début
		$this->TdfMoment = array('matin'=>'Matin','apresmidi'=>'Après-midi');	//moment de date fin
		}

		function save(&$db) {
			$ATMdb=new Tdb;
			global $conf, $user;
			$this->entity = $conf->entity;
			
			//on calcule la duree de l'absence, en décomptant jours fériés et jours non travaillés par le collaborateur
			$dureeAbsenceCourante=$this->calculDureeAbsence($ATMdb);
			$dureeAbsenceCourante=$this->calculJoursFeries($ATMdb, $dureeAbsenceCourante);
			$dureeAbsenceCourante=$this->calculJoursTravailles($ATMdb, $dureeAbsenceCourante);
			
			
			///////décompte des congés
			if($this->type=="rttcumule"){
				$sqlDecompte="UPDATE `llx_rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule-".$dureeAbsenceCourante."  where fk_user=".$user->id;
				$ATMdb->Execute($sqlDecompte);
				$this->rttPris=$this->rttPris+$dureeAbsenceCourante;
				$this->rttAcquisAnnuelCumule=$this->rttAcquisAnnuelCumule-$dureeAbsenceCourante;
				
			}else if($this->type=="rttnoncumule"){
				$sqlDecompte="UPDATE `llx_rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante.",rttAcquisAnnuelNonCumule=rttAcquisAnnuelNonCumule-".$dureeAbsenceCourante." where fk_user=".$user->id;
				$ATMdb->Execute($sqlDecompte);
				$this->rttPris=$this->rttPris-$dureeAbsenceCourante;
				$this->rttAcquisAnnuelNonCumule=$this->rttAcquisAnnuelNonCumule-$dureeAbsenceCourante;
			}
			else {	//autre que RTT : décompte les congés
				$sqlDecompte="UPDATE `llx_rh_compteur` SET congesPrisNM1=congesPrisNM1+".$dureeAbsenceCourante." where fk_user=".$user->id;
				$ATMdb->Execute($sqlDecompte);
				$this->congesResteNM1=$this->congesResteNM1-$dureeAbsenceCourante;
			}
			//autres paramètes à sauvegarder
			$this->libelle=saveLibelle($this->type);
			$this->duree=$dureeAbsenceCourante;
			$this->etat="Avalider";
			$this->libelleEtat=saveLibelleEtat($this->etat);
			parent::save($db);
		}

		
		//calcul de la durée initiale de l'absence (sans jours fériés, sans les jours travaillés du salariés)
		function calculDureeAbsence(&$ATMdb){
			$diff=$this->date_fin-$this->date_debut;
			$duree=$diff/3600/24;
			
			//prise en compte du matin et après midi
			if(isset($_REQUEST['id'])){
				if($this->ddMoment=="matin"&&$this->dfMoment=="apresmidi"){
					$duree+=1;
				}
				else if($this->ddMoment==$this->dfMoment){
					$duree+=0.5;
				}
			}
			return $duree; 
		}
		
		
		//calcul la durée de l'absence après le décompte des jours fériés
		function calculJoursFeries(&$ATMdb, $duree){

			$dateDebutAbs=$this->php2Date($this->date_debut);
			$dateFinAbs=$this->php2Date($this->date_fin);
			
			//on cherche s'il existe un ou plusieurs jours fériés  entre la date de début et de fin d'absence
			$sql="SELECT rowid, date_jourOff, moment FROM `llx_rh_absence_jours_feries` WHERE date_jourOff between '"
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
					if($this->ddMoment==$this->dfMoment&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->ddMoment==$this->dfMoment&&$this->ddMoment=='matin'&&$jour['moment']=='matin'){
						$duree-=0.5;
					}
					else if($this->ddMoment==$this->dfMoment&&$this->ddMoment=='apresmidi'&&$jour['moment']=='apresmidi'){
						$duree-=0.5;
					}
					else if($this->ddMoment=='matin'&&$this->dfMoment=='apresmidi'&&$jour['moment']=='apresmidi'){
						$duree-=0.5;
					}
					else if($this->ddMoment=='matin'&&$this->dfMoment=='apresmidi'&&$jour['moment']=='matin'){
						$duree-=0.5;
					}
					else if($this->ddMoment=='matin'&&$this->dfMoment=='apresmidi'&&$jour['moment']=='allday'){
						$duree-=1;
					}
				}else if($dateDebutAbs==$jour['date_jourOff']){	//si la date début est égale à la date du jour férié
					//echo "boucle2";
					if($this->ddMoment=='matin'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
						$duree-=1;
					}
					else if($this->ddMoment=='matin'&&$jour['moment']=='matin'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->ddMoment=='matin'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->ddMoment=='apresmidi'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->ddMoment=='apresmidi'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
				}
				else if($dateFinAbs==$jour['date_jourOff']){	//si la date début est égale à la date du jour férié
				//	echo "boucle3";
					if($this->dfMoment=='matin'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->dfMoment=='matin'&&$jour['moment']=='matin'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->dfMoment=='matin'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->dfMoment=='apresmidi'&&$jour['moment']=='apresmidi'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->dfMoment=='apresmidi'&&$jour['moment']=='matin'){ //traite le cas matin et apresmidi
						$duree-=0.5;
					}
					else if($this->dfMoment=='apresmidi'&&$jour['moment']=='allday'){ //traite le cas matin et apresmidi
						$duree-=1;
					}
				}
				else{
					//echo "boucle4";
					if($jour['date_jourOff']=='allday'){
						$duree-=1;
					}else{
						$duree-=0.5;
					}
				}
			}
			return $duree;
		}

		
		function calculJoursTravailles(&$ATMdb, $duree){
			
			//traitement jour de début
			$dateDebutAbs=$this->php2Date($this->date_debut);
			$jourDebutSem=$this->jourSemaine($this->date_debut);
			
			//traitement jour de fin
			$dateFinAbs=$this->php2Date($this->date_fin);
			$jourFinSem=$this->jourSemaine($this->date_fin);
			
			//on récupère les jours fériés compris dans la demande d'absence
			$sql="SELECT date_jourOff FROM `llx_rh_absence_jours_feries` WHERE date_jourOff between '"
			.$dateDebutAbs."' and '". $dateFinAbs."'"; 
			//echo $sql;
			$ATMdb->Execute($sql);
			$TabFerie = array();
			while($ATMdb->Get_line()) {
				$TabFerie[]= $ATMdb->Get_field('date_jourOff');
			}				

			//on cherche les jours travaillés par l'employé
			$sql="SELECT rowid, lundiam, lundipm, 
			mardiam, mardipm, mercrediam, mercredipm, 
			jeudiam, jeudipm, vendrediam, vendredipm,
			samediam, samedipm, dimancheam, dimanchepm
			FROM `llx_rh_absence_emploitemps` 
			WHERE fk_user=".$this->fk_user; 

			$ATMdb->Execute($sql);
			$TTravail = array();
			while($ATMdb->Get_line()) {
				foreach ($this->TJour as $jour) {
					foreach(array('am','pm') as $moment) {
						$TTravail[$jour.$moment]=$ATMdb->Get_field($jour.$moment);
					}
				}
				$rowid=$ATMdb->Get_field($rowid);
			}			
			
			//on traite les jours de début et de fin indépendemment des autres
			if($this->date_debut==$this->date_fin){	//si les jours de début et de fin sont les mêmes
				$ferie=0;
				foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
		 			if(strtotime($jourFerie)==$this->date_debut){
		 				$ferie=1;
		 			}
		 		}
				if(!$ferie){
					//echo "boucle1";
					if($this->dfMoment=='matin'){		// si la date de fin est le matin, il n'y a donc que le cas matin à traiter
						if($TTravail[$jourDebutSem.'am']==0){
							$duree-=0.5;
						}
					}else if($this->ddMoment=='apresmidi'){		// si la date de debut est lapres midi, il n'y a donc que le cas pm à traiter
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}
					}else{	//sinon on traite les cas matin et apres midi
						if($TTravail[$jourDebutSem.'am']==0){
							$duree-=0.5;
						}
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}
					}
					return $duree;
				}
				else return $duree;
				
			}else{	//les jours de début et de fin sont différents
				//echo "boucle2";
				//////////////////////////jour de début
				$ferie=0;		
				foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
		 			if(strtotime($jourFerie)==$this->date_debut){
		 				$ferie=1;
		 			}
		 		}
				if(!$ferie){
					if($this->ddMoment=='matin'){
						if($TTravail[$jourDebutSem.'am']==0){
							$duree-=0.5;
						}
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}
					}else if($this->ddMoment=='apresmidi'){
						if($TTravail[$jourDebutSem.'pm']==0){
							$duree-=0.5;
						}
					}
				}
				
				///////////////////////////jour de fin
				$ferie=0;		
				foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
		 			if(strtotime($jourFerie)==$this->date_fin){
		 				$ferie=1;
		 			}
		 		}
				if(!$ferie){
					if($this->dfMoment=='matin'){
						if($TTravail[$jourFinSem.'am']==0){
							$duree-=0.5;
						}
					}else if($this->dfMoment=='apresmidi'){
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
			$jourEnCours=$this->date_debut+3600*24;
			$jourFin=$this->date_fin;
			while($jourEnCours!=$jourFin){
				$ferie=0;
				//echo "boucle1";
				
				foreach($TabFerie as $jourFerie){	//si le jour est un jour férié, on ne le traite pas, car déjà traité avant. 
		 			if(strtotime($jourFerie)==$jourEnCours){
		 				$ferie=1;
		 			}
		 		}
				if(!$ferie){
					$jourEnCoursSem=$this->jourSemaine($jourEnCours);
					//echo $jourEnCoursSem;
					foreach ($this->TJour as $jour) {
						if($jour==$jourEnCoursSem){
							foreach(array('am','pm') as $moment) {
								if($TTravail[$jour.$moment]==0){
									$duree-=0.5;
								}
							}
						}
					}
				}
				$jourEnCours=$jourEnCours+3600*24;
				
			}
			
		    return $duree;
		}
		
		
		//renvoie le jour de la semaine correspondant à la date passée en paramètre
		function jourSemaine($phpDate){
		    $frdate=$this->php2dmy($phpDate);
			list($jour, $mois, $annee) = explode('/', $frdate);
			// calcul du timestamp
			$timestamp = mktime (0, 0, 0, $mois, $jour, $annee);
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
						$sqlRecredit="UPDATE llx_rh_compteur SET rttPris=rttPris-".$this->duree.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule+".$this->duree."  where fk_user=".$user->id;
						$ATMdb->Execute($sqlRecredit);
					break;
					case "rttnoncumule" : 
						$sqlRecredit="UPDATE `llx_rh_compteur` SET rttPris=rttPris-".$this->duree.",rttAcquisAnnuelNonCumule=rttAcquisAnnuelNonCumule+".$this->duree."  where fk_user=".$user->id;
						$ATMdb->Execute($sqlRecredit);
					break;
					default :  //dans les autres cas, on recrédite les congés
						$sqlRecredit="UPDATE `llx_rh_compteur` SET congesPrisNM1=congesPrisNM1-".$this->duree."  where fk_user=".$user->id;
						//echo $this->type.$sqlRecredit;exit;
						$ATMdb->Execute($sqlRecredit);
					break;
				}
			}
		}

		//fonction qui va renvoyer 1 si l'utilisateur est valideur de l'absence courante
		function estValideur(&$ATMdb,$idUser){
			
			if($this->fk_user==$idUser) return 0;
			//on récupère les groupes auxquels appartient l'utilisateur ayant créé l'absence
			$sql="SELECT fk_usergroup FROM `llx_usergroup_user` WHERE fk_user=".$this->fk_user;
			$ATMdb->Execute($sql);
			$TGroupesUser = array();
			while($ATMdb->Get_line()) {
				$TGroupesUser[]= $ATMdb->Get_field('fk_usergroup');
			}
			
			//on récupère les groupes dont l'utilisateur courant est valideur de congés
			$sql2="SELECT fk_usergroup FROM llx_rh_valideur_groupe WHERE fk_user=".$idUser." AND type='Conges'";
			$ATMdb->Execute($sql2);
			$TGroupesValideur = array();
			while($ATMdb->Get_line()) {
				$TGroupesValideur[]= $ATMdb->Get_field('fk_usergroup');
			}
			
			//on regarde si l'utilisateur courant peut valider la demande d'absence
			foreach($TGroupesUser as $grUser){
				foreach($TGroupesValideur as $grValideur){
					if($grUser==$grValideur) return 1;
				}
			}	
			return 0;
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

	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function razCheckbox(&$ATMdb, $absence){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		foreach ($this->TJour as $jour) {
			$this->{$jour."am"}=0;
			 $this->{$jour."pm"}=0;
		}
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
		global $conf, $user;
		$this->entity = $conf->entity;

			$this->matin=0;
			 $this->apresmidi=0;
	}
	
}





//définition de la classe pour la notion de pointage
class TRH_Pointage extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_pointage');
		parent::add_champs('date','type=date;');		//date de pointage
		parent::add_champs('present','type=entier'); 	//collaborateur présent ou non
		
		parent::_init_vars();
		parent::start();
		
	}
}

//TODO  A terminer de definir...
//Définiton classe d'export vers la comptabilité + export bilan social individuel annuel 
class TRH_Export extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_export');
		parent::add_champs('date','type=date;');		//date de l'export
		parent::add_champs('nb_rtt','type=entier'); 	//nombre de Rtt à décompter
		parent::add_champs('nb_conge_paye','type=entier'); 	//nombre de congés payés à décompter
		parent::add_champs('nb_absence_autre','type=entier'); 	//nombre d'absences de type autres (deuil, maladie etc...) à décompter
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
	}
}

//définition de la table pour l'enregistrement des jours non travaillés dans l'année (fériés etc...)
class TRH_Jour_non_travaille extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_jour_non_travaille');
		parent::add_champs('date','type=date;');		//date du jour non travaillé
		
		parent::_init_vars();
		parent::start();
		
	}
}