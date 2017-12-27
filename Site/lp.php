<?php
include_once( 'lp_dbconf.php');

class LP
{
	var $PageIDs = "";
	var $PageHeights = "";
	var $PageWidths = "";
	var $PageCount = 0;
	var $BookKey = "";
	var $Err = "";
	
	// Output consistent web page top
	function HTMLPageTop( $Title = "LibraryPi", $Header="", $Script="" )
	{
		echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n".
			 "<html lang=\"en\">\n" .
			 "<head>\n" .
			 "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">\n" .
			 "<title>$Title</title>\n$Header\n" .
			 "<link rel=\"stylesheet\" type=\"text/css\" href=\"lp.css\"/>\n" .
			 "</head>\n" .
			 "<script>\n$Script\n</script>\n".
			 "<body>\n" .
			 "<div id=lpmenu><a href=index.php>Home</a>&nbsp;&nbsp;<a href=upload.php>Upload</a></div>";
	}
	// Output consistent web page bottom
	function HTMLPageBottom( $Script = '' )
	{
		echo "</body></html>\n<script>\n$Script\n</script>\n";
	}
	// Generates a unique random key
	function UKey()
	{
		return md5( "LP1_" + uniqid(rand(), true) );
	}
	// Extracts files from zip file and add to database
	function AddBook( $Title )
	{
		$UKey = $this->UKey();
		$Title = isset( $_POST["book_title"] ) ? $_POST["book_title"] : "Untitled";

		$DestPath = $_SERVER["DOCUMENT_ROOT"]."/uploads/$UKey/";
		$zip = new ZipArchive;
		$Err = $zip->open( $_FILES['zip_file']['tmp_name'] );
		if ( $Err  === TRUE) {
			$Cmd = "insert into lp_book (title, ukey) values( '$Title', '$UKey' )";
			mysqli_query( db(), $Cmd);
			$BookID  = mysqli_insert_id ( db() );
			$Seq = 1;
			for( $i = 0; $i < $zip->numFiles; $i++ ){ 
				$Filename = $zip->getNameIndex( $i );
				if( preg_match('(\.jpg$)i', $Filename) )
				{
					if( !$zip->extractTo( $DestPath, $Filename ) )
					{
						$zip->close();
						$this->Err = "Error extracting ".$_FILES['zip_file']['name']." to $DestPath";
						return false;
					}
					list($width, $height, $type, $attr) = getimagesize( $DestPath . $Filename);
					$Cmd = "insert into lp_page (book_id, seq, status, height, width, filename ) values( $BookID, $Seq, 'N', $height, $width, '$DestPath$Filename'  )";
					mysqli_query( db(), $Cmd);					
					$Seq++;
				}
			}
			$zip->close();
		} 
		else
		{
			$this->Err = "Error loading temporary file";
			return false;
		}
		return true;
	}
	// Returns list of books in database as links.
	function BookList()
	{
		$Ret = '';
		$Cmd = "select b.title, b.ukey, "
				. " (select count(*) from lp_page P where P.book_id = b.id ) Pages, "
				. "(select count(*) from lp_page P where P.book_id = b.id and P.status<>'I' ) ToDo "
				. " from lp_book b"
				. " order by b.title";

		$result = mysqli_query( db(), $Cmd);
		if( $result === false )
			return( "Error: Can't read book data.  Did you create the SQL tables in the correct database?<br>" );
		while ( $row = mysqli_fetch_array( $result ) )
		{
			$Pages = $row["Pages"];
			$ToDo = $row["ToDo"];
			if( $ToDo == 0 )
				$Det = "$Pages pages";
			else
				$Det = "$ToDo pages to process";
			$Ret .= "<a href=\"reader.php?bk=" . $row["ukey"] . "\">".$row["title"] . " [$Det]</a><br />";
		}
		$result->close();
		if( strlen( $Ret ) == 0 )
			$Ret = "No books uploaded yet";
		return '<div class=booklist>'.$Ret.'</div>';
	}
	// Load page data like count and dimensions from a book, used by other methods
	function LoadPages( $BookKey )
	{
		$this->BookKey = preg_replace("/[^A-Za-z0-9 ]/", '', $BookKey);
		$this->PageIDs = "";
		$this->PageHeights = "";
		$this->PageWidths = "";
		$this->PageCount = 0;
		
		$Cmd = "select p.id, p.width, p.height from lp_book b " .
			" join lp_page p on p.book_id = b.id "  .
			" where b.ukey ='$this->BookKey' order by p.seq";
		$result = mysqli_query( db(), $Cmd);
		$Sep = "";
		while ( $row = mysqli_fetch_array( $result ) )
		{
			$this->PageIDs .= $Sep . $row["id"];
			$this->PageHeights .= $Sep . $row["height"];
			$this->PageWidths .= $Sep . $row["width"];
			$Sep = ',';
			$this->PageCount++;
		}
		$result->close();
	}
	// Determines filename of page JPG file for a specific book page
	function GetPageFilename( $BookKey, $PageID )
	{
		$Ret = "";
		$Cmd = "select filename from lp_book b ".
			" join lp_page p on p.book_id = b.id ".
			" where b.ukey ='$BookKey' ".
			" and p.id = $PageID";
		$result = mysqli_query( db(), $Cmd);
		if ( $row = mysqli_fetch_array( $result ) )
			$Ret = $row["filename"];
		$result->close();
		return $Ret;
	}
	// Outputs the contents of a JPG file for display in browser
	function OutputPageAndExit( $BookKey, $PageID ) 
	{
		$Filename = $this->GetPageFilename( $BookKey, $PageID );
		header("Content-Type: image/jpg");
		header("Content-Length: " . filesize($Filename));
		header("Content-Disposition: inline; filename=\"{$BookKey}_$PageID.jpg\"" );
		readfile( $Filename );
		exit;
	}

