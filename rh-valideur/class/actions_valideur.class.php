<?php
class ActionsValideur
{
	 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */ 
      
    function formObjectOptions($parameters, &$object, &$action, $hookmanager) 
    { 
        global $db,$html,$user;
		
		if($action=='list_test'){
			$sql = "SELECT n.rowid, n.ref, n.tms, n.total_ht, n.total_ttc, n.fk_user, n.statut, n.fk_soc, n.dates, n.datee,";
	        $sql.= " u.rowid as uid, u.name, u.firstname, s.nom AS soc_name, s.rowid AS soc_id, u.login, n.total_tva, SUM(p.amount) AS already_paid";
	        $sql.= " FROM ".MAIN_DB_PREFIX."rh_valideur_groupe as v, ".MAIN_DB_PREFIX."usergroup_user as a, ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."ndfp as n";
	        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = n.fk_soc";
	        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."ndfp_pay_det as p ON p.fk_ndfp = n.rowid";
	        $sql.= " WHERE n.entity = ".$object->entity;
			$sql.= " AND ((n.fk_user = ".$user->id;
			
			if ($parameters[0] == 'unpaid')
	        {
	            $sql.= " AND n.statut = 1";
	        }
			
			$sql.= ") OR (n.fk_user = a.fk_user";
			$sql.= " AND u.rowid = a.fk_user";
			$sql.= " AND a.fk_usergroup = v.fk_usergroup";
			$sql.= " AND v.fk_user = ".$user->id;
			$sql.= " AND n.statut = 4";
			$sql.= " AND NOW() >= ADDDATE(n.tms, v.nbjours)))";
			
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
	            $sql.= ' AND (u.name LIKE \'%'.$db->escape(trim($parameters[4])).'%\' OR u.firstname LIKE \'%'.$db->escape(trim($parameters[4])).'%\')';
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
		}
		
		if($action=='validation'){
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
							$sql = "SELECT v.rowid, v.nbjours";
							$sql.= " FROM ".MAIN_DB_PREFIX."rh_valideur_groupe v";
							$sql.= " WHERE v.fk_user = ".$user->id;
							$sql.= " AND v.fk_usergroup =".$obj_group->group_id;
							$sql.= " AND v.type = 'NDFP'";
							$sql.= " ORDER BY v.nbjours ASC";
							
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
		}else{
			$idUsercourant=$_GET["id"];
			if (in_array('usercard',explode(':',$parameters['context']))){ 
	        // do something only for the context 'somecontext'
	          
	        dol_include_once('/core/class/html.form.class.php');
	          
			$form=new Form($db);
			  
	        if($action=='update') {
		        $fk_user_delegation = GETPOST('fk_user_delegation','int');
	          	$sql = "UPDATE llx_user SET fk_user_delegation=$fk_user_delegation WHERE rowid=".$idUsercourant;	
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
	           ?><tr>
				      <td>
				      	Délégation Note de Frais      	
				      </td>	
				      <td>
				          	<?
						  	if($action=='edit'){				//on affiche la liste déroulante des utilisateurs
						      	echo $form->select_dolusers($fk_user_delegation, "fk_user_delegation", 1);
						  	}else if($action=='update'||$action==''){		//on affiche le délégateur courant
								if($fk_user_delegation==0){
							    	print "Aucun délégateur choisi";
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
	        }else if (in_array('ndfpcard',explode(':',$parameters['context']))){
	        	if($action=="create"){		//au moment de la création de la ndf et non dans le default
					$tabDelegation=array();
					$k=0;
					$tabDelegation[$k]=$user->id;
					$k++;
					//on récupère les delegateurs du user et on les affiche
					$sql = "SELECT rowid FROM llx_user WHERE fk_user_delegation=".$user->id;
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
					}else{ //on est dans le default 
						$tabDelegation=array();
						$k=0;
						$tabDelegation[$k]=$user->id;
						$k++;
						//on récupère les delegateurs du user et on les affiche
						$sql = "SELECT rowid FROM llx_user WHERE fk_user_delegation=".$user->id;
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