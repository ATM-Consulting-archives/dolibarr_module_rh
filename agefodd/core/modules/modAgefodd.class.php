<?php
/** Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2012	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012       Florian Henry   <florian.henry@open-concept.pro>
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
 *	\defgroup   	agefodd     Module AGeFoDD (Assistant de GEstion de la FOrmation Dans Dolibarr)
 *	\brief      	agefodd module descriptor.
 *	\file       	/core/modules/modAgefodd.class.php
 *	\ingroup    	agefodd
 *	\brief      	Description and activation file for module agefodd
 */
include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 *	\class      modAgefodd
 *	\brief      Description and activation class for module agefodd
 */
class modAgefodd extends DolibarrModules
{
	
	var $error;
	/**
	 *	Constructor.
	 *	@param	DoliDB		Database handler
	 */
	function __construct($db)
	{
		global $conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 103000;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'agefodd';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		//It is used to group modules in module setup page
		$this->family = "hr";
		// Module label, used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Trainning Management Assistant Module";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '2.1.7';

		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/images directory, use this->picto=DOL_URL_ROOT.'/module/images/file.png'
		$this->picto='agefodd@agefodd';

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array("/agefodd");
		$r=0;

		// Relative path to module style sheet if exists. Example: '/mymodule/mycss.css'.
		$this->style_sheet = '/agefodd/css/agefodd.css';

		// Config pages. Put here list of php page names stored in admin directory used to setup module.
		$this->config_page_url = array("admin_agefodd.php@agefodd");

		//define triggers
		$this->module_parts = array('triggers' => 1);

		// Dependencies
		$this->depends = array('modSociete', 'modPropale', 'modCommande', 'modComptabilite', 'modFacture', 'modBanque', 'modFournisseur', 'modService', 'modAgenda');		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(4,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,4);	// Minimum version of Dolibarr required by module
		$this->langfiles = array('agefodd@agefodd');

		// Constants
		$this->const = array();			// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 0 or 'allentities')
		$r=0;

		$r++;
		$this->const[$r][0] = "AGF_USE_STAGIAIRE_TYPE";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Use trainee type';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_DEFAULT_STAGIAIRE_TYPE";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '2';
		$this->const[$r][3] = 'Type of  trainee funding';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_UNIVERSAL_MASK";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Mask of training number ref';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_ADDON";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = 'mod_agefodd_simple';
		$this->const[$r][3] = 'Use simple mask for training ref';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_ORGANISME_PREF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Prefecture d\'enregistrement';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_ORGANISME_NUM";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Numerot d\'enregistrement a la prefecture';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_ORGANISME_REPRESENTANT";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Representant de la societé de formation';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_TRAINING_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Search Training with combobox';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_TRAINER_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Search Trainer with combobox';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_TRAINEE_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Search Trainee with combobox';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_SITE_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Search site with combobox';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_STAGTYPE_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Search stagiaire type with combobox';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_CONTACT_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Search contact with combobox';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_CONTACT_DOL_SESSION";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Use dolibarr or agefodd contact for session';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_LAST_VERION_INSTALL";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = $this->version;
		$this->const[$r][3] = 'Last version installed to know change table to execute';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$this->const[$r][6] = 0;

		$r++;
		$this->const[$r][0] = "AGF_DOL_AGENDA";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Create Event in Dolibarr Agenda';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;		
		$this->const[$r][0] = "AGF_USE_FAC_WITHOUT_ORDER";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Can link invocie without order to session';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_LINK_OPCA_ADRR_TO_CONTACT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Display OPCA adress from OPCA contact rather than OPCA';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_TEXT_COLOR";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '000000';
		$this->const[$r][3] = 'Text color of PDF in hexadecimal';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_HEAD_COLOR";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = 'CB4619';
		$this->const[$r][3] = 'Text color header in hexadecimal';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_FOOT_COLOR";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = 'BEBEBE';
		$this->const[$r][3] = 'Text color of PDF footer, in hexadccimal';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_MANAGE_CERTIF";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Manage certification';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_DEFAULT_CREATE_CERTIF";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Whenn Add a trainee defaut create sertificate';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_FCKEDITOR_ENABLE_TRAINING";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Use WISIWYG on training information';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_MANAGE_OPCA";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Manage Opca';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_CERTIF_ADDON";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = 'mod_agefoddcertif_simple';
		$this->const[$r][3] = 'Use simple mask for certif ref';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_CERTIF_UNIVERSAL_MASK";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Mask of certificate code';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_SESSION_TRAINEE_STATUS_AUTO";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Manage subcription status by propal/order status';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		$r++;
		$this->const[$r][0] = "AGF_DOL_TRAINER_AGENDA";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Manage time by session for trainer';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_DEFAULT_SESSION_STATUS";
		$this->const[$r][1] = "1";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Defaut status session';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_ADD_TRAINEE_NAME_INTO_DOCPROPODR";
		$this->const[$r][1] = "0";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Add trainnee name when create order/proposal';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_MANAGE_CURSUS";
		$this->const[$r][1] = "1";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'MAnage cursus';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		
		$r++;
		$this->const[$r][0] = "AGF_CURSUS_USE_SEARCH_TO_SELECT";
		$this->const[$r][1] = "1";
		$this->const[$r][2] = '';
		$this->const[$r][3] = 'Display combobox for cursus select';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;

		//Setup $conf environement Dolibarr variable
		if (! isset($conf->agefodd->enabled)) {
			$conf->agefodd = (object) array();
			$conf->agefodd->enabled=0; // This is to avoid warnings
		}
		
		// Dictionnaries
		$this->dictionnaries=array(
		'langs'=>'agefodd@agefodd',
		'tabname'=>array(MAIN_DB_PREFIX."agefodd_stagiaire_type", MAIN_DB_PREFIX."agefodd_certificate_type", MAIN_DB_PREFIX."agefodd_formation_catalogue_type"),		// List of tables we want to see into dictonnary editor
		'tablib'=>array("AgfTraineeType","AgfCertificateType", "AgfTrainingCategTbl"),								// Label of tables
		'tabsql'=>array('SELECT f.rowid as rowid, f.intitule, f.sort, f.active FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire_type as f',
		'SELECT f.rowid as rowid, f.intitule, f.sort, f.active FROM '.MAIN_DB_PREFIX.'agefodd_certificate_type as f',
		'SELECT f.rowid as rowid, f.code, f.intitule, f.sort, f.active FROM '.MAIN_DB_PREFIX.'agefodd_formation_catalogue_type as f',
		),	// Request to select fields
		'tabsqlsort'=>array('sort ASC','sort ASC','sort ASC'),					// Sort order
		'tabfield'=>array("intitule,sort","intitule,sort","code,intitule,sort"),						// List of fields (result of select to show dictionnary)
		'tabfieldvalue'=>array("intitule,sort","intitule,sort","code,intitule,sort"),				// List of fields (list of fields to edit a record)
		'tabfieldinsert'=>array("intitule,sort","intitule,sort","code,intitule,sort"),				// List of fields (list of fields for insert)
		'tabrowid'=>array("rowid","rowid","rowid"),										// Name of columns with primary key (try to always name it 'rowid')
		'tabcond'=>array('$conf->agefodd->enabled','$conf->agefodd->enabled','$conf->agefodd->enabled')	// Condition to show each dictionnary
		);


		// Import list of trainee
		$r=0;
		$r++;
		$this->import_code[$r]=$this->rights_class.'_'.$r;
		$this->import_label[$r]='ImportDataset_trainee';
		$this->import_icon[$r]='contact';
		$this->import_entities_array[$r]=array('s.fk_soc'=>'company','s.nom'=>'AgfNbreParticipants','s.prenom'=>'AgfNbreParticipants','s.civilite'=>'AgfNbreParticipants',
		's.tel1'=>'AgfNbreParticipants','s.tel2'=>'AgfNbreParticipants','s.mail'=>'AgfNbreParticipants',
		's.date_birth'=>'AgfNbreParticipants','s.place_birth'=>'AgfNbreParticipants','s.datec'=>'AgfNbreParticipants');	// We define here only fields that use another icon that the one defined into import_icon
		$this->import_tables_array[$r]=array('s'=>MAIN_DB_PREFIX.'agefodd_stagiaire');	// List of tables to insert into (insert done in same order)
		$this->import_fields_array[$r]=array('s.fk_soc'=>'ThirdPartyName*','s.nom'=>'AgfFamilyName','s.prenom'=>'AgfFirstName','s.civilite'=>'AgfTitle',
		's.tel1'=>'AgfTelephone1','s.tel2'=>'AgfTelephone2','s.mail'=>'AgfPDFFicheEvalEmailTrainee',
		's.date_birth'=>'DateToBirth','s.place_birth'=>'AgfPlaceBirth','s.datec'=>'AgfDateC');
		$this->import_fieldshidden_array[$r]=array('s.fk_user_author'=>'user->id','s.fk_user_mod'=>'user->id');    // aliastable.field => ('user->id' or 'lastrowid-'.tableparent)
		$this->import_convertvalue_array[$r]=array('s.fk_soc'=>array('rule'=>'fetchidfromref','file'=>'/societe/class/societe.class.php','class'=>'Societe','method'=>'fetch','element'=>'ThirdParty'));
		$this->import_regex_array[$r]=array('s.date_birth'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$','s.datec'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$');
		$this->import_examplevalues_array[$r]=array('s.fk_soc'=>'MyBigCompany','s.nom'=>'Huppelepup','s.prenom'=>'Jantje','s.civilite'=>'MR',
		's.tel1'=>'1234567890','s.tel2'=>'0987654321','s.mail'=>'Jantje@tks.nl',
		's.date_birth'=>'2013-11-12','s.place_birth'=>'Almelo','s.datec'=>'1998-11-06');

		// Import certificate
		$r++;
		$this->import_code[$r]=$this->rights_class.'_'.$r;
		$this->import_label[$r]='ImportDataset_agefoddcertificate';
		$this->import_icon[$r]='contact';
		$this->import_entities_array[$r]=array('s.fk_session_agefodd'=>'AgefoddMenuAction','s.fk_stagiaire'=>'AgfNbreParticipants','s.fk_agefodd_stagiaire_type'=>'AgfNbreParticipants',
		's.datec'=>'AgfNbreParticipants','certif.fk_stagiaire'=>'AgfNbreParticipants','certif.fk_session_agefodd'=>'AgefoddMenuAction',
		'certif.certif_code'=>'AgfCertificate','certif.certif_label'=>'AgfCertificate','certif.certif_dt_start'=>'AgfCertificate','certif.certif_dt_end'=>'AgfCertificate',
		'certif.datec'=>'AgfCertificate'
		);	// We define here only fields that use another icon that the one defined into import_icon
		$this->import_tables_array[$r]=array('s'=>MAIN_DB_PREFIX.'agefodd_session_stagiaire','certif'=>MAIN_DB_PREFIX.'agefodd_stagiaire_certif');	// List of tables to insert into (insert done in same order)
		$this->import_fields_array[$r]=array('s.fk_session_agefodd'=>'Id*','s.fk_stagiaire'=>'Id*','s.fk_agefodd_stagiaire_type'=>"AgfTraineeType",
		's.datec'=>'DateCreation',
		'certif.fk_stagiaire'=>'Id*','certif.fk_session_agefodd'=>'Id*',
		'certif.certif_code'=>'AgfCertifCode','certif.certif_label'=>'AgfCertifLabel','certif.certif_dt_start'=>'AgfCertifDateSt','certif.certif_dt_end'=>'AgfCertifDateEnd',
		'certif.datec'=>"DateCreation");

		$this->import_fieldshidden_array[$r]=array('s.fk_user_author'=>'user->id','s.fk_user_mod'=>'user->id',
		'certif.fk_user_author'=>'user->id','certif.fk_user_mod'=>'user->id',
		'certif.fk_session_stagiaire'=>'lastrowid-'.MAIN_DB_PREFIX.'agefodd_session_stagiaire');    // aliastable.field => ('user->id' or 'lastrowid-'.tableparent)
		$this->import_convertvalue_array[$r]=array();
		//$this->import_convertvalue_array[$r]=array('s.fk_soc'=>array('rule'=>'lastrowid',table='t');
		$this->import_regex_array[$r]=array('certif.datec'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$',
		's.datec'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$',
		'certif.certif_dt_start'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$',
		'certif.certif_dt_end'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$');
		$this->import_examplevalues_array[$r]=array(
		's.fk_session_agefodd'=>'999999','s.fk_stagiaire'=>'1','s.fk_agefodd_stagiaire_type'=>$conf->global->AGF_DEFAULT_STAGIAIRE_TYPE,
		's.datec'=>'2013-11-12','çertif.fk_stagiaire'=>'1','certif.fk_session_agefodd'=>'999999',
		'certif.certif_code'=>'CertifCode','certif.certif_label'=>'CertifLabel','certif.certif_dt_start'=>'2013-11-12','certif.certif_dt_end'=>'2013-11-12',
		'certif.datec'=>"2013-11-12"
		);

		//Trainee export
		$r=0;
		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='ExportDataset_trainee';
		$this->export_icon[$r]='contact';
		$this->export_permission[$r]=array(array("agefodd","export"));
		$this->export_fields_array[$r]=array('s.rowid'=>'Id','c.nom'=>'ThirdPartyName','s.nom'=>'AgfFamilyName','s.prenom'=>'AgfFirstName',
		's.civilite'=>'AgfTitle',
		's.tel1'=>'AgfTelephone1','s.tel2'=>'AgfTelephone2','s.mail'=>'AgfPDFFicheEvalEmailTrainee',
		's.date_birth'=>'DateToBirth','s.place_birth'=>'AgfPlaceBirth','s.datec'=>'AgfDateC');
		$this->export_TypeFields_array[$r]=array('c.nom'=>"Text",'s.nom'=>"Text",'s.prenom'=>"Text",'s.civilite'=>"Text");
		$this->export_entities_array[$r]=array('c.nom'=>"company",'s.rowid'=>"AgfNbreParticipants",'s.nom'=>"AgfNbreParticipants",'s.prenom'=>"AgfNbreParticipants"
			,'s.civilite'=>"AgfNbreParticipants",'s.tel1'=>"AgfNbreParticipants",'s.tel2'=>"AgfNbreParticipants",'s.mail'=>"AgfNbreParticipants",'s.date_birth'=>"AgfNbreParticipants",
		's.place_birth'=>"AgfNbreParticipants"
		,'s.datec'=>"AgfNbreParticipants"
		);	// We define here only fields that use another picto

		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire as s';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'societe as c ON s.fk_soc = c.rowid';
		$this->export_sql_end[$r] .=' WHERE c.entity IN ('.getEntity("societe", 1).')';


		//certificate export
		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='ExportDataset_certificate';
		$this->export_icon[$r]='contact';
		$this->export_permission[$r]=array(array("agefodd","export"));
		$this->export_fields_array[$r]=array('s.nom'=>'AgfFamilyName','s.prenom'=>'AgfFirstName','s.civilite'=>'AgfTitle',
		's.date_birth'=>'DateToBirth','s.place_birth'=>'AgfPlaceBirth',
		'certif.fk_stagiaire'=>'Id','certif.fk_session_agefodd'=>'Id',
		'certif.certif_code'=>'AgfCertifCode','certif.certif_label'=>'AgfCertifLabel','certif.certif_dt_start'=>'AgfCertifDateSt','certif.certif_dt_end'=>'AgfCertifDateEnd',
		's.datec'=>'AgfDateC');
		$this->export_TypeFields_array[$r]=array('c.nom'=>"Text",'s.nom'=>"Text",'s.prenom'=>"Text",'s.civilite'=>"Text");
		$this->export_entities_array[$r]=array('c.nom'=>"company",'s.nom'=>'AgfNbreParticipants','s.prenom'=>'AgfNbreParticipants','s.civilite'=>'AgfNbreParticipants',
		's.date_birth'=>'AgfNbreParticipants','s.place_birth'=>'AgfNbreParticipants',
		'certif.fk_stagiaire'=>'AgfNbreParticipants','certif.fk_session_agefodd'=>'AgefoddMenuAction',
		'certif.certif_code'=>'AgfCertificate','certif.certif_label'=>'AgfCertificate','certif.certif_dt_start'=>'AgfCertificate','certif.certif_dt_end'=>'AgfCertificate',
		's.datec'=>'AgfNbreParticipants'
		 );	// We define here only fields that use another picto

		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire as s';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'agefodd_stagiaire_certif as certif ON certif.fk_stagiaire = s.rowid';
		
		//Session export
		$r++;
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='ExportDataset_session';
		$this->export_icon[$r]='bill';
		$this->export_permission[$r]=array(array("agefodd","export"));
		$this->export_fields_array[$r]=array('c.rowid'=>'Id',
			'CASE WHEN s.type_session=0 THEN \'Intra\' ELSE \'Inter\' END as type_session'=>'AgfFormTypeSession',
			's.dated'=>'AgfDateDebut',
			's.datef'=>'AgfDateFin',
			's.nb_stagiaire'=>'AgfNbreParticipants',
			's.notes'=>'AgfNote',
			's.cost_trainer'=>'AgfCoutFormateur',
			's.cost_site'=>'AgfCoutSalle',
			's.cost_trip'=>'AgfCoutDeplacement',
			's.sell_price'=>'AgfCoutFormation',
			's.archive as sessionarchive'=>'AgfArchiver',
			's.is_opca as sessionisopca'=>'AgfSubrocation',
			'socsessopca.nom as sessionsocopca'=>'AgfOPCAName',
			'contactsessopca.civilite as contactsessopcaciv'=>'AgfOPCASessContactCiv',
			'contactsessopca.lastname as contactsessopcalastname'=>'AgfOPCASessContactFirstName',
			'contactsessopca.firstname as contactsessopcalastname'=>'AgfOPCASessContactLastName',
			'c.intitule'=>'AgfFormIntitule',
			'c.ref'=>'Ref',
			'c.ref_interne'=>'AgfFormCodeInterne',
			'c.duree'=>'AgfDuree',
			'dictcat.code as catcode '=>'AgfTrainingCategCode',
			'dictcat.intitule as catlib'=>'AgfTrainingCategLabel',
			'product.ref'=>'ProductRef',
			'product.label'=>'ProductLabel',
			'product.price'=>'SellingPriceTTC',
			'product.accountancy_code_buy'=>'ProductAccountancySellCode',
			'p.ref_interne'=>'AgfLieu',
			'p.adresse'=>'Address',
			'p.cp'=>'Zip',
			'p.ville'=>'Town',
			'p_pays.libelle as country'=>'Country',
			'CASE WHEN f.type_trainer=\'user\' THEN fu.civilite ELSE fp.civilite END as trainerciv'=>'AgfTrainerCiv',
			'CASE WHEN f.type_trainer=\'user\' THEN fu.lastname ELSE fp.lastname END as trainerlastname'=>'AgfTrainerLastname',
			'CASE WHEN f.type_trainer=\'user\' THEN fu.firstname ELSE fp.firstname END as trainerfirstname'=>'AgfTrainerCivFirstname',
			'so.nom as cust_name'=>'Customer',
			'sta.civilite as traineeciv'=>'AgfCivilite',
			'sta.nom as traineelastname'=>'AgfStaLastname',
			'sta.prenom as traineefirstname'=>'AgfStaFirstname',
			'ssdicttype.intitule as statype'=>'AgfStagiaireModeFinancement',
			's.is_opca as staisopca'=>'AgfSubrocation',
			'socstaopca.nom as stasocopca'=>'AgfOPCAName',
			'contactstaopca.civilite as contactstaopcaciv'=>'AgfOPCAStaContactCiv',
			'contactstaopca.lastname as contactstaopcalastname'=>'AgfOPCAStaContactLastName',
			'contactstaopca.firstname as contactstaopcafirstname'=>'AgfOPCAStaContactFirstName'
		);
		$this->export_TypeFields_array[$r]=array('c.rowid'=>"Text");
		$this->export_entities_array[$r]=array('c.rowid'=>"Id",
			'CASE WHEN s.type_session=0 THEN \'Intra\' ELSE \'Inter\' END as type_session'=>'AgfSessionDetail',
			's.dated'=>'AgfSessionDetail',
			's.datef'=>'AgfSessionDetail',
			's.nb_stagiaire'=>'AgfSessionDetail',
			's.notes'=>'AgfSessionDetail',
			's.cost_trainer'=>'AgfSessionDetail',
			's.cost_site'=>'AgfSessionDetail',
			's.cost_trip'=>'AgfSessionDetail',
			's.sell_price'=>'AgfSessionDetail',
			's.archive as sessionarchive'=>'AgfSessionDetail',
			's.is_opca as sessionisopca'=>'AgfSessionDetail',
			'socsessopca.nom as sessionsocopca'=>'AgfSessionDetail',
			'contactsessopca.civilite as contactsessopcaciv'=>'AgfSessionDetail',
			'contactsessopca.lastname as contactsessopcalastname'=>'AgfSessionDetail',
			'contactsessopca.firstname as contactsessopcalastname'=>'AgfSessionDetail',
			'c.intitule'=>'AgfCatalogDetail',
			'c.ref'=>'AgfCatalogDetail',
			'c.ref_interne'=>'AgfCatalogDetail',
			'c.duree'=>'AgfCatalogDetail',
			'dictcat.code as catcode '=>'AgfCatalogDetail',
			'dictcat.intitule as catlib'=>'AgfCatalogDetail',
			'product.ref'=>'Product',
			'product.label'=>'Product',
			'product.price'=>'Product',
			'product.accountancy_code_buy'=>'Product',
			'p.ref_interne'=>'AgfSessPlace',
			'p.adresse'=>'AgfSessPlace',
			'p.cp'=>'AgfSessPlace',
			'p.ville'=>'AgfSessPlace',
			'p_pays.libelle as country'=>'AgfSessPlace',
			'CASE WHEN f.type_trainer=\'user\' THEN fu.civilite ELSE fp.civilite END as trainerciv'=>'AgfTeacher',
			'CASE WHEN f.type_trainer=\'user\' THEN fu.lastname ELSE fp.lastname END as trainerlastname'=>'AgfTeacher',
			'CASE WHEN f.type_trainer=\'user\' THEN fu.firstname ELSE fp.firstname END as trainerfirstname'=>'AgfTeacher',
			'so.nom as cust_name'=>'AgfSessionDetail',
			'sta.civilite as traineeciv'=>'AgfNbreParticipants',
			'sta.nom as traineelastname'=>'AgfNbreParticipants',
			'sta.prenom as traineefirstname'=>'AgfNbreParticipants',
			'ssdicttype.intitule as statype'=>'AgfNbreParticipants',
			's.is_opca as staisopca'=>'AgfNbreParticipants',
			'socstaopca.nom as stasocopca'=>'AgfNbreParticipants',
			'contactstaopca.civilite as contactstaopcaciv'=>'AgfNbreParticipants',
			'contactstaopca.lastname as contactstaopcalastname'=>'AgfNbreParticipants',
			'contactstaopca.firstname as contactstaopcafirstname'=>'AgfNbreParticipants'
		);	// We define here only fields that use another picto
		
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]=' FROM llx_agefodd_session as s';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_formation_catalogue as c ON c.rowid = s.fk_formation_catalogue';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_place as p ON p.rowid = s.fk_session_place';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_session_stagiaire as ss ON s.rowid = ss.fk_session_agefodd';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_stagiaire as sta ON sta.rowid = ss.fk_stagiaire';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_stagiaire_type as ssdicttype ON ssdicttype.rowid = ss.fk_agefodd_stagiaire_type';
		$this->export_sql_end[$r].=' LEFT JOIN llx_societe as so ON so.rowid = s.fk_soc';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_session_formateur as sf ON sf.fk_session = s.rowid';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_formateur as f ON f.rowid = sf.fk_agefodd_formateur';
		$this->export_sql_end[$r].=' LEFT JOIN llx_user as fu ON fu.rowid = f.fk_user';
		$this->export_sql_end[$r].=' LEFT JOIN llx_socpeople as fp ON fp.rowid = f.fk_socpeople';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_formation_catalogue_type as dictcat ON dictcat.rowid = c.fk_c_category';
		$this->export_sql_end[$r].=' LEFT JOIN llx_c_pays as p_pays ON p_pays.rowid = p.fk_pays';
		$this->export_sql_end[$r].=' LEFT JOIN llx_product as product ON product.rowid = c.fk_product';
		$this->export_sql_end[$r].=' LEFT JOIN llx_societe as socsessopca ON socsessopca.rowid = s.fk_soc_opca';
		$this->export_sql_end[$r].=' LEFT JOIN llx_socpeople as contactsessopca ON contactsessopca.rowid = s.fk_socpeople_opca';
		$this->export_sql_end[$r].=' LEFT JOIN llx_agefodd_opca as staopca ON staopca.fk_session_agefodd=s.rowid AND staopca.fk_soc_trainee=sta.fk_soc';
		$this->export_sql_end[$r].=' LEFT JOIN llx_societe as socstaopca ON socstaopca.rowid = staopca.fk_soc_opca';
		$this->export_sql_end[$r].=' LEFT JOIN llx_socpeople as contactstaopca ON contactstaopca.rowid = staopca.fk_socpeople_opca';


		// Array to add new pages in new tabs
		//$this->tabs = array('entity:Title:@mymodule:/mymodule/mynewtab.php?id=__ID__');
		// where entity can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'supplier_order'   to add a tab in supplier order view
		// 'supplier_invoice' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view
		// Array to add new pages in new tabs
		$this->tabs = array(
		'order:+tabAgefodd:AgfMenuSess:agefodd@agefodd:/agefodd/session/list_fin.php?search_orderid=__ID__',
		'invoice:+tabAgefodd:AgfMenuSess:agefodd@agefodd:/agefodd/session/list_fin.php?search_invoiceid=__ID__',
		'propal:+tabAgefodd:AgfMenuSess:agefodd@agefodd:/agefodd/session/list_fin.php?search_propalid=__ID__',
		'thirdparty:+tabAgefodd:AgfMenuSess:agefodd@agefodd:/agefodd/session/list_soc.php?socid=__ID__'
			);


		// Boxes
		$this->boxes = array();			// List of boxes
		$r=0;

		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		// Example:
		//$this->boxes[$r][1] = "myboxa.php";
		//$r++;
		//$this->boxes[$r][1] = "myboxb.php";
		//$r++;


		// Permissions
		$this->rights = array();
		$r=0;

		$this->rights[$r][0] = 103001;
		$this->rights[$r][1] = 'Lecture';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'lire';
		$r++;

		$this->rights[$r][0] = 103002;
		$this->rights[$r][1] = 'Modification';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'modifier';
		$r++;

		$this->rights[$r][0] = 103003;
		$this->rights[$r][1] = 'Ajout';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'creer';
		$r++;


		$this->rights[$r][0] = 103004;
		$this->rights[$r][1] = 'Suppression';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'supprimer';
		$r++;
		
		$this->rights[$r][0] = 103005;
		$this->rights[$r][1] = 'Voir stats';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'viewstats';
		$r++;

		$this->rights[$r][0] = 103006;
		$this->rights[$r][1] = 'export';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'export';
		$r++;
		
		$this->rights[$r][0] = 103007;
		$this->rights[$r][1] = 'agenda';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'agenda';
		$r++;
		
		$this->rights[$r][0] = 103008;
		$this->rights[$r][1] = 'agendatrainer';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'agendatrainer';
		$r++;

		// Main menu entries
		$this->menus = array();
		$r=0;

		$this->menu[$r]=array(	'fk_menu'=>0,
		'type'=>'top',
		'titre'=>'Module103000Name',
		'mainmenu'=>'agefodd',
		'leftmenu'=>'0',
		'url'=>'/agefodd/index.php',
		'langs'=>'agefodd@agefodd',
		'position'=>100,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0
		);
		$r++;
		// 1
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		'type'=>'left',
		'titre'=>'AgfMenuCat',
		'leftmenu'=>'AgfMenuCat',
		'url'=>'/agefodd/training/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>101,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuCat',
		'type'=>'left',
		'titre'=>'AgfMenuCatListActivees',
		'mainmenu'=>'agefodd',
		'url'=>'/agefodd/training/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>102,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuCat',
		'type'=>'left',
		'titre'=>'AgfMenuCatListArchivees',
		'mainmenu'=>'agefodd',
		'url'=>'/agefodd/training/list.php?arch=1',
		'langs'=>'agefodd@agefodd',
		'position'=>103,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuCat',
		'type'=>'left',
		'titre'=>'AgfMenuCatNew',
		'url'=>'/agefodd/training/card.php?action=create',
		'langs'=>'agefodd@agefodd',
		'position'=>104,
		'enabled'=>'$user->rights->agefodd->creer',
		'perms'=>'$user->rights->agefodd->creer',
		'target'=>'',
		'user'=>0);
		$r++;
		// 2
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		'type'=>'left',
		'titre'=>'AgfMenuSess',
		'leftmenu'=>'AgfMenuSess',
		'url'=>'/agefodd/session/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>201,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessDraftList',
		'url'=>'/agefodd/session/list.php?status=1',
		'langs'=>'agefodd@agefodd',
		'position'=>202,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessActList',
		'url'=>'/agefodd/session/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>203,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessArchList',
		'url'=>'/agefodd/session/list.php?arch=1',
		'langs'=>'agefodd@agefodd',
		'position'=>204,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessArchiveByYear',
		'url'=>'/agefodd/session/archive_year.php',
		'langs'=>'agefodd@agefodd',
		'position'=>205,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessNew',
		'url'=>'/agefodd/session/card.php?action=create',
		'langs'=>'agefodd@agefodd',
		'position'=>206,
		'enabled'=>'$user->rights->agefodd->creer',
		'perms'=>'$user->rights->agefodd->creer',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessStats',
		'url'=>'/agefodd/session/stats/index.php',
		'langs'=>'agefodd@agefodd',
		'position'=>207,
		'enabled'=>'$user->rights->agefodd->viewstats',
		'perms'=>'$user->rights->agefodd->viewstats',
		'target'=>'',
		'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuSess',
		'type'=>'left',
		'titre'=>'AgfMenuSessListOpe',
		'url'=>'/agefodd/session/list_ope.php',
		'langs'=>'agefodd@agefodd',
		'position'=>208,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;
		
