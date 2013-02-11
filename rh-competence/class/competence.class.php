<?php

//TRH_FORMULAIRE
//classe pour la création d'un formulaire
class TRH_Formulaire extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formulaire');
		parent::add_champs('annee','type=date;');	//dates debut fin de congés
		
		parent::_init_vars();
		parent::start();
		
	}
}

//TRH_LIGNE_CV
//définition de la classe décrivant les lignes de CV d'un utilisateur
class TRH_Ligne_cv extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_ligne_cv');
		parent::add_champs('date_debut,date_fin','type=date;');		//dates de début et de fin de la formation suivie
		parent::add_champs('formation','type=chaine'); 	//formation suivie
		parent::add_champs('fk_utilisateur','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
	}
}

//TRH_COMPETENCES
//définition de la classe pour rentrer les compétences d'un utilisateur
class TRH_Competences extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_competences');
		parent::add_champs('competences','type=chaine;');		//compétences acquises sous forme de chaine de caractères
		
		parent::_init_vars();
		parent::start();
		
	}
}

//TRH_QUESTION
//Définiton classe pour une question d'un formulaire 
class TRH_Question extends TObjetStd {
	function __construct() { /* declaration */
		
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
	function __construct() { /* declaration */
		
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
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_scan');
		parent::add_champs('chemin_fichier','type=chaine;');		//chemin d'acces au fichier 
		parent::add_champs('date','type=date;');	//date du fichier
		parent::add_champs('fk_utilisateur','type=entier;');	//utilisateur concerné
		parent::add_champs('fk_formulaire','type=entier;');	//formulaire concerné par le scan
			
		parent::_init_vars();
		parent::start();
		
	}
}