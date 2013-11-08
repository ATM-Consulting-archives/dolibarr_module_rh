<?php
/* Copyright (C) 2010-2012	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2011-2012	Regis Houssin		<regis.houssin@capnetworks.com>
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
 *      \file       test/phpunit/AllTest.php
 *		\ingroup    test
 *      \brief      This file is a test suite to run all unit tests
 *		\remarks	To run this script as CLI:  phpunit filename.php
 */
 
 global $conf,$user,$langs,$db;
//inclusion de config des tests.
require('./config.php');
require('../lib/ressource.lib.php');
require('../class/ressource.class.php');
require('../class/evenement.class.php');
require('../class/contrat.class.php');

$ress = new TRH_Ressource;
$type = new TRH_Ressource_type;
$field = new TRH_Ressource_field;
$typeEvent = new TRH_Type_Evenement;
$event = new TRH_Evenement;
$contrat = new TRH_Contrat;
$ATMdb = new TPDOdb;



/**
 * Class for the All test suite
 */
class AllTests
{
    /**
     * Function suite to make all PHPUnit tests
     *
     * @return	void
     */
	public static function suite()
    {
		$suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
		
		require_once './RessourceTest.php';
		$suite->addTestSuite('RessourceTest');
		
		require_once './LibRessourceTest.php';
		$suite->addTestSuite('LibRessourceTest');
		
		require_once './TypeRessourceTest.php';
		$suite->addTestSuite('TypeRessourceTest');
		
		require_once './EvenementTest.php';
		$suite->addTestSuite('EvenementTest');
		
		require_once './ContratTest.php';
		$suite->addTestSuite('ContratTest');
		
        return $suite;
    }
}

?>
