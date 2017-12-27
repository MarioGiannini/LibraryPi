<?php
	include_once( 'lp.php' );
	
	$lp = new LP();
	$lp->HTMLPageTop();
	echo $lp->BookList();
	$lp->HTMLPageBottom();
?>
