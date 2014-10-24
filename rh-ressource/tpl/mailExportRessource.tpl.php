<html>
	<head>
		<style type="text/css">
			table {
				width:100%;
				border-collapse: collapse;
				border:0;
			}
			table td, table th {
				border:0;
			}
			table th {
				text-transform: uppercase;
				border-bottom: 2px solid #000033;
			}
			
			table.ligne tr.total td {
				
				border-top: 2px solid #000033;
			}
			
			
			table td.title {
				text-transform: uppercase;
				color:#000033;
				font-weight: bold;
				white-space: nowrap;
			}
			
			h1 {
				text-transform: uppercase;
				color:#000033;
			}
			
		</style>
		
	</head>
	<body>

	<h1>Etat des appels sur téléphone mobile</h1>

	<table border="1" width="100%">
		<tr>
			<td class="title" width="10%">Nom : </td>
			<td>[card.username]</td>
			<td class="title"  width="10%">Date Facture : </td>
			<td>[card.date_facture]</td>
		</tr>
		<tr>
			<td class="title">numéro de GSM : </td>
			<td>[card.gsm]</td>
		</tr>
	</table>
<p>&nbsp;</p>
	<table border="1" class="ligne" width="100%">
		<tr>
			<th>Date d'appel</th>
			<th>Heure d'appel</th>
			<th>Numéro appelé</th>
			<th>Type d'appel</th>
			<th>Durée</th>
		</tr>
		<tr>
			<td align="center">[line.date_appel;block=tr]</td>
			<td align="center">[line.heure_appel]</td>
			<td align="center">[line.numero]</td>
			<td align="left">[line.type]</td>
			<td align="right">[line.duree]</td>
			
		</tr>
		<tr class="total">
			<td colspan="3">&nbsp;</td>
			<td class="title">Total durée appel externe</td>
			<td align="right">[card.duree_total_externe] </td>
		</tr>
		<tr >
			<td colspan="3">&nbsp;</td>
			<td class="title">Total durée appel interne</td>
			<td align="right">[card.duree_total_interne] </td>
		</tr>
		<tr >
			<td colspan="3">&nbsp;</td>
			<td class="title">Total Euros</td>
			<td align="right"><strong>[card.total]</strong></td>
		</tr>
	</table>	

		
	</body>
</html>