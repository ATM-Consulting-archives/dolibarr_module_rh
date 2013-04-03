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
	function findProfile(&$ATMdb, $competence){

			global $conf;
			
			$TUser=array();
			
			$sql="SELECT * FROM llx_rh_competence_cv WHERE libelleCompetence LIKE '".$competence."'
				AND entity=".$conf->entity;
			$ATMdb->Execute($sql);
			$TUser=array();
			$k=0;
			while($ATMdb->Get_line()) {
						/*$TUser[$k]['libelleCompetence']=$ATMdb->Get_field('libelleCompetence');
						$TUser[$k]['fk_user']=$ATMdb->Get_field('fk_user');
						$TUser[$k]['fk_user_formation']=$ATMdb->Get_field('fk_user_formation');
						$k++;*/
						$TUser[]=$ATMdb->Get_field('fk_user');
			}
			return $TUser;
		}
	
}


/*
//TRH_FORMULAIRE
//classe pour la création d'un formulaire
class TRH_Formulaire extends TObjetStd {
	function __construct() { /* declaration 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formulaire');
		parent::add_champs('annee','type=date;');	//dates debut fin de congés
		
		parent::_init_vars();
		parent::start();
		
	}
}
//TRH_QUESTION
//Définiton classe pour une question d'un formulaire 
class TRH_Question extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_question');
		parent::add_champs('question','type=chaine;');		//question à poser
		parent::add_champs('type','type=varchar;'); 	//type de la question
		parent::add_champs('fk_formulaire','type=entier;');		//formulaire dont dépend la question
		
		parent::_init_vars();
		parent::start();
		
	}
}

//TRH_REPONSE
//définition de la classe pour la réponse à une question
class TRH_Reponse extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_reponse');
		parent::add_champs('reponse','type=chaine;');		//réponse donnée à la question
		parent::add_champs('fk_utilisateur','type=entier;');	//utilisateur concerné
		parent::add_champs('fk_question','type=entier;');	//question concernée par la réponse
		
		parent::_init_vars();
		parent::start();
		
	}
}

//TRH_SCAN
//définition de la classe pour l'enregistrement des jours non travaillés dans l'année (fériés etc...)
class TRH_Scan extends TObjetStd {
	function __construct() { 
		
		parent::set_table(MAIN_DB_PREFIX.'rh_scan');
		parent::add_champs('chemin_fichier','type=chaine;');		//chemin d'acces au fichier 
		parent::add_champs('date','type=date;');	//date du fichier
		parent::add_champs('fk_utilisateur','type=entier;');	//utilisateur concerné
		parent::add_champs('fk_formulaire','type=entier;');	//formulaire concerné par le scan
			
		parent::_init_vars();
		parent::start();
		
	}
}*/