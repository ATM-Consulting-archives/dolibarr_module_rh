<script type="text/javascript">

$(function () {
    var chart;
    var chart2;
    
    $(document).ready(function () {
    	
    	// Build the chart
        chart = new Highcharts.Chart({
            chart: {
            	renderTo: "container",
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Graphiques'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage}%</b>',
            	percentageDecimals: 1
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                type: 'pie',
                name: 'Pourcentage',
	            data: [
	                ['Utilisateurs de niveau faible', [resultat.nb_faible;strconv=no;protect=no]],
	                ['Utilisateurs de niveau moyen', [resultat.nb_moyen;strconv=no;protect=no]],
	                ['Utilisateurs de niveau bon', [resultat.nb_bon;strconv=no;protect=no]],
	                ['Utilisateurs de niveau excellent', [resultat.nb_excellent;strconv=no;protect=no]],
	                {
	                    name: 'Utilisateurs sans la compétence',
	                    y: [resultat.nb_autres;strconv=no;protect=no],
	                    sliced: true,
	                    selected: true
	                }
	            ]
            }]
        });
    
    var colors = Highcharts.getOptions().colors,
        categories = ['Utilisateurs de niveau faible', 'Utilisateurs de niveau moyen', 'Utilisateurs de niveau bon', 'Utilisateurs de niveau excellent', 'Utilisateurs sans la compétence'],
        name = ' ',
        data = [{
                y: [resultat.faible;strconv=no;protect=no],
                color: colors[0],
                drilldown: {
                    name: 'Utilisateurs de niveau faible',
                    color: colors[0]
                }
            }, {
                y: [resultat.moyen;strconv=no;protect=no],
                color: colors[1],
                drilldown: {
                    name: 'Utilisateurs de niveau moyen',
                    color: colors[1]
                }
            }, {
                y: [resultat.bon;strconv=no;protect=no],
                color: colors[2],
                drilldown: {
                    name: 'Utilisateurs de niveau bon',
                    color: colors[2]
                }
            }, {
                y: [resultat.excellent;strconv=no;protect=no],
                color: colors[3],
                drilldown: {
                    name: 'Utilisateurs de niveau excellent',
                    color: colors[3]
                }
            }, {
                y: [resultat.autres;strconv=no;protect=no],
                color: colors[4],
                drilldown: {
                    name: 'Utilisateurs sans la compétence',
                    color: colors[4]
                }
            }];

    chart2 = new Highcharts.Chart({
        chart: {
        	renderTo: "container2",
            type: 'column'
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        xAxis: {
            categories: categories
        },
        yAxis: {
            title: {
                text: 'Pourcentage d\'utilisateurs'
            }
        },
        plotOptions: {
            column: {
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: colors[0],
                    style: {
                        fontWeight: 'bold'
                    },
                    formatter: function() {
                        return this.y.toFixed(2) +'%';
                    }
                }
            }
        },
        tooltip: {
            formatter: function() {
                var point = this.point,
                    s = this.x +':<b>'+ this.y.toFixed(2) +'%</b><br/>';
                return s;
            }
        },
        series: [{
            name: name,
            data: data,
            color: 'white'
        }]
    });
    
    });
    
});


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
			<tr>
				<td style="width:30%"><b>Sans la compétence</b></td>
				<td> [resultat.autres;block=tr;strconv=no;protect=no;frm=0,00]% ([resultat.nb_autres;strconv=no;protect=no] utilisateur(s))</td>
			</tr>
		</tbody>
	</table>
	<br/>
	
	<div id="container" style="width:700px;float:left;"></div>
	
	<div style="clear:both;"></div>
	<br><br>
	<div id="container2" style="width:700px;float:left;"></div>
	
	<div style="clear:both;"></div>
	<br><br>
	<a class="butAction" href="?">Retour</a>
	<div style="clear:both;"></div>
	
</div>