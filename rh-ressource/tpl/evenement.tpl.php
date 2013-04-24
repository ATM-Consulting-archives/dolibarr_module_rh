[onshow;block=begin;when [view.mode]=='view']
    <div class="fiche"> <!-- begin div class="fiche" -->
    [view.head;strconv=no]
[onshow;block=end]                                

<div>
	<table class="border" style="width:100%">
		<tr>
			<td>Date début</td>
			<td>[NEvent.date_debut;block=tr;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Date fin</td>
			<td>[NEvent.date_fin;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Type</td>
			<td>[NEvent.type;strconv=no;protect=no]</td>
		</tr>
		<tr id="numFacture">
			<td>Numéro de facture</td>
			<td>[NEvent.numFacture;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Motif</td>
			<td>[NEvent.motif;strconv=no;protect=no]</td>[NEvent.fk_rh_ressource;strconv=no;protect=no]
		</tr>
		<tr id="user">
			<td >Utilisateur</td>
			<td>[NEvent.user;strconv=no;protect=no]</td>
		</tr>
		<tr id="responsabilite">
			<td >Responsabilité</td>
			<td>[NEvent.responsabilite;strconv=no;protect=no]</td>
			<script>
				$(document).ready(function(){$('#responsabilite').val(100);})
			</script>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[NEvent.commentaire;strconv=no;protect=no]</td>
		</tr>
		<tr id="numContrat">
			<td>Contrat associé</td>
			<td><a href="contrat.php?id=[NEvent.idContrat;strconv=no;protect=no]">[NEvent.numContrat;strconv=no;protect=no]</a></td>
		</tr>
		<tr>
			<td>Coût TTC</td>
			<td>[NEvent.coutTTC;strconv=no;protect=no] €</td>
		</tr>
		<tr>
			<td>Coût pour l'entreprise TTC</td>
			<td>[NEvent.coutEntrepriseTTC;strconv=no;protect=no] €</td>
		</tr>
		<tr>
			<td>TVA</td>
			<td>[NEvent.TVA;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Coût pour l'entreprise HT</td>
			<td>[NEvent.coutEntrepriseHT;strconv=no;protect=no] €</td>
		</tr>
	</table>
</div>

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
		
		function effacerChamps(){
			$('#user').hide();
			$('#responsabilite').hide();
			$('#numFacture').hide();
			$('#numContrat').hide();
			
		};
		
		function afficherSelonType(type){
			effacerChamps();
			switch (type.toLowerCase()){
				case 'accident':
					$('#user').show();
					$('#responsabilite').show();
					break;
				case 'facture':
					$('#numFacture').show();
					$('#numContrat').show();
				default : 
					break;}
		};
				
		$("#date_debut").change(comparerDates);
		$("#date_fin").change(comparerDates);
		
		[onshow;block=begin;when [view.mode]=='view']
		afficherSelonType('[NEvent.type;strconv=no;protect=no]');
		[onshow;block=end]
		
		[onshow;block=begin;when [view.mode]!='view']
		afficherSelonType($("#type option:selected").val());
		$("#type").change(function () {
			$("#type option:selected").each(function () {
				afficherSelonType($(this).val());
			});
		})
		[onshow;block=end]

	});
</script>

[onshow;block=begin;when [view.userRight]==1]
<div class="tabsAction" style="text-align:center;">
	[onshow;block=begin;when [view.mode]=='view']
		<a class="butAction"  href="ressource.php?id=[ressource.id]&action=view">Ressource associée</a>
		<a class="butAction"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=edit">Modifier</a>
		<a class="butActionDelete"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=deleteEvent">Supprimer</a>
	[onshow;block=end]
 
	[onshow;block=begin;when [view.mode]!='view']
		<input type="submit" value="Enregistrer" name="save" class="button">
		&nbsp; &nbsp; 
		<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
	[onshow;block=end]
	 
</div>
[onshow;block=end]
