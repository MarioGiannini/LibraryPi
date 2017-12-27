<?php
	$GLOBALS[ "db_srvr" ] = "localhost";
	$GLOBALS[ "db_user" ] = "root";
	$GLOBALS[ "db_pass" ] = "OfEvil!";
	$GLOBALS[ "db_data" ] = "lp";
	
	@ $GLOBALS["db"] = mysqli_connect( $GLOBALS[ "db_srvr" ], $GLOBALS[ "db_user" ], $GLOBALS[ "db_pass" ], $GLOBALS[ "db_data" ] );
	if( !$GLOBALS["db"] )
		die( "Database connection failed.  Please check settings in " . __FILE__ );
	
	function db() 
	{
		return $GLOBALS["db"];
	}
?>
