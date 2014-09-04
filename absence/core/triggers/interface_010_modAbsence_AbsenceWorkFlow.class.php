<?php

//define('INC_FROM_DOLIBARR', true);
//define('TRIGGER', true);
		
//require('../valideur/config.php');
//require('../valideur/lib/valideur.lib.php');

/**
 *	\file       htdocs/core/triggers/interface_modValideur_ValideurWorkflow.class.php
 *	\class      InterfaceValideurWorkflow
 *  \brief      Class of triggered functions for ndfp module
 */
class InterfaceAbsenceWorkflow
{
    var $db;
    var $error;

    /**
     *   Constructor.
     *   @param      DB      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "absence";
        $this->description = "Triggers of absence module.";
        $this->version = '1.0.0';                        // 'experimental' or 'dolibarr' or version
        $this->picto = 'absence@absence';
    }

    /**
     *   Return name of trigger file
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   Return description of trigger file
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "run_trigger" are triggered if file is inside directory htdocs/includes/triggers
     *
     *      @param      action      Event code (COMPANY_CREATE, PROPAL_VALIDATE, ...)
     *      @param      object      Object action is done on
     *      @param      user        Object user
     *      @param      langs       Object langs
     *      @param      conf        Object conf
     *      @return     int         <0 if KO, 0 if no action are done, >0 if OK
     */
    function run_trigger($action, &$object, $user, $langs, $conf)
    {
        global $db,$langs;
		
		if ($action === 'USER_CREATE' || $action === 'USER_MODIFY') {
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->rowid);
			
			define('INC_FROM_DOLIBARR', true);
	        dol_include_once('/absence/config.php');
		    dol_include_once('/valideur/lib/valideur.lib.php');
			dol_include_once('/absence/class/absence.class.php');
			
				
			$ATMdb=new TPDOdb;
			
			if($object->id>0) {
				$compteur=new TRH_Compteur;
				if(!$compteur->load_by_fkuser($ATMdb, $object->id)) {
					
						$compteur->initCompteur($ATMdb,$object->id );
					
						$compteur->save($ATMdb);
					
				}
				
				$emploi = new TRH_EmploiTemps;
				
				if(!$emploi->load_by_fkuser($ATMdb, $object->id)) {
					
						$emploi->initCompteurHoraire($ATMdb,$object->id );
					
						$emploi->save($ATMdb);
					
				}
				
				
			}
			
		} elseif ($action === 'ABSENCE_CREATE') {
				
			global $user, $db;
				
			$ATMdb=new TPDOdb;
			$object->calculDureeAbsenceParAddition($ATMdb);
			$object->TDureeAllAbsenceUser = $object->TDureeAbsenceUser;
			
			$this->_loadDureeAllAbsenceUser($ATMdb, $object);
			
			$this->_absenceEstValide($ATMdb, $object);
			
		}
		
		
		return 0;
    }

	function _loadDureeAllAbsenceUser(&$ATMdb, &$object) {
		
		global $db;
		
		// On récupère toutes les absences contenues dans le ou les mois sur le(s)quel(s) se trouve la plage de congés
		$sql = $object->rechercheAbsenceUser($ATMdb,$this->fk_user, date("Y-m-01 H:i:s", $object->date_debut), date("Y-m-".date("t", date("m", $object->date_fin))." H:i:s", $object->date_fin), 'Tous');

		$resql = $db->query($sql);
		//$this->_nbJoursConsecutifsInferieurATrois($ATMdb, $object);
		while($res = $db->fetch_object($resql)) {
			
			$abs = new TRH_Absence;
			$abs->load($ATMdb, $res->ID);
			$abs->calculDureeAbsenceParAddition($ATMdb);
			
			foreach($abs->TDureeAbsenceUser as $annee => $tabMonth) {
				foreach($tabMonth as $month => $duree) {
					@$object->TDureeAllAbsenceUser[$annee][$month] += $duree;
				}
			}
			
		}
		
	}
	
	function _absenceEstValide(&$ATMdb, &$object) {
		
		if($this->_nbJoursTotalInferieureNbJoursMax($object->TDureeAllAbsenceUser) && $this->_nbJoursConsecutifsInferieurNbMax($ATMdb, $object))
			return true;
		else 
			return false;
		
	}
	
	function _nbJoursTotalInferieureNbJoursMax($TDureeAllAbsenceUser) {

		foreach($TDureeAllAbsenceUser as $annee => $tabMonth) {
			foreach($tabMonth as $duree) {
				if($duree > 2) {
					return false;
				}
			}
		}
		
		return true;

	}
	
	function _nbJoursConsecutifsInferieurNbMax(&$ATMdb, &$object) {
		
		global $TJourNonTravailleEntreprise, $langs;
		
		$TTradJoursSemaine = array(
						"Mon"=>"lundi"
						,"Tue"=>"mardi"
						,"Wed"=>"mercredi"
						,"Thu"=>"jeudi"
						,"Fri"=>"vendredi"
						,"Sat"=>"samedi"
						,"Sun"=>"dimanche"
					);
		
		$t_start = $object->date_debut;
		$t_end = $object->date_fin;
		$t_current = $t_start;
		
		$TJoursFeries = $object->getJourFerie($ATMdb);

		$nbJoursConsecutifs = 0;
		$nbJoursConsecutifsMax = 0;
		
		while($t_current<=$t_end) {
			//echo isset($TJoursFeries[date('Y-m-d', $t_current)]);
			if(!in_array($TTradJoursSemaine[date('D', $t_current)], $TJourNonTravailleEntreprise) && !isset($TJoursFeries[date('Y-m-d', $t_current)])){
				$nbJoursConsecutifs++;
				if($nbJoursConsecutifs > $nbJoursConsecutifsMax) {
					$nbJoursConsecutifsMax = $nbJoursConsecutifs;
				}
			}
			else
				$nbJoursConsecutifs = 0;
			
			$t_current = strtotime("+1day",$t_current);
			
		}
		
		if($nbJoursConsecutifsMax < 3)
			return true;
		else
			return false;
		
	}

}
?>