	function OCRFiles( ) 
	{
		$Ret = 0;
		$Cmd = 'select p.id, filename '.
			' from lp_page p '.
			' where status = \'N\'';
		$result = mysqli_query( db(), $Cmd );
		if ( $row = mysqli_fetch_array( $result ) )
		{
			$ID = $row["id"];
			$Filename = $row["filename"];
			exec( "tesseract $Filename $Filename hocr" ); // Create same file with .hocr extension appended
			if( file_exists( $Filename.".hocr" ) )
			{
				mysqli_query( db(), "update lp_page set status='O' where id = $ID" );
				$Ret++;
			}
		}
		$result->close();
		return $Ret;			
	}
	
	function ProcessWord( $Word )
	{
		return trim( preg_replace('/[^a-z0-9]+/i', '_', $Word), '_' );
	}
	function GetWordID( $Word )
	{
		$Word =  $this->ProcessWord( $Word );
		if( strlen( $Word ) == 0 )
			return 0;
		$result = mysqli_query( db(), "select * from lp_word w where w.word = '$Word' ");
		if ( $row = mysqli_fetch_array( $result ) )
			$Ret = $row["id"];
		else
		{
			mysqli_query( db(), "insert into lp_word (word) values( '$Word') ");
			$Ret  = mysqli_insert_id ( db() );
		}
		$result->close();
		return $Ret;
	}
	function AddOCRRecords()
	{
		$Ret = 0;
		$Cmd = 'select id, filename '.
			' from lp_page '.
			'where status = \'O\'';
		$result = mysqli_query( db(), $Cmd);
		$Max = 10;
		while ( $row = mysqli_fetch_array( $result ) )
		{
			$Max--;
			if( $Max == 0 )
				break;
			$PageID = $row["id"];
			$Filename = $row["filename"].'.hocr';
			$Text = file_get_contents( $Filename );
			$Seq = 0;
			while( ($P = strpos( $Text, "<span class='ocrx_word'" )) !== FALSE )
			{
				$Text = substr( $Text, $P+23 );

				$P = strpos( $Text, "</span>" );
				$Word = substr( $Text, 0, $P );
				$P = strpos( $Word, 'bbox'  );
				$Word = substr( $Word, $P+4 );
				$P = strpos( $Word, ';' );
				$Dim = substr( $Word, 0, $P  );
				$P = strpos( $Word, '>' );
				$Word = strip_tags( substr( $Word, $P+1 ) );
				$WordID = $this->GetWordID( $Word );
				if( $WordID > 0 )
				{
					$Seq++;
					$ar = explode( ' ', $Dim );
					$Cmd = "insert into lp_page_word ( page_id, word_id, seq, posleft, postop, posright, posbottom ) values (".
						"$PageID, $WordID, $Seq, ".$ar[1].",".$ar[2].",".$ar[3].",".$ar[4].")"; // LTRB
					mysqli_query( db(), $Cmd );
				}
			}
			$Cmd = "update lp_page set status='I' where id = $PageID";
				mysqli_query( db(), $Cmd );
			$Ret++;
			
		}
		$result->close();
		return $Ret;			
	}

