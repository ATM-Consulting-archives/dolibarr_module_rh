<?php
	
	set_time_limit(0);
	ini_set('memory_limit','512M');
	
	
	require('../../config.php');
	require('../../class/contrat.class.php');
	require('../../class/evenement.class.php');
	require('../../class/ressource.class.php');
	require('../../lib/ressource.lib.php');
	
	$reel = __get('reel','N');

	$ATMdb=new TPDOdb;

	$f1=fopen('ticket191.csv','r');
	fgets($f1);
	fgets($f1);
	
	while($data = fgetcsv($f1,1024,';','"')) {
		
		$r=new TRH_Ressource;
		$ref = trim($data[0]);
		if($r->load_by_numId($ATMdb, $ref)!==false) {
			print "$ref...";
			$r->co2 = $data[1];
			$r->libelle = $data[2];
			$r->marquevoit = $data[3];
			$r->modlevoit = $data[4];
			$r->modlevoitversioncomm = $data[8];
			$r->pf = $data[9];
			$r->coderefac = $data[13];
			
			$r->fk_utilisatrice = _getIdGroupe($ATMdb, $data[5] );
			$r->fk_proprietaire = _getIdEntity($data[6] );
			$r->fk_entity_utilisatrice = _getIdEntity($data[7] );
			
			
			$ATMdb->Execute("SELECT fk_rh_contrat FROM llx_rh_contrat_ressource WHERE fk_rh_ressource=".$r->getId());
			$TContrat = $ATMdb->Get_All();
			if(count($TContrat)==1) {
				
				$idContrat = $TContrat[0]->fk_rh_contrat;
				
				$contrat=new TRH_Contrat;
				if($contrat->load($ATMdb, $idContrat)) {
					$contrat->frais = Tools::string2num($data[10]);
					$contrat->entretien = Tools::string2num($data[11]);
					$contrat->loyer_TTC = Tools::string2num($data[12]);
					$contrat->assurance = Tools::string2num($data[14]);
					
				//	exit($contrat->loyer_TTC);
					
					if($reel=='Y')$contrat->save($ATMdb);
					
				}
				else {
					print "erreur contrat...";
				}
				
			}
			else if(count($TContrat)==0) {
				
				if($reel=='Y') {
					$contrat=new TRH_Contrat;
					$contrat->frais = Tools::string2num($data[10]);
					$contrat->entretien = Tools::string2num($data[11]);
					$contrat->loyer_TTC = Tools::string2num($data[12]);
					$contrat->assurance = Tools::string2num($data[14]);
					$contrat->fk_rh_ressource_type = 1;
					$contrat->date_debut = $r->date_achat; 
					$contrat->date_fin = $r->date_vente; 
					
					if($contrat->loyer_TTC>0) {
							
						$contrat->save($ATMdb);
						
						$cr=new TRH_Contrat_Ressource;
						$cr->fk_rh_contrat=$contrat->getId();
						$cr->fk_rh_ressource = $r->getId();
						$cr->commentaire = "Créer par ticket#191";
						
						$cr->save($ATMdb);						
						print "pas de contrat : création (".$contrat->getId().")...";
						
					}
					else {
						print "pas de contrat...";
					}
					
				}
				
				
				
				
			}
			else {
				print "nombre de contrat non cohérent...";
			}


			if($reel=='Y')$r->save($ATMdb);

			print "ok<br/>";
		}
		else {
			print $ref." non trouvé <br/>";
			
		}
		
		
		
		
	}
function _getIdGroupe(&$ATMdb, $groupe) {
	
	if($groupe=='CPRO TELECOM')$groupe='AGT';
	
	$ATMdb->Execute("SELECT rowid FROM `llx_usergroup`
WHERE `nom` LIKE '$groupe' LIMIT 1");
	if($ATMdb->Get_line()) {
		return $ATMdb->Get_field('rowid');
	}
	else{
		print('erreur agence non trouvée');
		return 0;
	}
	
}	
	
function _getIdEntity($company) {
	$company=strtolower($company);
				
		if(strpos($company,'informatique')!==false) {
			$ldap_entity_login = 3;
		}
		else if(strpos($company,'groupe')!==false) {
			$ldap_entity_login = 1;
		}
		else if(strpos($company,'global')!==false) {
			$ldap_entity_login = 5;
		}
		else if(strpos($company,'agt')!==false || strpos($company,'telecom')!==false) {
			$ldap_entity_login = 4;
		}
		else {
			$ldap_entity_login = 2; 
		}
	
	return $ldap_entity_login;
}
