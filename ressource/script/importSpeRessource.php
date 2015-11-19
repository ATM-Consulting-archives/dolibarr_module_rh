<?php
	
	define('INC_FROM_CRON_SCRIPT', true);
	require '../config.php';
	
	dol_include_once('/ressource/class/ressource.class.php');
	dol_include_once('/ressource/class/contrat.class.php');
	
	$PDOdb = new TPDOdb;
	$file = 'fichierImports/baseressource.csv';
	$TData = array();
	$TTypeRessource = array();
	
	$sqlReq="SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."rh_ressource_type ";
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) 
	{
		$TTypeRessource[$PDOdb->Get_field('libelle')] = $PDOdb->Get_field('rowid');
	}
	
	$handle = fopen($file, 'r');
	
	$line_1 = fgetcsv($handle, 4096, ';');
	$line_2 = fgetcsv($handle, 4096, ';');
	
	while ($row = fgetcsv($handle, 4096, ';')) 
	{
		$fk_type_ressource = isset($TTypeRessource[$row[29]]) ? $TTypeRessource[$row[29]] : false;
		if (!$fk_type_ressource) exit("ressourceTypeNotFound => ".$row[29]);
		
		$TData[] = array(
			'ressource'=>array(
				'numId'=>$row[1]
				,'libelle'=>$row[2]
				,'date_achat'=>$row[3]
				,'date_vente'=>$row[4]
				,'bailvoit' => $row[5]
				,'fk_proprietaire'=> _getEntityByName($PDOdb, $row[6]) //fk_entity du propriétaire
				,'fk_loueur'=> _getIdFournisseurByName($PDOdb, $row[7]) //fk_societe fournisseur
				,'fk_entity_utilisatrice' => _getEntityByName($PDOdb, $row[8])
				,'fk_utilisatrice' => _getIdUtilisateurByName($PDOdb, $row[9]) //agenceutilisatrice
				,'immatriculation' => $row[10]
				,'marquevoit' => $row[11]
				,'modlevoitversioncomm' => $row[12]
				,'modlevoitversioncomm' => $row[13]
				,'localisationvehicule' => $row[14]
				,'typevehicule' => $row[15]
				,'co2' => $row[16]
				,'coderefac' => $row[17]
				,'typecarburant' => $row[18]
				,'premisecircvoit' => date('Y-m-d',Tools::get_time($row[20]))
				,'dateimmatrvoit' => date('Y-m-d',Tools::get_time($row[21]))
				,'echeancectvoit' => date('Y-m-d',Tools::get_time($row[22]))
				,'pf' => $row[23]
			)
			,'emprunt'=>array(
				'fk_user'=>_getUserByName($PDOdb, $row[24],$row[25])
				,'date_debut' => date('Y-m-d',Tools::get_time($row[30]))
				,'date_fin'=>date('Y-m-d',Tools::get_time($row[31]))
				//,'fk_fournisseur'=>_getIdFournisseurByName($PDOdb, $row[7])
				,'entity'=>_getEntityByName($PDOdb, $row[8]) // FIXME 
				,'type'=>'emprunt'
				/*,'firstname'=>$row[24]
				,'lastname'=>$row[25]*/
			)
			,'contrat'=>array(
				'libelle'=>$row[27]
				,'numContrat'=>$row[28]
				,'fk_contrat'=>_getIdContrat($PDOdb, $row[28])
				,'fk_rh_ressource_type'=>$fk_type_ressource
				,'date_debut' =>date('Y-m-d',Tools::get_time($row[30]))
				,'date_fin'=>date('Y-m-d',Tools::get_time($row[31]))
				,'kilometre'=>$row[32]
				,'dureeMois'=>$row[33]
				,'entretien'=>$row[34]
				,'frais'=>$row[35]
				,'assurance'=>$row[36]
				,'loyer_TTC'=>$row[37]
				,'TVA'=>$row[38]
				,'loyer_HT'=>$row[38]
				//,'entity'=> _getEntityByName($PDOdb, $row[8]) // FIXME 
				,'fk_tier_fournisseur' => _getIdFournisseurByName($PDOdb, $row[7]) //FIXME
			)
		);
	}
	
	fclose($handle);
	
	
	foreach ($TData as &$line) 
	{
		$numid = $line['ressource']['numId'];
		
		$ressource = new TRH_Ressource;
		$ressource->load_by_numId($PDOdb, $numid);
		
		if ($ressource->getId() > 0)
		{
			print "<br>OK $numid...<br>";
			
			$ressource->set_values($line['ressource']);
			$ressource->save($PDOdb);
			$ressource->load_contrat($PDOdb);
			
			$fk_user_emprunteur = $ressource->isEmpruntee($PDOdb, $line['emprunt']['date_debut']);
			var_dump($fk_user_emprunteur, $line['emprunt']['fk_user']);
			
			if($fk_user_emprunteur != $line['emprunt']['fk_user']) 
			{
				echo 'nouvelEmprunt...<br>';
				// création d'un TRH_Evenement
				$TValue = $line['emprunt'] + array(
					'fk_rh_ressource' => $ressource->getId()
					,'confidentiel'=>'non'
					,'idEven' => 0
				);
				
				$fk_emprunt = $ressource->nouvelEmprunt($PDOdb, $TValue, true);
				var_dump($fk_emprunt);
			}
			
			if(!_contratAssocie($ressource->TContratAssocies, $line['contrat']['fk_contrat'] )) 
			{
				if ($line['contrat']['fk_contrat'] > 0) echo 'updateContrat => '.$line['contrat']['fk_contrat'].'...<br>';
				else 
				{
					
					echo 'nouveauContrat...<br>';
					// $contrat new or update
					$contrat = new TRH_Contrat;
					$contrat->load($PDOdb, $line['contrat']['fk_contrat']);
					$line['contrat']['date_debut'] = date('d/m/Y', $line['contrat']['date_debut']);
					$line['contrat']['date_fin'] = date('d/m/Y', $line['contrat']['date_fin']);
					$contrat->set_values($line['contrat']);
					$contrat->save($PDOdb);
				}
						
				$ressource->addContrat($PDOdb, array(
					'fk_rh_contrat' => $line['contrat']['fk_contrat']
					,'entity' => $line['ressource']['fk_entity_utilisatrice']
					,'fk_rh_ressource' => $ressource->getId()
				)); // <= on va créer le TRH_Contrat_Ressource
				
			}
			
			print "terminé<br />";
		}
		else
		{
			echo '<br>Echec! Fetch Ressource => '.$numid.'<br>';
			/*$ressource->set_values($line['ressource']);
			$ressource->save($PDOdb);*/
		}
		
	}

	echo '<br><br>FIN IMPORT';

