function setOverlaySize() {
	var c = $('#container');
    var v = $('#viewer');
    v.css("width", "100%");
    v.css("height", "90%");
    c.css("width", "90%");
    c.css("height", "90%");
}

function SetupBookReader( br )
{
    br.getPageWidth = function (index) {
        return this.pageWidths[ index ];
    }

    br.getPageHeight = function (index) {
        return this.pageHeights[ index ];
    }

    br.getPageURI = function (index, reduce, rotate) {
        url = "util.php?act=pj&bk=" + this.BookKey + "&pid=" + PgIDs[ index ];
        return url;
    }

    br.getPageSide = function (index) {
        if (0 == (index & 0x1)) {
            return 'R';
        } else {
            return 'L';
        }
    }

    br.getSpreadIndices = function (pindex) {
        var spreadIndices = [null, null];
        if ('rl' == this.pageProgression) {
            // Right to Left
            if (this.getPageSide(pindex) == 'R') {
                spreadIndices[1] = pindex;
                spreadIndices[0] = pindex + 1;
            } else {
                // Given index was LHS
                spreadIndices[0] = pindex;
                spreadIndices[1] = pindex - 1;
            }
        } else {
            // Left to right
            if (this.getPageSide(pindex) == 'L') {
                spreadIndices[0] = pindex;
                spreadIndices[1] = pindex + 1;
            } else {
                // Given index was RHS
                spreadIndices[1] = pindex;
                spreadIndices[0] = pindex - 1;
            }
        }

        return spreadIndices;
    }

    br.getPageNum = function (index) {
        $('#spnWhichPage').hide();
        return index + 1;
    }

    // Override the path used to find UI images
    br.imagesBaseURL = 'bookreader/images/';

    br.getEmbedCode = function (frameWidth, frameHeight, viewParams) {
        return "";
    }

	br.search = function(term) {
        $('#spnWhichPage').hide();
        if( term == "" )
            return;
        $('#textSrch').blur(); //cause mobile safari to hide the keyboard
        var url    = 'util.php?act=ts&r=' + Math.random() + '&bk=' + this.BookKey + '&tt='+escape(term);

        if( this.BookKey == "" )
        {
            var timeout = 3000;
            this.showProgressPopup('Nothing is open');
            $(br.popup).html('Nothing is open');
            setTimeout(function(){ 
                $(br.popup).fadeOut('slow', function() {
                    br.removeProgressPopup();
                })
            },timeout);
        }
        else
        {
            term = term.replace(/\//g, ' '); // strip slashes, since this goes in the url
            this.searchTerm = term;

            this.removeSearchResults();
            this.showProgressPopup('<img id="searchmarker" src="'+this.imagesBaseURL + 'marker_srch-on.png'+'"> Search results will appear below...');

            var request = $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                data: "",
                contentType: 'application/json',
                success: function (data ) {
                    br.BRSearchCallback( data );
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    br.removeProgressPopup();
                    alert('Error ' + textStatus + ': ' + errorThrown);
                }
            });
        }
    }
	
    br.BRSearchCallback = function(results) {
        br.removeSearchResults();
        br.searchResults = results;

        if (0 == results.matches.length) {
            var errStr  = 'No matches were found.';
            var timeout = 3000;
            if (false === results.indexed) {
                errStr  = "<p>This book hasn't been indexed for searching yet. We've just started indexing it, so search should be available soon. Please try again later. Thanks!</p>";
                timeout = 5000;
            }
            $(br.popup).html(errStr);
            setTimeout(function(){
                $(br.popup).fadeOut('slow', function() {
                    br.removeProgressPopup();
                })
            },timeout);
            return;
        }

        var i;
        for (i=0; i<results.matches.length; i++) {
            br.addSearchResult(results.matches[i].text, br.leafNumToIndex(results.matches[i].par[0].page));
        }
        br.updateSearchHilites();
        br.removeProgressPopup();
    }

    br.leafNumToIndex = function( Page ) { return Page; }
}



