<?php

//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Conge extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_conge');
		parent::add_champs('acquisExercice','type=float;');				//type de congé
		parent::add_champs('acquisAnciennete','type=float;');				//type de congé
		parent::add_champs('acquisHorsPeriode','type=float;');				//type de congé
		parent::add_champs('reportConges','type=float;');				//type de congé
		parent::add_champs('congesPris','type=float;');				//type de congé
		parent::add_champs('annee','type=date;');					//dates debut fin de congés
		parent::add_champs('duree','type=entier;');				//duree en demi-journees
		parent::add_champs('fk_user','type=entier;');			//utilisateur concerné
		parent::_init_vars();
		parent::start();
	}
}




//TRH_ABSENCE
//classe pour la définition d'une absence 
class TRH_Absence extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_absence');
		parent::add_champs('type','type=varchar;');				//type de congé
		parent::add_champs('date_debut,date_fin','type=date;');	//dates debut fin de congés
		parent::add_champs('duree','type=entier;');				//duree en demi-journees
		parent::add_champs('commentaire','type=chaine;');		//commentaire
		parent::add_champs('fk_user','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
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