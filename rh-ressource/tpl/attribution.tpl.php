[onshow;block=begin;when [view.mode]=='view']   
   [view.head;strconv=no]
   [ressource.entete;strconv=no;protect=no]
[onshow;block=end] 	

[onshow;block=begin;when [view.mode]!='view']
    [view.onglet;strconv=no]

	[ressource.entete;strconv=no;protect=no]
	
    [onshow;block=begin;when [view.mode]=='new']
    	[ressource.titreNouvelleAttribution;strconv=no;protect=no]
    [onshow;block=end]
    
    [onshow;block=begin;when [view.mode]=='edit']
    	[ressource.titreModificationAttribution;strconv=no;protect=no]
    [onshow;block=end]
    
[onshow;block=end] 



	<table class="border" style="width:100%">
		[NEmprunt.fk_rh_ressource;strconv=no;protect=no]
		[NEmprunt.type;strconv=no;protect=no]
		<tr>
			<td style="width:20%">Utilisateur</td>
			<td>[NEmprunt.fk_user;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date début</td>
			<td>[NEmprunt.date_debut;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date fin</td>
			<td>[NEmprunt.date_fin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[NEmprunt.commentaire;strconv=no;protect=no]</td>
		</tr>
			

	</table>


<script>
	$(document).ready( function(){
		//on empêche que la date de début dépasse pas celle de fin
		function comparerDates(){
			jd = parseInt($("#date_debut").val().substr(0,2));
			md = parseInt($("#date_debut").val().substr(3,2));
			ad = parseInt($("#date_debut").val().substr(6,4));
			jf = parseInt($("#date_fin").val().substr(0,2));
			mf = parseInt($("#date_fin").val().substr(3,2));
			af = parseInt($("#date_fin").val().substr(6,4));
			if(af<ad){
				$("#date_fin").val($("#date_debut").val());
				return;
			}
			else if(af==ad){
				
				if(mf<md){
					$("#date_fin").val($("#date_debut").val());
					return;}
					
				else if(mf==md){
					
					if(jf<jd){
						$("#date_fin").val($("#date_debut").val());
						return;}
					else if(jf=jd){return;}
					else{return;}
					
				}
				else{return;}
			}
			else{return;}
			
			
		};
		
		$("#date_debut").change(comparerDates);
		$("#date_fin").change(comparerDates);
			
	});
</script>



[onshow;block=begin;when [view.userRight]==1]
[onshow;block=begin;when [view.mode]=='view']
	<div class="tabsAction" style="text-align:center;">
		<a class="butAction"  href="?id=[ressource.id]&idEven=[NEmprunt.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  
		onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[ressource.id]&idEven=[NEmprunt.id]&action=deleteAttribution'};">Supprimer</a>
		</div>
[onshow;block=end]
[onshow;block=end] 

[onshow;block=begin;when [view.userRight]==1]
[onshow;block=begin;when [view.mode]!='view']
	<div class="tabsAction" style="text-align:center;">
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; <input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	</div>
[onshow;block=end] 
[onshow;block=end] 


	

