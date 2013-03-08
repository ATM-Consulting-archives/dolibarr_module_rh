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
        global $db; 
		
        if (in_array('usercard',explode(':',$parameters['context']))) 
        { 
          // do something only for the context 'somecontext'
          
          dol_include_once('/core/class/html.form.class.php');
          
		  $form=new Form($db);
		  
          if($action=='update') {
	         $fk_user_delegation = GETPOST('fk_user_delegation','int');
          	 $sql = "UPDATE llx_user SET fk_user_delegation=$fk_user_delegation WHERE rowid=".$object->id;	
			 $result = $db->query($sql);
		  }
		else {
			$sql = "SELECT fk_user_delegation FROM llx_user WHERE rowid=".$object->id;	
			 if ($result)
			 {
					$obj = $this->db->fetch_object($result);	
					$fk_user_delegation->$obj->fk_user_delegation;	
    	      }
          
			
			}		 
          
           ?><tr>
		      <td>
		      	Déléguation Note de Frais      	
		      </td>	
		      	<td><?=$action.$fk_user_delegation ?>
		          	<?
			  	if($action=='edit') {
			      	echo $form->select_dolusers($fk_user_delegation, "fk_user_delegation", 1);
			  	}	
		          	?>
		      		
		      	</td>
		      </tr>
		      <?
		     
        }
	else if (in_array('ndfpcard',explode(':',$parameters['context']))) 
        {
        
			?>
			liste user + déléguation
			<?
			
			return 1;
		} 
 
        return 0;
	}

	function doActions($parameters, &$object, &$action, $hookmanager) 
    { 
        if (in_array('usercard',explode(':',$parameters['context']))) 
        { 
          // do something only for the context 'somecontext'
          
          dol_include_once('/core/class/html.form.class.php');
          
		  $form=new Form($db);
		  
			$sql = "SELECT fk_user_delegation FROM llx_user WHERE rowid=".$object->id;	
			 if ($result)
			 {
					$obj = $this->db->fetch_object($result);	
					$fk_user_delegation->$obj->fk_user_delegation;	
    	      }
          
			
		}		 
          
   
	  	if($action=='create') {
	      	echo $form->select_dolusers($fk_user_delegation, "fk_user_delegation", 1);
	  	}	

		     
        
		
			
			return 1;

	}



}