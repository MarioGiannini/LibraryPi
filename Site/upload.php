<?php
	include_once( 'lp.php' );
	
	$lp = new LP();
	$lp->HTMLPageTop();
	if( isset( $_POST[ "ispost" ] ) )
	{
		if( $lp->AddBook( $_POST[ "book_title" ]) )
			echo "Uploaded";
		else
			echo "Error: " . $lp->Err;
		}
	else
	{
?>
	<form method="post" action="upload.php" enctype="multipart/form-data">
	<input type=hidden name=ispost value=\"Y\"><br>
	<table border=0><tr><td>Book title:</td><td><input type=text name=book_title></td></tr>
	<tr><td>Zip file:</td><td><input type="file" name="zip_file" id="zip_file"></td></tr>
	<tr><td colspan=2><input type="submit" value="Upload" name="submit"></td></tr></table>
	</form>
<?php	
	}
	
	$lp->HTMLPageBottom();
?>
