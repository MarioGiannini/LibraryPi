<?php
	include_once( 'lp.php' );
	
	function DoProcess() {
		$lp = new LP();
		$lp->OCRFiles();
		$lp->AddOCRRecords();
		$lp->PurgeBooks();
	} // end of function DoProcess() 
	
    $fp = fopen( "uploads/file.flag","w");
    if (flock($fp, LOCK_EX)) {
		try {
			$StopAt = time() + 50; // Run for 50 seconds
			$Cnt = 0;
			while( $StopAt > time() )
			{
				DoProcess();
				sleep( 2 );
				$Cnt++;
			}
		} 
		catch( Exception $e ) 
		{
			echo "Exception: " . $e-> $e->getMessage();
		}
        
        flock($fp, LOCK_UN);
    } // No else needed
	fclose($fp);
?>
