<?php

//TRH_LIGNE_CV
//définition de la classe décrivant les lignes de CV d'un utilisateur
class TGroupeFormulaire extends TObjetStd {
	function __construct() {
		
		parent::set_table(MAIN_DB_PREFIX.'rh_formulaire_groupe');
		parent::add_champs('fk_usergroup, fk_survey','type=entier;');
		parent::add_champs('date_deb, date_fin','type=date;');
		parent::_init_vars();
		parent::start();
	}
}