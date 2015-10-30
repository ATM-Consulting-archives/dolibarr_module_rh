<?php
class ActionsValideur
{
	 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */ 
      
      
    function formObjectOptions(&$parameters, &$object, &$action, $hookmanager) 
    { 
        global $db,$html,$user, $langs;
		if(in_array('ndfpcard', explode(':', $parameters['context']))) {
			
			if($action==='create' && $parameters['action']==='delegation') {
					$TUserDelegation=array();
					$TUserDelegation[]=$user->id;
					
					//on récupère les delegateurs du user et on les affiche
					$sql = "SELECT fk_object FROM ".MAIN_DB_PREFIX."user_extrafields WHERE fk_user_delegation=".$user->id;
					$result = $db->query($sql);
					if ($result)
					{
						$num = $db->num_rows($result);
						$i = 0;
						if ($num){
						    while ($i < $num){
						        $obj = $db->fetch_object($sql);
						        if ($obj){
									$TUserDelegation[]=$obj->fk_object;
									
						        }
						        $i++;
							}
						}
			     	}
					echo $html->select_dolusers($user->id, "fk_user",0,'','',$TUserDelegation );
					
					return 1;
				
			}
			
			
		}
		
		// TODO tout le code qui suit est de la merde, il faut le débugguer dans les prochainess versions
		if($action=='is_validator'){
			$user_id=$user->id;
			
			$sqlReq="SELECT *";
			$sqlReq.=" FROM ".MAIN_DB_PREFIX."rh_valideur_groupe as v";
			$sqlReq.=" WHERE v.fk_user=".$user_id;
			
			$result = $db->query($sqlReq);
			
			if($result->num_rows > 0){
				return 1;
			}else{
				return 0;
			}
		}elseif($action=='list_validateurs_groupe'){
			$group_id=$parameters[0];
			$user_id=$object->id;
			
			$sqlReq="SELECT *";
			$sqlReq.=" FROM ".MAIN_DB_PREFIX."rh_valideur_groupe as v";
			$sqlReq.=" WHERE v.fk_user=".$user_id;
			$sqlReq.=" AND v.fk_usergroup=".$group_id;
			
			$result = $db->query($sqlReq);
			if($result->num_rows > 0){
				return 1;
			}else{
				return 0;
			}
		}elseif($action=='has_vehicle'){
			$sqlReq="SELECT *";
			$sqlReq.=" FROM ".MAIN_DB_PREFIX."rh_ressource_type as t, ";
			$sqlReq.=MAIN_DB_PREFIX."rh_ressource as r, ";
			$sqlReq.=MAIN_DB_PREFIX."rh_evenement as e";
			$sqlReq.=" WHERE t.code='voiture'";
			$sqlReq.=" AND r.fk_rh_ressource_type=t.rowid";
			$sqlReq.=" AND e.fk_rh_ressource=r.rowid";
			$sqlReq.=" AND e.type='emprunt'";
			$sqlReq.=" AND e.fk_user=".$object->fk_user;
			$sqlReq.=" AND NOT (UNIX_TIMESTAMP(e.date_debut) > ".$object->dates;
			$sqlReq.=" AND UNIX_TIMESTAMP(e.date_fin) < ".$object->datee.")";
			$sqlReq.=" GROUP BY t.rowid";
			
			$result = $db->query($sqlReq);
			
			return $result;
		}elseif($action=='list_ndf'){
		 	/*
            * Récupération des Ids sur lesquels j'ai les droits
            */
            $resUsers=$db->query("SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE fk_user_delegation=".$user->id);
            $TUser=array($user->id);        
            while ($obj = $db->fetch_object($resUsers)){
                 $TUser[] = $obj->rowid;
            }
                   
            $sql = "SELECT n.rowid, n.ref, n.tms, n.total_ht, n.total_ttc, n.fk_user, n.statut, n.fk_soc, n.dates, n.datee,
			u.rowid as uid, u.lastname, u.firstname, s.nom AS soc_name, s.rowid AS soc_id, u.login, n.total_tva, SUM(p.amount) AS already_paid 
			FROM (((((".MAIN_DB_PREFIX."ndfp as n 
			LEFT JOIN ".MAIN_DB_PREFIX."ndfp_pay_det as p ON (p.fk_ndfp = n.rowid))
			       LEFT OUTER JOIN ".MAIN_DB_PREFIX."user as u ON (n.fk_user = u.rowid))
			               LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (s.rowid = n.fk_soc))
			                       LEFT OUTER JOIN ".MAIN_DB_PREFIX."usergroup_user as g ON (n.fk_user=g.fk_user))
			                            LEFT OUTER JOIN ".MAIN_DB_PREFIX."rh_valideur_groupe as v ON (g.fk_usergroup=v.fk_usergroup)) 
			                               
			WHERE n.entity = ".$object->entity;

            if(!$user->rights->ndfp->allactions->viewall) {

	         $sql.=" AND (n.fk_user IN (".implode(',', $TUser).")
			               OR (v.type='NDFP' AND v.fk_user = ".$user->id."
			                       AND (n.statut = 4 OR n.statut = 1)
			                       AND ((NOW() >= ADDDATE(n.tms, v.nbjours)) OR (n.total_ttc > v.montant) OR v.level>=n.alertLevel)
                   )
           	)";
			
	    }
			
		if ($parameters[0] == 'unpaid')
	        {
	            $sql.= " AND n.statut = 1";
	        }
			
			if ($parameters[1] > 0)
	        {
	            $sql .= " AND n.fk_soc = ".$parameters[1];
	        }
			
	        if ($parameters[2])
	        {
	            $sql.= ' AND n.ref LIKE \'%'.$db->escape(trim($parameters[2])).'%\'';
	        }
	        if ($parameters[3])
	        {
	            $sql.= ' AND s.nom LIKE \'%'.$db->escape(trim($parameters[3])).'%\'';
	        }
	        if ($parameters[4])
	        {
	            $sql.= ' AND (u.lastname LIKE \'%'.$db->escape(trim($parameters[4])).'%\' OR u.firstname LIKE \'%'.$db->escape(trim($parameters[4])).'%\')';
	        }
	
	        if ($parameters[5])
	        {
	            $sql.= ' AND n.total_ht = '.$db->escape(price2num(trim($parameters[5])));
	        }
	        if ($parameters[6])
	        {
	            $sql.= ' AND n.total_ttc = '.$db->escape(price2num(trim($parameters[6])));
	        }
	        if ($parameters[7])
	        {
	            $sql.= ' AND already_paid = '.$db->escape(price2num(trim($parameters[7])));
	        }
	
	        if ($parameters[8] > 0)
	        {
	            if ($parameters[9] > 0)
	            $sql.= " AND n.dates BETWEEN '".$db->idate(dol_get_first_day($parameters[9],$parameters[8],false))."' AND '".$db->idate(dol_get_last_day($parameters[9],$parameters[8],false))."'";
	            else
	            $sql.= " AND date_format(n.dates, '%m') = '".$parameters[8]."'";
	        }
	        else if ($parameters[9] > 0)
	        {
	            $sql.= " AND n.dates BETWEEN '".$db->idate(dol_get_first_day($parameters[9],1,false))."' AND '".$db->idate(dol_get_last_day($parameters[9],12,false))."'";
	        }
	
	        if ($parameters[10] > 0)
	        {
	            if ($parameters[11] > 0)
	            $sql.= " AND n.datee BETWEEN '".$db->idate(dol_get_first_day($parameters[11],$parameters[10],false))."' AND '".$db->idate(dol_get_last_day($parameters[11],$parameters[10],false))."'";
	            else
	            $sql.= " AND date_format(n.datee, '%m') = '".$parameters[10]."'";
	        }
	        else if ($parameters[11] > 0)
	        {
	            $sql.= " AND n.datee BETWEEN '".$db->idate(dol_get_first_day($parameters[11],1,false))."' AND '".$db->idate(dol_get_last_day($parameters[11],12,false))."'";
	        }
	
	        $sql.= ' GROUP BY n.rowid ORDER BY '.$parameters[12].' '.$parameters[13].', n.rowid DESC ';
	        $sql.= $db->plimit($parameters[14]+1, $parameters[15]);
			
			$result = $db->query($sql);
			
			return $result;
		}elseif($action=='validation'){
			//On récupère d'abord tous les groupes auxquels appartient l'utilisateur concerné par la note de frais
			$sql = "SELECT";
			$sql.= " g.fk_usergroup as 'group_id'";
			
		    $sql.= " FROM ".MAIN_DB_PREFIX."usergroup_user as g";
		    $sql.= " WHERE g.fk_user = ".$object->fk_user; // utilisateur de la note de frais
			
			$resql_group=$db->query($sql);
			
			if ($resql_group){
		        $num = $db->num_rows($resql_group);
		        $j = 0;
				
		        if ($num){
		            while ($j < $num){
		                $obj_group = $db->fetch_object($resql_group);
		                if ($obj_group){
							//On regarde ensuite pour chaque groupe retourné si l'utilisateur courant en est valideur
							
							
							$sql = "SELECT v.rowid, v.nbjours
								FROM ".MAIN_DB_PREFIX."rh_valideur_groupe v
								WHERE v.fk_user=".$user->id." AND (v.validate_himself=1 OR v.fk_user!=".$object->fk_user." )
								AND v.fk_usergroup =".$obj_group->group_id."
								AND v.type = 'NDFP'
								ORDER BY v.nbjours ASC";
									
						if(isset($_REQUEST['DEBUG'])) print $sql.'<br>'.$object->statut.'<br>';
							
							
							$result = $db->query($sql);
							if($result)
							{
								$obj = $db->fetch_object($sql);
							    if($obj)
							    {
									return 0;	
								}
							}
			                $j++;
			            }
			        }
				}
			}
			
			return 1;

		}
		else if($action=='buttons') {
			if($object->statut==4 && $user->rights->ndfp->allactions->showvalideur1) {
				$parameters['buttons'][]= '<a class="butActionDelete" href="javascript:ndfp_alert_next_level('.$object->id.')">Montrer au valideur + 1</a>';	
			}
			
			
		}
		
