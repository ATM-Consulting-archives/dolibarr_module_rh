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
        	if($action=="createNdF"){ //au moment de la création de la ndf et non dans le default
				?><script>
					$(document).ready(function(){
						var fk_user = $('#fk_user option:selected').val();
						var dates = $('#d').val();
						var datee = $('#f').val();
						
						$('body').click(function(){
							fk_user = $('#fk_user option:selected').val();
							dates = $('#d').val();
							datee = $('#f').val();
							
							$.ajax({
								url : '<?=DOL_URL_ROOT_ALT ?>/ressource/script/utilisateur-a-un-vehicule.php?fk_user='+fk_user+'&dates='+dates+'&datee='+datee
								,dataType: 'json'
								,data: {
									json:1
								}
								
							})
							.then(function ( vehicule ) {
								if(vehicule.result=="0") {
									$('select[name=fk_cat]').parent().parent().show();
								}else{
									$('select[name=fk_cat]').val(4);
									$('select[name=fk_cat]').parent().parent().hide();
								}
							});
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
								$('select[name=fk_cat]').parent().parent().show();
							}else{
								$('select[name=fk_cat]').val(4);
								$('select[name=fk_cat]').parent().parent().hide();
							}
						});
					});
				</script><?
			}elseif($action=="vehiculeAttributed"){
				$sqlReq="SELECT *";
				$sqlReq.=" FROM ".MAIN_DB_PREFIX."rh_ressource_type as t, ";
				$sqlReq.=MAIN_DB_PREFIX."rh_ressource as r, ";
				$sqlReq.=MAIN_DB_PREFIX."rh_evenement as e";
				$sqlReq.=" WHERE t.code='voiture'";
				$sqlReq.=" AND r.fk_rh_ressource_type=t.rowid";
				$sqlReq.=" AND e.fk_rh_ressource=r.rowid";
				$sqlReq.=" AND e.type='emprunt'";
				$sqlReq.=" AND e.fk_user=".$object->fk_user;
				$sqlReq.=" AND NOT (UNIX_TIMESTAMP(e.date_debut) > ".$object->datee;
				$sqlReq.=" OR UNIX_TIMESTAMP(e.date_fin) < ".$object->dates.")";
				$sqlReq.=" GROUP BY t.rowid";
				
				$resultReq = $db->query($sqlReq);
				
				$sql="SELECT e.rowid";
				$sql.=" FROM ".MAIN_DB_PREFIX."c_exp as e";
				$sql.=" WHERE e.code='EX_KME'";
				
				$result = $db->query($sql);
				$obj = $db->fetch_object($sql);
				
				if($resultReq->num_rows > 0){
					?>
			    	<script>
			    	$(document).ready(function(){
			    		$('#fk_exp option[value='+ <? echo $obj->rowid ?> +']').hide();
			    	});
			    	</script>
			    	<?
					
					return 1;
				}else{
					?>
			    	<script>
			    	$(document).ready(function(){
			    		$('#fk_exp option[value='+ <? echo $obj->rowid ?> +']').show();
			    	});
			    	</script>
			    	<?
					
					return 0;
				}
			}
		
		}
	}


	function doActions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user, $html;  
		
		
			
	}


}
