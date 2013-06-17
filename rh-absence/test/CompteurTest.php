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



/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class CompteurTest extends PHPUnit_Framework_TestCase
{
	protected $id;
	public function testCompteur()
	
    {
    	$compteur=new TRH_Compteur;
		$this->assertNotNull($compteur);
		
		$compteur->initCompteur($ATMdb, $idUser);
		$this->assertNotNull($compteur);
		
		
		
		$ATMdb=new Tdb;
		$id = $compteur->load_by_fkuser($ATMdb, $user->id);
		$this->assertNotNull($id);
		
		$id = $compteur->load_by_fkuser($ATMdb, 'testFaux');
		$this->assertEquals(0,$id);

		
		print __METHOD__."\n";
	}


}
