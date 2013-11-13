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




//global $conf,$user,$langs,$db;
//inclusion de config des tests.
/*require('./config.php');
require('../class/contrat.class.php');*/


$contrat = new TRH_Contrat;
$ATMdb = new TPDOdb;


/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class ContratTest extends PHPUnit_Framework_TestCase
{
		
		
	public static function setUpBeforeClass()
    {
        print "Début du test des Contrats.\n";
    }
 
    public static function tearDownAfterClass()
    {
    	global $ATMdb;
		print "\nFin du test des Contrats.\n";
    }
	
	public function testcreateContrat()
    {
    	global $contrat;
		$contrat = new TRH_Contrat;
		$this->assertNotNull($contrat);
		print __METHOD__."\n";
    }
	
	public function testload_liste()
    {
    	global $contrat, $ATMdb;
		$contrat->load_liste($ATMdb);
		$this->assertNotEmpty($contrat->TTypeRessource);
		$this->assertNotEmpty($contrat->TAgence);
		$this->assertNotEmpty($contrat->TFournisseur);
		$this->assertNotEmpty($contrat->TTVA);
		print __METHOD__."\n";
    }
	
	public function testSaveDelete()
    {
    	global $contrat, $ATMdb;
		
		//cas particulier de non-concordance des dates
		$contrat->date_fin = 10;
		$contrat->date_debut = 20;
		
		$contrat->save($ATMdb);
		$contrat->delete($ATMdb);
		
		print __METHOD__."\n";
    }
	
	public function testCreateAssoc()
    {
    	global $contrat, $ATMdb;
		$assoc = new TRH_Contrat_Ressource;
		$assoc->save($ATMdb);
		$assoc->delete($ATMdb);
		print __METHOD__."\n";
    }
	
	
}
