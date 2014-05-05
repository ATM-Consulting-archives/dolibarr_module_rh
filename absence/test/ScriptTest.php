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

$absence=new TRH_Absence;
$ATMdb=new TPDOdb;

/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class ScriptTest extends PHPUnit_Framework_TestCase
{
	/*public function testInterface(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		// ---- Jours de congés
		$url = DOLHTTP."/absence/script/interface.php?get=conges&fk_user=".$fk_user."&date_debut=".$date_debut."&date_fin=".$date_fin;
		$resultConges = file_get_contents($url);
		$TConges = unserialize($resultConges);
		$this->assertNotEmpty($TConges);
		
		// ---- Jours d'ancienneté
		$url = DOLHTTP."/absence/script/interface.php?get=jour_anciennete&fk_user=".$fk_user;
		$resultJourAnc = file_get_contents($url);
		$TJourAnc = unserialize($resultJourAnc);
		$this->assertNotEmpty($TJourAnc);
		
		// ---- Maladies maintenues
		$url = DOLHTTP."/absence/script/interface.php?get=maladie_maintenue&fk_user=".$fk_user."&date_debut=".$date_debut."&date_fin=".$date_fin;
		$resultMadMaint = file_get_contents($url);
		$TMadMaint = unserialize($resultMadMaint);
		$this->assertNotEmpty($TMadMaint);
		
		// ---- Maladies non maintenues
		$url = DOLHTTP."/absence/script/interface.php?get=maladie_non_maintenue&fk_user=".$fk_user."&date_debut=".$date_debut."&date_fin=".$date_fin;
		$resultMadNonMaint = file_get_contents($url);
		$TMadNonMaint = unserialize($resultMadNonMaint);
		$this->assertNotEmpty($TMadNonMaint);
		
		print __METHOD__."\n";
	}*/
	
	public function testJourAnciennete(){
		global $ATMdb;
		
		$fk_user=3;
		
		$result = _jourAnciennete($ATMdb, $fk_user);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testDureeMaladieMaintenue(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		$result = _dureeMaladieMaintenue($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testDureeMaladieNonMaintenue(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		$result = _dureeMaladieNonMaintenue($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testConges(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		$result = _conges($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
}
