
<h1>Vérification des contrats</h1>

Contrats dont la fin est comprise entre [infos.date_debut;strconv=no;protect=no] et [infos.date_fin;strconv=no;protect=no] <br><br>

<script>

function ajax(){
	$.ajax({
			url: 'script/loadContratLimite.php?plagedebut='+$('#date_debut').val()+'&plagefin='+$('#date_fin').val()
		}).done(function(data) {
			//liste = JSON.parse(data);
			//alert(data);
			/*$('#content').html('');
			for (var i=0; i<liste.length; i++){
				$('#content').html($('#content').html()+liste[i].nom+'<br>');
				$('#content').html($('#content').html()+liste[i].info);
			}*/
		});
}
/*
$('#limite').live("keyup", function(){
	ajax();	
});
*/
$(document).ready(function(){
	ajax();
});

</script>
<div id="content">
	<table class="border" style="text-align:center;width:100%">
		<tr>
			<td colspan="5" >Véhicule</td>
			<td colspan="6" >Contrat associé à ce véhicule</td>
		</tr>
		<tr>
			<td>Société utilisatrice</td>
			<td>Nom collaborateur</td>
			<td>Immatriculation</td>
			<td>Marque</td>
			<td>Version</td>
			<td>Montant Loyer</td>
			<td>Montant assurance</td>
			<td>Montant entretien</td>
			<td>Date de début</td>
			<td>Date de fin</td>
			<td>Nom Leaseur</td>
		</tr>
	</table>
	

</div>


