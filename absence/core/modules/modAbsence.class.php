<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis@dolibarr.fr>
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
 * 	\defgroup   mymodule     Module MyModule
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/mymodule/core/modules directory.
 *  \file       htdocs/mymodule/core/modules/modMyModule.class.php
 *  \ingroup    mymodule
 *  \brief      Description and activation file for module MyModule
 */

include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 *  Description and activation class for module MyModule
 */
class modAbsence extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
        global $langs,$conf, $user;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 7100;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'absence';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "ATM";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Gestion des absences";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='absence@absence';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		
		
		$this->module_parts = array(
			'triggers' =>1      
			,'hooks'=>array('userdao')
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
		$this->config_page_url = array("admin.php@absence");

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("absence@absence");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0)
		// );
		$this->const = array(
			array('RH_DOL_ADMIN_USER','chaine','admin','Ajouté par RH',1)
			,array('RH_USER_MAIL_SENDER','chaine','webmaster@atm-consulting.fr','Ajouté par RH',1)
			,array('RH_DATE_RTT_CLOTURE','chaine','28-02-2014','Ajouté par RH',1)
			,array('RH_DATE_CONGES_CLOTURE','chaine','31-05-2014','Ajouté par RH',1)
			,array('RH_JOURS_NON_TRAVAILLE','chaine','samedi,dimanche','Ajouté par RH',1)
			,array('RH_MONTANT_TICKET_RESTO','chaine','7','Valeur de base des tickets restaurant en centime',1)
			,array('RH_PART_PATRON_TICKET_RESTO','chaine','50','Part patronnale en % des tickets restaurant',1)
			,array('RH_NDF_TICKET_RESTO','chaine','30','id des dépenses ne donnant pas droit à des ticket resto',1)
			,array('RH_CODEPRODUIT_TICKET_RESTO','chaine','789456','Code produit',1)
			,array('RH_CODECLIENT_TICKET_RESTO','chaine','123456','Code Client',1)
			
		); 

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:langfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  // To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:Title2:langfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  // To add another new tab identified by code tabname2
        //                              'objecttype:-tabname');                                                     // To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'thirdparty'       to add a tab in third party view
		// 'intervention'     to add a tab in intervention view
		// 'order_supplier'   to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice'          to add a tab in customer invoice view
		// 'order'            to add a tab in customer order view
		// 'product'          to add a tab in product view
		// 'stock'            to add a tab in stock view
		// 'propal'           to add a tab in propal view
		// 'member'           to add a tab in fundation member view
		// 'contract'         to add a tab in contract view
		// 'user'             to add a tab in user view
		// 'group'            to add a tab in group view
		// 'contact'          to add a tab in contact view
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        $this->tabs = array();/*'thirdparty:+creerTypeRessource:CréerTypeRessource:ressource@ressource:/ressource/index.php',  // To add a new tab identified by code tabname1
                             'thirdparty:+nouvelleRessource:NouvelleRessource:ressource@ressource:/ressource/index.php',
                                      );*/
	
        // Dictionnaries
       // if (!isset($conf->ressource->enabled)) @$conf->ressource->enabled=0;
		$this->dictionnaries=array();
        /* Example:
        if (! isset($conf->mymodule->enabled)) $conf->mymodule->enabled=0;	// This is to avoid warnings
        $this->dictionnaries=array(
            'langs'=>'mymodule@mymodule',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionnary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->mymodule->enabled,$conf->mymodule->enabled,$conf->mymodule->enabled)												// Condition to show each dictionnary
        );
        */

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
        $r = 0;
        $this->boxes[$r][1] = "box_absence@absence";
        
		//$r=0;
		// Example:
		/*
		$this->boxes[$r][1] = "myboxa.php";
		$r++;
		$this->boxes[$r][1] = "myboxb.php";
		$r++;
		*/

		
		$this->rights[$r][0] = 7101;
		$this->rights[$r][1] = 'Valider ou refuser une demande de congés';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'valideurConges';
		$r++;
		
		$this->rights[$r][0] = 7102;
		$this->rights[$r][1] = 'Visualiser le compteur de congés d\'un collaborateur';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'visualiserCompteur';
		$r++;
		
		$this->rights[$r][0] = 7103;
		$this->rights[$r][1] = 'Modifier le compteur de congés d\'un collaborateur';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'modifierCompteur';
		$r++;
		
		$this->rights[$r][0] = 7104;
		$this->rights[$r][1] = 'Modifier les paramètres globaux des congés';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'modifierParamGlobalConges';
		$r++;
		
		$this->rights[$r][0] = 7105;
		$this->rights[$r][1] = 'Ajouter/Supprimer des jours non travaillés';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'ajoutJourOff';
		$r++;
		
		$this->rights[$r][0] = 7106;
		$this->rights[$r][1] = 'Visualiser l\'emploi du temps des collaborateurs';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'voirTousEdt';
		$r++;
		
		$this->rights[$r][0] = 7118;
		$this->rights[$r][1] = 'Visualiser son emploi du temps';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'voirSonEdt';
		$r++;
		
		$this->rights[$r][0] = 7107;
		$this->rights[$r][1] = 'Modifier l\'emploi du temps des collaborateurs';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'modifierEdt';
		$r++;

		$this->rights[$r][0] = 7108;
		$this->rights[$r][1] = 'Voir toutes les absences ou présences sur la liste des absences';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'voirToutesAbsencesListe';
		$r++;
		
		$this->rights[$r][0] = 7109;
		$this->rights[$r][1] = 'Voir toutes les absences ou présences des collaborateurs sur le calendrier';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'voirToutesAbsences';
		$r++;
		
		$this->rights[$r][0] = 7110;
		$this->rights[$r][1] = 'Rajouter des règles sur les demandes d\'absences';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'rajouterRegle';
		$r++;
		
		$this->rights[$r][0] = 7111;
		$this->rights[$r][1] = 'Créer une absence ou présence pour un collaborateur';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'creerAbsenceCollaborateur';
		$r++;
		
		$this->rights[$r][0] = 7112;
		$this->rights[$r][1] = 'Créer une absence ou présence pour un collaborateur de son groupe';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'creerAbsenceCollaborateurGroupe';
		$r++;
		
		$this->rights[$r][0] = 7113;
		$this->rights[$r][1] = 'Uploader des fichiers joints sur les règles';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'uploadFilesRegle';
		
		$r++;
		$this->rights[$r][0] = 7114;
		$this->rights[$r][1] = 'Effectuer une recherche sur les absences ou présence des collaborateurs';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'rechercherAbsence';

		$r++;
		$this->rights[$r][0] = 7115;
		$this->rights[$r][1] = 'Visualiser le planning par utilisateur';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'voirPlanningUser';
		
		$r++;
		$this->rights[$r][0] = 7116;
		$this->rights[$r][1] = 'Voir l\'onglet des absences/présences';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'voirOngletAbsence';

		
		$r++;
		$this->rights[$r][0] = 7117;
		$this->rights[$r][1] = 'Visualiser les fichiers des absences';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';
		
		$r++;
		$this->rights[$r][0] = 7119;
		$this->rights[$r][1] = 'Pointage à l\'entrée et sortie de l\'entreprise';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'pointeuse';
		
		
		$r++;
		$this->rights[$r][0] = 7120;
		$this->rights[$r][1] = 'Voir les absences refusee sur le planning';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'voirAbsenceRefusee';
		
		$r++;
		$this->rights[$r][0] = 7121;
		$this->rights[$r][1] = 'Gérer les tickets restaurants';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'gererTicketRestaurant';
		
		$r++;
		$this->rights[$r][0] = 7122;
		$this->rights[$r][1] = 'Fiche de déclaration temps cadres';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'declarationCadre';
		
		$r++;
		$this->rights[$r][0] = 7123;
		$this->rights[$r][1] = 'Supprimer son absence une fois déclarée';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
		$this->rights[$r][5] = 'supprimerMonAbsence';
		
		
		
		
		

		
		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		// $this->rights[$r][0] = 2000; 				// Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label';	// Permission label
		// $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $r++;


		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus
		//
		// Example to declare a new Top Menu entry and its Left menu entry:
		$this->menu[$r]=array(	'fk_menu'=>0,			                // Put 0 if this is a top menu
								'type'=>'top',			                // This is a Top menu entry
								'titre'=>$langs->trans('Abs./Présence'),
								'mainmenu'=>'absence',
								'leftmenu'=>'',
								'url'=>'/absence/calendrierAbsence.php?',
								'langs'=>'absence@absence',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>100,
								'enabled'=>'1',	// Define condition to show or hide menu entry. Use '$conf->financement->enabled' if entry must be visible if module is enabled.
								'perms'=>'$user->rights->absence->myactions->voirOngletAbsence',			                // Use 'perms'=>'$user->rights->financement->level1->level2' if you want your menu with a permission rules
								'target'=>'',
								'user'=>0);						                // 0=Menu for internal users, 1=external users, 2=both
		
		$r++;
        $this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=absence',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Absences'),
			        	'mainmenu'=> 'absence',
			        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/absence/calendrierAbsence.php?idUser=0',
						'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 101,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Planning utilisateurs'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/planningUser.php?action=view',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 102,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->voirPlanningUser',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Emplois du temps'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/emploitemps.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 102,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->voirTousEdt || $user->rights->absence->myactions->voirSonEdt',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Demande d\'absence'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/absence.php?action=new',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 102,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Présences'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/presence.php?action=nsupprimerMonAbsenceew',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 102,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Vos absences'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/absence.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 103,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Absences à valider'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/absence.php?action=listeValidation',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 103,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->valideurConges',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Pointeuse'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/pointeuse.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 103,
					'perms'=> '$user->rights->absence->myactions->pointeuse',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Toutes les absences'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/absence.php?action=listeAdmin',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 104,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->voirToutesAbsencesListe',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
			$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Compteurs jours acquis'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/compteur.php?action=view',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 104,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Administration compteurs'),
		        	'mainmenu'=> 'absence',
		        	'leftmenu'=> 'admin',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/compteur.php?action=compteurAdmin',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 105,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->modifierCompteur',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=admin',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Administration générale des congés'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/adminConges.php?action=view',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 105,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->modifierParamGlobalConges',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=admin',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Recherche absences'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/rechercheAbsence.php?action=view',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 105,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->rechercherAbsence',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Règles absences'),
		        	'mainmenu'=> 'absence',
		        	'leftmenu'=> 'regle',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/regleAbsence.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 105,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->rajouterRegle',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> 'Documents règles absence',
		        	'mainmenu'=> 'absence',
		        	'leftmenu'=> 'test',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/documentRegle.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 115,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		

		 $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report,fk_leftmenu=exports',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Statistiques absences découpé'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> '',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/report-stat.php',
					'langs'=> 'report@report',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 107,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->report->exports->generateAbsenceExport',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Tickets resto'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/ticketResto.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 110,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->gererTicketRestaurant',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=absence,fk_leftmenu=sousabsence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Temps travaillés'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'sousabsence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/absence/declarationCadre.php',
					'langs'=> 'absence@absence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 110,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->absence->myactions->declarationCadre',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		



		// Exports
		$r=1;

		// Example:
		// $this->export_code[$r]=$this->rights_class.'_'.$r;
		// $this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        // $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		// $this->export_permission[$r]=array(array("facture","facture","export"));
		// $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.cp'=>'Zip','s.ville'=>'Town','s.fk_pays'=>'Country','s.tel'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		// $this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.cp'=>'company','s.ville'=>'company','s.fk_pays'=>'company','s.tel'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		// $this->export_sql_start[$r]='SELECT DISTINCT ';
		// $this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		// $this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		// $this->export_sql_end[$r] .=' WHERE f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		// $r++;
	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function init($options='')
	{
		$sql = array();
		
		$result=$this->load_tables();

		if(!is_file( dol_buildpath("/absence/config.php" ))) {
			 $data='<?php require(\'default.config.php\'); /* fichier de conf de base */';	
			
			file_put_contents( dol_buildpath("/absence/config.php" ) , $data);
		}

		$url =dol_buildpath("/absence/script/create-maj-base.php",2);
		file_get_contents($url);
		
		$url2 =dol_buildpath("/absence/script/create-compteur.php",2);
		file_get_contents($url2);
		
		dol_include_once('/core/class/extrafields.class.php');
        $extrafields=new ExtraFields($this->db);
		$res = $extrafields->addExtraField('ticketresto_ok', 'A choisir les tickets restaurants ?', 'boolean', 0, '', 'user');
		

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KOsupprimerMonAbsence
	 */
	function remove($options='')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

	function load_tables()
	{
		return $this->_load_tables('/absence/sql/');
	}


}
