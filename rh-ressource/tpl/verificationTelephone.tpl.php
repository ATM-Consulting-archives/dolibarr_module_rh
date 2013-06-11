
[infos.titre;strconv=no;protect=no]
<table>
	<tr>
		<td>[infos.date_debut;strconv=no;protect=no]</td>
		<td>[infos.date_fin;strconv=no;protect=no]</td>
		<td><input type="submit" class="button" value="Générer" /></td>
	</tr>
</table>

[onshow;block=begin;when [view.mode]=='view']
<br />
<table class="liste formdoc noborder" style="width:100%;">
	<thead>
		<tr class="liste_titre">
			<th>Société</th>
			<th>Collaborateur</th>
			<th>Type de Téléphone</th>
			<th>Numéro de Téléphone</th>
			<th colspan=2 style="text-align:center;">Forfait</th>
			<th colspan=2 style="text-align:center;">Consommation</th>
			
		</tr>
		<tr class="liste_titre">
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th style="text-align:center;">Interne</th>
			<th style="text-align:center;">Externe</th>
			<th style="text-align:center;">Interne</th>
			<th style="text-align:center;">Externe</th>
		</tr>
	</thead>
	<tbody>
		<tr 
		[onshow;block=begin;when [tabTel.11]=='pair'] class="pair" [onshow;block=end]
		[onshow;block=begin;when [tabTel.11]=='impair'] class="impair" [onshow;block=end]
		>
			<td> [tabTel.0;block=tr;strconv=no;protect=no] </td>
			<td> [tabTel.1;strconv=no;protect=no] </td>
			<td> [tabTel.2;strconv=no;protect=no] </td>
			<td> [tabTel.3;strconv=no;protect=no] </td>
			[onshow;block=begin;when [tabTel.4]=='extint']
			<td style="text-align:center;"> [tabTel.6;strconv=no;protect=no] </td>
			<td style="text-align:center;"> [tabTel.7;strconv=no;protect=no] </td>
			<td style="text-align:center;"> [tabTel.9;strconv=no;protect=no] </td>
			<td style="text-align:center;"> [tabTel.10;strconv=no;protect=no] </td>
			[onshow;block=end]
			[onshow;block=begin;when [tabTel.4]!='extint']
			<td colspan=2 style="text-align:center;"> [tabTel.5;strconv=no;protect=no] </td>
			<td colspan=2 style="text-align:center;"> [tabTel.8;strconv=no;protect=no] </td>
			[onshow;block=end]
		</tr>
		
		
	</tbody>
</table>
[onshow;block=end]