function _contratAssocie(&$TContratAssocies, $fk_contrat ) 
{
	if ($fk_contrat == 0) return false;
	
	foreach ($TContratAssocies as &$contrat_ressource) 
	{
		if ($contrat_ressource->fk_rh_contrat == $fk_contrat) return true;
	}
	
	return false;
}

function _getIdContrat(&$PDOdb, $numero)
{
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'rh_contrat WHERE numContrat = "'.$numero.'"';
	$PDOdb->Execute($sql);
	
	if ($PDOdb->Get_Recordcount() > 0)
	{
		$row = $PDOdb->Get_line();
		return $row->rowid;
	}
	
	return 0;
}

function _getUserByName(&$PDOdb, $firstname, $lastname) 
{
	global $db;
	$found = false;
	$id = 0;
	
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'user WHERE lastname = "'.$db->escape($lastname).'" AND firstname = "'.$db->escape($firstname).'"';
	
	$resql = $db->query($sql);
	if ($db->num_rows($resql) == 1)
	{
		$row = $db->fetch_object($resql);
		$id = $row->rowid;
		$found = true;
	}
	
	if(!$found) exit("userNotFound => $firstname $lastname");
	
	return $id;
}

function _getEntityByName(&$PDOdb, $name) 
{
	global $db;
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'entity WHERE label = "'.$db->escape($name).'"';
	$resql = $db->query($sql);
	if ($db->num_rows($resql) > 0)
	{
		$row = $db->fetch_object($resql);
		return $row->rowid;
	}
	
	exit("entityNotFound => $name");
}

function _getIdFournisseurByName(&$PDOdb, $name)
{
	global $db;
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'societe WHERE nom = '.$PDOdb->quote($name);
	$resql = $db->query($sql);
	if ($db->num_rows($resql) == 1)
	{
		$row = $db->fetch_object($resql);
		return $row->rowid;
	}
	
	if ($db->num_rows($resql) > 1) exit("idFournisseurMultipleKeyFound => $name");
	else exit("idFournisseurNotFound => $name");
}

function _getIdUtilisateurByName(&$PDOdb, $name) 
{
	global $db;
	$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'usergroup WHERE nom = '.$PDOdb->quote($name);
	$resql = $db->query($sql);
	if ($db->num_rows($resql) > 0)
	{
		$row = $db->fetch_object($resql);
		return $row->rowid;
	}
	
	exit("userGroupNotFound => $name");
}