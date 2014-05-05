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
class LibTest extends PHPUnit_Framework_TestCase
{
	protected $id;
	
	public function testMenusAbsence()
	
    {
    	global $ATMdb, $absence;
		
		//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}
		
		$view = absencePrepareHead($absence, 'absence') ;
		$this->assertNotNull($view);
		
		$view = absencePrepareHead($absence, 'absenceCreation') ;
		$this->assertNotNull($view);
		
		$view = absencePrepareHead($absence, 'test') ;


		$view = compteurPrepareHead($absence, 'compteur', $nomUser, $prenomUser) ;
		$this->assertNotNull($view);
		
		$view = compteurPrepareHead($absence, 'test', $nomUser, $prenomUser) ;
		
		$view = adminCompteurPrepareHead($absence, 'compteur') ;
		$this->assertNotNull($view);
		
		$view = adminCompteurPrepareHead($absence, 'test') ;
		
		$view = adminCongesPrepareHead($absence, 'compteur') ;
		$this->assertNotNull($view);
		
		$view = adminCongesPrepareHead($absence, 'test') ;
		
		$view = adminRecherchePrepareHead($absence, 'recherche') ;
		$this->assertNotNull($view);
		
		
		$view = adminRecherchePrepareHead($absence, 'test') ;
		
		$view = edtPrepareHead($absence, 'emploitemps') ;
		$this->assertNotNull($view);
		
			$view = edtPrepareHead($absence, 'test') ;
		
		$view = reglePrepareHead($absence, 'regle') ;
		$this->assertNotNull($view);
		
		$view = reglePrepareHead($absence, 'test') ;

		
		$view = reglePrepareHead($absence, 'import') ;
		$this->assertNotNull($view);
		
		$view = reglePrepareHead($absence, 'test') ;

		
		print __METHOD__."\n";
	}

	public function testSaveLibelle()
	
    {

		$libelle = saveLibelle('rttcumule') ;
		$this->assertEquals('RTT cumulé',$libelle);
		
		$libelle = saveLibelle('rttnoncumule') ;
		$this->assertEquals('RTT non cumulé',$libelle);
		
		$libelle = saveLibelle('conges') ;
		$this->assertEquals("Absence congés",$libelle);
		
		$libelle = saveLibelle('maladiemaintenue') ;
		$this->assertEquals("Absence maladie maintenue",$libelle);
		
		$libelle = saveLibelle('maladienonmaintenue') ;
		$this->assertEquals('Absence maladie non maintenue',$libelle);
		
		$libelle = saveLibelle('maternite') ;
		$this->assertEquals('Absence maternité',$libelle);
		
		$libelle = saveLibelle('pathologie') ;
		$this->assertEquals('Absence pathologie',$libelle);
		
		$libelle = saveLibelle('paternite') ;
		$this->assertEquals('Absence paternité',$libelle);
		
		$libelle = saveLibelle('chomagepartiel') ;
		$this->assertEquals('Absence Chômage partiel',$libelle);
		
		$libelle = saveLibelle('nonremuneree') ;
		$this->assertEquals('Absence congés sans solde',$libelle);
		
		$libelle = saveLibelle('accidentdetravail') ;
		$this->assertEquals('Absence accident du travail',$libelle);
		
		$libelle = saveLibelle('maladieprofessionnelle') ;
		$this->assertEquals('Absence maladie Professionnelle',$libelle);
		
		$libelle = saveLibelle('congeparental') ;
		$this->assertEquals('Absence Congés parental',$libelle);
		
		$libelle = saveLibelle('accidentdetrajet') ;
		$this->assertEquals('Absence Accident trajet',$libelle);
		
		$libelle = saveLibelle('mitempstherapeutique') ;
		$this->assertEquals('Absence Mi-temps thérapeutique',$libelle);
		
		$libelle = saveLibelle('mariage') ;
		$this->assertEquals('Mariage',$libelle);
		
		$libelle = saveLibelle('deuil') ;
		$this->assertEquals('Deuil',$libelle);
		
		$libelle = saveLibelle('naissanceadoption') ;
		$this->assertEquals('Naissance ou adoption',$libelle);
		
		$libelle = saveLibelle('enfantmalade') ;
		$this->assertEquals('Enfant malade',$libelle);
		
		$libelle = saveLibelle('demenagement') ;
		$this->assertEquals('Déménagement',$libelle);
		
		$libelle = saveLibelle('cours') ;
		$this->assertEquals('Cours',$libelle);
		
		$libelle = saveLibelle('preavis') ;
		$this->assertEquals('Absence préavis',$libelle);
		
		$libelle = saveLibelle('rechercheemploi') ;
		$this->assertEquals('Absence recherche emploi',$libelle);
		
		$libelle = saveLibelle('miseapied') ;
		$this->assertEquals('Absence mise à pied',$libelle);
		
		$libelle = saveLibelle('nonjustifiee') ;
		$this->assertEquals('Absence non justifiée',$libelle);
		
		$libelle = saveLibelle('cppartiel') ;
		$this->assertEquals('CP à temps partiel',$libelle);
		
		$libelle = saveLibelle('test') ;
		
		print __METHOD__."\n";
	}

