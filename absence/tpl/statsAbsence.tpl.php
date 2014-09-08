[exports.action;strconv=no;protect=no]

<table class="liste formdoc noborder">
	<tr>
		<td><b>[translate.Groups;strconv=no]</b> </td>
		<td>[exports.fk_group; strconv=no]</td>
	</tr>
	<tr>
		<td><b>[translate.Users;strconv=no]</b> </td>
		<td>[exports.fk_user; strconv=no]</td>
	</tr>

	<tr>
		<td><b>[translate.StartDate;strconv=no]</b></td> 
		<td>[exports.date_debut;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td><b>[translate.EndDate;strconv=no]</b> </td>
		<td>[exports.date_fin;strconv=no;protect=no]</td>
	</tr>
	<tr>
		<td colspan="2"><a href="#" onclick="$('input[type=checkbox]').removeAttr('checked')">[translate.UnCheckAllTypes;strconv=no;protect=no]</a> / <a href="#" onclick="$('input[type=checkbox]').attr('checked','checked')">[translate.CheckAllTypes;strconv=no;protect=no]</a>  </td>
	
	</tr>
	<tr>
		<td>[TType.libelle; block=tr;strconv=no;protect=no] </td>
		<td>[TType.case; strconv=no]</td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" class="button" value="[translate.Generate;strconv=no;protect=no]" /></td>
	</tr>
</table>

<br/>


[onshow;block=begin;when [view.showStat]==1]
<br />
<table class="liste formdoc noborder" style="width:100%">
		<tr class="liste_titre">
			<th>[translate.IncidentsAndEvents;strconv=no;protect=no]</th>
			<th>[translate.StartDate;strconv=no;protect=no]</th>
			<th>[translate.EndDate;strconv=no;protect=no]</th>
			<th>[translate.RealDurationInDays;strconv=no;protect=no]</th>
			<th>[translate.RealDurationInHours;strconv=no;protect=no]</th>
			<th>[translate.StartDateOnSlot;strconv=no;protect=no]</th>
			<th>[translate.EndDateOnSlot;strconv=no;protect=no]</th>
			<th>[translate.DurationOnSlotInDays;strconv=no;protect=no]</th>
			<th>[translate.DurationOnSlotInHours;strconv=no;protect=no]</th>
			
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