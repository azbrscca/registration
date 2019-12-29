        function checkDate( id ) {

          var tokens = $( id ).val().split( "-" );
          var valid = (tokens.length == 3)
          
          if ( valid ) {
            $.each( tokens, function( i, t ) {
              valid &= !isNaN( t );
            });
          }

          var date = new Date(
            parseInt( tokens[ 0 ] ),
            ( parseInt( tokens[ 1 ], 10 ) - 1 ),
            parseInt( tokens[ 2 ], 10 ),
            0, 0, 0, 0 );
           
          if ( ( date.toString() == "Invalid Date" ) ||
               ( date.toString() == "NaN" ) ) {
            $( id ).val( "Invalid Date" );
		    		if ( $( id + "-cg" ) ) { $( id + "-cg" ).addClass( "error" ); }
		    		return false;
          } else {
            $( id ).val( date.format( 'isoDate' ) );
		    		if ( $( id + "-cg" ) ) { $( id + "-cg" ).removeClass( "error" ); }
            return true;
		  }
		}

		function hasDatePast( id ) {

		  var today = new Date();

      var tokens = $( id ).val().split( "-" );
			var date = new Date(
				parseInt( tokens[ 0 ] ),
				( parseInt( tokens[ 1 ], 10 ) - 1 ),
				parseInt( tokens[ 2 ], 10 ),
				0, 0, 0, 0 );

		  date.setHours( 23 );
		  date.setMinutes( 59 )

		  if ( date.getTime() < today.getTime() ) {
		  	$( id + "-warning" ).slideDown();
				$( id + "-cg" ).addClass( 'warning' );
		  } else {
				$( id + "-warning" ).slideUp();
				$( id + "-cg" ).removeClass( 'warning' );
		  }
		}
