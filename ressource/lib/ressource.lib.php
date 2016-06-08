<?php

function ressourcePrepareHead(&$obj, $type='type-ressource',&$param=null) {
	global $user;
	
	switch ($type) {
		case 'type-ressource':
				return array(
					array(dol_buildpath('/ressource/typeRessource.php?id='.$obj->getId(),1), 'Fiche','fiche')
					,array(dol_buildpath('/ressource/typeRessourceField.php?id='.$obj->getId(),1), 'Champs','field')
					,($obj->code == 'telephone') ? array(dol_buildpath('/ressource/typeRessourceRegle.php?id='.$obj->getId(),1), 'Règles','regle'): null
					,array(dol_buildpath('/ressource/typeRessourceEvenement.php?id='.$obj->getId(),1), 'Evénements','event')
				);
			
			break;
		case 'ressource':
				return array(
					array(dol_buildpath('/ressource/ressource.php?id='.$obj->getId(),1), 'Fiche','fiche')
					,($obj->fk_rh_ressource == 0) ? array(dol_buildpath('/ressource/attribution.php?id='.$obj->getId(),1), 'Attribution','attribution'):null
					,array(dol_buildpath('/ressource/evenement.php?id='.$obj->getId(),1), 'Evénement','evenement')
					,$user->rights->ressource->ressource->viewResourceCalendar ? array(dol_buildpath('/ressource/calendrierRessource.php?id='.$obj->getId(),1).'&fiche=true', 'Calendrier','calendrier'):''
					,array(dol_buildpath('/ressource/document.php?id='.$obj->getId(),1), 'Fichiers joints','document')
					,$user->rights->ressource->ressource->viewFilesRestricted?array(dol_buildpath('/ressource/documentConfidentiel.php?id='.$obj->getId(),1), 'Fichiers confidentiels','documentConfidentiel'):''
					,array(dol_buildpath('/ressource/contratRessource.php?id='.$obj->getId(),1), 'Contrats','contrats')
				);
			
			break;
		case 'contrat':
				return array(
					array(dol_buildpath('/ressource/contrat.php?id='.$obj->getId(),1), 'Fiche','fiche')
					,array(dol_buildpath('/ressource/documentContrat.php?id='.$obj->getId(),1), 'Fichiers joints','document')
				);
			
			break;
		case 'evenement':
				return array(
					array(dol_buildpath('/ressource/evenement.php?id='.$param->getId().'&idEven='.$obj->getId().'&action=view',1), 'Fiche','fiche')
					,array(dol_buildpath('/ressource/documentEvenement.php?id='.$param->getId().'&idEven='.$obj->getId(),1), 'Fichiers joints','document')
				);
			
			break;
		case 'import':
				return array(
					array(dol_buildpath('/ressource/documentSupplier.php',1), 'Fiche','fiche')
				);
			
			break;
		default :
				return array();
			break;
	}
}

/**
 * Affiche un tableau avec le numId et le libellé de la ressource
 */
function printLibelle($ressource){
	
	print getLibelle($ressource);
	
}

function getLibelle($ressource){
	return '<table class="border" style="width:100%">
		<tr>
			<td style="width:20%">Numéro Id</td>
			<td>'.$ressource->numId.'</td>
		</tr>
		<tr>
			<td>Libellé</td>
			<td><a href="ressource.php?id='.$ressource->getId().'">'.$ressource->libelle.'</a> </td>
		</tr>
	</table><br>';
}

/**
 * Retourne la liste des types d'événement associé à un type de ressource
 */
function getTypeEvent($idTypeRessource = 0){
	global $conf;
	$TEvent = array();
	
	$sql="SELECT rowid, code, libelle FROM ".MAIN_DB_PREFIX."rh_type_evenement 
	WHERE (fk_rh_ressource_type=".$idTypeRessource." OR fk_rh_ressource_type=0) ORDER BY fk_rh_ressource_type";
	$PDOdb =new TPDOdb;
	$PDOdb->Execute($sql);
	while($row = $PDOdb->Get_line()) {
		$TEvent[$row->code] = $row->libelle;	
	}
	$PDOdb->close();
	return $TEvent;
}

/**
 * Renvoie un tableau de id=>libelle des ressources de type spécifié. Par défaut toute les ressources.
 */
function getRessource($idTypeRessource = 0){
	global $conf;
	$TRessource = array(0=>'');
	$PDOdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,libelle, numId FROM ".MAIN_DB_PREFIX."rh_ressource WHERE 1 ";
	if ($idTypeRessource>0){$sqlReq.= " AND fk_rh_ressource_type=".$idTypeRessource;}
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TRessource[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('libelle').' '.$PDOdb->Get_field('numId'), ENT_COMPAT , 'ISO8859-1');
		}
	$PDOdb->close();
	return $TRessource;
}

/**
 * Retourne l'ID du type de ressource correspondant à 'code', false si code pas trouvé.
 */
function getIdType($code){
	global $conf;
	$PDOdb =new TPDOdb;
	$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."rh_ressource_type 
		WHERE code= '".$code."'";
	$PDOdb->Execute($sql);
	$id = false;
	if ($PDOdb->Get_line()) {$id = $PDOdb->Get_field('rowid');}
	$PDOdb->close();
	return $id;
}

/**
 * Renvoie un tableau $numId=>$rowid des ressources du type spécifié.
 */
function getIDRessource(&$PDOdb, $idType=0){
	global $conf;
	$TRessource = array();
	
	$sql="SELECT rowid, numId  FROM ".MAIN_DB_PREFIX."rh_ressource
	 WHERE fk_rh_ressource_type=".$idType;
	// echo $sql.'<br>';
	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		$TRessource[$PDOdb->Get_field('numId')] = $PDOdb->Get_field('rowid');
	}
	return $TRessource;
}

/**
 * Renvoie un tableau $id=> nom des users
 * $inEntity à vrai ne renvoie que les User de l'entité courante
 * $avecAll à vrai rajoute une ligne Tous
 */
