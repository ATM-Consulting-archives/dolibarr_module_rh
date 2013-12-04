<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013 Florian Henry <florian.henry@open-concept.pro>
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
 *  \file       agefodd/class/agefodd_formation_curus.class.php
 *  \ingroup    agefodd
 *  \brief      class to manage 'training program' link to training on agefodd module
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Agefodd_formation_cursus extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='agefodd_formation_cursus';			//!< Id that identify managed objects
	var $table_element='agefodd_formation_cursus';		//!< Name of table without prefix where object is stored
	protected $ismultientitymanaged = 1;  // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	
    var $id;
    
	var $entity;
	var $fk_formation_catalogue;
	var $fk_cursus;
	var $fk_user_author;
	var $datec='';
	var $fk_user_mod;
	var $tms='';
	
	var $lines=array();

    


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
        
		if (isset($this->fk_formation_catalogue)) $this->fk_formation_catalogue=trim($this->fk_formation_catalogue);
		if (isset($this->fk_cursus)) $this->fk_cursus=trim($this->fk_cursus);

        

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."agefodd_formation_cursus(";
		
		$sql.= "entity,";
		$sql.= "fk_formation_catalogue,";
		$sql.= "fk_cursus,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->fk_formation_catalogue)?'NULL':"'".$this->fk_formation_catalogue."'").",";
		$sql.= " ".(! isset($this->fk_cursus)?'NULL':"'".$this->fk_cursus."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " '".$this->db->idate(dol_now())."',";
		$sql.= " ".$user->id;

        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."agefodd_formation_cursus");

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
		
		$sql.= " t.entity,";
		$sql.= " t.fk_formation_catalogue,";
		$sql.= " t.fk_cursus,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms";

		
        $sql.= " FROM ".MAIN_DB_PREFIX."agefodd_formation_cursus as t";
        $sql.= " WHERE t.rowid = ".$id;
        $sql .= " AND t.entity IN (" . getEntity ( 'agsession' ) . ")";

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
				$this->entity = $obj->entity;
				$this->fk_formation_catalogue = $obj->fk_formation_catalogue;
				$this->fk_cursus = $obj->fk_cursus;
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
        
		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->fk_formation_catalogue)) $this->fk_formation_catalogue=trim($this->fk_formation_catalogue);
		if (isset($this->fk_cursus)) $this->fk_cursus=trim($this->fk_cursus);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);

        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_formation_cursus SET";
        
		$sql.= " entity=".$conf->entity.",";
		$sql.= " fk_formation_catalogue=".(isset($this->fk_formation_catalogue)?$this->fk_formation_catalogue:"null").",";
		$sql.= " fk_cursus=".(isset($this->fk_cursus)?$this->fk_cursus:"null").",";
		$sql.= " fk_user_mod=".$user->id;

        
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
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."agefodd_formation_cursus";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
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
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Agefoddformationcursus($this->db);

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
		
		$this->entity='';
		$this->fk_formation_catalogue='';
		$this->fk_cursus='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';

		
	}
	
	/**
	 *  Load object in memory from database
	 *
	 *  @param	string $sortorder    Sort Order
	 *  @param	string $sortfield    Sort field
	 *  @param	int $limit    	offset limit
	 *  @param	int $offset    	offset limit
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function fetch_formation_per_cursus($sortorder="ASC", $sortfield="f.ref", $limit=0, $offset=0 ) {
		global $langs;
		
		$sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " f.ref_interne,";
		$sql.= " f.ref,";
		$sql.= " f.entity,";
		$sql.= " f.intitule,";
		$sql.= " f.archive";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_formation_cursus as t";
		$sql.= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_cursus as c ON t.fk_cursus=c.rowid";
		$sql.= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue as f ON t.fk_formation_catalogue=f.rowid"; 
		$sql.= " WHERE f.entity IN (".getEntity('agsession').") AND c.entity IN (".getEntity('agcursus').")";
		$sql.= " AND fk_cursus=".$this->fk_cursus;
		$sql.= " ORDER BY ".$sortfield." ".$sortorder." ";
		if (!empty($limit)) {
			$sql.= $this->db->plimit( $limit + 1 ,$offset);
		}
		
		dol_syslog(get_class($this)."::fetch_formation_per_cursus sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{	
			$this->line = array();
			$num = $this->db->num_rows($resql);
		
			while( $obj = $this->db->fetch_object($resql))
			{
				$line = new AgfCursusTrainingLine();
		
				$line->id    = $obj->rowid;
		
				$line->ref_interne = $obj->ref_interne;
				$line->ref = $obj->ref;
				$line->intitule = $obj->intitule;
				$line->archive = $obj->archive;
		
				$this->lines[]=$line;

			}
			$this->db->free($resql);
			return $num;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch_formation_per_cursus ".$this->error, LOG_ERR);
			return -1;
		}
	}

}

Class AgfCursusTrainingLine {
	
	
	var $id;
	
	var $ref_interne;
	var $ref;
	var $entity;
	var $intitule;
	var $archive;
	
	function __construct()
	{
		return 1;
	}
}
