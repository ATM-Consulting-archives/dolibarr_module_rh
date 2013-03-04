<?php
/*
 * RH Gère les valideur d'un group et leur type
 */ 
class TRH_valideur_groupe extends TObjetStd {
	function __construct() { /* declaration */
		global $conf;
		
		
		parent::set_table(MAIN_DB_PREFIX.'rh_valideur_groupe');
		parent::add_champs('type','type=varchar;');				//type de valideur
		parent::add_champs('fk_user,fk_group','type=entier;');	//utilisateur concerné
		
		parent::_init_vars();
		parent::start();
		
		
		$TType =array();
		
		// en fonction des droits
		$TType['NDFP']='Note de frais';
		
		$TType['Ressource']='Ressources';
		
	}
}
