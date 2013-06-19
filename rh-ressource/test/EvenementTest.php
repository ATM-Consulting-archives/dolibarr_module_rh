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
require('../lib/ressource.lib.php');
require('../class/evenement.class.php');*/


$event = new TRH_Evenement;
$typeEvent = new TRH_Type_Evenement;
$ATMdb = new TPDOdb;


/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class EvenementTest extends PHPUnit_Framework_TestCase
{
		
		
	public static function setUpBeforeClass()
    {
        print "Début du test des Evenements.\n";
    }
 
    public static function tearDownAfterClass()
    {
    	global $ATMdb;
		print "\nFin du test des Evenements.\n";
    }
	
	public function testcreateEvent()
    {
    	global $event;
		$event = new TRH_Evenement;
		$this->assertNotNull($event);
		print __METHOD__."\n";
    }
	
	public function testLoad_liste()
    {
    	global $event, $ATMdb;
		$event->load_liste($ATMdb);
		$this->assertNotEmpty($event->TTVA);
		$this->assertNotEmpty($event->TUser);
		print __METHOD__."\n";
    }
	
	public function testload_liste_type()
    {
    	global $event, $ATMdb;
		$event->fk_rh_ressource_type = 1;
		$event->load_liste_type(1);
		$this->assertNotEmpty($event->TType);
		print __METHOD__."\n";
    }
	
	
	public function testSaveDelete()
    {
    	global $event, $ATMdb;
		
		//cas particulier de non-concordance des dates
		$event->date_fin = 10;
		$event->date_debut = 20;
		$event->type = 'emprunt';
		$event->save($ATMdb);
		$sqlReq="SELECT type FROM ".MAIN_DB_PREFIX."rh_evenement WHERE rowid= ".$event->getId();
		$ATMdb->Execute($sqlReq);
		if ($row = $ATMdb->Get_line()) {$this->assertEquals('emprunt', $row->type);}
		
		$event->type = 'accident';
		$event->save($ATMdb);
		$event->type = 'reparation';
		$event->save($ATMdb);
		$event->type = 'facture';
		$event->save($ATMdb);
		$event->type = 'divers';
		$event->save($ATMdb);
		
		$event->delete($ATMdb);
		
		$sqlReq="SELECT COUNT(rowid) as 'nb' FROM ".MAIN_DB_PREFIX."rh_evenement WHERE rowid= ".$event->getId();
		$ATMdb->Execute($sqlReq);
		if ($row = $ATMdb->Get_line()) {$this->assertEquals(0, $row->nb);}
				
		print __METHOD__."\n";
    }
	
	
	//TEST DE LA CLASSE TYPE D4EVENEMENT
	public function testcreateTypeRessource()
    {
    	global $typeEvent;
		$this->assertNotNull($typeEvent);
		print __METHOD__."\n";
    }
	
	public function testload_by_code(){
		global $typeEvent, $ATMdb;
		$ret = $typeEvent->load_by_code($ATMdb, 'accident');
		$this->assertTrue($ret);
		$ret = $typeEvent->load_by_code($ATMdb, 'innexistantnimpotequoi');
		$this->assertFalse($ret);
		print __METHOD__."\n";
	}
	
	public function testchargement(){
		global $typeEvent, $ATMdb;
		$typeEvent = new TRH_Type_Evenement;
		//test de chargement et save
		$typeEvent->chargement($ATMdb, 'testlibelle', 'testcode', '1234', 0, 0);
		$this->assertEquals('testlibelle', $typeEvent->libelle);
		$this->assertEquals('testcode', $typeEvent->code);
		$this->assertEquals('1234', $typeEvent->codecomptable);
		$this->assertEquals('vrai', $typeEvent->supprimable);
		$this->assertEquals(0, $typeEvent->fk_rh_ressource_type);
		//suppression de l'élément de test qui est vide
		$typeEvent->delete($ATMdb);
	}
	
	public function testsave(){
		global $typeEvent, $ATMdb;	
		//save et suppression de l'élément de test qui est vide
		$typeEvent = new TRH_Type_Evenement;
		$typeEvent->save($ATMdb);
		$this->assertEquals('vrai', $typeEvent->supprimable);
		$this->assertEquals(0, $typeEvent->fk_rh_ressource_type);
		
		$typeEvent->delete($ATMdb);
		
		
		print __METHOD__."\n";
	}
	
	
}
