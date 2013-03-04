<?php llxHeader();?>

[onshow;block=begin;when [view.mode]=='view']
			<h1>Visualisation de vos jours de congés [userCourant.firstname] [userCourant.lastname]</h1>
			
			<h2>Jour de congés payés N-1 ([congesPrec.anneePrec;strconv=no;protect=no])</h2>
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
			
				<h2>Jour de congés payés N ([congesPrec.anneeCourante;strconv=no;protect=no])</h2>
			<b>Acquis</b><br/>
			Acquis Exercice : 	[congesCourant.acquisEx;strconv=no;protect=no]<br/>
			Acquis Ancienneté : [congesCourant.acquisAnc;strconv=no;protect=no]	<br/>
			Acquis Hors-Période : 	<br/>


			
			<br/>
			<b>Total :</b><br/>

			<b>Reste à prendre :</b><br/>

			
[onshow;block=end]


[onshow;block=begin;when [view.mode]=='edit']
			<h1>Visualisation de vos jours de congés [userCourant.firstname] [userCourant.lastname]</h1>
			
			
[onshow;block=end]

