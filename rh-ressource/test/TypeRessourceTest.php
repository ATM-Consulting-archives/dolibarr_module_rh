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




global $conf,$user,$langs,$db;
//inclusion de config des tests.
require('./config.php');
require('../lib/ressource.lib.php');
require('../class/ressource.class.php');
//require('../class/evenement.class.php');
//require('../class/contrat.class.php');


$type = new TRH_Ressource_type;
$ATMdb = new TPDOdb;


/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class TypeRessourceTest extends PHPUnit_Framework_TestCase
{
		
		
	public static function setUpBeforeClass()
    {
        print "Début du test de Type de Ressource.\n";
		
    }
 
    public static function tearDownAfterClass()
    {
    	global $ATMdb;
        $ATMdb->close();
		print "\nFin du test de Type de Ressource.\n";
    }
	
	public function testcreateTypeRessource()
    {
    	global $type;
		$this->assertNotNull($type);
		$this->assertCount(6, $type->TType);
		print __METHOD__."\n";
    }
	
	public function testload_by_code(){
		global $type, $ATMdb;
		$ret = $type->load_by_code($ATMdb, 'voiture');
		$this->assertTrue($ret);
		$ret = $type->load_by_code($ATMdb, 'nimportequoi');
		$this->assertFalse($ret);
		print __METHOD__."\n";
	}
	
	public function testchargement(){
		global $type, $ATMdb;
		$type = new TRH_Ressource_type;
		//test de chargement et save
		$type->chargement($ATMdb, 'testlibelle', 'testcode', 1);
		$this->assertEquals('testlibelle', $type->libelle);
		$this->assertEquals('testcode', $type->code);
		$this->assertEquals(1, $type->supprimable);
		
		//test de suppression d'un supprimable
		$ret = $type->delete($ATMdb);
		$this->assertTrue($ret);
		
		//test de suppression d'un non supprimable
		$type->chargement($ATMdb, 'testlibelle', 'testcode', 0);
		$ret = $type->delete($ATMdb);
		$this->assertFalse($ret);
		print __METHOD__."\n";
	}
	
	public function testcode_format(){
		global $type, $ATMdb;
		$ret = TRH_Ressource_type::code_format('Habééé o lo');
		$this->assertEquals('habolo', $ret);
		print __METHOD__."\n";
	}
	
	public function testisUsedByRessource(){
		global $type, $ATMdb;
		
		//cas vrai : il y a des voitures
		$type->load_by_code($ATMdb, 'voiture');
		$ret = $type->isUsedByRessource($ATMdb);
		$this->assertTrue($ret);
		//cas faux : il n'y a pas de badges area.
		$type->load_by_code($ATMdb, 'badgearea');
		$ret = $type->isUsedByRessource($ATMdb);
		$this->assertFalse($ret);
		print __METHOD__."\n";
	}
	
	public function testload_field(){
		global $type, $ATMdb;
		
		$type->load_by_code($ATMdb, 'voiture');
		$sqlReq="SELECT COUNT(rowid) as 'nb' FROM ".MAIN_DB_PREFIX."rh_ressource_field WHERE fk_rh_ressource_type=".$type->getId();
		$ATMdb->Execute($sqlReq);
		if ($ATMdb->Get_line()) {$nb= intval($ATMdb->Get_field('nb'));}
		
		$ret = $type->load_field($ATMdb);
		$this->assertCount( $nb, $type->TField);
		
		print __METHOD__."\n";
	}
	
	
	public function testField(){
		global $type, $ATMdb;
		$type->load_by_code($ATMdb, 'voiture');
		$TNField = array("code"=>'testField'
						,"ordre"=>100
						,"libelle"=>'testField'
						,"type"=>'chaine'
						,"options"=>""
						,"obligatoire"=>1);
		//ajout
		$idField = $type->addField($ATMdb, $TNField);
		$this->assertNotEquals(0, $idField);
		
		//suppression
		$ret = $type->delField($ATMdb, $idField);
		$this->assertTrue($ret);
		print __METHOD__."\n";
	}
	

	
	
	
}
