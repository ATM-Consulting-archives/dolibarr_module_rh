<?php

class TRH_Pointeuse extends TObjetStd {
	function __construct() { /* declaration */
		$this->set_table(MAIN_DB_PREFIX.'rh_pointeuse');
		
		$this->add_champs('date_deb_am,date_fin_am,date_deb_pm,date_fin_pm,date_jour','type=date;');
		$this->add_champs('time_presence', 'type=entier;');
		$this->add_champs('fk_user','type=entier;index;');
		
		$this->_init_vars();
		
		$this->start();
		
		
		$this->date_deb_am=$this->date_fin_am=$this->date_deb_pm=$this->date_fin_pm=0;
		
		
	}
	function loadByDate(&$ATMdb, $date) {
		$this->loadBy($ATMdb, $date, 'date_jour');
	}
	
	function save(&$ATMdb) {
		$this->get_time_presence();
		parent::save($ATMdb);
	}
	function get_time_presence() {
		
		if($this->date_fin_am==0 || $this->date_deb_pm==0) {
			$this->time_presence = $this->date_fin_pm - $this->date_deb_am;
		}
		else {
			$this->time_presence = ($this->date_fin_am - $this->date_deb_am) + ( $this->date_fin_pm - $this->date_deb_pm );
		}	
		
	
	}
}