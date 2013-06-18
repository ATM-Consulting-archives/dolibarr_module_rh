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
		
		// ---- Formations
		$url = DOLHTTP."/competence/script/interface.php?get=formation&fk_user=".$fk_user."&date_debut=".$date_debut."&date_fin=".$date_fin;
		$resultForm = file_get_contents($url);
		$TForm = unserialize($resultForm);
		$this->assertNotEmpty($TForm);
		
		// ---- Rémunérations
		$url = DOLHTTP."/competence/script/interface.php?get=remuneration&fk_user=".$fk_user."&date_debut=".$date_debut."&date_fin=".$date_fin;
		$resultRem = file_get_contents($url);
		$TRem = unserialize($resultRem);
		$this->assertNotEmpty($TRem);
		
		// ---- DIF
		$url = DOLHTTP."/competence/script/interface.php?get=dif&fk_user=".$fk_user."&date_debut=".$date_debut."&date_fin=".$date_fin;
		$resultDif = file_get_contents($url);
		$TDif = unserialize($resultDif);
		$this->assertNotEmpty($TDif);
		
		print __METHOD__."\n";
	}*/
	
	public function testFormation(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		$result = _formation($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testRemuneration(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		$result = _remuneration($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testDif(){
		global $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_fin=date('Y').'-12-30';
		
		$result = _dif($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
}
