<?php
/** Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013
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
 *  \file       dev/skeletons/agefoddtrainingadmlevel.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Initialy built by build_class_from_table on 2013-07-03 15:18
*/

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");


/**
 *	Put here description of your class
*/
class Agefodd_training_admlevel extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='agefodd_training_admlevel';			//!< Id that identify managed objects
	var $table_element='agefodd_training_admlevel';		//!< Name of table without prefix where object is stored

	var $id;

	var $fk_training;
	var $level_rank;
	var $fk_parent_level;
	var $indice;
	var $intitule;
	var $delais_alerte;
	var $fk_user_author;
	var $datec='';
	var $fk_user_mod;
	var $tms='';
	var $fk_agefodd_training_admlevel;
	
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

		if (isset($this->fk_training)) $this->fk_training=trim($this->fk_training);
		if (isset($this->level_rank)) $this->level_rank=trim($this->level_rank);
		if (isset($this->fk_parent_level)) $this->fk_parent_level=trim($this->fk_parent_level);
		if (isset($this->indice)) $this->indice=trim($this->indice);
		if (isset($this->intitule)) $this->intitule=trim($this->intitule);
		if (isset($this->delais_alerte)) $this->delais_alerte=trim($this->delais_alerte);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);



		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."agefodd_training_admlevel(";

		$sql.= "fk_agefodd_training_admlevel,";
		$sql.= "fk_training,";
		$sql.= "level_rank,";
		$sql.= "fk_parent_level,";
		$sql.= "indice,";
		$sql.= "intitule,";
		$sql.= "delais_alerte,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod";


		$sql.= ") VALUES (";

		$sql.= " ".(empty($this->fk_agefodd_training_admlevel)?'0':"'".$this->fk_agefodd_training_admlevel."'").",";
		$sql.= " ".(! isset($this->fk_training)?'NULL':"'".$this->fk_training."'").",";
		$sql.= " ".(! isset($this->level_rank)?'NULL':"'".$this->level_rank."'").",";
		$sql.= " ".(! isset($this->fk_parent_level)?'NULL':"'".$this->fk_parent_level."'").",";
		$sql.= " ".(empty($this->indice)?'0':"'".$this->indice."'").",";
		$sql.= " ".(! isset($this->intitule)?'NULL':"'".$this->db->escape($this->intitule)."'").",";
		$sql.= " ".(empty($this->delais_alerte)?'0':$this->delais_alerte).",";
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
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."agefodd_training_admlevel");

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

		$sql.= " t.fk_training,";
		$sql.= " t.level_rank,";
		$sql.= " t.fk_parent_level,";
		$sql.= " t.indice,";
		$sql.= " t.intitule,";
		$sql.= " t.delais_alerte,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms,";
		$sql.= " t.fk_agefodd_training_admlevel";


		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_training_admlevel as t";
		$sql.= " WHERE t.rowid = ".$id;

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id    = $obj->rowid;

				$this->fk_training = $obj->fk_training;
				$this->level_rank = $obj->level_rank;
				$this->fk_parent_level = $obj->fk_parent_level;
				$this->indice = $obj->indice;
				$this->intitule = $obj->intitule;
				$this->delais_alerte = $obj->delais_alerte;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);
				$this->fk_agefodd_training_admlevel = $obj->fk_agefodd_training_admlevel;


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
	 *  @param	int		$training_id    Id object
	 *  @return array          	array of object
	 */
	function fetch_all($training_id)
	{
		global $langs;

		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.fk_training,";
		$sql.= " t.level_rank,";
		$sql.= " t.fk_parent_level,";
		$sql.= " t.indice,";
		$sql.= " t.intitule,";
		$sql.= " t.delais_alerte,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.datec,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.tms,";
		$sql.= " t.fk_agefodd_training_admlevel";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_training_admlevel as t";
		$sql.= " WHERE t.fk_training=".$training_id;
		$sql.= " ORDER BY t.indice";

		dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);

		if ($resql)
		{
			$this->line = array();
			$num = $this->db->num_rows($resql);
			$i = 0;

			while( $i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$line=new AgfTrainingAdmlvlLine();

				$line->rowid = $obj->rowid;
				$line->fk_training = $obj->fk_training;
				$line->level_rank = $obj->level_rank;
				$line->fk_parent_level = $obj->fk_parent_level;
				$line->indice = $obj->indice;
				$line->intitule = $obj->intitule;
				$line->alerte = $obj->delais_alerte;
				$line->fk_agefodd_training_admlevel = $obj->fk_agefodd_training_admlevel;
				 
				$this->lines[$i]=$line;
				$i++;
			}
			$this->db->free($resql);
			return $num;
			//return 1;
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

		if (isset($this->fk_training)) $this->fk_training=trim($this->fk_training);
		if (isset($this->level_rank)) $this->level_rank=trim($this->level_rank);
		if (isset($this->fk_parent_level)) $this->fk_parent_level=trim($this->fk_parent_level);
		if (isset($this->indice)) $this->indice=trim($this->indice);
		if (isset($this->intitule)) $this->intitule=trim($this->intitule);
		if (isset($this->delais_alerte)) $this->delais_alerte=trim($this->delais_alerte);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);



		// Check parameters
		// Put here code to add a control on parameters values

		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_training_admlevel SET";

		$sql.= " fk_training=".(isset($this->fk_training)?$this->fk_training:"null").",";
		$sql.= " fk_agefodd_training_admlevel=".(isset($this->fk_agefodd_training_admlevel)?$this->fk_agefodd_training_admlevel:"null").",";
		$sql.= " level_rank=".(isset($this->level_rank)?$this->level_rank:"null").",";
		$sql.= " fk_parent_level=".(isset($this->fk_parent_level)?$this->fk_parent_level:"null").",";
		$sql.= " indice=".(isset($this->indice)?$this->indice:"null").",";
		$sql.= " intitule=".(isset($this->intitule)?"'".$this->db->escape($this->intitule)."'":"null").",";
		$sql.= " delais_alerte=".(isset($this->delais_alerte)?$this->delais_alerte:"null").",";
		$sql.= " fk_user_mod=".$user->id;

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
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."agefodd_training_admlevel";
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

		$object=new Agefoddtrainingadmlevel($this->db);

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

		$this->fk_training='';
		$this->level_rank='';
		$this->fk_parent_level='';
		$this->indice='';
		$this->intitule='';
		$this->delais_alerte='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';


	}

	/**
	 *  shift indice object into database
	 *
	 *  @param	User	$user        User that modify
	 *  @param str		$type		 less for -1 more for +1
	 *  @param  $notrigger int			 0=launch triggers after, 1=disable triggers
	 *  @return int     		   	 <0 if KO, >0 if OK
	 */
	function shift_indice($user, $type='', $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters
		if (isset($this->indice)) $this->indice=trim($this->indice);

		$this->db->begin();

		if ($type=='less')
		{
			if ($this->level_rank!='0')
			{
				$this->indice=intval(intval($this->indice)-1);
				// Update request
				$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_training_admlevel SET";
					
				$sql.= " indice=".(isset($this->indice)?intval(intval($this->indice))+1:"null").",";
				$sql.= " fk_user_author=".$user->id.",";
				$sql.= " fk_user_mod=".$user->id;
					
				$sql.= " WHERE indice=".$this->indice;
				$sql.= " AND fk_training=".$this->fk_training;
					
				dol_syslog(get_class($this).":shift_indice:less rank no 0 sql=".$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}

				// Update request
				$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_training_admlevel SET";

				$sql.= " indice=".(isset($this->indice)?$this->indice:"null").",";
				$sql.= " fk_user_mod=".$user->id;

				$sql.= " WHERE rowid=".$this->id;
				$sql.= " AND fk_training=".$this->fk_training;

				dol_syslog(get_class($this).":shift_indice:update sql=".$sql, LOG_DEBUG);
				$resql = $this->db->query($sql);
				if (! $resql) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}
			}
			else
			{
				// Update request
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel SET';
					
				$sql.= ' indice=indice+10000,';
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= ' WHERE indice>='.$this->indice.' AND indice<'.intval(intval($this->indice)+100);
				$sql.= " AND fk_training=".$this->fk_training;
					
				dol_syslog(get_class($this).':shift_indice:less rank is 0 sql='.$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}

				// Update request
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel SET';
					
				$sql.= ' indice=indice+100,';
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= ' WHERE indice>='.intval(intval($this->indice)-100).' AND indice<'.$this->indice;
				$sql.= " AND fk_training=".$this->fk_training;
					
				dol_syslog(get_class($this).':shift_indice:less rank is 0 sql='.$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}

				// Update request
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel SET';
					
				$sql.= ' indice=indice-10100,';
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= ' WHERE indice>='.intval(intval($this->indice)+10000).' AND indice<'.intval(intval($this->indice)+10100);
					
				dol_syslog(get_class($this).':shift_indice:less rank is 0 sql='.$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}
			}
		}

		if ($type=='more')
		{
			if ($this->level_rank!=0)
			{
				$this->indice=intval(intval($this->indice)+1);
				// Update request
				$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_training_admlevel SET";
					
				$sql.= " indice=".(isset($this->indice)?intval(intval($this->indice)-1):"null").",";
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= " WHERE indice=".$this->indice;
					
				dol_syslog(get_class($this).":shift_indice:more rank no 0 sql=".$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}

				// Update request
				$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_training_admlevel SET";

				$sql.= " indice=".(isset($this->indice)?$this->indice:"null").",";
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= " WHERE rowid=".$this->id;

				dol_syslog(get_class($this).":shift_indice:update sql=".$sql, LOG_DEBUG);
				$resql = $this->db->query($sql);
				if (! $resql) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}
			}
			else
			{
				// Update request
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel SET';
					
				$sql.= ' indice=indice+10000,';
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= ' WHERE indice>='.$this->indice.' AND indice<'.intval(intval($this->indice)+100);
					
				dol_syslog(get_class($this).':shift_indice:more rank is 0 sql='.$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}

				// Update request
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel SET';
					
				$sql.= ' indice=indice-100,';
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= ' WHERE indice>='.intval(intval($this->indice)+100).' AND indice<'.intval(intval($this->indice)+200);
					
				dol_syslog(get_class($this).':shift_indice:more rank is 0 sql='.$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}

				// Update request
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel SET';
					
				$sql.= ' indice=indice-9900,';
				$sql.= ' fk_user_mod='.$user->id;
				$sql.= ' WHERE indice>='.intval(intval($this->indice)+10000).' AND indice<'.intval(intval($this->indice)+10100);
					
				dol_syslog(get_class($this).':shift_indice:more rank is 0 sql='.$sql, LOG_DEBUG);
				$resql1 = $this->db->query($sql);
				if (! $resql1) {
					$error++; $this->errors[]="Error ".$this->db->lasterror();
				}
			}
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
	 *  After a creation set the good parent id for action session
	 *
	 *  @param	$user	int	       		 User id that modify
	 *  @param $training_id	int			 Training to update
	 *  @param  $notrigger int			 0=launch triggers after, 1=disable triggers
	 *  @return int     		   	 	 <0 if KO, >0 if OK
	 */
	function setParentActionId($user, $training_id)
	{
		global $conf, $langs;
		$error = 0;

		// Update request
		if ($this->db->type=='pgsql') {
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel as upd';
			$sql.= ' SET fk_parent_level=ori.rowid ';
			$sql.= ' FROM  '.MAIN_DB_PREFIX.'agefodd_training_admlevel as ori';
			$sql.= ' WHERE upd.fk_parent_level=ori.fk_agefodd_training_admlevel AND upd.level_rank<>0 AND upd.fk_training=ori.fk_training';
			$sql.= ' AND upd.fk_training='.$training_id;
		}else {
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_training_admlevel as ori, '.MAIN_DB_PREFIX.'agefodd_training_admlevel as upd ';
			$sql.= ' SET upd.fk_parent_level=ori.rowid ';
			$sql.= ' WHERE upd.fk_parent_level=ori.fk_agefodd_training_admlevel AND upd.level_rank<>0 AND upd.fk_training=ori.fk_training';
			$sql.= ' AND upd.fk_training='.$training_id;
		}

		//print $sql;
		//exit;
		$this->db->begin();

		dol_syslog(get_class($this)."::setParentActionId sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::setParentActionId ".$errmsg, LOG_ERR);
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

}


/**
 *	line Class
 */
class AgfTrainingAdmlvlLine
{
	var $rowid;
	var $fk_training;
	var $level_rank ;
	var $fk_parent_level;
	var $indice;
	var $intitule;
	var $alerte;
	var $fk_agefodd_training_admlevel;

	function __construct()
	{
		return 1;
	}
}
