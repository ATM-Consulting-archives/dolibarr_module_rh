<?php

class TRH_Hierarchie extends TObjetStd {
	function __construct() { /* declaration */
		parent::set_table('llx_user');
		parent::add_champs('name','type=chaine;');
		parent::add_champs('firstname','type=chaine;');
		

		//clé étrangère : société
		parent::add_champs('fk_user','type=entier;');//fk_soc_leaser
		
		
		parent::_init_vars();
		parent::start();
	}
}


	
	