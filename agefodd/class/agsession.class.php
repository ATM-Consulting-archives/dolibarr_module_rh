<?php
/**
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2012		JF FERRY	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file agefodd/class/agsession.class.php
 * \ingroup agefodd
 * \brief Manage Session object
 */
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");

/**
 * Session Class
 */
class Agsession extends CommonObject {
	var $db;
	var $error;
	var $errors = array ();
	var $element = 'agefodd_agsession';
	var $table_element = 'agefodd_session';
	protected $ismultientitymanaged = 1; // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
	var $id;
	var $fk_soc;
	var $client;
	var $socid;
	var $fk_formation_catalogue;
	var $fk_session_place;
	var $nb_place;
	var $nb_stagiaire;
	var $force_nb_stagiaire;
	var $type_session; // type formation entreprise : 0 intra / 1 inter
	var $dated = '';
	var $datef = '';
	var $notes;
	var $color;
	var $cost_trainer;
	var $cost_site;
	var $cost_trip;
	var $sell_price;
	var $date_res_site = '';
	var $is_date_res_site;
	var $date_res_trainer = '';
	var $is_date_res_trainer;
	var $date_ask_OPCA = '';
	var $is_date_ask_OPCA;
	var $is_OPCA;
	var $fk_soc_OPCA;
	var $soc_OPCA_name;
	var $fk_socpeople_OPCA;
	var $contact_name_OPCA;
	var $OPCA_contact_adress;
	var $OPCA_adress;
	var $num_OPCA_soc;
	var $num_OPCA_file;
	var $fk_user_author;
	var $datec = '';
	var $fk_user_mod;
	var $tms = '';
	var $archive;
	var $lines = array ();
	var $commercialid;
	var $commercialname;
	var $contactid;
	var $contactname;
	var $sourcecontactid;
	var $fk_actioncomm;
	var $fk_product;
	var $formintitule;
	var $formid;
	var $formref;
	var $duree;
	var $nb_subscribe_min;
	var $status;
	var $statuscode;
	var $statuslib;
	var $contactcivilite;
	var $duree_session;
	var $intitule_custo;

	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	function __construct($db) {

		$this->db = $db;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user that create
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function create($user, $notrigger = 0) {

		require_once ('agefodd_formation_catalogue.class.php');
		
		require_once (DOL_DOCUMENT_ROOT . "/societe/class/societe.class.php");
		
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset ( $this->fk_formation_catalogue ))
			$this->fk_formation_catalogue = trim ( $this->fk_formation_catalogue );
		if (isset ( $this->fk_session_place ))
			$this->fk_session_place = trim ( $this->fk_session_place );
		if (isset ( $this->fk_soc ))
			$this->fk_soc = trim ( $this->fk_soc );
		if ($this->fk_soc == - 1)
			unset ( $this->fk_soc );
		if (isset ( $this->nb_place ))
			$this->nb_place = trim ( $this->nb_place );
		if (isset ( $this->notes ))
			$this->notes = trim ( $this->notes );
		if (isset ( $this->status ))
			$this->status = trim ( $this->status );
		if (empty ( $this->status ))
			$this->status = $conf->global->AGF_DEFAULT_SESSION_STATUS;
			
			// Check parameters
			// Put here code to add control on parameters values
		if (empty ( $this->nb_place ))
			$this->nb_place = 0;
			
			// find the nb_subscribe_min of training to set it into session
		$training = new Agefodd ( $this->db );
		$training->fetch ( $this->fk_formation_catalogue );
		$this->nb_subscribe_min = $training->nb_subscribe_min;
		if (empty ( $this->duree_session )) {
			$this->duree_session = $training->duree;
		}
		if (empty ( $this->intitule_custo )) {
			$this->intitule_custo = $training->intitule;
		}
		if (empty ( $this->fk_product )) {
			$this->fk_product = $training->fk_product;
		}
		
		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_session(";
		$sql .= "fk_soc,";
		$sql .= "fk_formation_catalogue,";
		$sql .= "fk_session_place,";
		$sql .= "nb_place,";
		$sql .= "type_session,";
		$sql .= "dated,";
		$sql .= "datef,";
		$sql .= "notes,";
		$sql .= "nb_subscribe_min,";
		$sql .= "fk_user_author,";
		$sql .= "datec,";
		$sql .= "fk_user_mod,";
		$sql .= "entity,";
		$sql .= "fk_product,";
		$sql .= "status,";
		$sql .= "duree_session,";
		$sql .= "intitule_custo";
		$sql .= ") VALUES (";
		$sql .= " " . (! isset ( $this->fk_soc ) ? 'NULL' : "'" . $this->fk_soc . "'") . ",";
		$sql .= " " . (! isset ( $this->fk_formation_catalogue ) ? 'NULL' : "'" . $this->fk_formation_catalogue . "'") . ",";
		$sql .= " " . (! isset ( $this->fk_session_place ) ? 'NULL' : "'" . $this->fk_session_place . "'") . ",";
		$sql .= " " . (! isset ( $this->nb_place ) ? 'NULL' : $this->nb_place) . ",";
		$sql .= " " . (! isset ( $this->type_session ) ? '0' : "'" . $this->type_session . "'") . ",";
		$sql .= " " . (! isset ( $this->dated ) || dol_strlen ( $this->dated ) == 0 ? 'NULL' : "'" . $this->db->idate ( $this->dated ) . "'") . ",";
		$sql .= " " . (! isset ( $this->datef ) || dol_strlen ( $this->datef ) == 0 ? 'NULL' : "'" . $this->db->idate ( $this->datef ) . "'") . ",";
		$sql .= " " . (! isset ( $this->notes ) ? 'NULL' : "'" . $this->db->escape ( $this->notes ) . "'") . ",";
		$sql .= " " . (! isset ( $this->nb_subscribe_min ) ? 'NULL' : $this->nb_subscribe_min) . ",";
		$sql .= " " . $this->db->escape ( $user->id ) . ",";
		$sql .= " '" . $this->db->idate ( dol_now () ) . "',";
		$sql .= " " . $this->db->escape ( $user->id ) . ",";
		$sql .= " " . $conf->entity . ",";
		$sql .= " " . (empty ( $this->fk_product ) ? 'NULL' : $this->fk_product) . ",";
		$sql .= " " . (! isset ( $this->status ) ? 'NULL' : "'" . $this->db->escape ( $this->status ) . "'"). ",";
		$sql .= " " . (empty( $this->duree_session ) ? '0' : $this->duree_session). ",";
		$sql .= " " . (! isset ( $this->intitule_custo ) ? 'NULL' : "'" . $this->db->escape ( $this->intitule_custo ) . "'"). "";
		$sql .= ")";
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		if (! $error) {
			$this->id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "agefodd_session" );
			// Create or update line in session commercial table and get line number
			if (! empty ( $this->commercialid )) {
				$result = $this->setCommercialSession ( $this->commercialid, $user );
				if ($result <= 0) {
					$error ++;
					$this->errors [] = "Error " . $this->db->lasterror ();
				}
			}
			
			// Create or update line in session contact table and get line number
			/*
			 * if ($conf->global->AGF_CONTACT_DOL_SESSION)	{ $contactid = $this->sourcecontactid; } else { $contactid = $this->contactid; }
			 */
			$contactid = $this->contactid;
			if ($contactid) {
				$result = $this->setContactSession ( $contactid, $user );
				if ($result <= 0) {
					$error ++;
					$this->errors [] = "Error " . $this->db->lasterror ();
				}
			}
			
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
			
			if (empty ( $conf->global->MAIN_EXTRAFIELDS_DISABLED )) 			// For avoid conflicts if trigger used
			{

				//Fill session extrafields with customer extrafield if they are the same
				if (!empty($this->fk_soc)) {
					$soc=new Societe($this->db);
					$soc->fetch($this->fk_soc);
					if (!empty($soc->id)) {
						foreach($this->array_options as $key=>$value) {
							//If same extrafeild exists into customer=> Transfert it to session and value is not fill yet
							if ( array_key_exists($key,$soc->array_options) && (!empty($soc->array_options[$key])) && (empty($this->array_options[$key]))) {
								$this->array_options[$key]=$soc->array_options[$key];
							}
						}
						
					}
				}
				
				
				$result = $this->insertExtraFields ();
				if ($result < 0) {
					$error ++;
				}
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
	 * Load an object from its id and create a new one in database
	 *
	 * @param int $fromid of object to clone
	 * @return int id of clone
	 */
	function createFromClone($fromid) {

		global $user, $langs;
		
		$error = 0;
		
		$object = new Agsession ( $this->db );
		
		$this->db->begin ();
		
		// Load source object
		$object->fetch ( $fromid );
		$object->id = 0;
		$object->statut = 0;
		$object->nb_stagiaire = 0;
		
		// Create clone
		$result = $object->create ( $user );
		
		$result = $object->createAdmLevelForSession ( $user );
		
		// Other options
		if ($result < 0) {
			$this->error = $object->error;
			$error ++;
		}
		
		// End
		if (! $error) {
			$this->db->commit ();
			return $object->id;
		} else {
			$this->db->rollback ();
			return - 1;
		}
	}

	/**
	 * Create admin level for a session
	 */
	function createAdmLevelForSession($user) {

		$error = '';
		
		require_once ('agefodd_sessadm.class.php');
		require_once (DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php");
		require_once ('agefodd_training_admlevel.class.php');
		$admlevel = new Agefodd_training_admlevel ( $this->db );
		$result2 = $admlevel->fetch_all ( $this->fk_formation_catalogue );
		
		if ($result2 > 0) {
			foreach ( $admlevel->lines as $line ) {
				$actions = new Agefodd_sessadm ( $this->db );
				
				$actions->datea = dol_time_plus_duree ( $this->dated, $line->alerte, 'd' );
				$actions->dated = dol_time_plus_duree ( $actions->datea, - 7, 'd' );
				
				if ($actions->datea > $this->datef) {
					$actions->datef = dol_time_plus_duree ( $actions->datea, 7, 'd' );
				} else {
					$actions->datef = $this->datef;
				}
				
				$actions->fk_agefodd_session_admlevel = $line->rowid;
				$actions->fk_agefodd_session = $this->id;
				$actions->delais_alerte = $line->alerte;
				$actions->intitule = $line->intitule;
				$actions->indice = $line->indice;
				$actions->archive = 0;
				$actions->level_rank = $line->level_rank;
				$actions->fk_parent_level = $line->fk_parent_level; // Treatement to calculate the new parent level is after
				$result3 = $actions->create ( $user );
				
				if ($result3 < 0) {
					dol_syslog ( get_class ( $this ) . "::createAdmLevelForSession error=" . $actions->error, LOG_ERR );
					$this->error = $actions->error;
					$error ++;
				}
			}
			
			// Caculate the new parent level
			$action_static = new Agefodd_sessadm ( $this->db );
			$result4 = $action_static->setParentActionId ( $user, $this->id );
			if ($result4 < 0) {
				dol_syslog ( get_class ( $this ) . "::createAdmLevelForSession error=" . $action_static->error, LOG_ERR );
				$this->error = $action_static->error;
				$error ++;
			}
		} else {
			dol_syslog ( get_class ( $this ) . "::createAdmLevelForSession error=" . $admlevel->error, LOG_ERR );
			$this->error = $admlevel->error;
			$error ++;
		}
		
		return $error;
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id) {

		global $langs, $conf;
		
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.fk_soc,";
		$sql .= " t.fk_formation_catalogue,";
		$sql .= " c.intitule as formintitule,";
		$sql .= " c.rowid as formid,";
		$sql .= " c.ref as formref,";
		$sql .= " c.duree,";
		$sql .= " t.fk_session_place,";
		$sql .= " t.nb_place,";
		$sql .= " t.nb_stagiaire,";
		$sql .= " t.force_nb_stagiaire,";
		$sql .= " t.type_session,";
		$sql .= " t.dated,";
		$sql .= " t.datef,";
		$sql .= " t.notes,";
		$sql .= " t.nb_subscribe_min,";
		$sql .= " t.color,";
		$sql .= " t.cost_trainer,";
		$sql .= " t.cost_site,";
		$sql .= " t.cost_trip,";
		$sql .= " t.sell_price,";
		$sql .= " t.date_res_site,";
		$sql .= " t.is_date_res_site,";
		$sql .= " t.date_res_trainer,";
		$sql .= " t.is_date_res_trainer,";
		$sql .= " t.date_ask_OPCA as date_ask_opca,";
		$sql .= " t.is_date_ask_OPCA as is_date_ask_opca,";
		$sql .= " t.is_OPCA as is_opca,";
		$sql .= " t.fk_soc_OPCA as fk_soc_opca,";
		$sql .= " t.fk_socpeople_OPCA as fk_socpeople_opca,";
		$sql .= " concactOPCA.lastname as concact_opca_name, concactOPCA.firstname as concact_opca_firstname,";
		$sql .= " t.num_OPCA_soc as num_opca_soc,";
		$sql .= " t.num_OPCA_file as num_opca_file,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms,";
		$sql .= " t.archive,";
		$sql .= " t.fk_product,";
		$sql .= " t.duree_session,";
		$sql .= " t.intitule_custo,";
		$sql .= " t.status,dictstatus.intitule as statuslib, dictstatus.code as statuscode,";
		$sql .= " p.rowid as placeid, p.ref_interne as placecode,";
		$sql .= " us.lastname as commercialname, us.firstname as commercialfirstname, ";
		$sql .= " com.fk_user_com as commercialid, ";
		$sql .= " socp.lastname as contactname, socp.firstname as contactfirstname, socp.civilite as contactcivilite,";
		$sql .= " agecont.fk_socpeople as sourcecontactid, ";
		$sql .= " agecont.rowid as contactid, ";
		$sql .= " socOPCA.address as opca_adress, socOPCA.zip as opca_cp, socOPCA.town as opca_ville, ";
		$sql .= " socOPCA.nom as soc_opca_name, ";
		$sql .= " concactOPCA.address as opca_contact_adress, concactOPCA.zip as opca_contact_cp, concactOPCA.town as opca_contact_ville ";
		
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as t";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " ON c.rowid = t.fk_formation_catalogue";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
		$sql .= " ON p.rowid = t.fk_session_place";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON ss.fk_session_agefodd = c.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_commercial as com";
		$sql .= " ON com.fk_session_agefodd = t.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as us";
		$sql .= " ON com.fk_user_com = us.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_contact as scont";
		$sql .= " ON scont.fk_session_agefodd = t.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_contact as agecont";
		$sql .= " ON agecont.rowid = scont.fk_agefodd_contact";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as socp ";
		$sql .= " ON agecont.fk_socpeople = socp.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as socOPCA ";
		$sql .= " ON t.fk_soc_OPCA = socOPCA.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as concactOPCA ";
		$sql .= " ON t.fk_socpeople_OPCA = concactOPCA.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_status_type as dictstatus";
		$sql .= " ON t.status = dictstatus.rowid";
		$sql .= " WHERE t.rowid = " . $id;
		$sql .= " AND t.entity IN (" . getEntity ( 'agsession' ) . ")";
		
		dol_syslog ( get_class ( $this ) . "::fetch sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->id = $obj->rowid;
				$this->ref = $obj->rowid; // Use for next prev ref
				$this->fk_soc = $obj->fk_soc; // don't work with fetch_thirdparty()
				$this->socid = $obj->fk_soc; // work with fetch_thirdparty()
				$this->fk_formation_catalogue = $obj->fk_formation_catalogue;
				$this->formintitule = $obj->formintitule;
				$this->formid = $obj->formid;
				$this->formref = $obj->formref;
				$this->duree = $obj->duree;
				$this->fk_product = $obj->fk_product;
				$this->fk_session_place = $obj->fk_session_place;
				$this->nb_place = $obj->nb_place;
				$this->nb_stagiaire = $obj->nb_stagiaire;
				$this->force_nb_stagiaire = $obj->force_nb_stagiaire;
				$this->type_session = $obj->type_session;
				$this->placeid = $obj->placeid;
				$this->placecode = $obj->placecode;
				$this->dated = $this->db->jdate ( $obj->dated );
				$this->datef = $this->db->jdate ( $obj->datef );
				$this->notes = $obj->notes;
				$this->nb_subscribe_min = $obj->nb_subscribe_min;
				$this->color = $obj->color;
				$this->cost_trainer = $obj->cost_trainer;
				$this->cost_site = $obj->cost_site;
				$this->cost_trip = $obj->cost_trip;
				$this->sell_price = $obj->sell_price;
				$this->date_res_site = $this->db->jdate ( $obj->date_res_site );
				$this->is_date_res_site = $obj->is_date_res_site;
				$this->date_res_trainer = $this->db->jdate ( $obj->date_res_trainer );
				$this->is_date_res_trainer = $obj->is_date_res_trainer;
				$this->date_ask_OPCA = $this->db->jdate ( $obj->date_ask_opca );
				$this->is_date_ask_OPCA = $obj->is_date_ask_opca;
				$this->is_OPCA = $obj->is_opca;
				$this->fk_soc_OPCA = $obj->fk_soc_opca;
				$this->soc_OPCA_name = $obj->soc_opca_name;
				if (($conf->global->AGF_LINK_OPCA_ADRR_TO_CONTACT) && (! empty ( $obj->opca_contact_adress ))) {
					$this->OPCA_adress = $obj->opca_contact_adress . "\n" . $obj->opca_contact_cp . ' - ' . $obj->opca_contact_ville;
				} else {
					$this->OPCA_adress = $obj->opca_adress . "\n" . $obj->opca_cp . ' - ' . $obj->opca_ville;
				}
				$this->fk_socpeople_OPCA = $obj->fk_socpeople_opca;
				$this->contact_name_OPCA = $obj->concact_opca_name . ' ' . $obj->concact_opca_firstname;
				$this->num_OPCA_soc = $obj->num_opca_soc;
				$this->num_OPCA_file = $obj->num_opca_file;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate ( $obj->datec );
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate ( $obj->tms );
				$this->archive = $obj->archive;
				$this->commercialname = $obj->commercialname . ' ' . $obj->commercialfirstname;
				$this->commercialid = $obj->commercialid;
				$this->contactname = $obj->contactname . ' ' . $obj->contactfirstname;
				$this->contactcivilite = $obj->contactcivilite;
				$this->sourcecontactid = $obj->sourcecontactid;
				$this->contactid = $obj->contactid;
				$this->archive = $obj->archive;
				$this->status = $obj->status;
				$this->statuscode = $obj->statuscode;
				if ($obj->statuslib == $langs->trans ( 'AgfStatusSession_' . $obj->statuscode )) {
					$label = stripslashes ( $obj->statuslib );
				} else {
					$label = $langs->trans ( 'AgfStatusSession_' . $obj->statuscode );
				}
				$this->statuslib = $label;
				$this->intitule_custo = $obj->intitule_custo;
				$this->duree_session = $obj->duree_session;
			}
			$this->db->free ( $resql );
			
			require_once (DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php');
			$extrafields = new ExtraFields ( $this->db );
			$extralabels = $extrafields->fetch_name_optionals_label ( $this->table_element, true );
			if (count ( $extralabels ) > 0) {
				$this->fetch_optionals ( $this->id, $extralabels );
			}
			
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Load object (all trainee for one session) in memory from database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_session_per_trainee($id) {

		global $langs;
		
		$sql = "SELECT";
		$sql .= " s.rowid as sessid,";
		$sql .= " so.rowid as socid,";
		$sql .= " so.nom as socname,";
		$sql .= " s.type_session,";
		$sql .= " s.fk_session_place,";
		$sql .= " s.dated,";
		$sql .= " s.datef,";
		$sql .= " c.intitule,";
		$sql .= " c.ref,";
		$sql .= " c.ref_interne,";
		$sql .= " s.color,";
		$sql .= " ss.status_in_session";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON s.rowid = ss.fk_session_agefodd";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " ON c.rowid = s.fk_formation_catalogue";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sa";
		$sql .= " ON sa.rowid = ss.fk_stagiaire";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_civilite as civ";
		$sql .= " ON civ.code = sa.civilite";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = s.fk_soc";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as sope";
		$sql .= " ON sope.rowid = sa.fk_socpeople";
		$sql .= " WHERE sa.rowid = " . $id;
		if (! empty ( $socid ))
			$sql .= " AND so.rowid = " . $socid;
		$sql .= " ORDER BY sa.nom";
		
		dol_syslog ( get_class ( $this ) . "::fetch_session_per_trainee sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->line = array ();
			$num = $this->db->num_rows ( $resql );
			
			$i = 0;
			while ( $i < $num ) {
				$obj = $this->db->fetch_object ( $resql );
				
				$line = new AgfSessionLine ();
				
				$line->rowid = $obj->sessid;
				$line->socid = $obj->socid;
				$line->socname = $obj->socname;
				$line->type_session = $obj->type_session;
				$line->fk_session_place = $obj->fk_session_place;
				$line->dated = $this->db->jdate ( $obj->dated );
				$line->datef = $this->db->jdate ( $obj->datef );
				$line->intitule = $obj->intitule;
				$line->ref = $obj->ref;
				$line->ref_interne = $obj->ref_interne;
				$line->color = $obj->color;
				$line->status_in_session = $obj->status_in_session;
				
				$this->lines [$i] = $line;
				
				$i ++;
			}
			$this->db->free ( $resql );
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_session_per_trainee " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Load object (company per session) in memory from database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_societe_per_session($id) {

		$error = 0;
		global $langs;
		
		$array_soc = array ();
		
		// Soc trainee
		$sql = "SELECT";
		$sql .= " DISTINCT so.rowid as socid,";
		$sql .= " s.rowid, s.type_session, s.is_OPCA as is_opca, s.fk_soc_OPCA as fk_soc_opca, so.nom as socname, so.code_client ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON s.rowid = ss.fk_session_agefodd";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sa";
		$sql .= " ON sa.rowid = ss.fk_stagiaire";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = sa.fk_soc";
		$sql .= " WHERE s.rowid = " . $id;
		$sql .= " ORDER BY socname";
		
		dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session SocTrainee sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->line = array ();
			$num = $this->db->num_rows ( $resql );
			
			if ($num) {
				$i = 0;
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					
					$newline = new AgfSocLine ();
					
					$newline->sessid = $obj->rowid;
					$newline->socname = $obj->socname;
					$newline->code_client = $obj->code_client;
					$newline->socid = $obj->socid;
					$newline->type_session = $obj->type_session;
					$newline->is_OPCA = $obj->is_opca;
					$newline->fk_soc_OPCA = $obj->fk_soc_opca;
					
					$array_soc [] = $obj->socid;
					
					$this->lines [] = $newline;
					$i ++;
				}
			}
			
			$this->db->free ( $resql );
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session " . $this->error, LOG_ERR );
			$error ++;
		}
		
		// Get OPCA Soc
		$sql = "SELECT";
		$sql .= " DISTINCT so.rowid as socid,";
		$sql .= " s.rowid, s.type_session, s.is_OPCA as is_opca, s.fk_soc_OPCA as fk_soc_opca, so.nom as socname, so.code_client ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = s.fk_soc_OPCA";
		$sql .= " WHERE s.rowid = " . $id;
		$sql .= " ORDER BY socname";
		
		dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session OPCA sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$add_soc = 0;
			$num_other = $this->db->num_rows ( $resql );
			
			if ($num_other) {
				$i = 0;
				while ( $i < $num_other ) {
					$obj = $this->db->fetch_object ( $resql );
					if (! empty ( $obj->fk_soc_opca )) {
						if (! in_array ( $obj->socid, $array_soc )) {
							$newline = new AgfSocLine ();
							
							$newline->sessid = $obj->rowid;
							$newline->socname = $obj->socname;
							$newline->socid = $obj->socid;
							$newline->code_client = $obj->code_client;
							$newline->type_session = $obj->type_session;
							$newline->is_OPCA = $obj->is_opca;
							$newline->fk_soc_OPCA = $obj->fk_soc_opca;
							
							$array_soc [] = $obj->socid;
							
							$this->lines [] = $newline;
							
							$add_soc ++;
						}
					}
					$i ++;
				}
			}
			
			$this->db->free ( $resql );
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session OPCA " . $this->error, LOG_ERR );
			$error ++;
		}
		
		$num = $num + $add_soc;
		
		// Get OPCA Soc of trainee
		$sql = "SELECT";
		$sql .= " DISTINCT soOPCATrainee.rowid as socid,";
		$sql .= " s.rowid, s.type_session, s.is_OPCA as is_opca, s.fk_soc_OPCA as fk_soc_opca, soOPCATrainee.nom as socname, soOPCATrainee.code_client ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON s.rowid = ss.fk_session_agefodd";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sa";
		$sql .= " ON sa.rowid = ss.fk_stagiaire";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = sa.fk_soc";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_opca AS soOPCA ON soOPCA.fk_soc_trainee = so.rowid ";
		$sql .= " AND soOPCA.fk_session_agefodd = s.rowid ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as soOPCATrainee";
		$sql .= " ON soOPCATrainee.rowid = soOPCA.fk_soc_OPCA";
		$sql .= " WHERE s.rowid = " . $id;
		$sql .= " ORDER BY socname";
		
		dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session OPCAtrainee sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$add_soc = 0;
			$num_other = $this->db->num_rows ( $resql );
			
			if ($num_other) {
				$i = 0;
				while ( $i < $num_other ) {
					$obj = $this->db->fetch_object ( $resql );
					
					if (! empty ( $obj->socid )) {
						if (! in_array ( $obj->socid, $array_soc )) {
							$newline = new AgfSocLine ();
							$newline->sessid = $obj->rowid;
							$newline->socname = $obj->socname;
							$newline->socid = $obj->socid;
							$newline->code_client = $obj->code_client;
							$newline->type_session = $obj->type_session;
							$newline->is_OPCA = $obj->is_opca;
							$newline->fk_soc_OPCA = $obj->fk_soc_opca;
							
							$array_soc [] = $obj->socid;
							
							$this->lines [] = $newline;
							$add_soc ++;
						}
					}
					$i ++;
				}
			}
			
			$this->db->free ( $resql );
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session OPCAtrainee " . $this->error, LOG_ERR );
			$error ++;
		}
		
		$num = $num + $add_soc;
		
		// Get session customer
		$sql = "SELECT";
		$sql .= " DISTINCT s.fk_soc as socid,";
		$sql .= " s.rowid, s.type_session, s.is_OPCA as is_opca, s.fk_soc_OPCA as fk_soc_opca , so.nom as socname, so.code_client ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = s.fk_soc";
		$sql .= " WHERE s.rowid = " . $id;
		$sql .= " ORDER BY socname";
		
		dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session Customer sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$add_soc = 0;
			$num_other = $this->db->num_rows ( $resql );
			
			if ($num_other) {
				$i = 0;
				while ( $i < $num_other ) {
					$obj = $this->db->fetch_object ( $resql );
					if (! empty ( $obj->socid )) {
						if (! in_array ( $obj->socid, $array_soc )) {
							$newline = new AgfSocLine ();
							$newline->sessid = $obj->rowid;
							$newline->socname = $obj->socname;
							$newline->socid = $obj->socid;
							$newline->code_client = $obj->code_client;
							$newline->type_session = $obj->type_session;
							$newline->is_OPCA = $obj->is_opca;
							$newline->fk_soc_OPCA = $obj->fk_soc_opca;
							
							$array_soc [] = $obj->socid;
							
							$this->lines [] = $newline;
							$add_soc ++;
						}
					}
					$i ++;
				}
			}
			
			$this->db->free ( $resql );
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_societe_per_session Customer " . $this->error, LOG_ERR );
			$error ++;
		}
		
		$num = $num + $add_soc;
		
		if (! $error) {
			return $num;
		} else {
			return - 1;
		}
	}

	/**
	 * Load object (information) in memory from database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	function info($id) {

		global $langs;
		
		$sql = "SELECT";
		$sql .= " s.rowid, s.datec, s.tms, s.fk_user_author, s.fk_user_mod";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " WHERE s.rowid = " . $id;
		
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
	 * Update only archive session into database
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function updateArchive($user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session SET";
		$sql .= " fk_user_mod=" . $this->db->escape ( $user->id ) . ",";
		$sql .= " archive=" . (isset ( $this->archive ) ? $this->archive : "0") . "";
		$sql .= " WHERE rowid=" . $this->id;
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::updateArchive sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			dol_syslog ( get_class ( $this ) . "::updateArchive sql=" . $sql, LOG_ERR );
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function update($user, $notrigger = 0) {

		require_once ('agefodd_session_stagiaire.class.php');
		
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		if (isset ( $this->fk_soc ))
			$this->fk_soc = trim ( $this->fk_soc );
		if ($this->fk_soc == - 1)
			unset ( $this->fk_soc );
		if (isset ( $this->fk_formation_catalogue ))
			$this->fk_formation_catalogue = trim ( $this->fk_formation_catalogue );
		if (isset ( $this->fk_session_place ))
			$this->fk_session_place = trim ( $this->fk_session_place );
		if (isset ( $this->nb_place ))
			$this->nb_place = trim ( $this->nb_place );
		if (isset ( $this->nb_stagiaire ))
			$this->nb_stagiaire = trim ( $this->nb_stagiaire );
		if (isset ( $this->force_nb_stagiaire ))
			$this->force_nb_stagiaire = trim ( $this->force_nb_stagiaire );
		if (isset ( $this->type_session ))
			$this->type_session = trim ( $this->type_session );
		if (isset ( $this->notes ))
			$this->notes = trim ( $this->notes );
		if (isset ( $this->color ))
			$this->color = trim ( $this->color );
		if (isset ( $this->cost_trainer ))
			$this->cost_trainer = price2num ( trim ( $this->cost_trainer ) );
		if (isset ( $this->cost_site ))
			$this->cost_site = price2num ( trim ( $this->cost_site ) );
		if (isset ( $this->cost_trip ))
			$this->cost_trip = price2num ( trim ( $this->cost_trip ) );
		if (isset ( $this->sell_price ))
			$this->sell_price = price2num ( trim ( $this->sell_price ) );
		if (isset ( $this->is_OPCA ))
			$this->is_OPCA = trim ( $this->is_OPCA );
		if (isset ( $this->is_date_res_site ))
			$this->is_date_res_site = trim ( $this->is_date_res_site );
		if (isset ( $this->is_date_res_trainer ))
			$this->is_date_res_trainer = trim ( $this->is_date_res_trainer );
		if (isset ( $this->fk_soc_OPCA ))
			$this->fk_soc_OPCA = trim ( $this->fk_soc_OPCA );
		if (isset ( $this->fk_socpeople_OPCA ))
			$this->fk_socpeople_OPCA = trim ( $this->fk_socpeople_OPCA );
		if (isset ( $this->num_OPCA_soc ))
			$this->num_OPCA_soc = trim ( $this->num_OPCA_soc );
		if (isset ( $this->num_OPCA_file ))
			$this->num_OPCA_file = trim ( $this->num_OPCA_file );
		if (isset ( $this->archive ))
			$this->archive = trim ( $this->archive );
		if (isset ( $this->fk_product ))
			$this->fk_product = trim ( $this->fk_product );
		if (isset ( $this->status ))
			$this->status = trim ( $this->status );
		if (isset ( $this->duree_session ))
			$this->duree_session = trim ( $this->duree_session );
		if (isset ( $this->intitule_custo ))
			$this->intitule_custo = trim ( $this->intitule_custo );
			
			// Create or update line in session commercial table and get line number
		$result = $this->setCommercialSession ( $this->commercialid, $user );
		if ($result <= 0) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		// Create or update line in session contact table and get line number
		if ($conf->global->AGF_CONTACT_DOL_SESSION) {
			$result = $this->setContactSession ( $this->sourcecontactid, $user );
		} else {
			$result = $this->setContactSession ( $this->contactid, $user );
		}
		
		if ($result <= 0) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		if (empty ( $this->force_nb_stagiaire )) {
			$session_sta = new Agefodd_session_stagiaire ( $this->db );
			$session_sta->fetch_stagiaire_per_session ( $this->id );
			$this->nb_stagiaire = count ( $session_sta->lines );
		}
		
		if ($error == 0) {
			// Check parameters
			// Put here code to add control on parameters values
			
			// Update request
			$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session SET";
			
			$sql .= " fk_soc=" . (isset ( $this->fk_soc ) ? $this->fk_soc : "null") . ",";
			$sql .= " fk_formation_catalogue=" . (isset ( $this->fk_formation_catalogue ) ? $this->fk_formation_catalogue : "null") . ",";
			$sql .= " fk_session_place=" . (isset ( $this->fk_session_place ) ? $this->fk_session_place : "null") . ",";
			$sql .= " nb_place=" . (isset ( $this->nb_place ) ? $this->nb_place : "null") . ",";
			$sql .= " nb_subscribe_min=" . (! empty ( $this->nb_subscribe_min ) ? $this->nb_subscribe_min : "null") . ",";
			$sql .= " nb_stagiaire=" . (isset ( $this->nb_stagiaire ) ? $this->nb_stagiaire : "null") . ",";
			$sql .= " force_nb_stagiaire=" . (isset ( $this->force_nb_stagiaire ) ? $this->force_nb_stagiaire : "0") . ",";
			$sql .= " type_session=" . (isset ( $this->type_session ) ? $this->type_session : "null") . ",";
			$sql .= " dated=" . (dol_strlen ( $this->dated ) != 0 ? "'" . $this->db->idate ( $this->dated ) . "'" : 'null') . ",";
			$sql .= " datef=" . (dol_strlen ( $this->datef ) != 0 ? "'" . $this->db->idate ( $this->datef ) . "'" : 'null') . ",";
			$sql .= " notes=" . (isset ( $this->notes ) ? "'" . $this->db->escape ( $this->notes ) . "'" : "null") . ",";
			$sql .= " color=" . (isset ( $this->color ) ? "'" . $this->db->escape ( $this->color ) . "'" : "null") . ",";
			$sql .= " cost_trainer=" . (isset ( $this->cost_trainer ) ? $this->cost_trainer : "null") . ",";
			$sql .= " cost_site=" . (isset ( $this->cost_site ) ? $this->cost_site : "null") . ",";
			$sql .= " cost_trip=" . (isset ( $this->cost_trip ) ? $this->cost_trip : "null") . ",";
			$sql .= " sell_price=" . (isset ( $this->sell_price ) ? $this->sell_price : "null") . ",";
			$sql .= " date_res_site=" . (dol_strlen ( $this->date_res_site ) != 0 ? "'" . $this->db->idate ( $this->date_res_site ) . "'" : 'null') . ",";
			$sql .= " date_res_trainer=" . (dol_strlen ( $this->date_res_trainer ) != 0 ? "'" . $this->db->idate ( $this->date_res_trainer ) . "'" : 'null') . ",";
			$sql .= " date_ask_OPCA=" . (dol_strlen ( $this->date_ask_OPCA ) != 0 ? "'" . $this->db->idate ( $this->date_ask_OPCA ) . "'" : 'null') . ",";
			$sql .= " is_OPCA=" . (isset ( $this->is_OPCA ) ? $this->is_OPCA : "0") . ",";
			$sql .= " is_date_res_site=" . (isset ( $this->is_date_res_site ) ? $this->is_date_res_site : "0") . ",";
			$sql .= " is_date_res_trainer=" . (isset ( $this->is_date_res_trainer ) ? $this->is_date_res_trainer : "0") . ",";
			$sql .= " is_date_ask_OPCA=" . (isset ( $this->is_date_ask_OPCA ) ? $this->is_date_ask_OPCA : "0") . ",";
			$sql .= " fk_soc_OPCA=" . (isset ( $this->fk_soc_OPCA ) && $this->fk_soc_OPCA != - 1 ? $this->fk_soc_OPCA : "null") . ",";
			$sql .= " fk_socpeople_OPCA=" . (isset ( $this->fk_socpeople_OPCA ) && $this->fk_socpeople_OPCA != 0 ? $this->fk_socpeople_OPCA : "null") . ",";
			$sql .= " num_OPCA_soc=" . (isset ( $this->num_OPCA_soc ) ? "'" . $this->db->escape ( $this->num_OPCA_soc ) . "'" : "null") . ",";
			$sql .= " num_OPCA_file=" . (isset ( $this->num_OPCA_file ) ? "'" . $this->db->escape ( $this->num_OPCA_file ) . "'" : "null") . ",";
			$sql .= " fk_user_mod=" . $this->db->escape ( $user->id ) . ",";
			$sql .= " archive=" . (isset ( $this->archive ) ? $this->archive : "0") . ",";
			$sql .= " fk_product=" . (!empty ( $this->fk_product ) ? $this->fk_product : "null") . ",";
			$sql .= " status=" . (isset ( $this->status ) ? $this->status : "null") . ",";
			$sql .= " duree_session=" . (!empty ( $this->duree_session ) ? $this->duree_session : "0") . ",";
			$sql .= " intitule_custo=" . (!empty ( $this->intitule_custo ) ?   "'" .$this->db->escape ($this->intitule_custo). "'" : "null") . "";
			
			$sql .= " WHERE rowid=" . $this->id;
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::update sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		if (! $error) {
			if (empty ( $conf->global->MAIN_EXTRAFIELDS_DISABLED )) 			// For avoid conflicts if trigger used
			{
				$result = $this->insertExtraFields ();
				if ($result < 0) {
					$error ++;
				}
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
	 * Update object (commercial in session) into database
	 *
	 * @param int $userid User commercial to link to session
	 * @param User $user that modify
	 * @return int <0 if KO, >0 if OK
	 */
	function setCommercialSession($userid, $user) {

		global $conf, $langs;
		$error = 0;
		$to_create = false;
		$to_update = false;
		$to_delete = false;
		
		if (empty ( $userid ) || $userid == - 1) {
			$to_delete = true;
		} else {
			
			$sql = "SELECT com.rowid,com.fk_user_com as commercialid FROM " . MAIN_DB_PREFIX . "agefodd_session_commercial as com ";
			$sql .= " WHERE com.fk_session_agefodd=" . $this->db->escape ( $this->id );
			
			dol_syslog ( get_class ( $this ) . "::setCommercialSession sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if ($resql) {
				if ($this->db->num_rows ( $resql )) {
					$obj = $this->db->fetch_object ( $resql );
					// metre a jour
					if ($obj->commercialid != $userid) {
						$to_update = true;
						$fk_commercial = $obj->rowid;
					} else {
						$this->commercialid = $obj->commercialid;
						$fk_commercial = $obj->rowid;
					}
				} else {
					// a crée
					$to_create = true;
				}
				
				$this->db->free ( $resql );
			} else {
				dol_syslog ( get_class ( $this ) . "::setCommercialSession " . $this->db->lasterror (), LOG_ERR );
				return - 1;
			}
		}
		
		if ($to_update) {
			
			// Update request
			$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'agefodd_session_commercial SET ';
			$sql .= ' fk_user_com=' . $this->db->escape ( $userid ) . ',';
			$sql .= ' fk_user_mod=' . $this->db->escape ( $user->id );
			$sql .= ' WHERE rowid=' . $this->db->escape ( $fk_commercial );
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::setCommercialSession update sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		
		if ($to_create) {
			
			// INSERT request
			$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . 'agefodd_session_commercial(fk_session_agefodd, fk_user_com, fk_user_author,fk_user_mod, datec)';
			$sql .= ' VALUES ( ';
			$sql .= $this->db->escape ( $this->id ) . ',';
			$sql .= $this->db->escape ( $userid ) . ',';
			$sql .= $this->db->escape ( $user->id ) . ',';
			$sql .= $this->db->escape ( $user->id ) . ',';
			$sql .= "'" . $this->db->idate ( dol_now () ) . "')";
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::setCommercialSession insert sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		
		if ($to_delete) {
			
			// DELETE request
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_commercial";
			$sql .= " WHERE fk_session_agefodd = " . $this->id;
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::setCommercialSession delete sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::setCommercialSession " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} elseif ($to_create || $to_update || $to_delete) {
			$this->db->commit ();
			return 1;
		} else {
			return 1;
		}
	}

	/**
	 * Update object (contact in session) into database
	 *
	 * @param int $contactid User contact to link to session
	 * @param User $user that modify
	 * @return int <0 if KO, >0 if OK
	 */
	function setContactSession($contactid, $user) {

		global $conf, $langs;
		$error = 0;
		$to_create = false;
		$to_update = false;
		$to_delete = false;
		
		if (empty ( $contactid ) || $contactid == - 1) {
			$to_delete = true;
		} else {
			
			// Contact id can be dolibarr contactid (from llx_socpoeple) or contact of Agefodd (llx_agefodd_contact) according settings
			if ($conf->global->AGF_CONTACT_DOL_SESSION) {
				// Test if this dolibarr contact is already a Agefodd contact
				$sql = "SELECT agecont.rowid FROM " . MAIN_DB_PREFIX . "agefodd_contact as agecont ";
				$sql .= " WHERE agecont.fk_socpeople=" . $contactid;
				
				dol_syslog ( get_class ( $this ) . "::setContactSession sql=" . $sql, LOG_DEBUG );
				$resql = $this->db->query ( $sql );
				if ($resql) {
					if ($this->db->num_rows ( $resql ) > 0) {
						// if exists the contact id to set is the rowid of agefood contact
						$obj = $this->db->fetch_object ( $resql );
						$contactid = $obj->rowid;
					} else {
						// We need to create the agefodd contact
						dol_include_once ( '/agefodd/class/agefodd_contact.class.php' );
						$contactAgefodd = new Agefodd_contact ( $this->db );
						$contactAgefodd->spid = $contactid;
						$result = $contactAgefodd->create ( $user );
						if ($result > 0) {
							$contactid = $result;
						} else {
							dol_syslog ( get_class ( $this ) . "::setContactSession Error agefodd_contact" . $contactAgefodd->error, LOG_ERR );
							$this->db->free ( $resql );
							return - 1;
						}
					}
				} else {
					dol_syslog ( get_class ( $this ) . "::setContactSession Error AGF_CONTACT_DOL_SESSION:" . $this->db->lasterror (), LOG_ERR );
					return - 1;
				}
			}
			
			$sql = "SELECT agecont.rowid,agecont.fk_agefodd_contact as contactid FROM " . MAIN_DB_PREFIX . "agefodd_session_contact as agecont ";
			$sql .= " WHERE agecont.fk_session_agefodd=" . $this->db->escape ( $this->id );
			
			dol_syslog ( get_class ( $this ) . "::setContactSession sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if ($resql) {
				if ($this->db->num_rows ( $resql )) {
					$obj = $this->db->fetch_object ( $resql );
					// metre a jour
					if ($obj->contactid != $contactid) {
						$to_update = true;
						$fk_contact = $obj->rowid;
					} else {
						$this->contactid = $obj->contactid;
						$fk_contact = $obj->rowid;
					}
				} else {
					// a crée
					$to_create = true;
				}
				
				$this->db->free ( $resql );
			} else {
				dol_syslog ( get_class ( $this ) . "::setContactSession Error:" . $this->db->lasterror (), LOG_ERR );
				return - 1;
			}
		}
		
		dol_syslog ( get_class ( $this ) . "::setContactSession to_update:" . $to_update . ", to_create:" . $to_create . ", to_delete:" . $to_delete, LOG_DEBUG );
		
		if ($to_update) {
			
			// Update request
			$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'agefodd_session_contact SET ';
			$sql .= ' fk_agefodd_contact=' . $this->db->escape ( $contactid ) . ',';
			$sql .= ' fk_user_mod=' . $this->db->escape ( $user->id );
			$sql .= ' WHERE rowid=' . $this->db->escape ( $fk_contact );
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::setContactSession update sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		
		if ($to_create) {
			
			// INSERT request
			$sql = 'INSERT INTO ' . MAIN_DB_PREFIX . 'agefodd_session_contact(fk_session_agefodd, fk_agefodd_contact, fk_user_mod, fk_user_author, datec)';
			$sql .= ' VALUES ( ';
			$sql .= $this->db->escape ( $this->id ) . ',';
			$sql .= $this->db->escape ( $contactid ) . ',';
			$sql .= $this->db->escape ( $user->id ) . ',';
			$sql .= $this->db->escape ( $user->id ) . ',';
			$sql .= "'" . $this->db->idate ( dol_now () ) . "')";
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::setContactSession insert sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		
		if ($to_delete) {
			
			// DELETE request
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_contact";
			$sql .= " WHERE fk_session_agefodd = " . $this->id;
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::setContactSession delete sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::setContactSession " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} elseif ($to_create || $to_update || $to_delete) {
			$this->db->commit ();
			return 1;
		} else {
			return 1;
		}
	}

	/**
	 * Update OPCA info for a trainee in a session (used if session type is 'inter')
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function updateInfosOpcaForTrainee() {

		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		$this->date_ask_OPCA = addslashes ( trim ( $this->date_ask_OPCA ) );
		$this->is_date_ask_OPCA = addslashes ( trim ( $this->is_date_ask_OPCA ) );
		$this->is_OPCA = addslashes ( trim ( $this->is_OPCA ) );
		$this->fk_soc_OPCA = addslashes ( trim ( $this->fk_soc_OPCA ) );
		$this->soc_OPCA_name = addslashes ( trim ( $this->soc_OPCA_name ) );
		$this->OPCA_adress = addslashes ( trim ( $this->OPCA_adress ) );
		$this->fk_socpeople_OPCA = addslashes ( trim ( $this->fk_socpeople_OPCA ) );
		$this->contact_name_OPCA = addslashes ( trim ( $this->contact_name_OPCA ) );
		$this->num_OPCA_soc = addslashes ( trim ( $this->num_OPCA_soc ) );
		$this->num_OPCA_file = addslashes ( trim ( $this->num_OPCA_file ) );
		
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_opca SET";
		$sql .= " fk_session_agefodd=" . $this->sessid . ",";
		$sql .= " fk_stagiaire=" . $this->stagiaire . ",";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " fk_agefodd_stagiaire_type='" . $this->stagiaire_type . "',";
		$sql .= " WHERE rowid = " . $this->id;
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::updateInfosOpcaForTrainee sql=" . $sql, LOG_DEBUG );
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
				dol_syslog ( get_class ( $this ) . "::updateInfosOpcaForTrainee " . $errmsg, LOG_ERR );
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
	 * @param int $id to delete
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function remove($id, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		$this->db->begin ();
		
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session";
		$sql .= " WHERE rowid = " . $id;
		
		dol_syslog ( get_class ( $this ) . "::remove sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		if (! $error) {
			// Removed extrafields
			if (empty ( $conf->global->MAIN_EXTRAFIELDS_DISABLED )) 			// For avoid conflicts if trigger used
			{
				$this->id = $id;
				$result = $this->deleteExtraFields ();
				if ($result < 0) {
					$error ++;
					dol_syslog ( get_class ( $this ) . "::delete erreur " . $error . " " . $this->error, LOG_ERR );
				}
			}
		}
		
		if (! $error) {
			// Delete event from agenda that are no more link to a session
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "actioncomm WHERE elementtype='agefodd_agsession' AND fk_element NOT IN (SELECT rowid FROM llx_agefodd_session)";
			
			dol_syslog ( get_class ( $this ) . "::remove sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
		}
		if (! $error) {
			$this->db->commit ();
		} else {
			$this->db->rollback ();
		}
		
		if (! $error) {
			return 1;
		} else {
			$this->error = $this->db->lasterror ();
			return - 1;
		}
	}

	/**
	 * \brief		Initialise object with example values
	 * \remarks	id must be 0 if object instance is a specimen.
	 */
	function initAsSpecimen() {

		$this->id = 0;
	}

	/**
	 * Return description of session
	 *
	 * @param int $type
	 * @return string translated description
	 */
	function getToolTip($type) {

		global $conf;
		
		$langs->load ( "admin" );
		
		$s = '';
		if (type == 'training') {
			dol_include_once ( '/agefodd/class/agefodd_formation_catalogue.class.php' );
			
			$agf_training = new Agefodd ( $db );
			$agf_training->fetch ( $this->formid );
			$s = $agf_training->getToolTip ();
		}
		return $s;
	}

	/**
	 * Load all objects in memory from database
	 *
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param int $arch archive or not
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all($sortorder, $sortfield, $limit, $offset, $arch, $filter = array()) {

		global $langs;
		
		$sql = "SELECT s.rowid, s.fk_soc, s.fk_session_place, s.type_session, s.dated, s.datef, s.status, dictstatus.intitule as statuslib, dictstatus.code as statuscode, ";
		$sql .= " s.is_date_res_site, s.is_date_res_trainer, s.date_res_trainer, s.color, ";
		$sql .= " s.force_nb_stagiaire, s.nb_stagiaire,s.notes,";
		$sql .= " c.intitule, c.ref,c.ref_interne as trainingrefinterne,s.nb_subscribe_min,";
		$sql .= " p.ref_interne";
		$sql .= " ,so.nom as socname";
		$sql .= " ,f.rowid as trainerrowid";
		$sql .= " ,s.intitule_custo";
		$sql .= " ,s.duree_session,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire WHERE (status_in_session=0 OR status_in_session IS NULL) AND fk_session_agefodd=s.rowid) as nb_prospect,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire WHERE (status_in_session=2 OR status_in_session=1 OR status_in_session=3) AND fk_session_agefodd=s.rowid) as nb_confirm,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire WHERE status_in_session=6 AND fk_session_agefodd=s.rowid) as nb_cancelled";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " ON c.rowid = s.fk_formation_catalogue";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
		$sql .= " ON p.rowid = s.fk_session_place";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON s.rowid = ss.fk_session_agefodd";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as sa";
		$sql .= " ON s.rowid = sa.fk_agefodd_session";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = s.fk_soc";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf";
		$sql .= " ON sf.fk_session = s.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as f";
		$sql .= " ON f.rowid = sf.fk_agefodd_formateur";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_status_type as dictstatus";
		$sql .= " ON s.status = dictstatus.rowid";
		
		if ($arch == 2) {
			$sql .= " WHERE s.archive = 0";
			$sql .= " AND sa.indice=";
			$sql .= "(";
			$sql .= " SELECT MAX(indice) FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE level_rank=0";
			$sql .= ")";
			$sql .= " AND sa.archive = 1";
		} else
			$sql .= " WHERE s.archive = " . $arch;
		
		$sql .= " AND s.entity IN (" . getEntity ( 'agsession' ) . ")";
		
		// Manage filter
		if (count ( $filter ) > 0) {
			foreach ( $filter as $key => $value ) {
				if (strpos ( $key, 'date' )) 				// To allow $filter['YEAR(s.dated)']=>$year
				{
					$sql .= ' AND ' . $key . ' = \'' . $value . '\'';
				} elseif (($key == 's.fk_session_place') || ($key == 'f.rowid') || ($key == 's.type_session') || ($key == 's.status')) {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape ( $value ) . '%\'';
				}
			}
		}
		$sql .= " GROUP BY s.rowid, s.fk_soc, s.fk_session_place, s.type_session, s.dated, s.datef,  s.status, dictstatus.intitule , dictstatus.code, s.is_date_res_site, s.is_date_res_trainer, s.date_res_trainer, s.color, s.force_nb_stagiaire, s.nb_stagiaire,s.notes,";
		$sql .= " p.ref_interne, c.intitule, c.ref,c.ref_interne, so.nom, f.rowid";
		$sql .= " ORDER BY " . $sortfield . ' ' . $sortorder;
		if (! empty ( $limit )) {
			$sql .= ' ' . $this->db->plimit ( $limit + 1, $offset );
		}
		
		dol_syslog ( get_class ( $this ) . "::fetch_all sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			$this->lines = array ();
			
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					
					$line = new AgfSessionLine ();
					
					$line->rowid = $obj->rowid;
					$line->socid = $obj->fk_soc;
					$line->socname = $obj->socname;
					$line->trainerrowid = $obj->trainerrowid;
					$line->type_session = $obj->type_session;
					$line->is_date_res_site = $obj->is_date_res_site;
					$line->is_date_res_trainer = $obj->is_date_res_trainer;
					$line->date_res_trainer = $this->db->jdate ( $obj->date_res_trainer );
					$line->fk_session_place = $obj->fk_session_place;
					$line->dated = $this->db->jdate ( $obj->dated );
					$line->datef = $this->db->jdate ( $obj->datef );
					$line->intitule = $obj->intitule;
					$line->ref = $obj->ref;
					$line->training_ref_interne = $obj->trainingrefinterne;
					$line->ref_interne = $obj->ref_interne;
					$line->color = $obj->color;
					$line->nb_stagiaire = $obj->nb_stagiaire;
					$line->force_nb_stagiaire = $obj->force_nb_stagiaire;
					$line->notes = $obj->notes;
					$line->nb_subscribe_min = $obj->nb_subscribe_min;
					$line->nb_prospect = $obj->nb_prospect;
					$line->nb_confirm = $obj->nb_confirm;
					$line->nb_cancelled = $obj->nb_cancelled;
					$line->duree_session = $obj->duree_session;
					$line->intitule_custo = $obj->intitule_custo;
					
					if ($obj->statuslib == $langs->trans ( 'AgfStatusSession_' . $obj->code )) {
						$label = stripslashes ( $obj->statuslib );
					} else {
						$label = $langs->trans ( 'AgfStatusSession_' . $obj->code );
					}
					$line->status_lib = $obj->statuscode . ' - ' . $label;
					
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
	 * Load all objects in memory from database
	 *
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param int $arch archive or not
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all_with_task_state($sortorder, $sortfield, $limit, $offset, $filter = '') {
	
		global $langs;
		
		$interval0day='0 DAY';
		$interval3day='3 DAY';
		$interval8day='8 DAY';
		
		if ($this->db->type=='pgsql') {
			$interval0day="'0 DAYS'";
			$interval3day="'3 DAYS'";;
			$interval8day="'8 DAYS'";;
		}
	
		$sql = "SELECT s.rowid, s.fk_soc, s.fk_session_place, s.type_session, s.dated, s.datef, s.status, dictstatus.intitule as statuslib, dictstatus.code as statuscode, ";
		$sql .= " s.is_date_res_site, s.is_date_res_trainer, s.date_res_trainer, s.color, ";
		$sql .= " s.force_nb_stagiaire, s.nb_stagiaire,s.notes,";
		$sql .= " c.intitule, c.ref,c.ref_interne as trainingrefinterne,s.nb_subscribe_min,";
		$sql .= " p.ref_interne";
		$sql .= " ,so.nom as socname";
		$sql .= " ,f.rowid as trainerrowid";
		$sql .= " ,s.intitule_custo";
		$sql .= " ,s.duree_session,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE (datea - INTERVAL ".$interval0day.") <= NOW() AND fk_agefodd_session=s.rowid) as task0,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE (  NOW() BETWEEN (datea - INTERVAL ".$interval3day.") AND (datea) ) AND fk_agefodd_session=s.rowid) as task1,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE (  NOW() BETWEEN (datea - INTERVAL ".$interval8day.") AND (datea - INTERVAL ".$interval3day.") ) AND fk_agefodd_session=s.rowid) as task2,";
		$sql .= " (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE archive=0 AND fk_agefodd_session=s.rowid) as task3";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " ON c.rowid = s.fk_formation_catalogue";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
		$sql .= " ON p.rowid = s.fk_session_place";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON s.rowid = ss.fk_session_agefodd";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as sa";
		$sql .= " ON s.rowid = sa.fk_agefodd_session";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
		$sql .= " ON so.rowid = s.fk_soc";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf";
		$sql .= " ON sf.fk_session = s.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as f";
		$sql .= " ON f.rowid = sf.fk_agefodd_formateur";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_status_type as dictstatus";
		$sql .= " ON s.status = dictstatus.rowid";
	
		$sql .= " WHERE s.archive = 0";
		$sql .= " AND s.entity IN (" . getEntity ( 'agsession' ) . ")";
		$sql .= " AND (SELECT count(rowid) FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE archive=0 AND fk_agefodd_session=s.rowid)<>0";
	
		// Manage filter
		if (! empty ( $filter )) {
			foreach ( $filter as $key => $value ) {
				if (strpos ( $key, 'date' )) 				// To allow $filter['YEAR(s.dated)']=>$year
				{
					$sql .= ' AND ' . $key . ' = \'' . $value . '\'';
				} elseif (($key == 's.fk_session_place') || ($key == 'f.rowid') || ($key == 's.type_session') || ($key == 's.status')) {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape ( $value ) . '%\'';
				}
			}
		}
		$sql .= " GROUP BY s.rowid, s.fk_soc, s.fk_session_place, s.type_session, s.dated, s.datef,  s.status, dictstatus.intitule , dictstatus.code, s.is_date_res_site, s.is_date_res_trainer, s.date_res_trainer, s.color, s.force_nb_stagiaire, s.nb_stagiaire,s.notes,";
		$sql .= " p.ref_interne, c.intitule, c.ref,c.ref_interne, so.nom, f.rowid";
		$sql .= " ORDER BY " . $sortfield . ' ' . $sortorder;
		if (! empty ( $limit )) {
			$sql .= ' ' . $this->db->plimit ( $limit + 1, $offset );
		}
	
		dol_syslog ( get_class ( $this ) . "::fetch_all_with_task_state sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
	
		if ($resql) {
			$this->lines = array ();
				
			$num = $this->db->num_rows ( $resql );
			$i = 0;
				
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
						
					$line = new AgfSessionLineTask ();
						
					$line->rowid = $obj->rowid;
					$line->socid = $obj->fk_soc;
					$line->socname = $obj->socname;
					$line->trainerrowid = $obj->trainerrowid;
					$line->type_session = $obj->type_session;
					$line->is_date_res_site = $obj->is_date_res_site;
					$line->is_date_res_trainer = $obj->is_date_res_trainer;
					$line->date_res_trainer = $this->db->jdate ( $obj->date_res_trainer );
					$line->fk_session_place = $obj->fk_session_place;
					$line->dated = $this->db->jdate ( $obj->dated );
					$line->datef = $this->db->jdate ( $obj->datef );
					$line->intitule = $obj->intitule;
					$line->ref = $obj->ref;
					$line->training_ref_interne = $obj->trainingrefinterne;
					$line->ref_interne = $obj->ref_interne;
					$line->color = $obj->color;
					$line->nb_stagiaire = $obj->nb_stagiaire;
					$line->force_nb_stagiaire = $obj->force_nb_stagiaire;
					$line->notes = $obj->notes;
					$line->task0 = $obj->task0;
					$line->task1 = $obj->task1;
					$line->task2 = $obj->task2;
					$line->task3 = $obj->task3;
					$line->duree_session = $obj->duree_session;
					$line->intitule_custo = $obj->intitule_custo;
						
					if ($obj->statuslib == $langs->trans ( 'AgfStatusSession_' . $obj->code )) {
						$label = stripslashes ( $obj->statuslib );
					} else {
						$label = $langs->trans ( 'AgfStatusSession_' . $obj->code );
					}
					$line->status_lib = $obj->statuscode . ' - ' . $label;
						
					$this->lines [$i] = $line;
					$i ++;
				}
			}
			$this->db->free ( $resql );
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_all_with_task_state " . $this->error, LOG_ERR );
			return - 1;
		}
	}
	
	/**
	 * Load all objects in memory from database
	 *
	 * @param int $socid socid filter
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all_by_soc($socid, $sortorder, $sortfield, $limit, $offset, $filter = '') {

		global $langs;
		
		$sql = "SELECT DISTINCT s.rowid, s.fk_soc, s.fk_session_place, s.type_session, s.dated, s.datef, s.status, dictstatus.intitule as statuslib, dictstatus.code as statuscode, ";
		$sql .= " s.is_date_res_site, s.is_date_res_trainer, s.date_res_trainer, s.color, ";
		$sql .= " s.force_nb_stagiaire, s.nb_stagiaire,s.notes,";
		$sql .= " c.intitule, c.ref,c.ref_interne as trainingrefinterne,s.nb_subscribe_min,";
		$sql .= " p.ref_interne";
		$sql .= " ,so.nom as socname";
		$sql .= " ,f.rowid as trainerrowid";
		$sql .= " ,s.intitule_custo";
		$sql .= " ,s.duree_session";
		$sql .= " ,s.archive";
		if ($filter ['type_affect'] == 'thirdparty') {
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
			$sql .= " ON c.rowid = s.fk_formation_catalogue";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
			$sql .= " ON p.rowid = s.fk_session_place";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
			$sql .= " ON s.rowid = ss.fk_session_agefodd";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as sa";
			$sql .= " ON s.rowid = sa.fk_agefodd_session";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as so";
			$sql .= " ON so.rowid = s.fk_soc AND s.fk_soc=" . $socid;
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf";
			$sql .= " ON sf.fk_session = s.rowid";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as f";
			$sql .= " ON f.rowid = sf.fk_agefodd_formateur";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_status_type as dictstatus";
			$sql .= " ON s.status = dictstatus.rowid";
			
			$type_affect=$langs->trans('ThirdParty');

		} elseif ($filter ['type_affect'] == 'trainee') {
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
			$sql .= " ON c.rowid = s.fk_formation_catalogue";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
			$sql .= " ON p.rowid = s.fk_session_place";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
			$sql .= " ON s.rowid = ss.fk_session_agefodd";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta";
			$sql .= " ON ss.fk_stagiaire = sta.rowid AND sta.fk_soc=" . $socid;
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as sa";
			$sql .= " ON s.rowid = sa.fk_agefodd_session";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
			$sql .= " ON so.rowid = s.fk_soc";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf";
			$sql .= " ON sf.fk_session = s.rowid";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as f";
			$sql .= " ON f.rowid = sf.fk_agefodd_formateur";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_status_type as dictstatus";
			$sql .= " ON s.status = dictstatus.rowid";
			
			$type_affect=$langs->trans('AgfParticipant');
		} elseif ($filter ['type_affect'] == 'opca') {
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
			$sql .= " ON c.rowid = s.fk_formation_catalogue";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
			$sql .= " ON p.rowid = s.fk_session_place";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
			$sql .= " ON s.rowid = ss.fk_session_agefodd";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as sa";
			$sql .= " ON s.rowid = sa.fk_agefodd_session";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as so";
			$sql .= " ON so.rowid = s.fk_soc";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf";
			$sql .= " ON sf.fk_session = s.rowid";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as f";
			$sql .= " ON f.rowid = sf.fk_agefodd_formateur";
			$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_status_type as dictstatus";
			$sql .= " ON s.status = dictstatus.rowid";
			
					
			$type_affect=$langs->trans('AgfMailTypeContactOPCA');
		}
		$sql .= " WHERE s.entity IN (" . getEntity ( 'agsession' ) . ")";
		
		if ($filter ['type_affect'] == 'opca') {
			$sql .= ' AND (s.rowid IN (SELECT rowid FROM ' . MAIN_DB_PREFIX . 'agefodd_session WHERE is_OPCA=1 AND fk_soc_OPCA='.$socid.')';
			$sql .= ' OR s.rowid IN (SELECT innersess.rowid FROM ' . MAIN_DB_PREFIX . 'agefodd_session as innersess';
			$sql .= '		INNER JOIN ' . MAIN_DB_PREFIX . 'agefodd_opca as opca';
			$sql .= '		ON opca.fk_session_agefodd=innersess.rowid AND opca.is_OPCA=1 AND opca.fk_soc_OPCA='.$socid.'))';
		}
		
		// Manage filter
		if (! empty ( $filter )) {
			foreach ( $filter as $key => $value ) {
				if ($key != 'type_affect') {
					if (strpos ( $key, 'date' )) 					// To allow $filter['YEAR(s.dated)']=>$year
					{
						$sql .= ' AND ' . $key . ' = \'' . $value . '\'';
					} elseif (($key == 's.fk_session_place') || ($key == 'f.rowid') || ($key == 's.type_session') || ($key == 's.status')) {
						$sql .= ' AND ' . $key . ' = ' . $value;
					} else {
						$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape ( $value ) . '%\'';
					}
				}
			}
		}
		$sql .= " ORDER BY " . $sortfield . ' ' . $sortorder;
		if (! empty ( $limit )) {
			$sql .= ' ' . $this->db->plimit ( $limit + 1, $offset );
		}
		
		dol_syslog ( get_class ( $this ) . "::fetch_all_by_soc sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			$this->lines = array ();
			
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					
					$line = new AgfSessionLineSoc ();
					
					$line->rowid = $obj->rowid;
					$line->socid = $obj->fk_soc;
					$line->socname = $obj->socname;
					$line->trainerrowid = $obj->trainerrowid;
					$line->type_session = $obj->type_session;
					$line->is_date_res_site = $obj->is_date_res_site;
					$line->is_date_res_trainer = $obj->is_date_res_trainer;
					$line->date_res_trainer = $this->db->jdate ( $obj->date_res_trainer );
					$line->fk_session_place = $obj->fk_session_place;
					$line->dated = $this->db->jdate ( $obj->dated );
					$line->datef = $this->db->jdate ( $obj->datef );
					$line->intitule = $obj->intitule;
					$line->ref = $obj->ref;
					$line->training_ref_interne = $obj->trainingrefinterne;
					$line->ref_interne = $obj->ref_interne;
					$line->color = $obj->color;
					$line->nb_stagiaire = $obj->nb_stagiaire;
					$line->force_nb_stagiaire = $obj->force_nb_stagiaire;
					$line->notes = $obj->notes;
					$line->nb_subscribe_min = $obj->nb_subscribe_min;
					$line->type_affect = $type_affect;
					$line->archive = $obj->archive;
					$line->duree_session = $obj->duree_session;
					$line->intitule_custo = $obj->intitule_custo;
					
					if ($obj->statuslib == $langs->trans ( 'AgfStatusSession_' . $obj->statuscode )) {
						$label = stripslashes ( $obj->statuslib );
					} else {
						$label = $langs->trans ( 'AgfStatusSession_' . $obj->statuscode );
					}
					$line->status_lib = $label;
					
					$this->lines [$i] = $line;
					$i ++;
				}
			}
			$this->db->free ( $resql );
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_all_by_soc " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Load all objects in memory from database
	 *
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param string $ordernum num linked
	 * @param string $invoicenum num linked
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_all_by_order_invoice_propal($sortorder, $sortfield, $limit, $offset, $orderid = '', $invoiceid = '', $propalid = '') {

		global $langs;
		
		$sql = "SELECT s.rowid, s.fk_soc, s.fk_session_place, s.type_session, s.dated, s.datef, s.is_date_res_site, s.is_date_res_trainer, s.date_res_trainer, s.color, s.force_nb_stagiaire, s.nb_stagiaire,s.notes,";
		$sql .= " c.intitule, c.ref";
		$sql .= " ,s.intitule_custo";
		$sql .= " ,s.duree_session,";
		$sql .= " p.ref_interne";
		if (! empty ( $invoiceid )) {
			$sql .= " ,invoice.facnumber as invoiceref";
		}
		if (! empty ( $orderid )) {
			$sql .= " ,order_dol.ref as orderref";
		}
		if (! empty ( $propalid )) {
			$sql .= " ,propal_dol.ref as propalref";
		}
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as s";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as c";
		$sql .= " ON c.rowid = s.fk_formation_catalogue";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_place as p";
		$sql .= " ON p.rowid = s.fk_session_place";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as ss";
		$sql .= " ON s.rowid = ss.fk_session_agefodd";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as sa";
		$sql .= " ON s.rowid = sa.fk_agefodd_session";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_facture as ord_inv";
		$sql .= " ON s.rowid = ord_inv.fk_session";
		
		if (! empty ( $invoiceid )) {
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "facture as invoice ";
			$sql .= " ON invoice.rowid = ord_inv.fk_facture ";
			$sql .= ' AND invoice.rowid=' . $invoiceid;
		}
		
		if (! empty ( $orderid )) {
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "commande as order_dol ";
			$sql .= " ON order_dol.rowid = ord_inv.fk_commande";
			$sql .= ' AND order_dol.rowid=' . $orderid;
		}
		
		if (! empty ( $propalid )) {
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "propal as propal_dol ";
			$sql .= " ON propal_dol.rowid = ord_inv.fk_propal";
			$sql .= ' AND propal_dol.rowid=' . $propalid;
		}
		$sql .= " WHERE s.entity IN (" . getEntity ( 'agsession' ) . ")";
		
		$sql .= " GROUP BY s.rowid,c.intitule,c.ref,p.ref_interne";
		
		if (! empty ( $invoiceid )) {
			$sql .= " ,invoice.facnumber ";
		}
		
		if (! empty ( $orderid )) {
			$sql .= " ,order_dol.ref ";
		}
		
		if (! empty ( $propalid )) {
			$sql .= " ,propal_dol.ref ";
		}
		
		$sql .= " ORDER BY $sortfield $sortorder " . $this->db->plimit ( $limit + 1, $offset );
		
		dol_syslog ( get_class ( $this ) . "::fetch_all_by_order_invoice_propal sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			$this->line = array ();
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					
					$line = new AgfInvoiceOrder ();
					
					$line->rowid = $obj->rowid;
					$line->socid = $obj->fk_soc;
					$line->type_session = $obj->type_session;
					$line->is_date_res_site = $obj->is_date_res_site;
					$line->is_date_res_trainer = $obj->is_date_res_trainer;
					$line->date_res_trainer = $this->db->jdate ( $obj->date_res_trainer );
					$line->fk_session_place = $obj->fk_session_place;
					$line->dated = $this->db->jdate ( $obj->dated );
					$line->datef = $this->db->jdate ( $obj->datef );
					$line->intitule = $obj->intitule;
					$line->ref = $obj->ref;
					$line->ref_interne = $obj->ref_interne;
					$line->color = $obj->color;
					$line->nb_stagiaire = $obj->nb_stagiaire;
					$line->force_nb_stagiaire = $obj->force_nb_stagiaire;
					$line->duree_session = $obj->duree_session;
					$line->intitule_custo = $obj->intitule_custo;
					$line->notes = $obj->notes;
					if (! empty ( $invoiceid )) {
						$line->invoiceref = $obj->invoiceref;
					}
					if (! empty ( $orderid )) {
						$line->orderref = $obj->orderref;
					}
					if (! empty ( $propalid )) {
						$line->propalref = $obj->propalref;
					}
					
					$this->lines [$i] = $line;
					
					$i ++;
				}
			}
			$this->db->free ( $resql );
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_all_by_order_invoice " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Print table of session information
	 */
	function printSessionInfo() {

		global $form, $langs;
		
		require_once (DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php');
		require_once (DOL_DOCUMENT_ROOT . '/product/class/product.class.php');
		$extrafields = new ExtraFields ( $this->db );
		$extralabels = $extrafields->fetch_name_optionals_label ( $this->table_element );
		
		print '<table class="border" width="100%">';
		
		print '<tr><td width="20%">' . $langs->trans ( "Ref" ) . '</td>';
		print '<td>' . $form->showrefnav ( $this, 'id', '', 1, 'rowid', 'id' ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfFormIntitule" ) . '</td>';
		print '<td><a href="' . dol_buildpath ( '/agefodd/training/card.php', 1 ) . '?id=' . $this->fk_formation_catalogue . '">' . $this->formintitule . '</a></td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfFormIntituleCust" ) . '</td>';
		print '<td><a href="' . dol_buildpath ( '/agefodd/training/card.php', 1 ) . '?id=' . $this->fk_formation_catalogue . '">' . $this->intitule_custo . '</a></td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfFormRef" ) . '</td>';
		print '<td>' . $this->formref . '</td></tr>';
		
		// Type de la session
		print '<tr><td>' . $langs->trans ( "AgfFormTypeSession" ) . '</td>';
		print '<td>' . ($this->type_session ? $langs->trans ( 'AgfFormTypeSessionInter' ) : $langs->trans ( 'AgfFormTypeSessionIntra' )) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfSessionCommercial" ) . '</td>';
		print '<td><a href="' . dol_buildpath ( '/user/fiche.php', 1 ) . '?id=' . $this->commercialid . '">' . $this->commercialname . '</a></td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfDuree" ) . '</td>';
		print '<td>' . $this->duree_session . ' '.$langs->trans('Hour').'(s)</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfProductServiceLinked" ) . '</td>';
		print '<td>';
		if (! empty ( $this->fk_product )) {
			$product = new Product ( $this->db );
			$result = $product->fetch ( $this->fk_product );
			if ($result < 0) {
				setEventMessage ( $product->error, 'errors' );
			}
			print $product->getNomUrl ( 1 ) . ' - ' . $product->label;
		}
		
		print "</td></tr>";
		
		print '<tr><td>' . $langs->trans ( "AgfDateDebut" ) . '</td>';
		print '<td>' . dol_print_date ( $this->dated, 'daytext' ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfDateFin" ) . '</td>';
		print '<td>' . dol_print_date ( $this->datef, 'daytext' ) . '</td></tr>';
		
		print '<tr><td width="20%">' . $langs->trans ( "Customer" ) . '</td>';
		print '	<td>';
		if ((! empty ( $this->fk_soc )) && ($this->fk_soc > 0)) {
			print $this->getElementUrl ( $this->fk_soc, 'societe', 1 );
		}
		print '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfSessionContact" ) . '</td>';
		print '<td><a href="' . dol_buildpath ( '/agefodd/contact/card.php', 1 ) . '?id=' . $this->contactid . '">' . $this->contactname . '</a></td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfLieu" ) . '</td>';
		print '<td><a href="' . dol_buildpath ( '/agefodd/site/card.php', 1 ) . '?id=' . $this->placeid . '">' . $this->placecode . '</a></td></tr>';
		
		print '<tr><td valign="top">' . $langs->trans ( "AgfNote" ) . '</td>';
		if (! empty ( $this->notes ))
			$notes = nl2br ( $this->notes );
		else
			$notes = $langs->trans ( "AgfUndefinedNote" );
		print '<td>' . stripslashes ( $notes ) . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfDateResTrainer" ) . '</td>';
		if ($this->is_date_res_trainer) {
			print '<td>' . dol_print_date ( $this->date_res_trainer, 'daytext' ) . '</td></tr>';
		} else {
			print '<td>' . $langs->trans ( "AgfNoDefined" ) . '</td></tr>';
		}
		
		print '<tr><td>' . $langs->trans ( "AgfDateResSite" ) . '</td>';
		if ($this->is_date_res_site) {
			print '<td>' . dol_print_date ( $this->date_res_site, 'daytext' ) . '</td></tr>';
		} else {
			print '<td>' . $langs->trans ( "AgfNoDefined" ) . '</td></tr>';
		}
		
		print '<tr><td>' . $langs->trans ( "AgfNbMintarget" ) . '</td><td>';
		print $this->nb_subscribe_min . '</td></tr>';
		
		print '<tr><td>' . $langs->trans ( "AgfStatusSession" ) . '</td><td>';
		print $this->statuslib . '</td></tr>';
		
		if (! empty ( $extrafields->attribute_label )) {
			print $this->showOptionals ( $extrafields );
		}
		
		print '</table>';
	}

	/**
	 * Return clicable link of object (with eventually picto)
	 *
	 * @param int $withpicto into link
	 * @param string $option the link
	 * @param int $maxlength ref
	 * @return string with URL
	 */
	function getNomUrl($withpicto = 0, $option = '', $maxlength = 0) {

		global $langs;
		
		$result = '';
		
		if (! $option) {
			$lien = '<a href="' . dol_buildpath ( '/agefodd/session/card.php', 1 ) . '?id=' . $this->id . '">';
			$lienfin = '</a>';
		}
		$newref = $this->formintitule;
		if ($maxlength)
			$newref = dol_trunc ( $newref, $maxlength, 'middle' );
		
		if ($withpicto) {
			$result .= ($lien . img_object ( $langs->trans ( "ShowSession" ) . ' ' . $this->ref, 'agefodd@agefodd' ) . $lienfin . ' ');
		}
		$result .= $lien . $newref . $lienfin;
		return $result;
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id_trainee trainee in session
	 * @param int $id_session session
	 * @return int <0 if KO, >0 if OK (rowid)
	 */
	function getOpcaForTraineeInSession($fk_soc_trainee, $id_session) {

		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.fk_soc_trainee,";
		$sql .= " t.fk_session_agefodd,";
		$sql .= " t.date_ask_OPCA as date_ask_opca,";
		$sql .= " t.is_date_ask_OPCA as is_date_ask_opca,";
		$sql .= " t.is_OPCA as is_opca,";
		$sql .= " t.fk_soc_OPCA as fk_soc_opca,";
		$sql .= " t.fk_socpeople_OPCA as fk_socpeople_opca,";
		$sql .= " concactOPCA.lastname as concact_opca_name, concactOPCA.firstname as concact_opca_firstname,";
		$sql .= " t.num_OPCA_soc as num_opca_soc,";
		$sql .= " t.num_OPCA_file as num_opca_file,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms";
		
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_opca as t";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "socpeople as concactOPCA ";
		$sql .= " ON t.fk_socpeople_OPCA = concactOPCA.rowid";
		
		$sql .= " WHERE t.fk_soc_trainee = " . $fk_soc_trainee;
		$sql .= " AND t.fk_session_agefodd = " . $id_session;
		
		dol_syslog ( get_class ( $this ) . "::getOpcaForTraineeInSession sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			if ($this->db->num_rows ( $resql )) {
				$obj = $this->db->fetch_object ( $resql );
				
				$this->opca_rowid = $obj->rowid;
				$this->fk_soc_trainee = $obj->fk_soc_trainee;
				$this->fk_session_agefodd = $obj->fk_session_agefodd;
				$this->date_ask_OPCA = $this->db->jdate ( $obj->date_ask_opca );
				$this->is_date_ask_OPCA = $obj->is_date_ask_opca;
				$this->is_OPCA = $obj->is_opca;
				$this->fk_soc_OPCA = $obj->fk_soc_opca;
				$this->fk_socpeople_OPCA = $obj->fk_socpeople_opca;
				$this->num_OPCA_soc = $obj->num_opca_soc;
				$this->num_OPCA_file = $obj->num_opca_file;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate ( $obj->datec );
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate ( $obj->tms );
				
				$this->soc_OPCA_name = $this->getValueFrom ( 'societe', $this->fk_soc_OPCA, 'nom' );
				$this->contact_name_OPCA = $obj->concact_opca_name . ' ' . $obj->concact_opca_firstname;
			} else {
				$this->opca_rowid = '';
				$this->fk_soc_trainee = '';
				$this->fk_session_agefodd = '';
				$this->date_ask_OPCA = '';
				$this->is_date_ask_OPCA = 0;
				$this->is_OPCA = 0;
				$this->fk_soc_OPCA = '';
				$this->fk_socpeople_OPCA = '';
				$this->num_OPCA_soc = '';
				$this->num_OPCA_file = '';
				$this->fk_user_author = '';
				$this->datec = '';
				$this->fk_user_mod = '';
				$this->tms = '';
				$this->soc_OPCA_name = '';
				$this->contact_name_OPCA = '';
			}
			$this->db->free ( $resql );
			
			return $this->opca_rowid;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * Create line into database about OPCA infos
	 *
	 * @param User $user that create
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	function saveInfosOpca($user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		if (isset ( $this->fk_soc_trainee ))
			$this->fk_soc_trainee = trim ( $this->fk_soc_trainee );
		if (isset ( $this->fk_session_agefodd ))
			$this->fk_session_agefodd = trim ( $this->fk_session_agefodd );
		if (isset ( $this->date_ask_OPCA ))
			$this->date_ask_OPCA = trim ( $this->date_ask_OPCA );
		if (isset ( $this->is_date_ask_OPCA ))
			$this->is_date_ask_OPCA = trim ( $this->is_date_ask_OPCA );
		if (isset ( $this->is_OPCA ))
			$this->is_OPCA = trim ( $this->is_OPCA );
		if (isset ( $this->fk_soc_OPCA ))
			$this->fk_soc_OPCA = trim ( $this->fk_soc_OPCA );
		if (isset ( $this->fk_socpeople_OPCA ))
			$this->fk_socpeople_OPCA = trim ( $this->fk_socpeople_OPCA );
		if (isset ( $this->num_OPCA_soc ))
			$this->num_OPCA_soc = trim ( $this->num_OPCA_soc );
		if (isset ( $this->num_OPCA_file ))
			$this->num_OPCA_file = trim ( $this->num_OPCA_file );
			
			// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_opca(";
		
		$sql .= "fk_soc_trainee,";
		$sql .= "fk_session_agefodd,";
		$sql .= "date_ask_OPCA,";
		$sql .= "is_date_ask_OPCA,";
		$sql .= "is_OPCA,";
		$sql .= "fk_soc_OPCA,";
		$sql .= "fk_socpeople_OPCA,";
		$sql .= "num_OPCA_soc,";
		$sql .= "num_OPCA_file,";
		$sql .= "fk_user_author,";
		$sql .= "datec,";
		$sql .= "fk_user_mod";
		
		$sql .= ") VALUES (";
		
		$sql .= " " . (! isset ( $this->fk_soc_trainee ) ? 'NULL' : "'" . $this->fk_soc_trainee . "'") . ",";
		$sql .= " " . (! isset ( $this->fk_session_agefodd ) ? 'NULL' : "'" . $this->fk_session_agefodd . "'") . ",";
		$sql .= " " . (! isset ( $this->date_ask_OPCA ) || dol_strlen ( $this->date_ask_OPCA ) == 0 ? 'NULL' : "'" . $this->db->idate ( $this->date_ask_OPCA ) . "'") . ",";
		$sql .= " " . (! isset ( $this->is_date_ask_OPCA ) ? 'NULL' : "'" . $this->is_date_ask_OPCA . "'") . ",";
		$sql .= " " . (! isset ( $this->is_OPCA ) ? 'NULL' : "'" . $this->is_OPCA . "'") . ",";
		$sql .= " " . (! isset ( $this->fk_soc_OPCA ) ? 'NULL' : "'" . $this->fk_soc_OPCA . "'") . ",";
		$sql .= " " . (! isset ( $this->fk_socpeople_OPCA ) ? 'NULL' : "'" . $this->fk_socpeople_OPCA . "'") . ",";
		$sql .= " " . (! isset ( $this->num_OPCA_soc ) ? 'NULL' : "'" . $this->db->escape ( $this->num_OPCA_soc ) . "'") . ",";
		$sql .= " " . (! isset ( $this->num_OPCA_file ) ? 'NULL' : "'" . $this->db->escape ( $this->num_OPCA_file ) . "'") . ",";
		$sql .= " " . $user->id . ",";
		$sql .= " '" . $this->db->idate ( dol_now () ) . "',";
		$sql .= " " . $user->id . "";
		
		$sql .= ")";
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::saveInfosOpca sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		if (! $error) {
			$this->id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "agefodd_opca" );
			
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.
				
				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
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
	 * Update OPCA info into database for the thirparty of trainee in agefodd session
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function updateInfosOpca($user = 0, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset ( $this->fk_soc_trainee ))
			$this->fk_soc_trainee = trim ( $this->fk_soc_trainee );
		if (isset ( $this->fk_session_agefodd ))
			$this->fk_session_agefodd = trim ( $this->fk_session_agefodd );
		if (isset ( $this->is_date_ask_OPCA ))
			$this->is_date_ask_OPCA = trim ( $this->is_date_ask_OPCA );
		if (isset ( $this->is_OPCA ))
			$this->is_OPCA = trim ( $this->is_OPCA );
		if (isset ( $this->fk_soc_OPCA ))
			$this->fk_soc_OPCA = trim ( $this->fk_soc_OPCA );
		if (isset ( $this->fk_socpeople_OPCA ))
			$this->fk_socpeople_OPCA = trim ( $this->fk_socpeople_OPCA );
		if (isset ( $this->num_OPCA_soc ))
			$this->num_OPCA_soc = trim ( $this->num_OPCA_soc );
		if (isset ( $this->num_OPCA_file ))
			$this->num_OPCA_file = trim ( $this->num_OPCA_file );
			
			// Check parameters
			// Put here code to add control on parameters values
			
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_opca SET";
		
		$sql .= " fk_soc_trainee=" . (isset ( $this->fk_soc_trainee ) ? $this->fk_soc_trainee : "null") . ",";
		$sql .= " fk_session_agefodd=" . (isset ( $this->fk_session_agefodd ) ? $this->fk_session_agefodd : "null") . ",";
		$sql .= " date_ask_OPCA=" . (dol_strlen ( $this->date_ask_OPCA ) != 0 ? "'" . $this->db->idate ( $this->date_ask_OPCA ) . "'" : 'null') . ",";
		$sql .= " is_date_ask_OPCA=" . (isset ( $this->is_date_ask_OPCA ) ? "'" . $this->db->escape ( $this->is_date_ask_OPCA ) . "'" : "null") . ",";
		$sql .= " is_OPCA=" . (isset ( $this->is_OPCA ) ? "'" . $this->db->escape ( $this->is_OPCA ) . "'" : "null") . ",";
		$sql .= " fk_soc_OPCA=" . (isset ( $this->fk_soc_OPCA ) ? "'" . $this->db->escape ( $this->fk_soc_OPCA ) . "'" : "null") . ",";
		$sql .= " fk_socpeople_OPCA=" . (isset ( $this->fk_socpeople_OPCA ) ? "'" . $this->db->escape ( $this->fk_socpeople_OPCA ) . "'" : "null") . ",";
		$sql .= " num_OPCA_soc=" . (isset ( $this->num_OPCA_soc ) ? "'" . $this->db->escape ( $this->num_OPCA_soc ) . "'" : "null") . ",";
		$sql .= " num_OPCA_file=" . (isset ( $this->num_OPCA_file ) ? "'" . $this->db->escape ( $this->num_OPCA_file ) . "'" : "null") . ",";
		$sql .= " fk_user_mod=" . $user->id . "";
		
		$sql .= " WHERE rowid=" . $this->id_opca_trainee;
		
		$this->db->begin ();
		
		dol_syslog ( get_class ( $this ) . "::updateInfosOpca sql=" . $sql, LOG_DEBUG );
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
				// include_once(DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::updateInfosOpca " . $errmsg, LOG_ERR );
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
	 * Set archive flag to 1 to session according to selected year
	 *
	 * @param int $year year
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	function updateArchiveByYear($year, $user, $notrigger = 0) {

		global $conf, $langs;
		$error = 0;
		
		// Check parameters
		if (! isset ( $year )) {
			$error ++;
			$this->errors [] = "Error " . $langs->trans ( 'ErrorParameterMustBeProvided', 'year' );
		}
		
		// Update request
		if (! $error) {
			$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session SET";
			$sql .= " archive='1',";
			$sql .= " fk_user_mod=" . $user->id . " ";
			$sql .= " WHERE YEAR(dated)='" . $year . "'";
			
			$this->db->begin ();
			
			dol_syslog ( get_class ( $this ) . "::update sql=" . $sql, LOG_DEBUG );
			$resql = $this->db->query ( $sql );
			if (! $resql) {
				$error ++;
				$this->errors [] = "Error " . $this->db->lasterror ();
			}
			if (! $error) {
				if (! $notrigger) {
					// // Call triggers
					// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
					// $interface=new Interfaces($this->db);
					// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
					// if ($result < 0) { $error++; $this->errors=$interface->errors; }
					// // End call triggers
				}
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
	 * Create order from session
	 *
	 * @param User $user that modify
	 * @param int $socid id
	 * @param int $frompropalid from proposal
	 *       
	 * @return int <0 if KO, >0 if OK
	 */
	function createOrder($user, $socid, $frompropalid = 0) {

		require_once (DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php');
		require_once (DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
		require_once (DOL_DOCUMENT_ROOT . '/product/class/product.class.php');
		require_once ('agefodd_facture.class.php');
		require_once ('agefodd_session_stagiaire.class.php');
		
		global $langs, $mysoc, $conf;
		
		$order = new Commande ( $this->db );
		
		// Create order from proposal
		if (! empty ( $frompropalid )) {
			require_once (DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php');
			
			// Find proposal
			$propal = new Propal ( $this->db );
			$result = $propal->fetch ( $frompropalid );
			if ($result < 0 || empty ( $propal->id )) {
				$this->error = $propal->error;
				return - 1;
			} elseif ($propal->statut != 2) {
				$this->error = $langs->trans ( 'AgfProposalMustBeSignToCreateOrderFrom' );
				return - 1;
			} else {
				$neworderid = $order->createFromProposal ( $propal );
				if ($neworderid < 0) {
					$this->error = $order->error;
					return - 1;
				}
			}
		} else {
			// Define new order from scratch
			$soc = new Societe ( $this->db );
			$result = $soc->fetch ( $socid );
			if ($result < 0 || empty ( $soc->id )) {
				$this->error = $soc->error;
				return - 1;
			}
			
			$order->client = $soc;
			
			$order->socid = $socid;
			$order->date_commande = dol_now ();
			$order->modelpdf = $conf->global->COMMANDE_ADDON_PDF;
			
			if (! empty ( $this->fk_product )) {
				
				$product = new Product ( $this->db );
				$result = $product->fetch ( $this->fk_product );
				if ($result < 0 || empty ( $product->id )) {
					$this->error = $product->error;
					return - 1;
				}
				
				$order->lines [0] = new OrderLine ( $db );
				$order->lines [0]->fk_product = $this->fk_product;
				$order->lines [0]->qty = 1;
				
				$desc = $this->formintitule . "\n" . dol_print_date ( $this->dated, 'daytext' ) . '-' . dol_print_date ( $this->datef, 'daytext' );
				$session_trainee = new Agefodd_session_stagiaire ( $this->db );
				$session_trainee->fetch_stagiaire_per_session ( $this->id, $socid );
				$desc .= "\n" . count ( $session_trainee->lines ) . ' ' . $langs->trans ( 'AgfParticipant' ) . '/' . $langs->trans ( 'AgfParticipants' );
				$order->lines [0]->desc = $desc;
				
				// Calculate price
				$tva_tx = get_default_tva ( $mysoc, $order->client, $product->id );
				
				// multiprix
				if (! empty ( $conf->global->PRODUIT_MULTIPRICES ) && ! empty ( $order->client->price_level )) {
					$pu_ht = $prod->multiprices [$order->client->price_level];
					$pu_ttc = $prod->multiprices_ttc [$order->client->price_level];
					$price_min = $prod->multiprices_min [$order->client->price_level];
					$price_base_type = $prod->multiprices_base_type [$order->client->price_level];
				} else {
					$pu_ht = $product->price;
					$pu_ttc = $product->price_ttc;
					$price_min = $product->price_min;
					$price_base_type = $product->price_base_type;
				}
				
				$order->lines [0]->subprice = $pu_ht;
				$order->lines [0]->tva_tx = $tva_tx;
			}
			
			$neworderid = $order->create ( $user );
			if ($neworderid < 0) {
				$this->error = $order->error;
				return - 1;
			}
		}
		
		if (! empty ( $neworderid )) {
			// Link new order to the session/thridparty
			$agf = new Agefodd_facture ( $this->db );
			$result = $agf->fetch ( $this->id, $socid );
			
			$agf->comid = $neworderid;
			
			// Already exists
			if ($agf->id) {
				$result2 = $agf->update ( $user );
			} 			// else create
			else {
				$agf->sessid = $this->id;
				$agf->socid = $socid;
				$result2 = $agf->create ( $user );
				if ($result2 < 0) {
					$this->error = $agf->error;
					return - 1;
				}
			}
			
			return 1;
		} else {
			$this->error = $order->error;
			return - 1;
		}
	}

	/**
	 * Create order from session
	 *
	 * @param User $user that modify
	 * @param int $socid id
	 *       
	 * @return int <0 if KO, >0 if OK
	 */
	function createProposal($user, $socid) {

		require_once (DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php');
		require_once (DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php');
		require_once (DOL_DOCUMENT_ROOT . '/product/class/product.class.php');
		require_once ('agefodd_facture.class.php');
		require_once ('agefodd_session_stagiaire.class.php');
		
		global $langs, $mysoc, $conf;
		
		// Define new propal
		$propal = new Propal ( $this->db );
		
		$soc = new Societe ( $this->db );
		$result = $soc->fetch ( $socid );
		if ($result < 0 || empty ( $soc->id )) {
			$this->error = $soc->error;
			return - 1;
		}
		
		$propal->client = $soc;
		
		$propal->socid = $socid;
		$propal->date = dol_now ();
		if (! empty ( $soc->cond_reglement_id )) {
			$propal->cond_reglement_id = $soc->cond_reglement_id;
		} else {
			$propal->cond_reglement_id = 1;
		}
		if (! empty ( $soc->mode_reglement_id )) {
			$propal->mode_reglement_id = $soc->mode_reglement_id;
		} else {
			$propal->mode_reglement_id = 1;
		}
		$propal->duree_validite = $conf->global->PROPALE_VALIDITY_DURATION;
		$propal->modelpdf = $conf->global->PROPALE_ADDON_PDF;
		
		if (! empty ( $this->fk_product )) {
			
			$product = new Product ( $this->db );
			$result = $product->fetch ( $this->fk_product );
			if ($result < 0 || empty ( $product->id )) {
				$this->error = $product->error;
				return - 1;
			}
			
			$propal->lines [0] = new PropaleLigne ( $db );
			$propal->lines [0]->fk_product = $this->fk_product;
			$propal->lines [0]->qty = 1;
			
			$desc = $this->formintitule . "\n" . dol_print_date ( $this->dated, 'daytext' ) . '-' . dol_print_date ( $this->datef, 'daytext' );
			$session_trainee = new Agefodd_session_stagiaire ( $this->db );
			$session_trainee->fetch_stagiaire_per_session ( $this->id, $socid );
			$desc .= "\n" . count ( $session_trainee->lines ) . ' ';
			if (count ( $session_trainee->lines ) >= 1) {
				$desc .= $langs->trans ( 'AgfParticipant' );
			} else {
				$desc .= $langs->trans ( 'AgfParticipants' );
			}
			if ($conf->global->AGF_ADD_TRAINEE_NAME_INTO_DOCPROPODR) {
				foreach ( $session_trainee->lines as $line ) {
					$desc .= "\n" . dol_strtoupper ( $line->nom ) . ' ' . $line->prenom . "\n";
				}
			}
			$propal->lines [0]->desc = $desc;
			
			// Calculate price
			$tva_tx = get_default_tva ( $mysoc, $propal->client, $product->id );
			
			// multiprix
			if (! empty ( $conf->global->PRODUIT_MULTIPRICES ) && ! empty ( $propal->client->price_level )) {
				$pu_ht = $product->multiprices [$propal->client->price_level];
				$pu_ttc = $product->multiprices_ttc [$propal->client->price_level];
				$price_min = $product->multiprices_min [$propal->client->price_level];
				$price_base_type = $product->multiprices_base_type [$propal->client->price_level];
			} else {
				$pu_ht = $product->price;
				$pu_ttc = $product->price_ttc;
				$price_min = $product->price_min;
				$price_base_type = $product->price_base_type;
			}
			
			$propal->lines [0]->subprice = $pu_ht;
			$propal->lines [0]->tva_tx = $tva_tx;
		}
		
		$newpropalid = $propal->create ( $user );
		if ($newpropalid < 0) {
			$this->error = $propal->error;
			return - 1;
		} else {
			
			// Link new order to the session/thridparty
			$agf = new Agefodd_facture ( $this->db );
			$result = $agf->fetch ( $this->id, $socid );
			
			$agf->propalid = $newpropalid;
			
			// Already exists
			if ($agf->id) {
				$result2 = $agf->update ( $user );
			} 			// else create
			else {
				$agf->sessid = $this->id;
				$agf->socid = $socid;
				$result2 = $agf->create ( $user );
				if ($result2 < 0) {
					$this->error = $agf->error;
					return - 1;
				}
			}
			
			return 1;
		}
	}
}

/**
 * Session Thridparty Link Class
 */
class AgfSocLine {
	var $sessid;
	var $socname;
	var $socid;
	var $type_session;
	var $is_OPCA;
	var $fk_soc_OPCA;
	var $code_client;

	function __construct() {

		return 1;
	}
}

/**
 * Session Invoice Order Link Class
 */
class AgfInvoiceOrder {
	var $rowid;
	var $socid;
	var $type_session;
	var $is_date_res_site;
	var $is_date_res_trainer;
	var $date_res_trainer;
	var $fk_session_place;
	var $dated;
	var $datef;
	var $intitule;
	var $ref;
	var $ref_interne;
	var $color;
	var $nb_stagiaire;
	var $force_nb_stagiaire;
	var $notes;
	var $invoiceref;
	var $orderref;
	var $propalref;
	var $duree_session;
	var $intitule_custom;

	function __construct() {

		return 1;
	}
}

/**
 * Session line Class
 */
class AgfSessionLine {
	var $rowid;
	var $socid;
	var $socname;
	var $trainerrowid;
	var $type_session;
	var $is_date_res_site;
	var $is_date_res_trainer;
	var $date_res_trainer;
	var $fk_session_place;
	var $dated;
	var $datef;
	var $intitule;
	var $intitule_custom;
	var $ref;
	var $ref_interne;
	var $color;
	var $nb_stagiaire;
	var $force_nb_stagiaire;
	var $notes;
	var $nb_subscribe_min;
	var $nb_prospect;
	var $nb_confirm;
	var $nb_cancelled;
	var $statuslib;
	var $statuscode;
	var $status_in_session;
	var $realdurationsession;
	var $duree_session;

	function __construct() {

		return 1;
	}
}

/**
 * Session line Class
 */
class AgfSessionLineTask {
	var $rowid;
	var $socid;
	var $socname;
	var $trainerrowid;
	var $type_session;
	var $is_date_res_site;
	var $is_date_res_trainer;
	var $date_res_trainer;
	var $fk_session_place;
	var $dated;
	var $datef;
	var $intitule;
	var $intitule_custom;
	var $ref;
	var $ref_interne;
	var $color;
	var $nb_stagiaire;
	var $force_nb_stagiaire;
	var $notes;
	var $task0;
	var $task1;
	var $task2;
	var $task3;
	var $statuslib;
	var $statuscode;
	var $status_in_session;
	var $realdurationsession;
	var $duree_session;

	function __construct() {

		return 1;
	}
}

/**
 * Session line Class for list by soc
 */
class AgfSessionLineSoc {
	var $rowid;
	var $socid;
	var $socname;
	var $trainerrowid;
	var $type_session;
	var $is_date_res_site;
	var $is_date_res_trainer;
	var $date_res_trainer;
	var $fk_session_place;
	var $dated;
	var $datef;
	var $intitule;
	var $ref;
	var $ref_interne;
	var $color;
	var $nb_stagiaire;
	var $force_nb_stagiaire;
	var $notes;
	var $nb_subscribe_min;
	var $type_affect;
	var $statuslib;
	var $statuscode;
	var $status_in_session;
	var $active;
	var $duree_session;
	var $intitule_custom;

	function __construct() {

		return 1;
	}
}