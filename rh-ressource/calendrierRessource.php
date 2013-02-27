<?php
	require('config.php');
	//require('./class/ressource.class.php');
	
	
	
	llxHeader('','Calendrier des ressources');
	
	?>
	
	<h1>Agenda des ressources</h1>

	<div id="agenda"></div>
	
	<script type="text/javascript">
	
	$.get('../wdCalendar/sample.php', function(data) {
	  $("#agenda").html(data);
	});
	</script>

<?php

	llxFooter();