function getUsers($avecAll = false, $inEntity = true){
	global $conf;
	$TUser = $avecAll ? array(0=>'Tous') : array() ;
	$PDOdb =new TPDOdb;
	
	$sqlReq = "SELECT rowid,lastname, firstname FROM ".MAIN_DB_PREFIX."user";
	if ($inEntity){$sqlReq .= " WHERE entity IN (0,".$conf->entity.") ";} 
	$sqlReq.= " ORDER BY lastname, firstname ";
	
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TUser[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('firstname').' '.$PDOdb->Get_field('lastname'), ENT_COMPAT , 'ISO8859-1');
		}
	$PDOdb->close();
	return $TUser;
	
}

function getFactures(&$PDOdb, $fk_fournisseur) {
        //chargement des voitures
        $TFacture = array(0=>'Tous');
        $sql = "SELECT DISTINCT idImport
            FROM ".MAIN_DB_PREFIX."rh_evenement
            WHERE fk_fournisseur =".$fk_fournisseur."
            AND idImport IS NOT NULL";
        
        
        $PDOdb->Execute($sql);
        while($row = $PDOdb->Get_line()) {
            $TFacture[$row->idImport] = $row->idImport;
        }
        if(isset($_REQUEST['DEBUG'])) {
            echo $sql.'<br>';
            print_r($TFacture);
        }
        
                
    
    return $TFacture;
}

/**
 * renvoie une liste des groupes $id=>nom
 */
function getGroups(){
	global $conf;
	$TGroups = array();
	$PDOdb =new TPDOdb;
	
	$sqlReq="SELECT rowid,nom FROM ".MAIN_DB_PREFIX."usergroup WHERE entity IN (0,".$conf->entity.")";
	
	$PDOdb->Execute($sqlReq);
	while($PDOdb->Get_line()) {
		$TGroups[$PDOdb->Get_field('rowid')] = htmlentities($PDOdb->Get_field('nom'), ENT_COMPAT , 'ISO8859-1');
		}
	return $TGroups;
	
}


/**
 * si le choix limite est cohérant avec la colonne, on affiche la valeur
 */
function afficheOuPas($val, $choixLimite,$colonne){
	if ($colonne==$choixLimite){return intToString($val);}
	return '';
}

	
/**
 * renvoie 'Tous' si choixApplication='all', renvoie val sinon. 
 */
function stringTous($val, $choixApplication){
	if ($choixApplication == 'all') return 'Tous';
	else return $val;
}

/**
 * Transforme un nombre de minute (entier) en jolie chaine de caractère donnant l'heure
 * @return une string
 */
function intToString($val = 0){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	if ($h==0 && $m==0){return '00:00';}
	return $h.':'.$m;
}

/**
 * Donnant le nombre d'heure correspondant à $val minutes
 * @return une string
 */
function intToHour($val){
	$h = intval($val/60);
	if ($h < 10){$h = '0'.$h;}
	return $h;
}
/**
 * Donne le modulo 60 de $val minutes
 * @return une string
 */
function intToMinute($val){
	$m = $val%60;
	if ($m < 10){$m = '0'.$m;}
	return $m;
}

/**
 * f(heure, minutes) => minutes
 */
function timeToInt($h, $m){
	return intval($h)*60+intval($m);
}


/**
 * Charge les règles pour chacun des utilisateurs
 */
function load_limites_telephone(&$PDOdb, $TGroups, $TRowidUser){
	$default = 359940; //consideration conso infinie : 99H
	$TLimites = array();
	foreach ($TRowidUser as $id) {		
		$TLimites[$id] = array(
			'lim'=>$default
			,'limInterne' => $default	//en sec
			,'limExterne' => $default	//en sec
			,'dataIllimite' => false
			,'dataIphone' => false
			,'mailforfait'=> false
			,'smsIllimite'=> false
			,'data15Mo'=> false
			,'natureRefac'=>''
			,'montantRefac'=>0
			);
	}
	

	/*echo '<br><br><br>';
foreach ($TLimites as $key => $value) {
	echo $key.' ';	
	print_r($value);
	echo '<br>';*/


	$sql="SELECT fk_user, fk_usergroup, choixApplication, dureeInt, dureeExt,duree,
		dataIllimite, dataIphone, smsIllimite, mailforfait, data15Mo, natureRefac, montantRefac 
		FROM ".MAIN_DB_PREFIX."rh_ressource_regle
		";
	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		if ($PDOdb->Get_field('choixApplication')=='user'){
			modifierLimites($TLimites, $PDOdb->Get_field('fk_user')
				, $PDOdb->Get_field('duree')
				, $PDOdb->Get_field('dureeInt')
				, $PDOdb->Get_field('dureeExt')
				, $PDOdb->Get_field('dataIllimite')
				, $PDOdb->Get_field('dataIphone')
				, $PDOdb->Get_field('mailforfait')
				, $PDOdb->Get_field('smsIllimite')
				, $PDOdb->Get_field('data15Mo')
				, $PDOdb->Get_field('natureRefac')
				, $PDOdb->Get_field('montantRefac')
				);
			}
		else if ($PDOdb->Get_field('choixApplication')=='group'){
			if (empty($TGroups[$PDOdb->Get_field('fk_usergroup')]))
				{$message .= 'Groupe n°'.$PDOdb->Get_field('fk_usergroup').' inexistant.<br>';}
			else{
				foreach ($TGroups[$PDOdb->Get_field('fk_usergroup')] as $members) {
					modifierLimites($TLimites, $members
						, $PDOdb->Get_field('duree')
						, $PDOdb->Get_field('dureeInt')
						, $PDOdb->Get_field('dureeExt')
						, $PDOdb->Get_field('dataIllimite')
						, $PDOdb->Get_field('dataIphone')
						, $PDOdb->Get_field('mailforfait')
						, $PDOdb->Get_field('smsIllimite')
						, $PDOdb->Get_field('data15Mo')
						, $PDOdb->Get_field('natureRefac')
						, $PDOdb->Get_field('montantRefac')
						
						);
					}
				}
			}
		else if ($PDOdb->Get_field('choixApplication')=='all'){
			foreach ($TRowidUser as $idUser) {
				modifierLimites($TLimites, $idUser
					, $PDOdb->Get_field('duree')
					, $PDOdb->Get_field('dureeInt')
					, $PDOdb->Get_field('dureeExt')
					, $PDOdb->Get_field('dataIllimite')
					, $PDOdb->Get_field('dataIphone')
					, $PDOdb->Get_field('mailforfait')
					, $PDOdb->Get_field('smsIllimite')
					, $PDOdb->Get_field('data15Mo')
					, $PDOdb->Get_field('natureRefac')
					, $PDOdb->Get_field('montantRefac')
					);
				}
			}
		}
	return $TLimites;
}


