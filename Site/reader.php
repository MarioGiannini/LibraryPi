<?php
	include_once( 'lp.php' );
	$Header = 	
	"<link rel=\"stylesheet\" type=\"text/css\" href=\"bookreader/BookReader.css\" />\n" .
	"<script type=\"text/javascript\" src=\"bookreader/jquery-1.4.2.min.js\"></script>\n" .
    "<script type=\"text/javascript\" src=\"bookreader/jquery-ui-1.8.5.custom.min.js\"></script>\n" .
	"<script type=\"text/javascript\" src=\"bookreader/dragscrollable.js\"></script>\n" .
    "<script type=\"text/javascript\" src=\"bookreader/jquery.colorbox-min.js\"></script>\n" .
    "<script type=\"text/javascript\" src=\"bookreader/jquery.ui.ipad.js\"></script>\n" .
    "<script type=\"text/javascript\" src=\"bookreader/jquery.bt.min.js\"></script>\n" .
    "<script type=\"text/javascript\" src=\"bookreader/BookReader.js\"></script>\n" .
    "<script type=\"text/javascript\" src=\"reader.js\"></script>\n";
	
	$lp = new LP();
	$BookKey = isset( $_GET['bk'] ) ? $_GET['bk'] : "";
	$lp->LoadPages( $BookKey );
	$lp->HTMLPageTop( "Reader", $Header);
?>
<div id="BookReader">
            BookReader <br/>
            <noscript>
            <p>
                The BookReader requires JavaScript to be enabled. 
            </p>
            </noscript>
        </div>		
<?php		
	$lp->HTMLPageBottom();
	echo "<script>\n".
		"		var br = new BookReader();\n".
		"		var CurPageIndex = 0;\n" .
		"		var PgIDs = [$lp->PageIDs];\n".
		"		br.pageWidths = [$lp->PageWidths];\n".
		"		br.pageHeights = [$lp->PageHeights];\n".
		"		br.BookKey = '$BookKey';\n" .
		"		br.numLeafs = $lp->PageCount;\n".
		"		br.bookTitle = \"Demo\";\n" .
		"		br.bookUrl = \"Demo\";\n" .
		"		br.logoURL = \"Demo\";\n" .
		"		SetupBookReader( br );\n";
?>
    br.init();
    // Disable items as needed
    $('#BRtoolbar').find('.read').hide();
    $('.info').hide();
    $('.thumb').hide();

</script>