<?php llxHeader();?>

[onshow;block=begin;when [view.mode]=='view']
			<h1>Visualisation de vos jours de congés [userCourant.firstname] [userCourant.lastname]</h1>
			
			<table  border ="1" cellspacing="1" cellpadding="1">
				<tr>
					<td>
						<div>
							<h2>Jours de congés payés N-1 ([congesPrec.anneePrec;strconv=no;protect=no])</h2>
							<b>Acquis</b><br/>
							Acquis Exercice : [congesPrec.acquisEx;strconv=no;protect=no]	<br/>
							Acquis Ancienneté : [congesPrec.acquisAnc;strconv=no;protect=no]	<br/>
							Acquis Hors-Période : [congesPrec.acquisHorsPer;strconv=no;protect=no]	<br/>
							
							<br/>
							Report congés non soldés : [congesPrec.reportConges;strconv=no;protect=no]<br/>
							
							<br/>
							<b>Total : [congesPrec.total;strconv=no;protect=no] </b><br/>
							Pris : [congesPrec.congesPris;strconv=no;protect=no]<br/>
							<b>Reste à prendre : [congesPrec.reste;strconv=no;protect=no]</b><br/>
						</div>
					</td>
				<tr>
			</table>
			
			<table style="margin-top: 30px"  border ="1" cellspacing="1" cellpadding="1">
				<tr>
					<td>
						<div>
							<h2>Jours de congés payés N ([congesCourant.anneeCourante;strconv=no;protect=no])</h2>
							<b>Acquis</b><br/>
							Acquis Exercice : 	[congesCourant.acquisEx;strconv=no;protect=no]<br/>
							Acquis Ancienneté : [congesCourant.acquisAnc;strconv=no;protect=no]	<br/>
							Acquis Hors-Période : [congesCourant.acquisHorsPer;strconv=no;protect=no]	<br/>
				
							<br/>
							<b>Total : [congesCourant.total;strconv=no;protect=no]</b><br/>

							<b> Dernière clôture :</b><br/>
						</div>
					</td>
				<tr>
			</table>
			
			
			
				

			
[onshow;block=end]


[onshow;block=begin;when [view.mode]=='edit']
			<h1>Visualisation de vos jours de congés [userCourant.firstname] [userCourant.lastname]</h1>
			
			
[onshow;block=end]