	function OutputSearchAndExit( $BookKey, $OTerms )
	{
		$BookKey = preg_replace("/[^A-Za-z0-9 ]/", '', $BookKey);
		$Terms = array_unique( explode( ' ', $OTerms ));
		$Seq = 0;
		$Seq0 = $Seq-1;
		$FieldList = '';
		$JoinList = '';
		$Blocks = array();
		$Pages = array();

		foreach ($Terms as $Term) 
		{
			{
					$FieldList .= ",pw{$Seq}.id pw{$Seq}id, 'N' group{$Seq}, ".
						" pw{$Seq}.seq pw{$Seq}seq, ".
						" pw{$Seq}.posleft pw{$Seq}posleft, ".
						" pw{$Seq}.postop pw{$Seq}postop, ".
						" pw{$Seq}.posright pw{$Seq}posright, ".
						" pw{$Seq}.posbottom pw{$Seq}posbottom, ".
						" w{$Seq}.word w{$Seq}word ";
				$JoinList .= 	
					" join lp_page_word pw{$Seq} on pw{$Seq}.page_id = lp_page.id\n".
					" join lp_word      w{$Seq}  on w{$Seq}.id = pw{$Seq}.word_id and w{$Seq}.word = '".$this->ProcessWord($Term)."' ";
					$Seq0 = $Seq;
					$Seq++;
			}
		}

		$Cmd = "select distinct lp_page.seq, height, width, $Seq setcount $FieldList " .
			" from lp_page $JoinList " .
			" join lp_book b on b.id = lp_page.book_id " .
			" where b.ukey = '$BookKey' " .
			" order by lp_page.id, pw0seq";
			//die( $Cmd );
		$result = mysqli_query( db(), $Cmd);
		while ( $row = mysqli_fetch_array( $result ) )
		{
			$Group = "";
			for( $i=0; $i < $Seq; $i++ )
			{
				$ThisPage = $row["seq"];
				$ThisID = $row["pw{$i}id"];
				$ThisGroup = $row["group{$i}"];
				$ThisLeft = $row["pw{$i}posleft"];
				$ThisTop = $row["pw{$i}postop"];
				$ThisRight = $row["pw{$i}posright"];
				$ThisBottom = $row["pw{$i}posbottom"];
				$ThisWord = $row["w{$i}word"];
				$PageHeight = $row["height"];
				$PageWidth = $row["width"];
				if( !isset( $Pages[ "page$ThisPage" ] ) )
					$Pages[ "page$ThisPage" ] = array();
				$Pages[ "page$ThisPage" ][ "$PageHeight\t$PageWidth\t$ThisLeft\t$ThisTop\t$ThisRight\t$ThisBottom\t$ThisWord" ] = "Bogus";
			}
		}
		$RTerms = addslashes( $OTerms );
		$PageCount = count( $Pages );
		$Ret = 	"{\n" .
				"\"ia\": \"designevaluation25clin\",\n" .
				"\"q\": \"$RTerms\",\n" .
				"\"page_count\": $PageCount,\n" .
				// "\"body_length\": 475677," .
				"\"leaf0_missing\": true," .
				"\"matches\": [\n";		
		$Text = $OTerms;
		$SoFar = 0;
		foreach( $Pages as $PageKey => $Blocks )
		{
			$Page = str_replace( 'page', '', $PageKey) -1; 
			$Boxes = "";
			foreach( $Blocks as $BlockKey => $Bogus )
			{
				$ar = explode( "\t", $BlockKey );
				$Cnt = count( $ar );
				$PageHeight = $ar[0];
				$PageWidth = $ar[1];

				for( $i = 6; $i < $Cnt; $i += 7 )
				{
					if( strlen( $Boxes ) > 0 )
						$Boxes .= ",\n{ \"page\": $Page, \"r\": ".$ar[4].", \"b\": ".$ar[5].", \"t\": ".$ar[3].", \"l\": ".$ar[2]." }";
					else
						$Boxes .= "{ \"page\": $Page, \"r\": ".$ar[4].", \"b\": ".$ar[5].", \"t\": ".$ar[3].", \"l\": ".$ar[2]." }";
				}
			}
			if( $SoFar > 0 )
				$Ret .= ",";
			$SoFar++;
			$Ret .= "{\n" .
					"   \"text\": \"$Text\",\n" .
					"   \"par\": [\n" .
					"       {\n" .
					"            \"page\": $Page, \"page_width\": $PageWidth, \"page_height\": $PageHeight,\n" .
					"            \"boxes\": [\n" .
					"                $Boxes\n" .
					"            ]\n" .
					"        }\n" .
					"    ]\n" .
					"}\n";
			
		}
		
		$Ret .= "] }\n";
		echo $Ret;
		exit();
	}	
}
?>