
<h1>Vérification des consommations d'essence</h1>

Renseignez ici la limite de consommation : [infos.limite;strconv=no;protect=no] L/100km<br><br>

<script>

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

</script>
<div id="content">
	
</div>


