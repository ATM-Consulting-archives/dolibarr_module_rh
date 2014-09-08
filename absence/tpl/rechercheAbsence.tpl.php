
	<div>			
		 [recherche.titreRecherche;block=tr;strconv=no;protect=no]
		<br/>
		<table class="border" style="width:60%">	
			<tr >
				<td colspan="2"><b>[translate.InformSearchAbsencesParameters;strconv=no]</b></td>	
			</tr>
			<tr >
				<td> [translate.StartDate;strconv=no]</td> <td> [recherche.date_debut;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr >
				<td> [translate.EndDate;strconv=no]</td> <td> [recherche.date_fin;block=tr;strconv=no;protect=no]</td>
			</tr>
			
			<tr >
				<td> [translate.Group;strconv=no]</td> <td> [recherche.TGroupe;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr >
				<td> [translate.User;strconv=no] </td><td> [recherche.TUser;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr >
				<td> [translate.Type;strconv=no] </td><td> [recherche.TTypeAbsence;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr >
				<td> [translate.NoHolidays;strconv=no]</td> <td> [recherche.horsConges;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr >
				<td colspan="2" style="text-align:center"> [recherche.btValider;block=tr;strconv=no;protect=no]</td>
			</tr>	
		</table>	
	</div>


[onshow;block=begin;when [userCourant.droitRecherche]!=1]
		[translate.NoRightsForSearchCollabAbsences;strconv=no]
[onshow;block=end]



	<script>
		$('#groupe').change(function(){
				$.ajax({
					url: 'script/loadUtilisateurs.php?groupe='+$('#groupe option:selected').val()
					,dataType:'json'
				}).done(function(liste) {
					$("#user").empty(); // remove old options
					$.each(liste, function(key, value) {
					  $("#user").append($("<option></option>")
					     .attr("value", key).text(value));
					});	
				});
		});
		
		
		</script>
	