		elseif($parameters['action']=='delegation'){

			$idUsercourant=$_GET["id"];
			if (in_array('usercard',explode(':',$parameters['context']))){ 
	        // do something only for the context 'somecontext'
	        
	        dol_include_once('/core/class/html.form.class.php');
	          
			$form=new Form($db);
			  
	        if($action=='update') {
		        $fk_user_delegation = GETPOST('fk_user_delegation','int');
	          	$sql = "UPDATE llx_user SET fk_user_delegation=".$fk_user_delegation." WHERE rowid=".$idUsercourant;	
				$result = $db->query($sql);
			}else{ //on récupère le numéro du délégateur
				$sql = "SELECT fk_user_delegation FROM llx_user WHERE rowid=".$idUsercourant;	
				$result = $db->query($sql);
				if ($result){
                	$obj = $db->fetch_object($sql);
                    if ($obj){
						$fk_user_delegation=$obj->fk_user_delegation;
					}
			    }	
			}

			if($parameters['action']=='delegation'){    

	           ?><tr>
				      <td>
				      	Délégation Note de Frais      	
				      </td>	
				      <td>
				          	<?
						  	if($action=='edit'){				//on affiche la liste déroulante des utilisateurs
						      	echo $form->select_dolusers($fk_user_delegation, "fk_user_delegation", 1,$idUsercourant,0,'','', empty($conf->multicompany->transverse_mode) ? $user->entity : 1 );
						  	}else if($action=='update'||$action==''){		//on affiche le délégateur courant
								if($fk_user_delegation==0){
							    	print "Aucun délégué choisi";
								}else{
									$sql = "SELECT name,firstname FROM llx_user WHERE rowid=".$fk_user_delegation;	
									$result = $db->query($sql);
									if ($result){
										$num = $db->num_rows($result);
										$i = 0;
										if ($num){
										    while ($i < $num){
										        $obj = $db->fetch_object($sql);
										        if ($obj){
										        	echo $obj->firstname." ".$obj->name;
										        }
										        $i++;
										    }
										}	
								    }
								}
							}
				          	?>	
				      </td>
			      </tr>
			      <? 
			      }   
	        }else if (in_array('ndfpcard',explode(':',$parameters['context']))){
	        	if($action=="create"){		//au moment de la création de la ndf et non dans le default
					$tabDelegation=array();
					$k=0;
					$tabDelegation[$k]=$user->id;
					$k++;
					//on récupère les delegateurs du user et on les affiche
					$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE fk_user_delegation=".$user->id;
					$result = $db->query($sql);
					if ($result)
					{
						$num = $db->num_rows($result);
						$i = 0;
						if ($num){
						    while ($i < $num){
						        $obj = $db->fetch_object($sql);
						        if ($obj){
									$tabDelegation[$k]=$obj->rowid;
									$k++;
						        }
						        $i++;
							}
						}
			     	}
					echo $html->select_users($user->id, "fk_user",0,'','',$tabDelegation );

					}elseif($action=='edituser'){ //on est dans le default 

						$tabDelegation=array();
						$k=0;
						$tabDelegation[$k]=$user->id;
						$k++;
						//on récupère les delegateurs du user et on les affiche
						$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE fk_user_delegation=".$user->id;
						$result = $db->query($sql);
						if ($result)
						{
							$num = $db->num_rows($result);
						    $i = 0;
						    if ($num){
						        while ($i < $num){
						            $obj = $db->fetch_object($sql);
						            if ($obj){
										$tabDelegation[$k]=$obj->rowid;
										$k++;
						            }
						            $i++;
						        }
							}
						}
						echo $html->form_users($_SERVER['PHP_SELF'].'?id='.$object->id,$ndfp->fk_user,'fk_user','',$tabDelegation );	
	        	}
				return 1;
			}
			return 0; 
		}
	}


	function doActions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user, $html;  
		
		$idUsercourant=$user->id;
		dol_include_once('/core/class/html.form.class.php');
		$tabDelegation=array();
		$k=0;
		$tabDelegation[$k]=$user->id;
		$k++;
		 //on récupère les delegateurs du user et on les affiche
		 $sql = "SELECT rowid FROM llx_user WHERE fk_user_delegation=".$idUsercourant;	
		
		 $result = $db->query($sql);
		 if ($result)
		 {
				$num = $db->num_rows($result);
	                $i = 0;
	                if ($num)
	                {
	                        while ($i < $num)
	                        {
	                                $obj = $db->fetch_object($sql);
	                                if ($obj)
	                                {
										$tabDelegation[$k]=$obj->rowid;
										$k++;
	                                }
	                                $i++;
	                        }
	                }	
				
	     }
		 echo $html->select_users($user->id, "fk_user",0,'','',$tabDelegation );
		 return 1;  		
	}


}
