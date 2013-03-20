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
			
			///////calcul durée du congés 
			$diff=$this->date_fin-$this->date_debut;
			$dureeAbsenceCourante=$diff/3600/24;
			
			//prise en compte du matin et après midi
			if(isset($_REQUEST['id'])){
				if($this->ddMoment=="matin"&&$this->dfMoment=="apresmidi"){
					$dureeAbsenceCourante+=1;
				}else if($this->ddMoment==$this->dfMoment&&$dureeAbsenceCourante==0){
					$dureeAbsenceCourante+=0.5;
				}
			}
			
			///////décompte des congés
			if($this->type=="rttcumule"){
				$sqlDecompte="UPDATE `llx_rh_compteur` SET rttPris=rttPris+".$dureeAbsenceCourante.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule-".$dureeAbsenceCourante."  where fk_user=".$user->id;
				$ATMdb->Execute($sqlDecompte);
				$this->rttPris=$this->rttPris-$dureeAbsenceCourante;
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

		//recrédite les heures au compteur lors de la suppression d'une absence 
		function recrediterHeure(&$ATMdb){
			global $conf, $user;
			$this->entity = $conf->entity;
			
			switch($this->type){
				case "rttcumule" : 
					$sqlRecredit="UPDATE `llx_rh_compteur` SET rttPris=rttPris-".$this->duree.",rttAcquisAnnuelCumule=rttAcquisAnnuelCumule+".$this->duree."  where fk_user=".$user->id;
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
		$TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		foreach ($TJour as $jour) {
			parent::add_champs($jour.'am','type=entier;');
			parent::add_champs($jour.'pm','type=entier;');		
		}
		
		//horaires de travail
		foreach ($TJour as $jour) {
			parent::add_champs($jour.'_heuredam','type=chaine;');
			parent::add_champs($jour.'_heurefam','type=chaine;');		
			parent::add_champs($jour.'_heuredpm','type=chaine;');
			parent::add_champs($jour.'_heurefpm','type=chaine;');
		}
					
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=int;');
		
		parent::_init_vars();
		parent::start();	
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
		$this->lundiam='1';
		$this->lundipm='1';
		$this->mardiam='1';
		$this->mardipm='1';
		$this->mercrediam='1';
		$this->mercredipm='1';
		$this->jeudiam='1';
		$this->jeudipm='1';
		$this->vendrediam='1';
		$this->vendredipm='1';
		$this->samediam='0';
		$this->samedipm='0';
		$this->dimancheam='0';
		$this->dimancheam='0';
		
		$TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi');
		foreach ($TJour as $jour) {
			$this->{$jour."_heuredam"}='9:00';
			 $this->{$jour."_heurefam"}='12:15';
			 $this->{$jour."_heuredpm"}='14:00';
			 $this->{$jour."_heurefpm"}='18:00';
		}

		$this->samedi_heuredam='0:00';
		$this->samedi_heurefam='0:00';
		$this->samedi_heuredpm='0:00';
		$this->samedi_heurefpm='0:00';
		
		$this->dimanche_heuredam='0:00';
		$this->dimanche_heurefam='0:00';
		$this->dimanche_heuredpm='0:00';
		$this->dimanche_heurefpm='0:00';
	}
	
	//remet à 0 les checkbox avant la sauvegarde
	function razCheckbox(&$ATMdb, $absence){
		global $conf, $user;
		$this->entity = $conf->entity;
		
		$TJour = array('lundi','mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
		foreach ($TJour as $jour) {
			$this->{$jour."am"}='0';
			 $this->{$jour."pm"}='0';
		}
	}
}


//définition de la classe pour l'administration des compteurs
class TRH_JoursFeries extends TObjetStd {
	function __construct() { 
		parent::set_table(MAIN_DB_PREFIX.'rh_absence_jours_feries');
		parent::add_champs('date_jourOff','type=date;');
		parent::add_champs('matin','type=int;');
		parent::add_champs('apresmidi','type=int;');
		parent::add_champs('commentaire','type=chaine;');
		parent::add_champs('entity','type=int;');
		
		
		parent::_init_vars();
		parent::start();	
		
		$this->TFerie=array();
		
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

			$this->matin='0';
			 $this->apresmidi='0';
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