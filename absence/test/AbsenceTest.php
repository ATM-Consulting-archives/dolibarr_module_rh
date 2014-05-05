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
class AbsenceTest extends PHPUnit_Framework_TestCase
{
	protected $id;
	
	
	public function testCompteur()
	
    {
    	global $ATMdb;
    	//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}
		
		
    	$compteur=new TRH_Compteur;
		$this->assertNotNull($compteur);
		
		$compteur->initCompteur($ATMdb, $idUser);
		$this->assertNotNull($compteur);
		
		
		
		$ATMdb=new TPDOdb;
		$id = $compteur->load_by_fkuser($ATMdb, $idTestUser);
		$this->assertNotNull($id);
		
		$id = $compteur->load_by_fkuser($ATMdb, 'testFaux');
		$this->assertEquals(0,$id);

		$compteur=new TRH_Compteur;
		$compteur->save($ATMdb);
		$compteur->load($ATMdb,$compteur->getId());
		$compteur->delete($ATMdb);
		
		print __METHOD__."\n";
	}
	
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

		//on crée une règle pour l'utilisateur
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_regle (`rowid` ,`date_cre` ,`date_maj` ,`typeAbsence` ,
		`nbJourCumulable` ,`fk_user` ,`fk_usergroup` ,`entity` ,`choixApplication` ,`restrictif`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'rttcumule', '10', NULL , NULL , '0', 'all', NULL
		)";
		$ATMdb->Execute($sql);
		
		
		$TRegle = $absence->recuperationRegleUser($ATMdb, $idTestUser);
		$this->assertNotNull($TRegle);
		
