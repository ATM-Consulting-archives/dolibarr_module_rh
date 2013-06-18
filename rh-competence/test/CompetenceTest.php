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
class CompetenceTest extends PHPUnit_Framework_TestCase
{
		
	public static function setUpBeforeClass()
    {
        print "Début du test du module Compétence.\n";
    }
 
    public static function tearDownAfterClass()
    {
		print "\nFin du test du module Compétence.\n";
    }
	
	public function testLoad_by_user_and_libelle_cv(){
		global $cv, $ATMdb;
		
		$fk_user=3;
		$libelleExperience='Test';
		
		$result = $cv->load_by_user_and_libelle($ATMdb, $fk_user, $libelleExperience);
		$this->assertNotNull($result);
		
		$result = $cv->load_by_user_and_libelle($ATMdb,0,'');
		$this->assertFalse($result);
		
		print __METHOD__."\n";
	}
	
	public function testLoad_by_user_and_libelle_formation(){
		global $formation, $ATMdb;
		
		$fk_user=3;
		$libelleFormation='Test';
		
		$result = $formation->load_by_user_and_libelle($ATMdb, $fk_user, $libelleFormation);
		$this->assertNotNull($result);
		
		$result = $formation->load_by_user_and_libelle($ATMdb,0,'');
		$this->assertFalse($result);
		
		print __METHOD__."\n";
	}
	
	public function testLoad_by_user_and_libelle_competence(){
		global $competence, $ATMdb;
		
		$fk_user=3;
		$libelleCompetence='Test';
		
		$result = $competence->load_by_user_and_libelle($ATMdb, $fk_user, $libelleCompetence);
		$this->assertNotNull($result);
		
		$result = $competence->load_by_user_and_libelle($ATMdb,0,'');
		$this->assertFalse($result);
		
		print __METHOD__."\n";
	}
	
	public function testReplaceEspaceEnPourcentage(){
		global $competence;
		
		$result = $competence->replaceEspaceEnPourcentage('Test1 ET Test2');
		$this->assertNotEmpty($result);
		
		print __METHOD__."\n";
	}
	
	public function testSeparerOu(){
		global $competence;
		
		$result = $competence->separerOu('Test1%ou%Test2');
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testSeparerEt(){
		global $competence;
		
		$result = $competence->separerEt('Test1%et%Test2');
		$this->assertNotNull($result);
		
		print __METHOD__."\n";
	}
	
	public function testRequeteRecherche(){
		global $competence, $ATMdb;
		
		$recherche=array('Test1%et%Test2');
		
		$result = $competence->requeteRecherche($ATMdb,$recherche);
		$this->assertNotEmpty($result);
		
		print __METHOD__."\n";
	}
	
	public function testRequeteStatistique(){
		global $competence, $ATMdb;
		
		$result = $competence->requeteStatistique($ATMdb,0,1,'Test');
		$this->assertNotEmpty($result);
		
		$result = $competence->requeteStatistique($ATMdb,1,1,'Test');
		$this->assertNotEmpty($result);
		
		$result = $competence->requeteStatistique($ATMdb,0,0,'Test');
		$this->assertNotEmpty($result);
		
		$result = $competence->requeteStatistique($ATMdb,1,0,'Test');
		$this->assertEmpty($result);
		
		print __METHOD__."\n";
	}

	public function testLoad_by_user_and_dates(){
		global $remuneration, $ATMdb;
		
		$fk_user=3;
		$date_debut=date('Y').'-01-02';
		$date_debut=strtotime($date_debut);
		$date_fin=date('Y').'-12-30';
		$date_fin=strtotime($date_fin);
		
		$result = $remuneration->load_by_user_and_dates($ATMdb, $fk_user, $date_debut, $date_fin);
		$this->assertNotNull($result);
		
		$result = $remuneration->load_by_user_and_dates($ATMdb,0,0,0);
		$this->assertFalse($result);
		
		print __METHOD__."\n";
	}
	
	public function testLoad_by_user_and_annee(){
		global $dif, $ATMdb;
		
		$fk_user=3;
		
		$result = $dif->load_by_user_and_annee($ATMdb, $fk_user, '2013');
		$this->assertNotNull($result);
		
		$result = $dif->load_by_user_and_annee($ATMdb,0,'');
		$this->assertFalse($result);
		
		print __METHOD__."\n";
	}
}
