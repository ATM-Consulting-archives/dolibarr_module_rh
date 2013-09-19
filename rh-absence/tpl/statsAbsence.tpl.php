[exports.action;strconv=no;protect=no]

<table class="liste formdoc noborder">
	<tr>
		<td><b>Groupes</b> </td>
		<td>[exports.fk_group; strconv=no]</td>
	</tr>
	<tr>
		<td><b>Utilisateurs</b> </td>
		<td>[exports.fk_user; strconv=no]</td>
	</tr>

	<tr>
		<td><b>Date de début</b></td> 
		<td>[exports.date_debut;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td><b>Date de fin</b> </td>
		<td>[exports.date_fin;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td colspan="2"><a href="#" onclick="$('input[type=checkbox]').removeAttr('checked')">Décocher tous les types</a> / <a href="#" onclick="$('input[type=checkbox]').attr('checked','checked')">Cocher tous les types</a>  </td>
	
	</tr>
	<tr>
		<td>[TType.libelle; block=tr;strconv=no;protect=no] </td>
		<td>[TType.case; strconv=no]</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" class="button" value="Générer" /></td>
	</tr>
</table>

<br/>


[onshow;block=begin;when [view.showStat]==1]
<br />
<table class="liste formdoc noborder" style="width:100%">
		<tr class="liste_titre">
			<th>Incidents/Evènements</th>
			<th>Date de début</th>
			<th>Date de fin</th>
			<th>Durée réelle (Jours)</th>
			<th>Durée réelle (Heures)</th>
			<th>Date de début sur la plage</th>
			<th>Date de fin sur la plage</th>
			<th>Durée sur la plage (Jours)</th>
			<th>Durée sur la plage (Heures)</th>
			
		</tr>
	
		
		<tr class="pair">
			<td> [TRecap.event;block=tr;strconv=no;protect=no] </td>
			<td> [TRecap.date_debut;strconv=no;protect=no] </td>
			<td> [TRecap.date_fin;strconv=no;protect=no] </td>
			<td> [TRecap.dureeJour;strconv=no;protect=no] </td>
			<td> [TRecap.dureeHeure;strconv=no;protect=no] </td>
			<td style="background-color: #e1d0df;"> [TRecap.date_debutPlage;strconv=no;protect=no] </td>
			<td style="background-color: #e1d0df;"> [TRecap.date_finPlage;strconv=no;protect=no] </td>
			<td style="background-color: #e1d0df;"> [TRecap.dureeJourPlage;strconv=no;protect=no] </td>
			<td style="background-color: #e1d0df;"> [TRecap.dureeHeurePlage;strconv=no;protect=no] </td>
			
		</tr>
		<tr class="impair">
			<td> [TRecap.event;block=tr;strconv=no;protect=no] </td>
			<td> [TRecap.date_debut;strconv=no;protect=no] </td>
			<td> [TRecap.date_fin;strconv=no;protect=no] </td>
			<td> [TRecap.dureeJour;strconv=no;protect=no] </td>
			<td> [TRecap.dureeHeure;strconv=no;protect=no] </td>
			<td style="background-color: #cfc0cd;"> [TRecap.date_debutPlage;strconv=no;protect=no] </td>
			<td style="background-color: #cfc0cd;"> [TRecap.date_finPlage;strconv=no;protect=no] </td>
			<td style="background-color: #cfc0cd;"> [TRecap.dureeJourPlage;strconv=no;protect=no] </td>
			<td style="background-color: #cfc0cd;"> [TRecap.dureeHeurePlage;strconv=no;protect=no] </td>
			
		</tr>

	
</table>
[onshow;block=end]