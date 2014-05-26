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
require('../lib/valideur.lib.php');
require('../class/valideur.class.php');
require('../class/analytique_user.class.php');
require('../class/actions_valideur.class.php');

dol_include_once('/ndfp/class/ndfp.class.php');

$ATMdb=new TPDOdb;
$valideur=new TRH_valideur_groupe;
$analytique_user=new TRH_analytique_user;

/**
 * Class for PHPUnit tests
 *
 * @backupGlobals disabled
 * @backupStaticAttributes enabled
 * @remarks	backupGlobals must be disabled to have db,conf,user and lang not erased.
 */
class ValideurTest extends PHPUnit_Framework_TestCase
{
	protected $id;
	
	public function testSendMail()
    {
    	global $ATMdb;
		
		$ndfp= new Ndfp($ATMdb);
		
    	// Envoi mail - Accepté
		$ndfp->fk_user=1;
		$ndfp->ref='NF1305-0042';
		$ndfp->statut=1;
		$ndfp->total_ttc=155.2;
		
		$id = send_mail($ATMdb,$ndfp,$user,$langs,1);
		$this->assertEquals(1,$id);
		
		// Envoi mail - Soumis à validation
		$ndfp->fk_user=1;
		$ndfp->ref='NF1305-0042';
		$ndfp->statut=4;
		$ndfp->total_ttc=155.2;
		
		$id = send_mail($ATMdb,$ndfp,$user,$langs,4);
		$this->assertEquals(1,$id);
		
		// Envoi mail - Refusé
		$ndfp->fk_user=1;
		$ndfp->ref='NF1305-0042';
		$ndfp->statut=3;
		$ndfp->total_ttc=155.2;
		
		$id = send_mail($ATMdb,$ndfp,$user,$langs,3);
		$this->assertEquals(1,$id);
		
		// Envoi mail - Remboursé
    	$ndfp->fk_user=1;
		$ndfp->ref='NF1305-0042';
		$ndfp->statut=2;
		$ndfp->total_ttc=155.2;
		
		$id = send_mail($ATMdb,$ndfp,$user,$langs,2);
		$this->assertEquals(1,$id);
		
		print __METHOD__."\n";
	}

	public function testFormObjectOptions(){
		
	}
}
