<?php

//TRH_LIGNE_CV
//définition de la classe décrivant les lignes de CV d'un utilisateur
class TRH_ligne_cv extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ligne_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('libelleExperience','type=chaine'); 	//formation suivie
		parent::add_champs('descriptionExperience','type=chaine'); 	//formation suivie
		parent::add_champs('lieuExperience','type=chaine'); 	//formation suivie
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		
		parent::_init_vars();
		parent::start();
	}
}

//TRH_FORMATION
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_formation_cv extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formation_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('libelleFormation','type=chaine;');		
		parent::add_champs('competenceFormation','type=chaine;');	
		parent::add_champs('commentaireFormation','type=chaine;');		//commentaire associé	
		parent::add_champs('lieuFormation','type=chaine;');		
		parent::add_champs('date_formationEcheance','type=date;');	
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		parent::_init_vars();
		parent::start();
	}
}

//TRH_COMPETENCES
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_competence_cv extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_competence_cv');
		
		parent::add_champs('libelleCompetence','type=chaine;');		
		parent::add_champs('fk_user_formation','type=entier;');	
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		parent::add_champs('entity','type=entier;');
		parent::_init_vars();
		parent::start();
	}
	
	
	//mise en forme de la recherche : suppression des espaces, rajout des %
	function miseEnForme($competence){
		$competence=str_replace(' ','%',$competence);
		return "%".$competence."%";
	}
	
	
	//fonction permettant de donner les utilisateurs ayant une compétence recherchée
	function findProfile(&$ATMdb, $competenceInit){

			global $conf;
			echo $competenceInit;
			$competenceEt=separerEt($competenceInit);
			echo $competenceEt;
			/* $TUser=array();
			  $sql="SELECT * FROM llx_rh_competence_cv WHERE libelleCompetence LIKE '".$competence."'
				AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			$TUser=array();
			$k=0;
			while($ATMdb->Get_line()) {
						$TUser[]=$ATMdb->Get_field('fk_user');
			}
			return $TUser;*/
			return $competence;
		}

	function separerEt($competenceEt){
		
	}
	
}

