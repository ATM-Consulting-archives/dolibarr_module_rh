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

	/**
	 * Charge l'attribut TDureeAllAbsenceUser de l'objet absence associant à chaque mois de chaque année une durée total de congés pris ou demandés
	 * @param object $objet : objet absence
	 */
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
	
	/**
	 * Vérifie si une absence est valide en fonction du nombre de jours demandé et du nombre de jours consécutifs
	 * @param object $objet objet absence
	 * @return bool 1 ok, 0 ko
	 */
	function _absenceEstValide(&$ATMdb, &$object) {
		
		$codeRetour_totalInfNbJourMax = $this->_nbJoursTotalInferieureNbJoursMax($object->TDureeAllAbsenceUser);
		$codeRetour_consecutifsInfNbJourMax = $this->_nbJoursConsecutifsInferieurNbMax($ATMdb, $object);
		
		if($codeRetour_totalInfNbJourMax && $codeRetour_consecutifsInfNbJourMax)
			return true;
		else if($codeRetour_totalInfNbJourMax == 0)
			return false;
		else if(!$codeRetour_totalInfNbJourMax == -1)
			return -1;
		else if(!$codeRetour_consecutifsInfNbJourMax)
			return -2;
		
	}
	
	/**
	 * Retourne un tableau contenant les règles sur le Home office qui concernent l'utilisateur courant
	 * @return array $tabRegles tableau de règles sur le home Office par lesquelles l'utilisateur courant est concerné
	 */
	function _getReglesHomeOffice() {
			
		global $db, $user;
		
		$tabRegles = array();
		
		$user_group = new UserGroup($db);
		$TGroups_of_user = $user_group->listGroupsForUser($user->id);
		if(count($TGroups_of_user) > 0) $TGroups_of_user = array_keys($TGroups_of_user);
		
		$sql = "SELECT rowid, nbJourCumulable, restrictif, periode";
		$sql.= " FROM ".MAIN_DB_PREFIX.'rh_absence_regle';
		$sql.= " WHERE (fk_user = ".$user->id;
		if(count($TGroups_of_user) > 0) $sql.= " OR fk_usergroup IN (".implode(",", $TGroups_of_user).")";
		$sql.= ")";
		$sql.= ' AND typeAbsence = "HomeOffice"';
		
		$resql = $db->query($sql);
		if($resql->num_rows > 0) {
			while($res = $db->fetch_object($resql)) {
				$tabRegles[] = $res;
			}
		}
		//echo $sql;exit;
		return $tabRegles;
		
	}
	
	/**
	 * Vérifie si le nombre de jours total de congés de l'utilisateur est inférieure au nombre total de jours autorisé par les règles sur le home office
	 * @param array $TDureeAllAbsenceUser Tableau de l'objet absence qui associe à chaque mois de chaque année une durée total de congés pris ou demandés
	 * @param int 1 ok, 0 : nb jours demandés supérieur à total année, -1 : nb jours demandés supérieur à total mois
	 */
	function _nbJoursTotalInferieureNbJoursMax($TDureeAllAbsenceUser) {

		// On charge les règles de HomeOffice
		$tabReglesHomeOffice = $this->_getReglesHomeOffice();
		
		// On récupère la règle qui concerne le nombre de jours à ne pas dépasser

		if(is_array($tabReglesHomeOffice) && count($tabReglesHomeOffice) > 0) {
			foreach($tabReglesHomeOffice as $obj_sql_regle) {
					
				$nbJoursAutorises = $obj_sql_regle->nbJourCumulable;
				
				if($obj_sql_regle->periode === "YEAR") {
					$typePlage = "YEAR";

					foreach($TDureeAllAbsenceUser as $annee => $tabMonth) {
						
						// On calcule le nombre de jour total par an
						$dureeTotale = 0;
						foreach($tabMonth as $duree) {
							$dureeTotale += $duree;
						}
						// Si le nombre de jours total par an est supérieur au nb autorisé, on retourn false
						if($dureeTotale > $nbJoursAutorises) {
							return false;
						}
					}
					
				} else if($obj_sql_regle->periode === "MONTH") {
					$typePlage = "MONTH";
					
					foreach($TDureeAllAbsenceUser as $annee => $tabMonth) {
						foreach($tabMonth as $duree) {
							if($duree > $nbJoursAutorises) {
								return -1;
							}
						}
					}
					
				}
			}
		}

		return true;

	}
	
	/**
	 * Vérifie si le nombre de jours de congés consécutifs maximum de l'utilisateur est inférieure au nombre total de jours consécutifs maximal autorisé par les règles sur le home office
	 * @param object $object objet absence
	 * @param bool 1 ok, 0 ko
	 */
	function _nbJoursConsecutifsInferieurNbMax(&$ATMdb, &$object) {
		
		global $TJourNonTravailleEntreprise, $langs;
		
		// On charge les règles de HomeOffice
		$tabReglesHomeOffice = $this->_getReglesHomeOffice();
		
		// On récupère la règle qui concerne les jours consécutifs
		$regle_existe = false;
		if(is_array($tabReglesHomeOffice) && count($tabReglesHomeOffice) > 0) {
			foreach($tabReglesHomeOffice as $obj_sql_regle) {
				if($obj_sql_regle->periode === "ONE") {
					$nbJoursConsecutifsAutorises = $obj_sql_regle->nbJourCumulable;
					$regle_existe = true;
				}
			}
		}
		
		// Si aucune règle existe, on ne fait aucun traitement
		if(!$regle_existe) return true;
		
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
		
		if($nbJoursConsecutifsMax <= $nbJoursConsecutifsAutorises)
			return true;
		else
			return false;
		
	}

}
?>
