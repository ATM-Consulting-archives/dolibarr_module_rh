<?php
/**
 * Copyright (C) 2012-2013      Florian Henry  		<florian.henry@open-concept.pro>
 * Copyright (C) 2013       	Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *      \file       agefodd/class/agefodd_session_formateur_calendrier.class.php
 *      \brief      Manage calendar for traner by session
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
require_once("agefodd_formateur.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Agefoddsessionformateurcalendrier extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='agefodd_sessionformateurcalendrier';			//!< Id that identify managed objects
	var $table_element='agefodd_sessionformateurcalendrier';		//!< Name of table without prefix where object is stored

    var $id;

	var $fk_agefodd_session_formateur;
	var $date_session='';
	var $heured='';
	var $heuref='';
	var $trainer_cost;
	var $fk_actioncomm;
	var $fk_user_author;
	var $datec='';
	var $fk_user_mod;
	var $tms='';




    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->fk_agefodd_session_formateur)) $this->fk_agefodd_session_formateur=trim($this->fk_agefodd_session_formateur);
		if (isset($this->trainer_cost)) $this->trainer_cost=trim($this->trainer_cost);
		if (isset($this->fk_actioncomm)) $this->fk_actioncomm=trim($this->fk_actioncomm);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);



		// Check parameters
		// Put here code to add control on parameters values

		if (!empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
			$result = $this->createAction($user);
			if ($result <= 0){
				$error++; $this->errors[]="Error ".$this->db->lasterror();
			}
			else {
				$this->fk_actioncomm=$result;
			}
		}

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."agefodd_session_formateur_calendrier(";
		$sql.= "entity,";
		$sql.= "fk_agefodd_session_formateur,";
		$sql.= "date_session,";
		$sql.= "heured,";
		$sql.= "heuref,";
		$sql.= "trainer_cost,";
		$sql.= "fk_actioncomm,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod";
        $sql.= ") VALUES (";

		$sql.= " '".$conf->entity."',";
		$sql.= " ".(! isset($this->fk_agefodd_session_formateur)?'NULL':"'".$this->fk_agefodd_session_formateur."'").",";
		$sql.= " ".(! isset($this->date_session) || dol_strlen($this->date_session)==0?'NULL':$this->db->idate($this->date_session)).",";
		$sql.= " ".(! isset($this->heured) || dol_strlen($this->heured)==0?'NULL':$this->db->escape($this->db->idate($this->heured))).",";
		$sql.= " ".(! isset($this->heuref) || dol_strlen($this->heuref)==0?'NULL':$this->db->escape($this->db->idate($this->heuref))).",";
		$sql.= " ".(! isset($this->trainer_cost)?'NULL':"'".$this->db->escape($this->trainer_cost)."'").",";
		$sql.= " ".(! isset($this->fk_actioncomm)?'NULL':"'".$this->fk_actioncomm."'").",";
		$sql.= " ".(! isset($this->fk_user_author)?$user->id:"'".$this->fk_user_author."'").",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0?$this->db->idate(dol_now()):$this->db->idate($this->datec)).",";
		$sql.= " ".(! isset($this->fk_user_mod)?$user->id:"'".$this->fk_user_mod."'")."";

		$sql.= ")";
		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."agefodd_session_formateur_calendrier");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.fk_agefodd_session_formateur,";
		$sql.= " t.date_session,";
		$sql.= " t.heured,";
		$sql.= " t.heuref,";
		$sql.= " t.trainer_cost,";
		$sql.= " t.fk_actioncomm,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms";


        $sql.= " FROM ".MAIN_DB_PREFIX."agefodd_session_formateur_calendrier as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
				$this->date_session = $this->db->jdate($obj->date_session);
				$this->heured = $this->db->jdate($obj->heured);
				$this->heuref = $this->db->jdate($obj->heuref);
				$this->trainer_cost = $obj->trainer_cost;
				$this->fk_actioncomm = $obj->fk_actioncomm;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);


            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *  Load object in memory from database
     *
     *  @param	int		$actionid    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch_by_action($actionid)
    {
    	global $langs;

    	$sql = "SELECT";
    	$sql.= " s.rowid, s.date_session, s.heured, s.heuref, s.fk_actioncomm, s.fk_agefodd_session_formateur,s.trainer_cost ";
    	$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_session_formateur_calendrier as s";
    	$sql.= " WHERE s.fk_actioncomm = ".$actionid;

    	dol_syslog(get_class($this)."::fetch_by_action sql=".$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		if ($this->db->num_rows($resql))
    		{
    			$obj = $this->db->fetch_object($resql);
    			$this->id = $obj->rowid;
    			$this->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
    			$this->date_session = $this->db->jdate($obj->date_session);
    			$this->heured = $this->db->jdate($obj->heured);
    			$this->heuref = $this->db->jdate($obj->heuref);
    			$this->sessid = $obj->fk_agefodd_session;
    			$this->trainer_cost = $obj->trainer_cost;
    			$this->fk_actioncomm = $obj->fk_actioncomm;
    		}
    		$this->db->free($resql);

    		return 1;
    	}
    	else
    	{
    		$this->error="Error ".$this->db->lasterror();
    		dol_syslog(get_class($this)."::fetch_by_action ".$this->error, LOG_ERR);
    		return -1;
    	}
    }
	/**
	 *  Load object in memory from database
	 *
	 *  @param	int		$id    Id of session
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function fetch_all($id)
	{
		global $langs;

		$sql = "SELECT ";
		$sql.= "s.rowid,";
		$sql.= "s.fk_agefodd_session_formateur,";
		$sql.= "s.date_session,";
		$sql.= "s.heured,";
		$sql.= "s.heuref,";
		$sql.= "s.trainer_cost,";
		$sql.= "s.fk_actioncomm,";
		$sql.= "s.fk_user_author";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_session_formateur_calendrier as s";
		$sql.= " WHERE s.fk_agefodd_session_formateur = ".$id;
		$sql.= " ORDER BY s.date_session ASC, s.heured ASC";

		dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->lines = array();
			$num = $this->db->num_rows($resql);
			$i = 0;
			for ($i=0; $i < $num; $i++)
			{
				$line = new AgefoddcalendrierformateurLines();

				$obj = $this->db->fetch_object($resql);

				$line->id = $obj->rowid;
				$line->date_session = $this->db->jdate($obj->date_session);
				$line->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
				$line->heured = $this->db->jdate($obj->heured);
				$line->heuref = $this->db->jdate($obj->heuref);
				$line->trainer_cost = $obj->trainer_cost;
				$line->fk_actioncomm = $obj->fk_actioncomm;
				$line->fk_user_author = $obj->fk_user_author;


				$this->lines[$i]=$line;

			}
			$this->db->free($resql);
			return 1;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch_all ".$this->error, LOG_ERR);
			return -1;
		}
	}

    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->fk_agefodd_session_formateur)) $this->fk_agefodd_session_formateur=trim($this->fk_agefodd_session_formateur);
		if (isset($this->trainer_cost)) $this->trainer_cost=trim($this->trainer_cost);
		if (isset($this->fk_actioncomm)) $this->fk_actioncomm=trim($this->fk_actioncomm);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_session_formateur_calendrier SET";

		$sql.= " fk_agefodd_session_formateur=".(isset($this->fk_agefodd_session_formateur)?$this->fk_agefodd_session_formateur:"null").",";
		$sql.= " date_session=".(dol_strlen($this->date_session)!=0 ? "'".$this->db->idate($this->date_session)."'" : 'null').",";
		$sql.= " heured=".(dol_strlen($this->heured)!=0 ? "'".$this->db->idate($this->heured)."'" : 'null').",";
		$sql.= " heuref=".(dol_strlen($this->heuref)!=0 ? "'".$this->db->idate($this->heuref)."'" : 'null').",";
		$sql.= " trainer_cost='".(isset($this->trainer_cost)?$this->trainer_cost:"null")."',";
		$sql.= " fk_actioncomm=".(isset($this->fk_actioncomm)?$this->fk_actioncomm:"null").",";
		$sql.= " fk_user_author=".(isset($this->fk_user_author)?$this->fk_user_author:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " fk_user_mod=".(isset($this->fk_user_mod)?$this->fk_user_mod:"null").",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null')."";


        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


	/**
	 *  Delete object in database
	 *
	 *  @param  int		$id	 id to delete
	 *  @return	 int		 <0 if KO, >0 if OK
	 */
	function remove($id)
	{
		$result = $this->fetch($id);
		if (!empty($this->fk_actioncomm)) {
			dol_include_once('/comm/action/class/actioncomm.class.php');

			$action = new ActionComm($this->db);
			$action->id=$this->fk_actioncomm;
			$action->delete();
		}

		$sql  = "DELETE FROM ".MAIN_DB_PREFIX."agefodd_session_formateur_calendrier";
		$sql .= " WHERE rowid = ".$id;

		dol_syslog(get_class($this)."::remove sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query ($sql);

		if ($resql)
		{
			return 1;
		}
		else
		{
			$this->error=$this->db->lasterror();
			return -1;
		}
	}

	/**
	 *  Create Action in Dolibarr Agenda
	 *
	 *  @param	int			fk_session_place    Location of session
	 *  @param	User	$user        User that modify
	 */
	function createAction($user){

		global $conf, $langs;

		$error = 0;

		dol_include_once('/comm/action/class/actioncomm.class.php');
		dol_include_once('/agefodd/class/agsession.class.php');
		dol_include_once('/agefodd/class/agsession.class.php');

		require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
		require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
		require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';

		$action = new ActionComm($this->db);
		$session = new Agsession($this->db);

		$formateur = new Agefodd_teacher($this->db);
		$formateur->fetch($this->fk_agefodd_session_formateur);

		$result = $session->fetch($this->sessid);
		if ($result < 0) {
			$error ++;
		}

		$action->label =  $session->formintitule.'('.$session->formref.')';
		$action->location =  $session->placecode;
		$action->datep = $this->heured;
		$action->datef = $this->heuref;
		$action->author      = $user;   // User saving action
		$action->fk_element  = $session->id;
		$action->elementtype = $session->element;
		$action->type_code = 'AC_AGF_SESST';


		//Si le formateur est un contact alors sur l'évenement : « Evénement concernant la société » = fournisseur
		//Sinon si le formateur est un user alors « Action affectée » = user correspondant.
		if($formateur->fk_user)
		{
			$userstat= new User($this->db);
			$ret = $userstat->fetch($formateur->fk_user);
			if($ret)
			{
				$action->usertodo = $userstat;

			}
		}
		else
		{
			$contactstat= new Contact($this->db);
			$ret = $contactstat->fetch($formateur->fk_socpeople);
			if($ret) {
				$action->contact = $contactstat;

				$companystat = new Societe($this->db);
				$ret=$companystat->fetch($contactstat->socid);
				if($ret)
					$action->societe=$companystat;

			}
		}

		if ($error == 0) {

			$result = $action->add($user);

			if ($result < 0) {
				$error ++;
				dol_syslog(get_class($this)."::createAction ".$action->error, LOG_ERR);
				return -1;
			}
			else {
				return $result;
			}
		}
		else {
			dol_syslog(get_class($this)."::createAction ".$action->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 *  update Action in Dolibarr Agenda
	 *
	 *  @param	User	$user        User that modify
	 */
	function updateAction($user){

		global $conf, $langs;

		$error = 0;

		dol_include_once('/comm/action/class/actioncomm.class.php');
		dol_include_once('/agefodd/class/agsession.class.php');

		$action = new ActionComm($this->db);
		$session = new Agsession($this->db);

		$result = $session->fetch($this->sessid);
		if ($result < 0) {
			$error ++;
		}

		$result = $action->fetch($this->fk_actioncomm);
		if ($result < 0) {
			$error ++;
		}

		if ($error == 0) {

			if ($action->id==$this->fk_actioncomm){

				$action->label =  $session->formintitule.'('.$session->formref.')';
				$action->location =  $session->placecode;
				$action->datep = $this->heured;
				$action->datef = $this->heuref;
				$action->type_code = 'AC_AGF_SESST';

				$result = $action->update($user);
			}
			else {
				$result = $this->createAction($user);
			}

			if ($result < 0) {
				$error ++;

				dol_syslog(get_class($this)."::updateAction ".$action->error, LOG_ERR);
				return -1;
			}
			else {
				return 1;
			}
		}
		else {
			dol_syslog(get_class($this)."::updateAction ".$action->error, LOG_ERR);
			return -1;
		}
	}


	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Agefoddsessionformateurcalendrier($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;

		$this->fk_agefodd_session_formateur='';
		$this->date_session='';
		$this->heured='';
		$this->heuref='';
		$this->trainer_cost='';
		$this->fk_actioncomm='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';


	}

}


class AgefoddcalendrierformateurLines {
	var $id;
	var $day_session;
	var $heured;
	var $heuref;
}
?>
