<?php
/** Copyright (C) 2007-2008	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
* Copyright (C) 2012       Florian Henry   	<florian.henry@open-concept.pro>
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 *  \file       agefodd/class/agefodd_stagiaire.class.php
 *  \ingroup    agefodd
*  \brief      Manage trainee object
*/

require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");



/**
 *	Trainee Class
*/
class Agefodd_stagiaire extends CommonObject
{
	var $db;
	var $error;
	var $errors=array();
	var $element='agefodd';
	var $table_element='agefodd_stagiaire';
	var $id;
	
	protected $ismultientitymanaged = 1;  // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	var $nom;
	var $prenom;
	var $fonction;
	var $tel1;
	var $tel2;
	var $mail;
	var $note;
	var $date_birth;
	var $place_birth;

	var $socid;
	var $socname;
	var $fk_socpeople;

	var $lines=array();

	/**
	 *  Constructor
	 *
	 *  @param	DoliDb		$db      Database handler
	*/
	function __construct($DB)
	{
		$this->db = $DB;
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
		if (isset($this->nom)) $this->nom = trim($this->nom);
		if (isset($this->prenom)) $this->prenom = trim($this->prenom);
		if (isset($this->fonction)) $this->fonction = $this->db->escape(trim($this->fonction));
		if (isset($this->tel1)) $this->tel1 = $this->db->escape(trim($this->tel1));
		if (isset($this->tel2)) $this->tel2 = $this->db->escape(trim($this->tel2));
		if (isset($this->mail)) $this->mail = $this->db->escape(trim($this->mail));
		if (isset($this->note)) $this->note = $this->db->escape(trim($this->note));
		if (isset($this->place_birth)) $this->place_birth = $this->db->escape(trim($this->place_birth));

		// Check parameters
		// Put here code to add control on parameters value
		$this->nom = mb_strtoupper($this->nom, 'UTF-8');
		$this->prenom = ucfirst(mb_strtolower($this->prenom, 'UTF-8'));

		if (empty($this->civilite)){
			$error++;
			$this->errors[]=$langs->trans("AgfCiviliteMandatory");
		}


		// Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."agefodd_stagiaire(";
		$sql.= "nom, prenom, civilite, fk_user_author,fk_user_mod, datec, ";
		$sql.= "fk_soc, fonction, tel1, tel2, mail, note,fk_socpeople";
		$sql.= ",entity";
		$sql.= ",date_birth";
		$sql.= ",place_birth";
		$sql.= ") VALUES (";

		$sql.= " ".(isset($this->nom)?"'".$this->nom."'":"null").", ";
		$sql.= " ".(isset($this->prenom)?"'".$this->prenom."'":"null").", ";
		$sql.= " ".(isset($this->civilite)?"'".$this->civilite."'":"null").", ";
		$sql.= ' '.$user->id.", ";
		$sql.= ' '.$user->id.", ";
		$sql.= "'".$this->db->idate(dol_now())."', ";
		$sql.= " ".(isset($this->socid)?$this->db->escape($this->socid):"null").", ";
		$sql.= " ".(isset($this->fonction)?"'".$this->fonction."'":"null").", ";
		$sql.= " ".(isset($this->tel1)?"'".$this->tel1."'":"null").", ";
		$sql.= " ".(isset($this->tel2)?"'".$this->tel2."'":"null").", ";
		$sql.= " ".(isset($this->mail)?"'".$this->mail."'":"null").", ";
		$sql.= " ".(isset($this->note)?"'".$this->note."'":"null").", ";
		$sql.= " ".(isset($this->fk_socpeople)?$this->db->escape($this->fk_socpeople):"null").", ";
		$sql.= " ".$conf->entity.",";
		$sql.= " ".(! isset($this->date_birth) || dol_strlen($this->date_birth)==0?'NULL':"'".$this->db->idate($this->date_birth)."'").", ";
		$sql.= " ".(isset($this->place_birth)?"'".$this->place_birth."'":"null");
		$sql.= ")";

		if (! $error)
		{
			$this->db->begin();

			dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
			$resql=$this->db->query($sql);
			if (! $resql) {
				$error++; $this->errors[]="Error ".$this->db->lasterror();
			}

			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."agefodd_stagiaire");
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				//// Call triggers
				//include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
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
		$sql.= " so.rowid as socid, so.nom as socname,";
		$sql.= " civ.code as civilite_code,";
		$sql.= " s.rowid, s.nom, s.prenom, s.civilite, s.fk_soc, s.fonction,";
		$sql.= " s.tel1, s.tel2, s.mail, s.note, s.fk_socpeople, s.date_birth, s.place_birth";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_stagiaire as s";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as so";
		$sql.= " ON s.fk_soc = so.rowid";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_civilite as civ";
		$sql.= " ON s.civilite = civ.code";
		$sql.= " WHERE s.rowid = ".$id;
		$sql.= " AND s.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				if (!(empty($obj->fk_socpeople)))
				{
					$contact = new Contact($this->db);
					$result = $contact->fetch($obj->fk_socpeople);

					if ($result > 0)
					{

						$this->id = $obj->rowid;
						$this->ref = $obj->rowid; // use for next prev refs

						$this->nom = $contact->lastname;
						$this->prenom = $contact->firstname;
						$this->civilite = $contact->civilite_id;
						$this->socid = $contact->socid;
						$this->socname = $contact->socname;
						$this->fonction = $contact->poste;
						$this->tel1 = $contact->phone_pro;
						$this->tel2 = $contact->phone_mobile;
						$this->mail = $contact->email;
						$this->note = $obj->note;
						$this->fk_socpeople = $obj->fk_socpeople;
						$this->date_birth = $contact->birthday;
						$this->place_birth = $obj->place_birth;

					}
				}
				else
				{
					$this->id = $obj->rowid;
					$this->ref = $obj->rowid; // use for next prev refs
					$this->nom = $obj->nom;
					$this->prenom = $obj->prenom;
					$this->civilite = $obj->civilite;
					$this->socid = $obj->socid;
					$this->socname = $obj->socname;
					$this->fonction = $obj->fonction;
					$this->tel1 = $obj->tel1;
					$this->tel2 = $obj->tel2;
					$this->mail = $obj->mail;
					$this->note = $obj->note;
					$this->place_birth = $obj->place_birth;
					$this->fk_socpeople = 0;
					$this->date_birth = $this->db->jdate($obj->date_birth);
				}

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
	 *  Load all objects in memory from database
	 *
	 *  @param	string		$sortorder    sort order
	 *  @param	string		$sortfield    sort field
	 *  @param	int			$limit		  limit page
	 *  @param	int			$offset    	  page
	 *  @param	int			$arch    	  display archive or not
	 *  @param	array		$filter    	  filter output
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function fetch_all($sortorder, $sortfield, $limit='', $offset, $filter='')
	{
		global $langs;

		$sql = "SELECT";
		$sql.= " so.rowid as socid, so.nom as socname,";
		$sql.= " civ.code as civilitecode,";
		$sql.= " s.rowid, s.nom, s.prenom, s.civilite, s.fk_soc, s.fonction,";
		$sql.= " s.tel1, s.tel2, s.mail, s.note, s.fk_socpeople, s.date_birth, s.place_birth";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_stagiaire as s";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as so";
		$sql.= " ON s.fk_soc = so.rowid";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_civilite as civ";
		$sql.= " ON s.civilite = civ.code";

		//Manage filter
		if (!empty($filter)){
			$addcriteria=false;
			foreach($filter as $key => $value) {
				if ($key=='civ.code') {
					if ($addcriteria) {
						$sql.= ' AND ';
					}
					$sqlwhere.= $key.' = \''.$value.'\'';
					$addcriteria=true;
				}
				elseif ($key!='s.tel1') {
					if ($addcriteria) {
						$sql.= ' AND ';
					}
					$sqlwhere.= $key.' LIKE \'%'.$value.'%\'';
					$addcriteria=true;
				}
			}
			if (!empty($sqlwhere))	{
				$sql .= ' WHERE '. $sqlwhere;
			}
		}
		else {
			$sql.= " WHERE s.entity IN (".getEntity('agsession').")";
		}

		$sql.= " ORDER BY ".$sortfield." ".$sortorder." ";
		if (!empty($limit)) {
			$sql.=$this->db->plimit( $limit + 1 ,$offset);
		}

		dol_syslog(get_class($this)."::fetch_all sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$this->line = array();
			$num = $this->db->num_rows($resql);
			$i = 0;

			if ($num)
			{
				while( $i < $num)
				{
					$obj = $this->db->fetch_object($resql);
						
					$line = new AgfTraineeLine();

					//Manage filter for telephone to remove all space from result to filter correctly
					if (!empty($filter)){
						if (array_key_exists('s.tel1',$filter)) {
							$value = $filter['s.tel1'];
							if (!empty($value)) {
								if ($pos!==false)
								{
									$line->socid = $obj->socid;
									$line->socname = $obj->socname;
									$line->civilitecode = $obj->civilitecode;
									$line->rowid = $obj->rowid;
									$line->nom = $obj->nom;
									$line->prenom = $obj->prenom;
									$line->civilite = $obj->civilite;
									$line->fk_soc = $obj->fk_soc;
									$line->fonction = $obj->fonction;
									$line->tel1 = $obj->tel1;
									$line->tel2 = $obj->tel2;
									$line->mail = $obj->mail;
									$line->note = $obj->note;
									$line->place_birth = $obj->place_birth;
									$line->fk_socpeople = $obj->fk_socpeople;
									$line->date_birth = $this->db->jdate($obj->date_birth);
								}
							}
						}
						else
						{
							$line->socid = $obj->socid;
							$line->socname = $obj->socname;
							$line->civilitecode = $obj->civilitecode;
							$line->rowid = $obj->rowid;
							$line->nom = $obj->nom;
							$line->prenom = $obj->prenom;
							$line->civilite = $obj->civilite;
							$line->fk_soc = $obj->fk_soc;
							$line->fonction = $obj->fonction;
							$line->tel1 = $obj->tel1;
							$line->tel2 = $obj->tel2;
							$line->mail = $obj->mail;
							$line->note = $obj->note;
							$line->fk_socpeople = $obj->fk_socpeople;
							$line->date_birth = $this->db->jdate($obj->date_birth);
							$line->place_birth = $obj->place_birth;
						}
					}
					else
					{
						$line->socid = $obj->socid;
						$line->socname = $obj->socname;
						$line->civilitecode = $obj->civilitecode;
						$line->rowid = $obj->rowid;
						$line->nom = $obj->nom;
						$line->prenom = $obj->prenom;
						$line->civilite = $obj->civilite;
						$line->fk_soc = $obj->fk_soc;
						$line->fonction = $obj->fonction;
						$line->tel1 = $obj->tel1;
						$line->tel2 = $obj->tel2;
						$line->mail = $obj->mail;
						$line->note = $obj->note;
						$line->fk_socpeople = $obj->fk_socpeople;
						$line->date_birth = $this->db->jdate($obj->date_birth);
						$line->place_birth = $obj->place_birth;
					}
						
					$this->lines[$i]=$line;
						
					$i++;
				}
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
	 *  Give information on the object
	 *
	 *  @param	int		$id    Id object
	 *  @return int          	<0 if KO, >0 if OK
	 */
	function info($id)
	{
		global $langs;

		$sql = "SELECT";
		$sql.= " s.rowid, s.datec, s.tms, s.fk_user_author, s.fk_user_mod";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_stagiaire as s";
		$sql.= " WHERE s.rowid = ".$id;

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->tms);
				$this->user_modification = $obj->fk_user_mod;
				$this->user_creation = $obj->fk_user_author;
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
	function update($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		// Clean parameters
		if (isset($this->nom)) $this->nom = trim($this->nom);
		if (isset($this->prenom)) $this->prenom = trim($this->prenom);
		if (isset($this->fonction)) $this->fonction = $this->db->escape(trim($this->fonction));
		if (isset($this->tel1)) $this->tel1 = $this->db->escape(trim($this->tel1));
		if (isset($this->tel2)) $this->tel2 = $this->db->escape(trim($this->tel2));
		if (isset($this->mail)) $this->mail = $this->db->escape(trim($this->mail));
		if (isset($this->note)) $this->note = $this->db->escape(trim($this->note));
		if (isset($this->place_birth)) $this->place_birth = $this->db->escape(trim($this->place_birth));

		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		if (!isset($this->archive)) $this->archive = 0;
		$sql = "UPDATE ".MAIN_DB_PREFIX."agefodd_stagiaire SET";
		$sql.= " nom=".(isset($this->nom)?"'".$this->nom."'":"null").",";
		$sql.= " prenom=".(isset($this->prenom)?"'".$this->prenom."'":"null").",";
		$sql.= " civilite=".(isset($this->civilite)?"'".$this->civilite."'":"null").",";
		$sql.= " fk_user_mod=".$user->id.",";
		$sql.= " fk_soc=".(isset($this->socid)?$this->socid:"null").",";
		$sql.= " fonction=".(isset($this->fonction)?"'".$this->fonction."'":"null").",";
		$sql.= " tel1=".(isset($this->tel1)?"'".$this->tel1."'":"null").",";
		$sql.= " tel2=".(isset($this->tel2)?"'".$this->tel2."'":"null").",";
		$sql.= " mail=".(isset($this->mail)?"'".$this->mail."'":"null").",";
		$sql.= " note=".(isset($this->note)?"'".$this->note."'":"null").",";
		$sql.= " fk_socpeople=".(isset($this->fk_socpeople)?$this->fk_socpeople:"null").", ";
		$sql.= " date_birth=".(! isset($this->date_birth) || dol_strlen($this->date_birth)==0?"null":"'".$this->db->idate($this->date_birth)."'");
		$sql.= " ,place_birth=".(isset($this->place_birth)?"'".$this->place_birth."'":"null");
		$sql.= " WHERE rowid = ".$this->id;

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
				//include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
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
	 *  @return	 int					 <0 if KO, >0 if OK
	 */
	function remove($id)
	{
		$sql  = "DELETE FROM ".MAIN_DB_PREFIX."agefodd_stagiaire";
		$sql .= " WHERE rowid = ".$id;

		$this->db->begin();

		dol_syslog(get_class($this)."::remove sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query ($sql);

		if ($resql)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error=$this->db->lasterror();
			dol_syslog(get_class($this)."::remove ".$this->error, LOG_ERR);
			$this->db->rollback();
			return -1;
		}
	}
}

class AgfTraineeLine {

	var $socid;
	var $socname;
	var $civilitecode;
	var $rowid;
	var $nom;
	var $prenom;
	var $civilite;
	var $fk_soc;
	var $fonction;
	var $tel1;
	var $tel2;
	var $mail;
	var $note;
	var $fk_socpeople;
	var $date_birth;
	var $place_birth;

	function __construct()
	{
		return 1;
	}
}