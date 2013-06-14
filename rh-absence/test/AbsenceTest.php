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
require('../lib/absence.lib.php');
require('../class/absence.class.php');

$absence=new TRH_Absence;
$ATMdb=new Tdb;

/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class AbsenceTest extends PHPUnit_Framework_TestCase
{
	protected $id;
	
	public function testAbsence()
	
    {
    	global $ATMdb, $absence;
		
		//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}
		
		$TUser = $absence->recupererTUser($ATMdb);
		$this->assertNotEmpty($TUser);

		$TRegle = $absence->recuperationRegleUser($ATMdb, $idTestUser);
		$this->assertNotNull($TRegle);
		
		$TRegle = $absence->recuperationRegleUser($ATMdb, 'testFaux');
		$this->assertNotNull($TRegle);
		
		$Tabs = $absence->recuperationDerAbsUser($ATMdb, 'testFaux');
		$this->assertNotNull($Tabs);
		
		$Tabs = $absence->recuperationDerAbsUser($ATMdb, $idTestUser);
		$this->assertNotNull($Tabs);
		
		$testDemande = $absence->testDemande($ATMdb, $idTestUser, $absence);
		$this->assertNotNull($testDemande);
		
		$testDemande = $absence->testDemande($ATMdb, 'testFaux', $absence);
		$this->assertNotNull($testDemande);

		
		print __METHOD__."\n";
	}
	
	
	public function testDureeAbsence()
	
    {
    	global $ATMdb, $absence;
		
		//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}
		
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculDureeAbsence($ATMdb, time(), time(), $absence);
		$this->assertEquals(1,$duree);
		
		$absence->ddMoment='matin';
		$absence->dfMoment='matin';
		$duree = $absence->calculDureeAbsence($ATMdb, time(), time(), $absence);
		$this->assertEquals(0.5,$duree);
		
		$absence->ddMoment='apresmidi';
		$absence->dfMoment='matin';
		$duree = $absence->calculDureeAbsence($ATMdb, time(), time(), $absence);
		$this->assertEquals(0,$duree);
		
		$duree=0;
		$duree = $absence->calculJoursFeries($ATMdb, $duree, time(), time()+3600*24, $absence);
		$this->assertEquals(0,$duree);
		
		
		//on charge une absence pour faire les tests
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idAbsence=$ATMdb->Get_field('rowid');	
		}
		$absence->load($ATMdb,$idAbsence);
		$avertissement = $absence->dureeAbsenceRecevable($ATMdb);
		$this->assertEquals(1,$avertissement);
		
		$avertissement = $absence->isWorkingDayNext($ATMdb, time());
		$this->assertEquals(1,$avertissement);
		
		$avertissement = $absence->isWorkingDayPrevious($ATMdb, time());
		$this->assertEquals(1,$avertissement);
		
		
		
		

		
		print __METHOD__."\n";
	}
	
	public function testFctUtilitaireAbsence()
    {
    	global $ATMdb, $absence;
		
		//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}
	
		$heure = $absence->additionnerHeure(3, 5);
		$this->assertEquals('8:0',$heure);
		
		$heure = $absence->additionnerHeure($heure, 5);
		$this->assertEquals('13:0',$heure);
		
		
		$heure = $absence->horaireMinuteEnCentieme('7:15');
		$this->assertEquals('7.25',$heure);
		
		$heure = $absence->horaireMinuteEnCentieme('2:45');
		$this->assertEquals('2.75',$heure);
		
		$heure = $absence->jourSemaine(strtotime('14-06-2013'));
		$this->assertEquals('vendredi',$heure);
		
		//retourne la date au format "Y-m-d H:i:s"
		$heure = $absence->php2Date(strtotime('14-06-2013'));
		$this->assertEquals('2013-06-14 00:00:00',$heure);
		
		$heure = $absence->php2dmy(strtotime('14-06-2013'));
		$this->assertEquals('14/06/2013',$heure);

		$heure = $absence->difheure('00:00:00','14:15:00');
		$this->assertEquals('14:15:0',$heure);

		
	}
	
	
	
}
