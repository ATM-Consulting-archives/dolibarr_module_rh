
<h1>Vérification des consommations d'essence</h1>

Renseignez ici la limite de consommation : [infos.limite;strconv=no;protect=no] L/100km     [infos.valider;strconv=no;protect=no] <br><br>

<script>
/*
function ajax(){
	$.ajax({
			url: 'script/loadConsommationEssence.php?limite='+$('#limite').val()
		}).done(function(data) {
			liste = JSON.parse(data);
			$('#content').html('');
			for (var i=0; i<liste.length; i++){
				$('#content').html($('#content').html()+liste[i].nom+'<br>');
				$('#content').html($('#content').html()+liste[i].info);
			}
		});
}

$('#limite').live("keyup", function(){
	ajax();	
});

$(document).ready(function(){
	ajax();
});
*/
</script>
<div id="content">
<table class="liste formdoc noborder" style="width:100%">
	<thead >
		<tr class="liste_titre">
			<td>Carte Total</td>
			<td>Plein d'essence</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>[ressource.nom;block=tr;strconv=no;protect=no]</td>
			<td>[ressource.info;strconv=no;protect=no]</td>
		</tr>
	</tbody>
</table>
</div>


