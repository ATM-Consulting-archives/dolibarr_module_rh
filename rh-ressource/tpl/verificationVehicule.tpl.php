
<h1>Vérification des contrats</h1>

Contrats dont la fin est comprise entre [infos.date_debut;strconv=no;protect=no] et [infos.date_fin;strconv=no;protect=no] <br><br>

<script>

function ajax(){
	$.ajax({
			url: 'script/loadContratLimite.php?plagedebut='+$('#date_debut').val()+'&plagefin='+$('#date_fin').val()
		}).done(function(data) {
			liste = JSON.parse(data);
			$('#content').html('');
			for (var i=0; i<liste.length; i++){
				var texte = "<tr>"
					+"<td>"+liste[i].societe+"</td>"
					+"<td>"+liste[i].collaborateur+"</td>"
					+"<td>"+liste[i].immatriculation+"</td>"
					+"<td>"+liste[i].marque+"</td>"
					+"<td>"+liste[i].version+"</td>"
					+"<td>"+liste[i].loyer+"</td>"
					+"<td>"+liste[i].assurance+"</td>"
					+"<td>"+liste[i].entretien+"</td>"
					+"<td>"+liste[i].date_debut+"</td>"
					+"<td>"+liste[i].date_fin+"</td>"
					+"<td>"+liste[i].fournisseur+"</td>"
					+"</tr>";
				$('#content').html($('#content').html()+texte);
			}
		});
}

$('#date_debut').change(function(){
	ajax();	
});

$('#date_fin').change(function(){
	ajax();	
});

$(document).ready(function(){
	ajax();
});

</script>
<div>
	<table class="liste formdoc noborder" style="width:100%">
		<thead >
			<tr class="liste_titre">
				<td colspan="5" style="text-align:center;">Véhicule</td>
				<td colspan="6" style="text-align:center;">Contrat associé à ce véhicule</td>
			</tr>
			<tr class="liste_titre">
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
		</thead>
		
		<tbody id="content">
			[infos.texte;strconv=no;protect=no]
		</tbody>
	
	</table>
	

</div>