function modifierLimites(&$TLimites, $fk_user, $gen,  $int, $ext, $dataIll = false, $dataIphone = false, $mail = false, $smsIll = false, $data15Mo= false, $natureRefac = false, $montantRefac = 0){
	if (($TLimites[$fk_user]['limInterne'] > $int*60)){
		$TLimites[$fk_user]['limInterne'] = $int*60;
	}
	if (($TLimites[$fk_user]['limExterne'] > $ext*60)) {
		$TLimites[$fk_user]['limExterne'] = $ext*60;
	}
	if ($TLimites[$fk_user]['lim'] > ($gen*60)){
		$TLimites[$fk_user]['lim'] = $gen*60;
	}
	
	$TLimites[$fk_user]['dataIllimite'] =$dataIll;
	$TLimites[$fk_user]['dataIphone'] =$dataIphone;
	$TLimites[$fk_user]['mailforfait']=$mail;
	$TLimites[$fk_user]['smsIllimite']=$smsIll;
	$TLimites[$fk_user]['data15Mo']=$data15Mo;
	if ($natureRefac){
		if (!empty($TLimites[$fk_user]['natureRefac'])){$TLimites[$fk_user]['natureRefac'] .= " ; ";}	
		$TLimites[$fk_user]['natureRefac'] .= $natureRefac;
		$TLimites[$fk_user]['montantRefac'] += $montantRefac;
		}
		
	return;
}




function send_mail_resources($subject, $message){
	global $langs,$user;
	
	$langs->load('mails');
	
	$from = USER_MAIL_SENDER;
	//$sendto = USER_MAIL_RECEIVER;
	$sendto = $user->email;

	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
	dol_syslog("Ressource::sendmail content=$from,$sendto,$subject,$message", LOG_DEBUG);
	
    (int)$result = $mail->send(true, 'utf-8');
	return (int)$result;
}
	
	

/**
 * La fonction renvoie le rowid de l'user qui a la ressource $idRessource à la date $jour, 0 sinon.
 * $jour a la forme Y-m-d
 */
function ressourceIsEmpruntee(&$PDOdb, $idRessource, $jour){
		global $conf;
		$sql = "SELECT e.fk_user, e.date_debut , e.date_fin
				FROM ".MAIN_DB_PREFIX."rh_evenement as e
				LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource=r.rowid OR e.fk_rh_ressource=r.fk_rh_ressource) 
				WHERE e.type='emprunt'
				AND r.rowid = ".$idRessource."
				AND e.date_debut<='".$jour."' AND e.date_fin >= '".$jour."' 
				";
				
		$PDOdb->Execute($sql);
		if ($PDOdb->Get_line()){
			return $PDOdb->Get_field('fk_user');
		}
		return 0;
}	

function getIdSuperAdmin(&$PDOdb){
	//trouve l'id du SuperAdmin
	$idSuperAdmin = 0;
	$sql="SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE name = 'SuperAdmin' ";
		$PDOdb->Execute($sql);
		if($row = $PDOdb->Get_line()) {
		$idSuperAdmin = $row->rowid;}
	return $idSuperAdmin;
}

function getIdSociete(&$PDOdb, $nomMinuscule){
	global $conf;
	$idParcours = 0;
	$sql="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe ";
	$PDOdb->Execute($sql);
	while($PDOdb->Get_line()) {
		if (strtolower($PDOdb->Get_field('nom')) == $nomMinuscule){ 
			return $PDOdb->Get_field('rowid');}}
	
	return false;
}

	

function createRessourceFactice(&$PDOdb, $type, $idFacture, $entity, $fournisseur){
	$ress = new TRH_Ressource;
	if ($ress->loadBy($PDOdb, 'factice'.$idFacture, 'numId' )){
		return $ress->getId();}
	
	$ress->numId = 'factice'.$idFacture;
	$ress->fk_rh_ressource_type = $type;
	$ress->libelle = 'Factice facture '.$idFacture;
	$ress->fk_entity_utilisatrice = $entity;
	$ress->fk_proprietaire = $entity;
	$ress->fk_loueur = $fournisseur;
	$ress->save($PDOdb);
	return $ress->getId();
}



