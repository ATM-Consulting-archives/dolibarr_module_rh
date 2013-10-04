[onshow;block=begin;when [view.mode]=='view']
   
    [view.head;strconv=no]
[onshow;block=end]  

[onshow;block=begin;when [view.mode]!='view']
    [view.onglet;strconv=no]
[onshow;block=end] 

[ressource.entete;strconv=no;protect=no]

<br>


[ressource.titreEvenement;strconv=no;protect=no]

<div>
	<table class="border" style="width:100%">
		<tr>
			<td style="width:20%">Date début</td>
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
			<td>Référence externe</td>
			<td>[NEvent.refexterne;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Motif</td>
			<td>[NEvent.motif;strconv=no;protect=no]</td>[NEvent.fk_rh_ressource;strconv=no;protect=no]
		</tr>
		<tr>
			<td>Confidentiel</td>
			<td>[NEvent.confidentiel;strconv=no;protect=no]</td>
		</tr>
		<tr id="user">
			<td>Utilisateur</td>
			<td>
				[onshow;block=begin;when [view.mode]=='view']
					<a href="[ressource.URLroot;strconv=no;protect=no]/user/fiche.php?id=[NEvent.fk_user;strconv=no;protect=no]" >[NEvent.user;strconv=no;protect=no]</a>
				[onshow;block=end] 	
					
				[onshow;block=begin;when [view.mode]!='view']
					[NEvent.user;strconv=no;protect=no]
				[onshow;block=end]
			</td>
		</tr>
		<tr id="tiersimpl">
			<td >Tiers Impliqué</td>
			<td>[NEvent.tiersimplique;strconv=no;protect=no]</td>
		</tr>
		<tr id="responsabilite">
			<td >Responsabilité de l'utilisateur</td>
			<td>[NEvent.responsabilite;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td>Coût TTC</td>
			<td>[NEvent.coutTTC;strconv=no;protect=no] €</td>
		</tr>
		<tr>
			<td>Coût pour l'entreprise TTC</td>
			<td>[NEvent.coutEntrepriseTTC;strconv=no;protect=no] €</td>
		</tr>
		<script>
			function actuHT(){
				ttc = parseFloat($('#coutEntrepriseTTC').val());
				tva = parseFloat($('#TVA option:selected').html());
				ht = ttc*(1-(tva/100));
				ht = ht.toFixed(2)
				$('#coutEntrepriseHT').val(ht);
			}
			
			$('#coutEntrepriseTTC').live('keyup', function(){
				actuHT();});
			$(function() {$('#TVA').change(function(){actuHT();	});	});
		</script>
		
		<tr>
			<td>TVA</td>
			<td>[NEvent.TVA;strconv=no;protect=no] %</td>
		</tr>
		<tr>
			<td>Coût pour l'entreprise HT</td>
			<td>[NEvent.coutEntrepriseHT;strconv=no;protect=no] €</td>
		</tr>
		<tr>
			<td>Commentaire</td>
			<td>[NEvent.commentaire;strconv=no;protect=no]</td>
		</tr>
		<tr id="listeappels">
			<td>Liste des appels</td>
			<td><pre>[NEvent.appels;strconv=no;protect=no]</pre></td>
		</tr>
	</table>
</div>

<script>
	var ecartEntreDate = 0;

	$(document).ready( function(){
		//on empêche que la date de début dépasse pas celle de fin
		function comparerDates(idObj){
			dd = $("#date_debut").val().split("/");
			df = $("#date_fin").val().split("/");
			
			var dDebut = new Date(dd[2], dd[1]-1, dd[0], 0,0,0,0); 
			var dFin = new Date(df[2], df[1]-1, df[0], 0,0,0,0); 
			
				
			
			if(idObj=='date_debut') {
				dFin.setTime(dDebut.getTime()+ecartEntreDate);
				//alert(dFin+'='+ ecart);
				$("#date_fin").val($.datepicker.formatDate('dd/mm/yy', dFin ));
			} 	
			
			if(dDebut>dFin) {
				$("#date_fin").val($.datepicker.formatDate('dd/mm/yy', dDebut ));
			}	

			ecartEntreDate = dFin.getTime() - dDebut.getTime();
			

		}
		
		function effacerChamps(){
			$('#responsabilite').hide();
			$('#tiersimpl').hide();
			$('#numFacture').hide();
			$('#numContrat').hide();
			$('#listeappels').hide();
		}
		
		function afficherSelonType(type){
			effacerChamps();
			switch (type.toLowerCase()){
				case 'accident':
					$('#responsabilite').show();
					$('#tiersimpl').show();
					break;
				case 'facture':
					$('#numFacture').show();
					break;
				case 'facttel':
				case 'facture téléphonique' :
					$('#listeappels').show();
				default : 
					break;}
		};
				
		$("#date_debut").change(function() {
			comparerDates($(this).attr('id'));	
		});
		$("#date_fin").change(function() {
			comparerDates($(this).attr('id'));	
		});

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

</div>
<div class="tabsAction" style="text-align:center;">
	[onshow;block=begin;when [view.mode]=='view']
		[onshow;block=begin;when [view.userRight]==1]
			<a class="butAction"  href="?id=[ressource.id]&idEven=[NEvent.id]&action=edit">Modifier</a>
			<a class="butActionDelete" onclick="if (window.confirm('Voulez vous supprimer l\'élément ?')){document.location.href='?id=[ressource.id]&idEven=[NEvent.id]&action=deleteEvent'};">Supprimer</a>
		[onshow;block=end]
	[onshow;block=end]
 
	[onshow;block=begin;when [view.mode]!='view']
		[onshow;block=begin;when [view.userRight]==1]
			<input type="submit" value="Enregistrer" name="save" class="button">
			&nbsp; &nbsp; 
			<input type="button" value="Annuler" name="cancel" class="button" onclick="document.location.href='?id=[ressource.id]'">
		[onshow;block=end]
	[onshow;block=end]
<div style="clear:both"></div>

