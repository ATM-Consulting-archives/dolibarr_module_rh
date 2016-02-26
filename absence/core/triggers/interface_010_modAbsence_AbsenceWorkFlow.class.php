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
			
		} 
		elseif ($action === 'ABSENCE_BEFORECREATE') {
				
			global $user, $db,$langs;
				
			define('INC_FROM_DOLIBARR', true);
	        dol_include_once('/absence/config.php');	
		
			$ATMdb=new TPDOdb;
			$demandeRecevable=$object->testDemande($ATMdb, $object->fk_user, $object);

			if($demandeRecevable==1 || $demandeRecevable==2){
						
				if($demandeRecevable==2) {
					$object->avertissementInfo.=$langs->trans('AbsencesPresencesRequestRulesMaxTimeOut');
					$object->avertissement=1;
				}	
				
				return 1;
			}
			else{
				
				$this->error =$langs->trans('AbsencesPresencesRequestRulesMaxTimeOutRestrictif');;
				
				return -1;
			}
			
			
		}
		elseif ($action === 'ABSENCE_BEFOREVALIDATE') {
			
			if($object->isPresence==0) {
				$u=new User($db);
				$u->fetch($object->fk_user);
				$u->getrights('absence');
				
				if($u->rights->absence->myactions->alertAllMyCoWorker) {
					define('INC_FROM_DOLIBARR', true);
					dol_include_once('/absence/config.php');

                	                $ATMdb=new TPDOdb;
                                	
					// Gestion type absence
					$the_type='absent';
                        	        $typeAbs = new TRH_TypeAbsence;
        	                        $typeAbs->load_by_type($ATMdb, $object->type);
	                                if($typeAbs->isPresence)
					{
						$the_type='present';
					}
					if($object->date_debut == $object->date_debut) {
						$dateInterval = 'le '.dol_print_date($object->date_debut);
					}
					else{
						$dateInterval = 'du '.dol_print_date($object->date_debut).' '.$langs->trans('to').' '.dol_print_date($object->date_fin);	
					}
					
					
					
					$Tab = $ATMdb->ExecuteAsArray("SELECT gu.fk_user 
						FROM ".MAIN_DB_PREFIX."usergroup_user gu 
						WHERE gu.fk_usergroup IN ( SELECT DISTINCT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE fk_user=".$u->id." )	 
						AND gu.fk_user NOT IN (".$u->id.") 
					");
					
					foreach($Tab as $row) {
						
						$uMail = new User($db);
						$uMail->fetch($row->fk_user);

						if( $uMail->email ) {
							$TBS=new TTemplateTBS;						
							$html = $TBS->render( dol_buildpath('/absence/tpl/mail.absence.alert.coworkers.tpl.php')
								,array() 
								,array(
									'mail'=>array(
										'name'=>$uMail->getNomUrl()
										,'collabName'=>$u->getNomUrl()
										,'DateInterval'=>$dateInterval
										,'theType'=>$the_type
									)
								)
							);
							
							$rep=new TReponseMail($conf->global->RH_USER_MAIL_SENDER, $uMail->email,"Votre collaborateur sera ".$the_type, $html);
							$rep->send();
							
						}
						
					}
					
				}
				

			}
			
			
			return 0;
		}

		return 0;
    }

	
	

}
?>
