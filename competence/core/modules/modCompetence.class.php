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
class modCompetence extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 7900;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'curriculumvitae';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "ATM";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Gestion des expériences, formations et compétences";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '1.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='competence@competence';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 // Set this to 1 if module has its own trigger directory
		//							'login' => 0,                                    // Set this to 1 if module has its own login method directory
		//							'substitutions' => 0,                            // Set this to 1 if module has its own substitution function file
		//							'menus' => 0,                                    // Set this to 1 if module has its own menus handler directory
		//							'barcode' => 0,                                  // Set this to 1 if module has its own barcode directory
		//							'models' => 0,                                   // Set this to 1 if module has its own models directory
		//							'css' => '/mymodule/css/mymodule.css.php',       // Set this to relative path of css if module has its own css file
		//							'hooks' => array('hookcontext1','hookcontext2')  // Set here all hooks context managed by module
		//							'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE')) // Set here all workflow context managed by module
		//                        );
		$this->module_parts = array();

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
		//$this->config_page_url = array("setuppage.php@competence");

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("competence@competence");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0)
		// );
		$this->const = array(0=>array('COMPETENCE_HAUTEURGRAPHIQUES','chaine','200','Permet de modifier la hauteur des graphiques',1)); 

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
        $this->tabs = array(
        	'user:+competence:Formations et expériences:competence@competence:$user->rights->curriculumvitae->myactions->skill:/competence/experience.php?fk_user=__ID__'  // To add a new tab identified by code tabname1
            ,'user:+remuneration:Rémunérations:competence@competence:$user->rights->curriculumvitae->myactions->voirRemuneration:/competence/remuneration.php?fk_user=__ID__'
            ,'user:+productivite:Productivité:competence@competence:$user->rights->curriculumvitae->productivite->read:/competence/productivite_user.php?fk_user=__ID__&action=view'
       	);

        // Dictionnaries
        if (!isset($conf->ressource->enabled)) @$conf->ressource->enabled=0;
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
		//$r=0;
		// Example:
		/*
		$this->boxes[$r][1] = "myboxa.php";
		$r++;
		$this->boxes[$r][1] = "myboxb.php";
		$r++;
		*/
		
		$r=0;
		$this->rights[$r][0] = 7951;
		$this->rights[$r][1] = 'Rechercher un profil';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'rechercheProfil';
		$r++;
		
		
		$this->rights[$r][0] = 7952;
		$this->rights[$r][1] = 'Ajouter/Supprimer une ligne de rémunération';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'ajoutRemuneration';
		$r++;
		
		$this->rights[$r][0] = 7953;
		$this->rights[$r][1] = 'Consulter toutes les fiches de DIF';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'consulterAllDif';
		$r++;
		
		$this->rights[$r][0] = 7954;
		$this->rights[$r][1] = 'Consulter ses propres fiches de DIF';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'consulterOwnDif';
		$r++;
		
		$this->rights[$r][0] = 7955;
		$this->rights[$r][1] = 'Gérer les DIF';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'gererDif';
		$r++;
	
		$this->rights[$r][0] = 7956;
		$this->rights[$r][1] = 'Consulter sa rémunération';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'voirRemuneration';
		$r++;
	
		$this->rights[$r][0] = 7957;
		$this->rights[$r][1] = 'Consulter la rémunération des membres de son/ses groupes d\'utilisateur';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'voirRemunerationGroupe';
		$r++;
	
		$this->rights[$r][0] = 7958;
		$this->rights[$r][1] = 'Consulter sa productivité';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'productivite';
        $this->rights[$r][5] = 'read';
		$r++;
	
		$this->rights[$r][0] = 7959;
		$this->rights[$r][1] = 'Enregistrer sa productivité';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'productivite';
        $this->rights[$r][5] = 'write';
		$r++;
	
		$this->rights[$r][0] = 7960;
		$this->rights[$r][1] = 'Administrer la productivité';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'productivite';
        $this->rights[$r][5] = 'admin';
		$r++;
		
		$this->rights[$r][0] = 7961;
		$this->rights[$r][1] = 'Administrer les fiche de poste';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'poste';
        $this->rights[$r][5] = 'admin';
		$r++;
		
		$this->rights[$r][0] = 7962;
		$this->rights[$r][1] = 'Consulter les fiche de poste';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'poste';
        $this->rights[$r][5] = 'read';
		$r++;
		
		$this->rights[$r][0] = 7963;
		$this->rights[$r][1] = 'Importer les rémunérations';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'remuneration';
        $this->rights[$r][5] = 'importer';
		$r++;
		
		$this->rights[$r][0] = 7964;
		$this->rights[$r][1] = 'Ajouter/Supprimer une compétence/formation';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'myactions';
        $this->rights[$r][5] = 'skill';
		$r++;
		
	
		// Permissions
		/*$this->rights = array();		// Permission array used by this module
		$r=0;
		$this->rights[$r][0] = 7101;
		$this->rights[$r][1] = 'Afficher sa hiérarchie';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'mydossier';
		$this->rights[$r][5] = 'write';
		$r++;*/
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
		
		$this->menu[$r]=array(	'fk_menu'=>0,			                // Put 0 if this is a top menu
								'type'=>'top',			                // This is a Top menu entry
								'titre'=>$langs->trans('Salarié'),
								'mainmenu'=>'competence',
								'leftmenu'=>'',
								'url'=>'/competence/index.php',
								'langs'=>'competence@competence',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
								'position'=>100,
								'enabled'=>'1',	// Define condition to show or hide menu entry. Use '$conf->financement->enabled' if entry must be visible if module is enabled.
								'target'=>'',
								'user'=>0);						                // 0=Menu for internal users, 1=external users, 2=both
		
		$r++;
        $this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Fiche utilisateur'),
			        	'mainmenu'=> 'competence',
			        	'leftmenu'=> 'ficheUser',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/index.php',
						'langs'=> 'competence@competence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 99,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		
		
		$r++;
        $this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Recherche compétences'),
			        	'mainmenu'=> 'competence',
			        	'leftmenu'=> 'souscompetence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/rechercheCompetence.php',
						'langs'=> 'competence@competence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 101,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=>'$user->rights->curriculumvitae->myactions->rechercheProfil',			                // Use 'perms'=>'$user->rights->financement->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		
		
		
		$r++;
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=competence,fk_leftmenu=souscompetence',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Statistiques'),
		        	'mainmenu'=> '',
		        	'leftmenu'=> 'souscompetence',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/competence/statCompetence.php',
					'langs'=> 'competence@competence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 102,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
		
		$this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('typePoste'),
			        	'mainmenu'=> 'competence',
			        	'leftmenu'=> 'type_poste',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/liste_types_postes.php',
						'position'=> 103,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '$user->rights->curriculumvitae->poste->read',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );

		$r++;
        $this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=competence,fk_leftmenu=type_poste',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('createPoste'),
		        	'mainmenu'=> 'competence',
		        	'leftmenu'=> 'formulaires',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/competence/fiche_type_poste.php?action=new',
					'position'=> 104,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->curriculumvitae->poste->admin',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		$r++;
		$this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Productivité'),
			        	'mainmenu'=> 'competence',
			        	'leftmenu'=> 'productivite',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/productivite_liste.php',
						'position'=> 105,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '$user->rights->curriculumvitae->productivite->admin',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		$r++;
		$this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence,fk_leftmenu=productivite',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Nouvel indice'),
			        	'mainmenu'=> 'competence',
			        	'leftmenu'=> 'formulaires',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/productivite.php?action=new',
						'position'=> 106,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '$user->rights->curriculumvitae->productivite->admin',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		$r++;
		
		$this->menu[$r]=array(
		            'fk_menu'=>'fk_mainmenu=report',			// Put 0 if this is a top menu
		        	'type'=> 'left',			// This is a Top menu entry
		        	'titre'=> $langs->trans('Statistique productivité'),
		        	'mainmenu'=> 'report',
		        	'leftmenu'=> 'productivite',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
					'url'=> '/competence/productivite.php?action=stat',
					'langs'=> 'report@report',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
					'position'=> 107,
					'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
					'perms'=> '$user->rights->curriculumvitae->productivite->admin',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
					'target'=> '',
					'user'=> 2
        );
		
		
	/*	
		$this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Plans de Formation'),
			        	'mainmenu'=> 'competence',
			        	'leftmenu'=> 'listeplanformation',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/planFormation.php',
						'langs'=> 'competence@competence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 103,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		$r++;
		$this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence,fk_leftmenu=listeplanformation',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Liste des Plans'),
			        	'mainmenu'=> '',
			        	'leftmenu'=> 'listeplanformation',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/planFormation.php',
						'langs'=> 'competence@competence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 104,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		$r++;
		
		$this->menu[$r]=array(
			            'fk_menu'=>'fk_mainmenu=competence,fk_leftmenu=listeplanformation',			// Put 0 if this is a top menu
			        	'type'=> 'left',			// This is a Top menu entry
			        	'titre'=>$langs->trans('Nouveau Plan'),
			        	'mainmenu'=> '',
			        	'leftmenu'=> 'nouveauplanformation',		// Use 1 if you also want to add left menu entries using this descriptor. Use 0 if left menu entries are defined in a file pre.inc.php (old school).
						'url'=> '/competence/planFormation.php?action=new',
						'langs'=> 'competence@competence',	// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
						'position'=> 105,
						'enabled'=> '1',			// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
						'perms'=> '',			// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
						'target'=> '',
						'user'=> 2	// 0=Menu for internal users, 1=external users, 2=both
        );
		$r++;
		*/

		// Add here entries to declare new menus
		//
		// Example to declare a new Top Menu entry and its Left menu entry:
	


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

		if(!is_file( dol_buildpath("/competence/config.php" ))) {
			 $data='<?php require(\'default.config.php\'); /* fichier de conf de base */';	
			
			file_put_contents( dol_buildpath("/competence/config.php" ) , $data);
		}

		$url =dol_buildpath("/competence/script/create-maj-base.php",2);
		file_get_contents($url);
		
		//Création extrafield type de poste issu de la table llx_rh_fiche_poste
		global $db;
		dol_include_once('/core/class/extrafields.class.php');
		$e = new Extrafields($db);
		$e->addExtraField("type_poste", "Type de poste", "sellist", 0, $size, "user", 0, 0, '', array('options'=>array("rh_fiche_poste:type_poste"=>null)));
		
		$e = new Extrafields($db);
		$e->addExtraField("echelon", "Echelon", "int", 0, 11, "user", 0, 0, '');
		
		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function remove($options='')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}



}

?>
