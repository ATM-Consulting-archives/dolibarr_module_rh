<?php


/**
 *      Fonction envoyant un mail lors de la validation ou du refus d'une note de frais
 *
 * 		@param      db        	Database
 *      @param      object      Object action is done on
 *      @param      user        Object user
 * 		@param      langs       Object langs
 * 		@param      statut      Expense status
 *      @return     int         <0 if KO, 0 if no action are done, >0 if OK
 */
function send_mail(&$db, $object, $user, $langs, $statut)
{
	global $conf;
	
	// On récupère les informations de l'utilisateur
	
	$sql = "SELECT lastname,firstname,email FROM ".MAIN_DB_PREFIX."user WHERE rowid=".$object->fk_user;
	$resql_user=$db->query($sql);
	
	$obj_user = $db->fetch_object($resql_user);
    $name=$obj_user->name;
    $firstname=$obj_user->firstname;
    $email=$obj_user->email;

	/*
	$ATMdb->Execute($sql);
	while($ATMdb->Get_line()) {
		$name=$ATMdb->Get_field('lastname');
		$firstname=$ATMdb->Get_field('firstname');
		$email=$ATMdb->Get_field('email');
	}*/
	
	$from = USER_MAIL_SENDER;
	$sendto = $email;
	
	$TBS=new TTemplateTBS();
	if($object->statut==1){
		$subject = $object->ref." - Acceptée";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.acceptation.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
				)
			)
		);
	}elseif($object->statut==4){
		$subject = $object->ref." - Soumis à validation";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.soumission.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
				)
			)
		);
	}elseif($object->statut==3){
		$subject = $object->ref." - Refusée";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.refus.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
					,'motif'=>(isset($_REQUEST['cancelComment']) ?$_REQUEST['cancelComment']:'')
				)
			)
		);
	}elseif($object->statut==2){
		$subject = $object->ref." - Remboursée";
		$message = $TBS->render(dol_buildpath('/valideur/tpl/mail.validation.rembourse.tpl.php')
			,array()
			,array(
				'validation'=>array(
					'nom'=>$name
					,'prenom'=>$firstname
					,'ref'=>$object->ref
					,'total_ttc'=>$object->total_ttc
				)
			)
		);
	}
	
	// Send mail
	$mail = new TReponseMail($from,$sendto,$subject,$message);
	
	if(!empty($conf->global->NDFP_MAIL_COPY_VALIDATION))$mail->emailtoBcc = $conf->global->NDFP_MAIL_COPY_VALIDATION;
	
	dol_syslog("Valideur::sendmail content=$from,$sendto,$subject,$message", LOG_DEBUG);
	
    (int)$result = $mail->send(true, 'utf-8');
	//exit("SENDF MAIL $from,$sendto,$subject,$message");
	return (int)$result;
}

