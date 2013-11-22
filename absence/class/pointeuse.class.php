<?php

class TRH_Pointeuse extends TObjetStd {
	function __construct() { /* declaration */
		$this->set_table(MAIN_DB_PREFIX.'rh_pointeuse');
		
		$this->add_champs('date_deb_am,date_fin_am,date_deb_pm,date_fin_pm,date_jour','type=date;');
		
		$this->add_champs('fk_user','type=entier;index;');
		
		$this->_init_vars();
		
		$this->start();
		
		
		$this->date_deb_am=$this->date_fin_am=$this->date_deb_pm=$this->date_fin_pm=0;
		
		
	}
	function loadByDate(&$ATMdb, $date) {
		$this->loadBy($ATMdb, $date, 'date_jour');
	}
}