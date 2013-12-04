<?php
/** Copyright (C) 2007-2008	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
* Copyright (C) 2012-2013       Florian Henry       <florian.henry@open-concept.pro>
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
 * \file agefodd/class/agefodd_foramtion_catalogue.class.php
 * \ingroup agefodd
 * \brief Manage training object
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");

/**
 * trainning Class
 */
class Agefodd extends CommonObject {
	var $db;
	var $error;
	var $errors = array ();
	var $element = 'agefodd_formation_catalogue';
	var $table_element = 'agefodd_formation_catalogue';
	
	protected $ismultientitymanaged = 1;  // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	
	var $id;
	var $entity;
	var $ref;
	var $ref_obj;
	var $ref_interne;
	var $intitule;
	var $duree;
	var $public;
	var $methode;
	var $prerequis;
	var $but;
	var $programme;
	var $note1;
	var $note2;
	var $archive;
	var $note_private;
	var $note_public;
	var $fk_product;
	var $nb_subscribe_min;
	var $fk_formation_catalogue;
	var $priorite;
	var $fk_c_category;
	var $category_lib;
	var $certif_duration;
	var $lines = array ();
	

	/**
	 * Constructor
	 *
	 * @param DoliDb $db
	 *        	handler
	 */
	function __construct($db) {
		$this->db = $db;
		return 1;
	}
	
