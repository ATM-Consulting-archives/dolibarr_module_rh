<?php

class TRH_Pointeuse extends TObjetStd {
	function __construct() { /* declaration */
		
		//conges N
		parent::set_table(MAIN_DB_PREFIX.'rh_pointeuse');
		
		parent::add_champs('date_deb_am,date_fin_am,date_deb_pm,date_fin_pm,date_jour','type=date;');
		
	}
}