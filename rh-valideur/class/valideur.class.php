<?php
/*
 * RH Gère les valideur d'un group et leur type
 */ 
class TRH_valideur_groupe extends TObjetStd {
	function __construct() { /* declaration */
		
		parent::set_table(MAIN_DB_PREFIX.'rh_valideur_groupe');
		parent::add_champs('type','type=varchar;');				//type de congé
		parent::add_champs('fk_user,fk_group','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
	}
}
