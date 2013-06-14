<?php
/* Copyright (C) 2010 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * or see http://www.gnu.org/
 */

/**
 *      \file       test/phpunit/UserTest.php
 *		\ingroup    test
 *      \brief      PHPUnit test
 *		\remarks	To run this script as CLI:  phpunit filename.php
 */

global $conf;

//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
require('../config.php');
/*$url = '/var/www/rhcpro/rh-library/PHPUnit/PHPUnit/Autoload.php';echo $url;
require_once $url;*/
//require('../class/ressource.class.php');
//require('../lib/ressource.lib.php');

//$conf->global->MAIN_DISABLE_ALL_MAILS=1;


/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class RessourceTest extends PHPUnit_Framework_TestCase
{
	/*protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;*/

	/**
	 * Constructor
	 * We save global variables into local variables
	 *
	 * @return RessourceTest
	 */
	function __construct()
	{
		//$this->sharedFixture
		global $conf;//f,$user,$langs,$db;
		$this->conf=$conf;
		/*$this->savuser=$user;
		$this->savlangs=$langs;
		$this->savdb=$db;*/
		print 'construct';
		print __METHOD__."\n";// db->type=".$db->type." user->id=".$user->id;
		//print " - db ".$db->db;
		print "\n";
	}

	// Static methods
  	public static function setUpBeforeClass()
    {
    	//global $conf,$user,$langs,$db;
		//$this->db = new TPDOdb;
		print 'ca commence !\n';
		print __METHOD__."\n";
    }
    public static function tearDownAfterClass()
    {
    	//global $conf,$user,$langs,$db;
		//$this->db->close();
		print "cest fini";
		print __METHOD__."\n";
    }

	/**
	 * Init phpunit tests
	 *
	 * @return	void
	 */
    protected function setUp()
    {
    	/*global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;*/

		print __METHOD__."\n";
    }

	/**
	 * End phpunit tests
	 *
	 * @return	void
	 */
    protected function tearDown()
    {
    	print __METHOD__."\n";
    }

    /**
     * testRessourceCreate
     *
     * @return	void
     */
    public function testRessourceCreate()
    {
    	/*global $conf,$user,$langs,$db;
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;

		$localobject=new User($this->savdb);
    	$localobject->initAsSpecimen();
    	$result=$localobject->create($user);

    	$this->assertLessThan($result, 0);*/
    	$result = 'lol';
    	print 'Test Création de Ressource\n';
    	print __METHOD__."\n";
    	
    }

    
   

}
?>