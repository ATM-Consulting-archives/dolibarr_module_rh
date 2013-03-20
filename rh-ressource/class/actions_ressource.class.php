<?php

class ActionsRessource
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
		
		
		if (in_array('ndfpcard',explode(':',$parameters['context']))){
			
        	if($action=="create"){ //au moment de la création de la ndf et non dans le default
				?><script>
					fk_user = $('#fk_user option:selected').val();
					dates = $('#d').val();
					datee = $('#f').val();
					
					$('body').click(function(){
						fk_user = $('#fk_user option:selected').val();
						dates = $('#d').val();
						datee = $('#f').val();
					});
				
					$.ajax({
						url : '<?=DOL_URL_ROOT_ALT ?>/ressource/script/utilisateur-a-un-vehicule.php?fk_user='+fk_user+'&dates='+dates+'&datee='+datee
						,dataType: 'json'
						,data: {
							json:1
						}
						
					})
					.then(function ( vehicule ) {
						if(vehicule.result=="0") {
							null;
						}else{
							$('select[name=fk_cat]').val(4);
							$('select[name=fk_cat]').parent().parent().hide();
						}
					});
				</script><?
			}else{ //on est dans le default 
					
        	}
		
		}
	}


	function doActions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user, $html;  
		
		
			
	}


}