		// 3
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		'type'=>'left',
		'titre'=>'AgfMenuActStagiaire',
		'leftmenu'=>'AgfMenuActStagiaire',
		'url'=>'/agefodd/trainee/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>301,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuActStagiaire',
		'type'=>'left',
		'titre'=>'AgfMenuActStagiaireList',
		'url'=>'/agefodd/trainee/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>302,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuActStagiaire',
		'type'=>'left',
		'titre'=>'AgfMenuActStagiaireNew',
		'url'=>'/agefodd/trainee/card.php?action=create',
		'langs'=>'agefodd@agefodd',
		'position'=>303,
		'enabled'=>'$user->rights->agefodd->creer',
		'perms'=>'$user->rights->agefodd->creer',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuActStagiaire',
		'type'=>'left',
		'titre'=>'AgfMenuActStagiaireNewFromContact',
		'url'=>'/agefodd/trainee/card.php?action=nfcontact',
		'langs'=>'agefodd@agefodd',
		'position'=>304,
		'enabled'=>'$user->rights->agefodd->creer',
		'perms'=>'$user->rights->agefodd->creer',
		'target'=>'',
		'user'=>0);
		$r++;
		// 4
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		'type'=>'left',
		'titre'=>'AgfMenuLogistique',
		'leftmenu'=>'AgfMenuLogistique',
		'url'=>'/agefodd/trainee/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>401,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuLogistique',
		'type'=>'left',
		'titre'=>'AgfMenuSite',
		'url'=>'/agefodd/site/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>402,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuLogistique',
		'type'=>'left',
		'titre'=>'AgfMenuFormateur',
		'url'=>'/agefodd/trainer/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>403,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuLogistique',
		'type'=>'left',
		'titre'=>'AgfMenuContact',
		'url'=>'/agefodd/contact/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>404,
		'enabled'=>'!($conf->global->AGF_CONTACT_DOL_SESSION)',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		'type'=>'left',
		'titre'=>'AgfMenuAgenda',
		'leftmenu'=>'AgfMenuAgenda',
		'url'=>'',
		'langs'=>'agefodd@agefodd',
		'position'=>501,
		'enabled'=>'$user->rights->agefodd->lire',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuAgenda',
		'type'=>'left',
		'titre'=>'AgfMenuAgenda',
		'url'=>'/agefodd/agenda/index.php',
		'langs'=>'agefodd@agefodd',
		'position'=>502,
		'enabled'=>'$user->rights->agefodd->agenda',
		'perms'=>'$user->rights->agefodd->agenda',
		'target'=>'',
		'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuAgenda',
		'type'=>'left',
		'titre'=>'AgfMenuAgendaTrainer',
		'url'=>'/agefodd/agenda/index.php?type=trainer',
		'langs'=>'agefodd@agefodd',
		'position'=>503,
		'enabled'=>'$user->rights->agefodd->agendatrainer',
		'perms'=>'$user->rights->agefodd->agendatrainer',
		'target'=>'',
		'user'=>0);
		$r++;
		

		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		 'type'=>'left',
		'titre'=>'AgfMenuCursus',
		'leftmenu'=>'AgfMenuCursus',
		'url'=>'/agefodd/cursus/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>601,
		'enabled'=>'$conf->global->AGF_MANAGE_CURSUS',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuCursus',
		'type'=>'left',
		'titre'=>'AgfMenuCursusList',
		'url'=>'/agefodd/cursus/list.php',
		'langs'=>'agefodd@agefodd',
		'position'=>602,
		'enabled'=>'$conf->global->AGF_MANAGE_CURSUS',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuCursus',
		'type'=>'left',
		'titre'=>'AgfMenuCursusNew',
		'url'=>'/agefodd/cursus/card.php?action=create',
		'langs'=>'agefodd@agefodd',
		'position'=>603,
		'enabled'=>'$conf->global->AGF_MANAGE_CURSUS',
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;


		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd',
		'type'=>'left',
		'titre'=>'AgfMenuDemoAdmin',
		'leftmenu'=>'AgfMenuDemoAdmin',
		'url'=>'/agefodd/admin/admin_agefodd.php',
		'langs'=>'agefodd@agefodd',
		'position'=>701,
		'enabled'=>1,
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);
		$r++;
		
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=agefodd,fk_leftmenu=AgfMenuDemoAdmin',
		'type'=>'left',
		'titre'=>'AgfMenuDemoAdminDetail',
		'url'=>'/agefodd/admin/admin_agefodd.php',
		'langs'=>'agefodd@agefodd',
		'position'=>702,
		'enabled'=>1,
		'perms'=>'$user->rights->agefodd->lire',
		'target'=>'',
		'user'=>0);

	}

	/**
	 *	Function called when module is enabled.
	 *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *	It also creates data directories.
	 *
	 *  @return		int	1 if OK, 0 if KO
	 */
	function init()
	{
		global $conf;
		
		$sql = array();

		$result=$this->load_tables();

		if ($this->db->type=='pgsql') {
			dol_syslog(get_class($this)."::init this->db->type=".$this->db->type, LOG_DEBUG);
			$res=@include_once(DOL_DOCUMENT_ROOT ."/core/lib/admin.lib.php");
			dol_syslog(get_class($this)."::init res=".$res, LOG_DEBUG);
			foreach($conf->file->dol_document_root as $dirroot)
			{
				$dir = $dirroot.'/agefodd/lib/sql/';
				
				$handle=@opendir($dir);         // Dir may not exists
				if (is_resource($handle))
				{
					$result=run_sql($dir.'agefodd_function.sql',1,'',1);
				}
			}
		}
		
		return $this->_init($sql);		
	}

	/**
	 *	Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted.
	 *
	 *  @return		int	1 if OK, 0 if KO
	 */
	function remove()
	{
		$sql = array();

		return $this->_remove($sql);
	}


	/**
	 *	Create tables, keys and data required by module
	 * 	Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 	and create data commands must be stored in directory /mymodule/sql/
	 *	This function is called by this->init.
	 *
	 * 	@return		int	<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables_agefodd('/agefodd/sql/');
	}

	/**
	 *  Create tables and keys required by module.
	 *  Do not use version of Dolibarr because execute script only if version requiered it
	 *  Files module.sql and module.key.sql with create table and create keys
	 *  commands must be stored in directory reldir='/module/sql/'
	 *  This function is called by this->init
	 *
	 *  @param	string	$reldir		Relative directory where to scan files
	 *  @return	int     			<=0 if KO, >0 if OK
	 */
	function _load_tables_agefodd($reldir)
	{
		global $db,$conf;

		$error=0;

		include_once(DOL_DOCUMENT_ROOT ."/core/lib/admin.lib.php");

		$ok = 1;
		foreach($conf->file->dol_document_root as $dirroot)
		{
			if ($ok)
			{
				$dir = $dirroot.$reldir;
				$ok = 0;

				// Run llx_mytable.sql files
				$handle=@opendir($dir);         // Dir may not exists
				if (is_resource($handle))
				{
					while (($file = readdir($handle))!==false)
					{
						if (preg_match('/\.sql$/i',$file) && ! preg_match('/\.key\.sql$/i',$file) && substr($file,0,4) == 'llx_' && substr($file,0,4) != 'data')
						{
							$result=run_sql($dir.$file,1,'',1);
							if ($result <= 0) $error++;
						}
					}
					closedir($handle);
				}

				// Run llx_mytable.key.sql files (Must be done after llx_mytable.sql)
				$handle=@opendir($dir);         // Dir may not exist
				if (is_resource($handle))
				{
					while (($file = readdir($handle))!==false)
					{
						if (preg_match('/\.key\.sql$/i',$file) && substr($file,0,4) == 'llx_' && substr($file,0,4) != 'data')
						{
							$result=run_sql($dir.$file,1,'',1);
							if ($result <= 0) $error++;
						}
					}
					closedir($handle);
				}

				// Run data_xxx.sql files (Must be done after llx_mytable.key.sql)
				$handle=@opendir($dir);         // Dir may not exist
				if (is_resource($handle))
				{
					while (($file = readdir($handle))!==false)
					{
						if (preg_match('/\.sql$/i',$file) && ! preg_match('/\.key\.sql$/i',$file) && substr($file,0,4) == 'data')
						{
							$result=run_sql($dir.$file,1,'',1);
							if ($result <= 0) $error++;
						}
					}
					closedir($handle);
				}

				// Run update_xxx.sql files
				$handle=@opendir($dir);         // Dir may not exist
				if (is_resource($handle))
				{
					while (($file = readdir($handle))!==false)
					{
						$dorun = false;
						if (preg_match('/\.sql$/i',$file) && ! preg_match('/\.key\.sql$/i',$file) && substr($file,0,6) == 'update')
						{
							dol_syslog(get_class($this)."::_load_tables_agefodd analyse file:".$file, LOG_DEBUG);
							
							//Special test to know what kind of update script to run
							$sql="SELECT value FROM ".MAIN_DB_PREFIX."const WHERE name='AGF_LAST_VERION_INSTALL'";
							
							dol_syslog(get_class($this)."::_load_tables_agefodd sql:".$sql, LOG_DEBUG);
							$resql=$this->db->query($sql);
							if ($resql) {
								if ($this->db->num_rows($resql)==1) {
									$obj = $this->db->fetch_object($resql);
									$last_version_install=$obj->value;
									dol_syslog(get_class($this)."::_load_tables_agefodd last_version_install:".$last_version_install, LOG_DEBUG);
									
									$tmpversion=explode('_',$file);
									$fileversion_array=explode('-',$tmpversion[1]);
									$fileversion=str_replace('.sql','',$fileversion_array[1]);
									dol_syslog(get_class($this)."::_load_tables_agefodd fileversion:".$fileversion, LOG_DEBUG);
									if (version_compare($last_version_install, $fileversion)==-1) {
										$dorun = true;
										dol_syslog(get_class($this)."::_load_tables_agefodd run file:".$file, LOG_DEBUG);
									}
									
								}
							}
							else
							{
								$this->error="Error ".$this->db->lasterror();
								dol_syslog(get_class($this)."::_load_tables_agefodd ".$this->error, LOG_ERR);
								$error ++;
							}
							
							if ($dorun) {
								$result=run_sql($dir.$file,1,'',1);
								if ($result <= 0) $error++;
							}
						}
					}
					closedir($handle);
				}

				if ($error == 0)
				{
					$ok = 1;
				}
			}
		}
		
		//DELETE AGF_LAST_VERION_INSTALL to update with the new one
		$sql='DELETE FROM '.MAIN_DB_PREFIX.'const WHERE name=\'AGF_LAST_VERION_INSTALL\'';
		dol_syslog(get_class($this)."::_load_tables_agefodd sql:".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		 if (!$resql) {
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::_load_tables_agefodd ".$this->error, LOG_ERR);
			$error ++;
		}

		return $ok;
	}
}