	public function testLibFctUtilitaire()
    {
		global $ATMdb, $absence;
		
		
		//on récupère un utilisateur de la base : 
		$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$ATMdb->Execute($sql);
		if($ATMdb->Get_line()){
			$idTestUser=$ATMdb->Get_field('rowid');	
		}

		$code = saveCodeTypeAbsence($ATMdb, 'rttcumule') ;
		$this->assertEquals('0930',$code);
		
		$code = saveCodeTypeAbsence($ATMdb, 'test') ;
		
		$etat = saveLibelleEtat('Avalider') ;
		$this->assertEquals('En attente de validation',$etat);
		
		$etat = saveLibelleEtat('Validee') ;
		$this->assertEquals('Acceptée',$etat);
		
		$etat = saveLibelleEtat('Refusee') ;
		$this->assertEquals('Refusée',$etat);
		
		$etat = saveLibelleEtat('test') ;
		
		$etat = round2Virgule(0) ;
		$this->assertEquals('0',$etat);
		
		$etat = round2Virgule(4.521) ;
		$this->assertEquals('4.52',$etat);
		
		$date = php2dmy(2013) ;
		$this->assertEquals('01/01/1970',$date);
		
		$absence->fk_user=$idTestUser;
		$absence->etat='Avalider';
		$mail = mailConges($absence) ;
		$this->assertNotNull($mail);
		
		$absence->fk_user=$idTestUser;
		$absence->etat='Validee';
		$mail = mailConges($absence) ;
		$this->assertNotNull($mail);
		
		$absence->fk_user=$idTestUser;
		$absence->etat='Refusee';
		$mail = mailConges($absence) ;
		$this->assertNotNull($mail);

		$absence->fk_user=$idTestUser;
		$mail = mailCongesValideur($ATMdb, $absence) ;
		
		$chaine = supprimerAccent($chaine);
		$this->assertNotNull($chaine);
		
		$heure = additionnerHeure(3, 5);
		$this->assertEquals('8:0',$heure);
		
		$heure = additionnerHeure($heure, 5);
		$this->assertEquals('13:0',$heure);
		
		$heure = additionnerHeure('2:45', '2:45');

		$heure = difheure('00:00:00','14:15:00');
		$this->assertEquals('14:15:0',$heure);
		
		$heure = horaireMinuteEnCentieme('7:15');
		$this->assertEquals('7.25',$heure);
		
		$heure = horaireMinuteEnCentieme('2:45');
		$this->assertEquals('2.75',$heure);
		
		$heure = php2Date(strtotime('14-06-2013'));
		$this->assertEquals('2013-06-14 00:00:00',$heure);
		
		$absence->fk_user=$idTestUser;
		$mail =  envoieMailValideur($ATMdb, $absence, $idTestUser);
		$this->assertNotNull($mail);
		
		$absence->fk_user=$idTestUser;
		$mail =  _mail_valideur($ATMdb, $idTestUser, $firstname,$name, $sendto);
		
		$mail =  _mail_valideur($ATMdb, 3, $firstname,$name, $sendto);
		
		
	}
}
