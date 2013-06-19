
[infos.titre;strconv=no;protect=no]

Renseignez ici la limite de consommation : [infos.limite;strconv=no;protect=no] L/100km. 
Sur la période de [infos.plagedebut;strconv=no;protect=no] à [infos.plagefin;strconv=no;protect=no] <br><br> 
Utilisateur : [infos.fk_user;strconv=no;protect=no] <br><br>

[infos.valider;strconv=no;protect=no] <br>
<br>

<div id="content">
<table class="liste formdoc noborder" style="width:100%">
	<thead >
		<tr class="liste_titre">
			<td>Carte Total</td>
			<td>Véhicule</td>
			<td>Relevé kilométrique</td>
			<td>Différence kilométrique</td>
			<td>Plein d'essence</td>
			<td>Consommation</td>
			<td>Date</td>
			<td>Utilisateur</td>
		</tr>
	</thead>
	<tbody>
		<tr
		[onshow;block=begin;when [ressource.parite]=='pair'] class="pair" [onshow;block=end]
		[onshow;block=begin;when [ressource.parite]=='impair'] class="impair" [onshow;block=end]
		>
			<td>[ressource.nom;block=tr;strconv=no;protect=no]</td>
			<td>[ressource.vehicule;strconv=no;protect=no]</td>
			<td>[ressource.km;strconv=no;protect=no]</td>
			<td>[ressource.diffkm;strconv=no;protect=no]</td>
			<td>[ressource.essence;strconv=no;protect=no]</td>
			<td>[ressource.conso;strconv=no;protect=no]</td>
			<td>[ressource.date;strconv=no;protect=no]</td>
			<td>[ressource.user;strconv=no;protect=no]</td>
		</tr>
	</tbody>
</table>
</div>


