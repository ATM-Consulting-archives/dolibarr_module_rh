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
require('../lib/competence.lib.php');
require('../class/competence.class.php');
require('../script/interface.php');

global $conf,$user,$langs,$db;

$cv=new TRH_ligne_cv;
$formation=new TRH_formation_cv;
$competence=new TRH_competence_cv;
$remuneration = new TRH_remuneration;
$dif = new TRH_dif;

$ATMdb = new Tdb;


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
		
		require_once './CompetenceTest.php';
		$suite->addTestSuite('CompetenceTest');
		
		require_once './ScriptTest.php';
		$suite->addTestSuite('ScriptTest');
		
        return $suite;
    }
}

?>
