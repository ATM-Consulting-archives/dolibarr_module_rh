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
		
		parent::_init_vars();
		parent::start();
	}
		
	function save(&$db) {
		global $conf;
		$this->entity = $conf->entity;
		
		parent::save($db);
	}
}




//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('code','type=int;');				//code  congé
		parent::add_champs('type','type=varchar;');				//type de congé
		parent::add_champs('date_debut,date_fin','type=date;');	//dates debut fin de congés
		parent::add_champs('ddMoment, dfMoment','type=chaine;');		//moment (matin ou après midi)
		parent::add_champs('duree','type=float;');				
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('etat','type=chaine;');			//état (à valider, validé...)
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		
		
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
			
			//$this->congesTotalNM1=$this->acquisEx+$this->acquisAnc+$this->acquisHorsPer+$this->reportConges;
			//$this->congesResteNM1=$congePrecTotal-$this->congesPris;
			
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
	
			$this->duree=$dureeAbsenceCourante;
			$this->etat="Avalider";
			parent::save($db);
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