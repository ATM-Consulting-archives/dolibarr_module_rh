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
        global $db,$html, $user; 
		
		$idUsercourant=$_GET["id"];
		if (in_array('usercard',explode(':',$parameters['context']))) 
		{ 
          // do something only for the context 'somecontext'
          
          dol_include_once('/core/class/html.form.class.php');
          
		  $form=new Form($db);
		  
          if($action=='update') {
	         $fk_user_delegation = GETPOST('fk_user_delegation','int');
          	 $sql = "UPDATE llx_user SET fk_user_delegation=$fk_user_delegation WHERE rowid=".$idUsercourant;	
			 $result = $db->query($sql);
		 }
		 else { //on récupère le numéro du délégateur
				 $sql = "SELECT fk_user_delegation FROM llx_user WHERE rowid=".$idUsercourant;	
				 $result = $db->query($sql);
				 if ($result)
				 {
                    $obj = $db->fetch_object($sql);
                    if ($obj)
                    {
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
					  	if($action=='edit') {				//on affiche la liste déroulante des utilisateurs
					      	echo $form->select_dolusers($fk_user_delegation, "fk_user_delegation", 1);
					  	}
			 	
						else if($action=='update'||$action==''){		//on affiche le délégateur courant
							 if($fk_user_delegation==0){
						         print "Aucun délégateur choisi";
							 }else 
						 	{
							 $sql = "SELECT name,firstname FROM llx_user WHERE rowid=".$fk_user_delegation;	
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
		else if (in_array('ndfpcard',explode(':',$parameters['context']))) 
        {
			$tabDelegation=array();
			$k=0;
			$tabDelegation[$k]=$idUsercourant;
			$k++;
			 //on récupère les delegateurs du user et on les affiche
			 $sql = "SELECT fk_user_delegation FROM llx_user WHERE rowid=".$idUsercourant;	
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
											$tabDelegation[$k]=$obj->fk_user_delegation;
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




	function doActions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user, $html;  
		$idUsercourant=$_GET["id"];
		 dol_include_once('/core/class/html.form.class.php');
		$tabDelegation=array();
		$k=0;
		$tabDelegation[$k]=$user->id;
		$k++;
		 //on récupère les delegateurs du user et on les affiche
		 $sql = "SELECT fk_user_delegation FROM llx_user WHERE rowid=".$idUsercourant;	
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
										$tabDelegation[$k]=$obj->fk_user_delegation;
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