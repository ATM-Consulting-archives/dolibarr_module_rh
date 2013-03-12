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
							$sql = "SELECT v.rowid";
							$sql.= " FROM ".MAIN_DB_PREFIX."rh_valideur_groupe v";
							$sql.= " WHERE v.fk_user = ".$user->id;
							$sql.= " AND v.fk_usergroup =".$obj_group->group_id;
							$sql.= " AND v.type = 'NDFP'";
							
							print $sql;
							
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