	/**
	 * Create object into database
	 *
	 * @param User $user
	 *        	that create
	 * @param int $notrigger
	 *        	triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		if (isset ( $this->intitule ))
			$this->intitule = $this->db->escape ( trim ( $this->intitule ) );
		if (isset ( $this->public ))
			$this->public = $this->db->escape ( trim ( $this->public ) );
		if (isset ( $this->methode ))
			$this->methode = $this->db->escape ( trim ( $this->methode ) );
		if (isset ( $this->prerequis ))
			$this->prerequis = $this->db->escape ( trim ( $this->prerequis ) );
		if (isset ( $this->but ))
			$this->but = $this->db->escape ( trim ( $this->but ) );
		if (isset ( $this->note1 ))
			$this->note1 = $this->db->escape ( trim ( $this->note1 ) );
		if (isset ( $this->note2 ))
			$this->note2 = $this->db->escape ( trim ( $this->note2 ) );
		if (isset ( $this->note2 ))
			$this->programme = $this->db->escape ( trim ( $this->programme ) );
		if (isset ( $this->certif_duration ))
			$this->certif_duration = $this->db->escape ( trim ( $this->certif_duration ) );
		
		if (empty ( $this->duree ))
			$this->duree = 0;
		
		if ($this->fk_c_category==-1)
			$this->fk_c_category = 0;
			
			// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_formation_catalogue(";
		$sql .= "datec, ref,ref_interne,intitule, duree, public, methode, prerequis, but,";
		$sql .= "programme, note1, note2, fk_user_author,fk_user_mod,entity,";
		$sql .= "fk_product,nb_subscribe_min,fk_c_category,certif_duration";
		$sql .= ") VALUES (";
		$sql .= $this->db->idate ( dol_now () ) . ', ';
		$sql .= " " . (! isset ( $this->ref_obj ) ? 'NULL' : "'" . $this->ref_obj . "'") . ",";
		$sql .= " " . (! isset ( $this->ref_interne ) ? 'NULL' : "'" . $this->ref_interne . "'") . ",";
		$sql .= " " . (! isset ( $this->intitule ) ? 'NULL' : "'" . $this->intitule . "'") . ",";
		$sql .= " " . (! isset ( $this->duree ) ? 'NULL' : $this->duree) . ",";
		$sql .= " " . (! isset ( $this->public ) ? 'NULL' : "'" . $this->public . "'") . ",";
		$sql .= " " . (! isset ( $this->methode ) ? 'NULL' : "'" . $this->methode . "'") . ",";
		$sql .= " " . (! isset ( $this->prerequis ) ? 'NULL' : "'" . $this->prerequis . "'") . ",";
		$sql .= " " . (! isset ( $this->but ) ? 'NULL' : "'" . $this->but . "'") . ",";
		$sql .= " " . (! isset ( $this->programme ) ? 'NULL' : "'" . $this->programme . "'") . ",";
		$sql .= " " . (! isset ( $this->note1 ) ? 'NULL' : "'" . $this->note1 . "'") . ",";
		$sql .= " " . (! isset ( $this->note2 ) ? 'NULL' : "'" . $this->note2 . "'") . ",";
		$sql .= " " . $user->id . ',';
		$sql .= " " . $user->id . ',';
		$sql .= " " . $conf->entity . ', ';
		$sql .= " " . (empty ( $this->fk_product ) ? 'null' : $this->fk_product) . ', ';
		$sql .= " " . (empty ( $this->nb_subscribe_min ) ? "null" : $this->nb_subscribe_min). ', ';
		$sql .= " " . (empty ( $this->fk_c_category ) ? "null" : $this->fk_c_category). ', ';
		$sql .= " " . (empty ( $this->certif_duration ) ? "null" : "'".$this->certif_duration."'");
		$sql .= ")";
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "agefodd_formation_catalogue" );
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
		{
			$result=$this->insertExtraFields();
			if ($result < 0)
			{
				$error++;
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::create " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return $this->id;
		}
	}
	
	/**
	 * Load object in memory from database
	 *
	 * @param int $id
	 *        	object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id, $ref = '') {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " c.rowid, c.ref, c.ref_interne, c.intitule, c.duree,";
		$sql .= " c.public, c.methode, c.prerequis, but, c.programme, c.archive, c.note1, c.note2 ";
		$sql .= " ,c.note_private, c.note_public, c.fk_product,c.nb_subscribe_min,c.fk_c_category,dictcat.code as catcode ,dictcat.intitule as catlib ";
		$sql .= " ,c.certif_duration";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue_type as dictcat ON dictcat.rowid=c.fk_c_category";
		if ($id && ! $ref)
			$sql .= " WHERE c.rowid = " . $id;
		if (! $id && $ref)
			$sql .= " WHERE c.ref = '" . $ref . "'";
		$sql .= " AND c.entity IN (" . getEntity ( 'agsession' ) . ")";
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				$this->id = $obj->rowid;
				$this->ref = $obj->rowid; // use for next prev ref
				$this->ref_obj = $obj->ref; // use for next prev ref
				$this->ref_interne = $obj->ref_interne;
				$this->intitule = stripslashes ( $obj->intitule );
				$this->duree = $obj->duree;
				$this->public = stripslashes ( $obj->public );
				$this->methode = stripslashes ( $obj->methode );
				$this->prerequis = stripslashes ( $obj->prerequis );
				$this->but = stripslashes ( $obj->but );
				$this->programme = stripslashes ( $obj->programme );
				$this->note1 = stripslashes ( $obj->note1 );
				$this->note2 = stripslashes ( $obj->note2 );
				$this->archive = $obj->archive;
				$this->note_private = $obj->note_private;
				$this->note_public = $obj->note_public;
				$this->fk_product = $obj->fk_product;
				$this->nb_subscribe_min = $obj->nb_subscribe_min;
				$this->fk_c_category = $obj->fk_c_category;
				if (!empty($obj->catcode) || !empty($obj->catlib)) {
					$this->category_lib = $obj->catcode.' - '.$obj->catlib;
				}
				$this->certif_duration = $obj->certif_duration;
			}
			$this->db->free ( $resql );
			
			require_once(DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php');
			$extrafields=new ExtraFields($this->db);
			$extralabels=$extrafields->fetch_name_optionals_label($this->table_element,true);
			if (count($extralabels)>0) {
				$this->fetch_optionals($this->id,$extralabels);
			}
			
			
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}
	
	/**
	 * Give information on the object
	 *
	 * @param int $id
	 *        	object
	 * @return int <0 if KO, >0 if OK
	 */
	function info($id) {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " c.rowid, c.datec, c.tms, c.fk_user_author, c.fk_user_mod ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " WHERE c.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				$this->id = $obj->rowid;
				$this->date_creation = $this->db->jdate ( $obj->datec );
				$this->date_modification = $this->db->jdate ( $obj->tms );
				$this->user_creation = $obj->fk_user_author;
				$this->user_modification = $obj->fk_user_mod;
			}
			$this->db->free ( $resql );
			
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}
	
	/**
	 * Update object into database
	 *
	 * @param User $user
	 *        	that modify
	 * @param int $notrigger
	 *        	triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		$this->intitule = $this->db->escape ( trim ( $this->intitule ) );
		$this->ref_obj = $this->db->escape ( trim ( $this->ref_obj ) );
		$this->ref_interne = $this->db->escape ( trim ( $this->ref_interne ) );
		$this->public = $this->db->escape ( trim ( $this->public ) );
		$this->methode = $this->db->escape ( trim ( $this->methode ) );
		$this->prerequis = $this->db->escape ( trim ( $this->prerequis ) );
		$this->but = $this->db->escape ( trim ( $this->but ) );
		$this->programme = $this->db->escape ( trim ( $this->programme ) );
		$this->note1 = $this->db->escape ( trim ( $this->note1 ) );
		$this->note2 = $this->db->escape ( trim ( $this->note2 ) );
		$this->certif_duration = $this->db->escape ( trim ( $this->certif_duration ) );
		
		if ($this->fk_c_category==-1)
			$this->fk_c_category = 0;
		
		$this->fk_c_category = $this->db->escape ( trim ( $this->fk_c_category ) );
		
		// Check parameters
		// Put here code to add control on parameters values
		if (empty ( $this->duree ))
			$this->duree = 0;
			
			// Update request
		if (! isset ( $this->archive ))
			$this->archive = 0;
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_formation_catalogue SET";
		$sql .= " ref=" . (isset ( $this->ref_obj ) ? "'" . $this->ref_obj . "'" : "null") . ",";
		$sql .= " ref_interne=" . (isset ( $this->ref_interne ) ? "'" . $this->ref_interne . "'" : "null") . ",";
		$sql .= " intitule=" . (isset ( $this->intitule ) ? "'" . $this->intitule . "'" : "null") . ",";
		$sql .= " duree=" . (isset ( $this->duree ) ? $this->duree : "null") . ",";
		$sql .= " public=" . (isset ( $this->public ) ? "'" . $this->public . "'" : "null") . ",";
		$sql .= " methode=" . (isset ( $this->methode ) ? "'" . $this->methode . "'" : "null") . ",";
		$sql .= " prerequis=" . (isset ( $this->prerequis ) ? "'" . $this->prerequis . "'" : "null") . ",";
		$sql .= " but=" . (isset ( $this->but ) ? "'" . $this->but . "'" : "null") . ",";
		$sql .= " programme=" . (isset ( $this->programme ) ? "'" . $this->programme . "'" : "null") . ",";
		$sql .= " note1=" . (isset ( $this->note1 ) ? "'" . $this->note1 . "'" : "null") . ",";
		$sql .= " note2=" . (isset ( $this->note2 ) ? "'" . $this->note2 . "'" : "null") . ",";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " archive=" . $this->archive . ",";
		$sql .= " fk_product=" . (!empty ( $this->fk_product ) ? $this->fk_product : "null") . ",";
		$sql .= " nb_subscribe_min=" . (!empty ( $this->nb_subscribe_min ) ? $this->nb_subscribe_min : "null"). "," ;
		$sql .= " fk_c_category=" . (!empty ( $this->fk_c_category ) ? $this->fk_c_category : "null"). ",";
		$sql .= " certif_duration=" . (!empty ( $this->certif_duration ) ? "'" .$this->certif_duration. "'" : "null");
		$sql .= " WHERE rowid = " . $this->id;
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::update sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		
		if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
		{
			$result=$this->insertExtraFields();
			if ($result < 0)
			{
				$error++;
			}
		}
		
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::update " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return 1;
		}
	}
	
	/**
	 * Delete object in database
	 *
	 * @param int $id
	 *        	to delete
	 * @return int if KO, >0 if OK
	 */
	function remove($id) {
		
		global $conf;
		
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_formation_catalogue";
		$sql .= " WHERE rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::remove sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		// Removed extrafields
		if (! $error)
		{
			if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
			{
				$this->id=$id;
				$result=$this->deleteExtraFields();
				if ($result < 0)
				{
					$error++;
					dol_syslog(get_class($this)."::delete erreur ".$error." ".$this->error, LOG_ERR);
				}
			}
		}
		
		if (! $error) {
			return 1;
		} else {
			$this->error = $this->db->lasterror ();
			return - 1;
		}
	}
	
	/**
	 * Create pegagogic goal
	 *
	 * @param User $user
	 *        	that delete
	 * @param int $notrigger
	 *        	triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function create_objpeda($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		$this->intitule = $this->db->escape ( $this->intitule );
		
		// Check parameters
		// Put here code to add control on parameters value
		
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_formation_objectifs_peda(";
		$sql .= "fk_formation_catalogue, intitule, priorite, fk_user_author,fk_user_mod,datec";
		$sql .= ") VALUES (";
		$sql .= " " . $this->fk_formation_catalogue . ', ';
		$sql .= "'" . $this->intitule . "', ";
		$sql .= " " . $this->priorite . ", ";
		$sql .= " " . $user->id . ',';
		$sql .= " " . $user->id . ',';
		$sql .= "'" . $this->db->idate ( dol_now () ) . "'";
		$sql .= ")";
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "agefodd_formation_objectifs_peda" );
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::create " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return $this->id;
		}
	}
	
	/**
	 * Load object in memory from database
	 *
	 * @param int $id
	 *        	of object
	 * @return int if KO, >0 if OK
	 */
	function fetch_objpeda($id) {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " o.intitule, o.priorite";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_formation_objectifs_peda";
		$sql .= " as o";
		$sql .= " WHERE o.rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				$this->id = $obj->rowid;
				$this->intitule = stripslashes ( $obj->intitule );
				$this->priorite = $obj->priorite;
			}
			$this->db->free ( $resql );
			
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}
	
	/**
	 * Load object in memory from database
	 *
	 * @param int $id_formation
	 *        	concern by objectif peda
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_objpeda_per_formation($id_formation) {
		global $langs;
		
		$sql = "SELECT";
		$sql .= " o.rowid, o.intitule, o.priorite, o.fk_formation_catalogue, o.tms, o.fk_user_author";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_formation_objectifs_peda AS o";
		$sql .= " WHERE o.fk_formation_catalogue = " . $id_formation;
		$sql .= " ORDER BY o.priorite ASC";
		
		dol_syslog ( get_class ( $this ) . "::fetch_objpeda_per_formation sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->line = array ();
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			
			while ( $i < $num ) {
				$obj = $this->db->fetch_object ( $resql );
				
				$line = new AgfObjPedaLine ();
				
				$line->id = $obj->rowid;
				$line->intitule = stripslashes ( $obj->intitule );
				$line->priorite = $obj->priorite;
				
				$this->lines [$i] = $line;
				
				$i ++;
			}
			$this->db->free ( $resql );
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_objpeda_per_formation " . $this->error, LOG_ERR );
			return - 1;
		}
	}
	
	/**
	 * Update object into database
	 *
	 * @param User $user
	 *        	that modify
	 * @param int $notrigger
	 *        	triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update_objpeda($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		$this->intitule = $this->db->escape ( trim ( $this->intitule ) );
		
		// Check parameters
		// Put here code to add control on parameters values
		
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_formation_objectifs_peda SET";
		$sql .= " fk_formation_catalogue=" . $this->fk_formation_catalogue . ",";
		$sql .= " intitule='" . $this->intitule . "',";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " priorite=" . $this->priorite . " ";
		$sql .= " WHERE rowid = " . $this->id;
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::update sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::update " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return 1;
		}
	}
	
	/**
	 * Delete object in database
	 *
	 * @param int $id
	 *        	to delete
	 * @return int if KO, >0 if OK
	 */
	function remove_objpeda($id) {
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_formation_objectifs_peda";
		$sql .= " WHERE rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::remove sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror ();
			return - 1;
		}
	}
	
	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	function initAsSpecimen() {
		$this->id = 0;
		$this->ref = '';
		$this->intitule = '';
		$this->duree = '';
		$this->public = '';
		$this->methode = '';
		$this->prerequis = '';
		$this->programme = '';
		$this->archive = '';
	}
	
	/**
	 * Return description of training
	 *
	 * @return string translated description
	 */
	function getToolTip() {
		global $conf;
		
		$langs->load ( "admin" );
		$langs->load ( "agefodd@agefodd" );
		
		$s = '';
		if (type == 'trainning') {
			$s .= '<b>' . $langs->trans ( "AgfTraining" ) . '</b>:<u>' . $this->intitule . ':</u><br>';
			$s .= '<br>';
			$s .= $langs->trans ( "AgfDuree" ) . ' : ' . $this->duree . ' H <br>';
			$s .= $langs->trans ( "AgfPublic" ) . ' : ' . $this->public . '<br>';
			$s .= $langs->trans ( "AgfMethode" ) . ' : ' . $this->methode . '<br>';
			
			$s .= '<br>';
		}
		return $s;
	}
	
	/**
	 * Load object in memory from database
	 *
	 * @param string $sortorder
	 *        	Sort Order
	 * @param string $sortfield
	 *        	Sort field
	 * @param int $limit
	 *        	offset limit
	 * @param int $offset
	 *        	offset limit
	 * @param int $arch
	 *        	archive
	 * @param array $filter
	 *        	array of filter where clause
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all($sortorder, $sortfield, $limit, $offset, $arch = 0, $filter=array()) {
		global $langs;
		
		$sql = "SELECT c.rowid, c.intitule, c.ref_interne, c.ref, c.datec, c.duree, c.fk_product, c.nb_subscribe_min, dictcat.code as catcode ,dictcat.intitule as catlib, ";
		$sql .= " (SELECT MAX(sess1.datef) FROM " . MAIN_DB_PREFIX . "agefodd_session as sess1 WHERE sess1.fk_formation_catalogue=c.rowid AND sess1.archive=1) as lastsession,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session as sess WHERE sess.fk_formation_catalogue=c.rowid AND sess.archive=1) as nbsession";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session as a";
		$sql .= " ON c.rowid = a.fk_formation_catalogue";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue_type as dictcat";
		$sql .= " ON dictcat.rowid = c.fk_c_category";
		$sql .= " WHERE c.archive = " . $arch;
		$sql .= " AND c.entity IN (" . getEntity ( 'agsession' ) . ")";
		// Manage filter
		if (! empty ( $filter )) {
			foreach ( $filter as $key => $value ) {
				if ($key == 'c.datec') 				// To allow $filter['YEAR(s.dated)']=>$year
				{
					$sql .= ' AND DATE_FORMAT(' . $key . ',\'%Y-%m-%d\') = \'' . dol_print_date($value,'%Y-%m-%d') . '\'';
				} elseif ($key == 'c.duree') {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $value . '%\'';
				}
			}
		}
		
		$sql .= " GROUP BY c.ref,c.ref_interne,c.rowid, dictcat.code, dictcat.intitule";
		$sql .= " ORDER BY $sortfield $sortorder " . $this->db->plimit ( $limit + 1, $offset );
		
		dol_syslog ( get_class ( $this ) . "::fetch_all sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->line = array ();
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					
					$line = new AgfTrainingLine ();
					
					$line->rowid = $obj->rowid;
					$line->intitule = $obj->intitule;
					$line->ref = $obj->ref;
					$line->ref_interne = $obj->ref_interne;
					$line->datec = $this->db->jdate ( $obj->datec );
					$line->duree = $obj->duree;
					$line->lastsession = $obj->lastsession;
					$line->nbsession = $obj->nbsession;
					$line->fk_product = $obj->fk_product;
					$line->nb_subscribe_min = $obj->nb_subscribe_min;
					$line->category_lib=$obj->catcode.' - '.$obj->catlib;;
					
					$this->lines [$i] = $line;
					
					$i ++;
				}
			}
			$this->db->free ( $resql );
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_all " . $this->error, LOG_ERR );
			return - 1;
		}
	}
	
	/**
	 * Return information of Place
	 *
	 * @return void
	 */
	function printFormationInfo() {
		global $form, $langs;
		
		print '<table class="border" width="100%">';
		
		print "<tr>";
		print '<td width="20%">' . $langs->trans ( "Ref" ) . '</td><td colspan=2>';
		print $this->ref;
		print '</td></tr>';
		
		print '<tr><td width="20%">' . $langs->trans ( "AgfIntitule" ) . '</td>';
		print '<td colspan=2>' . stripslashes ( $this->intitule ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfRefInterne" ) . '</td><td colspan=2>';
		print $this->ref_interne . '</td></tr>';
		
		print '</table>';
	}
	
	/**
	 * Create admin level for a session
	 */
	function createAdmLevelForTraining($user) {
		$error = '';
		
		require_once ('agefodd_sessadm.class.php');
		require_once ('agefodd_session_admlevel.class.php');
		require_once ('agefodd_training_admlevel.class.php');
		require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");
		$admlevel = new Agefodd_session_admlevel ( $this->db );
		$result2 = $admlevel->fetch_all ();
		
		if ($result2 > 0) {
			foreach ( $admlevel->lines as $line ) {
				$actions = new Agefodd_training_admlevel ( $this->db );
				
				$actions->fk_agefodd_training_admlevel = $line->rowid;
				$actions->fk_training = $this->id;
				$actions->delais_alerte = $line->alerte;
				$actions->intitule = $line->intitule;
				$actions->indice = $line->indice;
				$actions->archive = 0;
				$actions->level_rank = $line->level_rank;
				$actions->fk_parent_level = $line->fk_parent_level; // Treatement to calculate the new parent level is after
				$result3 = $actions->create ( $user );
				
				if ($result3 < 0) {
					dol_syslog ( get_class ( $this ) . "::createAdmLevelForTraining error=" . $actions->error, LOG_ERR );
					$this->error = $actions->error;
					$error ++;
				}
			}
			
			// Caculate the new parent level
			$action_static = new Agefodd_training_admlevel ( $this->db );
			$result4 = $action_static->setParentActionId ( $user, $this->id );
			if ($result4 < 0) {
				dol_syslog ( get_class ( $this ) . "::createAdmLevelForTraining error=" . $action_static->error, LOG_ERR );
				$this->error = $action_static->error;
				$error ++;
			}
		} else {
			dol_syslog ( get_class ( $this ) . "::createAdmLevelForTraining error=" . $admlevel->error, LOG_ERR );
			$this->error = $admlevel->error;
			$error ++;
		}
		
		return $error;
	}
}
class AgfObjPedaLine {
	var $id;
	var $fk_formation_catalogue;
	var $intitule;
	var $priorite;
	function __construct() {
		return 1;
	}
}
class AgfTrainingLine {
	var $rowid;
	var $intitule;
	var $ref_interne;
	var $ref;
	var $datec;
	var $duree;
	var $lastsession;
	var $nbsession;
	var $fk_product;
	var $nb_subscribe_min;
	var $category_lib;
	function __construct() {
		return 1;
	}
}
