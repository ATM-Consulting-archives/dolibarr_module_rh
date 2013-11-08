<?php
	
	require('config.php');
	
	llxHeader('','Informations salarié');

?><script type="text/javascript">
document.location.href="<?=DOL_URL_ROOT.'/user/fiche.php?id='.$user->id ?>";
</script>
<?

	llxFooter();
//	llx

//	header('location:'.DOL_URL_ROOT.'/user/fiche.php?id='.$user->id);
	exit;