function _exportVoiture(&$PDOdb, $date_debut, $date_fin, $entity, $fk_fournisseur, $idTypeRessource, $idImport){
    $TLignes = array();
    if(isset($_REQUEST['DEBUG'])) {echo $idImport.'<br>';}
                        
    
    //$idImport = false;
    $date_debut=explode("/", $date_debut);
    $date_debut=date('Y-m-d',mktime(0, 0, 0, $date_debut[1], $date_debut[0], $date_debut[2]));
    $date_fin=explode("/", $date_fin);
    $date_fin=date('Y-m-d',mktime(0, 0, 0, $date_fin[1], $date_fin[0], $date_fin[2]));
    
    $idVoiture = getIdType('voiture');
    
    /**----***********************----**/
    /**----** Ligne de l'entité **----**/
    /**----***********************----**/
    
    $sql = "SELECT
            e.label as 'label'
            FROM ".MAIN_DB_PREFIX."entity as e
            WHERE e.rowid IN (0,".$entity.")";
            
    if(isset($_REQUEST['DEBUG'])) {
        print $sql;
    }
    
    $PDOdb->Execute($sql);
    while($PDOdb->Get_line()) {
        $TLignes[]=$PDOdb->Get_field('label');
    }
    
    /**----***********************----**/
    /**----** Lignes de débit **----**/
    /**----***********************----**/
    
    $sql="SELECT CAST(SUM(e.coutEntrepriseTTC) as DECIMAL(16,2)) as coutEntrepriseTTC, 
                CAST(SUM(e.coutEntrepriseHT) as DECIMAL(16,2)) as coutEntrepriseHT, 
                e.type, e.date_facture, 
                DATE_FORMAT(e.date_debut, '%d%m%y') as date_debut, 
                DATE_FORMAT(e.date_debut, '%m') as mois_date_debut, 
                DATE_FORMAT(e.date_debut, '%Y') as annee_date_debut, 
                r.typeVehicule, u.lastname, u.firstname, e.entity, t.codecomptable, 
                ue.COMPTE_TIERS, e.idImport,e.numFacture
    FROM ".MAIN_DB_PREFIX."rh_evenement as e
    LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
    LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
    LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
        LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as ue ON (u.rowid = ue.fk_object)
    WHERE  (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
    AND e.entity = ".$entity."
    AND e.fk_fournisseur =".$fk_fournisseur;    
    if ($idImport){ $sql .= " AND e.idImport = '".$idImport."' ";}
    $sql .= " GROUP BY e.numFacture, t.codecomptable";
    
    if(isset($_REQUEST['DEBUG'])) {
        print $sql;
    }
    
    $PDOdb2=new TPDOdb;
    
    $PDOdb->Execute($sql);
    while($row = $PDOdb->Get_line()) {
    	$montant = $row->coutEntrepriseHT;
        $sens = 'D';
        $code_compta = $row->codecomptable;
        $type_compte = 'G';
        
        $numeroFacture = $row->numFacture;
        
        $TLignes[] = array(
            'numFacture'=>$numeroFacture
            ,'codeJournal'=>'RES'
            ,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
            ,'typePiece'=> 'FF'
            ,'compteGeneral'=> $code_compta
            ,'typeCompte'=> $type_compte
	    	,'immatriculation'=> ''
	    	,'codeAnalytique'=> ''
            ,'nom'=>''
            ,'prenom'=>''
            ,'referenceEcriture' => ''
            ,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
            ,'modePaiement'=> 'V'
            ,'dateEcheance'=> date('dmy')
            ,'sens'=> $sens
            ,'montant'=>  $montant
            ,'typeEcriture'=> 'N'
            ,'numeroPiece'=> ''
            ,'devise'=>'EUR'
            ,'idImport'=>$row->idImport
            
        );
        
        /*
         * Exploitation de l'analytique
         */
         
        $sql_anal="SELECT DISTINCT e.rowid
                , e.coutEntrepriseTTC as coutEntrepriseTTC , e.date_facture
                , (e.coutEntrepriseHT * IFNULL(a.pourcentage,100) / 100) as coutEntrepriseHT
                , a.code as 'code_analytique'
                , a.pourcentage as 'pourcentage'
                ,u.firstname,u.lastname,u.rowid as 'fk_user'
                ,e.idImport,e.numFacture
		,r.numId as immat
        FROM ".MAIN_DB_PREFIX."rh_evenement as e
        LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
        LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
        LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as a ON (e.fk_user=a.fk_user)
        LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid=e.fk_user
        WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
        AND e.entity = ".$entity."
        AND e.fk_fournisseur =".$fk_fournisseur;
        if ($idImport){ $sql_anal .= " AND e.idImport = '".$idImport."' ";}
        $sql_anal .= " AND t.codecomptable = '".$code_compta."' AND e.numFacture='".$numeroFacture."'";
        
        if(isset($_REQUEST['DEBUG'])) {
            print $sql_anal;
        }
            $PDOdb2->Execute($sql_anal);
        $TabAna=array();    $TUser=array(); 
        while($PDOdb2->Get_line()) {

            $code_anal = $PDOdb2->Get_field('code_analytique');
            $total_anal = $PDOdb2->Get_field('coutEntrepriseHT');
            $fk_user =  $PDOdb2->Get_field('fk_user');
//print_r($code_anal);
		$immat = $PDOdb2->Get_field('immat');

            $TUser[$code_anal][$fk_user]=array(
                    'nom' => ' <a href="'.HTTP.'custom/valideur/analytique.php?fk_user='.$PDOdb2->Get_field('fk_user').'">'. $PDOdb2->Get_field('lastname') ."</a>"
                    ,'prenom' => $PDOdb2->Get_field('firstname')
		   ,'immat' => $immat
            );
                        
            if(isset($_REQUEST['DEBUG'])) {
                print "$code_anal=$total_anal<br/>";
            }
            if(!isset($TabAna[$code_anal][$fk_user])) $TabAna[$code_anal][$fk_user]=0;
            $TabAna[$code_anal][$fk_user]+=$total_anal;
            /*$TabAna[] = array(
                $code_anal
                ,number_format($PDOdb2->Get_field('total_ht'),2,'.','' )
            );*/
        }
    
        $nbElement = count($TabAna, COUNT_RECURSIVE );
        $total_partiel = 0;$cpt=0;
        foreach($TabAna as $code_analytique=>$TAnal_user /*$ana*/) {
            
            
            foreach($TAnal_user as $fk_user=>$total_ht_anal) {
            if(isset($_REQUEST['DEBUG'])) {
                                print "<b>$code_analytique=$total_ht_anal</b><br/>";
                        }

            $total_ht_anal = round($total_ht_anal,2);

            if($cpt==$nbElement-1) $total_ht_anal = $montant - $total_partiel;
                $total_partiel+=$total_ht_anal;
              
                    $type_compte        =   'A';
                        
                        $TLignes[] = array(
                            'numFacture'=>$row->numFacture
                            ,'codeJournal'=>'RES'
                            ,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
                            ,'typePiece'=> 'FF'
                            ,'compteGeneral'=> $code_compta
                            ,'typeCompte'=> $type_compte
				,'immatriculation'=> $TUser[$code_analytique][$fk_user]['immat']
							
                            ,'codeAnalytique'=> $code_analytique
                            ,'nom'=>$TUser[$code_analytique][$fk_user]['nom']
                            ,'prenom'=>$TUser[$code_analytique][$fk_user]['prenom']
                            ,'referenceEcriture' => ''
                            ,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
                            ,'modePaiement'=> 'V'
                            ,'dateEcheance'=> date('dmy')
                            ,'sens'=> $sens
                            ,'montant'=>  number_format($total_ht_anal,2,'.','')
                            ,'typeEcriture'=> 'N'
                            ,'numeroPiece'=> ''
                            ,'devise'=>'EUR'
                            ,'idImport'=>$row->idImport
                            
                                
                        );
                 $cpt++;                
            }
            
            //list($code_analytique,$total_ht_anal)=$ana ;
            

        }
    
     
        $ressource_exist=1;
    }

    /**----**********************----**/
    /**----**** Ligne de TVA ****----**/
    /**----**********************----**/
    
    if($ressource_exist){
        $sql="SELECT CAST(SUM(e.coutEntrepriseTTC) as DECIMAL(16,2)) as coutEntrepriseTTC, 
                    CAST(SUM(e.coutEntrepriseHT) as DECIMAL(16,2)) as coutEntrepriseHT , e.date_facture, e.idImport,e.numFacture
        FROM ".MAIN_DB_PREFIX."rh_evenement as e
        LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
        LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
        WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
        AND e.entity = ".$entity."
        AND e.fk_fournisseur =".$fk_fournisseur;
        if ($idImport){ $sql .= " AND e.idImport = '".$idImport."' ";}
        
        $sql.=" GROUP BY e.numFacture ";
        
        if(isset($_REQUEST['DEBUG'])) {
            print $sql;
        }
        
        $PDOdb->Execute($sql);
        while($row = $PDOdb->Get_line()) {
            $total_tva  = number_format(floatval($PDOdb->Get_field('coutEntrepriseTTC')) - floatval($PDOdb->Get_field('coutEntrepriseHT')),2,'.','');
            
            $TLignes[] =array(
                'numFacture'=>$row->numFacture
                ,'codeJournal'=>'RES'
                ,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
                ,'typePiece'=> 'FF'
                ,'compteGeneral'=> '445660'
                ,'typeCompte'=> 'G'
		,'immatriculation'=> ''				
                ,'codeAnalytique'=> ''
                ,'nom'=>''
                ,'prenom'=>''
                
                ,'referenceEcriture' => ''
                ,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
                ,'modePaiement'=> 'V'
                ,'dateEcheance'=> date('dmy')
                ,'sens'=> 'D'
                ,'montant'=> $total_tva
                ,'typeEcriture'=> 'N'
                ,'numeroPiece'=> ''
                ,'devise'=>'EUR'
                ,'idImport'=>$row->idImport
                
            ); 
            
        }
    }
    
    
    /**----***********************----**/
    /**----** Lignes de crédit **----**/
    /**----***********************----**/
    
    $TLoueurs = array();
    $sql="SELECT rowid, code_fournisseur FROM ".MAIN_DB_PREFIX."societe";
    $PDOdb->Execute($sql);
    while($row = $PDOdb->Get_line()) {
        $TLoueurs[$row->rowid] = $row->code_fournisseur;
    }
    
    $TEntity = array();
    $sql="SELECT rowid, label FROM ".MAIN_DB_PREFIX."entity";
    $PDOdb->Execute($sql);
    while($row = $PDOdb->Get_line()) {
        $TEntity[$row->rowid] = substr($row->label,0,13);
    }
    
    $idTotal = getIdSociete($PDOdb, 'total');
    
    $sql="SELECT SUM(e.coutEntrepriseTTC) as coutEntrepriseTTC, 
                e.coutEntrepriseHT as coutEntrepriseHT, type, e.date_facture, 
                DATE_FORMAT(e.date_debut, '%d%m%y') as date_debut, 
                DATE_FORMAT(e.date_debut, '%m') as mois_date_debut, 
                DATE_FORMAT(e.date_debut, '%Y') as annee_date_debut, 
                r.typeVehicule, t.codecomptable, r.fk_loueur, e.fk_fournisseur, 
                r.fk_entity_utilisatrice,e.idImport,e.numFacture
    FROM ".MAIN_DB_PREFIX."rh_evenement as e
    LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
    LEFT JOIN ".MAIN_DB_PREFIX."rh_type_evenement as t ON (e.type=t.code)
    WHERE (e.date_debut<='".$date_fin."' AND e.date_debut>='".$date_debut."')
    AND e.fk_fournisseur =".$fk_fournisseur."
    AND e.entity = ".$entity;
    if ($idImport){ $sql .= " AND e.idImport = '".$idImport."'";}
    
    $sql.=" GROUP BY e.numFacture ";
    
    if(isset($_REQUEST['DEBUG'])) {
        print $sql;
    }
    
    
    
    $PDOdb->Execute($sql);
    $TCredits = array();
    
    while($row = $PDOdb->Get_line()) {
        $date = $row->date_debut;
        $date_mois = $row->mois_date_debut;
        $date_annee = $row->annee_date_debut;
        //un VU : on prend le HT
        //un VP on prend le TTC
        /*if ($idTypeRessource==$idVoiture){
            $montant = (strtoupper($row->typeVehicule) == 'VP') ? $row->coutEntrepriseTTC : $row->coutEntrepriseHT;}
        else {
            $montant = $row->coutEntrepriseTTC;
        }*/
        
        $montant = $row->coutEntrepriseTTC;
        
        $sens = 'C';
        $code_compta = '425902'; //TODO paramètre
        $type_compte = 'X';
        
        //if($row->fk_entity_utilisatrice==$entity || $row->$fk_fournisseur==$idTotal){
            $compte_tiers=$TLoueurs[$fk_fournisseur];
        /*}else{
            $compte_tiers=$TEntity[$entity];
        }*/
    
        $TLignes[] =array(
                'numFacture'=>$row->numFacture
                ,'codeJournal'=>'RES'
                ,'datePiece'=>date('dmy', date2ToInt($row->date_facture))
                ,'typePiece'=> 'FF'
                ,'compteGeneral'=> $code_compta
                ,'typeCompte'=> $type_compte
		,'immatriculation'=> ''
                ,'codeAnalytique'=> $compte_tiers
                ,'nom'=>''
                ,'prenom'=>''
                ,'referenceEcriture' => ''
                ,'libelleEcriture'=> 'RESSOURCE '.date('m/Y')
                ,'modePaiement'=> 'V'
                ,'dateEcheance'=> date('dmy')
                ,'sens'=> $sens
                ,'montant'=>  number_format($montant,2,'.','')
                ,'typeEcriture'=> 'N'
                ,'numeroPiece'=> ''
                ,'devise'=>'EUR'
                ,'idImport'=>$row->idImport
                
        );
    
        
        
    }

    return $TLignes;
    
}


function _exportOrange2($PDOdb, $date_debut, $date_fin, $entity, $idImport){
    
    global $db;
    
    dol_include_once("/core/lib/admin.lib.php");
    dol_include_once("/ressource/class/numeros_speciaux.class.php");
    dol_include_once('/valideur/class/analytique_user.class.php');
    dol_include_once('/ressource/class/ressource.class.php');   

    $TabLigne = array();
    
    $date_deb = Tools::get_time($date_debut);
    $date_deb = date("Y-m-d", $date_deb);
    
    $date_end = Tools::get_time($date_fin);
    $date_end = date("Y-m-d", $date_end);
    
    $TabLigne = array();

    $TNumerosSpeciaux = TRH_Numero_special::getAllNumbers($db);

    $sql="SELECT ea.num_gsm, SUM(ea.montant_euros_ht) as 'montant_euros_ht',ea.date_appel FROM ".MAIN_DB_PREFIX."rh_evenement_appel ea
    WHERE ea.date_appel BETWEEN '$date_deb 00:00:00' AND '$date_end 23:59:59'"; 
    
    if(!empty($TNumerosSpeciaux)) {
        $sql.=" AND ea.num_appele NOT IN ('".implode("','", $TNumerosSpeciaux)."')";    
    }
    if($idImport)$sql.=" AND ea.idImport = '$idImport' ";
    
    $sql.=" GROUP BY ea.num_gsm"; //,ea.date_appel"; Je sais c'est moche
    
    if(isset($_REQUEST['DEBUG'])) print $sql;
    
    $resql = $db->query($sql);
    
    $total = array();
    
    // On récupère le tableau des numéros spéciaux (ceux à ne pas facturer)
    
    $r1=new TRH_Ressource;
    $r2=new TRH_Ressource;
    $user_ressource=new User($db);
    $TAnal=array();
    while($res = $db->fetch_object($resql)) {
        $gsm = trim($res->num_gsm);
        
        $non_facture = false;

        if($non_facture || $res->montant_euros_ht == 0) continue; // On sort pas les lignes à 0 dans le CSV
                
                    
        if(!$r1->load_by_numId($PDOdb, $gsm)) continue; // pas de ressource associée        
    
        $r2->load($PDOdb, $r1->fk_rh_ressource);        
    
        $id_user = $r2->isEmpruntee($PDOdb, $res->date_appel);
        if($id_user>0) {
        
            if($user_ressource->id!=$id_user) {
                    $user_ressource->fetch($id_user);
                    $user_ressource->fetch_optionals($user_ressource->id, array('COMPTE_TIERS' => ""));
                    $TAnal = TRH_analytique_user::getUserAnalytique($PDOdb, $id_user);  
            } 
            
            foreach($TAnal as $anal) {
                $total[$id_user][$gsm][$anal->code]['total'] += $res->montant_euros_ht * ($anal->pourcentage/100);
                $total[$id_user][$gsm][$anal->code]['total_nm'] += $res->montant_euros_ht ;
    
    
                /*
                 * On crée un tableau qui associe à chaque user la liste de ses codes analytiques
                 * A chaque code analytique est associé la ligne qui sera exportée
                 */
                $TabLigne[$id_user][$gsm][$anal->code] = array(
                        'nom'=>$user_ressource->lastname." ".$user_ressource->firstname
                        ,'fk_user'=>$id_user
                        ,'numero'=>$res->num_gsm
                        ,'email'=>$user_ressource->email
                        ,'compte_tier'=>$user_ressource->array_options['options_COMPTE_TIERS']
                        ,'code_agence'=>mb_strimwidth($user_ressource->array_options['options_COMPTE_TIERS'], 0, 3)
                        ,'code_analytique'=>$anal->code
                        ,'pourcentage'=>$anal->pourcentage
                        ,'total'=>$total[$id_user][$gsm][$anal->code]['total'] // Total qui va être calculé en fonction du pourcentage
                        ,'total_non_pondere'=>$total[$id_user][$gsm][$anal->code]['total_nm'] // Vrai total
                );
            
    
            }   
        }
        else{
            null;
        }

/*      if(!empty($TabLigne)){  
        var_dump($TabLigne);exit;}*/
        
    }
    
    /*
     * Pour chaque ligne du tableau $TabLigne, si certains user ont plusieurs codes analytiques,
     * on dispatch le montant à facturer en fonction du pourcentage correspondant au code analytique
     */
     
    //$TabLigne = _dispatchTarifsParCodeAnalytique($TabLigne);
    //_getFormattedArray($TabLigne); // TODO pas de fucking CSV ici, convertir à l'affichage //DODO beh vlà !
    
    return $TabLigne;
}
//TODO Delete, AA a priori plus utilisé
function _dispatchTarifsParCodeAnalytique(&$TabLigne) {
    
    $tab = array();
    
    foreach($TabLigne as $user_name => $TCodesAnalytiques) {
        if(count($TCodesAnalytiques) > 1) {
            foreach($TCodesAnalytiques as $code => $TArrayLines) {
                $tab[$user_name][$code] = $TArrayLines;
                $tab[$user_name][$code][count($TArrayLines)-2] = ($tab[$user_name][$code][count($TArrayLines)-2] * ($tab[$user_name][$code][count($TArrayLines)-3] / 100));
            }
        } else {
            $tab[$user_name] = $TCodesAnalytiques;
        }
    }
    
    return $tab;
    
}

//TODO Delete, AA a priori plus utilisé
function _getFormattedArray(&$TabLine) {
    
    foreach($TabLine as $user_name => $TCodesAnalytiques) {
        foreach($TCodesAnalytiques as $code => $line)
            $TabLine[$user_name][$code] = implode(";", $line);
    }
    
}

function _emprunt(&$PDOdb, $userId, $date_debut, $date_fin){
    global $user, $conf;
    
    $TabEmprunt=array();
    
    //on transforme la date du format timestamp en 2013-01-20
    //$timestamp = mktime(0,0,0,substr($date_debut, 3,2),substr($date_debut, 0,2), substr($date_debut, 6,4));
    $date_debut = date("Y-m-d", $date_debut);
    //$timestamp = mktime(0,0,0,substr($date_fin, 3,2),substr($date_fin, 0,2), substr($date_fin, 6,4));
    $date_fin = date("Y-m-d", $date_fin);
    
    $sql="SELECT libelle, numId 
    FROM ".MAIN_DB_PREFIX."rh_evenement as e
    LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (r.rowid=e.fk_rh_ressource)
    WHERE e.entity=".$conf->entity."
    AND e.fk_user=".$userId."
    AND (date_debut<='".$date_fin."' AND date_fin>='".$date_debut."')";
    
    $PDOdb->Execute($sql);
    while($PDOdb->Get_line()) {
        $TabEmprunt[]=array(
            'nom'=>$PDOdb->Get_field('libelle').' - '.$PDOdb->Get_field('numId')
            ,'date_debut'=>$PDOdb->Get_field('date_debut')
            ,'date_fin'=>$PDOdb->Get_field('date_fin')
        );
    }
    
    $PDOdb->close();
    return $TabEmprunt;
}

//TODO tu vois les 2 merdes là en dessous, tu prends le temps et tu me vire cette saloperie de là avec les compliments du chef
/**
 * prend un format 2013-03-19 00:00:00 et renvoie un timestamp
 */
function date2ToInt($chaine){
    return mktime(0,0,0,substr($chaine,5,2),substr($chaine,8,2),substr($chaine,0,4));
}
/*
 * prend un format d/m/Y et renvoie un timestamp
 */
function dateToInt($chaine){
    return mktime(0,0,0,substr($chaine,3,2),substr($chaine,0,2),substr($chaine,6,4));
}


function getContratLimit(&$PDOdb, $deb, $fin, $entity) {
    
    
$idVoiture = getIdType('voiture');


//chargement des voitures
$TVoitures = getRessource($idVoiture);
$sql = "SELECT r.rowid, fk_utilisatrice,  immatriculation , marquevoit, modlevoit, lastname, firstname, date_debut, date_fin
    FROM ".MAIN_DB_PREFIX."rh_ressource as r
    LEFT JOIN ".MAIN_DB_PREFIX."rh_evenement as e ON (
                                        e.type='emprunt' 
                                        AND r.rowid=e.fk_rh_ressource)
    LEFT JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid=e.fk_user)
    WHERE r.entity=".$conf->entity."
    AND fk_rh_ressource_type =".$idVoiture;

    //echo $sql;
$PDOdb->Execute($sql);
while($row = $PDOdb->Get_line()) {
    
    //echo $plagedeb.'   '.$row->date_debut.'<br>';
    $TVoitures[$row->rowid] = array(
        'societe'=>$row->fk_utilisatrice
        ,'fk_user'=>htmlentities($row->firstname.' '.$row->name, ENT_COMPAT , 'ISO8859-1')
        ,'immatriculation'=>$row->immatriculation
        ,'marque'=>$row->marquevoit
        ,'version'=>$row->modlevoit
        );
}


//chargement des contrats
$TContrats = array();
$sql="SELECT rowid, loyer_TTC, assurance, entretien, date_debut, date_fin, fk_tier_fournisseur
    FROM ".MAIN_DB_PREFIX."rh_contrat` 
    WHERE entity=".$conf->entity."
    ";
$PDOdb->Execute($sql);
while($row = $PDOdb->Get_line()) {
    $date_debut = mktime(0,0,0,substr($row->date_debut,5,2),substr($row->date_debut,8,2),substr($row->date_debut,0,4));
    $date_fin = mktime(0,0,0,substr($row->date_fin,5,2),substr($row->date_fin,8,2),substr($row->date_fin,0,4));
    $TContrats[$row->rowid] = array(
        'loyer'=>number_format($row->loyer_TTC,2).' €'
        ,'assurance'=>number_format($row->assurance,2).' €'
        ,'entretien'=>number_format($row->entretien,2).' €'
        ,'date_debut'=>date("d/m/Y", $date_debut)
        ,'date_fin'=>date("d/m/Y", $date_fin)
        ,'fk_soc'=>$row->fk_tier_fournisseur
        );
}

//chargement des associations
$TAssociations = array();
$sql="SELECT rowid, fk_rh_ressource, fk_rh_contrat 
    FROM ".MAIN_DB_PREFIX."rh_contrat_ressource` 
    WHERE entity=".$conf->entity;
$PDOdb->Execute($sql);
while($row = $PDOdb->Get_line()) {
    $TAssociations[$row->rowid] = array(
        'voiture'=>$row->fk_rh_ressource
        ,'contrat'=>$row->fk_rh_contrat
        );
}

//chargement des groupes
$TGroups = getGroups();

//chargement des fournisseurs
$TFournisseurs = array();
$sqlReq="SELECT rowid, nom FROM ".MAIN_DB_PREFIX."societe";
$PDOdb->Execute($sqlReq);
while($row = $PDOdb->Get_line()) {
    $TFournisseurs[$row->rowid] = htmlentities($row->nom, ENT_COMPAT , 'ISO8859-1'); 
    }


$TRetour = array();

$texte = '';
foreach ($TAssociations as $value) {
    $voiture = $TVoitures[$value['voiture']];
    $contrat = $TContrats[$value['contrat']]; 
    if (empty($voiture)){
        echo 'pas de voiture n°'.$value['voiture'].'<br>';      
    }
    else if (empty($voiture)){
        echo 'pas de contrat n°'.$value['contrat'].'<br>';      
    }
    else{
        if ( (dateToInt($contrat['date_fin'])<=$fin)
            &&
            (dateToInt($contrat['date_fin'])>=$deb) ){
            $TRetour[] = array(
                'societe'=>$TGroups[$voiture['societe']]
                ,'collaborateur'=>$voiture['fk_user']
                ,'immatriculation'=>$voiture['immatriculation']
                ,'marque'=>$voiture['marque']
                ,'version'=>$voiture['version']
                ,'loyer'=>$contrat['loyer']
                ,'assurance'=>$contrat['assurance']
                ,'entretien'=>$contrat['entretien']
                ,'date_debut'=>$contrat['date_debut']
                ,'date_fin'=>$contrat['date_fin']
                ,'fournisseur'=>$TFournisseurs[$contrat['fk_soc']]
            );
        
        }
        
        
    }       
}


    return $TRetour;
    
    
}