function extract_ndf(&$ATMdb, $date_debut, $date_fin, $type, $entity,$withLogin=0) {
    //var_dump($date_debut, $date_fin, $type, $entity,$withLogin);
    global $langs, $db, $user, $conf;
    
    $langs->load('ndfp@ndfp');
    $langs->load('main');
    
    $TabNdf=array();
    
    $date_debut=explode("/", $date_debut);
    $date_debut=date('Y-m-d',mktime(0, 0, 0, $date_debut[1], $date_debut[0], $date_debut[2]));
    $date_fin=explode("/", $date_fin);
    $date_fin=date('Y-m-d',mktime(0, 0, 0, $date_fin[1], $date_fin[0], $date_fin[2]));
    
    /**----***********************----**/
    /**----** Ligne de l'entité **----**/
    /**----***********************----**/
    
    $sql = "SELECT
            e.label as 'label'
            FROM ".MAIN_DB_PREFIX."entity as e
            WHERE e.rowid=".$entity;
    
    $ATMdb->Execute($sql);
    while($ATMdb->Get_line()) {
        $TabNdf[]=$ATMdb->Get_field('label');
    }
    
    /**----**********************----**/
    /**----** Lignes de débit **----**/
    /**----**********************----**/
    
    $sql = "SELECT
            t.accountancy_code
            ,SUM(l.total_ht) as 'total_ht', l.mission
            
            FROM ".MAIN_DB_PREFIX."ndfp_det as l
                LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp
                LEFT JOIN ".MAIN_DB_PREFIX."c_exp as t ON t.rowid = l.fk_exp
            
            WHERE n.statut = 1
            AND n.entity IN (".$entity.")
            AND n.type LIKE '".$type."'
            AND (n.date_valid>='".$date_debut."' AND n.date_valid<='".$date_fin."')
            GROUP BY t.accountancy_code";
	//Spécifque Sded
	if($conf->clisded->enabled){
		$sql .= ", l.mission";
	}
    
    if(isset($_REQUEST['DEBUG'])) {
        print $sql;
    }
    
    $ATMdb2=new TPDOdb;
    $ndf_exist=0;
            
    $ATMdb->Execute($sql);
    while($ATMdb->Get_line()) {
        $code_compta        =   $ATMdb->Get_field('accountancy_code');
		//Spécifque Sded
		if($conf->clisded->enabled && $ATMdb->Get_field('mission')) $code_compta == '625600';
		
        $total_ht           =   round($ATMdb->Get_field('total_ht'),2);
        
        $line = array('NDF', date('dmy'), 'OD', $code_compta, 'G', '', '', 'NOTE DE FRAIS '.date('m').'/'.date('Y'), 'V', date('dmy'), 'D', $total_ht, 'N', '', '', 'EUR', '', '');
        $TabNdf[]=$line;
        
        /*
         * Analytique
         */
        
        $sql_anal = "SELECT
                        l.rowid
                        , l.total_ht * IFNULL(  a.pourcentage, 100 ) / 100  as 'total_ht'
                        , a.code as 'code_analytique'
                        ,u.firstname,u.lastname,u.rowid as 'fk_user'
                    
                    FROM ".MAIN_DB_PREFIX."ndfp_det as l
                        LEFT JOIN ".MAIN_DB_PREFIX."ndfp as n ON n.rowid = l.fk_ndfp
                        LEFT JOIN ".MAIN_DB_PREFIX."c_exp as t ON t.rowid = l.fk_exp
                        LEFT JOIN ".MAIN_DB_PREFIX."rh_analytique_user as a ON a.fk_user = n.fk_user
                        LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid=n.fk_user
                    WHERE n.statut = 1
                    AND n.entity IN (".$entity.")
                    AND n.type LIKE '".$type."'
                    AND (n.date_valid>='".$date_debut."' AND n.date_valid<='".$date_fin."')
                    AND t.accountancy_code = ".$code_compta."
            
        ";

            
        if(isset($_REQUEST['DEBUG'])) {
            print $sql_anal;
        }
        
        $nb_parts=0;
        $new_code_compta=0;
        $ATMdb2->Execute($sql_anal);

        $TabAna=array();        
        while($ATMdb2->Get_line()) {

            $code_anal = $ATMdb2->Get_field('code_analytique');
            $total_anal = $ATMdb2->Get_field('total_ht');
//print_r($code_anal);
            if( $withLogin && empty( $code_anal ) ) {
                $code_anal = '<a href="'.HTTP.'custom/valideur/analytique.php?fk_user='.$ATMdb2->Get_field('fk_user').'">'. $ATMdb2->Get_field('firstname').' '.$ATMdb2->Get_field('lastname') ."</a>";
            }
            if(isset($_REQUEST['DEBUG'])) {
                print "$code_anal=$total_anal<br/>";
            }
            if(!isset($TabAna[$code_anal])) $TabAna[$code_anal]=0;
            $TabAna[$code_anal]+=$total_anal;
            /*$TabAna[] = array(
                $code_anal
                ,number_format($ATMdb2->Get_field('total_ht'),2,'.','' )
            );*/
        }

        $nbElement = count($TabAna);
        $total_partiel = 0;$cpt=0;
        foreach($TabAna as $code_analytique=>$total_ht_anal /*$ana*/) {
            //list($code_analytique,$total_ht_anal)=$ana ;
            
            if(isset($_REQUEST['DEBUG'])) {
                                print "<b>$code_analytique=$total_ht_anal</b><br/>";
                        }

            $total_ht_anal = round($total_ht_anal,2);

            if($cpt==$nbElement-1) $total_ht_anal = $total_ht - $total_partiel;
            $total_partiel+=$total_ht_anal;

             $TabNdf[] = array('NDF', date('dmy'), 'OD', $code_compta, 'A', $code_analytique, '', 'NOTE DE FRAIS '.date('m').'/'.date('Y'), 'V', date('dmy'), 'D', number_format($total_ht_anal,2,'.',''), 'N', '', '', 'EUR', '', '');
            $cpt++;
        }

        $ndf_exist=1;
        
    }
    
    /**----**********************----**/
    /**----**** Ligne de TVA ****----**/
    /**----**********************----**/
    
    if($ndf_exist){
        $sql = "SELECT CAST(SUM(n.total_tva) as DECIMAL(16,2)) as 'total_tva'
                    FROM ".MAIN_DB_PREFIX."ndfp as n
                    WHERE n.statut = 1
                    AND n.entity IN (".$entity.")
                    AND n.type LIKE '".$type."'
                    AND (n.date_valid>='".$date_debut."' AND n.date_valid<='".$date_fin."')";
        
        if(isset($_REQUEST['DEBUG'])) {
            print $sql;
        }
        
        $ATMdb->Execute($sql);
        while($ATMdb->Get_line()) {
            $total_tva_ndf  =   round($ATMdb->Get_field('total_tva'),2);
            
            $line = array('NDF', date('dmy'), 'OD', '445660', 'G', '', '', 'NOTE DE FRAIS '.date('m/Y'), 'V', date('dmy'), 'D', $total_tva_ndf, 'N', '', '', 'EUR', '', '');
            $TabNdf[]=$line;
        }
    }
    
    /**----**********************----**/
    /**----** Lignes de crédit **----**/
    /**----**********************----**/
    
    $sql = "SELECT
                    n.ref as 'ref'
                    ,CAST(n.total_ttc as DECIMAL(16,2)) as 'total_ttc'
                    ,n.datee as 'datef'
                    ,e.COMPTE_TIERS as 'compte_tiers'
                    ,u.login as 'login'
                    ,u.firstname as 'firstname'
                    ,u.lastname as 'lastname'
                    FROM ".MAIN_DB_PREFIX."ndfp as n
                    LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid = n.fk_user
                        LEFT JOIN ".MAIN_DB_PREFIX."user_extrafields as e ON u.rowid = e.fk_object
                WHERE n.statut = 1
                AND n.entity IN (".$entity.")
                AND n.type LIKE '".$type."'
                AND (n.date_valid>='".$date_debut."' AND n.date_valid<='".$date_fin."')
                GROUP BY n.rowid";
    
    if(isset($_REQUEST['DEBUG'])) {
        print $sql;
    }
    
    $ATMdb->Execute($sql);
    while($ATMdb->Get_line()) {
        $ref            =   $ATMdb->Get_field('ref');
        $compte_tiers   =   $ATMdb->Get_field('compte_tiers');
        
        if(isset($_REQUEST['withLogin'])) {
            $compte_tiers.=" (".$ATMdb->Get_field('firstname').' '.$ATMdb->Get_field('lastname').")";
        }
        
        $mois_ndf       =   substr($ATMdb->Get_field('datef'), 5, 2);
        $annee_ndf      =   substr($ATMdb->Get_field('datef'), 0, 4);
        //$datef_ndf        =   substr($ATMdb->Get_field('datef'), 8, 2).substr($ATMdb->Get_field('datef'), 5, 2).substr($ATMdb->Get_field('datef'), 2, 2);
        $total_ttc_ndf  =   round($ATMdb->Get_field('total_ttc'),2);
        
        $line = array('NDF', date('dmy'), 'OD', '425902', 'X', $compte_tiers, $ref, 'NOTE DE FRAIS '.$mois_ndf.'/'.$annee_ndf, 'V', date('dmy'), 'C', $total_ttc_ndf, 'N', '', '', 'EUR', '', '');
        $TabNdf[]=$line;
    }
    
    /* Equilibrage */
    
    $totalHT=0;
    $totalTTC=0;
    foreach($TabNdf as $ligne) {
        $credit = $ligne[10];   
        $montant = $ligne[11];
        $type =  $ligne[4];
        
        if($type=='G' && $credit=='D' && $ligne[3]!='445660') {
            $totalHT+=$montant;
        }
        else if($type=='X' && $credit=='C') {
            $totalTTC+=$montant;    
        }
        
    }
    
    foreach($TabNdf as &$ligne) {
        if($ligne[3]=='445660') {
            $ligne[11]=$totalTTC-$totalHT;
        }
    }
    
    return $TabNdf;
}
