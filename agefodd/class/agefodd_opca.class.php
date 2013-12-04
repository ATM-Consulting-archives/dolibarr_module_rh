<?php
/** Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2012      Florian Henry       <florian.henry@open-concept.pro>
* Copyright (C) 2012      JF FERRY            <jfefe@aternatik.fr>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
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
 *  \file       agefodd/class/agefodd_opca.class.php
 *  \ingroup    agefodd
 *  \brief      class to manage 'OPCA' on agefodd module
*/

// Put here all includes required by your class file
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once('agefodd_session.php');


/**
 *	Put here description of your class
*/
class Agefodd_opca // extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	//var $element='agefoddopca';			//!< Id that identify managed objects
	//var $table_element='agefoddopca';	//!< Name of table without prefix where object is stored

	var $id;

	var $fk_soc_trainee;
	var $fk_session_agefodd;
	var $date_ask_OPCA='';
	var $is_date_ask_OPCA;
	var $is_OPCA;
	var $fk_soc_OPCA;
	var $fk_socpeople_OPCA;
	var $num_OPCA_soc;
	var $num_OPCA_file;
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
	 *  @param	User	$user        User that create
	 *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
	 *  @return int      		   	 <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->fk_soc_trainee)) $this->fk_soc_trainee=trim($this->fk_soc_trainee);
		if (isset($this->fk_session_agefodd)) $this->fk_session_agefodd=trim($this->fk_session_agefodd);
		if (isset($this->is_date_ask_OPCA)) $this->is_date_ask_OPCA=trim($this->is_date_ask_OPCA);
		if (isset($this->is_OPCA)) $this->is_OPCA=trim($this->is_OPCA);
		if (isset($this->fk_soc_OPCA)) $this->fk_soc_OPCA=trim($this->fk_soc_OPCA);
		if (isset($this->fk_socpeople_OPCA)) $this->fk_socpeople_OPCA=trim($this->fk_socpeople_OPCA);
		if (isset($this->num_OPCA_soc)) $this->num_OPCA_soc=trim($this->num_OPCA_soc);
		if (isset($this->num_OPCA_file)) $this->num_OPCA_file=trim($this->num_OPCA_file);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);



		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."agefodd_opca(";

		$sql.= "fk_soc_trainee,";
		$sql.= "fk_session_agefodd,";
		$sql.= "date_ask_OPCA,";
		$sql.= "is_date_ask_OPCA,";
		$sql.= "is_OPCA,";
		$sql.= "fk_soc_OPCA,";
		$sql.= "fk_socpeople_OPCA,";
		$sql.= "num_OPCA_soc,";
		$sql.= "num_OPCA_file,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod,";


		$sql.= ") VALUES (";

		$sql.= " ".(! isset($this->fk_soc_trainee)?'NULL':"'".$this->fk_soc_trainee."'").",";
		$sql.= " ".(! isset($this->fk_session_agefodd)?'NULL':"'".$this->fk_session_agefodd."'").",";
		$sql.= " ".(! isset($this->date_ask_OPCA) || dol_strlen($this->date_ask_OPCA)==0?'NULL':$this->db->idate($this->date_ask_OPCA)).",";
		$sql.= " ".(! isset($this->is_date_ask_OPCA)?'NULL':"'".$this->is_date_ask_OPCA."'").",";
		$sql.= " ".(! isset($this->is_OPCA)?'NULL':"'".$this->is_OPCA."'").",";
		$sql.= " ".(! isset($this->fk_soc_OPCA)?'NULL':"'".$this->fk_soc_OPCA."'").",";
		$sql.= " ".(! isset($this->fk_socpeople_OPCA)?'NULL':"'".$this->fk_socpeople_OPCA."'").",";
		$sql.= " ".(! isset($this->num_OPCA_soc)?'NULL':"'".$this->db->escape($this->num_OPCA_soc)."'").",";
		$sql.= " ".(! isset($this->num_OPCA_file)?'NULL':"'".$this->db->escape($this->num_OPCA_file)."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " '".$this->db->idate(dol_now())."',";
		$sql.= " ".$user->id;


		$sql.= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."agefodd_opca");

			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				//// Call triggers
				//include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
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
	 *  Load object in memory from database
	 *
	 *  @param	int		$id    Id object
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function fetch($id)
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.fk_soc_trainee,";
		$sql.= " t.fk_session_agefodd,";
		$sql.= " t.date_ask_OPCA,";
		$sql.= " t.is_date_ask_OPCA,";
		$sql.= " t.is_OPCA,";
		$sql.= " t.fk_soc_OPCA,";
		$sql.= " t.fk_socpeople_OPCA,";
		$sql.= " t.num_OPCA_soc,";
		$sql.= " t.num_OPCA_file,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms";


		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_opca as t";
		$sql.= " WHERE t.rowid = ".$id;

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id    = $obj->rowid;

				$this->fk_soc_trainee = $obj->fk_soc_trainee;
				$this->fk_session_agefodd = $obj->fk_session_agefodd;
				$this->date_ask_OPCA = $this->db->jdate($obj->date_ask_OPCA);
				$this->is_date_ask_OPCA = $obj->is_date_ask_OPCA;
				$this->is_OPCA = $obj->is_OPCA;
				$this->fk_soc_OPCA = $obj->fk_soc_OPCA;
				$this->fk_socpeople_OPCA = $obj->fk_socpeople_OPCA;
				$this->num_OPCA_soc = $obj->num_OPCA_soc;
				$this->num_OPCA_file = $obj->num_OPCA_file;
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
	 *  @param	User	$user        User that modify
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return int     		   	 <0 if KO, >0 if OK
	 */
	function update($user=0, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->fk_soc_trainee)) $this->fk_soc_trainee=trim($this->fk_soc_trainee);
		if (isset($this->fk_session_agefodd)) $this->fk_session_agefodd=trim($this->fk_session_agefodd);
		if (isset($this->is_date_ask_OPCA)) $this->is_date_ask_OPCA=trim($this->is_date_ask_OPCA);
		if (isset($this->is_OPCA)) $this->is_OPCA=trim($this->is_OPCA);
		if (isset($this->fk_soc_OPCA)) $this->fk_soc_OPCA=trim($this->fk_soc_OPCA);
		if (isset($this->fk_socpeople_OPCA)) $this->fk_socpeople_OPCA=trim($this->fk_socpeople_OPCA);
		if (isset($this->num_OPCA_soc)) $this->num_OPCA_soc=trim($this->num_OPCA_soc);
		if (isset($this->num_OPCA_file)) $this->num_OPCA_file=trim($this->num_OPCA_file);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);



		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_opca SET";

		$sql.= " fk_soc_trainee=".(isset($this->fk_soc_trainee)?$this->fk_soc_trainee:"null").",";
		$sql.= " fk_session_agefodd=".(isset($this->fk_session_agefodd)?$this->fk_session_agefodd:"null").",";
		$sql.= " date_ask_OPCA=".(dol_strlen($this->date_ask_OPCA)!=0 ? "'".$this->db->idate($this->date_ask_OPCA)."'" : 'null').",";
		$sql.= " is_date_ask_OPCA=".(isset($this->is_date_ask_OPCA)?$this->is_date_ask_OPCA:"null").",";
		$sql.= " is_OPCA=".(isset($this->is_OPCA)?$this->is_OPCA:"null").",";
		$sql.= " fk_soc_OPCA=".(isset($this->fk_soc_OPCA)?$this->fk_soc_OPCA:"null").",";
		$sql.= " fk_socpeople_OPCA=".(isset($this->fk_socpeople_OPCA)?$this->fk_socpeople_OPCA:"null").",";
		$sql.= " num_OPCA_soc=".(isset($this->num_OPCA_soc)?"'".$this->db->escape($this->num_OPCA_soc)."'":"null").",";
		$sql.= " num_OPCA_file=".(isset($this->num_OPCA_file)?"'".$this->db->escape($this->num_OPCA_file)."'":"null").",";
		$sql.= " fk_user_author=".(isset($this->fk_user_author)?$this->fk_user_author:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " fk_user_mod=".(isset($this->fk_user_mod)?$this->fk_user_mod:"null").",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null')."";


		$sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				//// Call triggers
				//include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
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
	 *	@param  User	$user        User that delete
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
				// want this action call a trigger.

				//// Call triggers
				//include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}

		if (! $error)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."agefodd_opca";
			$sql.= " WHERE rowid=".$this->id;

			dol_syslog(get_class($this)."::delete sql=".$sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error++; $this->errors[]="Error ".$this->db->lasterror();
			}
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

		$object=new Agefoddopca($this->db);

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

		$this->fk_soc_trainee='';
		$this->fk_session_agefodd='';
		$this->date_ask_OPCA='';
		$this->is_date_ask_OPCA='';
		$this->is_OPCA='';
		$this->fk_soc_OPCA='';
		$this->fk_socpeople_OPCA='';
		$this->num_OPCA_soc='';
		$this->num_OPCA_file='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';


	}

}
?>
