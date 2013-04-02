[onshow;block=begin;when [view.mode]=='view']
  
<div class="fiche">
    <div class="tabBar">
		<h2>
			Liste de vos liens de validation
		</h2>
	
		<div>
							
			<table class="border" style="width:100%">			
				<tr>
					<td>Groupe</td>
					<td>Type</td>
					<td>Nombre de jours avant alerte</td>
					<td>Montant avant alerte</td>
					<td>Action</td>
					
				</tr>
				<tr>
					<td>[validations.group;block=tr;strconv=no;protect=no]</td>
					<td>[validations.type;strconv=no;protect=no]</td>
					<td>[validations.nbjours;strconv=no;protect=no]</td>
					<td>[validations.montant;strconv=no;protect=no]€</td>
					<td><img src="./img/delete.png"  style="cursor:pointer;" onclick="document.location.href='?fk_user=[userCourant.id]&deleteId=[validations.id]&action=delete'"></td>
				</tr>
			</table>
			
			<div class="tabsAction" >
				<a class="butAction"  href="?fk_user=[userCourant.id]&action=add">Ajouter</a>
			</div>
		</div>
	</div>
</div>


[onshow;block=end] 


[onshow;block=begin;when [view.mode]=='edit']
<table
	<tr>
		<td>Groupe</td>
		<td>Type</td>
		<td>Nombre de jours avant alerte</td>
		<td id="textMontant">Montant avant alerte</td>
	</tr>
	<tr>
		<td>[valideur.group;strconv=no;protect=no]</td>
		<td>[valideur.type;strconv=no;protect=no]</td>
		<td>[valideur.nbjours;strconv=no;protect=no]</td>
		<td id="textMontant2">[valideur.montant;strconv=no;protect=no]€</td>
	</tr>
</table>

<div class="tabsAction" >
	<input type="submit" value="Enregistrer" name="save" class="button">
	&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?fk_user=[userCourant.id]'">
</div>
[onshow;block=end]


<script>
	$(document).ready( function(){

		$('#type').change(function () {
      		  if($("#type option:selected").val()=="Conges"){
      		  		$('#textMontant').hide();
      		  		$('#textMontant2').hide();
      		  }
      		  else{
      		  		$("#textMontant").show();
      		  		$('#textMontant2').show();
      		  }
   		})
   		 
		$('#montant').click( 	function(){
			//if($("#date_debut").val()>$("#date_fin").val()){
			
			
		});	
		
	});
</script>


