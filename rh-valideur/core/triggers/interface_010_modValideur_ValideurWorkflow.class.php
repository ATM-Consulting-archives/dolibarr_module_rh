<?php

//define('INC_FROM_CRON_SCRIPT', true);
require('../valideur/config.php');
require('../valideur/lib/valideur.lib.php');

/**
 *	\file       htdocs/core/triggers/interface_modValideur_ValideurWorkflow.class.php
 *	\class      InterfaceValideurWorkflow
 *  \brief      Class of triggered functions for ndfp module
 */
class InterfaceValideurWorkflow
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
        $this->family = "valideur";
        $this->description = "Triggers of valideur module.";
        $this->version = '1.3.0';                        // 'experimental' or 'dolibarr' or version
        $this->picto = 'ndfp@ndfp';
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
    function run_trigger($action, $object, $user, $langs, $conf)
    {
        $langs->load("other");
        $langs->load('ndfp@ndfp');
		
		// Statut 1 : validée
		if($object->statut==1){
			// Actions
			if ($action == 'NDFP_VALIDATE'){
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->rowid);
				
				return send_mail_validate($this->db, $object, $user, $langs,1);
				
			}elseif ($action == 'NDFP_CANCELED'){
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->rowid);
				
				return send_mail_validate($this->db, $object, $user, $langs,0);
				
			}
		}

		return 0;
    }

}
?>
