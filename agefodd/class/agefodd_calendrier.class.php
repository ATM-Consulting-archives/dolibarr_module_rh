<?php
/** Copyright (C) 2012       Florian Henry  	<florian.henry@open-concept.pro>
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
 *      \file       agefodd/class/agefodd_calendrier.class.php
 *      \brief      MAnage base calendar
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");


/**
 *	Put here description of your class
*/
class Agefoddcalendrier extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='agefodd_calendrier';			//!< Id that identify managed objects
	var $table_element='agefodd_calendrier';	//!< Name of table without prefix where object is stored
	protected $ismultientitymanaged = 1;  // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	var $id;

	var $entity;
	var $day_session;
	var $heured='';
	var $heuref='';

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
	 *  @param	User	$user        User that create
	 *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
	 *  @return int      		   	 <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters
		if (isset($this->day_session)) $this->day_session=trim($this->day_session);
		if (isset($this->heured)) $this->heured=trim($this->heured);
		if (isset($this->heuref)) $this->heuref=trim($this->heuref);


		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."agefodd_calendrier(";
		$sql.= "entity,";
		$sql.= "day_session,";
		$sql.= "heured,";
		$sql.= "heuref,";
		$sql.= "fk_user_author,";
		$sql.= "datec,";
		$sql.= "fk_user_mod";
		$sql.= ") VALUES (";

		$sql.= " '".$conf->entity."',";
		$sql.= " ".(! isset($this->day_session)?'NULL':"'".$this->day_session."'").",";
		$sql.= " ".(! isset($this->heured)?'NULL':"'".$this->heured."'").",";
		$sql.= " ".(! isset($this->heuref)?'NULL':"'".$this->heuref."'").",";
		$sql.= " '".$user->id."',";
		$sql.= "'".$this->db->idate(dol_now())."',";
		$sql.= " '".$user->id."'";


		$sql.= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if (! $resql) {
			$error++; $this->errors[]="Error ".$this->db->lasterror();
		}

		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."agefodd_calendrier");

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

		$sql.= " t.entity,";
		$sql.= " t.day_session,";
		$sql.= " t.heured,";
		$sql.= " t.heuref";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_calendrier as t";
		$sql.= " WHERE t.rowid = ".$id;
		$sql.= " AND t.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->id    = $obj->rowid;

				$this->day_session = $obj->day_session;
				$this->heured = $obj->heured;
				$this->heuref = $obj->heuref;


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
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function fetch_all()
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " t.rowid,";
		$sql.= " t.day_session,";
		$sql.= " t.heured,";
		$sql.= " t.heuref";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_calendrier as t";
		$sql.= " WHERE t.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->lines = array();
			$num=$this->db->num_rows($resql);
			$i = 0;
			while ( $i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->lines[$i] = new AgefoddcalendrierLines();
				$this->lines[$i]->id    = $obj->rowid;

				$this->lines[$i]->day_session = $obj->day_session;
				$this->lines[$i]->heured = $obj->heured;
				$this->lines[$i]->heuref = $obj->heuref;

				$i++;
			}
			$this->db->free($resql);

			return $num;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch_all ".$this->error, LOG_ERR);
			return -1;
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
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."agefodd_calendrier";
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
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;

		$this->entity='';
		$this->day_session='';
		$this->heured='';
		$this->heuref='';
		$this->fk_user_author='';
		$this->datec='';
		$this->fk_user_mod='';
		$this->tms='';
	}

}

class AgefoddcalendrierLines {
	var $id;
	var $day_session;
	var $heured;
	var $heuref;
}
