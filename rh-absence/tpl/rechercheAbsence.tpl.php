
	<div>			
		 [recherche.titreRecherche;block=tr;strconv=no;protect=no]
		<br/>
		<table class="border" style="width:100%">	
			<tr>
				<td><b>Veuillez renseigner les paramètres pour la recherche des absences</b></td>	
			</tr>
			<tr>
				<td>
					Date début [recherche.date_debut;block=tr;strconv=no;protect=no]
					Date fin [recherche.date_fin;block=tr;strconv=no;protect=no]
					<br/>
					Groupe [recherche.TGroupe;block=tr;strconv=no;protect=no]
					Utilisateur [recherche.TUser;block=tr;strconv=no;protect=no]
					Aucun congés [recherche.horsConges;block=tr;strconv=no;protect=no]
					[recherche.btValider;block=tr;strconv=no;protect=no]
					
				</td>
			</tr>
		</table>	
	</div>


[onshow;block=begin;when [userCourant.droitRecherche]!=1]
		Vous ne possédez pas les droits pour effectuer une recherche sur les absences des collaborateurs
[onshow;block=end]



	<script>
		$('#groupe').change(function(){
				$.ajax({
					url: 'script/loadUtilisateurs.php?groupe='+$('#groupe option:selected').val()
				}).done(function(data) {
					liste = JSON.parse(data);
					$("#user").empty(); // remove old options
					$.each(liste, function(key, value) {
					  $("#user").append($("<option></option>")
					     .attr("value", key).text(value));
					});	
				});
		});
		
		$(document).ready( function(){
			$.ajax({
					url: 'script/loadUtilisateurs.php?groupe='+$('#groupe option:selected').val()
				}).done(function(data) {
					liste = JSON.parse(data);
					$("#user").empty(); // remove old options
					$.each(liste, function(key, value) {
					  $("#user").append($("<option></option>")
					     .attr("value", key).text(value));
					});	
			});
		});
		</script>
	
