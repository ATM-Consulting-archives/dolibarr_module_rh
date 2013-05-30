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
      ['Utilisateurs de niveau faible', [resultat.faible;strconv=no;protect=no]],
      ['Utilisateurs de niveau moyen', [resultat.moyen;strconv=no;protect=no]],
      ['Utilisateurs de niveau bon', [resultat.bon;strconv=no;protect=no]],
      ['Utilisateurs de niveau excellent', [resultat.excellent;strconv=no;protect=no]],
      ['Autres utilisateurs', [resultat.autres;strconv=no;protect=no]]
    ]);

    // Set chart options
    var options = {'title':'Graphique',
                   'width':400,
                   'height':300};

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
</script>

<div>			
	<h2 style="color: #2AA8B9;">Résultat de votre recherche</h2>	
	<br/>
	<table class="border" style="width:100%">	
		<tr>
			<td><b>Mots clés utilisés</b></td>	
		</tr>
		<tr>
			<td style="width:30%"> Libellé compétence </td>
			<td > [demande.nomTagRecherche;block=tr;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td style="width:30%"> Groupe </td>
			<td> [demande.nomGroupeRecherche;block=tr;strconv=no;protect=no]</td>
		</tr>
		<tr>
			<td style="width:30%"> Utilisateur </td>
			<td> [demande.nomUserRecherche;block=tr;strconv=no;protect=no]</td>
		</tr> 
	</table>	
	
	<br/><br/>
	<table class="border" style="width:100%">	
		<tr>
			<td><b>Statistiques</b></td>	
		</tr>
		<tr>
			<td style="width:30%">Niveau faible</td>
			<td > [resultat.faible;block=tr;strconv=no;protect=no;frm=0,00]%</td>
		</tr>
		<tr>
			<td style="width:30%">Niveau Moyen</td>
			<td> [resultat.moyen;block=tr;strconv=no;protect=no;frm=0,00]%</td>
		</tr>
		<tr>
			<td style="width:30%">Niveau Bon</td>
			<td> [resultat.bon;block=tr;strconv=no;protect=no;frm=0,00]%</td>
		</tr> 
		<tr>
			<td style="width:30%">Niveau Excellent</td>
			<td> [resultat.excellent;block=tr;strconv=no;protect=no;frm=0,00]%</td>
		</tr>
	</table>
	<br/>
	
	<div id="chart_div"></div>
	
</div>