		$TRegle = $absence->recuperationRegleUser($ATMdb, 10000000);
		$this->assertNotNull($TRegle);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_regle WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."llx_rh_absence (`rowid` ,`date_cre` ,`date_maj` ,`code` ,`type` ,`libelle` ,
		`date_debut` ,`date_fin` ,`ddMoment` ,`dfMoment` ,`duree` ,`commentaire` ,`etat` ,`libelleEtat` ,`fk_user` ,`entity` ,`dureeHeure` ,`avertissement` ,
		`niveauValidation` ,`dureeHeurePaie` ,`commentaireValideur` ,`idAbsImport`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '950', 'rttcumule', NULL , '0000-00-00 00:00:00', 
		'0000-00-00 00:00:00', NULL , NULL , NULL , NULL , NULL , NULL ,".$idTestUser.", '1', NULL , NULL , NULL , NULL , NULL , NULL
		)";
		$ATMdb->Execute($sql);
		
		$Tabs = $absence->recuperationDerAbsUser($ATMdb, 10000000);
		$this->assertNotNull($Tabs);
		
		$Tabs = $absence->recuperationDerAbsUser($ATMdb, $idTestUser);
		$this->assertNotNull($Tabs);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		$testDemande = $absence->testDemande($ATMdb, $idTestUser, $absence);
		$this->assertNotNull($testDemande);
		
		$absence->type="rttcumule";
		$testDemande = $absence->testDemande($ATMdb, $idTestUser, $absence);
		$this->assertNotNull($testDemande);
		
		$absence->type="rttnoncumule";
		$testDemande = $absence->testDemande($ATMdb, $idTestUser, $absence);
		$this->assertNotNull($testDemande);
		
		$absence->type="conges";
		$testDemande = $absence->testDemande($ATMdb, $idTestUser, $absence);
		$this->assertNotNull($testDemande);
		
		$testDemande = $absence->testDemande($ATMdb, 'testFaux', $absence);
		$this->assertNotNull($testDemande);
		
		
		//test load_by_idImport
		$testDemande = $absence->load_by_idImport($ATMdb, 100000);
		$this->assertNotNull($testDemande);

		//on charge une absence pour faire les tests
		$sql="SELECT rowid,idAbsImport,fk_user, date_debut FROM ".MAIN_DB_PREFIX."rh_absence";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idAbsenceImport=$ATMdb->Get_field('idAbsImport');
			$idAbsenceTest=	$ATMdb->Get_field('rowid');
			$fk_userTest=$ATMdb->Get_field('fk_user');
			$date_debutTest=$ATMdb->Get_field('date_debut');
		}
		
		$testDemande = $absence->load_by_idImport($ATMdb, 'idAbsImport');
		$this->assertNotNull($testDemande);
		
		$testDemande = $absence->testExisteDeja($ATMdb, $absence);
		$this->assertEquals(0,$testDemande);


		$absence=new TRH_Absence;
		$absence->save($ATMdb);
		$absence->load($ATMdb,$absence->getId());
		$absence->delete($ATMdb);
		
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
		
		
		/////////////////	TESTS CALCULJOURSTRAVAILLES
		$duree=0;
		$absence->fk_user=3;
		$duree= $absence->calculJoursTravailles($ATMdb, $duree, time(), time()+2*3600*24, $absence);
		$this->assertNotNull($duree);
		
		
		//on charge une absence pour faire les tests
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idAbsence=$ATMdb->Get_field('rowid');	
		}
		$absence->load($ATMdb,$idAbsence);
		$avertissement = $absence->dureeAbsenceRecevable($ATMdb);
		$this->assertEquals(1,$avertissement);
		
		/*$avertissement = $absence->isWorkingDayNext($ATMdb, time());
		$this->assertEquals(1,$avertissement);
		
		$avertissement = $absence->isWorkingDayPrevious($ATMdb, time());
		$this->assertEquals(1,$avertissement);*/
		
		
		//test fonction RecreditHeure
		$absence->etat='Refusee';
		$duree = $absence->recrediterHeure($ATMdb);
		
		$absence->etat='Acceptee';
		$absence->type='rttcumule';
		$duree = $absence->recrediterHeure($ATMdb);
		
		$absence->etat='Acceptee';
		$absence->type='rttnoncumule';
		$duree = $absence->recrediterHeure($ATMdb);
		
		$absence->etat='Acceptee';
		$absence->type='conges';
		$duree = $absence->recrediterHeure($ATMdb);

		
		// Duree absence recevable
		//on crée une règle pour l'utilisateur
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_regle (`rowid` ,`date_cre` ,`date_maj` ,`typeAbsence` ,
		`nbJourCumulable` ,`fk_user` ,`fk_usergroup` ,`entity` ,`choixApplication` ,`restrictif`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'rttcumule', '10', NULL , NULL , '0', 'all',NULL
		)";
		$ATMdb->Execute($sql);
		
		$absence->type='rttcumule';
		$absence->duree=20;
		$recevable=$absence->dureeAbsenceRecevable($ATMdb);
		$this->assertEquals(2,$recevable);
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_regle WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		// Duree absence recevable
		//on crée une règle pour l'utilisateur
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_regle (`rowid` ,`date_cre` ,`date_maj` ,`typeAbsence` ,
		`nbJourCumulable` ,`fk_user` ,`fk_usergroup` ,`entity` ,`choixApplication` ,`restrictif`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'rttcumule', '10', NULL , NULL , '0', 'all',1
		)";
		$ATMdb->Execute($sql);
		
		$absence->type='rttcumule';
		$absence->duree=20;
		$recevable=$absence->dureeAbsenceRecevable($ATMdb);
		$this->assertEquals(0,$recevable);
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_regle WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		
		print __METHOD__."\n";
	}
	
	public function testCalculJoursTravaillesAbsence()
    {
    	global $ATMdb, $absence;
		
		
		//TEST 1
		$absence->fk_user=3;
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, $date_debut, $date_fin, $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		
		//TEST 2
		$absence->fk_user=3;
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		//TEST 3
		$absence->fk_user=3;
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		//TEST 4
		$absence->fk_user=3;
		$absence->ddMoment='matin';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		//TEST 4
		$absence->fk_user=3;
		$absence->dfMoment='matin';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
	
		//TEST 5
		$absence->fk_user=3;
		$absence->ddMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);

		//TEST 5
		$absence->fk_user=3;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time()+10*3600*24)." 00:00:00"), $absence);
		
		
		//TEST 6
		$absence->fk_user=3;
		$absence->ddMoment='apresmidi';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
	
		//TEST 7
		$absence->fk_user=3;
		$absence->ddMoment='apresmidi';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$absence->date_fin=strtotime(date('Y-m-d',time()+3600*24*2)." 00:00:00");
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		
		
		//TEST 8
		$absence->fk_user=3;
		$absence->ddMoment='apresmidi';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$absence->date_fin=strtotime(date('Y-m-d',time()+3600*24*2)." 00:00:00");
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10001', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time()+3600*24)." 00:00:00', 'apresmidi', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10002', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time()+2*3600*24)." 00:00:00', 'apresmidi', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		
		
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, $absence->date_debut, $absence->date_fin, $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10001";
		$ATMdb->Execute($sql);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10002";
		$ATMdb->Execute($sql);
		
		//TEST 9
		$absence->fk_user=3;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime(date('Y-m-d',time())." 00:00:00");
		$absence->date_fin=strtotime(date('Y-m-d',time()+3600*24*5)." 00:00:00");
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10001', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time()+3600*24)." 00:00:00', 'apresmidi', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10002', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time()+2*3600*24)." 00:00:00', 'matin', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);
		
		
		$duree=$absence->calculJoursTravailles($ATMdb, $duree, $absence->date_debut, $absence->date_fin, $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10001";
		$ATMdb->Execute($sql);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10002";
		$ATMdb->Execute($sql);
		
		
		//TEST 10
		$absence->fk_user=3;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime("2013-15-06 00:00:00");
		$absence->date_fin=strtotime("2013-15-06 00:00:00");
		
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		
		$ATMdb->Execute($sql);

		$duree=$absence->calculJoursTravailles($ATMdb, $duree, $absence->date_debut, $absence->date_fin, $absence);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		//TEST 11
		$absence->fk_user=3;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$absence->date_debut=strtotime("2013-15-06 00:00:00");
		$absence->date_fin=strtotime("2013-15-06 00:00:00");

		$duree=$absence->calculJoursTravailles($ATMdb, $duree, $absence->date_debut, $absence->date_fin, $absence);
		
		
	
}
	
	
	
	public function testFctJFeriesAbsence()
    {
    	global $ATMdb, $absence;
		
		//////////////////	CALCUL DES JOURS FERIES 
		$duree=0;
		$duree = $absence->calculJoursFeries($ATMdb, $duree, time(), time()+3600*24, $absence);
		$this->assertEquals(0,$duree);

		/////////////////////TEST 1 
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, time()-2*3600*24, time()+10*3600*24, $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 2 
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, time()-2*3600*24, time()+10*3600*24, $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		
		/////////////////////TEST 3
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 4
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 5
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 6
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='apresmidi';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 7
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 8
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 9
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time()+3600*24)." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 10
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time()+3600*24)." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 11
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time()+3600*24)." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 12
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time()+3600*24)." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 12 bis
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;
		$absence->ddMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time())." 00:00:00"), strtotime(date('Y-m-d',time()+3600*24)." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 13
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;

		$absence->dfMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time()-3600*24)." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 14
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;

		$absence->dfMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time()-3600*24)." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 15
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;

		$absence->dfMoment='matin';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time()-3600*24)." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 16
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'apresmidi', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;

		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time()-3600*24)." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 17
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'matin', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;

		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time()-3600*24)." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		/////////////////////TEST 18
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);

		$duree=0;

		$absence->dfMoment='apresmidi';
		$duree = $absence->calculJoursFeries($ATMdb, $duree, strtotime(date('Y-m-d',time()-3600*24)." 00:00:00"), strtotime(date('Y-m-d',time())." 00:00:00"), $absence);
		$this->assertNotNull($duree);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		
		
		// TEST d'ajout de jours fériés
		$feries=new TRH_JoursFeries;
		$feries->date_jourOff=strtotime(date('Y-m-d',time())." 00:00:00");
		$testAjout=$feries->testExisteDeja($ATMdb, $feries);
		$this->assertEquals(0,$testAjout);
		
		// TEST d'ajout de jours fériés
		$feries=new TRH_JoursFeries;
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time())." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);
		$feries->date_jourOff=strtotime(date('Y-m-d',time())." 00:00:00");
		
		$testAjout=$feries->testExisteDeja($ATMdb, $feries);
		$this->assertEquals(1,$testAjout);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
		
		// TEST d'ajout de jours fériés
		$feries=new TRH_JoursFeries;
		$sql="INSERT INTO ".MAIN_DB_PREFIX."rh_absence_jours_feries (
		`rowid` ,`date_cre` ,`date_maj` ,`date_jourOff` ,`moment` ,`commentaire` ,`entity`
		)
		VALUES (
		'10000', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".date('Y-m-d',time()+2*3600*24)." 00:00:00', 'allday', NULL , '0'
		)";
		$ATMdb->Execute($sql);
		$feries->date_jourOff=strtotime(date('Y-m-d',time())." 00:00:00");
		
		$testAjout=$feries->testExisteDeja($ATMdb, $feries);
		$this->assertEquals(0,$testAjout);
		
		$sql="DELETE FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries WHERE rowid=10000";
		$ATMdb->Execute($sql);
	}
	
	
	
	public function testFctUtilitaireAbsence()
    {
    	global $ATMdb, $absence;
		
		$testAbsence= new TRH_Absence;
		$this->assertNotNull($testAbsence);
		
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
		
		$heure = $absence->additionnerHeure('8:45', '8:45');
		
		
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

	public function testFctRechercheAbsence()
	{
		global $ATMdb, $absence;
		$recherche=$absence->requeteRechercheAbsence($ATMdb, $idGroupeRecherche, $idUserRecherche, 1, $date_debut, $date_fin, $typeAbsence);
		$this->assertNotNull($recherche);
		
		$recherche=$absence->requeteRechercheAbsence($ATMdb, $idGroupeRecherche, 10000, 1, $date_debut, $date_fin, $typeAbsence);
		$this->assertNotNull($recherche);
		
		$recherche=$absence->requeteRechercheAbsence($ATMdb, 10000, $idUserRecherche, 1, $date_debut, $date_fin, $typeAbsence);
		$this->assertNotNull($recherche);
		
		$recherche=$absence->requeteRechercheAbsence($ATMdb, 10000, $idUserRecherche, 0, $date_debut, $date_fin, $typeAbsence);
		$this->assertNotNull($recherche);
		
		$recherche=$absence->requeteRechercheAbsence($ATMdb, 0, 10000, 0, $date_debut, $date_fin, $typeAbsence);
		$this->assertNotNull($recherche);
	}

	
	
	public function testEmploiTemps()
    {
    	global $ATMdb, $edt;
		
		$testEdt=new TRH_EmploiTemps;
		$this->assertNotNull($testEdt);
		
		$test=$testEdt->initCompteurHoraire ($ATMdb, 10000);
		
		$test=$testEdt->razCheckbox($ATMdb, $testEdt);
		
		//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}
	
		$test = $edt->loadByuser($ATMdb, $idTestUser);
		$this->assertNotNull($test);
		
		$test = $edt->loadByuser($ATMdb, 10000000);
		$this->assertEquals(0,$test);
		
		$test = $edt->load_entities($ATMdb);
		$this->assertNotNull($test);
		
		$edt->load($ATMdb, $idTestUser);
		$test = $edt->calculTempsHebdo($ATMdb, $edt);
		$this->assertEquals(37,$test);
		
		$test = $edt->load_by_fkuser($ATMdb, $idTestUser);
		$this->assertNotNull($test);
		
		$test = $edt->load_by_fkuser($ATMdb, 10000000);
		$this->assertEquals(0,$test);
		
		$testEdt=new TRH_EmploiTemps;
		$testEdt->save($ATMdb);
		$testEdt->load($ATMdb,$testEdt->getId());
		$testEdt->delete($ATMdb);
		

	}

	public function testTypeAbsence()
    {
    	global $ATMdb;
		
		$testType=new TRH_TypeAbsence;
		$this->assertNotNull($testType);
		
		$testType=new TRH_TypeAbsence;
		$testType->save($ATMdb);
		$testType->load($ATMdb,$testType->getId());
		$testType->delete($ATMdb);
		
	}
	
	public function testRegleAbsence()
    {
    	global $ATMdb;
		
		$testRegle=new TRH_RegleAbsence;
		$this->assertNotNull($testRegle);
		
		
		$testRegle->load_liste($ATMdb);
		$this->assertNotNull($testRegle);
		
		$testRegle=new TRH_RegleAbsence;
		$testRegle->save($ATMdb);
		$testRegle->load($ATMdb,$testRegle->getId());
		$testRegle->delete($ATMdb);
		
		$testRegle=new TRH_RegleAbsence;
		$testRegle->choixApplication='user';
		$testRegle->save($ATMdb);
		$testRegle->load($ATMdb,$testRegle->getId());
		$testRegle->delete($ATMdb);
		
		$testRegle=new TRH_RegleAbsence;
		$testRegle->choixApplication='group';
		$testRegle->save($ATMdb);
		$testRegle->load($ATMdb,$testRegle->getId());
		$testRegle->delete($ATMdb);
		
		$testRegle=new TRH_RegleAbsence;
		$testRegle->choixApplication='test';
		$testRegle->save($ATMdb);
		$testRegle->load($ATMdb,$testRegle->getId());
		$testRegle->delete($ATMdb);
	}
	
	public function testJFeries()
    {
    	global $ATMdb;
		
		$testJF=new TRH_JoursFeries;
		$this->assertNotNull($testJF);

		$testJF=new TRH_JoursFeries;
		$testJF->save($ATMdb);
		$testJF->load($ATMdb,$testJF->getId());
		$testJF->delete($ATMdb);
		
	}
	
	public function testAdminCompteur()
    {
    	global $ATMdb;
		
		$testAdmin=new TRH_AdminCompteur;
		$this->assertNotNull($testAdmin);
		
		$testAdmin=new TRH_AdminCompteur;
		$testAdmin->save($ATMdb);
		$testAdmin->load($ATMdb,$testAdmin->getId());
		$testAdmin->delete($ATMdb);

	}
	
}
