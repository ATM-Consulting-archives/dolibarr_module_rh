<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

  // Load the Visualization API and the piechart package.
  google.load('visualization', '1.0', {'packages':['corechart']});

  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawChart);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawChart() {

    // Create the data table.
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Topping');
    data.addColumn('number', 'Slices');
    data.addRows([
      ['Utilisateurs de niveau faible', [resultat.nb_faible;strconv=no;protect=no]],
      ['Utilisateurs de niveau moyen', [resultat.nb_moyen;strconv=no;protect=no]],
      ['Utilisateurs de niveau bon', [resultat.nb_bon;strconv=no;protect=no]],
      ['Utilisateurs de niveau excellent', [resultat.nb_excellent;strconv=no;protect=no]],
      ['Utilisateurs sans la compétence', [resultat.nb_autres;strconv=no;protect=no]]
    ]);

    // Set chart options
    var options = {'title':'Graphique',
                   'width':500,
                   'height':250};

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
</script>

<div>			
	[resultat.titreRecherche;block=tr;strconv=no;protect=no]	
	<br/>
	<table class="liste formdoc noborder" style="width:700px">
		<thead>
			<tr class="liste_titre">
				<th colspan="2" style="font-size:140%">Mots clés utilisés</th>	
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width:30%"><b>Libellé compétence</b></td>
				<td > [demande.nomTagRecherche;block=tr;strconv=no;protect=no]</td>
			</tr>
			<tr>
				<td style="width:30%"><b>Groupe</b></td>
				<td> [demande.nomGroupeRecherche;block=tr;strconv=no;protect=no]</td>
			</tr>
		</tbody>
	</table>	
	
	<br/><br/>
	<table class="liste formdoc noborder" style="width:700px">
		<thead>
			<tr class="liste_titre">
				<td colspan="2" style="font-size:140%">Statistiques</td>	
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width:30%"><b>Niveau Faible</b></td>
				<td > [resultat.faible;block=tr;strconv=no;protect=no;frm=0,00]% ([resultat.nb_faible;strconv=no;protect=no] utilisateur(s))</td>
			</tr>
			<tr>
				<td style="width:30%"><b>Niveau Moyen</b></td>
				<td> [resultat.moyen;block=tr;strconv=no;protect=no;frm=0,00]% ([resultat.nb_moyen;strconv=no;protect=no] utilisateur(s))</td>
			</tr>
			<tr>
				<td style="width:30%"><b>Niveau Bon</b></td>
				<td> [resultat.bon;block=tr;strconv=no;protect=no;frm=0,00]% ([resultat.nb_bon;strconv=no;protect=no] utilisateur(s))</td>
			</tr> 
			<tr>
				<td style="width:30%"><b>Niveau Excellent</b></td>
				<td> [resultat.excellent;block=tr;strconv=no;protect=no;frm=0,00]% ([resultat.nb_excellent;strconv=no;protect=no] utilisateur(s))</td>
			</tr>
		</tbody>
	</table>
	<br/>
	
	<div id="chart_div"></div>
	
	<a class="butAction" href="?">Retour</a>
	<div style="clear:both;"></div>
	
</div>