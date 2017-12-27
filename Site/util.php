<?php
	include_once( 'lp.php' );
	
	$Act = isset( $_GET["act"] ) ? $_GET["act"] : "";
	if( $Act == "pj" )
	{
		$BookKey = isset( $_GET["bk"] ) ? $_GET["bk"] : "";
		$PageID = isset( $_GET["pid"] ) ? $_GET["pid"] : "0";
		$lp = new LP();
		$lp->OutputPageAndExit( $BookKey, $PageID ); // Will exit
	}
	else if( $Act=="ts" )
	{
		$Term = isset( $_GET["tt"] ) ? $_GET["tt"] : "";
		$BookKey = isset( $_GET["bk"] ) ? $_GET["bk"] : "";
		$lp = new LP();
		$lp->OutputSearchAndExit( $BookKey, $Term ); // Will exit
	}
?>