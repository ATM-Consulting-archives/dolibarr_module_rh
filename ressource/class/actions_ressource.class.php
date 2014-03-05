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
								url : '<?=dol_buildpath('/ressource/script/utilisateur-a-un-vehicule.php',1) ?>?fk_user='+fk_user+'&dates='+dates+'&datee='+datee
								,dataType: 'json'
								,data: {
									json:1
								}
								
							})
							.then(function ( vehicule ) {
								if(vehicule.result=="0") {
									$('#has_a_vehicle').hide();
								}else{
									$('#has_a_vehicle').text("Vous disposez d'un véhicule de la société.");
								}
							});
						});
						
						$.ajax({
							url : '<?=dol_buildpath('/ressource/script/utilisateur-a-un-vehicule.php',1) ?>?fk_user='+fk_user+'&dates='+dates+'&datee='+datee
							,dataType: 'json'
							,data: {
								json:1
							}
							
						})
						.then(function ( vehicule ) {
							if(vehicule.result=="0") {
								$('#has_a_vehicle').hide();
							}else{
								$('#has_a_vehicle').text("Vous disposez d'un véhicule de la société.");
							}
						});
					});
				</script><?
			}elseif($action=="vehiculeAttributed"){
				/*$sqlReq="SELECT *";
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
				}*/
				
				$sqlReq="SELECT *";
				$sqlReq.=" FROM ".MAIN_DB_PREFIX."ndfp as n";
				$sqlReq.=" WHERE n.rowid=".$object->id;
				$sqlReq.=" AND n.fk_cat = 0";
				
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
					
					return $resultReq->num_rows;
				}else{
					?>
			    	<script>
			    	$(document).ready(function(){
			    		$('#fk_exp option[value='+ <? echo $obj->rowid ?> +']').show();
			    	});
			    	</script>
			    	<?
					
					return $resultReq->num_rows;
				}
			}
		
		}
	}


	function doActions($parameters, &$object, &$action, $hookmanager) 
    {
    	global $db, $user, $html;  
		
		
			
	}


}