function getConsommation(&$PDOdb, $plagedeb, $plagefin, $fk_user,  $limite ) {
    $idTotal = getIdType('cartetotal');
    $idVoiture = getIdType('voiture');
    $TCartes = getRessource($idTotal);
    $TVoiture = getRessource($idVoiture);
        
    $TPleins = array();
    $sql="SELECT e.rowid, DATE_FORMAT(date_debut,'%d/%m/%Y') as point,  date_debut , 
        r.fk_rh_ressource as 'voiture', e.fk_rh_ressource as 'carte', e.motif, e.commentaire, 
        e.litreEssence, e.kilometrage, e.fk_user 
        FROM ".MAIN_DB_PREFIX."rh_evenement as e
        LEFT JOIN ".MAIN_DB_PREFIX."rh_ressource as r ON (e.fk_rh_ressource = r.rowid)
        WHERE (e.type='gazolepremier' OR e.type='gazoleexcellium') ";
    if ($fk_user!= 0){ $sql .= "AND e.fk_user=".$fk_user;}  
    $sql .= " ORDER BY kilometrage";
    //echo $sql;
    
    $TUser = getUsers(false, false);
    
    $PDOdb->Execute($sql);
    while($row = $PDOdb->Get_line()) {  
        $TPleins[$row->carte][$row->kilometrage] = array(
            //'idcarte'=>$row->fk_rh_ressource
            'km'=>$row->kilometrage
            ,'litre'=>$row->litreEssence
            ,'fk_user'=>$row->fk_user //firstname.' '.$row->name
            ,'fk_rh_ressource'=>$row->voiture
            ,'date'=>$row->point
            ,'date_debut'=>date2ToInt($row->date_debut)
        );
    }   
    
    
    $TRessource = array();
    $cpt = 0;
    
    //on lit une carte
    foreach ($TPleins as $idcarte => $value) {
        
        $memKm = 0;
        $memLitre = 0;
        $texte = '';
        $depassement = false;  //indique si il y a au moins un plein dépassement sur l'ensemble de la carte.
        $TTempLigne = array();
        
        $sommeEssence = 0;
        
        //on lit une ligne de plein de la carte.
        foreach ($value as $km => $tab) {
            $sommeEssence += $tab['litre'];
            $memLitre = $tab['litre'];
            
            //calcul de la consommation instantanée
            if ($memKm!=0){
                $conso = number_format((100*$memLitre)/($km-$memKm),2);
                $consotexte = $conso.'L/100km';
                $diffkmtexte = ($km-$memKm).'km';
                $essencetexte = number_format($tab['litre'],2).' L';
                
                if ($conso>=$limite){$depassement = true;}
                
                //on met en gras si il y a dépassement.
                if ($depassement && $limite>0){
                    $consotexte = '<b>'.$consotexte.'</b>'; 
                    $diffkmtexte = '<b>'.$diffkmtexte.'</b>'; 
                    $essencetexte = '<b>'.$essencetexte.'</b>';
                }
                
            }
            //ajout de la conso instantanée
            if (($tab['date_debut']<= $plagefin) && ($tab['date_debut']>= $plagedeb)){
                $TTempLigne[] = array(
                    'nom'=>$TCartes[$idcarte]
                    ,'vehicule'=>$TVoiture[$tab['fk_rh_ressource']]
                    ,'km'=>$tab['km'].' km'
                    ,'diffkm' =>  ($memKm!=0) ? $diffkmtexte : ''
                    ,'essence'=>($memKm!=0) ? $essencetexte : ''
                    ,'conso'=> ($memKm!=0) ? $consotexte : ''
                    ,'user'=>$TUser[$tab['fk_user']]
                    ,'date'=> $tab['date']
                    ,'ok'=>$tab['date_debut']
                    ,'parite'=>($cpt%2==0) ? 'pair' : 'impair'
                );
            $memKm = $km;
            
            }
        }
        
        //calcul et ajout de la consommation générale sur la carte Total
        $kmdebut = min(array_keys($value));
        $kmfin = max(array_keys($value));
        $diffkm = $kmfin-$kmdebut;
        if ($diffkm>0){
            //$Moyconso = number_format((100*$sommeEssence)/($diffkm),2);
            
            $TTempLigne[] = array(
                    'nom'=>''
                    ,'vehicule'=>''
                    ,'km'=>''
                    ,'diffkm' =>'Total: '.$diffkm.'km'
                    ,'conso'=> ''//($limite>0) ? '<b style="color:red;">Moyenne : '.$Moyconso.'L/100km</b>' : 'Moyenne : '.$Moyconso.'L/100km'
                    ,'essence'=>'Total: '.$sommeEssence.'L'
                    ,'date'=> ''
                    ,'user'=>''
                    ,'ok'=>''
                    ,'parite'=>($cpt%2==0) ? 'pair' : 'impair'
                );
            //echo $kmdebut.' km ->'.$kmfin.'km : '.$diffkm.'km.   '.$sommeEssence.' L Moyenne : '.$Moyconso.'L/100km <br>';
        }
        if ($depassement){//il y a eu dépasement, on ajoute les lignes de la carte au tableau final.
            $cpt++;
            foreach ($TTempLigne as $key => $value) {
                $TRessource[] = $value;}
        }
        
    }
 
 
    return $TRessource;   
}
